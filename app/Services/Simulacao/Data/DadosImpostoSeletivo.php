<?php

declare(strict_types=1);

namespace App\Services\Simulacao\Data;

use App\Integrations\Calculadora\Data\AliquotasImpostoSeletivo;
use App\Integrations\Calculadora\Data\ImpostoSeletivoOperacao;

/**
 * Dados do Imposto Seletivo de um produto ou item simulado. As alíquotas são estimativas do
 * usuário (ainda não definidas em lei). `quantidade` está na `unidade` do IS (ex.: 0.35 LT).
 */
final readonly class DadosImpostoSeletivo
{
    public const string CST_PADRAO = '000';

    public const string C_CLASS_TRIB_PADRAO = '000001';

    /** Colunas/campos `is_*` (produto, item simulado, formulários, planilha). */
    public const array CAMPOS = ['is_cst', 'is_c_class_trib', 'is_unidade', 'is_quantidade', 'is_aliquota_ad_valorem', 'is_aliquota_ad_rem'];

    /**
     * @param  numeric-string|null  $quantidade
     * @param  numeric-string|null  $aliquotaAdValorem
     * @param  numeric-string|null  $aliquotaAdRem
     */
    public function __construct(
        public string $cst = self::CST_PADRAO,
        public string $cClassTrib = self::C_CLASS_TRIB_PADRAO,
        public ?string $unidade = null,
        public ?string $quantidade = null,
        public ?string $aliquotaAdValorem = null,
        public ?string $aliquotaAdRem = null,
    ) {}

    /**
     * Monta a partir das colunas `is_*`; null quando não há alíquota informada.
     *
     * @param  array<string, mixed>  $campos
     */
    public static function deCampos(array $campos): ?self
    {
        $texto = fn (string $campo): ?string => is_scalar($campos[$campo] ?? null) && (string) $campos[$campo] !== '' ? (string) $campos[$campo] : null;

        /** @var numeric-string|null $adValorem */
        $adValorem = $texto('is_aliquota_ad_valorem');
        /** @var numeric-string|null $adRem */
        $adRem = $texto('is_aliquota_ad_rem');
        /** @var numeric-string|null $quantidade */
        $quantidade = $texto('is_quantidade');

        if ($adValorem === null && $adRem === null) {
            return null;
        }

        return new self(
            cst: $texto('is_cst') ?? self::CST_PADRAO,
            cClassTrib: $texto('is_c_class_trib') ?? self::C_CLASS_TRIB_PADRAO,
            unidade: $texto('is_unidade'),
            quantidade: $quantidade,
            aliquotaAdValorem: $adValorem,
            aliquotaAdRem: $adRem,
        );
    }

    /**
     * @return array{is_cst: string, is_c_class_trib: string, is_unidade: string|null, is_quantidade: string|null, is_aliquota_ad_valorem: string|null, is_aliquota_ad_rem: string|null}
     */
    public function campos(): array
    {
        return [
            'is_cst'                 => $this->cst,
            'is_c_class_trib'        => $this->cClassTrib,
            'is_unidade'             => $this->unidade,
            'is_quantidade'          => $this->quantidade,
            'is_aliquota_ad_valorem' => $this->aliquotaAdValorem,
            'is_aliquota_ad_rem'     => $this->aliquotaAdRem,
        ];
    }

    /** Monta o grupo com o valor do item sem o IS. */
    public function paraOperacao(string $baseCalculo): ImpostoSeletivoOperacao
    {
        return new ImpostoSeletivoOperacao(
            cst: $this->cst,
            cClassTrib: $this->cClassTrib,
            baseCalculo: $baseCalculo,
            aliquotas: new AliquotasImpostoSeletivo($this->aliquotaAdValorem, $this->aliquotaAdRem),
            quantidade: $this->quantidade,
            unidade: $this->unidade,
        );
    }
}
