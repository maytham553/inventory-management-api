<?php

namespace App\Http\Repositories;

use App\Models\Supplier;
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


    // public function findWithPurchases($id, $search)
    // {
    //     if ($search) {
    //         return $this->supplier::with(['purchases' => function ($query) {
    //             $query->orderBy('id', 'desc')->paginate(15);
    //         }, 'purchases.rawMaterials'])->whereHas('purchase', function ($query) use ($search) {
    //             $query->where('id', 'like', "%$search%");
    //         })->findOrFail($id);
    //     }
    //     return $this->supplier::with(['purchases' => function ($query) {
    //         $query->orderBy('id', 'desc')->paginate(15);
    //     }, 'purchases.rawMaterials'])->findOrFail($id);
    // }


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
