<?php

namespace App\Http\Controllers;

use App\Http\Repositories\SaleRepository;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    private SaleRepository $saleRepository;

    public function __construct(SaleRepository $saleRepository)
    {
        $this->saleRepository = $saleRepository;
    }

    public function index()
    {
        try {
            $sales = $this->saleRepository->index();
            return response()->success($sales, 'Sales retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'subtotal_amount' => 'required|numeric|max:999999999999',
            'total_amount' => 'required|numeric|max:999999999999',
            'discount_amount' => 'nullable|numeric|max:9999999999',
            'status' => 'required|in:pending,confirmed,cancelled',
            'note' => 'nullable|string',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|max:999999999999',
            'products.*.subtotal' => 'required|numeric|max:999999999999',
            'products.*.total' => 'required|numeric|max:999999999999',
            'products.*.unit_price' => 'required|numeric|max:999999999999',
            'products.*.discount_amount' => 'nullable|numeric|max:9999999999',
        ]);
        $data['user_id'] = auth()->user()->id;
        try {
            $sale = $this->saleRepository->store($data);
            return response()->success($sale, 'Sale created successfully', 201);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), 400);
        }
    }

    public function show($id)
    {
        try {
            $sale = $this->saleRepository->find($id);
            return response()->success($sale, 'Sale retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'subtotal_amount' => 'nullable|numeric|max:999999999999',
            'total_amount' => 'nullable|numeric|max:999999999999',
            'discount_amount' => 'nullable|numeric|max:9999999999',
            'status' => 'nullable|in:pending,confirmed,cancelled',
            'note' => 'nullable|string',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|max:999999999999',
            'products.*.subtotal' => 'required|numeric|max:999999999999',
            'products.*.total' => 'required|numeric|max:999999999999',
            'products.*.unit_price' => 'required|numeric|max:999999999999',
            'products.*.discount_amount' => 'required|numeric|max:9999999999',
        ]);
        $data['user_id'] = auth()->user()->id;
        try {
            $sale = $this->saleRepository->find($id);
            $sale = $this->saleRepository->update($sale, $data);
            return response()->success($sale, 'Sale updated successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
