<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Job que falhou (tabela failed_jobs do Laravel). Só leitura no painel; reprocessar e
 * descartar usam os comandos queue:retry e queue:forget.
 *
 * @property string $uuid
 * @property string $connection
 * @property string $queue
 * @property array<string, mixed> $payload
 * @property string $exception
 * @property Carbon $failed_at
 */
#[Table('failed_jobs', timestamps: false)]
class FailedJob extends Model
{
    public function nomeDoJob(): string
    {
        $nome = $this->payload['displayName'] ?? null;

        return is_string($nome) ? class_basename($nome) : 'Desconhecido';
    }

    public function resumoDoErro(): string
    {
        return Str::limit(mb_trim(Str::before($this->exception, "\n")), 160);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload'   => 'array',
            'failed_at' => 'datetime',
        ];
    }
}
