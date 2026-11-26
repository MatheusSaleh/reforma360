---
paths:
  - app/Importers/**
  - app/Jobs/Import*/**
---

# Importadores de catálogo

Ative a skill `catalog-import`.

- Todos implementam `App\Importers\CatalogImporter`; o importador é escolhido por uma factory/resolver a partir do tipo do arquivo — nada de `if ($ext === 'csv')` espalhado.
- Saída comum: coleção/iterável de DTO `LinhaCatalogo` (ou `ItemImportado`) + erros por linha. O importador **não** grava no banco nem chama a calculadora.
- Arquivos grandes: leitura em stream (`LazyCollection`, leitor em chunks). Nunca carregue o arquivo inteiro em memória.
- Validação linha a linha com `Validator::make()` usando as mesmas regras do cadastro manual; erro de uma linha não aborta o arquivo.
- XML NF-e/NFS-e: parse com `DOMDocument`/`XMLReader` com entidades externas desligadas (XXE); validação XSD antes de extrair.
- Upload validado no FormRequest (`mimes`, `max`), salvo em disco privado, processado em job.
