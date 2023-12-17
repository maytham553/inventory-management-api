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
            'driver_name' => 'nullable|string',
            'products' => 'required|array',
            'products.*.product_id' => 'required|distinct|exists:products,id',
            'products.*.quantity' => 'required|numeric|max:2147483647|min:1',
            'products.*.subtotal' => 'required|numeric|max:9223372036854775807|min:-9223372036854775808',
            'products.*.total' => 'required|numeric|max:9223372036854775807|min:-9223372036854775808',
            'products.*.unit_price' => 'required|numeric|max:9223372036854775807|min:-9223372036854775808',
            'products.*.discount_amount' => 'nullable|numeric|max:9223372036854775807|min:-9223372036854775808',
        ]);
        $data['user_id'] = auth()->user()->id;
        try {
            $sale = $this->saleRepository->store($data);
            $sale->products->makeHidden('cost');
            $sale->products->makeHidden('pivot');
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
            'subtotal_amount' => 'nullable|numeric|max:9223372036854775807|min:-9223372036854775808',
            'total_amount' => 'nullable|numeric|max:9223372036854775807|min:-9223372036854775808',
            'discount_amount' => 'nullable|numeric|max:9223372036854775807|min:-9223372036854775808',
            'status' => 'nullable|in:pending,confirmed,cancelled',
            'note' => 'nullable|string',
            'driver_name' => 'nullable|string',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|max:2147483647|min:1',
            'products.*.subtotal' => 'required|numeric|max:9223372036854775807|min:-9223372036854775808',
            'products.*.total' => 'required|numeric|max:9223372036854775807|min:-9223372036854775808',
            'products.*.unit_price' => 'required|numeric|max:9223372036854775807|min:-9223372036854775808',
            'products.*.discount_amount' => 'required|numeric|max:9223372036854775807|min:-9223372036854775808',
        ]);
        $data['user_id'] = auth()->user()->id;
        try {
            $sale = $this->saleRepository->find($id);
            if ($sale->status == 'confirmed') {
                return response()->error('Sale status is confirmed, you can not update it', 400);
            }
            $sale = $this->saleRepository->update($sale, $data);
            $sale->products->makeHidden('cost');
            $sale->products->makeHidden('pivot');
            return response()->success($sale, 'Sale updated successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
