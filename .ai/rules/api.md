---
paths:
  - routes/api.php
  - app/Http/Controllers/Api/**
  - app/Http/Resources/**
---

# API pública (Sanctum)

- Versionada: `/api/v1/...`, controllers em `App\Http\Controllers\Api\V1\`.
- Autenticação `auth:sanctum` com Personal Access Tokens e abilities (`simulacao:create`, `catalogo:read`).
- Respostas sempre por `JsonResource`/`ResourceCollection` — nunca model ou array cru — e incluem o indicador de simulação.
- Mesmos FormRequests/Services da web; a API não tem regra própria.
- Erros no formato padrão do Laravel (422 com `errors`); exceções de domínio mapeadas em `bootstrap/app.php` (`withExceptions`).
- Rate limit por token (`throttle:api`).
- Paginação com `->paginate()`/`cursorPaginate()` em listagens.
