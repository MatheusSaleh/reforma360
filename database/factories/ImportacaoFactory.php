<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FormatoImportacao;
use App\Enums\StatusImportacao;
use App\Models\Empresa;
use App\Models\Importacao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Importacao>
 */
class ImportacaoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id'      => Empresa::factory(),
            'nome_arquivo'    => 'catalogo.csv',
            'caminho_arquivo' => 'importacoes/'.fake()->uuid().'.csv',
            'formato'         => FormatoImportacao::Csv,
            'status'          => StatusImportacao::Pendente,
        ];
    }

    public function concluida(int $importadas = 3, int $comErro = 0): static
    {
        return $this->state(fn (): array => [
            'status'            => StatusImportacao::Concluida,
            'caminho_arquivo'   => null,
            'total_linhas'      => $importadas + $comErro,
            'linhas_importadas' => $importadas,
            'linhas_com_erro'   => $comErro,
            'iniciada_em'       => now()->subMinute(),
            'concluida_em'      => now(),
        ]);
    }
}
