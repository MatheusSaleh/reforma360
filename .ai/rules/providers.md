---
paths:
  - app/Providers/**
---

# Providers

- `AppServiceProvider::boot()`: `Model::shouldBeStrict(! app()->isProduction())`, `DB::prohibitDestructiveCommands(app()->isProduction())`, `URL::forceHttps()` em produção, rate limiters nomeados.
- Bind das interfaces de integração (`CalculadoraRegimeRegular` → `RegimeRegularClient`) no `register()`.
- Gates de Horizon/Filament restritos a admins em produção.
- Nada de lógica de negócio nem queries em provider.
