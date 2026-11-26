<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FormatoImportacao;
use App\Enums\StatusImportacao;
use Database\Factories\ImportacaoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Importação de catálogo a partir de um arquivo (CSV/XLSX).
 *
 * @property StatusImportacao $status
 * @property FormatoImportacao $formato
 */
#[Table('importacoes')]
#[Fillable([
    'user_id', 'nome_arquivo', 'caminho_arquivo', 'formato', 'status', 'total_linhas',
    'linhas_importadas', 'linhas_com_erro', 'mensagem_erro', 'iniciada_em', 'concluida_em',
])]
class Importacao extends Model
{
    /** @use HasFactory<ImportacaoFactory> */
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
     * @return HasMany<ImportacaoErro, $this>
     */
    public function erros(): HasMany
    {
        return $this->hasMany(ImportacaoErro::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status'       => StatusImportacao::class,
            'formato'      => FormatoImportacao::class,
            'iniciada_em'  => 'datetime',
            'concluida_em' => 'datetime',
        ];
    }
}
