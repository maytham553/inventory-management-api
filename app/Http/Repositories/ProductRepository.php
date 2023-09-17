<?php

namespace App\Http\Repositories;

use App\Models\Product;

class ProductRepository
{
    private Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function index()
    {
        return $this->product::paginate(15);
    }

    public function find($id)
    {
        return $this->product::findOrFail($id);
    }

    public function store(array $data)
    {
        return $this->product::create($data);
    }

    public function update(Product $product, array $data)
    {
        return $product->update($data);
    }

    public function updateQuantity(Product $product, $quantity)
    {
        $product->quantity += $quantity;
        return $product->save();
    }

    public function destroy(Product $product)
    {
        return $product->delete();
    }
}
