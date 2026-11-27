<?php

declare(strict_types=1);

namespace App\Services\Simulacao;

use App\Enums\EstadoItemCalculo;
use App\Enums\TipoDocumentoFiscal;
use App\Integrations\Calculadora\Contracts\CalculadoraSimples;
use App\Integrations\Calculadora\Data\AtividadeSimples;
use App\Integrations\Calculadora\Data\ErroCalculo;
use App\Integrations\Calculadora\Data\ItemSimples;
use App\Integrations\Calculadora\Data\OperacaoSimples;
use App\Integrations\Calculadora\Data\ResultadoItemSimples;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Integrations\Calculadora\Exceptions\PayloadInvalidoException;
use App\Models\ComparacaoRegime;
use App\Models\Simulacao;
use App\Models\SimulacaoItem;
use App\Models\User;
use App\Services\Simulacao\Data\ParametrosSimples;
use App\Support\NormalizadorFiscal;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Compara os itens calculados de uma simulação (regime regular de IBS/CBS) com o
 * Simples Nacional, calculado pela calculadora do Simples com as alíquotas efetivas
 * informadas pelo usuário. Só mercadorias (NF-e/NFC-e) e fato gerador a partir de 2027,
 * limites da calculadora do Simples.
 *
 * O crédito das compras no regime regular é uma estimativa simplificada: valor das
 * compras × alíquotas nominais do ano.
 */
final readonly class CompararRegimesService
{
    public function __construct(
        private CalculadoraSimples $simples,
        #[Config('simulacao.itens_por_chamada')]
        private int $itensPorChamada = 50,
    ) {}

    /**
     * Atividades do Simples aceitas nos documentos de mercadorias (NF-e/NFC-e).
     *
     * @return list<AtividadeSimples>
     *
     * @throws CalculadoraIndisponivelException
     */
    public function atividades(Simulacao $simulacao): array
    {
        return array_values(array_filter(
            $this->simples->atividades($simulacao->data_fato_gerador),
            fn (AtividadeSimples $atividade): bool => in_array($this->tipoDocumento($simulacao), $atividade->tiposDocumento, true),
        ));
    }

    /**
     * Motivo de a simulação não poder ser comparada, ou null.
     */
    public function impedimento(Simulacao $simulacao): ?string
    {
        return match (true) {
            $simulacao->status->emAndamento()                                     => 'Aguarde a simulação terminar para comparar.',
            $simulacao->data_fato_gerador->year < 2027                            => 'A calculadora do Simples Nacional da Receita só calcula operações a partir de 2027. Simule o catálogo com uma data a partir de 2027 para comparar.',
            $simulacao->importacao?->tipo_documento === TipoDocumentoFiscal::Nfse => 'A calculadora do Simples Nacional ainda não calcula NFS-e.',
            $simulacao->itens_processados <= $simulacao->itens_com_erro           => 'A simulação não tem itens calculados para comparar.',
            default                                                               => null,
        };
    }

    /**
     * @throws ValidationException
     * @throws CalculadoraIndisponivelException
     */
    public function comparar(User $usuario, Simulacao $simulacao, ParametrosSimples $parametros): ComparacaoRegime
    {
        $impedimento = $this->impedimento($simulacao);

        if ($impedimento !== null) {
            throw ValidationException::withMessages(['comparacao' => $impedimento]);
        }

        $itens = array_values($simulacao->itens()
            ->whereIn('estado', [EstadoItemCalculo::Calculado, EstadoItemCalculo::CalculoSimulado])
            ->orderBy('id')
            ->get()
            ->all());

        $resultados = $this->calcularNoSimples($simulacao, $parametros, $itens);

        return DB::transaction(fn (): ComparacaoRegime => $this->gravar($usuario, $simulacao, $parametros, $itens, $resultados));
    }

    /**
     * @param  list<SimulacaoItem>  $itens
     * @return array<int, ResultadoItemSimples|string> por id do item: resultado ou motivo de não comparar
     */
    private function calcularNoSimples(Simulacao $simulacao, ParametrosSimples $parametros, array $itens): array
    {
        $resultados = [];
        $mercadorias = [];

        foreach ($itens as $item) {
            if ($item->ncm === null) {
                $resultados[$item->id] = 'Serviços (NBS) ainda não são calculados pela calculadora do Simples Nacional em NF-e/NFC-e.';
            } else {
                $mercadorias[] = $item;
            }
        }

        foreach (array_chunk($mercadorias, max(1, $this->itensPorChamada)) as $bloco) {
            $operacao = new OperacaoSimples(
                id: 'comparacao-'.$simulacao->id.'-'.Str::lower(Str::random(8)),
                data: $simulacao->data_fato_gerador,
                itens: array_map(fn (int $indice, SimulacaoItem $item): ItemSimples => new ItemSimples(
                    numero: $indice + 1,
                    atividade: $parametros->atividade,
                    cClassTrib: $item->c_class_trib,
                    baseCalculo: $item->base_calculo,
                    aliquotaCbs: $parametros->aliquotaCbs,
                    aliquotaIbs: $parametros->aliquotaIbs,
                    ncm: $item->ncm,
                    valorImpostoSeletivo: $item->valor_imposto_seletivo ?? '0.00',
                ), array_keys($bloco), $bloco),
                tipoDocumento: $this->tipoDocumento($simulacao),
            );

            try {
                $porNumero = [];

                foreach ($this->simples->calcular($operacao) as $resultado) {
                    $porNumero[$resultado->numero] = $resultado;
                }
            } catch (PayloadInvalidoException $e) {
                $mensagem = $e->erros[0]->detalhe ?? $e->getMessage();
                $porNumero = [];

                foreach (array_keys($bloco) as $indice) {
                    $porNumero[$indice + 1] = $mensagem;
                }
            }

            foreach ($bloco as $indice => $item) {
                $resultados[$item->id] = $porNumero[$indice + 1] ?? 'A calculadora do Simples não devolveu este item.';
            }
        }

        return $resultados;
    }

    /**
     * @param  list<SimulacaoItem>  $itens
     * @param  array<int, ResultadoItemSimples|string>  $resultados
     */
    private function gravar(User $usuario, Simulacao $simulacao, ParametrosSimples $parametros, array $itens, array $resultados): ComparacaoRegime
    {
        $simulacao->comparacao()->delete();

        $totais = ['base' => '0', 'simples_cbs' => '0', 'simples_ibs' => '0', 'regular_cbs' => '0', 'regular_ibs' => '0', 'is' => '0'];
        $linhas = [];
        $comparados = 0;

        foreach ($itens as $item) {
            $resultado = $resultados[$item->id];
            $calculado = $resultado instanceof ResultadoItemSimples && $resultado->foiCalculado();

            $linhas[] = [
                'simulacao_item_id'      => $item->id,
                'estado'                 => $resultado instanceof ResultadoItemSimples ? $resultado->estado->value : EstadoItemCalculo::NaoImplementado->value,
                'simples_cbs'            => $calculado ? $resultado->valorCbs : null,
                'simples_ibs'            => $calculado ? $resultado->valorIbs : null,
                'valor_imposto_seletivo' => $calculado ? $item->valor_imposto_seletivo : null,
                'erros'                  => $calculado ? null : json_encode(self::erros($resultado), JSON_UNESCAPED_UNICODE),
            ];

            if ($calculado && $resultado->valorCbs !== null && $resultado->valorIbs !== null) {
                $comparados++;
                $totais['base'] = bcadd($totais['base'], $item->base_calculo, 2);
                $totais['simples_cbs'] = bcadd($totais['simples_cbs'], $resultado->valorCbs, 2);
                $totais['simples_ibs'] = bcadd($totais['simples_ibs'], $resultado->valorIbs, 2);
                $totais['regular_cbs'] = bcadd($totais['regular_cbs'], $item->valor_cbs ?? '0', 2);
                $totais['regular_ibs'] = bcadd($totais['regular_ibs'], $item->valor_ibs ?? '0', 2);
                $totais['is'] = bcadd($totais['is'], $item->valor_imposto_seletivo ?? '0', 2);
            }
        }

        $comparacao = $simulacao->empresa->comparacoesRegime()->create([
            'user_id'                => $usuario->id,
            'simulacao_id'           => $simulacao->id,
            'atividade'              => $parametros->atividade,
            'aliquota_cbs_simples'   => $parametros->aliquotaCbs,
            'aliquota_ibs_simples'   => $parametros->aliquotaIbs,
            'compras'                => $parametros->compras,
            'itens_comparados'       => $comparados,
            'itens_nao_comparados'   => count($itens) - $comparados,
            'base_calculo'           => $totais['base'],
            'simples_cbs'            => $totais['simples_cbs'],
            'simples_ibs'            => $totais['simples_ibs'],
            'regular_cbs'            => $totais['regular_cbs'],
            'regular_ibs'            => $totais['regular_ibs'],
            'valor_imposto_seletivo' => $totais['is'],
            'credito_estimado'       => $this->creditoEstimado($simulacao, $parametros->compras),
        ]);

        foreach (array_chunk($linhas, 500) as $lote) {
            $comparacao->itens()->insert(array_map(fn (array $linha): array => ['comparacao_id' => $comparacao->id, ...$linha], $lote));
        }

        return $comparacao;
    }

    /**
     * Compras × (CBS + IBS nominais do ano) / 100, arredondado para centavos.
     */
    private function creditoEstimado(Simulacao $simulacao, ?string $compras): string
    {
        $aliquotas = $simulacao->aliquotasNominais();

        if ($compras === null || ! is_numeric($compras) || $aliquotas === null
            || ! is_numeric($aliquotas->cbs) || ! is_numeric($aliquotas->ibsEstadual) || ! is_numeric($aliquotas->ibsMunicipal)) {
            return '0.00';
        }

        $aliquotaTotal = bcadd(bcadd($aliquotas->cbs, $aliquotas->ibsEstadual, 4), $aliquotas->ibsMunicipal, 4);

        return NormalizadorFiscal::arredondar(bcdiv(bcmul($compras, $aliquotaTotal, 6), '100', 6), 2);
    }

    private function tipoDocumento(Simulacao $simulacao): string
    {
        return $simulacao->importacao?->tipo_documento === TipoDocumentoFiscal::Nfce ? '65' : '55';
    }

    /**
     * @return list<array{codigo: string|null, mensagem: string}>
     */
    private static function erros(ResultadoItemSimples|string $resultado): array
    {
        if (is_string($resultado)) {
            return [['codigo' => null, 'mensagem' => $resultado]];
        }

        $erros = array_map(fn (ErroCalculo $erro): array => ['codigo' => $erro->codigo, 'mensagem' => $erro->detalhe], $resultado->erros);

        return $erros === [] ? [['codigo' => null, 'mensagem' => "Item não calculado no Simples ({$resultado->estado->label()})."]] : $erros;
    }
}
