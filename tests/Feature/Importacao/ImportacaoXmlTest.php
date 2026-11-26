<?php

declare(strict_types=1);

use App\Enums\StatusImportacao;
use App\Enums\TipoDocumentoFiscal;
use App\Enums\Uf;
use App\Jobs\SimularItensJob;
use App\Models\Importacao;
use App\Models\Produto;
use App\Models\Simulacao;
use App\Models\User;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Sleep;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
    Http::preventStrayRequests();
    Sleep::fake();
    config()->set('services.calculadora_regular.url', 'http://calculadora-regular:8080/api');

    Http::fake([
        '*/dados-abertos/ncm/vigentes*'                  => Http::response(fixtureJson('calculadora/regular/ncm-vigentes.json')),
        '*/dados-abertos/nbs/lista*'                     => Http::response(fixtureJson('calculadora/regular/nbs-lista.json')),
        '*/dados-abertos/situacoes-tributarias/cbs-ibs*' => Http::response(fixtureJson('calculadora/regular/situacoes-tributarias-cbs-ibs.json')),
    ]);

    $this->usuario = User::factory()->create();
});

function importarXml(string $fixture): Importacao
{
    test()->actingAs(test()->usuario)
        ->post(route('importacoes.store'), ['arquivo' => new UploadedFile(base_path("tests/Fixtures/importacao/xml/{$fixture}"), $fixture, null, null, true)])
        ->assertRedirect();

    return Importacao::query()->latest('id')->firstOrFail();
}

test('importa a NF-e, guarda os dados da nota e usa o catálogo para itens sem IBS/CBS', function (): void {
    Produto::factory()->for($this->usuario->empresa)->mercadoria('09012100')->create(['descricao' => 'Café de referência', 'cst' => '000', 'c_class_trib' => '000002']);

    $importacao = importarXml('nfe-catalogo.xml');

    expect($importacao)
        ->status->toBe(StatusImportacao::Concluida)
        ->tipo_documento->toBe(TipoDocumentoFiscal::Nfe)
        ->numero_documento->toBe('1234')
        ->uf->toBe(Uf::RS)
        ->municipio->toBe(4314902)
        ->total_linhas->toBe(4)
        ->linhas_importadas->toBe(2)
        ->linhas_com_erro->toBe(2)
        ->caminho_arquivo->toBeNull();
    expect($importacao->data_documento->toDateString())->toBe('2027-03-10');

    expect(Produto::query()->where('descricao', 'Arroz tipo 1 5 kg')->sole())
        ->ncm->toBe('10063021')->cst->toBe('200')->c_class_trib->toBe('200003')->preco->toBe('14.46');
    expect(Produto::query()->where('descricao', 'Café torrado 500 g')->sole())
        ->cst->toBe('000')->c_class_trib->toBe('000002')->preco->toBe('12.50');

    expect($importacao->itens()->orderBy('linha')->get()->map(fn ($i) => [$i->linha, $i->valor])->all())
        ->toBe([[1, '28.91'], [2, '37.50']]);

    expect($importacao->erros()->orderBy('linha')->get()->map(fn ($e) => [$e->linha, $e->campo, $e->mensagem])->all())->toBe([
        [3, 'cst', 'Sem CST e cClassTrib (a nota não traz o grupo IBS/CBS) e nenhum produto do catálogo com o NCM 22030000 para usar como referência. Cadastre um produto com esse NCM e importe de novo.'],
        [4, 'ncm', 'NCM 99999999 não existe na tabela vigente.'],
    ]);
});

test('importa a NFS-e usando o produto do catálogo com o mesmo NBS', function (): void {
    Produto::factory()->for($this->usuario->empresa)->servico('101011100')->create(['descricao' => 'Obra de referência', 'cst' => '000', 'c_class_trib' => '000001']);

    $importacao = importarXml('oficial-nfse-sem-ibscbs.xml');

    expect($importacao)
        ->tipo_documento->toBe(TipoDocumentoFiscal::Nfse)
        ->linhas_importadas->toBe(1)
        ->uf->toBe(Uf::PR);
    expect($importacao->itens()->sole()->valor)->toBe('1000.00');
});

test('falha a importação de XML inválido ou perigoso sem gravar nada', function (string $fixture, string $mensagem): void {
    $importacao = importarXml($fixture);

    expect($importacao)
        ->status->toBe(StatusImportacao::Falhou)
        ->caminho_arquivo->toBeNull();
    expect($importacao->mensagem_erro)->toContain($mensagem);
    expect(Produto::query()->count())->toBe(0)
        ->and($importacao->itens()->count())->toBe(0);
})->with([
    'XXE'            => ['malicioso-xxe.xml', 'recusado por segurança'],
    'billion laughs' => ['malicioso-billion-laughs.xml', 'recusado por segurança'],
    'malformado'     => ['malformado.xml', 'não é um XML válido'],
    'não é nota'     => ['nao-e-nota.xml', 'não é uma NF-e'],
    'duas notas'     => ['duas-notas.xml', 'Envie uma nota por arquivo'],
]);

test('mostra os dados da nota, os erros por item e o botão para simular', function (): void {
    Produto::factory()->for($this->usuario->empresa)->mercadoria('09012100')->create();
    $importacao = importarXml('nfe-catalogo.xml');

    $this->actingAs($this->usuario)->get(route('importacoes.show', $importacao))
        ->assertSuccessful()
        ->assertSee('NF-e nº 1234')
        ->assertSee('emitida em 10/03/2027')
        ->assertSee('Erros por item')
        ->assertSee('Simular esta nota')
        ->assertSee(route('simulacoes.create', ['importacao' => $importacao->id, 'uf' => 'RS', 'municipio' => 4314902, 'data' => '2027-03-10']));
});

test('não sugere data fora da transição ao simular nota antiga', function (): void {
    Produto::factory()->for($this->usuario->empresa)->mercadoria('09012100')->create();
    $nota2025 = str_replace('2027-03-10T14:30:00-03:00', '2025-09-01T10:00:00-03:00', (string) file_get_contents(base_path('tests/Fixtures/importacao/xml/nfe-catalogo.xml')));

    $this->actingAs($this->usuario)->post(route('importacoes.store'), [
        'arquivo' => UploadedFile::fake()->createWithContent('nota-2025.xml', $nota2025),
    ]);
    $importacao = Importacao::query()->sole();

    expect($importacao->data_documento->toDateString())->toBe('2025-09-01');
    $this->actingAs($this->usuario)->get(route('importacoes.show', $importacao))
        ->assertSee(route('simulacoes.create', ['importacao' => $importacao->id, 'uf' => 'RS', 'municipio' => 4314902]));
});

test('simula só os itens da nota com o valor de cada item como base', function (): void {
    Produto::factory()->for($this->usuario->empresa)->mercadoria('09012100')->create(['descricao' => 'Café de referência']);
    Produto::factory()->for($this->usuario->empresa)->create(['descricao' => 'Fora da nota']);
    $importacao = importarXml('nfe-catalogo.xml');
    Bus::fake();

    $this->actingAs($this->usuario)
        ->get(route('simulacoes.create', ['importacao' => $importacao->id, 'uf' => 'RS', 'municipio' => 4314902, 'data' => '2027-03-10']))
        ->assertSuccessful()
        ->assertSee('Simular a nota')
        ->assertSee('Simular 2 itens')
        ->assertSee('name="importacao_id" value="'.$importacao->id.'"', escape: false)
        ->assertSee('data-selecionado="4314902"', escape: false);

    $this->actingAs($this->usuario)->post(route('simulacoes.store'), [
        'importacao_id'          => $importacao->id,
        'data'                   => '2027-03-10',
        'uf'                     => 'RS',
        'municipio'              => '4314902',
        'aliquota_cbs'           => '8,8',
        'aliquota_ibs_estadual'  => '0,05',
        'aliquota_ibs_municipal' => '0,05',
    ])->assertRedirect();

    $simulacao = Simulacao::query()->sole();

    expect($simulacao->importacao_id)->toBe($importacao->id)
        ->and($simulacao->total_itens)->toBe(2)
        ->and($simulacao->itens()->orderBy('base_calculo')->pluck('base_calculo')->all())->toBe(['28.91', '37.50'])
        ->and($simulacao->itens()->pluck('descricao')->all())->not->toContain('Fora da nota');

    Bus::assertBatched(fn (PendingBatch $batch): bool => $batch->jobs->every(fn ($job) => $job instanceof SimularItensJob));

    $this->actingAs($this->usuario)->get(route('simulacoes.show', $simulacao))
        ->assertSee('Itens da nota')
        ->assertSee('NF-e nº 1234');
});

test('não simula importação de outra empresa nem importação sem itens', function (): void {
    Bus::fake();
    $alheia = Importacao::factory()->concluida()->create();
    $falhou = Importacao::factory()->for($this->usuario->empresa)->create(['status' => StatusImportacao::Falhou]);
    $parametros = ['data' => '2026-06-10', 'uf' => 'RS', 'municipio' => '4314902'];

    $this->actingAs($this->usuario)->get(route('simulacoes.create', ['importacao' => $alheia->id]))->assertNotFound();
    $this->actingAs($this->usuario)->post(route('simulacoes.store'), [...$parametros, 'importacao_id' => $alheia->id])->assertNotFound();

    $this->actingAs($this->usuario)
        ->from(route('simulacoes.create'))
        ->post(route('simulacoes.store'), [...$parametros, 'importacao_id' => $falhou->id])
        ->assertSessionHasErrors(['simulacao' => 'Esta importação não tem itens importados para simular.']);

    expect(Simulacao::query()->count())->toBe(0);
    Bus::assertNothingBatched();
});

test('planilhas também registram os itens importados', function (): void {
    $this->actingAs($this->usuario)->post(route('importacoes.store'), [
        'arquivo' => new UploadedFile(base_path('tests/Fixtures/importacao/catalogo-valido.csv'), 'catalogo-valido.csv', null, null, true),
    ]);

    $importacao = Importacao::query()->sole();

    expect($importacao->itens()->count())->toBe(3)
        ->and($importacao->ehDocumentoFiscal())->toBeFalse()
        ->and($importacao->itens()->orderBy('linha')->first()->valor)->toBe('28.90');
});
