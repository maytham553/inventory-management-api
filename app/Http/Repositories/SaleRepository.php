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
        return $this->sale::with('customer')->orderBy('id', 'desc')->paginate(15);
    }

    public function find($id)
    {
        return $this->sale::with('customer', 'products')->findOrFail($id);
    }

    // store 
    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $sale = $this->sale::create($data);
            $sale->products()->sync($data['products'] ?? []);
            if ($data['status'] == 'confirmed') {
                $this->storeCustomerTransaction($sale);
                $this->calculateProductsQuantity($sale);
            }
            DB::commit();
            return $sale;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
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
