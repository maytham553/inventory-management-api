<?php

namespace App\Http\Controllers;

use App\Http\Repositories\SupplierTransactionRepository;
use Illuminate\Http\Request;

class SupplierTransactionController extends Controller
{
    private SupplierTransactionRepository $supplierTransactionRepository;

    public function __construct(SupplierTransactionRepository $supplierTransactionRepository)
    {
        $this->supplierTransactionRepository = $supplierTransactionRepository;
    }

    public function index()
    {
        try {
            $supplierTransactions = $this->supplierTransactionRepository->index();
            return response()->success($supplierTransactions, 'Supplier Transactions retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function indexBySupplier($id)
    {
        try {
            $supplierTransactions = $this->supplierTransactionRepository->indexBySupplier($id);
            return response()->success($supplierTransactions, 'Supplier Transactions retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|max:9223372036854775807|min:-9223372036854775808',
            'type' => 'required|in:credit,debit',
            'note' => 'nullable|string',
        ]);
        $data['user_id'] = auth()->user()->id;
        try {
            $supplierTransaction = $this->supplierTransactionRepository->store($data);
            return response()->success($supplierTransaction, 'Supplier Transaction created successfully', 201);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function show($id)
    {
        try {
            $supplierTransaction = $this->supplierTransactionRepository->find($id);
            return response()->success($supplierTransaction, 'Supplier Transaction retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'amount' => 'nullable|numeric|max:9223372036854775807|min:-9223372036854775808',
            'type' => 'nullable|in:credit,debit',
            'note' => 'nullable|string',
        ]);
        $data['user_id'] = auth()->user()->id;
        try {
            $supplierTransaction = $this->supplierTransactionRepository->find($id);
            $supplierTransaction = $this->supplierTransactionRepository->update($supplierTransaction, $data);
            return response()->success($supplierTransaction, 'Supplier Transaction updated successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function destroy($id)
    {
        try {
            $supplierTransaction = $this->supplierTransactionRepository->find($id);
            $this->supplierTransactionRepository->destroy($supplierTransaction);
            return response()->success(null, 'Supplier Transaction deleted successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
