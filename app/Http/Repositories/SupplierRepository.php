<?php

namespace App\Http\Repositories;

use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SupplierRepository
{
    private Supplier $supplier;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function index($search)
    {
        if ($search) {
            return $this->supplier::where('id', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%")
                ->orWhere('phone', 'like', "%$search%")
                ->orWhereHas('governorate', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                })
                ->orderByRaw("CASE WHEN id LIKE '%$search%' THEN 1 WHEN name LIKE '%$search%' THEN 2 WHEN phone LIKE '%$search%' THEN 3 WHEN address LIKE '%$search%' THEN 4 ELSE 5 END")
                ->paginate(15);
        }
        return $this->supplier::orderBy('id', 'desc')->paginate(15);
    }


    public function supplierTransactions($id, $from = null, $to = null)
    {
        $supplier = Supplier::findOrFail($id);

        $query = $supplier->supplierTransactions()->orderBy('created_at', 'desc');

        if ($from !== null) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $from)->startOfDay();
            $query->where('created_at', '>=', $fromDate);
        }

        if ($to !== null) {
            $toDate = Carbon::createFromFormat('Y-m-d', $to)->endOfDay();
            $query->where('created_at', '<=', $toDate);
        }
        return $query->paginate(200);
    }


    public function indexByGovernorate($id)
    {
        return $this->supplier::where('governorate_id', $id)->orderBy('id', 'desc')->paginate(15);
    }


    public function find($id)
    {
        return $this->supplier::findOrFail($id);
    }

    public function store(array $data)
    {
        return $this->supplier::create($data);
    }

    public function update(Supplier $supplier, array $data)
    {
        return $supplier->update($data);
    }

    public function updateBalance(Supplier $supplier, $amount, $type)
    {
        if ($type == 'credit') {
            $supplier->balance += $amount;
        } else {
            $supplier->balance -= $amount;
        }
        return $supplier->save();
    }

    public function reCalculateBalance(Supplier $supplier)
    {
        $supplier->balance = $supplier->supplierTransactions->sum(function ($transaction) {
            return $transaction->type == 'credit' ? $transaction->amount : -$transaction->amount;
        });
        return $supplier->save();
    }

    public function destroy(Supplier $supplier)
    {
        return $supplier->delete();
    }
}
