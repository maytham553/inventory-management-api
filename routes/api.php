<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// login 
Route::post('login' , [AuthController::class , 'login']);

Route::group(
    [
        'prefix' => 'auth',
        'middleware' => 'auth:sanctum',
    ],
    //auth/sendOtp
    function () {
        Route::get('users', [UserController::class, 'index']);
        Route::post('users', [UserController::class, 'store']);
        Route::get('users/currentUser', [UserController::class, 'showUser']);
        Route::get('users/{id}', [UserController::class, 'show']);
        Route::put('users/{id}', [UserController::class, 'update']);
        Route::delete('users/{id}', [UserController::class, 'destroy']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout/all', [AuthController::class, 'logoutFromAllDevices']);
    }); 