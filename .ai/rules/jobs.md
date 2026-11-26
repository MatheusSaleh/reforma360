---
paths:
  - app/Jobs/**
  - app/Events/**
  - app/Listeners/**
  - config/horizon.php
  - config/reverb.php
---

# Jobs, lotes e tempo real

- Simulação em lote: `Bus::batch([...SimularItemJob])` com `->name()`, `->allowFailures()` quando falha de um item não deve parar o lote, e callbacks `then/catch/finally` que só atualizam status (lógica no Service).
- Jobs com `Batchable` checam `if ($this->batch()?->cancelled()) return;`.
- Payload mínimo: passe IDs/models (SerializesModels), não arrays enormes.
- Defina `$tries`, `$backoff`, `$timeout` (e `retry_after` da conexão maior que o maior `timeout`). Falha de calculadora indisponível → `release()`/retry com backoff; payload inválido → falha definitiva registrada no item.
- Idempotência: rodar o job duas vezes não duplica resultado (`updateOrCreate` pela chave do item/ano).
- Filas nomeadas por prioridade (`simulacoes`, `importacoes`, `default`) e configuradas em `config/horizon.php`.
- Progresso em tempo real: evento `ShouldBroadcast` (ex.: `ProgressoLoteAtualizado`) em **canal privado** autorizado em `routes/channels.php`; payload só com contadores/percentual, sem dados fiscais.
- Testes: `Bus::fake()`/`Queue::fake()` para disparo; teste o `handle()` do job diretamente com `Http::fake()`.
