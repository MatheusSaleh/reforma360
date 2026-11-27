<?php

declare(strict_types=1);

namespace App\Http\Controllers\DadosAbertos;

use App\Enums\TipoNomenclatura;
use App\Http\Controllers\Controller;
use App\Http\Requests\DadosAbertos\DetalheNomenclaturaRequest;
use App\Integrations\Calculadora\Contracts\DadosAbertos;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class DetalheNomenclaturaController extends Controller
{
    public function __invoke(TipoNomenclatura $tipo, string $codigo, DetalheNomenclaturaRequest $request, DadosAbertos $dadosAbertos): JsonResponse
    {
        abort_unless(mb_strlen($codigo) === $tipo->digitos(), 404);

        $data = $request->filled('data')
            ? CarbonImmutable::parse($request->string('data')->toString(), 'America/Sao_Paulo')
            : CarbonImmutable::today('America/Sao_Paulo');
        $detalhe = $dadosAbertos->detalheNomenclatura($tipo, $codigo, $data);

        abort_if($detalhe === null, 404, "{$tipo->label()} não encontrado.");

        return response()->json([
            'codigo'                          => $detalhe->codigo,
            'niveis'                          => $detalhe->niveis,
            'tributado_pelo_imposto_seletivo' => $detalhe->tributadoPeloImpostoSeletivo,
        ]);
    }
}
