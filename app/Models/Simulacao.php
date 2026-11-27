<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusSimulacao;
use App\Enums\Uf;
use App\Integrations\Calculadora\Data\AliquotasNominais;
use Carbon\CarbonImmutable;
use Database\Factories\SimulacaoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Simulação do catálogo inteiro de uma empresa, processada em lote.
 *
 * @property StatusSimulacao $status
 * @property Uf $uf
 * @property CarbonImmutable $data_fato_gerador
 * @property numeric-string|null $aliquota_cbs
 * @property numeric-string|null $aliquota_ibs_estadual
 * @property numeric-string|null $aliquota_ibs_municipal
 * @property numeric-string|null $total_base_calculo
 * @property numeric-string|null $total_cbs
 * @property numeric-string|null $total_ibs
 * @property numeric-string|null $total_imposto_seletivo
 * @property numeric-string|null $total_tributos
 * @property CarbonImmutable|null $concluida_em
 */
#[Table('simulacoes')]
#[Fillable([
    'user_id', 'importacao_id', 'linha_do_tempo_id', 'status', 'data_fato_gerador', 'uf', 'municipio', 'aliquota_cbs', 'aliquota_ibs_estadual',
    'aliquota_ibs_municipal', 'total_itens', 'itens_processados', 'itens_com_erro', 'total_base_calculo',
    'total_cbs', 'total_ibs', 'total_imposto_seletivo', 'total_tributos', 'batch_id', 'iniciada_em', 'concluida_em',
])]
class Simulacao extends Model
{
    /** @use HasFactory<SimulacaoFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Empresa, $this>
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Nota importada que originou a simulação (nulo = catálogo inteiro).
     *
     * @return BelongsTo<Importacao, $this>
     */
    public function importacao(): BelongsTo
    {
        return $this->belongsTo(Importacao::class);
    }

    /**
     * Linha do tempo de que esta simulação é um dos anos (nulo = simulação principal).
     *
     * @return BelongsTo<LinhaDoTempo, $this>
     */
    public function linhaDoTempoPai(): BelongsTo
    {
        return $this->belongsTo(LinhaDoTempo::class, 'linha_do_tempo_id');
    }

    /**
     * Linha do tempo gerada a partir desta simulação.
     *
     * @return HasOne<LinhaDoTempo, $this>
     */
    public function linhaDoTempo(): HasOne
    {
        return $this->hasOne(LinhaDoTempo::class);
    }

    public function ehAnoDeLinhaDoTempo(): bool
    {
        return $this->linha_do_tempo_id !== null;
    }

    /**
     * Comparação com o Simples Nacional.
     *
     * @return HasOne<ComparacaoRegime, $this>
     */
    public function comparacao(): HasOne
    {
        return $this->hasOne(ComparacaoRegime::class);
    }

    /**
     * @return HasMany<SimulacaoItem, $this>
     */
    public function itens(): HasMany
    {
        return $this->hasMany(SimulacaoItem::class);
    }

    public function aliquotasNominais(): ?AliquotasNominais
    {
        if ($this->aliquota_cbs === null || $this->aliquota_ibs_estadual === null || $this->aliquota_ibs_municipal === null) {
            return null;
        }

        return new AliquotasNominais(
            cbs: self::semZerosFinais($this->aliquota_cbs),
            ibsEstadual: self::semZerosFinais($this->aliquota_ibs_estadual),
            ibsMunicipal: self::semZerosFinais($this->aliquota_ibs_municipal),
        );
    }

    public function percentualConcluido(): int
    {
        return $this->total_itens === 0 ? 0 : intdiv($this->itens_processados * 100, $this->total_itens);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status'                 => StatusSimulacao::class,
            'uf'                     => Uf::class,
            'data_fato_gerador'      => 'immutable_date',
            'aliquota_cbs'           => 'decimal:4',
            'aliquota_ibs_estadual'  => 'decimal:4',
            'aliquota_ibs_municipal' => 'decimal:4',
            'total_base_calculo'     => 'decimal:2',
            'total_cbs'              => 'decimal:2',
            'total_ibs'              => 'decimal:2',
            'total_imposto_seletivo' => 'decimal:2',
            'total_tributos'         => 'decimal:2',
            'iniciada_em'            => 'datetime',
            'concluida_em'           => 'datetime',
        ];
    }

    private static function semZerosFinais(string $valor): string
    {
        return str_contains($valor, '.') ? mb_rtrim(mb_rtrim($valor, '0'), '.') : $valor;
    }
}
