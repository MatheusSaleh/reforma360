<?php

declare(strict_types=1);

namespace App\Services\Simulacao;

use App\Enums\EstadoItemCalculo;
use App\Enums\StatusSimulacao;
use App\Integrations\Calculadora\Contracts\CalculadoraTributos;
use App\Integrations\Calculadora\Data\ErroCalculo;
use App\Integrations\Calculadora\Data\ParcelaTributo;
use App\Integrations\Calculadora\Data\ResultadoItem;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Integrations\Calculadora\Exceptions\PayloadInvalidoException;
use App\Integrations\Calculadora\Exceptions\RespostaInesperadaException;
use App\Models\Simulacao;
use App\Models\SimulacaoItem;
use Illuminate\Support\Facades\DB;

/**
 * Simula um bloco de itens de uma simulação em lote com uma única chamada à calculadora
 * (endpoint de observabilidade: cada item tem seu próprio estado e erros).
 */
final readonly class SimularLoteService
{
    public function __construct(
        private CalculadoraTributos $calculadora,
    ) {}

    /**
     * @param  list<int>  $idsDosItens
     *
     * @throws CalculadoraIndisponivelException transitório: o job tenta de novo
     */
    public function processar(Simulacao $simulacao, array $idsDosItens): void
    {
        Simulacao::query()->whereKey($simulacao->id)->where('status', StatusSimulacao::Pendente)
            ->update(['status' => StatusSimulacao::Processando, 'iniciada_em' => now()]);

        // Itens já processados (retry depois de sucesso parcial) não são simulados de novo.
        $itens = array_values($simulacao->itens()->whereKey($idsDosItens)->whereNull('estado')->orderBy('id')->get()->all());

        if ($itens === []) {
            return;
        }

        $resultados = $this->simular($simulacao, $itens);

        DB::transaction(function () use ($simulacao, $itens, $resultados): void {
            $comErro = 0;

            foreach ($itens as $indice => $item) {
                $dados = $resultados[$indice];
                $item->update($dados);

                if (! EstadoItemCalculo::from($dados['estado'])->foiCalculado()) {
                    $comErro++;
                }
            }

            Simulacao::query()->whereKey($simulacao->id)->incrementEach([
                'itens_processados' => count($itens),
                'itens_com_erro'    => $comErro,
            ]);
        });
    }

    /**
     * @param  list<SimulacaoItem>  $itens
     * @return list<array<string, mixed>> dados para gravar em cada item, na mesma ordem
     */
    private function simular(Simulacao $simulacao, array $itens): array
    {
        try {
            $resultado = $this->calculadora->calcular(OperacaoDaSimulacao::montar($simulacao, $itens));
        } catch (PayloadInvalidoException $e) {
            return array_fill(0, count($itens), self::erro(EstadoItemCalculo::InconsistenciaEntrada, $e->erros, $e->getMessage()));
        } catch (RespostaInesperadaException $e) {
            return array_fill(0, count($itens), self::erro(EstadoItemCalculo::FalhaTecnica, [], 'Resposta inesperada da calculadora.'));
        }

        $dados = [];

        foreach (array_keys($itens) as $indice) {
            $item = $resultado->item($indice + 1);

            $dados[] = $item === null
                ? self::erro(EstadoItemCalculo::FalhaTecnica, [], 'A calculadora não devolveu este item.')
                : self::resultado($item);
        }

        return $dados;
    }

    /**
     * @return array<string, mixed>
     */
    private static function resultado(ResultadoItem $item): array
    {
        $ibsCbs = $item->ibsCbs;

        if (! $item->foiCalculado() || $ibsCbs === null) {
            return self::erro($item->estado, $item->erros, "Item não calculado ({$item->estado->label()}).");
        }

        $valorIs = $item->impostoSeletivo?->valor;
        $total = bcadd($ibsCbs->cbs->valor, $ibsCbs->valorIbs, 2);

        if ($valorIs !== null) {
            $total = bcadd($total, $valorIs, 2);
        }

        return [
            'estado'                 => $item->estado->value,
            'valor_cbs'              => $ibsCbs->cbs->valor,
            'valor_ibs'              => $ibsCbs->valorIbs,
            'valor_imposto_seletivo' => $valorIs,
            'total_tributos'         => $total,
            'percentual_reducao'     => $ibsCbs->cbs->percentualReducao,
            'erros'                  => null,
            'detalhes'               => [
                'cbs'           => self::parcela($ibsCbs->cbs),
                'ibs_estadual'  => self::parcela($ibsCbs->ibsEstadual),
                'ibs_municipal' => self::parcela($ibsCbs->ibsMunicipal),
                'memoria'       => array_filter([
                    'cbs'           => $ibsCbs->memoriaCbs,
                    'ibs_estadual'  => $ibsCbs->memoriaIbsEstadual,
                    'ibs_municipal' => $ibsCbs->memoriaIbsMunicipal,
                ]),
                'imposto_seletivo' => $item->impostoSeletivo === null ? null : [
                    'aliquota' => $item->impostoSeletivo->aliquota,
                    'base'     => $item->impostoSeletivo->baseCalculo,
                    'valor'    => $item->impostoSeletivo->valor,
                ],
            ],
        ];
    }

    /**
     * @return array{aliquota: string, reducao: string|null, aliquota_efetiva: string, valor: string}
     */
    private static function parcela(ParcelaTributo $parcela): array
    {
        return [
            'aliquota'         => $parcela->aliquota,
            'reducao'          => $parcela->percentualReducao,
            'aliquota_efetiva' => $parcela->aliquotaEfetiva ?? $parcela->aliquota,
            'valor'            => $parcela->valor,
        ];
    }

    /**
     * @param  list<ErroCalculo>  $erros
     * @return array<string, mixed>
     */
    private static function erro(EstadoItemCalculo $estado, array $erros, string $mensagemPadrao): array
    {
        $lista = array_map(fn (ErroCalculo $erro): array => ['codigo' => $erro->codigo, 'mensagem' => $erro->detalhe], $erros);

        return [
            'estado'                 => $estado->value,
            'valor_cbs'              => null,
            'valor_ibs'              => null,
            'valor_imposto_seletivo' => null,
            'total_tributos'         => null,
            'percentual_reducao'     => null,
            'detalhes'               => null,
            'erros'                  => $lista === [] ? [['codigo' => null, 'mensagem' => $mensagemPadrao]] : $lista,
        ];
    }
}
