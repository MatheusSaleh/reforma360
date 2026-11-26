# Architecture Best Practices (Reforma360)

## Layers

Reforma360 é uma aplicação de produto (web + API) que consome as calculadoras da Receita como serviços internos. Camadas padrão:

```
HTTP / Filament / Job / Command
  → FormRequest or job payload (validation)
  → Service (business logic + side effects)
  → Model / DTO
```

| Layer | Namespace | Responsibility |
|-------|-----------|----------------|
| API / HTTP | `App\Http\Controllers`, `routes/api.php` | Sanctum, map HTTP ↔ Service |
| Form Request | `App\Http\Requests\{Domínio}\` | `rules()`, `authorize()`, `validated()` |
| Filament | `App\Filament\` | Painel admin (operação, não fluxo do usuário final) |
| Service | `App\Services\{Domínio}\` | Business rules; no `RedirectResponse` / Filament views |
| Model | `App\Models\` | Persistence, relationships, `#[Guarded]` |
| Job | `App\Jobs\` | Queue work; Horizon dashboard |
| Integração | `App\Integrations\Calculadora\` | Clients HTTP + DTOs das calculadoras — skill **calculadora-rtc** |
| Importação | `App\Importers\` | `CatalogImporter` (Strategy): CSV, XLSX, XML NF-e |

## Prefer Services over fat controllers / fat Filament pages

Controllers and Filament Resources orchestrate. Services own domain rules.

```php
public function store(StoreWidgetRequest $request): JsonResponse
{
    $widget = $this->widgetService->store($request->validated());

    return response()->json($widget, 201);
}
```

## Use Dependency Injection

Always use constructor injection. Avoid `app()` or `resolve()` inside classes except in providers.

```php
public function __construct(private WidgetService $service) {}
```

## Code to Interfaces at system boundaries

Depend on contracts for payment gateways, carriers, and external APIs. Bind in a service provider.

## Default sort by descending

When listing without an explicit order, use `latest()` / `orderByDesc`.

## Use atomic locks for race conditions

```php
Cache::lock('order-processing-'.$order->id, 10)->block(5, function () use ($order) {
    $order->process();
});
```

## Prefer `Str`, `Arr`, `Number` for UTF-8

Use Laravel helpers over raw PHP for user-facing pt-BR text.

## Use `defer()` for post-response work

Lightweight fire-and-forget (logging, analytics) can use `defer()`. Use queued jobs when work must survive crashes or needs retries.

## Convention over configuration

Follow the installed Laravel version and this repo. Don't invent a second admin stack beside Filament.
