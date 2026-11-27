<?php

declare(strict_types=1);

namespace App\Rules\Catalogo;

use App\Services\Simulacao\Data\DadosImpostoSeletivo;
use App\Support\NormalizadorFiscal;

/**
 * Validação e normalização dos campos `is_*` do Imposto Seletivo, usadas pelo cadastro de
 * produto, pela importação e pelo simulador de item. Sem alíquota, os dados do IS não valem.
 */
final class RegrasImpostoSeletivo
{
    /**
     * @return array<string, list<string>>
     */
    public static function regras(): array
    {
        return [
            'is_aliquota_ad_valorem' => ['bail', 'nullable', 'numeric', 'decimal:0,4', 'regex:/^\d+(?:\.\d+)?$/D', 'min:0', 'max:100'],
            'is_aliquota_ad_rem'     => ['bail', 'nullable', 'numeric', 'decimal:0,4', 'regex:/^\d+(?:\.\d+)?$/D', 'min:0', 'max:99999999999.9999'],
            'is_unidade'             => ['bail', 'nullable', 'required_with:is_aliquota_ad_rem', 'string', 'max:6', 'regex:/^[A-Z0-9]+$/'],
            'is_quantidade'          => ['bail', 'nullable', 'required_with:is_aliquota_ad_rem', 'numeric', 'decimal:0,4', 'regex:/^\d+(?:\.\d+)?$/D', 'gt:0', 'max:99999999999.9999'],
            'is_cst'                 => ['bail', 'nullable', 'digits:3'],
            'is_c_class_trib'        => ['bail', 'nullable', 'digits:6'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function mensagens(): array
    {
        return [
            'is_aliquota_ad_valorem.decimal' => 'A alíquota ad valorem do IS deve ter no máximo 4 casas decimais.',
            'is_aliquota_ad_valorem.max'     => 'A alíquota ad valorem do IS deve ser no máximo 100%.',
            'is_aliquota_ad_rem.decimal'     => 'A alíquota ad rem do IS deve ter no máximo 4 casas decimais.',
            'is_unidade.required_with'       => 'Informe a unidade do IS (ex.: LT) para usar a alíquota ad rem.',
            'is_unidade.regex'               => 'A unidade do IS deve ter só letras maiúsculas e números (ex.: LT, KG, UN).',
            'is_quantidade.required_with'    => 'Informe a quantidade na unidade do IS para usar a alíquota ad rem.',
            'is_quantidade.gt'               => 'A quantidade do IS deve ser maior que zero.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function atributos(): array
    {
        return [
            'is_aliquota_ad_valorem' => 'alíquota ad valorem do IS',
            'is_aliquota_ad_rem'     => 'alíquota ad rem do IS',
            'is_unidade'             => 'unidade do IS',
            'is_quantidade'          => 'quantidade do IS',
            'is_cst'                 => 'CST do IS',
            'is_c_class_trib'        => 'cClassTrib do IS',
        ];
    }

    /**
     * Descrição dos campos para a documentação da API (Scribe).
     *
     * @return array<string, array{description: string, example: mixed}>
     */
    public static function documentacao(): array
    {
        return [
            'is_aliquota_ad_valorem' => ['description' => 'Imposto Seletivo (a partir de 2027, só para NCMs tributados): alíquota ad valorem estimada em %.', 'example' => null],
            'is_aliquota_ad_rem'     => ['description' => 'Imposto Seletivo: alíquota ad rem estimada, em R$ por unidade do IS. Exige is_unidade e is_quantidade.', 'example' => null],
            'is_unidade'             => ['description' => 'Unidade do IS (ex.: LT, VN, KG).', 'example' => null],
            'is_quantidade'          => ['description' => 'Quantidade na unidade do IS (no produto, por unidade vendida; ex.: 0.35 LT por lata).', 'example' => null],
            'is_cst'                 => ['description' => 'CST do IS (3 dígitos). Padrão 000.', 'example' => null],
            'is_c_class_trib'        => ['description' => 'cClassTrib do IS (6 dígitos). Padrão 000001.', 'example' => null],
        ];
    }

    /**
     * Normaliza a entrada antes da validação: decimais pt-BR, códigos só com dígitos, unidade maiúscula.
     *
     * @param  array<string, mixed>  $campos
     * @return array<string, string|null>
     */
    public static function normalizar(array $campos): array
    {
        $texto = fn (string $campo): ?string => is_scalar($campos[$campo] ?? null) && mb_trim((string) $campos[$campo]) !== '' ? mb_trim((string) $campos[$campo]) : null;
        $normalizados = [];

        foreach (DadosImpostoSeletivo::CAMPOS as $campo) {
            $valor = $texto($campo);

            $normalizados[$campo] = match (true) {
                $valor === null                                       => null,
                in_array($campo, ['is_cst', 'is_c_class_trib'], true) => NormalizadorFiscal::codigo($valor, $campo === 'is_cst' ? 3 : 6),
                $campo === 'is_unidade'                               => mb_strtoupper($valor),
                default                                               => NormalizadorFiscal::decimal($valor),
            };
        }

        return $normalizados;
    }

    /**
     * Valores a gravar depois de validados: sem alíquota, tudo nulo; com alíquota, CST e
     * cClassTrib do IS caem no padrão (000 / 000001) quando vazios.
     *
     * @param  array<string, mixed>  $validados
     * @return array<string, string|null>
     */
    public static function paraGravar(array $validados): array
    {
        return DadosImpostoSeletivo::deCampos($validados)?->campos() ?? array_fill_keys(DadosImpostoSeletivo::CAMPOS, null);
    }
}
