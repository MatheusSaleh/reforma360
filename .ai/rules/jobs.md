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

## Implementado na #8 (simulação em lote)

- `IniciarSimulacaoEmLote` grava um retrato do catálogo em `simulacao_itens` e despacha `Bus::batch()` de `SimularItensJob` (um job por bloco de `config('simulacao.itens_por_chamada')` itens = **uma** chamada à calculadora). `->allowFailures()->finally(...)` chama `FinalizarSimulacao` (totais com `bcadd`, status, notificação).
- `SimularLoteService` é idempotente: só processa itens com `estado` nulo. Calculadora fora → `release()` com backoff; 4xx/resposta inesperada → itens do bloco marcados com erro (sem retry).
- Progresso: `SimulacaoAtualizada` (`ShouldBroadcastNow`, canal privado `simulacoes.{id}`, só contadores) transmitido por `TransmitirProgresso`, que **engole falhas do Reverb** (`report()`): broadcast nunca derruba o job. A tela recarrega periodicamente se o WebSocket não conectar.
- **`retry_after` da conexão Redis = 700 s** (`config/queue.php`), maior que o maior `timeout` de job (importação: 600 s) e que o `timeout` do supervisor do Horizon (620 s). Ao criar job mais longo, ajuste os três.
- Teste de autorização de canal: os canais são registrados no driver da inicialização (`null` nos testes); ao trocar para `reverb` no teste, faça `require base_path('routes/channels.php')` de novo.
