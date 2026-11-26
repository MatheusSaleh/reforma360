<?php

declare(strict_types=1);

namespace App\Services\Simulacao;

use App\Integrations\Calculadora\Data\AliquotasNominais;
use Illuminate\Container\Attributes\Config;
use InvalidArgumentException;

/**
 * Alíquotas nominais de cada ano da transição (config/transicao.php), a partir das
 * alíquotas plenas (estimadas) de CBS e IBS. Única fonte do cronograma no app.
 */
final readonly class CronogramaTransicaoService
{
    /**
     * @param  array<int, array{referencia?: bool, cbs_reducao_pp?: string, ibs_fixo?: array{estadual: string, municipal: string}, ibs_fracao?: string}>  $anos
     */
    public function __construct(
        #[Config('transicao.anos')]
        private array $anos,
    ) {}

    /**
     * @return list<int>
     */
    public function anos(): array
    {
        $anos = array_keys($this->anos);
        sort($anos);

        return $anos;
    }

    /**
     * Alíquotas nominais do ano, ou null quando a calculadora usa as de referência (2026).
     */
    public function aliquotasDoAno(int $ano, AliquotasNominais $plenas): ?AliquotasNominais
    {
        $regra = $this->anos[$ano] ?? throw new InvalidArgumentException("Ano fora do cronograma da transição: {$ano}.");

        if ($regra['referencia'] ?? false) {
            return null;
        }

        $cbs = bcsub(self::numero($plenas->cbs), self::numero($regra['cbs_reducao_pp'] ?? '0'), 4);

        if (isset($regra['ibs_fixo'])) {
            [$ibsEstadual, $ibsMunicipal] = [$regra['ibs_fixo']['estadual'], $regra['ibs_fixo']['municipal']];
        } else {
            $fracao = self::numero($regra['ibs_fracao'] ?? '1');
            $ibsEstadual = bcmul(self::numero($plenas->ibsEstadual), $fracao, 4);
            $ibsMunicipal = bcmul(self::numero($plenas->ibsMunicipal), $fracao, 4);
        }

        return new AliquotasNominais(
            cbs: self::semZeros(bccomp($cbs, '0', 4) < 0 ? '0' : $cbs),
            ibsEstadual: self::semZeros($ibsEstadual),
            ibsMunicipal: self::semZeros($ibsMunicipal),
        );
    }

    /**
     * @return numeric-string
     */
    private static function numero(string $valor): string
    {
        return is_numeric($valor) ? $valor : throw new InvalidArgumentException("Alíquota inválida: {$valor}.");
    }

    private static function semZeros(string $valor): string
    {
        return str_contains($valor, '.') ? (mb_rtrim(mb_rtrim($valor, '0'), '.') ?: '0') : $valor;
    }
}
