<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Contracts;

use App\Enums\TipoDocumentoFiscal;
use App\Integrations\Calculadora\Data\Operacao;
use App\Integrations\Calculadora\Data\ResultadoValidacaoXml;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Integrations\Calculadora\Exceptions\PayloadInvalidoException;

/**
 * XML dos grupos de tributação (IBS/CBS/IS) gerado e validado pela calculadora.
 */
interface XmlTributacao
{
    /**
     * Calcula a operação (falha no primeiro item com erro) e gera o XML do grupo
     * de tributação no leiaute do documento.
     *
     * @throws PayloadInvalidoException quando a operação ou o tipo é rejeitado
     * @throws CalculadoraIndisponivelException
     */
    public function gerarGrupo(Operacao $operacao, TipoDocumentoFiscal $tipo): string;

    /**
     * @param  'grupo'|'nota'  $subtipo
     *
     * @throws CalculadoraIndisponivelException
     */
    public function validar(string $xml, TipoDocumentoFiscal $tipo, string $subtipo = 'grupo'): ResultadoValidacaoXml;
}
