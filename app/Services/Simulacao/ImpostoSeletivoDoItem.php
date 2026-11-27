<?php

declare(strict_types=1);

namespace App\Services\Simulacao;

use App\Enums\TipoNomenclatura;
use App\Integrations\Calculadora\Contracts\DadosAbertos;
use App\Integrations\Calculadora\Data\AliquotasNominais;
use App\Integrations\Calculadora\Data\ErroCalculo;
use App\Integrations\Calculadora\Data\ImpostoSeletivoOperacao;
use App\Services\Simulacao\Data\DadosImpostoSeletivo;
use Carbon\CarbonImmutable;

/**
 * Decide se um item leva o grupo do Imposto Seletivo. A calculadora exige o grupo para NCMs
 * tributados na data (REG-013) e o rejeita em datas sem IS (REG-004); para NCMs não tributados
 * ela o aceitaria e somaria um IS indevido. Por isso a fonte é o dado aberto do NCM na data.
 */
final readonly class ImpostoSeletivoDoItem
{
    /** Erros da calculadora para item tributado pelo IS sem os dados ou sem a alíquota. */
    private const array ERROS_SEM_DADOS = ['REG-013', 'REG-035'];

    public const string MENSAGEM_SEM_DADOS = 'Item tributado pelo Imposto Seletivo nesta data: informe a alíquota estimada do IS (ad valorem e/ou ad rem).';

    public function __construct(private DadosAbertos $dadosAbertos) {}

    /**
     * Mensagem do app para os erros de IS sem dados; os demais erros seguem com o texto da calculadora.
     */
    public static function mensagem(ErroCalculo $erro): string
    {
        return in_array($erro->codigo, self::ERROS_SEM_DADOS, true) ? self::MENSAGEM_SEM_DADOS : $erro->detalhe;
    }

    public function tributado(?string $ncm, CarbonImmutable $data): bool
    {
        return $ncm !== null
            && $this->dadosAbertos->detalheNomenclatura(TipoNomenclatura::Ncm, $ncm, $data)?->tributadoPeloImpostoSeletivo === true;
    }

    /**
     * Grupo do IS a enviar, ou null quando o item não é tributado na data. Item tributado sem
     * dados do IS vai sem o grupo: a calculadora responde REG-013/REG-035 e o app explica.
     *
     * A base recebida é o valor do item sem o IS.
     */
    public function grupo(?string $ncm, CarbonImmutable $data, string $baseCalculo, ?DadosImpostoSeletivo $dados, ?AliquotasNominais $aliquotas): ?ImpostoSeletivoOperacao
    {
        if ($dados === null || $aliquotas === null || ! $this->tributado($ncm, $data)) {
            return null;
        }

        return $dados->paraOperacao($baseCalculo);
    }
}
