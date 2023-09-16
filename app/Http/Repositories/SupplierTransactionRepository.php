<?php

namespace App\Http\Repositories;

use App\Models\SupplierTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierTransactionRepository
{
    private SupplierTransaction $supplierTransaction;
    private SupplierRepository $supplierRepository;

    public function __construct(SupplierTransaction $supplierTransaction, SupplierRepository $supplierRepository)
    {
        $this->supplierTransaction = $supplierTransaction;
        $this->supplierRepository = $supplierRepository;
    }

    public function index()
    {
        return $this->supplierTransaction::with('supplier')->paginate(15);
    }

    public function find($id)
    {
        return $this->supplierTransaction::with('supplier')->findOrFail($id);
    }


    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $supplierTransaction = $this->supplierTransaction::create($data);
            $supplier = $supplierTransaction->supplier;
            $this->supplierRepository->updateBalance($supplier, $data['amount'], $data['type']);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $supplierTransaction;
    }

    public function update(SupplierTransaction $supplierTransaction, array $data)
    {
        DB::beginTransaction();
        try {
            $oldAmount = $supplierTransaction->amount;
            $oldType = $supplierTransaction->type;

            // update supplier transaction
            $supplierTransaction->update($data);

            $supplier = $supplierTransaction->supplier;
            $newType = $data['type'] ?? $supplierTransaction->type;
            $reverseType = $oldType == $newType ? ($oldType == 'credit' ? 'debit' : 'credit') : $newType;
            $newAmount = $data['amount'] ?? $supplierTransaction->amount;

            // delete old amount from supplier balance
            $this->supplierRepository->updateBalance(
                $supplier,
                $oldAmount,
                $reverseType
            );

            // add new amount to supplier balance
            $this->supplierRepository->updateBalance(
                $supplier,
                $newAmount,
                $newType
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $supplierTransaction;
    }

    public function destroy(SupplierTransaction $supplierTransaction)
    {
        DB::beginTransaction();
        try {
            $supplier = $supplierTransaction->supplier;
            $reverseType = $supplierTransaction->type == 'credit' ? 'debit' : 'credit';
            $this->supplierRepository->updateBalance(
                $supplier,
                $supplierTransaction->amount,
                $reverseType
            );
            $supplierTransaction->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
