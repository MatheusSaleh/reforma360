---
paths:
  - database/migrations/**
  - database/seeders/**
  - database/factories/**
---

# Migrations, Seeders e Factories

## Migrations
- Banco de produção: **MySQL**. Testes usam o banco configurado no `phpunit.xml` — não escreva SQL específico de um só banco sem necessidade.
- Uma migration por mudança; nunca edite migration já mergeada em `main`, crie outra.
- FKs com `foreignId()->constrained()` e `cascadeOnDelete()`/`restrictOnDelete()` explícitos.
- Índices para colunas de filtro/busca (`ncm`, `user_id`, `status`, `created_at` de importações).
- Dinheiro: `decimal(15,2)` (ver decisões abaixo). Alíquotas: `decimal(7,4)`. **Nunca** `float`/`double`.
- NCM `char(8)`, NBS `char(9)`, CNPJ `char(14)`.
- `down()` funcional.

## Seeders
- Idempotentes: `php artisan db:seed` deve rodar duas vezes seguidas sem erro (`updateOrCreate`/`upsert` ou limpeza prévia em ordem reversa de FK).
- `DatabaseSeeder` cria o usuário de demonstração e um catálogo de exemplo (dados fictícios) para o deploy de demo.
- Dados de referência vindos da Receita (NCM, cClassTrib) **não** são seedados à mão: vêm de sincronização com `/dados-abertos` (command/job).

## Factories
- Gere valores válidos (NCM de 8 dígitos, CNPJ com DV válido) e dinheiro sem float.
- Estados nomeados para variações de domínio.

## Decisões registradas
- **Dinheiro (#5):** coluna `decimal(15,2)` com cast `decimal:2` (string `"1200.00"`), o mesmo formato que a calculadora usa. Aritmética com `bcmath`. Nunca centavos em int, nunca float.
- **Migration que adiciona coluna obrigatória em tabela com dados (#5):** crie a coluna `nullable`, preencha os registros existentes e só depois aplique `->nullable(false)->change()` + FK. O MySQL não faz rollback de DDL: se a FK falhar, a coluna fica criada e a migration não roda de novo. Os testes (SQLite vazio) não pegam isso: rode `sail artisan migrate` com dados no banco antes de abrir o PR.
