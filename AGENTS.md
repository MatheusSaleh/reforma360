# Reforma360 — Agent Guidelines

Guia para agentes de IA (Claude Code, Cursor, Codex, Copilot) que trabalham neste repositório. Siga à risca.

Reforma360 é um simulador da Reforma Tributária do Consumo (CBS/IBS — LC 214/2025) para pequenas empresas e contadores. A aplicação Laravel concentra toda a lógica de produto e consome, como serviços internos, as calculadoras open source da Receita Federal. Visão geral, arquitetura e roadmap: `README.md`.

## Stack

- PHP ^8.4 · Laravel 13 · Pest 5 · Pint (`pint.json`, `declare_strict_types`) · Larastan nível 7 (`phpstan.neon.dist`)
- Ambiente local: Laravel Sail (`compose.yaml`) — PHP 8.5, MySQL 8.4, Redis. Testes em SQLite `:memory:` (`phpunit.xml`)
- MySQL · Redis · Fortify (login, registro, redefinição de senha) · Blade + Tailwind 4 · Laravel Excel (importação CSV/XLSX) · XML de NF-e/NFC-e/NFS-e (DOM nativo)
- Filas Redis com **Horizon** (serviço `horizon` do compose; filas `simulacoes`, `importacoes`, `default`) e tempo real com **Reverb** (serviço `reverb`, canais privados em `routes/channels.php`)
- Previstos: Sanctum (API pública) · Filament (painel admin)
- Traduções em `lang/pt_BR/` (parcial: chave ausente cai no inglês); locale padrão `pt_BR`
- Serviços internos (Docker, **sem modificações**, sem acesso público):
  - `calculadora-regime-regular` (Java/Spring Boot)
  - `calculadora-simples-nacional` (Java/Spring Boot)
  - `danfse` (Go)
- Código upstream em `services/` (clonado, **não versionado**). Nunca edite nada em `services/`.

Antes de usar a API de um pacote, confira a versão instalada (`composer show <pacote>`, `package.json`). Não assuma versão.

## Pastas de IA

| Path | Para quem | Conteúdo |
|------|-----------|----------|
| `AGENTS.md` / `CLAUDE.md` | Todos os agentes | Este guia (entrada) |
| `.ai/guidelines/` | Todos | Regras de domínio do Reforma360 |
| `.ai/rules/` | Todos | Regras por caminho de arquivo (`index.md` mapeia globs → regra) |
| `.ai/skills/` | Todos | **Fonte canônica** das skills |
| `.claude/skills/`, `.agents/skills/`, `.cursor/skills/`, `.github/skills/` | Claude Code, Codex/Amp, Cursor, Copilot | Symlinks para `.ai/skills/` |
| `.cursor/rules/` | Cursor | Rule `alwaysApply` apontando para este guia |

Edite skills **apenas** em `.ai/skills/`. Detalhes em `.ai/README.md`.

## Antes de criar ou editar arquivos (obrigatório)

1. Abra `.ai/rules/index.md`, leia toda regra cujos globs cubram os caminhos em escopo.
2. Leia `.ai/guidelines/reforma360.md` quando a mudança tocar cálculo, alíquotas, importação ou integração.
3. Ative a skill do domínio (tabela abaixo) **antes** de escrever código, não quando travar.
4. Olhe arquivos irmãos e siga o padrão existente. Inconsistência é pior que um padrão subótimo.

## Skills — ativar sempre que o domínio bater

| Skill | Quando |
|-------|--------|
| `laravel-best-practices` | Qualquer PHP Laravel (models, controllers, FormRequests, jobs, migrations, queries) |
| `calculadora-rtc` | Integração com as calculadoras da Receita / DANFSe, DTOs, alíquotas, transição 2026→2033 |
| `catalog-import` | Importação CSV/XLSX/XML NF-e, `CatalogImporter`, validação linha a linha, XSD |
| `pest-testing` | Escrever, corrigir ou refatorar testes |
| `conventional-commit` | Usuário pede commit |
| `pull-request` | Usuário pede PR |

## Convenções do projeto (resumo)

- **Controllers enxutos:** só autorizam (Policy/Gate), recebem FormRequest, chamam Service/Action e retornam resposta. Nada de `DB::transaction`, queries de negócio, notificações ou chamadas HTTP no controller.
- **Só métodos REST** (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`) ou **controllers invokáveis**. Nunca métodos ad-hoc (`simular()`, `exportar()`) em resource controllers → `SimularItemController`, `ExportarRelatorioController`.
- **Regra de negócio** em `App\Services\{Domínio}\` ou `App\Actions\{Domínio}\`.
- **Integrações HTTP** só via clients em `App\Integrations\Calculadora\` (nunca `Http::` solto em controller/service de domínio). URLs em `config/services.php` ← `.env`.
- **Importadores** implementam `App\Importers\CatalogImporter` (Strategy): `CsvImporter`, `XlsxImporter`, `NfeXmlImporter`.
- **Dinheiro e alíquotas nunca em `float`.** Valores monetários em centavos (`int`) ou `decimal` com cast; alíquotas como string decimal / `decimal` cast. Ver guideline.
- **Escopo por empresa:** usuário pertence a uma empresa; consultas pela relação (`$user->empresa->produtos()`), policies com 404 para recurso alheio. Ver `.ai/rules/tenancy.md`.
- **Mass assignment:** `$fillable`/`#[Fillable]` ou `$guarded` explícito; nunca `Model::unguard()` global. Persistir com `$request->validated()`.
- `Model::shouldBeStrict()` fora de produção; `DB::prohibitDestructiveCommands()` em produção.
- Resultados são **simulações**: toda tela/relatório/resposta de API que mostre cálculo deve deixar isso claro (o retorno da Receita marca `CALCULO_SIMULADO`).
- Textos de UI, mensagens de validação e nomes de domínio em **português**; código de infraestrutura pode seguir nomes do Laravel em inglês. Siga o que os arquivos irmãos já fazem.

## Comandos (fazer as coisas do jeito Laravel)

- Gere arquivos com `php artisan make:*` e sempre `--no-interaction` (classe genérica: `make:class`).
- Ao criar um model, crie também factory (e seeder quando fizer sentido).
- Se o projeto estiver rodando com Sail/Docker, prefixe com `vendor/bin/sail` (confira `docker-compose.yml`).

## Verificação

Toda mudança precisa de teste automatizado (novo ou atualizado). Rode o mínimo necessário:

- Pest: `php artisan test --compact --filter=...` (suite completa: `composer test`)
- Pint: `vendor/bin/pint --dirty --format agent` depois de alterar PHP (`composer lint` checa, `composer format` corrige)
- Larastan: `composer analyse` quando o escopo for amplo
- CI (GitHub Actions) exige Pest + Larastan + Pint verdes

Testes **nunca** chamam as calculadoras reais: use `Http::fake()` com fixtures em `tests/Fixtures/`.

## Git / Issues / PR

- Cada tarefa é uma issue do GitHub, agrupada por milestone (Semana 1–4).
- Branches: `{tipo}/{issue}-{slug}` → `feat/12-autocomplete-ncm`, `fix/31-arredondamento-cbs`, `chore/3-docker-compose`.
- Commits: Conventional Commits, tipo em inglês + descrição em PT-BR, escopo = número da issue: `feat(#12): adiciona autocomplete de NCM`.
- PR para `main` com `Closes #<issue>` e CI verde.
- Só commit / push / PR com pedido explícito do usuário.

## Não fazer

- Não alterar dependências (`composer require`, `npm install <pkg>`) sem aprovação.
- Não criar pastas-base novas em `app/` sem aprovação.
- Não criar arquivos de documentação sem pedido.
- Não criar scripts de verificação ou usar tinker quando um teste cobre o comportamento.
- Não apagar testes sem aprovação.
- Não expor as calculadoras publicamente nem repassar a resposta crua delas ao usuário final — mapeie para DTOs/Resources.
- Não commitar `.env`, dumps, XMLs reais de clientes (dados fiscais são sensíveis) ou o diretório `services/`.
