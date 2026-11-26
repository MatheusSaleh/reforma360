---
paths:
  - app/Integrations/**
  - config/services.php
---

# Integrações (calculadoras da Receita e DANFSe)

Ative a skill `calculadora-rtc`.

- Um client por serviço em `App\Integrations\Calculadora\` (regime regular: `ReceitaCalculadoraClient` implementa `Contracts\CalculadoraTributos`). Domínio depende de **interfaces** (`Contracts\...`), bindadas no `AppServiceProvider` — facilita fake em teste.
- `Http::baseUrl(config('services.calculadora_regular.url'))->timeout(...)->connectTimeout(...)->retry(...)->acceptJson()`. Configuração em `config/services.php` ← `.env` (`CALCULADORA_REGIME_REGULAR_URL`, `CALCULADORA_SIMPLES_URL`, `DANFSE_URL`). Nunca `env()` fora de `config/`.
- Retry só em falha de conexão/5xx; 4xx é erro de payload → exceção de domínio, sem retry.
- Entrada e saída em **DTOs** (`readonly` classes) em `App\Integrations\Calculadora\Data\`. Nenhum array cru da API sai do client.
- Erros viram exceções próprias (`CalculadoraIndisponivelException`, `PayloadInvalidoException`) com contexto (id da simulação), sem logar dado sensível.
- Não descubra endpoints/campos por tentativa: confira o OpenAPI/README do serviço em `services/<repo>/` e grave um exemplo real (anonimizado) em `tests/Fixtures/calculadora/`.
- Clients existentes: `ReceitaCalculadoraClient` (cálculo com observabilidade), `DadosAbertosClient`, `XmlTributacaoClient` (gerar/validar XML do grupo), `DanfseClient` (PDF). Todos usam exceções de domínio; `CalculadoraIndisponivelException::mensagemParaUsuario()` vira o aviso de serviço fora (chave de erro `simulacao`, que as telas exibem).
- Dados de referência (`/dados-abertos`) podem ser cacheados (`Cache::remember`) com TTL longo; o resultado de simulação, não.
