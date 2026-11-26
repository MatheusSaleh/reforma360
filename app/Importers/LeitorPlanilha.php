<?php

declare(strict_types=1);

namespace App\Importers;

use App\Importers\Data\LinhaPlanilha;
use App\Importers\Exceptions\ArquivoInvalidoException;
use Closure;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

/**
 * Leitura de planilha com Laravel Excel em blocos, entregando lotes de LinhaPlanilha.
 *
 * O StringValueBinder mantém células de CSV como texto ("000001" não vira 1).
 */
final class LeitorPlanilha extends StringValueBinder implements OnEachRow, SkipsEmptyRows, WithChunkReading, WithCustomCsvSettings, WithCustomValueBinder, WithHeadingRow
{
    /** @var list<LinhaPlanilha> */
    private array $lote = [];

    private int $linhasLidas = 0;

    private bool $cabecalhoVerificado = false;

    /**
     * @param  Closure(list<LinhaPlanilha>): void  $aoLerLote
     * @param  array<string, mixed>  $configuracaoCsv
     */
    public function __construct(
        private readonly Closure $aoLerLote,
        private readonly int $tamanhoLote,
        private readonly int $maximoLinhas,
        private readonly array $configuracaoCsv = [],
    ) {}

    public function onRow(Row $row): void
    {
        $valores = $row->toArray(null, false, false);

        if (! $this->cabecalhoVerificado) {
            $this->verificarCabecalho(array_map(strval(...), array_keys($valores)));
        }

        if (++$this->linhasLidas > $this->maximoLinhas) {
            throw ArquivoInvalidoException::linhasDemais($this->maximoLinhas);
        }

        $campos = [];

        foreach ($valores as $cabecalho => $valor) {
            $coluna = ColunasCatalogo::canonica((string) $cabecalho);

            if ($coluna !== null) {
                $campos[$coluna] = self::texto($valor);
            }
        }

        $this->lote[] = new LinhaPlanilha($row->getIndex(), $campos);

        if (count($this->lote) >= $this->tamanhoLote) {
            $this->entregarLote();
        }
    }

    /**
     * Entrega o último lote. Chamado depois que a leitura termina.
     */
    public function finalizar(): void
    {
        $this->entregarLote();

        if ($this->linhasLidas === 0) {
            throw ArquivoInvalidoException::semLinhas();
        }
    }

    public function chunkSize(): int
    {
        return $this->tamanhoLote;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCsvSettings(): array
    {
        return $this->configuracaoCsv;
    }

    private function entregarLote(): void
    {
        if ($this->lote === []) {
            return;
        }

        ($this->aoLerLote)($this->lote);
        $this->lote = [];
    }

    /**
     * @param  list<string>  $cabecalhos
     */
    private function verificarCabecalho(array $cabecalhos): void
    {
        $presentes = array_values(array_filter(array_map(ColunasCatalogo::canonica(...), $cabecalhos)));
        $faltando = ColunasCatalogo::faltando($presentes);

        if ($faltando !== []) {
            throw ArquivoInvalidoException::colunasFaltando($faltando);
        }

        $this->cabecalhoVerificado = true;
    }

    /**
     * Converte a célula para texto. Números do XLSX chegam como int/float: o float
     * vira texto com as casas que tiver ("28.9"), e a validação decide se é aceito.
     */
    private static function texto(mixed $valor): ?string
    {
        $texto = match (true) {
            $valor === null   => null,
            is_string($valor) => mb_trim($valor),
            is_int($valor)    => (string) $valor,
            is_float($valor)  => mb_rtrim(mb_rtrim(sprintf('%.10F', $valor), '0'), '.'),
            is_bool($valor)   => $valor ? '1' : '0',
            default           => null,
        };

        return $texto === '' ? null : $texto;
    }
}
