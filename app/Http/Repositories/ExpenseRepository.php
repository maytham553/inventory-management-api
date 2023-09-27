<?php

namespace App\Http\Repositories;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class ExpenseRepository
{
    private Expense $expense;

    public function __construct(Expense $expense)
    {
        $this->expense = $expense;
    }

    public function index()
    {
        return $this->expense::paginate(15);
    }

    public function indexByUser($userId)
    {
        return $this->expense::where('user_id', $userId)->paginate(15);
    }

    public function find($id)
    {
        return $this->expense::findOrFail($id);
    }

    public function store(array $data)
    {
        return $this->expense::create($data);
    }

    public function update(Expense $expense, array $data)
    {
        $expense->update($data);
        return $expense;
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return $expense;
    }
}
