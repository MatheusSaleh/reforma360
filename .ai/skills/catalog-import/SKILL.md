---
name: catalog-import
description: "Use ao implementar ou alterar a importação de catálogo do Reforma360: interface CatalogImporter (Strategy), CsvImporter, XlsxImporter, NfeXmlImporter, validação linha a linha, validação XSD de NF-e/NFS-e, upload de arquivos, processamento em fila e relatório de erros por linha."
---

# Importação de catálogo

## Desenho (Strategy)

```
app/Importers/
  CatalogImporter.php        interface
  CatalogImporterResolver.php escolhe a estratégia pelo tipo/MIME
  CsvImporter.php
  XlsxImporter.php
  NfeXmlImporter.php
  Data/LinhaCatalogo.php     DTO readonly (descricao, ncm|nbs, preco, linha)
  Data/ErroLinha.php         (linha, campo, mensagem)
```

```php
interface CatalogImporter
{
    public function suporta(string $mimeType, string $extensao): bool;

    /** @return iterable<int, LinhaCatalogo|ErroLinha> */
    public function ler(string $caminho): iterable;
}
```

- Importador **só lê e normaliza**. Persistência fica em `App\Services\Importacao\ImportarCatalogoService`; simulação, nos jobs de simulação.
- Resolver recebe as estratégias por injeção (tag no container) — adicionar formato novo = nova classe, sem editar `if`s.

## Fluxo

1. `ImportarCatalogoRequest` valida upload (`file`, `mimes:csv,txt,xlsx,xml`, `max:`).
2. Controller invokável salva no disco **privado** e cria `Importacao` (status `Pendente`).
3. `ProcessarImportacaoJob` resolve o importador, itera em stream, valida cada linha, grava itens válidos e erros por linha, atualiza contadores.
4. Ao final, opcionalmente dispara o lote de simulação (`Bus::batch`).
5. Progresso via evento broadcast em canal privado.

## Regras

- Stream: `LazyCollection`/leitura em chunks; nunca `file()`/carregar XLSX inteiro sem necessidade.
- Validação de linha com `Validator::make($linha, $regras)` reaproveitando as regras do cadastro manual (extrair para classe/`Rule` compartilhada). Erro numa linha não aborta o arquivo.
- Normalização: trim, NCM/NBS só dígitos, preço com vírgula decimal BR → string decimal (sem float), encoding (detecte/ converta para UTF-8; CSV com `;`).
- Cabeçalho do CSV/XLSX documentado num modelo baixável; aceite variações de caixa/acentos mapeando aliases.
- XML: `XMLReader`/`DOMDocument` com `LIBXML_NONET`, sem `LIBXML_NOENT` (evita XXE). Valide contra o XSD oficial (arquivos em `resources/xsd/`) antes de extrair itens; erro de XSD vira erro da importação com a mensagem do libxml.
- Dados de cliente: nunca logar conteúdo de linha/XML; apague o arquivo após a retenção definida.

## Testes

- Fixtures pequenas em `tests/Fixtures/importacao/` (válido, com erros por linha, encoding Latin-1, XML inválido no XSD).
- Unit por importador (entrada → DTOs/erros esperados) + feature do upload (`Storage::fake('local')`, `Bus::fake()`/`Queue::fake()`).
