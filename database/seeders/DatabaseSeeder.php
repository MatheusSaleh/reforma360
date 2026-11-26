<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Dados de demonstração (fictícios). Idempotente: pode rodar várias vezes.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * NCM/NBS e classificações dos exemplos oficiais da calculadora da Receita.
     *
     * @var list<array{descricao: string, ncm: string|null, nbs: string|null, cst: string, c_class_trib: string, preco: string}>
     */
    private const array PRODUTOS = [
        ['descricao' => 'Arroz tipo 1 (cesta básica)', 'ncm' => '10063021', 'nbs' => null, 'cst' => '200', 'c_class_trib' => '200003', 'preco' => '28.90'],
        ['descricao' => 'Cerveja lata 350 ml', 'ncm' => '22030000', 'nbs' => null, 'cst' => '000', 'c_class_trib' => '000001', 'preco' => '4.50'],
        ['descricao' => 'Notebook 14 polegadas', 'ncm' => '84713012', 'nbs' => null, 'cst' => '000', 'c_class_trib' => '000001', 'preco' => '3899.00'],
        ['descricao' => 'Curso de idiomas (mensalidade)', 'ncm' => null, 'nbs' => '122011200', 'cst' => '200', 'c_class_trib' => '200028', 'preco' => '450.00'],
    ];

    public function run(): void
    {
        $empresa = Empresa::query()->updateOrCreate(
            ['cnpj' => '11222333000181'],
            ['razao_social' => 'Empresa Demonstração Ltda'],
        );

        User::query()->updateOrCreate(
            ['email' => 'demo@reforma360.test'],
            ['name' => 'Usuário Demonstração', 'password' => Hash::make('password'), 'empresa_id' => $empresa->id, 'email_verified_at' => now()],
        );

        foreach (self::PRODUTOS as $produto) {
            $empresa->produtos()->updateOrCreate(['descricao' => $produto['descricao']], $produto);
        }
    }
}
