<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

/**
 * Uma parcela calculada (CBS, IBS estadual ou IBS municipal) de um item.
 * Alíquotas em % e valores em reais, como string decimal.
 */
final readonly class ParcelaTributo
{
    use LeCamposResposta;

    public function __construct(
        public string $aliquota,
        public string $valor,
        public ?string $percentualReducao = null,
        public ?string $aliquotaEfetiva = null,
    ) {}

    /**
     * @param  array<array-key, mixed>  $grupo  ex.: gCBS => ['pCBS' => '8.80', 'gRed' => [...], 'vCBS' => '42.24']
     */
    public static function fromGrupo(array $grupo, string $campoAliquota, string $campoValor): self
    {
        $reducao = self::grupoOpcional($grupo, 'gRed') ?? [];

        return new self(
            aliquota: self::texto($grupo, $campoAliquota),
            valor: self::texto($grupo, $campoValor),
            percentualReducao: self::textoOpcional($reducao, 'pRedAliq'),
            aliquotaEfetiva: self::textoOpcional($reducao, 'pAliqEfet'),
        );
    }
}
