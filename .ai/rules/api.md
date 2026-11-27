---
paths:
  - routes/api.php
  - app/Http/Controllers/Api/**
  - app/Http/Resources/**
  - config/api.php
  - config/scribe.php
---

# API pública (Sanctum + Scribe)

- Versionada: `/api/v1/...`, controllers em `App\Http\Controllers\Api\V1\` (estendem o `Api\V1\Controller`, que dá `usuario()` e `porPagina()`), Resources em `App\Http\Resources\V1\`.
- Autenticação `auth:sanctum` com Personal Access Tokens criados pelo usuário em **Tokens de API** (`/conta/tokens`). O token opera na empresa do usuário; recurso de outra empresa → 404 pela policy, como na web.
- Permissões (abilities) ficam em `config('api.permissoes')` e são exigidas na rota com `abilities:<permissao>`: `produtos:ler`, `produtos:escrever`, `simulacoes`. Permissão nova vai no config (a tela de tokens lê de lá) e na rota.
- Rate limit por token: `throttle:api` no grupo e `throttle:api-simulacoes` nas rotas que chamam a calculadora na hora (limites em `config/api.php` ← `.env`).
- Respostas sempre por `JsonResource`, nunca model, array cru ou resposta da calculadora. Valores e alíquotas como **string decimal**. Toda resposta com cálculo traz `calculo_simulado` e `aviso` (`AvisoDeSimulacao::TEXTO`).
- Mesmos FormRequests, Services, Actions e Policies da web; a API não tem regra própria. Campos novos em FormRequest usado pela API → atualize `bodyParameters()` (documentação).
- Erros: padrão do Laravel (422 com `errors`). Em `bootstrap/app.php`: 503 da calculadora, e 401/403 (permissão)/429 com mensagem em português para `api/*`.
- Listagens paginadas com `por_pagina` (1–100).

## Documentação (Scribe)

- Atributos do Scribe nos controllers (`#[Group]`, `#[Endpoint]`, `#[ResponseFromApiResource]`, `#[Response]`). Sem response calls: gerar a doc não chama a API nem a calculadora. Exemplo da simulação de item em `.scribe/exemplos/simulacao-item.json` (resposta real gravada).
- Mudou endpoint, Resource ou FormRequest da API → `sail artisan scribe:generate` e commite `.scribe/`, `resources/views/scribe/` e `public/vendor/scribe/`.
- Os exemplos de requisição usam o `APP_URL` de quando a doc foi gerada, e a coleção Postman/OpenAPI fica em `storage/app/private/scribe` (fora do git): **rode `scribe:generate` no deploy**.
