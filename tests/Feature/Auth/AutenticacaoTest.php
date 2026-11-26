<?php

declare(strict_types=1);

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @param  array<string, string>  $sobrescrever
 * @return array<string, string>
 */
function dadosDeRegistro(array $sobrescrever = []): array
{
    return array_merge([
        'razao_social'          => 'Padaria Exemplo Ltda',
        'cnpj'                  => '11.222.333/0001-81',
        'name'                  => 'Maria Silva',
        'email'                 => 'maria@example.com',
        'password'              => 'senha-forte-123',
        'password_confirmation' => 'senha-forte-123',
    ], $sobrescrever);
}

test('exibe as telas de login, registro e redefinição de senha', function (string $rota, string $texto): void {
    $this->get(route($rota))->assertSuccessful()->assertSee($texto);
})->with([
    ['login', 'Esqueci minha senha'],
    ['register', 'Razão social'],
    ['password.request', 'Enviar link'],
]);

test('o registro cria a empresa e o usuário vinculado a ela', function (): void {
    $this->post(route('register'), dadosDeRegistro())
        ->assertRedirect('/produtos');

    $usuario = User::query()->where('email', 'maria@example.com')->sole();

    expect($usuario->empresa->razao_social)->toBe('Padaria Exemplo Ltda')
        ->and($usuario->empresa->cnpj)->toBe('11222333000181');
    $this->assertAuthenticatedAs($usuario);
});

test('o CNPJ é opcional no registro', function (): void {
    $this->post(route('register'), dadosDeRegistro(['cnpj' => '']))->assertRedirect('/produtos');

    expect(Empresa::query()->sole()->cnpj)->toBeNull();
});

test('valida o registro', function (array $dados, string $campo): void {
    Empresa::factory()->create(['cnpj' => '11444777000161']);

    $this->from(route('register'))
        ->post(route('register'), dadosDeRegistro($dados))
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors($campo);

    expect(User::query()->count())->toBe(0);
})->with([
    'sem razão social'       => [['razao_social' => ''], 'razao_social'],
    'CNPJ com dígito errado' => [['cnpj' => '11222333000182'], 'cnpj'],
    'CNPJ já cadastrado'     => [['cnpj' => '11.444.777/0001-61'], 'cnpj'],
    'e-mail inválido'        => [['email' => 'maria'], 'email'],
    'senhas diferentes'      => [['password_confirmation' => 'outra'], 'password'],
]);

test('mensagens de validação do registro em português', function (): void {
    $this->from(route('register'))
        ->post(route('register'), dadosDeRegistro(['razao_social' => '', 'cnpj' => '11222333000182']))
        ->assertSessionHasErrors([
            'razao_social' => 'Informe razão social.',
            'cnpj'         => 'O CNPJ informado não é válido.',
        ]);
});

test('faz login e logout', function (): void {
    $usuario = User::factory()->create(['password' => 'senha-forte-123']);

    $this->post(route('login'), ['email' => $usuario->email, 'password' => 'senha-forte-123'])
        ->assertRedirect('/produtos');
    $this->assertAuthenticatedAs($usuario);

    $this->post(route('logout'))->assertRedirect('/');
    $this->assertGuest();
});

test('recusa login com senha errada com mensagem em português', function (): void {
    $usuario = User::factory()->create();

    $this->from(route('login'))
        ->post(route('login'), ['email' => $usuario->email, 'password' => 'errada'])
        ->assertSessionHasErrors(['email' => 'E-mail ou senha incorretos.']);
    $this->assertGuest();
});
