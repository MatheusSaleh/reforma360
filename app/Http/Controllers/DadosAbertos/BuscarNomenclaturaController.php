<?php

declare(strict_types=1);

namespace App\Http\Controllers\DadosAbertos;

use App\Enums\TipoNomenclatura;
use App\Http\Controllers\Controller;
use App\Http\Requests\DadosAbertos\BuscarNomenclaturaRequest;
use App\Integrations\Calculadora\Data\ItemNomenclatura;
use App\Services\Catalogo\BuscaNomenclaturaService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class BuscarNomenclaturaController extends Controller
{
    public function __invoke(TipoNomenclatura $tipo, BuscarNomenclaturaRequest $request, BuscaNomenclaturaService $busca): JsonResponse
    {
        $itens = $busca->buscar($tipo, $request->string('q')->toString(), CarbonImmutable::today('America/Sao_Paulo'));

        return response()->json(array_map(
            fn (ItemNomenclatura $item): array => ['codigo' => $item->codigo, 'descricao' => $item->descricao],
            $itens,
        ));
    }
}
