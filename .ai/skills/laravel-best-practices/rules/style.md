# Conventions & Style (Reforma360)

## PHP

- New PHP files: `declare(strict_types=1);` (Pint enforces this)
- Explicit parameter, return, and property types — Pint converts PHPDoc to native types when possible
- Constructor property promotion
- Curly braces even for single-line control structures
- Format with Pint: `vendor/bin/pint --dirty --format agent`
- Larastan (PHPStan) deve passar: `vendor/bin/phpstan analyse`

## Follow Laravel naming conventions

| What | Convention | Good | Bad |
|------|-----------|------|-----|
| Controller | singular | `SessionController` | `SessionsController` |
| Invokable controller | verb + subject | `SyncQuoteController` | `QuoteHelperController` |
| Model | singular | `User` | `Users` |
| Table | plural, snake_case | `article_comments` | `articleComments` |
| Column | snake_case | `meta_title` | `articleMetaTitle` |
| Route name | dots | `api.widgets.store` | `api-widgets-store` |
| Method | camelCase | `redirectRouteName` | `redirect_route_name` |
| Filament Resource | singular + Resource | `WidgetResource` | `Widgets` |
| Enum | singular TitleCase keys | `UserType` | `UserTypes` |

## Prefer shorter readable syntax

| Verbose | Shorter |
|---------|---------|
| `Session::get('cart')` | `session('cart')` |
| `$request->input('name')` | `$request->string('name')` |
| `return Redirect::back()` | `return back()` |
| `Carbon::now()` | `now()` |
| `->where('column', '=', 1)` | `->where('column', 1)` |
| `->orderBy('created_at', 'desc')` | `->latest()` |

## Prefer `Str`, `Arr`, `Number` helpers

Use Laravel helpers over raw PHP for strings/arrays when available (UTF-8 safe, chainable).

## No unnecessary comments

Prefer descriptive names. Comments only for non-obvious intent. Config files may keep explanatory comments.

## Feature checklist (quick)

- [ ] FormRequest for body input
- [ ] Service owns business logic when rules are non-trivial
- [ ] Model uses `#[Guarded]` / `$guarded` — never global `unguard()`
- [ ] Persist with `$request->validated()`
- [ ] Named routes
- [ ] Pest test under `tests/Feature/` or `tests/Unit/`
- [ ] Pint + Larastan limpos
