<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Simulacao\IniciarSimulacaoEmLote;
use App\Http\Requests\Simulacao\IniciarSimulacaoRequest;
use App\Http\Resources\V1\SimulacaoResource;
use App\Models\Simulacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Simulação do catálogo', 'Simula todos os produtos da empresa (ou os itens de uma nota importada) em segundo plano. Permissão: `simulacoes`.')]
class SimulacaoController extends Controller
{
    #[Endpoint('Listar simulações', 'Simulações do catálogo da empresa, da mais recente para a mais antiga.')]
    #[QueryParam('por_pagina', 'integer', 'Itens por página (1 a 100).', required: false, example: 25)]
    #[QueryParam('page', 'integer', 'Página.', required: false, example: 1)]
    #[ResponseFromApiResource(SimulacaoResource::class, Simulacao::class, collection: true, paginate: 25)]
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Simulacao::class);

        return SimulacaoResource::collection(
            $this->usuario($request)->empresa->simulacoes()
                ->whereNull('linha_do_tempo_id')
                ->latest()
                ->paginate($this->porPagina($request)),
        );
    }

    #[Endpoint('Iniciar simulação do catálogo', <<<'DESC'
        Enfileira a simulação de todos os produtos da empresa — ou só dos itens de uma nota importada, com `importacao_id` — e responde `202` na hora.

        Acompanhe o andamento em `GET /api/v1/simulacoes/{id}` (campo `status`) e leia o resultado de cada item em `GET /api/v1/simulacoes/{id}/itens`.
        DESC)]
    #[ResponseFromApiResource(SimulacaoResource::class, Simulacao::class, status: 202, description: 'Simulação enfileirada')]
    #[Response(['message' => 'Cadastre ou importe produtos antes de simular o catálogo.', 'errors' => ['simulacao' => ['Cadastre ou importe produtos antes de simular o catálogo.']]], 422, 'Sem produtos ou dados inválidos')]
    public function store(IniciarSimulacaoRequest $request, IniciarSimulacaoEmLote $iniciar): JsonResponse
    {
        Gate::authorize('create', Simulacao::class);

        $importacao = $request->importacao();

        if ($importacao !== null) {
            Gate::authorize('view', $importacao);
        }

        $simulacao = $iniciar->handle($this->usuario($request), $request->parametros(), $importacao);

        return (new SimulacaoResource($simulacao))->response()->setStatusCode(202);
    }

    #[Endpoint('Consultar simulação', 'Status, progresso e totais da simulação.')]
    #[UrlParam('simulacao', 'integer', 'ID da simulação.', example: 1)]
    #[ResponseFromApiResource(SimulacaoResource::class, Simulacao::class)]
    #[Response(['message' => 'Not Found'], 404, 'Simulação inexistente ou de outra empresa')]
    public function show(Simulacao $simulacao): SimulacaoResource
    {
        Gate::authorize('view', $simulacao);

        return new SimulacaoResource($simulacao);
    }
}
