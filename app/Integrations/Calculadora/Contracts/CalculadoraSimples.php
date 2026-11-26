<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Contracts;

use App\Integrations\Calculadora\Data\AtividadeSimples;
use App\Integrations\Calculadora\Data\OperacaoSimples;
use App\Integrations\Calculadora\Data\ResultadoItemSimples;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Integrations\Calculadora\Exceptions\PayloadInvalidoException;
use Carbon\CarbonImmutable;

/**
 * Calculadora do Simples Nacional (CBS/IBS dentro do Simples).
 */
interface CalculadoraSimples
{
    /**
     * Calcula item a item (observabilidade): erro de um item não impede os demais.
     *
     * @return list<ResultadoItemSimples>
     *
     * @throws PayloadInvalidoException quando a operação inteira é rejeitada (ex.: data antes de 2027)
     * @throws CalculadoraIndisponivelException
     */
    public function calcular(OperacaoSimples $operacao): array;

    /**
     * @return list<AtividadeSimples>
     *
     * @throws CalculadoraIndisponivelException
     */
    public function atividades(CarbonImmutable $data): array;
}
