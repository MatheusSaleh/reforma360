<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StatusSimulacao;
use App\Models\Empresa;
use App\Models\LinhaDoTempo;
use App\Models\Simulacao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LinhaDoTempo>
 */
class LinhaDoTempoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id'                   => Empresa::factory(),
            'simulacao_id'                 => fn (array $atributos) => Simulacao::factory()->create(['empresa_id' => $atributos['empresa_id']]),
            'status'                       => StatusSimulacao::Processando,
            'aliquota_plena_cbs'           => '8.8',
            'aliquota_plena_ibs_estadual'  => '8.85',
            'aliquota_plena_ibs_municipal' => '8.85',
        ];
    }
}
