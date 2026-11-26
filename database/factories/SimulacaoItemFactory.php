<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Simulacao;
use App\Models\SimulacaoItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SimulacaoItem>
 */
class SimulacaoItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'simulacao_id' => Simulacao::factory(),
            'descricao'    => fake()->words(3, true),
            'nbs'          => '122011200',
            'cst'          => '200',
            'c_class_trib' => '200028',
            'base_calculo' => '1200.00',
        ];
    }
}
