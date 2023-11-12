<?php

namespace App\Http\Repositories;

use App\Models\Purchase;
use App\Models\SupplierTransaction;
use Illuminate\Support\Facades\DB;

class PurchaseRepository
{
    private Purchase $purchase;
    private SupplierTransactionRepository $supplierTransactionRepository;
    public function __construct(Purchase $purchase, SupplierTransactionRepository $supplierTransactionRepository)
    {
        $this->purchase = $purchase;
        $this->supplierTransactionRepository = $supplierTransactionRepository;
    }


    public function index()
    {
        return $this->purchase::with('supplier')->orderBy('id', 'desc')->paginate(15);
    }


    public function indexBySupplier($supplierId, $search)
    {
        if ($search) {
            return $this->purchase::with('supplier', 'rawMaterials')
                ->where('supplier_id', $supplierId)
                ->where('id', 'like', "%$search%")
                ->paginate(15);
        }
        return $this->purchase::with('supplier', 'rawMaterials')
        ->where('supplier_id', $supplierId)
        ->orderBy('id', 'desc')->paginate(15);
    }

    public function find($id)
    {
        return $this->purchase::with('supplier', 'rawMaterials')->findOrFail($id);
    }


    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $rawMaterials = $data['raw_materials'];
            $purchase = $this->purchase::create($data);
            $purchase->rawMaterials()->sync($data['raw_materials'] ?? []);
            if ($data['status'] == 'confirmed') {
                $this->storeSupplierTransaction($purchase);
                $this->calculateRawMaterialsQuantity($purchase);
            }
            DB::commit();
            return $purchase;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function update(Purchase $purchase, array $data)
    {
        DB::beginTransaction();
        try {
            $this->updateSupplierTransaction($purchase, $data);
            $purchase->rawMaterials()->sync($data['raw_materials'] ?? []);
            $purchase->update($data);
            $purchase->refresh();
            $this->calculateRawMaterialsQuantity($purchase);
            DB::commit();
            return $purchase;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    private function updateSupplierTransaction(Purchase $purchase, array $data)
    {
        $oldTotal = $purchase->total_amount;
        $newTotal = $data['total_amount'] ?? $purchase->total_amount;
        $newStatus = $data['status'] ?? $purchase->status;
        $oldStatus = $purchase->status;
        $isStatusChanged = $oldStatus != $newStatus;
        $isTotalChanged = $oldTotal != $newTotal;

        if ($isStatusChanged && $oldStatus == 'confirmed') {
            $supplierTransaction = $purchase->supplierTransaction;
            $this->destroySupplierTransaction($purchase);
        } else if ($isStatusChanged && $newStatus == 'confirmed') {
            $this->storeSupplierTransaction($purchase);
        } else if (!$isStatusChanged && $isTotalChanged && $newStatus == 'confirmed') {
            $supplierTransaction = $purchase->supplierTransaction;
            $this->supplierTransactionRepository->update($supplierTransaction, [
                'amount' => $newTotal,
            ]);
        }
    }

    private function storeSupplierTransaction(Purchase $purchase)
    {
        DB::beginTransaction();
        try {
            $transaction =  $this->supplierTransactionRepository->store([
                'user_id' => $purchase->user_id,
                'supplier_id' => $purchase->supplier_id,
                'amount' => $purchase->total_amount,
                'type' => 'debit',
                'note' => 'purchase ID: ' . $purchase->id . ' - supplier name: ' . $purchase->supplier->name,
            ]);
            // Store transaction id in purchase
            $purchase->update([
                'supplier_transaction_id' => $transaction->id,
            ]);
            DB::commit();
            return $purchase;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function calculateRawMaterialsQuantity(Purchase $purchase)
    {
        $rawMaterials = $purchase->rawMaterials;
        foreach ($rawMaterials as $rawMaterial) {
            $rawMaterial->quantity += $rawMaterial->pivot->quantity;
            $rawMaterial->save();
        }
    }

    // destroy supplier transaction
    private function destroySupplierTransaction(Purchase $purchase)
    {
        DB::beginTransaction();
        try {
            $supplierTransaction = $purchase->supplierTransaction;
            $this->supplierTransactionRepository->destroy($supplierTransaction);
            $purchase->update([
                'supplier_transaction_id' => null,
            ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
