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

    public function index()
    {
        return $this->supplier::paginate(15);
    }

    public function indexByGovernorate($id)
    {
        return $this->supplier::where('governorate_id', $id)->paginate(15);
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

    public function destroy(Supplier $supplier)
    {
        return $supplier->delete();
    }

}
