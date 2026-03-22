<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\TransacaoController;
use App\Http\Controllers\TransferenciaController;

Route::post('/login', [ApiAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/transferencia', [TransferenciaController::class, 'store']);
    Route::get('/ultimas-transferencias', [TransacaoController::class, 'getLastsTransfers']);
    Route::get('/extrato-completo', [TransacaoController::class, 'getAllTransfers']);
    Route::get('/saldo', function (Request $request) {
        return ['success' => true, 'data' => $request->user()->carteira()->first()->valor_atual ?? 0];
    });
});
