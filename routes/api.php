<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerTransactionController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GovernorateController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\RawMaterialWithdrawalRecordController;
use App\Http\Controllers\SaleController;
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

// test api 
Route::get('test', function () {
    return response()->success('test', 'test', 200);
});

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
        Route::put('suppliers/{id}/re-calculate-balance', [SupplierController::class, 'reCalculateBalance']);
        Route::delete('suppliers/{id}', [SupplierController::class, 'destroy']);

        // customers
        Route::get('customers', [CustomerController::class, 'index']);
        Route::get('customers/governorate/{id}', [CustomerController::class, 'indexByGovernorate']);
        Route::post('customers', [CustomerController::class, 'store']);
        Route::get('customers/{id}', [CustomerController::class, 'show']);
        Route::put('customers/{id}', [CustomerController::class, 'update']);
        Route::put('customers/{id}/re-calculate-balance', [CustomerController::class, 'reCalculateBalance']);
        Route::delete('customers/{id}', [CustomerController::class, 'destroy']);
        Route::get('customers/{id}/sales', [CustomerController::class, 'getSales']);

        // supplier transactions
        Route::get('supplier-transactions', [SupplierTransactionController::class, 'index']);
        Route::post('supplier-transactions', [SupplierTransactionController::class, 'store']);
        Route::get('supplier-transactions/{id}', [SupplierTransactionController::class, 'show']);
        Route::put('supplier-transactions/{id}', [SupplierTransactionController::class, 'update']);
        Route::delete('supplier-transactions/{id}', [SupplierTransactionController::class, 'destroy']);
        Route::get('supplier-transactions/supplier/{id}', [SupplierTransactionController::class, 'indexBySupplier']);

        // customer transactions
        Route::get('customer-transactions', [CustomerTransactionController::class, 'index']);
        Route::get('customer-transactions/customer/{id}', [CustomerTransactionController::class, 'indexByCustomer']);
        Route::get('customer-transactions/{id}', [CustomerTransactionController::class, 'show']);
        Route::post('customer-transactions', [CustomerTransactionController::class, 'store']);
        Route::put('customer-transactions/{id}', [CustomerTransactionController::class, 'update']);
        Route::delete('customer-transactions/{id}', [CustomerTransactionController::class, 'destroy']);

        // products
        Route::get('products', [ProductController::class, 'index']);
        Route::post('products', [ProductController::class, 'store']);
        Route::get('products/{id}', [ProductController::class, 'show']);
        Route::put('products/{id}', [ProductController::class, 'update']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);

        // raw materials
        Route::get('raw-materials', [RawMaterialController::class, 'index']);
        Route::post('raw-materials', [RawMaterialController::class, 'store']);
        Route::get('raw-materials/{id}', [RawMaterialController::class, 'show']);
        Route::put('raw-materials/{id}', [RawMaterialController::class, 'update']);
        Route::delete('raw-materials/{id}', [RawMaterialController::class, 'destroy']);

        // purchases
        Route::get('purchases', [PurchaseController::class, 'index']);
        Route::get('purchases/supplier/{id}', [PurchaseController::class, 'indexBySupplier']);
        Route::post('purchases', [PurchaseController::class, 'store']);
        Route::get('purchases/{id}', [PurchaseController::class, 'show']);
        Route::put('purchases/{id}', [PurchaseController::class, 'update']);

        // sales
        Route::get('sales', [SaleController::class, 'index']);
        Route::post('sales', [SaleController::class, 'store']);
        Route::get('sales/{id}', [SaleController::class, 'show']);
        Route::put('sales/{id}', [SaleController::class, 'update']);
        

        // raw material withdrawal records
        Route::get('raw-material-withdrawal-records', [RawMaterialWithdrawalRecordController::class, 'index']);
        Route::post('raw-material-withdrawal-records', [RawMaterialWithdrawalRecordController::class, 'store']);
        Route::get('raw-material-withdrawal-records/{id}', [RawMaterialWithdrawalRecordController::class, 'show']);
        Route::get('raw-material-withdrawal-records/raw-material/{id}', [RawMaterialWithdrawalRecordController::class, 'indexByRawMaterial']);
        Route::put('raw-material-withdrawal-records/{id}', [RawMaterialWithdrawalRecordController::class, 'update']);
        Route::delete('raw-material-withdrawal-records/{id}', [RawMaterialWithdrawalRecordController::class, 'destroy']);

        // expenses
        Route::get('expenses', [ExpenseController::class, 'index']);
        Route::get('expenses/user/{id}', [ExpenseController::class, 'indexByUser']);
        Route::post('expenses', [ExpenseController::class, 'store']);
        Route::get('expenses/{id}', [ExpenseController::class, 'show']);
        Route::put('expenses/{id}', [ExpenseController::class, 'update']);
        Route::delete('expenses/{id}', [ExpenseController::class, 'destroy']);

        // governrate return all 
        Route::get('governorates', [GovernorateController::class, 'index']);
    }
);
