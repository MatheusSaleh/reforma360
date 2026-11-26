<?php

declare(strict_types=1);

namespace App\Http\Controllers\DadosAbertos;

use App\Http\Controllers\Controller;
use App\Integrations\Calculadora\Contracts\DadosAbertos;
use App\Integrations\Calculadora\Data\SituacaoTributaria;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class SituacoesTributariasController extends Controller
{
    public function __invoke(DadosAbertos $dadosAbertos): JsonResponse
    {
        return response()->json(array_map(
            fn (SituacaoTributaria $situacao): array => [
                'codigo'         => $situacao->codigo,
                'descricao'      => $situacao->descricao,
                'classificacoes' => $situacao->classificacoes,
            ],
            $dadosAbertos->situacoesTributarias(CarbonImmutable::today('America/Sao_Paulo')),
        ));
    }
}
