<?php

declare(strict_types=1);

namespace App\Services\Simulacao;

use App\Enums\EstadoItemCalculo;
use App\Integrations\Calculadora\Contracts\DadosAbertos;
use App\Integrations\Calculadora\Data\ClassificacaoTributaria;
use App\Integrations\Calculadora\Data\FundamentacaoLegal;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Models\Simulacao;
use App\Models\SimulacaoItem;
use App\Services\Simulacao\Data\RelatorioSimulacao;
use Carbon\CarbonImmutable;
use Illuminate\Container\Attributes\Config;
use Illuminate\Validation\ValidationException;

/**
 * Monta o relatório da simulação para o contador: resumo, quadro por classificação
 * tributária (com redução e fundamentação legal), itens, linha do tempo e comparação
 * com o Simples quando existirem.
 *
 * Acima do limite de itens, o detalhamento traz os de maior carga.
 */
final readonly class RelatorioSimulacaoService
{
    public function __construct(
        private DadosAbertos $dadosAbertos,
        private LinhaDoTempoService $linhaDoTempo,
        #[Config('simulacao.relatorio_maximo_itens')]
        private int $limiteItens = 1000,
    ) {}

    /**
     * @throws ValidationException quando a simulação ainda está em andamento
     */
    public function montar(Simulacao $simulacao): RelatorioSimulacao
    {
        if ($simulacao->status->emAndamento()) {
            throw ValidationException::withMessages(['relatorio' => 'Aguarde a simulação terminar para gerar o relatório.']);
        }

        $total = $simulacao->itens()->count();
        $truncado = $total > $this->limiteItens;
        $itens = $truncado
            ? $simulacao->itens()->orderByRaw('total_tributos is null')->orderByDesc('total_tributos')->orderBy('descricao')->limit($this->limiteItens)->get()
            : $simulacao->itens()->orderBy('descricao')->orderBy('id')->get();

        [$porClassificacao, $fundamentacaoIndisponivel] = $this->porClassificacao($simulacao);

        $linha = $simulacao->linhaDoTempo;
        $linhaConcluida = $linha !== null && ! $linha->status->emAndamento() ? $linha : null;

        return new RelatorioSimulacao(
            simulacao: $simulacao,
            itens: array_values($itens->all()),
            itensTruncados: $truncado,
            limiteItens: $this->limiteItens,
            porClassificacao: $porClassificacao,
            fundamentacaoIndisponivel: $fundamentacaoIndisponivel,
            linhaDoTempo: $linhaConcluida,
            anos: $linhaConcluida === null ? [] : $this->linhaDoTempo->anos($linhaConcluida),
            comparacao: $simulacao->comparacao,
            geradoEm: CarbonImmutable::now('America/Sao_Paulo'),
        );
    }

    /**
     * Itens calculados agrupados por CST/cClassTrib, com a classificação e a
     * fundamentação legal dos dados abertos (sem elas se a calculadora estiver fora).
     *
     * @return array{0: list<array{c_class_trib: string, cst: string, itens: int, base: numeric-string, tributos: numeric-string, reducao: numeric-string|null, classificacao: ClassificacaoTributaria|null, fundamentacao: FundamentacaoLegal|null}>, 1: bool}
     */
    private function porClassificacao(Simulacao $simulacao): array
    {
        $grupos = [];

        foreach ($simulacao->itens()->whereIn('estado', [EstadoItemCalculo::Calculado, EstadoItemCalculo::CalculoSimulado])->lazyById(500) as $item) {
            /** @var SimulacaoItem $item */
            $chave = $item->cst.'|'.$item->c_class_trib;
            $grupos[$chave] ??= ['c_class_trib' => $item->c_class_trib, 'cst' => $item->cst, 'itens' => 0, 'base' => '0', 'tributos' => '0', 'reducao' => null, 'classificacao' => null, 'fundamentacao' => null];
            $grupos[$chave]['itens']++;
            $grupos[$chave]['base'] = bcadd($grupos[$chave]['base'], $item->base_calculo, 2);
            $grupos[$chave]['tributos'] = bcadd($grupos[$chave]['tributos'], $item->total_tributos ?? '0', 2);
            $grupos[$chave]['reducao'] ??= $item->percentual_reducao !== null && bccomp($item->percentual_reducao, '0', 4) > 0 ? $item->percentual_reducao : null;
        }

        ksort($grupos);
        $indisponivel = false;

        foreach ($grupos as $chave => $grupo) {
            if ($indisponivel) {
                break;
            }

            try {
                $grupos[$chave]['classificacao'] = $this->dadosAbertos->classificacaoTributaria($grupo['c_class_trib'], $simulacao->data_fato_gerador);
                $grupos[$chave]['fundamentacao'] = $this->dadosAbertos->fundamentacaoLegal($grupo['c_class_trib'], $simulacao->data_fato_gerador);
            } catch (CalculadoraIndisponivelException) {
                $indisponivel = true;
            }
        }

        return [array_values($grupos), $indisponivel];
    }
}
