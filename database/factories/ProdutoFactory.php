<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Produto>
 */
class ProdutoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id'   => Empresa::factory(),
            'descricao'    => fake()->words(3, true),
            'ncm'          => fake()->numerify('########'),
            'nbs'          => null,
            'cst'          => '000',
            'c_class_trib' => '000001',
            'preco'        => fake()->numberBetween(1, 99999).'.'.fake()->numerify('##'),
        ];
    }

    public function mercadoria(string $ncm = '10063021'): static
    {
        return $this->state(fn (): array => ['ncm' => $ncm, 'nbs' => null]);
    }

    public function servico(string $nbs = '122011200'): static
    {
        return $this->state(fn (): array => ['ncm' => null, 'nbs' => $nbs, 'cst' => '200', 'c_class_trib' => '200028']);
    }
}
