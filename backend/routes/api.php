<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProdutoController;
use App\Http\Controllers\Api\CompraController;
use App\Http\Controllers\Api\VendaController;
use App\Http\Controllers\Api\AuthController;

// Auth routes (public) - Sem middleware CSRF
Route::withoutMiddleware([\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class])->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth user info
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    
    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        // Rotas para produtos (admin pode criar, editar, deletar)
        Route::post('produtos', [ProdutoController::class, 'store']);
        Route::put('produtos/{produto}', [ProdutoController::class, 'update']);
        Route::delete('produtos/{produto}', [ProdutoController::class, 'destroy']);
        
        // Rotas para compras (apenas admin)
        Route::apiResource('compras', CompraController::class);
    });
    
    // Routes accessible by both admin and user
    Route::middleware('role:user,admin')->group(function () {
        // Produtos - usuários podem ver e consultar (read-only para users)
        Route::get('produtos', [ProdutoController::class, 'index']);
        Route::get('produtos/{produto}', [ProdutoController::class, 'show']);
    });
    
    // User and Admin routes
    Route::middleware('role:user,admin')->group(function () {
        // Rotas para vendas (user e admin)
        Route::apiResource('vendas', VendaController::class);
        Route::post('vendas/{id}/cancelar', [VendaController::class, 'cancelar']);
    });
});
