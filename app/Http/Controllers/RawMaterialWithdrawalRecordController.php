<?php

namespace App\Http\Controllers;

use App\Http\Repositories\rawMaterialWithdrawalRecordRepository;
use Illuminate\Http\Request;

class RawMaterialWithdrawalRecordController extends Controller
{
    private RawMaterialWithdrawalRecordRepository $rawMaterialWithdrawalRecordRepository;

    public function __construct(RawMaterialWithdrawalRecordRepository $rawMaterialWithdrawalRecordRepository)
    {
        $this->rawMaterialWithdrawalRecordRepository = $rawMaterialWithdrawalRecordRepository;
    }

    public function index()
    {
        try {
            $rawMaterialWithdrawalRecords = $this->rawMaterialWithdrawalRecordRepository->index();
            return response()->success($rawMaterialWithdrawalRecords, 'Raw Material Withdrawal Records retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function indexByRawMaterial($rawMaterialId)
    {
        try {
            $rawMaterialWithdrawalRecords = $this->rawMaterialWithdrawalRecordRepository->indexByRawMaterial($rawMaterialId);
            return response()->success($rawMaterialWithdrawalRecords, 'Raw Material Withdrawal Records retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function find($id)
    {
        try {
            $rawMaterialWithdrawalRecord = $this->rawMaterialWithdrawalRecordRepository->find($id);
            return response()->success($rawMaterialWithdrawalRecord, 'Raw Material Withdrawal Record retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'raw_material_id' => 'required|exists:raw_materials,id',
                'quantity' => 'required|numeric|min:1',
                'note' => 'nullable|string',
            ]);
            $data['user_id'] = auth()->user()->id;
            $rawMaterialWithdrawalRecord = $this->rawMaterialWithdrawalRecordRepository->store($data);
            return response()->success($rawMaterialWithdrawalRecord, 'Raw Material Withdrawal Record created successfully', 201);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(),  $th->getCode() ?: 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $data = $request->validate([
                'raw_material_id' => 'nullable|exists:raw_materials,id',
                'user_id' => 'nullable|exists:users,id',
                'quantity' => 'nullable|numeric|min:1',
                'note' => 'nullable|string',
            ]);
            $data['user_id'] = auth()->user()->id;
            $rawMaterialWithdrawalRecord = $this->rawMaterialWithdrawalRecordRepository->find($request->id);
            $rawMaterialWithdrawalRecord = $this->rawMaterialWithdrawalRecordRepository->update($rawMaterialWithdrawalRecord, $data);
            return response()->success($rawMaterialWithdrawalRecord, 'Raw Material Withdrawal Record updated successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(),  $th->getCode() ?: 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $rawMaterialWithdrawalRecord = $this->rawMaterialWithdrawalRecordRepository->find($request->id);
            $this->rawMaterialWithdrawalRecordRepository->destroy($rawMaterialWithdrawalRecord);
            return response()->success(null, 'Raw Material Withdrawal Record deleted successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(),  $th->getCode() ?: 500);
        }
    }
}
