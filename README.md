# Reforma360

Simulador da Reforma Tributária do Consumo (CBS/IBS — LC 214/2025) para pequenas empresas e contadores, construído em **Laravel** sobre a [Calculadora de Tributos open source da Receita Federal](https://github.com/ReceitaFederal).

> ⚠️ Projeto em fase inicial (planejamento). As tarefas estão nas [issues](../../issues).
> Os resultados são **simulações** e não substituem a orientação de um contador.

## Visão geral

O Reforma360 permite cadastrar ou importar produtos e serviços, simular a incidência de CBS e IBS por item, visualizar a transição 2026→2033 e comparar o Simples Nacional com a opção pelo regime regular de IBS/CBS.

### Entrada de dados

1. **Cadastro manual** de produtos/serviços (descrição, NCM/NBS, preço), com autocomplete de NCM via `/dados-abertos` da calculadora.
2. **Importação de planilha CSV/XLSX**, com validação linha a linha.
3. **Upload de XML de NF-e/NFS-e**: extrai os itens, simula IBS/CBS, valida o grupo contra o XSD da Receita e gera o DANFSe em PDF.

### Funcionalidades

- Simulação em lote com filas (`Bus::batch()`) e progresso em tempo real (Reverb)
- Linha do tempo 2026→2033 por item
- Comparador Simples × regime regular de IBS/CBS
- Relatório em PDF
- Painel admin com Filament
- API pública com Sanctum
- Testes com Pest e CI no GitHub Actions

## Arquitetura

```
Laravel (app + API + Horizon + Reverb)
   ├── MySQL / Redis
   ├── calculadora-regime-regular   (Java/Spring Boot, container interno)
   ├── calculadora-simples-nacional (Java/Spring Boot, container interno)
   └── danfse                       (Go, container interno)
```

As calculadoras da Receita rodam **sem modificações**, como serviços internos sem acesso público. Toda a lógica de produto fica no Laravel. Os importadores seguem o Strategy pattern (`CsvImporter`, `XlsxImporter`, `NfeXmlImporter` atrás da interface `CatalogImporter`).

| Serviço | Repositório upstream | Stack |
|---|---|---|
| Regime regular | [calculadora-rtc-regime-regular](https://github.com/ReceitaFederal/calculadora-rtc-regime-regular) | Java 25, Spring Boot 4, SQLite |
| Simples Nacional | [calculadora-rtc-simples-nacional](https://github.com/ReceitaFederal/calculadora-rtc-simples-nacional) | Java 25, Spring Boot 4, SQLite |
| DANFSe | [calculadora-rtc-gerador-danfse](https://github.com/ReceitaFederal/calculadora-rtc-gerador-danfse) | Go, Echo |

## Como desenvolver

### Pré-requisitos

- Docker e Docker Compose
- PHP 8.4+ e Composer (opcional se usar Laravel Sail)
- Node.js 22+ (assets do front)
- Git

### 1. Clonar o projeto

```bash
git clone https://github.com/MatheusSaleh/reforma360.git
cd reforma360
```

### 2. Calculadoras da Receita

As calculadoras ficam em `services/`, clonadas do upstream (não versionadas aqui, para facilitar a atualização). O script clona na primeira vez e atualiza nas seguintes:

```bash
./scripts/calculadoras-clone.sh
```

O `compose.yaml` sobe, junto com o Laravel:

| Serviço | O que faz | Healthcheck |
|---|---|---|
| `calculadora-db` | Execução única: gera/atualiza o SQLite com `flyway:migrate` (scripts do regime regular) no volume `calculadora-db` | termina com código 0 |
| `calculadora-regular` | API do regime regular (Java 25) | `GET /api/versao/status` |
| `calculadora-simples` | API do Simples Nacional (Java 25) | `GET /api/versao/status` |
| `danfse` | Gerador de DANFSe (Go) | `GET /health/ready` |

- As duas APIs Java usam o **mesmo banco**, montado como somente leitura. O Simples não traz scripts Flyway próprios; o README dele manda usar os do regime regular.
- As calculadoras ficam na rede interna `calculadoras` (`internal: true`), **sem porta publicada no host** e sem saída para a internet. Só o container do Laravel participa dessa rede.
- A imagem das APIs Java é construída por [`docker/calculadora-java/Dockerfile`](docker/calculadora-java/Dockerfile) a partir do código upstream; o DANFSe usa o Dockerfile do próprio repositório.

O primeiro `sail up` demora (build Maven/Go e geração do banco). Para conferir de dentro da rede:

```bash
./vendor/bin/sail ps
./vendor/bin/sail exec laravel.test curl -s http://calculadora-regular:8080/api/versao/status
```

Depois de atualizar o upstream (`./scripts/calculadoras-clone.sh`), reconstrua as imagens e rode as migrações do banco:

```bash
./vendor/bin/sail build calculadora-regular calculadora-simples danfse
./vendor/bin/sail up -d
```

### 3. Aplicação Laravel

O ambiente usa [Laravel Sail](https://laravel.com/docs/sail) (`compose.yaml`) com MySQL 8.4 e Redis:

```bash
cp .env.example .env
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed   # cria o usuário demo@reforma360.test (senha: password)
./vendor/bin/sail npm install && ./vendor/bin/sail npm run dev
```

> Se as portas 80, 3306 ou 6379 já estiverem em uso, defina `APP_PORT`, `FORWARD_DB_PORT` e `FORWARD_REDIS_PORT` no `.env`.

Variáveis de ambiente das calculadoras:

```dotenv
CALCULADORA_REGIME_REGULAR_URL=http://calculadora-regular:8080/api
CALCULADORA_SIMPLES_URL=http://calculadora-simples:8080/api
DANFSE_URL=http://danfse:8080/api
```

### 4. Filas e tempo real

```bash
php artisan horizon
php artisan reverb:start
```

### 5. Testes e qualidade

```bash
composer test      # Pest (SQLite em memória)
composer analyse   # Larastan nível 7
composer lint      # Pint (--test); `composer format` corrige
```

## Fluxo de trabalho

- Cada tarefa é uma issue, agrupada por milestone (Semana 1 a 4).
- Branches: `feat/<numero-issue>-descricao`, `fix/...`, `chore/...`.
- Commits no padrão [Conventional Commits](https://www.conventionalcommits.org/pt-br/).
- PR para `main` com CI verde (Pest, Larastan, Pint).

## Roadmap

1. **Semana 1:** Docker com as calculadoras, client HTTP e simulação de um item.
2. **Semana 2:** cadastro manual com autocomplete de NCM, importação CSV/XLSX e lote com filas.
3. **Semana 3:** upload de XML, validação XSD, DANFSe, comparador e PDF.
4. **Semana 4:** testes, CI, deploy com dados de demonstração e README final.

## Cuidados conhecidos

- A calculadora só tem alíquotas de referência para 2026. A partir de 2027 as alíquotas nominais são **entrada obrigatória** (`aliquotasNominais`): o simulador sugere estimativas editáveis (`config/simulacao.php`) e o resultado sai como `CALCULO_SIMULADO`.
- O retorno da API marca os itens como `CALCULO_SIMULADO`.
- O Simples Nacional não tem scripts Flyway próprios: o banco é gerado com os do regime regular e compartilhado pelas duas APIs (serviço `calculadora-db`).

## Licença

MIT. As calculadoras da Receita Federal também são distribuídas sob licença MIT.
