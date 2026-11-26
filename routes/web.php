<?php

declare(strict_types=1);

use App\Http\Controllers\Catalogo\ProdutoController;
use App\Http\Controllers\DadosAbertos\BuscarNomenclaturaController;
use App\Http\Controllers\DadosAbertos\DetalheNomenclaturaController;
use App\Http\Controllers\DadosAbertos\MunicipiosController;
use App\Http\Controllers\DadosAbertos\SituacoesTributariasController;
use App\Http\Controllers\Importacao\BaixarDanfseController;
use App\Http\Controllers\Importacao\ImportacaoController;
use App\Http\Controllers\Importacao\ModeloPlanilhaController;
use App\Http\Controllers\Simulacao\BaixarXmlDoGrupoController;
use App\Http\Controllers\Simulacao\SimulacaoController;
use App\Http\Controllers\Simulacao\SimulacaoItemController;
use App\Http\Controllers\Simulacao\SimularItemController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/simulacoes/item')->name('home');

Route::get('simulacoes/item', [SimulacaoItemController::class, 'create'])->name('simulacoes.item.create');
Route::get('simulacoes/item/resultado', SimularItemController::class)
    ->middleware('throttle:simulacoes')
    ->name('simulacoes.item.resultado');

Route::prefix('dados-abertos')->name('dados-abertos.')->middleware('throttle:dados-abertos')->group(function (): void {
    Route::get('ufs/{uf}/municipios', MunicipiosController::class)->name('municipios');
    Route::get('nomenclaturas/{tipo}', BuscarNomenclaturaController::class)->name('nomenclaturas.busca');
    Route::get('nomenclaturas/{tipo}/{codigo}', DetalheNomenclaturaController::class)->whereNumber('codigo')->name('nomenclaturas.detalhe');
    Route::get('situacoes-tributarias', SituacoesTributariasController::class)->name('situacoes-tributarias');
});

Route::middleware('auth')->group(function (): void {
    Route::resource('produtos', ProdutoController::class)->except('show');

    Route::resource('simulacoes', SimulacaoController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->parameters(['simulacoes' => 'simulacao'])
        ->where(['simulacao' => '[0-9]+']);

    Route::get('simulacoes/{simulacao}/xml-ibscbs', BaixarXmlDoGrupoController::class)
        ->middleware('throttle:simulacoes')
        ->name('simulacoes.xml-ibscbs');

    Route::get('importacoes/{importacao}/danfse', BaixarDanfseController::class)
        ->middleware('throttle:simulacoes')
        ->name('importacoes.danfse');

    Route::get('importacoes/modelo/{formato}', ModeloPlanilhaController::class)->name('importacoes.modelo');
    Route::resource('importacoes', ImportacaoController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->parameters(['importacoes' => 'importacao']);
});
