<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoItemCalculo;
use Database\Factories\SimulacaoItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Item de uma simulação em lote: retrato do produto + resultado da calculadora.
 * estado null = ainda não processado.
 *
 * @property EstadoItemCalculo|null $estado
 * @property numeric-string $base_calculo
 * @property numeric-string|null $valor_cbs
 * @property numeric-string|null $valor_ibs
 * @property numeric-string|null $valor_imposto_seletivo
 * @property numeric-string|null $total_tributos
 * @property numeric-string|null $percentual_reducao
 * @property array<string, mixed>|null $detalhes
 * @property list<array{codigo: string|null, mensagem: string}>|null $erros
 */
#[Table('simulacao_itens')]
#[Fillable([
    'produto_id', 'origem_id', 'descricao', 'ncm', 'nbs', 'cst', 'c_class_trib', 'base_calculo', 'estado', 'valor_cbs',
    'valor_ibs', 'valor_imposto_seletivo', 'total_tributos', 'percentual_reducao', 'detalhes', 'erros',
])]
class SimulacaoItem extends Model
{
    /** @use HasFactory<SimulacaoItemFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Simulacao, $this>
     */
    public function simulacao(): BelongsTo
    {
        return $this->belongsTo(Simulacao::class);
    }

    public function temErro(): bool
    {
        return $this->estado !== null && ! $this->estado->foiCalculado();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estado'                 => EstadoItemCalculo::class,
            'base_calculo'           => 'decimal:2',
            'valor_cbs'              => 'decimal:2',
            'valor_ibs'              => 'decimal:2',
            'valor_imposto_seletivo' => 'decimal:2',
            'total_tributos'         => 'decimal:2',
            'percentual_reducao'     => 'decimal:4',
            'detalhes'               => 'array',
            'erros'                  => 'array',
        ];
    }
}
