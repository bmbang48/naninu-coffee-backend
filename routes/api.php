<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CashFlowController;
use App\Models\Material;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/materials', MaterialController::class);
Route::apiResource('/products', App\Http\Controllers\Api\ProductController::class);
Route::apiResource('/recipe-product', App\Http\Controllers\Api\RecipeProductController::class);
Route::apiResource('/other-cost', App\Http\Controllers\Api\OtherCostsController::class);
Route::apiResource('/transactions', App\Http\Controllers\Api\TransactionController::class);
Route::apiResource('/users', App\Http\Controllers\Api\UsersController::class);
Route::post('/login', [App\Http\Controllers\Api\UsersController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    return response()->json([
        'message' => 'Logout success'
    ]);
});
Route::middleware('auth:sanctum')->get('/me', function () {
    return auth()->user();
});
Route::get('/materials-all', [MaterialController::class, 'allMaterials']);
Route::get('/products-cashier', [App\Http\Controllers\Api\ProductController::class, 'productsCashier']);
Route::get('/products-all', [App\Http\Controllers\Api\ProductController::class, 'allProducts']);
Route::get('/transactions/profit/today', [App\Http\Controllers\Api\TransactionController::class, 'profitToday']);
Route::get('/recipe-bot', [App\Http\Controllers\Api\RecipeProductController::class, 'getByProduct']);
Route::post('/materials/restock', [MaterialController::class, 'restock']);
Route::post('/materials/adjust', [MaterialController::class, 'adjustStock']);
Route::get('/material-logs', [MaterialController::class, 'logs']);
Route::get('/dashboard', [MaterialController::class, 'dashboard']);
Route::get('/cashflow', [DashboardController::class, 'cashflow']);

Route::get('/cashflows', [CashFlowController::class, 'index']);
Route::post('/cashflows', [CashFlowController::class, 'store']);
Route::get('/cashflows-summary', [CashFlowController::class, 'summary']);
Route::get('/cashflows-chart', [CashFlowController::class, 'chart']);
//SSO
Route::get('/sso-login', [App\Http\COntrollers\Api\AuthController::class, 'ssoLogin']);
