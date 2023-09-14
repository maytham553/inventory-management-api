<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);
        if (!Auth::attempt($data)) {
            return response()->error('Invalid credentials', 401);
        }
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->success(['user' => $user, 'token' => $token], 'User logged in successfully', 200);
    }



    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->success([], 'User logged out successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function logoutFromAllDevices(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->success([], 'User logged out successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
