<?php

namespace App\Http\Controllers;

use App\Http\Repositories\SupplierRepository;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    private SupplierRepository $supplierRepository;

    public function __construct(supplierRepository $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }

    public function index()
    {
        try {
            $suppliers = $this->supplierRepository->index();
            return response()->success($suppliers, 'Suppliers retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function indexByGovernorate($id)
    {
        try {
            $suppliers = $this->supplierRepository->indexByGovernorate($id);
            return response()->success($suppliers, 'Suppliers retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'governorate_id' => 'required|exists:governorates,id',
            'balance' => 'required|numeric',
            'note' => 'nullable|string',
        ]);
        try {
            $supplier = $this->supplierRepository->store($request->all());
            return response()->success($supplier, 'Supplier created successfully', 201);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function show($id)
    {
        try {
            $supplier = $this->supplierRepository->find($id);
            return response()->success($supplier, 'Supplier retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'governorate_id' => 'nullable|exists:governorates,id',
            'balance' => 'nullable|numeric',
            'note' => 'nullable|string',
        ]);
        try {
            $supplier = $this->supplierRepository->find($id);
            $this->supplierRepository->update($supplier, $data);
            return response()->success($supplier, 'Supplier updated successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function reCalculateBalance($id)
    {
        try {
            $supplier = $this->supplierRepository->find($id);
            $this->supplierRepository->reCalculateBalance($supplier);
            return response()->success($supplier, 'Supplier balance recalculated successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function destroy($id)
    {
        try {
            $supplier = $this->supplierRepository->find($id);
            $this->supplierRepository->destroy($supplier);
            return response()->success($supplier, 'Supplier deleted successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
