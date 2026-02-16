<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/materials', App\Http\Controllers\Api\MaterialController::class);
Route::apiResource('/products', App\Http\Controllers\Api\ProductController::class);
Route::apiResource('/recipe-product', App\Http\Controllers\Api\RecipeProductController::class);
Route::apiResource('/other-cost', App\Http\Controllers\Api\OtherCostsController::class);
Route::apiResource('/transactions', App\Http\Controllers\Api\TransactionController::class);
Route::get('/materials-all', [App\Http\Controllers\Api\MaterialController::class, 'allMaterials']);
Route::get('/products-cashier', [App\Http\Controllers\Api\ProductController::class, 'productsCashier']);
