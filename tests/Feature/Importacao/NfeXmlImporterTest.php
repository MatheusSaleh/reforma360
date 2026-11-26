<?php

declare(strict_types=1);

use App\Enums\TipoDocumentoFiscal;
use App\Enums\Uf;
use App\Importers\Data\LinhaPlanilha;
use App\Importers\Exceptions\ArquivoInvalidoException;
use App\Importers\NfeXmlImporter;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');
});

function xmlNoDisco(string $fixture): string
{
    Storage::disk('local')->put("importacoes/{$fixture}", (string) file_get_contents(base_path("tests/Fixtures/importacao/xml/{$fixture}")));

    return "importacoes/{$fixture}";
}

/**
 * @return list<LinhaPlanilha>
 */
function linhasDoXml(string $fixture): array
{
    $linhas = [];
    app(NfeXmlImporter::class)->ler(xmlNoDisco($fixture), function (array $lote) use (&$linhas): void {
        $linhas = [...$linhas, ...$lote];
    });

    return $linhas;
}

test('lê os itens da NF-e oficial de exemplo com o grupo IBS/CBS', function (): void {
    $linhas = linhasDoXml('oficial-nfe.xml');

    expect($linhas)->toHaveCount(1)
        ->and($linhas[0]->linha)->toBe(1)
        ->and($linhas[0]->campos)->toBe([
            'descricao'       => 'PESSEGO BASE CONCENTRADA',
            'ncm'             => '76071120',
            'nbs'             => null,
            'cst'             => '200',
            'c_class_trib'    => '200022',
            'preco'           => '2.00',
            'valor_documento' => '2.00',
        ]);
});

test('lê os dados da operação da NF-e, NFC-e e NFS-e', function (string $fixture, TipoDocumentoFiscal $tipo, string $numero, string $data, Uf $uf, int $municipio): void {
    $documento = app(NfeXmlImporter::class)->documento(xmlNoDisco($fixture));

    expect($documento->tipo)->toBe($tipo)
        ->and($documento->numero)->toBe($numero)
        ->and($documento->data->toDateString())->toBe($data)
        ->and($documento->uf)->toBe($uf)
        ->and($documento->municipio)->toBe($municipio);
})->with([
    'NF-e'            => ['oficial-nfe.xml', TipoDocumentoFiscal::Nfe, '5', '2025-09-01', Uf::MA, 2111300],
    'NFC-e'           => ['oficial-nfce.xml', TipoDocumentoFiscal::Nfce, '664350', '2026-05-05', Uf::PA, 1504208],
    'NFS-e'           => ['oficial-nfse-sem-ibscbs.xml', TipoDocumentoFiscal::Nfse, '223', '2026-05-06', Uf::PR, 4113700],
    'NF-e em nfeProc' => ['nfe-catalogo.xml', TipoDocumentoFiscal::Nfe, '1234', '2027-03-10', Uf::RS, 4314902],
]);

test('arredonda o preço unitário com muitas casas e mantém o valor do item da nota', function (): void {
    $nfce = linhasDoXml('oficial-nfce.xml')[0]->campos;
    $nfe = linhasDoXml('nfe-catalogo.xml')[0]->campos;

    expect($nfce['preco'])->toBe('2.48')->and($nfce['valor_documento'])->toBe('14.88')
        ->and($nfe['preco'])->toBe('14.46')->and($nfe['valor_documento'])->toBe('28.91');
});

test('itens sem o grupo IBS/CBS vêm sem CST e cClassTrib', function (): void {
    $linhas = linhasDoXml('nfe-catalogo.xml');

    expect(array_map(fn (LinhaPlanilha $l) => $l->linha, $linhas))->toBe([1, 2, 3, 4])
        ->and($linhas[1]->campos['cst'])->toBeNull()
        ->and($linhas[1]->campos['c_class_trib'])->toBeNull()
        ->and($linhas[0]->campos['cst'])->toBe('200');
});

test('lê o serviço da NFS-e, com e sem o grupo IBS/CBS', function (): void {
    $sem = linhasDoXml('oficial-nfse-sem-ibscbs.xml');
    $com = linhasDoXml('oficial-nfse-com-ibscbs.xml');

    expect($sem)->toHaveCount(1)
        ->and($sem[0]->campos)->toMatchArray(['nbs' => '101011100', 'ncm' => null, 'cst' => null, 'c_class_trib' => null, 'preco' => '1000.00', 'valor_documento' => '1000.00'])
        ->and($com[0]->campos)->toMatchArray(['nbs' => '101011100', 'cst' => '000', 'c_class_trib' => '000001']);
});

test('recusa XML inválido ou perigoso', function (string $fixture, string $mensagem): void {
    expect(fn () => linhasDoXml($fixture))->toThrow(ArquivoInvalidoException::class, $mensagem);
})->with([
    'XXE (entidade externa)' => ['malicioso-xxe.xml', 'recusado por segurança'],
    'billion laughs'         => ['malicioso-billion-laughs.xml', 'recusado por segurança'],
    'malformado'             => ['malformado.xml', 'O arquivo não é um XML válido'],
    'não é nota fiscal'      => ['nao-e-nota.xml', 'não é uma NF-e, NFC-e ou NFS-e'],
    'duas notas'             => ['duas-notas.xml', 'O arquivo tem 2 notas. Envie uma nota por arquivo.'],
    'nota sem itens'         => ['sem-itens.xml', 'A planilha não tem linhas de produtos.'],
]);

test('o conteúdo da entidade externa nunca é lido', function (): void {
    try {
        linhasDoXml('malicioso-xxe.xml');
    } catch (ArquivoInvalidoException $e) {
        expect($e->getMessage())->not->toContain('root:');
    }
});
