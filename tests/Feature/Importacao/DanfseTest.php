<?php

declare(strict_types=1);

use App\Enums\StatusImportacao;
use App\Enums\TipoDocumentoFiscal;
use App\Models\Importacao;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Sleep;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
    Http::preventStrayRequests();
    Sleep::fake();
    config()->set('services.calculadora_regular.url', 'http://calculadora-regular:8080/api');
    config()->set('services.danfse.url', 'http://danfse:8080/api');

    Http::fake([
        '*/dados-abertos/nbs/lista*'                     => Http::response(fixtureJson('calculadora/regular/nbs-lista.json')),
        '*/dados-abertos/ncm/vigentes*'                  => Http::response(fixtureJson('calculadora/regular/ncm-vigentes.json')),
        '*/dados-abertos/situacoes-tributarias/cbs-ibs*' => Http::response(fixtureJson('calculadora/regular/situacoes-tributarias-cbs-ibs.json')),
    ]);

    $this->usuario = User::factory()->create();
});

function importarNfse(): Importacao
{
    test()->actingAs(test()->usuario)->post(route('importacoes.store'), [
        'arquivo' => new UploadedFile(base_path('tests/Fixtures/importacao/xml/oficial-nfse-com-ibscbs.xml'), 'nfse.xml', null, null, true),
    ]);

    return Importacao::query()->latest('id')->firstOrFail();
}

test('guarda o XML da NFS-e e baixa o DANFSe gerado a partir dele', function (): void {
    Http::fake(['danfse:8080/api/danfse' => Http::response('%PDF-1.7 danfse', 200, ['Content-Type' => 'application/pdf'])]);
    $importacao = importarNfse();

    expect($importacao->status)->toBe(StatusImportacao::Concluida)
        ->and($importacao->caminho_arquivo)->not->toBeNull()
        ->and(Storage::disk('local')->exists($importacao->caminho_arquivo))->toBeTrue();

    $this->actingAs($this->usuario)->get(route('importacoes.show', $importacao))
        ->assertSee('Baixar DANFSe (PDF)')
        ->assertSee('fica guardado até '.now()->addDays(30)->timezone('America/Sao_Paulo')->format('d/m/Y'));

    $this->actingAs($this->usuario)->get(route('importacoes.danfse', $importacao))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertDownload('danfse-nfse-228.pdf');

    Http::assertSent(fn (Request $r): bool => $r->url() === 'http://danfse:8080/api/danfse'
        && str_contains($r->body(), '<nNFSe>228</nNFSe>'));
});

test('NF-e e planilhas continuam sem arquivo guardado e sem DANFSe', function (): void {
    Produto::factory()->for($this->usuario->empresa)->mercadoria('09012100')->create();
    $this->actingAs($this->usuario)->post(route('importacoes.store'), [
        'arquivo' => new UploadedFile(base_path('tests/Fixtures/importacao/xml/nfe-catalogo.xml'), 'nfe.xml', null, null, true),
    ]);
    $nfe = Importacao::query()->sole();

    expect($nfe->caminho_arquivo)->toBeNull();
    $this->actingAs($this->usuario)->get(route('importacoes.show', $nfe))->assertDontSee('DANFSe');
    $this->actingAs($this->usuario)->get(route('importacoes.danfse', $nfe))->assertNotFound();
});

test('avisa quando o XML já foi apagado', function (): void {
    $importacao = Importacao::factory()->for($this->usuario->empresa)->concluida()->create([
        'tipo_documento' => TipoDocumentoFiscal::Nfse, 'numero_documento' => '228',
    ]);

    $this->actingAs($this->usuario)->get(route('importacoes.show', $importacao))
        ->assertDontSee('Baixar DANFSe')
        ->assertSee('já foi apagado (guardado por 30 dias)');

    $this->actingAs($this->usuario)->from(route('importacoes.show', $importacao))
        ->get(route('importacoes.danfse', $importacao))
        ->assertRedirect(route('importacoes.show', $importacao))
        ->assertSessionHasErrors(['danfse' => 'O XML desta NFS-e não está mais guardado. Importe a nota de novo para gerar o DANFSe.']);
});

test('mostra a recusa do gerador de DANFSe', function (): void {
    Http::fake(['danfse:8080/api/danfse' => Http::response(fixtureJson('calculadora/danfse/danfse-400.json'), 400)]);
    $importacao = importarNfse();

    $this->actingAs($this->usuario)->from(route('importacoes.show', $importacao))
        ->get(route('importacoes.danfse', $importacao))
        ->assertSessionHasErrors(['danfse' => 'O gerador de DANFSe recusou a nota: expected element type <NFSe> but have <NFe>']);
});

test('avisa quando o gerador de DANFSe está fora', function (): void {
    Http::fake(['danfse:8080/api/danfse' => Http::failedConnection()]);
    $importacao = importarNfse();

    $this->actingAs($this->usuario)->from(route('importacoes.show', $importacao))
        ->get(route('importacoes.danfse', $importacao))
        ->assertSessionHasErrors(['simulacao' => 'O gerador de DANFSe está indisponível no momento. Tente novamente em instantes.']);

    $this->actingAs($this->usuario)->followingRedirects()->from(route('importacoes.show', $importacao))
        ->get(route('importacoes.danfse', $importacao))
        ->assertSee('O gerador de DANFSe está indisponível no momento.');
});

test('não gera DANFSe de importação de outra empresa', function (): void {
    $alheia = Importacao::factory()->concluida()->create(['tipo_documento' => TipoDocumentoFiscal::Nfse, 'caminho_arquivo' => 'importacoes/x.xml']);

    $this->actingAs($this->usuario)->get(route('importacoes.danfse', $alheia))->assertNotFound();
});

test('o comando apaga só os arquivos vencidos', function (): void {
    Storage::disk('local')->put('importacoes/velho.xml', 'x');
    Storage::disk('local')->put('importacoes/novo.xml', 'y');
    $velha = Importacao::factory()->for($this->usuario->empresa)->concluida()->create(['caminho_arquivo' => 'importacoes/velho.xml', 'concluida_em' => now()->subDays(31)]);
    $nova = Importacao::factory()->for($this->usuario->empresa)->concluida()->create(['caminho_arquivo' => 'importacoes/novo.xml', 'concluida_em' => now()->subDays(29)]);

    $this->artisan('importacoes:apagar-arquivos-vencidos')
        ->expectsOutput('1 arquivo(s) apagado(s).')
        ->assertSuccessful();

    expect($velha->fresh()->caminho_arquivo)->toBeNull()
        ->and($nova->fresh()->caminho_arquivo)->toBe('importacoes/novo.xml');
    Storage::disk('local')->assertMissing('importacoes/velho.xml');
    Storage::disk('local')->assertExists('importacoes/novo.xml');
});

test('o comando está agendado diariamente', function (): void {
    $evento = collect(app(Schedule::class)->events())
        ->first(fn ($e) => str_contains((string) $e->command, 'importacoes:apagar-arquivos-vencidos'));

    expect($evento)->not->toBeNull()
        ->and($evento->expression)->toBe('0 3 * * *');
});
