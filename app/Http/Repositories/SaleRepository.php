<?php

namespace App\Http\Repositories;

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
        return $this->sale::with('customer', 'products')->get();
    }

    public function indexByDate($from = null, $to = null)
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

        return $query->orderBy('id', 'desc')->get();
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
        return $this->sale::with('customer', 'products')->findOrFail($id);
    }

    public function store(array $data)
    {
        $isConfirmed = $data['status'] === 'confirmed';
        DB::beginTransaction();
        try {
            if (!$isConfirmed) {
                $data['previous_balance'] = 0;
                $sale = $this->createSale($data, $isConfirmed);
            } else {
                $this->calculateProfit($data);
                $data['previous_balance'] = Customer::find($data['customer_id'])->balance;
                $sale = $this->createSale($data, $isConfirmed);
                $this->handleConfirmedSale($sale);
            }
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
        $sale->products()->sync($data['products'] ?? []);
        return $sale;
    }

    private function calculateProfit(array &$data)
    {
        $totalCost = 0;
        foreach ($data['products'] as $product) {
            $fetchedProduct = $this->productRepository->find($product['product_id']);
            $totalCost += $fetchedProduct->cost * $product['quantity'];
        }
        $data['profit'] = $data['total_amount'] - $totalCost;
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
                $data['previous_balance'] = Customer::find($sale['customer_id'])->balance;
                $this->updateSale($sale, $data);
                $sale->refresh();
                $this->handleConfirmedSale($sale);
            } else {
                $sale = $this->updateSale($sale, $data);
            }
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
        $sale->products()->sync($data['products'] ?? []);
        return $sale;
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
                'note' => 'رقم القائمة: ' . $sale->id . ' - اسم الزبون: ' . $sale->customer->name,
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
