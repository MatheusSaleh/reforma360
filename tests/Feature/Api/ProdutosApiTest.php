<?php

declare(strict_types=1);

use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->usuario = User::factory()->create();
});

test('exige token', function (): void {
    $this->getJson('/api/v1/produtos')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Não autenticado. Envie um token válido no cabeçalho Authorization.');
});

test('exige a permissão do token para cada operação', function (): void {
    Sanctum::actingAs($this->usuario, ['simulacoes']);

    $this->getJson('/api/v1/produtos')
        ->assertForbidden()
        ->assertJsonPath('message', 'O token não tem a permissão necessária para esta operação.');

    Sanctum::actingAs($this->usuario, ['produtos:ler']);

    $this->getJson('/api/v1/produtos')->assertSuccessful();
    $this->postJson('/api/v1/produtos', [])->assertForbidden();
});

test('lista só os produtos da empresa, com busca e paginação', function (): void {
    Produto::factory()->for($this->usuario->empresa)->mercadoria('10063021')->create(['descricao' => 'Arroz branco', 'preco' => '25.90']);
    Produto::factory()->for($this->usuario->empresa)->servico()->create(['descricao' => 'Curso de inglês']);
    Produto::factory()->create(['descricao' => 'Arroz de outra empresa']);
    Sanctum::actingAs($this->usuario, ['produtos:ler']);

    $this->getJson('/api/v1/produtos?busca=arroz')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.descricao', 'Arroz branco')
        ->assertJsonPath('data.0.tipo', 'mercadoria')
        ->assertJsonPath('data.0.ncm', '10063021')
        ->assertJsonPath('data.0.preco', '25.90')
        ->assertJsonPath('meta.total', 1);

    $this->getJson('/api/v1/produtos?por_pagina=1')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 2);
});

test('cadastra, consulta, altera e exclui um produto', function (): void {
    Sanctum::actingAs($this->usuario, ['produtos:ler', 'produtos:escrever']);

    $id = $this->postJson('/api/v1/produtos', [
        'descricao'    => 'Mensalidade escolar',
        'nbs'          => '1.2201.12.00',
        'cst'          => '200',
        'c_class_trib' => '200028',
        'preco'        => '1200.00',
    ])
        ->assertCreated()
        ->assertJsonPath('data.nbs', '122011200')
        ->assertJsonPath('data.tipo', 'servico')
        ->assertJsonPath('data.preco', '1200.00')
        ->json('data.id');

    expect(Produto::query()->findOrFail($id)->empresa_id)->toBe($this->usuario->empresa_id);

    $this->getJson("/api/v1/produtos/{$id}")->assertSuccessful()->assertJsonPath('data.descricao', 'Mensalidade escolar');

    $this->putJson("/api/v1/produtos/{$id}", [
        'descricao'    => 'Mensalidade escolar (anual)',
        'nbs'          => '122011200',
        'cst'          => '200',
        'c_class_trib' => '200028',
        'preco'        => '13200.00',
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.preco', '13200.00');

    $this->deleteJson("/api/v1/produtos/{$id}")->assertNoContent();

    expect(Produto::query()->find($id))->toBeNull();
});

test('valida o produto com as mesmas regras do app, com mensagens em português', function (): void {
    Sanctum::actingAs($this->usuario, ['produtos:escrever']);

    $this->postJson('/api/v1/produtos', ['descricao' => 'Sem código', 'cst' => '2', 'c_class_trib' => '200028', 'preco' => '10'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['ncm', 'cst'])
        ->assertJsonPath('message', fn (string $mensagem): bool => str_contains($mensagem, '(e mais'));
});

test('responde 404 para produto de outra empresa', function (): void {
    $alheio = Produto::factory()->create();
    Sanctum::actingAs($this->usuario, ['produtos:ler', 'produtos:escrever']);

    $this->getJson("/api/v1/produtos/{$alheio->id}")->assertNotFound();
    $this->deleteJson("/api/v1/produtos/{$alheio->id}")->assertNotFound();

    expect($alheio->fresh())->not->toBeNull();
});

test('limita as requisições por token', function (): void {
    config()->set('api.limite_por_minuto', 2);
    Sanctum::actingAs($this->usuario, ['produtos:ler']);

    $this->getJson('/api/v1/produtos')->assertSuccessful();
    $this->getJson('/api/v1/produtos')->assertSuccessful();
    $this->getJson('/api/v1/produtos')
        ->assertTooManyRequests()
        ->assertHeader('Retry-After')
        ->assertJsonPath('message', 'Limite de requisições atingido. Tente de novo em instantes.');
});

test('cadastra, consulta e preserva as estimativas do IS quando um cliente antigo edita o produto', function (): void {
    Sanctum::actingAs($this->usuario, ['produtos:ler', 'produtos:escrever']);
    $produto = ['descricao' => 'Cerveja', 'ncm' => '22030000', 'cst' => '000', 'c_class_trib' => '000001', 'preco' => '4.50'];

    $id = $this->postJson('/api/v1/produtos', [
        ...$produto, 'is_aliquota_ad_valorem' => '10,25', 'is_aliquota_ad_rem' => '0,1234', 'is_unidade' => 'lt', 'is_quantidade' => '0,35',
    ])->assertCreated()
        ->assertJsonPath('data.is_aliquota_ad_valorem', '10.2500')
        ->assertJsonPath('data.is_aliquota_ad_rem', '0.1234')
        ->assertJsonPath('data.is_quantidade', '0.3500')
        ->assertJsonPath('data.is_unidade', 'LT')
        ->assertJsonPath('data.is_cst', '000')
        ->assertJsonPath('data.is_c_class_trib', '000001')
        ->json('data.id');

    $this->putJson("/api/v1/produtos/{$id}", [...$produto, 'preco' => '5.00'])
        ->assertSuccessful()
        ->assertJsonPath('data.is_aliquota_ad_rem', '0.1234');

    $this->putJson("/api/v1/produtos/{$id}", [...$produto, 'is_aliquota_ad_valorem' => null, 'is_aliquota_ad_rem' => null])
        ->assertSuccessful()
        ->assertJsonPath('data.is_aliquota_ad_valorem', null)
        ->assertJsonPath('data.is_aliquota_ad_rem', null);
});
