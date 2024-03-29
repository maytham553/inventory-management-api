<?php

namespace App\Http\Controllers;

use App\Http\Repositories\ProductRepository;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }


    public function index()
    {
        try {
            if (auth()->user()->type === 'SuperAdmin') {
                $products = $this->productRepository->index();
            } else {
                $products = $this->productRepository->indexWithoutCost();
            }
            return response()->success($products, 'Products retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|unique:products,code',
            'barcode' => 'required|string|unique:products,barcode',
            'quantity' => 'required|numeric|max:2147483647|min:-2147483648',
            'price' => 'required|numeric|max:9223372036854775807|min:-9223372036854775808',
            'cost' => 'required|numeric|max:9223372036854775807|min:-9223372036854775808',
            'note' => 'nullable|string',
        ]);
        try {
            $product = $this->productRepository->store($data);
            return response()->success($product, 'Product created successfully', 201);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function show($id)
    {
        try {
            $product = $this->productRepository->find($id);
            return response()->success($product, 'Product retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:50',
            'code' => 'nullable|string|unique:products,code,' . $id,
            'barcode' => 'nullable|string|unique:products,barcode,' . $id,
            'quantity' => 'nullable|numeric|max:2147483647|min:-2147483648',
            'price' => 'nullable|numeric|max:9223372036854775807|min:-9223372036854775808',
            'cost' => 'nullable|numeric|max:9223372036854775807|min:-9223372036854775808',
            'note' => 'nullable|string',
        ]);
        try {
            $product = $this->productRepository->find($id);
            $this->productRepository->update($product, $data);
            return response()->success($product, 'Product updated successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = $this->productRepository->find($id);
            $this->productRepository->destroy($product);
            return response()->success(null, 'Product deleted successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
