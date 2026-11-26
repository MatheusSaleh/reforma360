<?php

declare(strict_types=1);

namespace App\Http\Controllers\Simulacao;

use App\Http\Controllers\Controller;
use App\Models\Simulacao;
use App\Services\Simulacao\RelatorioSimulacaoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Relatório em PDF da simulação para levar ao contador.
 */
class BaixarRelatorioPdfController extends Controller
{
    public function __invoke(Simulacao $simulacao, RelatorioSimulacaoService $service): Response
    {
        Gate::authorize('view', $simulacao);

        $relatorio = $service->montar($simulacao);

        $pdf = Pdf::loadView('relatorios.simulacao', ['relatorio' => $relatorio])->setPaper('a4');

        // "Página X de Y": o total só existe depois de renderizar (o CSS do dompdf não o calcula).
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();
        $fonte = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $dompdf->getCanvas()->page_text(500, 812, 'Página {PAGE_NUM} de {PAGE_COUNT}', $fonte, 7, [0.4, 0.4, 0.4]);

        return response((string) $dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"relatorio-simulacao-{$simulacao->id}.pdf\"",
        ]);
    }
}
