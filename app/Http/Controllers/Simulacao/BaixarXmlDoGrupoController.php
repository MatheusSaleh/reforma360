<?php

declare(strict_types=1);

namespace App\Http\Controllers\Simulacao;

use App\Http\Controllers\Controller;
use App\Models\Simulacao;
use App\Services\Simulacao\GerarXmlDoGrupoService;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Download do XML do grupo IBS/CBS da simulação, validado contra o XSD da Receita.
 */
class BaixarXmlDoGrupoController extends Controller
{
    public function __invoke(Simulacao $simulacao, GerarXmlDoGrupoService $service): Response
    {
        Gate::authorize('view', $simulacao);

        $xml = $service->gerar($simulacao);

        return response($xml->conteudo, 200, [
            'Content-Type'        => 'application/xml; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$xml->nomeArquivo}\"",
        ]);
    }
}
