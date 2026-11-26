<?php

declare(strict_types=1);

namespace App\Http\Controllers\Importacao;

use App\Enums\FormatoImportacao;
use App\Exports\ModeloCatalogoExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Excel as TipoArquivo;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ModeloPlanilhaController extends Controller
{
    public function __invoke(FormatoImportacao $formato): BinaryFileResponse
    {
        return Excel::download(
            new ModeloCatalogoExport,
            "modelo-catalogo-reforma360.{$formato->value}",
            $formato === FormatoImportacao::Csv ? TipoArquivo::CSV : TipoArquivo::XLSX,
        );
    }
}
