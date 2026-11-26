<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Contracts;

use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Integrations\Calculadora\Exceptions\DanfseRejeitadoException;

/**
 * DANFSe (PDF) da NFS-e, gerado pelo serviço da Receita.
 */
interface GeradorDanfse
{
    /**
     * @return string conteúdo do PDF
     *
     * @throws DanfseRejeitadoException quando o XML não é uma NFS-e aceita
     * @throws CalculadoraIndisponivelException
     */
    public function gerar(string $xmlDaNfse): string;
}
