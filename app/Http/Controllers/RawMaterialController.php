<?php

namespace App\Http\Controllers;

use App\Http\Repositories\RawMaterialRepository;
use Illuminate\Http\Request;

class RawMaterialController extends Controller
{
    private RawMaterialRepository $rawMaterialRepository;

    public function __construct(RawMaterialRepository $rawMaterialRepository)
    {
        $this->rawMaterialRepository = $rawMaterialRepository;
    }

    public function index()
    {
        try {
            $rawMaterials = $this->rawMaterialRepository->index();
            return response()->success($rawMaterials, 'Raw materials retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|unique:raw_materials,code',
            'barcode' => 'required|string|unique:raw_materials,barcode',
            'quantity' => 'required|numeric',
            'cost' => 'required|numeric',
            'note' => 'nullable|string',
        ]);
        try {
            $rawMaterial = $this->rawMaterialRepository->store($data);
            return response()->success($rawMaterial, 'Raw material created successfully', 201);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function show($id)
    {
        try {
            $rawMaterial = $this->rawMaterialRepository->find($id);
            return response()->success($rawMaterial, 'Raw material retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:50',
            'code' => 'nullable|string|unique:raw_materials,code,' . $id,
            'barcode' => 'nullable|string|unique:raw_materials,barcode,' . $id,
            'quantity' => 'required|numeric',
            'cost' => 'nullable|numeric',
            'note' => 'nullable|string',
        ]);
        try {
            $rawMaterial = $this->rawMaterialRepository->find($id);
            $this->rawMaterialRepository->update($rawMaterial, $data);
            return response()->success($rawMaterial, 'Raw material updated successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function destroy($id)
    {
        try {
            $rawMaterial = $this->rawMaterialRepository->find($id);
            $this->rawMaterialRepository->destroy($rawMaterial);
            return response()->success(null, 'Raw material deleted successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
