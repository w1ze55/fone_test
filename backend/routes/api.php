<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProdutoController;
use App\Http\Controllers\Api\CompraController;
use App\Http\Controllers\Api\VendaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rotas para produtos
Route::apiResource('produtos', ProdutoController::class);

// Rotas para compras
Route::apiResource('compras', CompraController::class);

// Rotas para vendas
Route::apiResource('vendas', VendaController::class);
Route::post('vendas/{id}/cancelar', [VendaController::class, 'cancelar']);
