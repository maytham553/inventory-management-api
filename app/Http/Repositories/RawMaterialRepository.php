<?php

namespace App\Http\Repositories;

use App\Exceptions\RecordNotFoundException;
use App\Models\RawMaterial;

class RawMaterialRepository
{
    private RawMaterial $rawMaterial;

    public function __construct(RawMaterial $rawMaterial)
    {
        $this->rawMaterial = $rawMaterial;
    }

    public function index()
    {
        return $this->rawMaterial::orderBy('id', 'desc')->paginate(15);
    }

    public function find($id)
    {
        $rawMaterial = $this->rawMaterial::find($id);

        if (! $rawMaterial) {
            throw new RecordNotFoundException('Raw material not found');
        }

        return $rawMaterial;
    }

    public function store(array $data)
    {
        return $this->rawMaterial::create($data);
    }

    public function update(RawMaterial $rawMaterial, array $data)
    {
        return $rawMaterial->update($data);
    }

    public function updateQuantity(RawMaterial $rawMaterial, $quantity)
    {
        $rawMaterial->quantity += $quantity;
        return $rawMaterial->save();
    }

    public function destroy(RawMaterial $rawMaterial)
    {
        return $rawMaterial->delete();
    }
}
