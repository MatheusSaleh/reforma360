<?php

declare(strict_types=1);

namespace App\Http\Controllers\Importacao;

use App\Http\Controllers\Controller;
use App\Integrations\Calculadora\Contracts\GeradorDanfse;
use App\Integrations\Calculadora\Exceptions\DanfseRejeitadoException;
use App\Models\Importacao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * DANFSe (PDF) da NFS-e importada, gerado a partir do XML original guardado.
 */
class BaixarDanfseController extends Controller
{
    public function __invoke(Importacao $importacao, GeradorDanfse $danfse): Response|RedirectResponse
    {
        Gate::authorize('view', $importacao);

        abort_unless($importacao->guardaXmlParaDanfse(), 404);

        if (! $importacao->temXmlParaDanfse()) {
            return back()->withErrors(['danfse' => 'O XML desta NFS-e não está mais guardado. Importe a nota de novo para gerar o DANFSe.']);
        }

        try {
            $pdf = $danfse->gerar((string) Storage::disk('local')->get((string) $importacao->caminho_arquivo));
        } catch (DanfseRejeitadoException $e) {
            return back()->withErrors(['danfse' => $e->getMessage()]);
        }

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="danfse-nfse-'.preg_replace('/\D/', '', (string) $importacao->numero_documento).'.pdf"',
        ]);
    }
}
