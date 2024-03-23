<?php

namespace App\Http\Controllers;

use App\Http\Repositories\ProductRepository;
use App\Http\Repositories\SaleRepository;
use App\Models\Product;
use Illuminate\Http\Request;

class   SaleController extends Controller
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

    public function indexByDateWithProductsAndCustomer(Request $request)
    {
        $data = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);
        $from = $data['from'] ?? null;
        $to = $data['to'] ?? null;

        try {
            $sales = $this->saleRepository->indexByDateWithProductsAndCustomer($from, $to);
            $formattedSales = [];
            foreach ($sales as $sale) {
                $formattedSale = [
                    'total_amount' => $sale->total_amount,
                    'previous_balance' => $sale->previous_balance,
                    'driver_name' => $sale->driver_name,
                    'updated_at' => $sale->updated_at,
                    'customer' => [
                        'id' => $sale->customer->id,
                        'name' => $sale->customer->name,
                        'phone' => $sale->customer->phone,
                    ],
                    'products' => $sale->products->map(function ($product) {
                        return [
                            'name' => $product->name,
                            'quantity' => $product->pivot->quantity,
                            'unit_price' => $product->pivot->unit_price,
                            'total_amount' => $product->pivot->total,
                        ];
                    }),
                ];
                $formattedSales[] = $formattedSale;
            }
            return response()->success($formattedSales, 'Sales retrieved successfully', 200);
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

    public function updateSaleProductsCostAndProfit()
    {
        $sales = $this->saleRepository->index();
        $products = Product::all();
        foreach ($sales as $sale) {
            foreach ($sale->products as $product) {
                $product = $products->find($product->id);
                $sale->products()->updateExistingPivot($product->id, [
                    'cost' => $product->cost,
                ]);
            }
        }
        $this->updateProfit();
        return response()->success($sales, 'Sales products cost and profit updated successfully', 200);
    }

    // recaclulate the profit of every sale
    public function updateProfit()
    {
        // get all sales
        $sales = $this->saleRepository->index();
        foreach ($sales as $sale) {
            // caclulate the profit of sale 
            $profit = 0;
            foreach ($sale->products as $product) {
                $profit += ($product->pivot->total - ($product->pivot->cost * $product->pivot->quantity));
            }
            $sale->update([
                'profit' => $profit,
            ]);
        }
        return response()->success($sales, 'Sales profit recalculated successfully', 200);
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
            'products.*.product_id' => 'required|distinct|exists:products,id',
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
