<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusSimulacao;
use App\Integrations\Calculadora\Data\AliquotasNominais;
use Database\Factories\LinhaDoTempoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Linha do tempo 2026→2033 de uma simulação (uma simulação filha por ano).
 *
 * @property StatusSimulacao $status
 * @property numeric-string $aliquota_plena_cbs
 * @property numeric-string $aliquota_plena_ibs_estadual
 * @property numeric-string $aliquota_plena_ibs_municipal
 */
#[Table('linhas_do_tempo')]
#[Fillable(['user_id', 'simulacao_id', 'status', 'aliquota_plena_cbs', 'aliquota_plena_ibs_estadual', 'aliquota_plena_ibs_municipal', 'concluida_em'])]
class LinhaDoTempo extends Model
{
    /** @use HasFactory<LinhaDoTempoFactory> */
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
     * Simulação de origem (a que o usuário abriu).
     *
     * @return BelongsTo<Simulacao, $this>
     */
    public function simulacao(): BelongsTo
    {
        return $this->belongsTo(Simulacao::class);
    }

    /**
     * Uma simulação por ano.
     *
     * @return HasMany<Simulacao, $this>
     */
    public function simulacoesDosAnos(): HasMany
    {
        return $this->hasMany(Simulacao::class)->orderBy('data_fato_gerador');
    }

    public function aliquotasPlenas(): AliquotasNominais
    {
        $semZeros = fn (string $valor): string => mb_rtrim(mb_rtrim($valor, '0'), '.') ?: '0';

        return new AliquotasNominais(
            cbs: $semZeros($this->aliquota_plena_cbs),
            ibsEstadual: $semZeros($this->aliquota_plena_ibs_estadual),
            ibsMunicipal: $semZeros($this->aliquota_plena_ibs_municipal),
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status'                       => StatusSimulacao::class,
            'aliquota_plena_cbs'           => 'decimal:4',
            'aliquota_plena_ibs_estadual'  => 'decimal:4',
            'aliquota_plena_ibs_municipal' => 'decimal:4',
            'concluida_em'                 => 'datetime',
        ];
    }
}
