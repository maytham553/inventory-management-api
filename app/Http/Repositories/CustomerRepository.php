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

    public function destroy(Customer $customer)
    {
        return $customer->delete();
    }
}
