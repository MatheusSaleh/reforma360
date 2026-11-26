<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Contracts;

use App\Integrations\Calculadora\Data\Operacao;
use App\Integrations\Calculadora\Data\ResultadoOperacao;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Integrations\Calculadora\Exceptions\PayloadInvalidoException;
use App\Integrations\Calculadora\Exceptions\RespostaInesperadaException;

/**
 * Cálculo de CBS, IBS e IS de uma operação no regime regular.
 */
interface CalculadoraTributos
{
    /**
     * Calcula a operação item a item: um item com erro não impede o cálculo dos demais
     * (veja ResultadoItem::$erros).
     *
     * @throws PayloadInvalidoException quando a operação inteira é rejeitada (4xx)
     * @throws CalculadoraIndisponivelException quando a calculadora não responde (conexão, timeout, 5xx)
     * @throws RespostaInesperadaException quando a resposta não tem o formato esperado
     */
    public function calcular(Operacao $operacao): ResultadoOperacao;
}
