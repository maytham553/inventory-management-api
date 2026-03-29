<?php

namespace App\Http\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    private Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function index(?string $search = null)
    {
        $query = $this->product::query()->orderBy('id', 'desc');
        $this->applySearch($query, $search);

        return $query->paginate(15);
    }

    public function indexWithoutCost(?string $search = null)
    {
        $query = $this->product::select('id', 'name', 'code', 'barcode', 'quantity', 'price', 'note')
            ->orderBy('id', 'desc');
        $this->applySearch($query, $search);

        return $query->paginate(1500);
    }

    private function applySearch($query, ?string $search): void
    {
        if (! $search) {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('id', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%");
        });
    }

    /**
     * Full product list for sale invoice line-item picker (not paginated).
     */
    public function allForSalePicker(bool $includeCost): Collection
    {
        $query = $includeCost
            ? $this->product::query()
            : $this->product::select('id', 'name', 'code', 'barcode', 'quantity', 'price', 'note');

        return $query->orderBy('id', 'desc')->get();
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
