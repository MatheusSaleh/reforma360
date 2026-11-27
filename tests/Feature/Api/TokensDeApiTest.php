<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->usuario = User::factory()->create();
});

test('a página de tokens exige login', function (): void {
    $this->get(route('conta.tokens.index'))->assertRedirect(route('login'));
});

test('cria o token, mostra o texto uma única vez e ele autentica na API', function (): void {
    $resposta = $this->actingAs($this->usuario)
        ->post(route('conta.tokens.store'), ['nome' => 'ERP da loja', 'permissoes' => ['produtos:ler']])
        ->assertRedirect(route('conta.tokens.index'))
        ->assertSessionHas('token_criado');

    $textoDoToken = session('token_criado');
    $token = PersonalAccessToken::query()->sole();

    expect($token)
        ->name->toBe('ERP da loja')
        ->abilities->toBe(['produtos:ler'])
        ->tokenable_id->toBe($this->usuario->id);

    $this->followRedirects($resposta)->assertSee($textoDoToken)->assertSee('ele não será mostrado de novo');
    $this->get(route('conta.tokens.index'))->assertDontSee($textoDoToken)->assertSee('ERP da loja');

    auth()->forgetGuards();

    $this->withToken($textoDoToken)->getJson('/api/v1/produtos')->assertSuccessful();
    $this->withToken($textoDoToken)->postJson('/api/v1/simulacoes/item', [])->assertForbidden();
});

test('valida nome e permissões', function (): void {
    $this->actingAs($this->usuario)
        ->post(route('conta.tokens.store'), ['nome' => '', 'permissoes' => ['admin']])
        ->assertSessionHasErrors(['nome', 'permissoes.0']);

    $this->actingAs($this->usuario)
        ->post(route('conta.tokens.store'), ['nome' => 'Sem permissão'])
        ->assertSessionHasErrors(['permissoes' => 'Escolha ao menos uma permissão.']);

    expect(PersonalAccessToken::query()->count())->toBe(0);
});

test('revoga o próprio token e não o de outro usuário', function (): void {
    $proprio = $this->usuario->createToken('Meu', ['produtos:ler']);
    $alheio = User::factory()->create()->createToken('Alheio', ['produtos:ler']);

    $this->actingAs($this->usuario)
        ->delete(route('conta.tokens.destroy', $alheio->accessToken->id))
        ->assertNotFound();

    $this->actingAs($this->usuario)
        ->delete(route('conta.tokens.destroy', $proprio->accessToken->id))
        ->assertRedirect(route('conta.tokens.index'));

    expect(PersonalAccessToken::query()->pluck('name')->all())->toBe(['Alheio']);

    auth()->forgetGuards();

    $this->withToken($proprio->plainTextToken)->getJson('/api/v1/produtos')->assertUnauthorized();
});
