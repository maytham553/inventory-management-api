<?php

namespace App\Http\Repositories;

use App\Models\Customer;

class CustomerRepository
{

    private Customer $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function index($search)
    {
        if ($search) {
            return $this->customer::where('id', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%")
                ->orWhere('phone', 'like', "%$search%")
                ->orWhereHas('governorate', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                })
                ->orderByRaw("CASE WHEN id LIKE '%$search%' THEN 1 WHEN name LIKE '%$search%' THEN 2 WHEN phone LIKE '%$search%' THEN 3 WHEN address LIKE '%$search%' THEN 4 ELSE 5 END")
                ->paginate(15);
        }
        return $this->customer::orderBy('id', 'desc')->paginate(15);
    }

    public function customerTransactions($id, $from = null, $to = null)
    {
        $customer = Customer::findOrFail($id);

        $query = $customer->customerTransactions()->orderBy('created_at', 'desc');

        if ($from !== null) {
            $query->where('created_at', '>=', $from);
        }

        if ($to !== null) {
            $query->where('created_at', '<=', $to);
        }
        return $query->paginate(200);
    }


    public function indexByGovernorate($id)
    {
        return $this->customer::where('governorate_id', $id)->orderBy('id', 'desc')->paginate(15);
    }

    public function getSales(Customer $customer, $search)
    {
        return $customer->sales()->with('products')->where('id', 'like', "%$search%")->orderBy('id', 'desc')->paginate(15);
    }

    public function find($id)
    {
        return $this->customer::findOrFail($id);
    }

    public function store(array $data)
    {
        return $this->customer::create($data);
    }

    public function update(Customer $customer, array $data)
    {
        return $customer->update($data);
    }


    public function updateBalance(Customer $customer, $amount, $type)
    {
        if ($type == 'credit') {
            $customer->balance += $amount;
        } else {
            $customer->balance -= $amount;
        }
        return $customer->save();
    }

    public function reCalculateBalance(Customer $customer)
    {
        $customer->balance = $customer->customerTransactions->sum(function ($transaction) {
            return $transaction->type == 'credit' ? $transaction->amount : -$transaction->amount;
        });
        return $customer->save();
    }

    public function destroy(Customer $customer)
    {
        return $customer->delete();
    }
}
