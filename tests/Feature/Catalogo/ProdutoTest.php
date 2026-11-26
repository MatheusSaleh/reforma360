<?php

declare(strict_types=1);

use App\Models\Empresa;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->usuario = User::factory()->create();
});

/**
 * @param  array<string, string>  $sobrescrever
 * @return array<string, string>
 */
function dadosDoProduto(array $sobrescrever = []): array
{
    return array_merge([
        'descricao'    => 'Curso de idiomas',
        'nbs'          => '1.2201.12.00',
        'ncm'          => '',
        'cst'          => '200',
        'c_class_trib' => '200028',
        'preco'        => '1.200,50',
    ], $sobrescrever);
}

test('visitante é levado ao login', function (string $metodo, string $rota): void {
    $produto = Produto::factory()->create();

    $this->{$metodo}(route($rota, $rota === 'produtos.index' || $rota === 'produtos.create' || $rota === 'produtos.store' ? [] : $produto))
        ->assertRedirect(route('login'));
})->with([
    ['get', 'produtos.index'],
    ['get', 'produtos.create'],
    ['post', 'produtos.store'],
    ['get', 'produtos.edit'],
    ['put', 'produtos.update'],
    ['delete', 'produtos.destroy'],
]);

test('lista só os produtos da empresa do usuário', function (): void {
    Produto::factory()->for($this->usuario->empresa)->create(['descricao' => 'Arroz da minha empresa']);
    Produto::factory()->create(['descricao' => 'Produto de outra empresa']);

    $this->actingAs($this->usuario)
        ->get(route('produtos.index'))
        ->assertSuccessful()
        ->assertSee('Arroz da minha empresa')
        ->assertDontSee('Produto de outra empresa');
});

test('busca por descrição, NCM ou NBS', function (string $busca, string $ve, string $naoVe): void {
    Produto::factory()->for($this->usuario->empresa)->mercadoria('10063021')->create(['descricao' => 'Arroz tipo 1']);
    Produto::factory()->for($this->usuario->empresa)->servico('122011200')->create(['descricao' => 'Curso de inglês']);

    $this->actingAs($this->usuario)
        ->get(route('produtos.index', ['busca' => $busca]))
        ->assertSee($ve)
        ->assertDontSee($naoVe);
})->with([
    'descrição'     => ['arroz', 'Arroz tipo 1', 'Curso de inglês'],
    'NCM formatado' => ['1006.30', 'Arroz tipo 1', 'Curso de inglês'],
    'NBS'           => ['1220112', 'Curso de inglês', 'Arroz tipo 1'],
]);

test('mostra estado vazio', function (): void {
    $this->actingAs($this->usuario)
        ->get(route('produtos.index'))
        ->assertSee('Nenhum produto cadastrado ainda');
});

test('pagina a listagem', function (): void {
    Produto::factory()->count(16)->for($this->usuario->empresa)->sequence(fn ($s) => ['descricao' => sprintf('Produto %02d', $s->index + 1)])->create();

    $this->actingAs($this->usuario)->get(route('produtos.index'))->assertSee('Produto 15')->assertDontSee('Produto 16');
    $this->actingAs($this->usuario)->get(route('produtos.index', ['page' => 2]))->assertSee('Produto 16');
});

test('cadastra produto na empresa do usuário com dados normalizados', function (): void {
    $this->actingAs($this->usuario)
        ->post(route('produtos.store'), dadosDoProduto())
        ->assertRedirect(route('produtos.index'))
        ->assertSessionHas('status', 'Produto cadastrado.');

    $produto = Produto::query()->sole();

    expect($produto->empresa_id)->toBe($this->usuario->empresa_id)
        ->and($produto->nbs)->toBe('122011200')
        ->and($produto->ncm)->toBeNull()
        ->and($produto->preco)->toBe('1200.50')
        ->and($produto->tipo()->label())->toBe('Serviço');
});

test('ignora empresa_id enviado no formulário', function (): void {
    $outraEmpresa = Empresa::factory()->create();

    $this->actingAs($this->usuario)
        ->post(route('produtos.store'), dadosDoProduto(['empresa_id' => (string) $outraEmpresa->id]));

    expect(Produto::query()->sole()->empresa_id)->toBe($this->usuario->empresa_id);
});

test('valida o cadastro', function (array $dados, string $campo): void {
    $this->actingAs($this->usuario)
        ->from(route('produtos.create'))
        ->post(route('produtos.store'), dadosDoProduto($dados))
        ->assertRedirect(route('produtos.create'))
        ->assertSessionHasErrors($campo);

    expect(Produto::query()->count())->toBe(0);
})->with([
    'sem descrição'       => [['descricao' => ''], 'descricao'],
    'descrição longa'     => [['descricao' => str_repeat('a', 256)], 'descricao'],
    'sem NCM nem NBS'     => [['nbs' => ''], 'ncm'],
    'NCM e NBS juntos'    => [['ncm' => '10063021'], 'ncm'],
    'NBS curto'           => [['nbs' => '1234'], 'nbs'],
    'CST inválido'        => [['cst' => '2'], 'cst'],
    'cClassTrib inválido' => [['c_class_trib' => '20002'], 'c_class_trib'],
    'preço zero'          => [['preco' => '0'], 'preco'],
    'preço com 3 casas'   => [['preco' => '10,123'], 'preco'],
]);

test('edita produto da própria empresa', function (): void {
    $produto = Produto::factory()->for($this->usuario->empresa)->mercadoria()->create();

    $this->actingAs($this->usuario)->get(route('produtos.edit', $produto))->assertSuccessful()->assertSee($produto->descricao);

    $this->actingAs($this->usuario)
        ->put(route('produtos.update', $produto), dadosDoProduto(['descricao' => 'Novo nome']))
        ->assertRedirect(route('produtos.index'));

    expect($produto->fresh())
        ->descricao->toBe('Novo nome')
        ->nbs->toBe('122011200')
        ->ncm->toBeNull();
});

test('exclui produto da própria empresa', function (): void {
    $produto = Produto::factory()->for($this->usuario->empresa)->create();

    $this->actingAs($this->usuario)
        ->delete(route('produtos.destroy', $produto))
        ->assertRedirect(route('produtos.index'));

    $this->assertModelMissing($produto);
});

test('não acessa produto de outra empresa (404)', function (string $metodo, string $rota): void {
    $produtoAlheio = Produto::factory()->create(['descricao' => 'Original']);

    $this->actingAs($this->usuario)
        ->{$metodo}(route($rota, $produtoAlheio), dadosDoProduto(['descricao' => 'Alterado']))
        ->assertNotFound();

    expect($produtoAlheio->fresh())->not->toBeNull()
        ->descricao->toBe('Original');
})->with([
    ['get', 'produtos.edit'],
    ['put', 'produtos.update'],
    ['delete', 'produtos.destroy'],
]);

test('o formulário usa o autocomplete de NCM/NBS e as sugestões de classificação', function (): void {
    $this->actingAs($this->usuario)
        ->get(route('produtos.create'))
        ->assertSuccessful()
        ->assertSee('data-autocomplete-nomenclatura', escape: false)
        ->assertSee(route('dados-abertos.nomenclaturas.busca', 'ncm'), escape: false)
        ->assertSee(route('dados-abertos.nomenclaturas.busca', 'nbs'), escape: false)
        ->assertSee('role="combobox"', escape: false)
        ->assertSee('list="opcoes-c-class-trib"', escape: false)
        ->assertSee(route('dados-abertos.situacoes-tributarias'), escape: false);
});
