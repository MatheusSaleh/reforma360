---
name: calculadora-rtc
description: "Use ao integrar com as calculadoras da Reforma Tributária da Receita Federal (regime regular, Simples Nacional) ou com o gerador de DANFSe: clients HTTP, DTOs, alíquotas nominais, cronograma de transição 2026→2033, comparador Simples × regular, dados abertos (NCM/cClassTrib) e testes com Http::fake. Ative para app/Integrations, config/services.php, Services de Simulacao e qualquer código que monte ou leia payload das calculadoras."
---

# Calculadoras RTC (Receita Federal)

## Serviços

| Serviço | Config | Env | Upstream |
|---------|--------|-----|----------|
| Regime regular | `services.calculadora_regular.url` | `CALCULADORA_REGIME_REGULAR_URL` | `services/calculadora-rtc-regime-regular` |
| Simples Nacional | `services.calculadora_simples.url` | `CALCULADORA_SIMPLES_URL` | `services/calculadora-rtc-simples-nacional` |
| DANFSe | `services.danfse.url` | `DANFSE_URL` | `services/calculadora-rtc-gerador-danfse` |

Rodam em containers internos, **sem modificações** e sem porta pública. Nunca edite `services/`.

## Antes de codar contra um endpoint

1. Leia o README e a especificação OpenAPI/Swagger do repositório em `services/<repo>/` (ou `/swagger-ui`/`/v3/api-docs` do container em dev).
2. Faça uma chamada real em dev e salve request/response **anonimizados** em `tests/Fixtures/calculadora/<servico>/<caso>.json`.
3. Modele DTOs a partir da fixture. Não invente nomes de campo.

## Estrutura

```
app/Integrations/Calculadora/
  Contracts/RegimeRegular.php          interface usada pelo domínio
  Contracts/SimplesNacional.php
  Contracts/GeradorDanfse.php
  RegimeRegularClient.php              implementação HTTP
  SimplesNacionalClient.php
  DanfseClient.php
  Data/                                DTOs readonly (entrada e saída)
  Exceptions/                          CalculadoraIndisponivelException, PayloadInvalidoException
```

Bind `Contract → Client` em `AppServiceProvider::register()`.

## Client (padrão)

```php
final class RegimeRegularClient implements RegimeRegular
{
    public function simular(SimulacaoRequest $dados): SimulacaoResultado
    {
        $response = $this->http()->post('/<endpoint-do-openapi>', $dados->toPayload());

        if ($response->clientError()) {
            throw PayloadInvalidoException::fromResponse($response);
        }

        $response->throw();

        return SimulacaoResultado::fromArray($response->json());
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(config('services.calculadora_regular.url'))
            ->acceptJson()
            ->connectTimeout(3)
            ->timeout(15)
            ->retry(3, 200, fn (Throwable $e) => $e instanceof ConnectionException, throw: false);
    }
}
```

- Retry só para conexão/5xx. 4xx → `PayloadInvalidoException`, sem retry.
- `ConnectionException`/5xx persistente → `CalculadoraIndisponivelException` (jobs fazem `release()` com backoff).
- Nenhum array cru sai do client: sempre DTO.

## Regras de domínio

- `aliquotasNominais` é **entrada**: o app busca a alíquota de referência e aplica o cronograma do ano simulado (fonte única: config/tabela via `CronogramaTransicaoService`).
- O resultado vem com `CALCULO_SIMULADO`; o DTO expõe isso e a UI/PDF/API mostram o aviso.
- Valores e alíquotas: string decimal ↔ `decimal` cast. Nada de `float` no DTO (`(string)` ao ler o JSON quando vier número).
- Linha do tempo: uma chamada por ano (pode ser batch), não extrapolação.
- Comparador: mesma lista de itens nas duas calculadoras; diferença calculada em `CompararRegimesService`.
- Dados abertos (NCM, cClassTrib): cache longo (`Cache::remember`) ou tabela sincronizada por command agendado; autocomplete de NCM consulta o cache/tabela, não a API a cada tecla.
- Atenção conhecida: o README do Simples aponta origem pouco clara dos scripts Flyway; pode ser preciso reaproveitar os do regime regular.

## Testes

```php
beforeEach(fn () => Http::preventStrayRequests());

test('simula item no regime regular', function () {
    Http::fake([
        config('services.calculadora_regular.url').'/*' => Http::response(
            json_decode(file_get_contents(base_path('tests/Fixtures/calculadora/regular/item-simples.json')), true),
        ),
    ]);

    $resultado = app(RegimeRegular::class)->simular(SimulacaoRequestFactory::make());

    expect($resultado->simulado)->toBeTrue();
    Http::assertSentCount(1);
});
```

Cubra: sucesso, 4xx (payload inválido), 5xx/timeout (indisponível + retry), campos opcionais ausentes.
