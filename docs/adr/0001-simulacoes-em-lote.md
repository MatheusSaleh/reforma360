# ADR 0001 — Processar simulações em filas com lotes do Laravel

**Status** · Aceita

## Contexto

Uma simulação pode abranger todo o catálogo ou os itens de um documento fiscal importado. A chamada HTTP do usuário precisa terminar enquanto os cálculos continuam. O usuário precisa acompanhar o progresso e consultar os itens calculados quando parte do lote falha.

## Decisão

`IniciarSimulacaoEmLote` grava um retrato dos itens e seus valores em `simulacao_itens`. Depois que a transação termina, `DespacharLoteDaSimulacao` usa `Bus::batch()` para enfileirar um `SimularItensJob` por bloco de itens. Cada bloco agrupa uma chamada à calculadora, com tamanho definido por `SIMULACAO_ITENS_POR_CHAMADA` (50 itens por padrão). O Horizon processa a fila `simulacoes` no Redis.

O lote permite falhas parciais com `allowFailures()`. Ao final, `FinalizarSimulacao` marca os itens sem resultado e consolida totais, contadores e status. O evento `SimulacaoAtualizada` publica o progresso em um canal privado do Reverb. A tela recarrega periodicamente se a conexão WebSocket falhar.

## Alternativas consideradas

- **Calcular durante a requisição.** Tem menos infraestrutura, mas o cliente precisa aguardar todas as chamadas e pode perder a resposta por timeout.
- **Enfileirar um job por item.** Permite repetir um item isoladamente, ao custo de mais jobs e chamadas HTTP.

## Consequências

- O retrato mantém a base de cálculo estável mesmo que alguém edite o catálogo durante o processamento.
- O progresso e o resultado parcial permanecem disponíveis quando um bloco falha. Nas novas tentativas, o serviço processa somente itens ainda sem resultado.
- O fluxo depende de workers ativos, Redis e acesso do Horizon à calculadora. A linha do tempo reaproveita esse fluxo com um lote por ano.

Código de referência em [DespacharLoteDaSimulacao](../../app/Actions/Simulacao/DespacharLoteDaSimulacao.php), [SimularItensJob](../../app/Jobs/SimularItensJob.php) e [FinalizarSimulacao](../../app/Actions/Simulacao/FinalizarSimulacao.php).
