# ADR 0003 — Usar Strategy para os importadores de catálogo

**Status** · Aceita

## Contexto

CSV, XLSX e XML fiscal variam em estrutura e leitura. Todos alimentam o mesmo catálogo e precisam compartilhar as regras de validação, atualização de produtos e registro de erros por linha.

## Decisão

`CsvImporter`, `XlsxImporter` e `NfeXmlImporter` implementam `CatalogImporter`. São registrados no contêiner com a tag `catalog-importers`. O `CatalogImporterResolver` seleciona a implementação pelo `FormatoImportacao`. Cada estratégia lê o arquivo e entrega lotes de `LinhaPlanilha` ao `ImportarCatalogoService`, que valida as linhas, persiste os itens válidos e registra os erros.

A importação roda na fila `importacoes`, com o arquivo no disco privado. O importador de XML também implementa `ImportadorDeDocumentoFiscal` para expor os dados da operação e os metadados da nota usados no fluxo fiscal.

## Alternativas consideradas

- **Centralizar os formatos em um único importador.** Simplifica o registro inicial, mas acopla mudanças de leitura de cada formato à rotina central.
- **Implementar um fluxo completo por formato.** Dá autonomia a cada leitor, mas duplica validação, persistência e tratamento de erros.

## Consequências

- Um novo formato precisa de uma estratégia, do respectivo registro e de testes. A validação e a persistência comuns permanecem no serviço de importação.
- O contrato de leitura em lotes permite que os leitores de planilha processem os dados em partes. Particularidades de documentos fiscais ficam na interface especializada.

Código de referência em [CatalogImporter](../../app/Importers/CatalogImporter.php), [CatalogImporterResolver](../../app/Importers/CatalogImporterResolver.php) e [ImportarCatalogoService](../../app/Services/Importacao/ImportarCatalogoService.php).
