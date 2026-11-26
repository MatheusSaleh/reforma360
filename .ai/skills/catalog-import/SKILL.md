---
name: catalog-import
description: "Use ao implementar ou alterar a importação de catálogo do Reforma360: interface CatalogImporter (Strategy), CsvImporter, XlsxImporter, NfeXmlImporter, validação linha a linha, validação XSD de NF-e/NFS-e, upload de arquivos, processamento em fila e relatório de erros por linha."
---

# Importação de catálogo

## Desenho (Strategy) — implementado na #7

```
app/Importers/
  CatalogImporter.php          interface (lê em lotes por callback)
  CatalogImporterResolver.php  escolhe a estratégia pelo FormatoImportacao (#[Tag('catalog-importers')])
  CsvImporter.php              detecta separador (; ou ,) e encoding (UTF-8 ou ISO-8859-1)
  XlsxImporter.php
  LeitorPlanilha.php           Laravel Excel: OnEachRow + WithChunkReading + WithHeadingRow + SkipsEmptyRows + StringValueBinder
  ColunasCatalogo.php          aliases do cabeçalho (slug) → colunas internas; obrigatórias
  Data/LinhaPlanilha.php       (linha, campos texto) — linha = número real na planilha (cabeçalho = 1)
  Exceptions/ArquivoInvalidoException.php   erro do arquivo inteiro (cabeçalho, vazio, linhas demais)
app/Services/Importacao/ImportarCatalogoService.php  valida, grava, registra erros
app/Services/Catalogo/ReferenciasFiscais.php          NCM/NBS existem? cClassTrib pertence ao CST? (conjuntos em memória)
app/Actions/Importacao/IniciarImportacao.php          salva arquivo (disco local privado), cria Importacao, despacha job
app/Jobs/ProcessarImportacaoJob.php                   fila "importacoes"; calculadora fora → release com backoff
app/Exports/ModeloCatalogoExport.php                  planilha modelo (colunas de código em formato texto)
```

```php
interface CatalogImporter
{
    public function suporta(FormatoImportacao $formato): bool;

    /** @param Closure(list<LinhaPlanilha>): void $aoLerLote */
    public function ler(string $caminho, Closure $aoLerLote): void;
}
```

O Laravel Excel lê em chunks via callbacks (não dá para expor um gerador), por isso a interface entrega **lotes por callback**. Novo formato (ex.: XML da #9) = nova classe + adicioná-la à tag `catalog-importers` no `AppServiceProvider`.

## Fluxo

1. `ImportarCatalogoRequest` valida upload (`extensions` + `mimes`: csv, txt, xlsx; tamanho em `config/importacao.php`).
2. `ImportacaoController@store` chama `IniciarImportacao` (arquivo no disco **privado**, `Importacao` Pendente, job na fila `importacoes`).
3. `ImportarCatalogoService::processar` roda tudo numa transação: erro de **linha** vira `ImportacaoErro` e a importação segue; erro do **arquivo** (`ArquivoInvalidoException`) desfaz tudo e marca Falhou; calculadora fora (`CalculadoraIndisponivelException`) desfaz e o job tenta de novo.
4. Produtos são gravados com `updateOrCreate` pela **descrição** (reimportar atualiza, não duplica).
5. Ao terminar (ou falhar de vez), o arquivo é apagado.
6. A tela de acompanhamento se atualiza com `<meta refresh>` enquanto `status->emAndamento()` (progresso via Reverb fica para a #8).

## Regras

- Stream: `LazyCollection`/leitura em chunks; nunca `file()`/carregar XLSX inteiro sem necessidade.
- Validação de linha com `Validator::make($campos, RegrasProduto::regras())` (mesmas regras do cadastro manual, com `bail` para uma mensagem por campo) + `ReferenciasFiscais`. Erro numa linha não aborta o arquivo; até 1000 erros gravados.
- Normalização: trim, NCM/NBS só dígitos, preço com vírgula decimal BR → string decimal (`NormalizadorFiscal`), encoding detectado no CSV.
- **Zeros à esquerda:** o Excel apaga (CST 000 → 0, cClassTrib 000001 → 1, NCM 01012100 → 1012100). CST e cClassTrib são completados com zeros; NCM só quando tem 7 dígitos. CSV é lido com `StringValueBinder` para não converter "000001" em número.
- Células numéricas do XLSX chegam como int/float: viram texto (`28.9`) no `LeitorPlanilha` e a validação decide (3 casas decimais → erro).
- Cabeçalho do CSV/XLSX documentado num modelo baixável; aceite variações de caixa/acentos mapeando aliases.
- XML: `XMLReader`/`DOMDocument` com `LIBXML_NONET`, sem `LIBXML_NOENT` (evita XXE). Valide contra o XSD oficial (arquivos em `resources/xsd/`) antes de extrair itens; erro de XSD vira erro da importação com a mensagem do libxml.
- Dados de cliente: nunca logar conteúdo de linha/XML; apague o arquivo após a retenção definida.

## Testes

- Fixtures pequenas em `tests/Fixtures/importacao/` (válido, com erros por linha, encoding Latin-1, XML inválido no XSD).
- Feature do upload em `tests/Feature/Importacao/ImportacaoTest.php` (`Storage::fake('local')`, fila sync nos testes, `Queue::fake()` para checar o dispatch). Fixtures: CSV válido (BOM, ;), com erros (um por linha), Latin-1 com vírgula e zeros perdidos, XLSX com células numéricas (gerado com PhpSpreadsheet), cabeçalho inválido, vazio.
