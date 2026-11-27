<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ItemDaSimulacaoController;
use App\Http\Controllers\Api\V1\ProdutoController;
use App\Http\Controllers\Api\V1\SimulacaoController;
use App\Http\Controllers\Api\V1\SimularItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
    Route::post('simulacoes/item', SimularItemController::class)
        ->middleware(['abilities:simulacoes', 'throttle:api-simulacoes'])
        ->name('simulacoes.item');

    Route::apiResource('produtos', ProdutoController::class)
        ->only(['index', 'show'])
        ->middleware('abilities:produtos:ler')
        ->where(['produto' => '[0-9]+']);

    Route::apiResource('produtos', ProdutoController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('abilities:produtos:escrever')
        ->where(['produto' => '[0-9]+']);

    Route::middleware('abilities:simulacoes')->group(function (): void {
        Route::apiResource('simulacoes', SimulacaoController::class)
            ->only(['index', 'store', 'show'])
            ->parameters(['simulacoes' => 'simulacao'])
            ->where(['simulacao' => '[0-9]+']);

        Route::get('simulacoes/{simulacao}/itens', [ItemDaSimulacaoController::class, 'index'])
            ->whereNumber('simulacao')
            ->name('simulacoes.itens.index');
    });
});
