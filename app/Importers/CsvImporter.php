<?php

declare(strict_types=1);

namespace App\Importers;

use App\Enums\FormatoImportacao;
use Closure;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel as TipoLeitor;
use Maatwebsite\Excel\Facades\Excel;

/**
 * CSV: detecta o separador (";" é o padrão do Excel em pt-BR) e o encoding
 * (UTF-8 ou Latin-1, comum em arquivos salvos pelo Excel no Windows).
 */
final readonly class CsvImporter implements CatalogImporter
{
    public function __construct(
        #[Config('importacao.tamanho_lote')]
        private int $tamanhoLote = 500,
        #[Config('importacao.maximo_linhas')]
        private int $maximoLinhas = 5000,
    ) {}

    public function suporta(FormatoImportacao $formato): bool
    {
        return $formato === FormatoImportacao::Csv;
    }

    public function ler(string $caminho, Closure $aoLerLote): void
    {
        $leitor = new LeitorPlanilha($aoLerLote, $this->tamanhoLote, $this->maximoLinhas, $this->configuracaoCsv($caminho));

        Excel::import($leitor, $caminho, 'local', TipoLeitor::CSV);
        $leitor->finalizar();
    }

    /**
     * @return array{delimiter: string, input_encoding: string, enclosure: string}
     */
    private function configuracaoCsv(string $caminho): array
    {
        $amostra = $this->amostra($caminho);
        $primeiraLinha = strtok($amostra, "\r\n") ?: '';

        return [
            'delimiter'      => mb_substr_count($primeiraLinha, ';') >= mb_substr_count($primeiraLinha, ',') ? ';' : ',',
            'input_encoding' => mb_check_encoding($amostra, 'UTF-8') ? 'UTF-8' : 'ISO-8859-1',
            'enclosure'      => '"',
        ];
    }

    private function amostra(string $caminho): string
    {
        $stream = Storage::disk('local')->readStream($caminho);

        if (! is_resource($stream)) {
            return '';
        }

        $amostra = (string) fread($stream, 65536);
        fclose($stream);

        // Não corta um caractere UTF-8 multibyte no fim da amostra.
        return mb_strcut($amostra, 0, mb_strlen($amostra, '8bit') - 4, 'UTF-8') ?: $amostra;
    }
}
