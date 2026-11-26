<?php

declare(strict_types=1);

namespace App\Actions\Importacao;

use App\Enums\FormatoImportacao;
use App\Enums\StatusImportacao;
use App\Jobs\ProcessarImportacaoJob;
use App\Models\Importacao;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Guarda o arquivo no disco privado, registra a importação e enfileira o processamento.
 */
final class IniciarImportacao
{
    public function handle(User $usuario, UploadedFile $arquivo): Importacao
    {
        $extensao = mb_strtolower($arquivo->getClientOriginalExtension());
        $formato = FormatoImportacao::daExtensao($extensao)
            ?? throw new InvalidArgumentException("Formato não suportado: {$extensao}.");

        $caminho = $arquivo->storeAs('importacoes', Str::uuid()->toString().'.'.$formato->value, 'local');

        $importacao = $usuario->empresa->importacoes()->create([
            'user_id'         => $usuario->id,
            'nome_arquivo'    => Str::limit($arquivo->getClientOriginalName(), 250, ''),
            'caminho_arquivo' => $caminho,
            'formato'         => $formato,
            'status'          => StatusImportacao::Pendente,
        ]);

        ProcessarImportacaoJob::dispatch($importacao);

        return $importacao;
    }
}
