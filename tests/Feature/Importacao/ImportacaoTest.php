<?php

declare(strict_types=1);

use App\Enums\StatusImportacao;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Jobs\ProcessarImportacaoJob;
use App\Models\Importacao;
use App\Models\Produto;
use App\Models\User;
use App\Services\Importacao\ImportarCatalogoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Sleep;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

function arquivoDeImportacao(string $nome): UploadedFile
{
    return new UploadedFile(base_path("tests/Fixtures/importacao/{$nome}"), $nome, null, null, true);
}

function importar(string $nome): Importacao
{
    test()->actingAs(test()->usuario)
        ->post(route('importacoes.store'), ['arquivo' => arquivoDeImportacao($nome)])
        ->assertRedirect();

    return Importacao::query()->latest('id')->firstOrFail();
}

test('importa CSV válido com dados normalizados e apaga o arquivo', function (): void {
    $this->actingAs($this->usuario)
        ->post(route('importacoes.store'), ['arquivo' => arquivoDeImportacao('catalogo-valido.csv')])
        ->assertRedirect(route('importacoes.show', Importacao::query()->sole()))
        ->assertSessionHas('status');

    $importacao = Importacao::query()->sole();

    expect($importacao)
        ->status->toBe(StatusImportacao::Concluida)
        ->total_linhas->toBe(3)
        ->linhas_importadas->toBe(3)
        ->linhas_com_erro->toBe(0)
        ->caminho_arquivo->toBeNull()
        ->nome_arquivo->toBe('catalogo-valido.csv')
        ->user_id->toBe($this->usuario->id);

    expect(Storage::disk('local')->allFiles())->toBe([]);

    $curso = Produto::query()->where('descricao', 'Curso pré-escola')->sole();
    expect($curso)
        ->empresa_id->toBe($this->usuario->empresa_id)
        ->nbs->toBe('122011200')
        ->ncm->toBeNull()
        ->preco->toBe('1200.00');
    expect(Produto::query()->where('descricao', 'Arroz tipo 1')->value('ncm'))->toBe('10063021');
});

test('registra os erros de cada linha sem interromper a importação', function (): void {
    $importacao = importar('catalogo-com-erros.csv');

    expect($importacao)
        ->status->toBe(StatusImportacao::Concluida)
        ->total_linhas->toBe(8)
        ->linhas_importadas->toBe(2)
        ->linhas_com_erro->toBe(6);

    $erros = $importacao->erros()->orderBy('linha')->get()->map(fn ($e) => [$e->linha, $e->campo, $e->mensagem])->all();

    expect($erros)->toBe([
        [3, 'descricao', 'Informe descrição.'],
        [4, 'ncm', 'NCM 99999999 não existe na tabela vigente.'],
        [5, 'cst', 'CST 999 não existe na tabela vigente.'],
        [6, 'c_class_trib', 'cClassTrib 200003 não pertence ao CST 000.'],
        [7, 'preco', 'Preço deve ser um número.'],
        [8, 'ncm', 'Informe o NCM (mercadoria) ou o NBS (serviço).'],
        [8, 'nbs', 'Informe o NCM (mercadoria) ou o NBS (serviço).'],
    ]);

    expect(Produto::query()->pluck('descricao')->sort()->values()->all())->toBe(['Arroz tipo 1', 'Café torrado']);
});

test('lê CSV em Latin-1 com vírgula, cabeçalho com acentos e zeros perdidos', function (): void {
    $importacao = importar('catalogo-latin1-virgula.csv');

    expect($importacao->linhas_importadas)->toBe(2)
        ->and($importacao->linhas_com_erro)->toBe(0);

    expect(Produto::query()->where('descricao', 'Café em grão')->sole())
        ->ncm->toBe('09012100')
        ->cst->toBe('000')
        ->c_class_trib->toBe('000001')
        ->preco->toBe('12.50');

    expect(Produto::query()->where('descricao', 'Feijão, arroz e açúcar')->value('preco'))->toBe('1234.56');
});

test('importa XLSX com células numéricas e ignora linhas vazias', function (): void {
    $importacao = importar('catalogo.xlsx');

    expect($importacao)
        ->total_linhas->toBe(3)
        ->linhas_importadas->toBe(2)
        ->linhas_com_erro->toBe(1);

    expect(Produto::query()->where('descricao', 'Café torrado')->sole())
        ->ncm->toBe('09012100')
        ->cst->toBe('000')
        ->c_class_trib->toBe('000001')
        ->preco->toBe('12.50');
    expect(Produto::query()->where('descricao', 'Curso pré-escola')->value('preco'))->toBe('450.00');

    expect($importacao->erros()->sole())
        ->linha->toBe(5)
        ->campo->toBe('preco')
        ->mensagem->toBe('O preço deve ter no máximo 2 casas decimais.');
});

test('reimportar atualiza pela descrição sem duplicar e sem tocar em outra empresa', function (): void {
    $alheio = Produto::factory()->create(['descricao' => 'Arroz tipo 1', 'preco' => '1.00']);
    Produto::factory()->for($this->usuario->empresa)->create(['descricao' => 'Arroz tipo 1', 'preco' => '99.00']);

    importar('catalogo-valido.csv');
    importar('catalogo-valido.csv');

    expect($this->usuario->empresa->produtos()->count())->toBe(3)
        ->and($this->usuario->empresa->produtos()->where('descricao', 'Arroz tipo 1')->value('preco'))->toBe('28.90')
        ->and($alheio->fresh()->preco)->toBe('1.00');
});

test('falha a importação inteira quando o arquivo é inválido', function (string $arquivo, string $mensagem): void {
    $importacao = importar($arquivo);

    expect($importacao)
        ->status->toBe(StatusImportacao::Falhou)
        ->mensagem_erro->toBe($mensagem)
        ->caminho_arquivo->toBeNull();
    expect(Produto::query()->count())->toBe(0);
})->with([
    'cabeçalho sem colunas' => ['cabecalho-invalido.csv', 'Cabeçalho inválido: faltam as colunas cst, c_class_trib, ncm ou nbs. Use a planilha modelo.'],
    'sem linhas'            => ['vazio.csv', 'A planilha não tem linhas de produtos.'],
]);

test('desfaz tudo quando a planilha passa do limite de linhas', function (): void {
    config()->set('importacao.maximo_linhas', 2);
    config()->set('importacao.tamanho_lote', 1);

    $importacao = importar('catalogo-valido.csv');

    expect($importacao->status)->toBe(StatusImportacao::Falhou)
        ->and($importacao->mensagem_erro)->toContain('limite de 2 linhas')
        ->and(Produto::query()->count())->toBe(0);
});

test('com a calculadora fora, desfaz o lote e mantém o arquivo para tentar de novo', function (): void {
    Http::fake(['*' => Http::failedConnection()]);
    $caminho = arquivoDeImportacao('catalogo-valido.csv')->storeAs('importacoes', 'teste.csv', 'local');
    $importacao = Importacao::factory()->for($this->usuario->empresa)->create(['caminho_arquivo' => $caminho]);

    expect(fn () => app(ImportarCatalogoService::class)->processar($importacao))
        ->toThrow(CalculadoraIndisponivelException::class);

    expect(Produto::query()->count())->toBe(0)
        ->and(Storage::disk('local')->exists($caminho))->toBeTrue();
});

test('o job marca a importação como falha depois de esgotar as tentativas', function (): void {
    $importacao = Importacao::factory()->for($this->usuario->empresa)->create(['caminho_arquivo' => 'importacoes/x.csv']);
    Storage::disk('local')->put('importacoes/x.csv', 'conteudo');

    (new ProcessarImportacaoJob($importacao))->failed(new RuntimeException('falhou'));

    expect($importacao->fresh())
        ->status->toBe(StatusImportacao::Falhou)
        ->mensagem_erro->toBe('Não foi possível processar a planilha agora. Tente importar novamente.')
        ->caminho_arquivo->toBeNull();
    expect(Storage::disk('local')->exists('importacoes/x.csv'))->toBeFalse();
});

test('enfileira o processamento na fila de importações', function (): void {
    Queue::fake();

    $this->actingAs($this->usuario)->post(route('importacoes.store'), ['arquivo' => arquivoDeImportacao('catalogo-valido.csv')]);

    $importacao = Importacao::query()->sole();
    expect($importacao->status)->toBe(StatusImportacao::Pendente);
    expect(Storage::disk('local')->exists($importacao->caminho_arquivo))->toBeTrue();
    Queue::assertPushedOn('importacoes', ProcessarImportacaoJob::class, fn ($job) => $job->importacao->is($importacao));
});

test('valida o arquivo enviado', function (?UploadedFile $arquivo, string $mensagem): void {
    $this->actingAs($this->usuario)
        ->from(route('importacoes.create'))
        ->post(route('importacoes.store'), ['arquivo' => $arquivo])
        ->assertRedirect(route('importacoes.create'))
        ->assertSessionHasErrors(['arquivo' => $mensagem]);

    expect(Importacao::query()->count())->toBe(0);
})->with([
    'sem arquivo'   => [null, 'Selecione a planilha.'],
    'PDF'           => [fn () => UploadedFile::fake()->create('catalogo.pdf', 10, 'application/pdf'), 'Envie um arquivo .csv, .xlsx ou .xml.'],
    'grande demais' => [fn () => UploadedFile::fake()->create('catalogo.csv', 6000, 'text/csv'), 'O arquivo deve ter no máximo 5120 KB.'],
]);

test('mostra o andamento com atualização automática e os erros por linha', function (): void {
    $pendente = Importacao::factory()->for($this->usuario->empresa)->create();
    $this->actingAs($this->usuario)->get(route('importacoes.show', $pendente))
        ->assertSee('<meta http-equiv="refresh" content="3">', escape: false)
        ->assertSee('Processando a planilha');

    $concluida = importar('catalogo-com-erros.csv');
    $this->actingAs($this->usuario)->get(route('importacoes.show', $concluida))
        ->assertSuccessful()
        ->assertDontSee('http-equiv="refresh"', escape: false)
        ->assertSee('Concluída')
        ->assertSeeInOrder(['4', 'NCM', 'NCM 99999999 não existe na tabela vigente.']);
});

test('lista só as importações da empresa e esconde as de outras (404)', function (): void {
    Importacao::factory()->for($this->usuario->empresa)->create(['nome_arquivo' => 'minha.csv']);
    $alheia = Importacao::factory()->create(['nome_arquivo' => 'alheia.csv']);

    $this->actingAs($this->usuario)->get(route('importacoes.index'))
        ->assertSee('minha.csv')
        ->assertDontSee('alheia.csv');

    $this->actingAs($this->usuario)->get(route('importacoes.show', $alheia))->assertNotFound();
});

test('visitante é levado ao login', function (): void {
    $this->get(route('importacoes.create'))->assertRedirect(route('login'));
    $this->post(route('importacoes.store'))->assertRedirect(route('login'));
});

test('baixa a planilha modelo em XLSX e CSV', function (): void {
    $this->actingAs($this->usuario)->get(route('importacoes.modelo', 'xlsx'))
        ->assertDownload('modelo-catalogo-reforma360.xlsx');

    $csv = $this->actingAs($this->usuario)->get(route('importacoes.modelo', 'csv'));
    $csv->assertDownload('modelo-catalogo-reforma360.csv');

    $conteudo = file_get_contents($csv->baseResponse->getFile()->getPathname());
    expect($conteudo)->toStartWith("\xEF\xBB\xBF")
        ->toContain('"descricao";"ncm";"nbs";"cst";"c_class_trib";"preco"')
        ->toContain('"Arroz tipo 1 (cesta básica)";"10063021";"";"200";"200003";"28,90"');

    $this->actingAs($this->usuario)->get('/importacoes/modelo/pdf')->assertNotFound();
});

test('importa colunas de IS do CSV e mantém os valores ao reimportar uma planilha sem estimativas', function (): void {
    $colunas = 'descricao;ncm;nbs;cst;c_class_trib;preco';
    $cerveja = 'Cerveja;22030000;;000;000001;4,50';

    foreach ([
        "{$colunas};aliquota_is;p_is_espec;unidade_is;quantidade_is
{$cerveja};10,25;0,1234;lt;0,35",
        "{$colunas}
{$cerveja}",
    ] as $csv) {
        $this->actingAs($this->usuario)->post(route('importacoes.store'), [
            'arquivo' => UploadedFile::fake()->createWithContent('catalogo-is.csv', $csv),
        ])->assertRedirect();
    }

    expect(Importacao::query()->pluck('linhas_importadas')->all())->toBe([1, 1]);
    expect(Produto::query()->sole())
        ->is_aliquota_ad_valorem->toBe('10.2500')
        ->is_aliquota_ad_rem->toBe('0.1234')
        ->is_unidade->toBe('LT')
        ->is_quantidade->toBe('0.3500');
});

test('importa estimativas do Imposto Seletivo de uma planilha XLSX', function (): void {
    $arquivo = UploadedFile::fake()->create('catalogo-is.xlsx');
    $planilha = new Spreadsheet;
    $planilha->getActiveSheet()->fromArray([
        ['descricao', 'ncm', 'cst', 'c_class_trib', 'preco', 'is_aliquota_ad_valorem', 'is_cst', 'is_c_class_trib'],
        ['Cerveja', '22030000', '000', '000001', '4,50', '10,25', '000', '000001'],
    ]);
    (new Xlsx($planilha))->save($arquivo->path());

    $this->actingAs($this->usuario)->post(route('importacoes.store'), ['arquivo' => $arquivo])->assertRedirect();

    expect(Importacao::query()->sole()->linhas_importadas)->toBe(1);
    expect(Produto::query()->sole())
        ->is_aliquota_ad_valorem->toBe('10.2500')
        ->is_cst->toBe('000')
        ->is_c_class_trib->toBe('000001');
});
