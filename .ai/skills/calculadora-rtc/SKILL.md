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

## Infra (compose.yaml)

- Rede `calculadoras` (`internal: true`): só o `laravel.test` alcança os serviços, pelo nome (`http://calculadora-regular:8080`).
- APIs Java: Spring Boot 4 / Java 25, perfil `offline`, context path `/api`. Imagem: `docker/calculadora-java/Dockerfile`.
- Banco: SQLite único gerado pelo serviço `calculadora-db` (`flyway:migrate` com os scripts do regime regular) no volume `calculadora-db`, montado `:ro` nas duas APIs.
- Health: `GET /api/calculadora/dados-abertos/versao` (regular), `GET /api/versao` (Simples), `GET /health/ready` (DANFSe). **Não** use `/api/versao/status`: consulta versão remota na internet.
- O Simples escuta na 8082 no perfil offline; o compose fixa `SERVER_PORT=8080`.
- Swagger em dev: `/api/swagger-ui.html` (acesse de dentro da rede: `sail exec laravel.test curl ...`).

## Endpoints conhecidos (regime regular — base `/api/calculadora`)

| Uso | Endpoint |
|-----|----------|
| Cálculo (falha no primeiro erro) | `POST /regime-geral` |
| Cálculo com erros por item | `POST /observabilidade/regime-geral` |
| Gerar XML do grupo IBS/CBS | `POST /xml/generate?tipo=nfe` |
| Validar XML contra XSD | `POST /xml/validate?tipo=nfe&subtipo=grupo` |
| NCM / NBS | `GET /dados-abertos/ncm`, `GET /dados-abertos/nbs` |
| CST / cClassTrib | `GET /dados-abertos/situacoes-tributarias/cbs-ibs`, `GET /dados-abertos/classificacoes-tributarias/...` |
| Alíquotas de referência | `GET /dados-abertos/aliquota-uniao`, `/aliquota-uf`, `/aliquota-municipio` |

Exemplos de payload: `services/calculadora-rtc-regime-regular/docs/exemplos.md`. Para o Simples e o DANFSe, consulte o README/Swagger de cada repositório.

## Antes de codar contra um endpoint

1. Leia o README e a especificação OpenAPI/Swagger do repositório em `services/<repo>/` (ou `/swagger-ui`/`/v3/api-docs` do container em dev).
2. Faça uma chamada real em dev e salve request/response **anonimizados** em `tests/Fixtures/calculadora/<servico>/<caso>.json`.
3. Modele DTOs a partir da fixture. Não invente nomes de campo.

## Estrutura (implementada na #3)

```
app/Integrations/Calculadora/
  Contracts/CalculadoraTributos.php    interface usada pelo domínio: calcular(Operacao): ResultadoOperacao
  ReceitaCalculadoraClient.php         implementação HTTP (POST calculadora/observabilidade/regime-geral)
  Data/                                DTOs readonly
    Operacao, ItemOperacao, AliquotasNominais      entrada (toPayload)
    ResultadoOperacao, ResultadoItem, TributacaoIbsCbs, ParcelaTributo,
    ImpostoSeletivoCalculado, TotalOperacao, ErroCalculo   saída (fromArray)
    Decimal                            valida decimal em string ("100.00")
    LeCamposResposta                   leitura tipada da resposta (trait)
  Exceptions/
    CalculadoraIndisponivelException   conexão/timeout/5xx após retries — transitório
    PayloadInvalidoException           4xx — operação inteira rejeitada, com list<ErroCalculo>
    RespostaInesperadaException        contrato da resposta mudou
app/Enums/EstadoItemCalculo.php        CALCULADO, CALCULO_SIMULADO, INCONSISTENCIA_ENTRADA, NAO_IMPLEMENTADO, FALHA_TECNICA
```

Bind `CalculadoraTributos → ReceitaCalculadoraClient` em `AppServiceProvider::register()`. Timeout/retry em `config/services.php` (`calculadora_regular.*`), injetados com `#[Config]`.

## Comportamento do endpoint de observabilidade (verificado na API real)

- HTTP 200 mesmo com itens em erro: cada objeto tem `estadoItem` e, se falhou, `erros[]` (`code`, `title`, `pointer`, `detail`).
- `total` **some** da resposta quando nenhum item é calculado (`ResultadoOperacao::$total` é nullable).
- Valores e alíquotas vêm como **string** ("42.24"); a API também aceita string na entrada (Jackson → BigDecimal). Nunca use float.
- Operação inválida (ex.: `itens` vazio) → 422 *problem detail* com `errors[]` no mesmo formato. JSON malformado → 400 sem `errors`.
- Headers: `x-warning-dados-simulados` (presente quando simulado), `X-CALC-APP-VERSION`, `X-CALC-DB-VERSION`.
- A partir de 2027 `aliquotasNominais` é obrigatório; antes, proibido.
- Imposto Seletivo na entrada ainda não é modelado em `ItemOperacao` (a saída já lê o grupo `IS`). Consequência: a partir de 2027, NCMs sujeitos ao IS (ex.: cerveja 22030000) voltam com **REG-013** ("Dados do Imposto Seletivo devem ser informados").
- Em 2026, sem alíquotas, o item sai `CALCULADO` (usa as de referência); de 2027 em diante, com alíquotas, sai `CALCULO_SIMULADO`.

## Dados abertos (implementado na #4)

`Contracts\DadosAbertos` → `DadosAbertosClient` (cache de 1 dia), usando `CalculadoraRegularHttp` (config HTTP compartilhada com o client de cálculo):

| Método | Endpoint | Observação |
|--------|----------|------------|
| `municipios(Uf)` | `GET /dados-abertos/ufs/municipios?siglaUf=RS` | `[{codigo, nome}]` |
| `classificacaoTributaria(cClassTrib, data)` | `GET /dados-abertos/classificacoes-tributarias/cbs-ibs/class-trib/{cClassTrib}?data=` | 404 → `null` (também cacheado) |
| `fundamentacaoLegal(cClassTrib, data)` | `GET /dados-abertos/fundamentacoes-legais?data=` | lista com ~170 itens (um por cClassTrib), filtrada no app; quebras de linha vêm escapadas (`\\n`) |

| `nomenclaturas(TipoNomenclatura, data)` | `GET /dados-abertos/ncm/vigentes?data=` / `GET /dados-abertos/nbs/lista?data=` | NCM: ~10,5 mil itens (~730 KB), descrição só do último nível (~3 mil são "Outros"), com traços de nível ("-- "); NBS: ~920 itens (#6) |
| `detalheNomenclatura(tipo, codigo, data)` | `GET /dados-abertos/ncm?ncm=` / `GET /dados-abertos/nbs?nbs=` | hierarquia `capitulo/posicao/subposicao/item/subitem` + `tributadoPeloImpostoSeletivo` (depende da data: IS só a partir de 2027); 404 → `null` |
| `situacoesTributarias(data)` | `GET /dados-abertos/situacoes-tributarias/cbs-ibs?data=` | 18 CSTs com `classificacoesTributarias` aninhadas (~20 KB) |

- Busca para autocomplete: `App\Services\Catalogo\BuscaNomenclaturaService` (prefixo do código ou palavras da descrição, sem acento), com **índice normalizado em cache** (normalizar 10 mil NCMs custa ~60 ms; ler do cache ~6 ms). Endpoints do app: `/dados-abertos/nomenclaturas/{ncm|nbs}?q=`, `/dados-abertos/nomenclaturas/{tipo}/{codigo}`, `/dados-abertos/situacoes-tributarias` (throttle `dados-abertos`).
- Front: componentes `<x-campo-nomenclatura tipo="ncm">` (combobox ARIA, debounce 300 ms) e `<x-campos-classificacao>` (datalist CST → cClassTrib); JS em `resources/js/autocomplete-nomenclatura.js` e `classificacoes.js`.
- Alíquotas de referência (`/aliquota-uniao`, `/aliquota-uf?codigoUf=`, `/aliquota-municipio?codigoMunicipio=`) só existem para 2026.
- Campos numéricos dos dados abertos (ex.: `percentualReducaoCbs: 60.0`) vêm como **número JSON** (float) — não leia esses campos para cálculo; use os valores do resultado da calculadora.

## Regras de domínio

- `aliquotasNominais` é **entrada** a partir de 2027 (não há alíquota de referência na calculadora depois de 2026). Ano de corte e sugestões em `config/simulacao.php`.
- O resultado vem com `CALCULO_SIMULADO`; o DTO expõe isso e a UI/PDF/API mostram o aviso.
- Valores e alíquotas: string decimal ↔ `decimal` cast. Nada de `float` no DTO (`(string)` ao ler o JSON quando vier número).
- Linha do tempo: uma chamada por ano (pode ser batch), não extrapolação.
- Comparador: mesma lista de itens nas duas calculadoras; diferença calculada em `CompararRegimesService`.
- Dados abertos (NCM, cClassTrib): cache longo (`Cache::remember`) ou tabela sincronizada por command agendado; autocomplete de NCM consulta o cache/tabela, não a API a cada tecla.

## Testes

Veja `tests/Feature/Calculadora/ReceitaCalculadoraClientTest.php`. Fixtures reais em `tests/Fixtures/calculadora/regular/`, carregadas com `fixtureJson('calculadora/regular/...')` (helper em `tests/Pest.php`; `fixture()` já existe no Pest).

```php
beforeEach(function () {
    Http::preventStrayRequests();
    Sleep::fake(); // o retry usa Sleep
});

Http::fake([URL_OBSERVABILIDADE => Http::response(fixtureJson('calculadora/regular/observabilidade-resposta.json'))]);
$resultado = app(CalculadoraTributos::class)->calcular($operacao);
```

Cubra: sucesso, erro por item, 4xx (sem retry), 5xx (retry + indisponível), falha de conexão, resposta com contrato diferente.
