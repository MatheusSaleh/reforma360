<?php

declare(strict_types=1);

namespace App\Services\Simulacao;

use App\Integrations\Calculadora\Contracts\CalculadoraTributos;
use App\Integrations\Calculadora\Contracts\DadosAbertos;
use App\Integrations\Calculadora\Data\AliquotasNominais;
use App\Integrations\Calculadora\Data\ErroCalculo;
use App\Integrations\Calculadora\Data\ItemOperacao;
use App\Integrations\Calculadora\Data\Operacao;
use App\Integrations\Calculadora\Exceptions\PayloadInvalidoException;
use App\Integrations\Calculadora\Exceptions\RespostaInesperadaException;
use App\Services\Simulacao\Data\DadosSimulacaoItem;
use App\Services\Simulacao\Data\SimulacaoItem;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Simula CBS/IBS de um único item na calculadora do regime regular.
 */
final readonly class SimularItemService
{
    /**
     * Campo da API (último segmento do "pointer" do erro) => campo do formulário.
     */
    private const array CAMPOS_DO_FORMULARIO = [
        'ncm'           => 'ncm',
        'nbs'           => 'nbs',
        'cst'           => 'cst',
        'cClassTrib'    => 'c_class_trib',
        'baseCalculo'   => 'valor',
        'uf'            => 'uf',
        'municipio'     => 'municipio',
        'dhFatoGerador' => 'data',
        'cbs'           => 'aliquota_cbs',
        'ibsEstadual'   => 'aliquota_ibs_estadual',
        'ibsMunicipal'  => 'aliquota_ibs_municipal',
    ];

    public function __construct(
        private CalculadoraTributos $calculadora,
        private DadosAbertos $dadosAbertos,
        #[Config('simulacao.ano_inicio_aliquotas_nominais')]
        private int $anoInicioAliquotasNominais,
    ) {}

    /**
     * @throws ValidationException quando a calculadora rejeita a entrada ou não calcula o item
     */
    public function simular(DadosSimulacaoItem $dados): SimulacaoItem
    {
        $aliquotas = $this->aliquotasNominais($dados);

        try {
            $resultado = $this->calculadora->calcular($this->operacao($dados, $aliquotas));
        } catch (PayloadInvalidoException $e) {
            throw $this->erroDeValidacao($e->erros, $e->getMessage());
        }

        $item = $resultado->item(1) ?? throw RespostaInesperadaException::campoAusente('objetos');

        if (! $item->foiCalculado() || $item->ibsCbs === null) {
            throw $this->erroDeValidacao($item->erros, "A calculadora não calculou o item ({$item->estado->label()}).");
        }

        $totalTributos = bcadd($item->ibsCbs->cbs->valor, $item->ibsCbs->valorIbs, 2);

        if ($item->impostoSeletivo !== null) {
            $totalTributos = bcadd($totalTributos, $item->impostoSeletivo->valor, 2);
        }

        return new SimulacaoItem(
            entrada: $dados,
            ibsCbs: $item->ibsCbs,
            totalTributos: $totalTributos,
            usouAliquotasInformadas: $aliquotas !== null,
            impostoSeletivo: $item->impostoSeletivo,
            classificacao: $this->dadosAbertos->classificacaoTributaria($dados->cClassTrib, $dados->data),
            fundamentacao: $this->dadosAbertos->fundamentacaoLegal($dados->cClassTrib, $dados->data),
            versaoBaseDados: $resultado->versaoBaseDados,
        );
    }

    public function exigeAliquotasNominais(int $ano): bool
    {
        return $ano >= $this->anoInicioAliquotasNominais;
    }

    /**
     * A calculadora proíbe alíquotas nominais antes do ano de início e as exige depois.
     */
    private function aliquotasNominais(DadosSimulacaoItem $dados): ?AliquotasNominais
    {
        return $this->exigeAliquotasNominais($dados->data->year) ? $dados->aliquotasNominais : null;
    }

    private function operacao(DadosSimulacaoItem $dados, ?AliquotasNominais $aliquotas): Operacao
    {
        return new Operacao(
            id: (string) Str::uuid(),
            dhFatoGerador: $dados->data,
            municipio: $dados->municipio,
            uf: $dados->uf->value,
            itens: [
                new ItemOperacao(
                    numero: 1,
                    cst: $dados->cst,
                    cClassTrib: $dados->cClassTrib,
                    baseCalculo: $dados->valor,
                    ncm: $dados->ncm,
                    nbs: $dados->nbs,
                    aliquotasNominais: $aliquotas,
                ),
            ],
        );
    }

    /**
     * @param  list<ErroCalculo>  $erros
     */
    private function erroDeValidacao(array $erros, string $mensagemPadrao): ValidationException
    {
        $mensagens = [];

        foreach ($erros as $erro) {
            $mensagens[$this->campoDoFormulario($erro)][] = $erro->detalhe;
        }

        return ValidationException::withMessages($mensagens === [] ? ['simulacao' => $mensagemPadrao] : $mensagens);
    }

    private function campoDoFormulario(ErroCalculo $erro): string
    {
        $campoDaApi = Str::afterLast((string) $erro->campo, '/');

        return self::CAMPOS_DO_FORMULARIO[$campoDaApi] ?? 'simulacao';
    }
}
