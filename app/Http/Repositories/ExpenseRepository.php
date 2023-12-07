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
    

    public function index($search)
    {
        if ($search) {
            return $this->expense::where('id', 'like', "%$search%")
                ->orWhere('title', 'like', "%$search%")
                ->orderByRaw("CASE WHEN id LIKE '%$search%' THEN 1 WHEN title LIKE '%$search%' THEN 2 ELSE 3 END")
                ->paginate(15);
        }
        return $this->expense::orderBy('id', 'desc')->paginate(15);
    }
  
    public function indexByDate($from = null, $to = null)
    {
        $query = $this->expense::query();

        if ($from !== null) {
            $query->where('updated_at', '>=', $from);
        }

        if ($to !== null) {
            $query->where('updated_at', '<=', $to);
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
