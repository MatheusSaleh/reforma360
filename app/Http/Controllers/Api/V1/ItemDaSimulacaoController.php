<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\AvisoDeSimulacao;
use App\Http\Resources\V1\SimulacaoItemResource;
use App\Models\Simulacao;
use App\Models\SimulacaoItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Simulação do catálogo')]
class ItemDaSimulacaoController extends Controller
{
    #[Endpoint('Resultado por item', 'Resultado de cada item da simulação, em ordem alfabética. Itens não calculados trazem o motivo em `erros`.')]
    #[UrlParam('simulacao', 'integer', 'ID da simulação.', example: 1)]
    #[QueryParam('erros', 'boolean', 'Só os itens que não puderam ser calculados.', required: false, example: false)]
    #[QueryParam('por_pagina', 'integer', 'Itens por página (1 a 100).', required: false, example: 25)]
    #[QueryParam('page', 'integer', 'Página.', required: false, example: 1)]
    #[ResponseFromApiResource(SimulacaoItemResource::class, SimulacaoItem::class, collection: true, paginate: 25, additional: ['aviso' => AvisoDeSimulacao::TEXTO])]
    public function index(Request $request, Simulacao $simulacao): AnonymousResourceCollection
    {
        Gate::authorize('view', $simulacao);

        return SimulacaoItemResource::collection(
            $simulacao->itens()
                ->when($request->boolean('erros'), fn ($query) => $query->whereNotIn('estado', ['CALCULADO', 'CALCULO_SIMULADO']))
                ->orderBy('descricao')
                ->paginate($this->porPagina($request))
                ->withQueryString(),
        )->additional(['aviso' => AvisoDeSimulacao::TEXTO]);
    }
}
