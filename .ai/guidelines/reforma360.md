# Reforma360 — regras de domínio

Regras que valem para qualquer código que calcule, mostre ou importe dados tributários.

## Natureza do produto

- O app **simula** CBS/IBS (LC 214/2025). Nunca apresente resultado como cálculo oficial ou definitivo.
- Toda saída de cálculo (tela, PDF, API) carrega o aviso de simulação. O retorno da calculadora vem marcado `CALCULO_SIMULADO`; preserve essa informação no DTO e no modelo persistido.
- Não invente alíquotas, reduções, regimes ou regras de transição no código. A fonte é a calculadora da Receita e os dados abertos dela (`/dados-abertos`). Quando algo precisar ser parametrizado no app, vai para `config/` ou tabela, nunca número mágico no meio de um Service.

## Alíquotas e transição 2026→2033

- A calculadora só tem alíquotas de referência para **2026** (`/dados-abertos/aliquota-uniao` etc. dão 404 de 2027 em diante). A partir de 2027 as alíquotas são **entrada obrigatória** (`aliquotasNominais`) e o resultado sai `CALCULO_SIMULADO`; antes disso são proibidas.
- O ano de corte fica em `config('simulacao.ano_inicio_aliquotas_nominais')` e as sugestões do formulário em `config('simulacao.aliquotas_nominais_sugeridas')` — são estimativas editáveis, nunca apresentadas como alíquota legal.
- O cronograma (ano → percentuais de CBS/IBS e do sistema antigo) fica em um único lugar (config ou tabela) e é consultado por um Service; não espalhe `if ($ano === 2027)` pelo código.
- A linha do tempo por item é calculada chamando a simulação por ano, não extrapolando um ano para os outros.

## Dinheiro e precisão

- **Proibido `float`** para valor monetário ou alíquota — em PHP, migrations, casts e JS.
- Valores monetários: centavos em `int`/`bigint` **ou** coluna `decimal(15,2)` com cast `decimal:2`. Escolha um padrão na primeira migration e mantenha (registre a decisão em `.ai/rules/database.md`).
- Alíquotas: `decimal(7,4)` (ou a precisão que a API exigir) com cast `decimal:4`, trafegando como string decimal.
- Aritmética com `bcmath` (ou `Brick\Math` se aprovado) quando não for soma de inteiros. Arredonde **uma vez**, no ponto que a regra define, e teste o arredondamento.
- Formatação para o usuário só na borda (Blade/Resource/PDF), com `Number::currency($v, 'BRL', 'pt_BR')`.

## Identificadores fiscais

- NCM: 8 dígitos; NBS: 9 dígitos (serviços). Guarde **só dígitos** (`string`, nunca `int` — zeros à esquerda importam) e formate na exibição.
- CNPJ/CPF: guarde só dígitos como `string`; valide dígito verificador com regra de validação dedicada (`App\Rules`).
- Códigos de classificação tributária (cClassTrib/CST) vêm da calculadora/dados abertos; não hardcode listas.

## Simples Nacional × regime regular

- O comparador chama as **duas** calculadoras com a mesma base de itens e apresenta a diferença. A lógica de comparação fica em um Service próprio (ex.: `App\Services\Simulacao\CompararRegimesService`), não no controller nem na view.
- Faturamento/receita bruta e demais entradas do Simples vêm de FormRequest validado.

## Dados sensíveis

- XML de NF-e/NFS-e e planilhas de clientes contêm dados fiscais e pessoais. Armazene em disco privado (`storage/app/private`), nunca em `public`.
- Não logue payload completo de XML, CNPJ/CPF ou valores de cliente em `info`. Em erro, logue identificadores internos (id da importação, linha).
- Fixtures de teste usam dados **fictícios**.

## Glossário

| Termo | Significado |
|-------|-------------|
| CBS | Contribuição sobre Bens e Serviços (federal) |
| IBS | Imposto sobre Bens e Serviços (estadual/municipal) |
| IS | Imposto Seletivo |
| NCM / NBS | Nomenclaturas de mercadorias / serviços |
| DANFSe | Documento auxiliar da NFS-e (PDF) |
| Regime regular | Apuração normal de IBS/CBS (fora do Simples) |
| Transição | 2026 (teste) → 2033 (extinção de PIS/Cofins/ICMS/ISS) |
