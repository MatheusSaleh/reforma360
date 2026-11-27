# Introduction

Simulação de CBS e IBS (LC 214/2025) com a calculadora oficial da Receita Federal.

<aside>
    <strong>Base URL</strong>: <code>{{ config("app.url") }}</code>
</aside>

A API do Reforma360 simula CBS e IBS de produtos e serviços com a calculadora oficial da Receita Federal,
gerencia o catálogo da empresa e simula o catálogo inteiro em lote.

<aside class="warning"><strong>Simulação.</strong> Os valores são estimativas para apoiar a análise e não substituem a
orientação de um contador. De 2027 em diante, as alíquotas informadas ainda não são as definitivas: o resultado vem
marcado com <code>calculo_simulado: true</code>.</aside>

Valores monetários e alíquotas trafegam como **texto decimal** com ponto (`"1200.00"`, `"8.8"`), nunca como número
de ponto flutuante. Datas no formato `AAAA-MM-DD`.

Erros seguem o padrão do Laravel: `401` sem token, `403` sem a permissão do token, `404` recurso inexistente ou
de outra empresa, `422` com `errors` por campo, `429` limite de requisições e `503` calculadora indisponível.

