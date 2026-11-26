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
| Simples Nacional | [calculadora-rtc-simples-nacional](https://github.com/ReceitaFederal/calculadora-rtc-simples-nacional) | Java 21, Spring Boot 3.5, SQLite |
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

### 2. Subir as calculadoras da Receita

As calculadoras ficam em `services/`, clonadas do upstream (não versionadas aqui, para facilitar a atualização):

```bash
mkdir -p services && cd services
git clone https://github.com/ReceitaFederal/calculadora-rtc-regime-regular.git
git clone https://github.com/ReceitaFederal/calculadora-rtc-simples-nacional.git
git clone https://github.com/ReceitaFederal/calculadora-rtc-gerador-danfse.git
cd ..
```

As APIs Java precisam do banco SQLite gerado antes da primeira execução (ver README de cada repositório):

```bash
cd services/calculadora-rtc-regime-regular
mkdir -p ./calculadora/db
./mvnw flyway:migrate -Dflyway.configFiles=./flyway/flyway-pro.conf --batch-mode --errors
cd ../..
```

> O `docker-compose.yml` com todos os serviços será adicionado na issue de setup (Semana 1).

### 3. Aplicação Laravel

O ambiente usa [Laravel Sail](https://laravel.com/docs/sail) (`compose.yaml`) com MySQL 8.4 e Redis:

```bash
cp .env.example .env
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
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

- As alíquotas são **entrada** da API (`aliquotasNominais`). O app busca as alíquotas de referência e respeita o cronograma de transição.
- O retorno da API marca os itens como `CALCULO_SIMULADO`.
- O README do repositório do Simples Nacional aponta para uma origem pouco clara dos scripts Flyway; pode ser necessário reaproveitar os do regime regular.

## Licença

MIT. As calculadoras da Receita Federal também são distribuídas sob licença MIT.
