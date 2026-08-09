<?php

namespace App\Http\Repositories;

use App\Exceptions\RecordNotFoundException;
use App\Models\Customer;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SaleRepository
{
    private Sale $sale;

    private CustomerTransactionRepository $customerTransactionRepository;

    private ProductRepository $productRepository;

    public function __construct(Sale $sale, CustomerTransactionRepository $customerTransactionRepository, ProductRepository $productRepository)
    {
        $this->sale = $sale;
        $this->customerTransactionRepository = $customerTransactionRepository;
        $this->productRepository = $productRepository;
    }

    public function index()
    {
        return $this->sale::with(['customer', 'products' => fn ($query) => $query->withTrashed()])
            ->orderBy('id', 'desc')
            ->paginate(15);
    }

    public function indexByDate($from = null, $to = null)
    {
        $query = $this->sale::query()->where('status', 'confirmed');

        if ($from !== null) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $from)->startOfDay();
            $query->where('updated_at', '>=', $fromDate);
        }

        if ($to !== null) {
            $toDate = Carbon::createFromFormat('Y-m-d', $to)->endOfDay();
            $query->where('updated_at', '<=', $toDate);
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function statisticsByDate($from = null, $to = null)
    {
        $query = $this->sale::query()->where('status', 'confirmed');

        if ($from !== null) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $from)->startOfDay();
            $query->where('updated_at', '>=', $fromDate);
        }

        if ($to !== null) {
            $toDate = Carbon::createFromFormat('Y-m-d', $to)->endOfDay();
            $query->where('updated_at', '<=', $toDate);
        }

        $totals = $query->selectRaw('COALESCE(SUM(total_amount), 0) as total_sales, COALESCE(SUM(profit), 0) as sales_profit')
            ->first();

        return [
            'total_sales' => (int) $totals->total_sales,
            'sales_profit' => (int) $totals->sales_profit,
        ];
    }

    // index by date with products
    public function indexByDateWithProductsAndCustomer($from = null, $to = null)
    {
        $query = $this->sale::query();

        if ($from !== null) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $from)->startOfDay();
            $query->where('updated_at', '>=', $fromDate);
        }

        if ($to !== null) {
            $toDate = Carbon::createFromFormat('Y-m-d', $to)->endOfDay();
            $query->where('updated_at', '<=', $toDate);
        }

        return $query->with(['products' => function ($query) {
            $query->withTrashed();
        }, 'customer'])->where('status', 'confirmed')->orderBy('id', 'desc')->get();
    }

    public function find($id)
    {
        $sale = $this->sale::with('customer', 'products', 'user')->find($id);

        if (! $sale) {
            throw new RecordNotFoundException('Sale not found');
        }

        return $sale;
    }

    public function store(array $data)
    {
        $isConfirmed = $data['status'] === 'confirmed';
        DB::beginTransaction();
        try {
            if (! $isConfirmed) {
                $data['previous_balance'] = 0;
                $sale = $this->createSale($data, $isConfirmed);
            } else {
                $this->calculateProfit($data);
                $data['previous_balance'] = $this->customerBalance($data['customer_id']);
                $sale = $this->createSale($data, $isConfirmed);
                $this->handleConfirmedSale($sale);
            }
            $sale->load('user');
            DB::commit();

            return $sale;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function createSale(array &$data): Sale
    {
        $sale = $this->sale::create($data);
        $sale->products()->sync($this->formatProductsForSync($data['products'] ?? []));

        return $sale;
    }

    private function calculateProfit(array &$data)
    {
        $totalCost = 0;
        foreach ($data['products'] as $product) {
            // withTrashed: a sale drafted before a product was deleted must still be
            // confirmable. Validation uses exists:products,id, which ignores
            // deleted_at, so a strict find() here 500s on a line the request accepted.
            $fetchedProduct = $this->productRepository->findWithTrashed($product['product_id']);
            $totalCost += $fetchedProduct->cost * $product['quantity'];
        }
        $data['profit'] = $data['total_amount'] - $totalCost;
    }

    /**
     * withTrashed: a draft written before the customer was deleted must still be
     * resolvable. Without it Customer::find() returns null and confirming the sale
     * dies on ->balance — and those drafts cannot be deleted either (they are not
     * the customer's last sale) and customers have no restore screen, so the row
     * would be stuck forever with no way out from the UI.
     */
    private function customerBalance($customerId)
    {
        $customer = Customer::withTrashed()->find($customerId);

        if (! $customer) {
            throw new RecordNotFoundException('Customer not found');
        }

        return $customer->balance;
    }

    private function handleConfirmedSale(Sale $sale)
    {
        $this->storeCustomerTransaction($sale);
        $this->calculateProductsQuantity($sale);
    }

    // update
    public function update(Sale $sale, array $data)
    {
        DB::beginTransaction();
        try {
            $isConfirmed = $data['status'] === 'confirmed';
            if ($isConfirmed) {
                $this->calculateProfit($data);
                $data['previous_balance'] = $this->customerBalance($sale['customer_id']);
                $this->updateSale($sale, $data);
                $sale->refresh();
                $this->handleConfirmedSale($sale);
            } else {
                $sale = $this->updateSale($sale, $data);
            }
            // Not loadMissing: find() already loaded the previous author, and
            // user_id has just been reassigned to whoever is editing now.
            $sale->load('user');
            DB::commit();

            return $sale;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    // update sale
    private function updateSale(Sale $sale, array $data)
    {
        $sale->update($data);
        $sale->products()->sync($this->formatProductsForSync($data['products'] ?? []));

        return $sale;
    }

    private function formatProductsForSync(array $products): array
    {
        return collect($products)->mapWithKeys(function ($product) {
            $id = $product['product_id'];
            unset($product['product_id']);
            return [$id => $product];
        })->toArray();
    }

    private function storeCustomerTransaction(Sale $sale)
    {
        DB::beginTransaction();
        try {
            $customerTransaction = $this->customerTransactionRepository->store([
                'user_id' => $sale->user_id,
                'customer_id' => $sale->customer_id,
                'amount' => $sale->total_amount,
                'type' => 'debit',
                'note' => 'رقم القائمة: '.$sale->id.' - اسم الزبون: '.$sale->customer->name,
            ]);
            // Store transaction id in sale
            $sale->update([
                'customer_transaction_id' => $customerTransaction->id,
            ]);
            DB::commit();

            return $sale;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function calculateProductsQuantity(Sale $sale)
    {
        $products = $sale->products;
        foreach ($products as $product) {
            $product->quantity -= $product->pivot->quantity;
            $product->save();
        }
    }

    private function reverseCalculateProductsQuantity(Sale $sale)
    {
        $products = $sale->products;
        foreach ($products as $product) {
            $product->quantity += $product->pivot->quantity;
            $product->save();
        }
    }

    public function destroy(Sale $sale)
    {
        DB::beginTransaction();
        try {
            $sale->products()->detach();
            $isConfirmed = $sale->status === 'confirmed';
            if ($isConfirmed) {
                $this->customerTransactionRepository->destroy($sale->customerTransaction);
                $this->reverseCalculateProductsQuantity($sale);
                $sale->update([
                    'customer_transaction_id' => null,
                ]);
            }
            $sale->delete();
            DB::commit();

            return $sale;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function checkLastSaleForCustomer($sale)
    {
        $customer = $sale->customer;
        $lastSale = $customer->sales->sortByDesc('created_at')->first();
        if ($lastSale->id != $sale->id) {
            return false;
        }

        return true;
    }
}
