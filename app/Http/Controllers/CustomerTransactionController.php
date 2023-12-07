<?php

namespace App\Http\Controllers;

use App\Http\Repositories\CustomerTransactionRepository;
use Illuminate\Http\Request;

class CustomerTransactionController extends Controller
{
    private CustomerTransactionRepository $customerTransactionRepository;

    public function __construct(CustomerTransactionRepository $customerTransactionRepository)
    {
        $this->customerTransactionRepository = $customerTransactionRepository;
    }

    public function index()
    {
        try {
            $customerTransactions = $this->customerTransactionRepository->index();
            return response()->success($customerTransactions, 'Customer Transactions retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }


    public function indexByCustomer($id)
    {
        try {
            $customerTransactions = $this->customerTransactionRepository->indexByCustomer($id);
            return response()->success($customerTransactions, 'Customer Transactions retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|max:9223372036854775807|min:-9223372036854775808',
            'type' => 'required|in:credit,debit',
            'note' => 'nullable|string',
        ]);
        $data['user_id'] = auth()->user()->id;
        try {
            $customerTransaction = $this->customerTransactionRepository->store($data);
            return response()->success($customerTransaction, 'Customer Transaction created successfully', 201);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function show($id)
    {
        try {
            $customerTransaction = $this->customerTransactionRepository->find($id);
            return response()->success($customerTransaction, 'Customer Transaction retrieved successfully', 200);
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
            $customerTransaction = $this->customerTransactionRepository->find($id);
            $customerTransaction = $this->customerTransactionRepository->update($customerTransaction, $data);
            return response()->success($customerTransaction, 'Customer Transaction updated successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function destroy($id)
    {
        try {
            $customerTransaction = $this->customerTransactionRepository->find($id);
            $customerTransaction->delete();
            return response()->success(null, 'Customer Transaction deleted successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
