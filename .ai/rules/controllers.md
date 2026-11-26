---
paths:
  - app/Http/Controllers/**
  - app/Http/Requests/**
  - app/Services/**
  - app/Actions/**
---

# Controllers, FormRequests, Services e Actions

## Controllers enxutos
O controller só orquestra:
1. Autoriza (`Gate::authorize(...)` / Policy / `authorize()` do FormRequest).
2. Recebe um `FormRequest` tipado e usa `$request->validated()`.
3. Delega para um Service/Action.
4. Retorna a resposta (view, `RedirectResponse`, `JsonResource`).

**Nunca** no controller: `DB::transaction`, queries com joins/agregações, `Http::`, disparo de jobs/notificações/eventos, cálculo tributário.

## Só REST ou invokável
Resource controllers têm apenas `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`.
Ações fora do CRUD viram controller invokável (`__invoke`):
- `SimularItemController`, `SimularLoteController`, `CompararRegimesController`
- `ImportarCatalogoController`, `GerarRelatorioPdfController`, `GerarDanfseController`

## FormRequests
- Um por ação (`StoreProdutoRequest`, `ImportarCatalogoRequest`), em `App\Http\Requests\{Domínio}\`.
- Regras em array (`['required', 'string', 'size:8']`), regras customizadas em `App\Rules` (ex.: `Ncm`, `Cnpj`).
- Mensagens e `attributes()` em PT-BR (ou `lang/pt_BR/validation.php`).
- Normalize entrada em `prepareForValidation()` (ex.: remover pontos do NCM/CNPJ).

## Services / Actions
- `App\Services\{Domínio}\{Nome}Service` para regras com vários métodos; `App\Actions\{Domínio}\{Verbo}{Coisa}` para uma operação única (`handle()`/`__invoke()`).
- Domínios previstos: `Catalogo`, `Simulacao`, `Importacao`, `Relatorio`.
- Recebem DTOs/arrays validados — nunca o `Request`.
- `declare(strict_types=1);`, tipos de retorno explícitos, array shapes no PHPDoc.
- Erros de domínio: exceções próprias em `App\Exceptions\{Domínio}\` (ou `ValidationException` quando é erro de entrada do usuário).
- Injete dependências pelo construtor (property promotion); nada de `app()`/`resolve()` no meio do método.
