<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
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

        //User and Auth
        Route::get('users', [UserController::class, 'index']);
        Route::post('users', [UserController::class, 'store']);
        Route::get('users/currentUser', [UserController::class, 'showUser']);
        Route::get('users/{id}', [UserController::class, 'show']);
        Route::put('users/{id}', [UserController::class, 'update']);
        Route::delete('users/{id}', [UserController::class, 'destroy']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout/all', [AuthController::class, 'logoutFromAllDevices']);

        // suppliers 
        Route::get('suppliers', [SupplierController::class, 'index']);
        Route::get('suppliers/governorate/{id}', [SupplierController::class, 'indexByGovernorate']);
        Route::post('suppliers', [SupplierController::class, 'store']);
        Route::get('suppliers/{id}', [SupplierController::class, 'show']);
        Route::put('suppliers/{id}', [SupplierController::class, 'update']);
        Route::delete('suppliers/{id}', [SupplierController::class, 'destroy']);
        
    }); 