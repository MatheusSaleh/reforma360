# Routing & Controllers Best Practices (Reforma360)

## Route files

| Área | Arquivo | Prefixo URL |
|------|---------|-------------|
| Web | `routes/web.php` | raiz |
| API | `routes/api.php` | `/api` — Sanctum |
| Console | `routes/console.php` | Artisan / schedule |
| Filament | PanelProvider | `/admin` (ou path configurado) |

Registered in `bootstrap/app.php`. Prefer named routes and `route()`. Health check: `/up`.

## Controllers: REST or invokable

Public methods allowed:

| Forma | Métodos |
|-------|---------|
| Resource | `index`, `show`, `create`, `store`, `edit`, `update`, `destroy` |
| Invokable | `__invoke` only |

When an action does not fit a resource verb, create a dedicated invokable controller.

## Keep controllers thin

Controllers must not contain business rules.

1. Accept FormRequest (when there is input)
2. Delegate to Service
3. Translate result to HTTP (JSON, redirect)

## Type-hint Form Requests

Incorrect:
```php
public function store(Request $request): JsonResponse
{
    $data = $request->validate([...]);
}
```

Correct:
```php
public function store(StoreWidgetRequest $request): JsonResponse
{
    $widget = $this->widgetService->store($request->validated());

    return response()->json($widget, 201);
}
```

## Sanctum API

- Protect API routes with `auth:sanctum`
- API pública versionada (`/api/v1/...`) com Personal Access Tokens
- Respostas via Eloquent API Resources
- Do not put secrets in route files

## Localisation

Use `route()` helpers. Don't hardcode URLs in PHP except well-known internal tools (`/horizon`).
