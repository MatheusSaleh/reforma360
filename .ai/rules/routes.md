---
paths:
  - routes/**
---

# Rotas

- Toda rota tem nome (`->name('produtos.index')`); gere URLs com `route()`.
- Prefira `Route::resource()` / `Route::apiResource()` com `->only([...])`.
- Agrupe por middleware (`auth`, `verified`) em vez de repetir por rota.
- Rotas invokáveis: `Route::post('simulacoes/lote', SimularLoteController::class)->name('simulacoes.lote')`.
- Upload/importação e endpoints que disparam simulação têm rate limit (`throttle:`) definido em `AppServiceProvider`/`bootstrap/app.php`.
- `/horizon`, `/admin` (Filament) e Reverb não ficam abertos em produção.
