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
            $products = $this->productRepository->index();
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
            'quantity' => 'required|numeric',
            'price' => 'required|numeric',
            'cost' => 'required|numeric',
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
            'quantity' => 'nullable|numeric',
            'price' => 'nullable|numeric',
            'cost' => 'nullable|numeric',
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
