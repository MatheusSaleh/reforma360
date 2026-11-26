<?php

declare(strict_types=1);

namespace App\Http\Controllers\DadosAbertos;

use App\Enums\Uf;
use App\Http\Controllers\Controller;
use App\Integrations\Calculadora\Contracts\DadosAbertos;
use App\Integrations\Calculadora\Data\Municipio;
use Illuminate\Http\JsonResponse;

class MunicipiosController extends Controller
{
    public function __invoke(Uf $uf, DadosAbertos $dadosAbertos): JsonResponse
    {
        return response()->json(array_map(
            fn (Municipio $municipio): array => ['codigo' => $municipio->codigo, 'nome' => $municipio->nome],
            $dadosAbertos->municipios($uf),
        ));
    }
}
