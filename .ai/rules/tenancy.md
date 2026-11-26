---
paths:
  - app/Models/**
  - app/Policies/**
  - app/Http/Controllers/**
  - app/Actions/Fortify/**
---

# Autenticação e escopo por empresa (multi-tenant simples)

Decidido na #5.

- **Autenticação:** Laravel Fortify, só `registration` e `resetPasswords` (`config/fortify.php`). Views Blade em `resources/views/auth/`, registradas no `FortifyServiceProvider`. Após login/registro vai para `/produtos`.
- **Empresa:** cada usuário pertence a **uma** empresa (`users.empresa_id`, obrigatório). O registro (`CreateNewUser`) cria empresa + usuário numa transação; CNPJ opcional, validado por `App\Rules\Cnpj` e único.
- **Escopo:** sem global scope. Consulte e crie sempre pela relação: `$user->empresa->produtos()`. Nunca aceite `empresa_id` vindo do request (não está no `#[Fillable]` do Produto).
- **Autorização:** policy por model; recurso de outra empresa responde **404** (`Response::denyAsNotFound()`), não 403, para não revelar que existe. Controller chama `Gate::authorize(...)` em toda ação.
- Todo model novo pertencente a uma empresa: `empresa_id` com FK `cascadeOnDelete`, índice começando por `empresa_id`, policy no mesmo padrão de `ProdutoPolicy` e teste de isolamento entre empresas.
