<?php

namespace App\Http\Controllers;

use App\Http\Repositories\PurchaseRepository;
use App\Http\Repositories\SupplierRepository;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    private PurchaseRepository $purchaseRepository;
    private SupplierRepository $supplierRepository;

    public function __construct(PurchaseRepository $purchaseRepository, SupplierRepository $supplierRepository)
    {
        $this->purchaseRepository = $purchaseRepository;
        $this->supplierRepository = $supplierRepository;
    }

    public function index()
    {
        try {
            $purchases = $this->purchaseRepository->index();
            return response()->success($purchases, 'Purchases retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'subtotal_amount' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'discount_amount' => 'nullable|numeric',
            'discount_percentage' => 'nullable|numeric',
            'status' => 'required|in:pending,confirmed,cancelled',
            'note' => 'nullable|string',
            'raw_materials' => 'required|array',
            'raw_materials.*.raw_material_id' => 'required|exists:raw_materials,id',
            'raw_materials.*.quantity' => 'required|numeric|max:9999999999|min:1',
            'raw_materials.*.subtotal' => 'required|numeric|max:9999999999.99',
            'raw_materials.*.total' => 'required|numeric|max:9999999999.99',
            'raw_materials.*.unit_price' => 'required|numeric|max:9999999999.99',
            'raw_materials.*.discount_amount' => 'nullable|numeric|max:9999999999.99',
            'raw_materials.*.discount_percentage' => 'nullable|numeric|max:100.00',
        ]);
        $data['user_id'] = auth()->user()->id;
        try {
            $purchase = $this->purchaseRepository->store($data);
            return response()->success($purchase, 'Purchase created successfully', 201);
        } catch (\Throwable $th) {
            // should be change the request error response
            return response()->error($th->getMessage(), 400);
        }
    }

    public function show($id)
    {
        try {
            $purchase = $this->purchaseRepository->find($id);
            return response()->success($purchase, 'Purchase retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }


    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'subtotal_amount' => 'nullable|numeric',
            'total_amount' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',
            'discount_percentage' => 'nullable|numeric',
            'status' => 'nullable|in:pending,confirmed,cancelled',
            'note' => 'nullable|string',
            'raw_materials' => 'required|array',
            'raw_materials.*.id' => 'required|exists:raw_materials,id',
            'raw_materials.*.quantity' => 'required|numeric|max:9999999999|min:1',
            'raw_materials.*.subtotal' => 'required|numeric|max:9999999999.99',
            'raw_materials.*.total' => 'required|numeric|max:9999999999.99',
            'raw_materials.*.unit_price' => 'required|numeric|max:9999999999.99',
            'raw_materials.*.discount_amount' => 'required|numeric|max:9999999999.99',
            'raw_materials.*.discount_percentage' => 'required|numeric|max:100.00',
        ]);
        $data['user_id'] = auth()->user()->id;
        try {
            $purchase = $this->purchaseRepository->find($id);
            $this->purchaseRepository->update($purchase, $data);
            return response()->success($purchase, 'Purchase updated successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
