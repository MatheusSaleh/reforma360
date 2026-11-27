<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Catalogo\ProdutoRequest;
use App\Http\Resources\V1\ProdutoResource;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response as RespostaDeExemplo;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Produtos', 'Catálogo de produtos e serviços da empresa. Leitura: `produtos:ler`. Criação, alteração e exclusão: `produtos:escrever`.')]
class ProdutoController extends Controller
{
    #[Endpoint('Listar produtos', 'Produtos da empresa em ordem alfabética, paginados.')]
    #[QueryParam('busca', 'string', 'Filtra por descrição, NCM ou NBS.', required: false, example: 'arroz')]
    #[QueryParam('por_pagina', 'integer', 'Itens por página (1 a 100).', required: false, example: 25)]
    #[QueryParam('page', 'integer', 'Página.', required: false, example: 1)]
    #[ResponseFromApiResource(ProdutoResource::class, Produto::class, collection: true, paginate: 25)]
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Produto::class);

        return ProdutoResource::collection(
            $this->usuario($request)->empresa->produtos()
                ->busca($request->string('busca')->trim()->toString())
                ->orderBy('descricao')
                ->paginate($this->porPagina($request))
                ->withQueryString(),
        );
    }

    #[Endpoint('Cadastrar produto', 'Informe NCM (mercadoria) ou NBS (serviço), CST e cClassTrib.')]
    #[ResponseFromApiResource(ProdutoResource::class, Produto::class, status: 201)]
    #[RespostaDeExemplo(['message' => 'Informe o NCM (mercadoria) ou o NBS (serviço).', 'errors' => ['ncm' => ['Informe o NCM (mercadoria) ou o NBS (serviço).']]], 422, 'Dados inválidos')]
    public function store(ProdutoRequest $request): ProdutoResource
    {
        Gate::authorize('create', Produto::class);

        return new ProdutoResource(
            $this->usuario($request)->empresa->produtos()->create($request->dadosDoProduto()),
        );
    }

    #[Endpoint('Consultar produto')]
    #[UrlParam('produto', 'integer', 'ID do produto.', example: 1)]
    #[ResponseFromApiResource(ProdutoResource::class, Produto::class)]
    #[RespostaDeExemplo(['message' => 'Not Found'], 404, 'Produto inexistente ou de outra empresa')]
    public function show(Produto $produto): ProdutoResource
    {
        Gate::authorize('view', $produto);

        return new ProdutoResource($produto);
    }

    #[Endpoint('Alterar produto', 'Envie todos os campos do produto (substituição completa).')]
    #[UrlParam('produto', 'integer', 'ID do produto.', example: 1)]
    #[ResponseFromApiResource(ProdutoResource::class, Produto::class)]
    public function update(ProdutoRequest $request, Produto $produto): ProdutoResource
    {
        Gate::authorize('update', $produto);

        $produto->update($request->dadosDoProduto());

        return new ProdutoResource($produto);
    }

    #[Endpoint('Excluir produto')]
    #[UrlParam('produto', 'integer', 'ID do produto.', example: 1)]
    #[RespostaDeExemplo(null, 204, 'Produto excluído')]
    public function destroy(Produto $produto): Response
    {
        Gate::authorize('delete', $produto);

        $produto->delete();

        return response()->noContent();
    }
}
