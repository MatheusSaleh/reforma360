---
paths:
  - tests/**
---

# Testes

Ative a skill `pest-testing`.

- Pest; siga `test()` vs `it()` dos arquivos irmãos (padrão do projeto: `test()`).
- Feature em `tests/Feature/{Domínio}/`, unit em `tests/Unit/`. Maioria deve ser feature.
- **Nunca** chamar calculadora/DANFSe reais: `Http::preventStrayRequests()` + `Http::fake()` com fixtures em `tests/Fixtures/`.
- Cálculo tributário e arredondamento têm testes com valores esperados explícitos (datasets), incluindo bordas (zero, centavos, alíquota reduzida, ano de transição).
- Importadores: fixtures pequenas em `tests/Fixtures/importacao/` (CSV/XLSX/XML válidos e com erro), dados fictícios.
- `RefreshDatabase` só onde persiste. Dados via factories.
