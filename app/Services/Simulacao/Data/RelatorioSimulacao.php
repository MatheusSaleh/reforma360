<?php

declare(strict_types=1);

namespace App\Services\Simulacao\Data;

use App\Integrations\Calculadora\Data\ClassificacaoTributaria;
use App\Integrations\Calculadora\Data\FundamentacaoLegal;
use App\Models\ComparacaoRegime;
use App\Models\LinhaDoTempo;
use App\Models\Simulacao;
use App\Models\SimulacaoItem;
use Carbon\CarbonImmutable;

/**
 * Dados do relatório em PDF de uma simulação.
 */
final readonly class RelatorioSimulacao
{
    /**
     * @param  list<SimulacaoItem>  $itens
     * @param  list<array{c_class_trib: string, cst: string, itens: int, base: numeric-string, tributos: numeric-string, reducao: numeric-string|null, classificacao: ClassificacaoTributaria|null, fundamentacao: FundamentacaoLegal|null}>  $porClassificacao
     * @param  list<AnoDaLinhaDoTempo>  $anos
     */
    public function __construct(
        public Simulacao $simulacao,
        public array $itens,
        public bool $itensTruncados,
        public int $limiteItens,
        public array $porClassificacao,
        public bool $fundamentacaoIndisponivel,
        public ?LinhaDoTempo $linhaDoTempo,
        public array $anos,
        public ?ComparacaoRegime $comparacao,
        public CarbonImmutable $geradoEm,
    ) {}
}
