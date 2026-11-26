<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('importacao_erros', timestamps: false)]
#[Fillable(['linha', 'campo', 'mensagem'])]
class ImportacaoErro extends Model
{
    /**
     * @return BelongsTo<Importacao, $this>
     */
    public function importacao(): BelongsTo
    {
        return $this->belongsTo(Importacao::class);
    }
}
