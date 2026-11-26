<?php

declare(strict_types=1);

use App\Http\Controllers\DadosAbertos\MunicipiosController;
use App\Http\Controllers\Simulacao\SimulacaoItemController;
use App\Http\Controllers\Simulacao\SimularItemController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/simulacoes/item')->name('home');

Route::get('simulacoes/item', [SimulacaoItemController::class, 'create'])->name('simulacoes.item.create');
Route::get('simulacoes/item/resultado', SimularItemController::class)
    ->middleware('throttle:simulacoes')
    ->name('simulacoes.item.resultado');

Route::get('dados-abertos/ufs/{uf}/municipios', MunicipiosController::class)->name('dados-abertos.municipios');
