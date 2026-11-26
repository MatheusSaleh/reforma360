<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

use App\Enums\EstadoItemCalculo;

/**
 * Resultado de uma operação calculada com observabilidade.
 */
final readonly class ResultadoOperacao
{
    use LeCamposResposta;

    /**
     * @param  list<ResultadoItem>  $itens
     * @param  TotalOperacao|null  $total  ausente quando nenhum item foi calculado
     * @param  string|null  $avisoDadosSimulados  header x-warning-dados-simulados: presente quando o cálculo é simulado
     */
    public function __construct(
        public array $itens,
        public ?TotalOperacao $total,
        public ?string $avisoDadosSimulados = null,
        public ?string $versaoApp = null,
        public ?string $versaoBaseDados = null,
    ) {}

    /**
     * @param  array<array-key, mixed>  $dados
     */
    public static function fromArray(
        array $dados,
        ?string $avisoDadosSimulados = null,
        ?string $versaoApp = null,
        ?string $versaoBaseDados = null,
    ): self {
        $total = self::grupoOpcional($dados, 'total');

        return new self(
            itens: array_map(ResultadoItem::fromArray(...), self::listaDeGrupos($dados, 'objetos')),
            total: $total === null ? null : TotalOperacao::fromArray($total),
            avisoDadosSimulados: $avisoDadosSimulados,
            versaoApp: $versaoApp,
            versaoBaseDados: $versaoBaseDados,
        );
    }

    public function item(int $numero): ?ResultadoItem
    {
        foreach ($this->itens as $item) {
            if ($item->numero === $numero) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @return list<ResultadoItem>
     */
    public function itensComErro(): array
    {
        return array_values(array_filter($this->itens, fn (ResultadoItem $item): bool => ! $item->foiCalculado()));
    }

    public function ehSimulado(): bool
    {
        if ($this->avisoDadosSimulados !== null) {
            return true;
        }

        foreach ($this->itens as $item) {
            if ($item->estado === EstadoItemCalculo::CalculoSimulado) {
                return true;
            }
        }

        return false;
    }
}
