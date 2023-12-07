<?php

namespace App\Http\Repositories;

use App\Models\CustomerTransaction;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class SaleRepository
{
    private Sale $sale;
    private CustomerTransactionRepository $customerTransactionRepository;
    public function __construct(Sale $sale, CustomerTransactionRepository $customerTransactionRepository)
    {
        $this->sale = $sale;
        $this->customerTransactionRepository = $customerTransactionRepository;
    }

    public function index()
    {
        return $this->sale::with('customer', 'products')->get();
    }

    public function indexByDate($from = null, $to = null)
    {
        $query = $this->sale::query();

        if ($from !== null) {
            $query->where('updated_at', '>=', $from);
        }

        if ($to !== null) {
            $query->where('updated_at', '<=', $to);
        }

        return $query->orderBy('id', 'desc')->get();
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
            $sale = $this->createSale($data, $isConfirmed);
            if ($isConfirmed) {
                $this->handleConfirmedSale($sale);
            }
            DB::commit();
            return $sale;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function createSale(array &$data, bool $isConfirmed): Sale
    {
        if (!$isConfirmed) {
            $data['previous_balance'] = null;
        } else {
            $this->calculateProfit($data);
        }

        $sale = $this->sale::create($data);
        $sale->products()->sync($data['products'] ?? []);

        return $sale;
    }

    private function calculateProfit(array &$data)
    {
        $totalCost = array_reduce($data['products'], function ($carry, $product) {
            return $carry + $product['cost'] * $product['quantity'];
        }, 0);

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
            $this->updateCustomerTransaction($sale, $data);
            $sale->products()->sync($data['products'] ?? []);
            $sale->update($data);
            if ($data['status'] == 'confirmed') {
                $this->calculateProductsQuantity($sale);
            }
            DB::commit();
            return $sale;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    // update customer transaction 
    private function updateCustomerTransaction(Sale $sale, array $data)
    {
        $oldTotal = $sale->total_amount;
        $newTotal = $data['total_amount'] ?? $sale->total_amount;
        $newStatus = $data['status'] ?? $sale->status;
        $oldStatus = $sale->status;
        $isStatusChanged = $oldStatus != $newStatus;
        $isTotalChanged = $oldTotal != $newTotal;

        if ($isStatusChanged && $oldStatus == 'confirmed') {
            $customerTransaction = $sale->customerTransaction;
            $this->destroyCustomerTransaction($sale);
        } else if ($isStatusChanged && $newStatus == 'confirmed') {
            $this->storeCustomerTransaction($sale);
        } else if ($isTotalChanged && $isTotalChanged && $newStatus == 'confirmed') {
            $customerTransaction = $sale->customerTransaction;
            $this->customerTransactionRepository->update($customerTransaction, [
                'amount' => $newTotal,
            ]);
        }
    }

    // store customer transaction
    private function storeCustomerTransaction(Sale $sale)
    {
        DB::beginTransaction();
        try {
            $customerTransaction = $this->customerTransactionRepository->store([
                'user_id' => $sale->user_id,
                'customer_id' => $sale->customer_id,
                'amount' => $sale->total_amount,
                'type' => 'debit',
                'note' => 'sale ID: ' . $sale->id . ' - customer name: ' . $sale->customer->name,
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

    private function destroyCustomerTransaction(Sale $sale)
    {
        DB::beginTransaction();
        try {
            $customerTransaction = $sale->customerTransaction;
            $this->customerTransactionRepository->destroy($customerTransaction);
            $sale->update([
                'customer_transaction_id' => null,
            ]);
            DB::commit();
            return $sale;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
