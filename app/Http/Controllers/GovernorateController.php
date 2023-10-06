<?php

namespace App\Http\Controllers;

use App\Models\Governorate;

class GovernorateController extends Controller
{
    // index  all governorates
    public function index()
    {
        try {
            $governorates = Governorate::all();
            return response()->success($governorates, 'Governorates retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}