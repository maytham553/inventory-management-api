<?php

namespace App\Http\Repositories;

use App\Models\RawMaterialWithdrawalRecord;
use Illuminate\Support\Facades\DB;

class RawMaterialWithdrawalRecordRepository 
{
    private RawMaterialWithdrawalRecord $rawMaterialWithdrawalRecord;

    public function __construct(RawMaterialWithdrawalRecord $rawMaterialWithdrawalRecord)
    {
        $this->rawMaterialWithdrawalRecord = $rawMaterialWithdrawalRecord;
    }

    public function index()
    {
        return $this->rawMaterialWithdrawalRecord::paginate(15);
    }

    public function indexByRawMaterial($rawMaterialId)
    {
        return $this->rawMaterialWithdrawalRecord::where('raw_material_id', $rawMaterialId)->paginate(15);
    }

    public function find($id)
    {
        return $this->rawMaterialWithdrawalRecord::findOrFail($id);
    }

    // store with effect in raw_materials table
    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $rawMaterialWithdrawalRecord = $this->rawMaterialWithdrawalRecord::create($data);
            $rawMaterial = $rawMaterialWithdrawalRecord->rawMaterial;
            $rawMaterial->quantity -= $rawMaterialWithdrawalRecord->quantity;
            $rawMaterial->save();
            DB::commit();
            return $rawMaterialWithdrawalRecord;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    // update with effect in raw_materials table
    public function update(RawMaterialWithdrawalRecord $rawMaterialWithdrawalRecord, array $data)
    {
        DB::beginTransaction();
        try {
            $rawMaterial = $rawMaterialWithdrawalRecord->rawMaterial;
            $rawMaterial->quantity += $rawMaterialWithdrawalRecord->quantity;
            $rawMaterial->quantity -= $data['quantity'];
            $rawMaterial->save();
            $rawMaterialWithdrawalRecord->update($data);
            DB::commit();
            return $rawMaterialWithdrawalRecord;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    // destroy with effect in raw_materials table
    public function destroy(RawMaterialWithdrawalRecord $rawMaterialWithdrawalRecord)
    {
        DB::beginTransaction();
        try {
            $rawMaterial = $rawMaterialWithdrawalRecord->rawMaterial;
            $rawMaterial->quantity += $rawMaterialWithdrawalRecord->quantity;
            $rawMaterial->save();
            $rawMaterialWithdrawalRecord->delete();
            DB::commit();
            return $rawMaterialWithdrawalRecord;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
