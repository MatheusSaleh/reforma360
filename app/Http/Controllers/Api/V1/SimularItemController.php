<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Simulacao\SimularItemRequest;
use App\Http\Resources\V1\SimulacaoDeItemResource;
use App\Services\Simulacao\SimularItemService;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromFile;

#[Group('Simulação de item', 'Simula CBS e IBS de um produto (NCM) ou serviço (NBS) na hora. Permissão: `simulacoes`.')]
class SimularItemController extends Controller
{
    #[Endpoint('Simular um item', <<<'DESC'
        Calcula CBS, IBS estadual e IBS municipal de um item com a calculadora oficial da Receita Federal e devolve o resultado na resposta.

        Informe **NCM** (mercadoria) **ou** **NBS** (serviço). De 2027 em diante, as três alíquotas nominais são obrigatórias e o resultado vem com `calculo_simulado: true`.
        DESC)]
    #[ResponseFromFile('.scribe/exemplos/simulacao-item.json', description: 'Item simulado')]
    #[Response(['message' => 'Informe o NCM (mercadoria) ou o NBS (serviço).', 'errors' => ['ncm' => ['Informe o NCM (mercadoria) ou o NBS (serviço).']]], 422, 'Dados inválidos')]
    #[Response(['message' => 'A calculadora da Receita está indisponível no momento. Tente novamente em instantes.'], 503, 'Calculadora indisponível')]
    public function __invoke(SimularItemRequest $request, SimularItemService $service): SimulacaoDeItemResource
    {
        return new SimulacaoDeItemResource($service->simular($request->dados()));
    }
}
