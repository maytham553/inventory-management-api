<?php

namespace App\Http\Repositories;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseRepository
{
    private Expense $expense;

    public function __construct(Expense $expense)
    {
        $this->expense = $expense;
    }


    public function index($search, $from = null, $to = null)
    {
        $expenses = $this->expense::query();

        if ($from !== null) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $from)->startOfDay();
            $expenses->where('updated_at', '>=', $fromDate);
        }

        if ($to !== null) {
            $toDate = Carbon::createFromFormat('Y-m-d', $to)->endOfDay();
            $expenses->where('updated_at', '<=', $toDate);
        }

        if ($search) {
            $expenses->where('id', 'like', "%$search%")
                ->orWhere('title', 'like', "%$search%")
                ->orderByRaw("CASE WHEN id LIKE '%$search%' THEN 1 WHEN title LIKE '%$search%' THEN 2 ELSE 3 END");
        }
        return $expenses->orderBy('id', 'desc')->paginate(50);
    }

    public function indexWithoutPagination($search, $from = null, $to = null)
    {
        $expenses = $this->expense::query();

        if ($from !== null) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $from)->startOfDay();
            $expenses->where('updated_at', '>=', $fromDate);
        }

        if ($to !== null) {
            $toDate = Carbon::createFromFormat('Y-m-d', $to)->endOfDay();
            $expenses->where('updated_at', '<=', $toDate);
        }

        if ($search) {
            $expenses->where('id', 'like', "%$search%")
                ->orWhere('title', 'like', "%$search%")
                ->orderByRaw("CASE WHEN id LIKE '%$search%' THEN 1 WHEN title LIKE '%$search%' THEN 2 ELSE 3 END");
        }
        return $expenses->orderBy('id', 'desc')->with('user')->get();
    }

    public function indexByDate($from = null, $to = null)
    {
        $query = $this->expense::query();

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


    public function search($search)
    {
        return $this->expense::where('id', $search)->orWhere('title', 'like', '%' . $search . '%')->orderBy('id', 'desc')->paginate(15);
    }

    public function indexByUser($userId)
    {
        return $this->expense::where('user_id', $userId)->orderBy('id', 'desc')->paginate(15);
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
