<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Contracts;

use App\Enums\TipoNomenclatura;
use App\Enums\Uf;
use App\Integrations\Calculadora\Data\ClassificacaoTributaria;
use App\Integrations\Calculadora\Data\DetalheNomenclatura;
use App\Integrations\Calculadora\Data\FundamentacaoLegal;
use App\Integrations\Calculadora\Data\ItemNomenclatura;
use App\Integrations\Calculadora\Data\Municipio;
use App\Integrations\Calculadora\Data\SituacaoTributaria;
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

    /**
     * Todos os códigos NCM ou NBS vigentes na data.
     *
     * @return list<ItemNomenclatura>
     *
     * @throws CalculadoraIndisponivelException
     */
    public function nomenclaturas(TipoNomenclatura $tipo, CarbonImmutable $data): array;

    /**
     * @throws CalculadoraIndisponivelException
     */
    public function detalheNomenclatura(TipoNomenclatura $tipo, string $codigo, CarbonImmutable $data): ?DetalheNomenclatura;

    /**
     * CSTs de CBS/IBS com suas classificações tributárias.
     *
     * @return list<SituacaoTributaria>
     *
     * @throws CalculadoraIndisponivelException
     */
    public function situacoesTributarias(CarbonImmutable $data): array;
}
