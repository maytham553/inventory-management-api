<?php

namespace App\Http\Controllers;

use App\Http\Repositories\ExpenseRepository;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    private ExpenseRepository $expenseRepository;

    public function __construct(ExpenseRepository $expenseRepository)
    {
        $this->expenseRepository = $expenseRepository;
    }

    public function index()
    {
        $search = request()->search;
        try {
            $expenses = $this->expenseRepository->index($search);
            return response()->success($expenses, 'Expenses retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function indexByUser($userId)
    {
        try {
            $expenses = $this->expenseRepository->indexByUser($userId);
            return response()->success($expenses, 'Expenses retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function show($id)
    {
        try {
            $expense = $this->expenseRepository->find($id);
            return response()->success($expense, 'Expense retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string',
                'description' => 'nullable|string',
                'amount' => 'required|numeric|max:9223372036854775807|min:-9223372036854775808',
            ]);
            $data['user_id'] = auth()->user()->id;
            $expense = $this->expenseRepository->store($data);
            return response()->success($expense, 'Expense created successfully', 201);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(),  $th->getCode() ?: 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'nullable|string',
                'description' => 'nullable|string',
                'amount' => 'nullable|numeric|max:9223372036854775807|min:-9223372036854775808',
            ]);
            $expense = $this->expenseRepository->find($request->id);
            $expense = $this->expenseRepository->update($expense, $data);
            return response()->success($expense, 'Expense updated successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(),  $th->getCode() ?: 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $expense = $this->expenseRepository->find($request->id);
            $expense = $this->expenseRepository->destroy($expense);
            return response()->success($expense, 'Expense deleted successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(),  $th->getCode() ?: 500);
        }
    }
}
