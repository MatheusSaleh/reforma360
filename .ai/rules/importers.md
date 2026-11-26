---
paths:
  - app/Importers/**
  - app/Jobs/Import*/**
---

# Importadores de catálogo

Ative a skill `catalog-import`.

- Todos implementam `App\Importers\CatalogImporter` e entram na tag `catalog-importers`; o `CatalogImporterResolver` escolhe pelo `FormatoImportacao` — nada de `if ($ext === 'csv')` espalhado.
- Saída comum: lotes de `LinhaPlanilha` via callback. O importador **não** valida, não grava no banco e não chama a calculadora (isso é do `ImportarCatalogoService`).
- Arquivos grandes: leitura em stream (`LazyCollection`, leitor em chunks). Nunca carregue o arquivo inteiro em memória.
- Validação linha a linha com `Validator::make()` usando as mesmas regras do cadastro manual; erro de uma linha não aborta o arquivo.
- XML NF-e/NFS-e: parse com `DOMDocument`/`XMLReader` com entidades externas desligadas (XXE); validação XSD antes de extrair.
- Upload validado no FormRequest (`mimes`, `max`), salvo em disco privado, processado em job.
