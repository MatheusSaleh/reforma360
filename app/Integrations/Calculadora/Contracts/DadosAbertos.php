<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Contracts;

use App\Enums\Uf;
use App\Integrations\Calculadora\Data\ClassificacaoTributaria;
use App\Integrations\Calculadora\Data\FundamentacaoLegal;
use App\Integrations\Calculadora\Data\Municipio;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use Carbon\CarbonImmutable;

/**
 * Tabelas de referência da calculadora (/calculadora/dados-abertos).
 */
interface DadosAbertos
{
    /**
     * @return list<Municipio>
     *
     * @throws CalculadoraIndisponivelException
     */
    public function municipios(Uf $uf): array;

    /**
     * @throws CalculadoraIndisponivelException
     */
    public function classificacaoTributaria(string $cClassTrib, CarbonImmutable $data): ?ClassificacaoTributaria;

    /**
     * @throws CalculadoraIndisponivelException
     */
    public function fundamentacaoLegal(string $cClassTrib, CarbonImmutable $data): ?FundamentacaoLegal;
}
