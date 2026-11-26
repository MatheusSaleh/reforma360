<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StatusSimulacao;
use App\Models\Empresa;
use App\Models\Simulacao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Simulacao>
 */
class SimulacaoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id'             => Empresa::factory(),
            'status'                 => StatusSimulacao::Pendente,
            'data_fato_gerador'      => '2027-03-10',
            'uf'                     => 'RS',
            'municipio'              => 4314902,
            'aliquota_cbs'           => '8.8',
            'aliquota_ibs_estadual'  => '0.05',
            'aliquota_ibs_municipal' => '0.05',
        ];
    }

    public function em2026(): static
    {
        return $this->state(fn (): array => [
            'data_fato_gerador'      => '2026-06-10',
            'aliquota_cbs'           => null,
            'aliquota_ibs_estadual'  => null,
            'aliquota_ibs_municipal' => null,
        ]);
    }
}
