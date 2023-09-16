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

    public function index()
    {
        return $this->customer::paginate(15);
    }

    public function indexByGovernorate($id)
    {
        return $this->customer::where('governorate_id', $id)->paginate(15);
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

    // public function reCalculateBalance(Customer $customer)
    // {
    //     $customer->balance = $customer->customerTransactions->sum(function ($transaction) {
    //         return $transaction->type == 'credit' ? $transaction->amount : -$transaction->amount;
    //     });
    //     return $customer->save();
    // }

    public function destroy(Customer $customer)
    {
        return $customer->delete();
    }
}
