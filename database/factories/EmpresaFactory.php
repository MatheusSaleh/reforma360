<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Empresa;
use App\Rules\Cnpj;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Empresa>
 */
class EmpresaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'razao_social' => fake()->company(),
            'cnpj'         => Cnpj::gerar(),
        ];
    }

    public function semCnpj(): static
    {
        return $this->state(fn (): array => ['cnpj' => null]);
    }
}
