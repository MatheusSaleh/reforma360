<?php

declare(strict_types=1);

namespace App\Importers;

use App\Enums\FormatoImportacao;
use Closure;
use Illuminate\Container\Attributes\Config;
use Maatwebsite\Excel\Excel as TipoLeitor;
use Maatwebsite\Excel\Facades\Excel;

final readonly class XlsxImporter implements CatalogImporter
{
    public function __construct(
        #[Config('importacao.tamanho_lote')]
        private int $tamanhoLote = 500,
        #[Config('importacao.maximo_linhas')]
        private int $maximoLinhas = 5000,
    ) {}

    public function suporta(FormatoImportacao $formato): bool
    {
        return $formato === FormatoImportacao::Xlsx;
    }

    public function ler(string $caminho, Closure $aoLerLote): void
    {
        $leitor = new LeitorPlanilha($aoLerLote, $this->tamanhoLote, $this->maximoLinhas);

        Excel::import($leitor, $caminho, 'local', TipoLeitor::XLSX);
        $leitor->finalizar();
    }
}
