---
name: laravel-best-practices
description: "Aplique sempre que escrever, revisar ou refatorar PHP Laravel no Reforma360: controllers, models, migrations, form requests, services, actions, policies, jobs, batches, commands, rotas de API e queries Eloquent. Cobre N+1, cache, autorização, validação, tratamento de erros, filas/Horizon, Sanctum, HTTP client e decisões de arquitetura. Também para code review Laravel."
---

# Laravel Best Practices (Reforma360)

Índice de regras. Cada arquivo em `rules/` ensina o que fazer e por quê. Para a sintaxe exata, confira a documentação da versão instalada (`composer show laravel/framework`) ou o código irmão.

## Stack

- PHP ^8.4, Laravel 13, Pest 5, Pint, Larastan nível 8
- MySQL, Redis, Horizon, Reverb, Sanctum, Filament (admin)
- Calculadoras da Receita + DANFSe como serviços HTTP internos — skill **calculadora-rtc**
- Importação CSV/XLSX/XML — skill **catalog-import**

## Consistência primeiro

Antes de aplicar qualquer regra, veja o que o app já faz. Prefira o padrão existente ao teoricamente melhor. Leia também `.ai/rules/index.md` e `.ai/guidelines/reforma360.md`.

## Como aplicar

1. Veja arquivos alterados, vizinhos e testes relacionados.
2. Mapeie cada assunto da mudança para a tabela abaixo e leia a regra antes de editar.
3. Faça a menor mudança coerente.
4. Rode os testes Pest mais estreitos (`php artisan test --compact --filter=...`).
5. `vendor/bin/pint --dirty --format agent` se alterou PHP.

## Índice

| Assunto | Ler |
| --- | --- |
| Quantidade de queries, eager loading, índices, grandes volumes | [`rules/db-performance.md`](rules/db-performance.md) |
| Subqueries, agregações, ordenação complexa | [`rules/advanced-queries.md`](rules/advanced-queries.md) |
| Models, relacionamentos, scopes, casts | [`rules/eloquent.md`](rules/eloquent.md) |
| Auth, autorização, entrada, segredos, uploads | [`rules/security.md`](rules/security.md) |
| Form Requests e validação | [`rules/validation.md`](rules/validation.md) |
| Controllers, rotas, middleware, API Sanctum | [`rules/routing.md`](rules/routing.md) |
| Migrations, colunas, FKs, índices | [`rules/migrations.md`](rules/migrations.md) |
| Jobs, retries, unicidade, Horizon, batches | [`rules/queue-jobs.md`](rules/queue-jobs.md) |
| Cache, invalidação, locks | [`rules/caching.md`](rules/caching.md) |
| HTTP de saída, retries, timeouts, fakes | [`rules/http-client.md`](rules/http-client.md) |
| Exceções, report, logs | [`rules/error-handling.md`](rules/error-handling.md) |
| Eventos, notificações, broadcast | [`rules/events-notifications.md`](rules/events-notifications.md) |
| Mailables | [`rules/mail.md`](rules/mail.md) |
| Agendamentos | [`rules/scheduling.md`](rules/scheduling.md) |
| Collections, iteração lazy | [`rules/collections.md`](rules/collections.md) |
| Config e env | [`rules/config.md`](rules/config.md) |
| Pest, factories, fakes | [`rules/testing.md`](rules/testing.md) |
| Nomes, estilo PHP | [`rules/style.md`](rules/style.md) |
| Services, DTOs, estrutura | [`rules/architecture.md`](rules/architecture.md) |

## Regras de decisão

- Prefira recursos do framework e abstrações existentes a helpers ou dependências novas.
- Sem abstração especulativa. Extraia quando houver fronteira de domínio clara, duplicação real ou comportamento testável isoladamente (ex.: clients das calculadoras, importadores).
- Nada de acesso a banco em Blade/closures de Filament; evite N+1 escondido.
- Controllers e páginas Filament finos; Services/Actions têm a regra.
- Persistir com `$request->validated()`. Nunca `Model::unguard()` global.
- Nunca `float` para dinheiro ou alíquota.
