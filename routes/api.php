<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerTransactionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierTransactionController;
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
Route::post('login', [AuthController::class, 'login']);

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
        // Route::put('suppliers/{id}/re-calculate-balance', [SupplierController::class, 'reCalculateBalance']);
        Route::delete('suppliers/{id}', [SupplierController::class, 'destroy']);

        // customers
        Route::get('customers', [CustomerController::class, 'index']);
        Route::get('customers/governorate/{id}', [CustomerController::class, 'indexByGovernorate']);
        Route::post('customers', [CustomerController::class, 'store']);
        Route::get('customers/{id}', [CustomerController::class, 'show']);
        Route::put('customers/{id}', [CustomerController::class, 'update']);
        // Route::put('customers/{id}/re-calculate-balance', [CustomerController::class, 'reCalculateBalance']);
        Route::delete('customers/{id}', [CustomerController::class, 'destroy']);

        // supplier transactions
        Route::get('supplier-transactions', [SupplierTransactionController::class, 'index']);
        Route::post('supplier-transactions', [SupplierTransactionController::class, 'store']);
        Route::get('supplier-transactions/{id}', [SupplierTransactionController::class, 'show']);
        Route::put('supplier-transactions/{id}', [SupplierTransactionController::class, 'update']);
        Route::delete('supplier-transactions/{id}', [SupplierTransactionController::class, 'destroy']);

        // customer transactions
        Route::get('customer-transactions', [CustomerTransactionController::class, 'index']);
        Route::get('customer-transactions/customer/{id}', [CustomerTransactionController::class, 'indexByCustomer']);
        Route::get('customer-transactions/{id}', [CustomerTransactionController::class, 'show']);
        Route::post('customer-transactions', [CustomerTransactionController::class, 'store']);
        Route::put('customer-transactions/{id}', [CustomerTransactionController::class, 'update']);
        Route::delete('customer-transactions/{id}', [CustomerTransactionController::class, 'destroy']);
    }
);
