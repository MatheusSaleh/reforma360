# Testing Best Practices (Reforma360)

## Pest is mandatory

Write tests with Pest (`pestphp/pest` ^5). Create with:

```bash
php artisan make:test --pest {Name}
php artisan make:test --pest --unit {Name}
```

Do not put `Feature/` or `Unit/` inside the `{Name}` argument (Artisan already nests them).

## Test locations

| Área | Path |
|------|------|
| Feature (HTTP, API, Filament, jobs) | `tests/Feature/` |
| Unit | `tests/Unit/` |

Match `test()` vs `it()` to sibling files. This template uses `test()`.

## Running

```bash
php artisan test --compact --filter=testName
composer test
```

Run the narrowest set that covers the change.

## Database

Use o banco definido no `phpunit.xml` (SQLite `:memory:` ou MySQL de teste) — nunca o banco de dev. Enable `RefreshDatabase` (or `LazilyRefreshDatabase`) on Feature tests that persist data. Do not mix a second approach in the same suite.

## Fakes

Use `Http::fake()`, `Mail::fake()`, `Queue::fake()`, `Event::fake()`, `Notification::fake()` at the boundary under test. Call `Event::fake()` after factory setup if models fire creating events.

## Don't

- Don't delete tests without approval
- Don't assert private internals of Services when an HTTP/Filament outcome is enough
- Don't hit real Redis/Horizon in unit tests — fake the queue
