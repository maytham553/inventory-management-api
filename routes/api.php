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
use App\Http\Controllers\ReportController;
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

Route::middleware(['auth:sanctum'])->prefix('auth')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('users/currentUser', [UserController::class, 'showUser']);

    // suppliers 
    Route::get('suppliers', [SupplierController::class, 'index']);
    Route::get('suppliers/governorate/{id}', [SupplierController::class, 'indexByGovernorate']);
    Route::post('suppliers', [SupplierController::class, 'store']);
    Route::get('suppliers/{id}', [SupplierController::class, 'show']);
    Route::put('suppliers/{id}', [SupplierController::class, 'update']);
    Route::put('suppliers/{id}/re-calculate-balance', [SupplierController::class, 'reCalculateBalance']);
    Route::delete('suppliers/{id}', [SupplierController::class, 'destroy']);
    Route::get('suppliers/{id}/transactions', [SupplierController::class, 'supplierTransactions']);

    // customers
    Route::get('customers', [CustomerController::class, 'index']);
    Route::get('customers/governorate/{id}', [CustomerController::class, 'indexByGovernorate']);
    Route::post('customers', [CustomerController::class, 'store']);
    Route::get('customers/{id}', [CustomerController::class, 'show']);
    Route::put('customers/{id}', [CustomerController::class, 'update']);
    Route::put('customers/{id}/re-calculate-balance', [CustomerController::class, 'reCalculateBalance']);
    Route::delete('customers/{id}', [CustomerController::class, 'destroy']);
    Route::get('customers/{id}/sales', [CustomerController::class, 'getSales']);
    Route::get('customers/{id}/transactions', [CustomerController::class, 'customerTransactions']);

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

    // purchases
    Route::get('purchases', [PurchaseController::class, 'index']);
    Route::get('purchases/supplier/{id}', [PurchaseController::class, 'indexBySupplier']);
    Route::post('purchases', [PurchaseController::class, 'store']);
    Route::get('purchases/{id}', [PurchaseController::class, 'show']);
    Route::put('purchases/{id}', [PurchaseController::class, 'update']);

    // sales
    Route::get('sales/date/{from?}/{to?}', [SaleController::class, 'indexByDateWithProductsAndCustomer']);
    Route::get('sales', [SaleController::class, 'index']);
    Route::post('sales', [SaleController::class, 'store']);
    Route::get('sales/{id}', [SaleController::class, 'show']);
    Route::put('sales/{id}', [SaleController::class, 'update']);
    Route::delete('sales/{id}', [SaleController::class, 'destroy']);



    // raw material withdrawal records
    Route::get('raw-material-withdrawal-records', [RawMaterialWithdrawalRecordController::class, 'index']);
    Route::post('raw-material-withdrawal-records', [RawMaterialWithdrawalRecordController::class, 'store']);
    Route::get('raw-material-withdrawal-records/{id}', [RawMaterialWithdrawalRecordController::class, 'show']);
    Route::get('raw-material-withdrawal-records/raw-material/{id}', [RawMaterialWithdrawalRecordController::class, 'indexByRawMaterial']);
    Route::put('raw-material-withdrawal-records/{id}', [RawMaterialWithdrawalRecordController::class, 'update']);
    Route::delete('raw-material-withdrawal-records/{id}', [RawMaterialWithdrawalRecordController::class, 'destroy']);
  
    Route::get('products', [ProductController::class, 'index']);

    // expenses
    Route::post('expenses', [ExpenseController::class, 'store']);
    // print expenses
    Route::get('expenses/print', [ExpenseController::class, 'printExpenses']);

    // governrate return all 
    Route::get('governorates', [GovernorateController::class, 'index']);


    Route::middleware(['checkUserType:Admin|SuperAdmin'])->group(function () {

        //User and Auth
        Route::get('users', [UserController::class, 'index']);
        Route::post('users', [UserController::class, 'store']);
        Route::get('users/{id}', [UserController::class, 'show']);
        Route::put('users/{id}', [UserController::class, 'update']);
        Route::delete('users/{id}', [UserController::class, 'destroy']);
        Route::post('logout/all', [AuthController::class, 'logoutFromAllDevices']);

        // products
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

        // expenses
        Route::get('expenses', [ExpenseController::class, 'index']);
        Route::get('expenses/user/{id}', [ExpenseController::class, 'indexByUser']);
        Route::get('expenses/{id}', [ExpenseController::class, 'show']);
        Route::put('expenses/{id}', [ExpenseController::class, 'update']);
        Route::delete('expenses/{id}', [ExpenseController::class, 'destroy']);
    },
     // super admin 
     Route::middleware(['checkUserType:SuperAdmin'])->group(function () {
        Route::get('reports/sales-statistics', [ReportController::class, 'salesStatistics']);
        Route::get('reports/sales-statistics-by-day', [ReportController::class, 'salesStatisticsByDay']);
    })

);
});

Route::put('sales/sss', [SaleController::class, 'updateSaleProductsCostAndProfit']);


// get every sale has duplicated item . return it with sale id and customer name and duplicated product name 
Route::get('sales/duplicated-products', function () {
    $sales = \App\Models\Sale::with('customer', 'products')->get();
    $duplicatedSales = [];
    foreach ($sales as $sale) {
        $products = $sale->products;
        $duplicatedProducts = $products->filter(function ($product) use ($products) {
            return $products->where('id', $product->id)->count() > 1;
        });
        if ($duplicatedProducts->count() > 0) {
            $duplicatedSales[] = [
                'sale_id' => $sale->id,
                'customer_name' => $sale->customer->name,
                'duplicated_products' => $duplicatedProducts->map(function ($product) {
                    return $product->name;
                }),
                'created_at' => $sale->created_at,
            ];
        }
    }
    return response()->success($duplicatedSales, 'duplicated products retrieved successfully', 200);
});