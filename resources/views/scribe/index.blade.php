<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>API do Reforma360</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
                    body .content .php-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "http://localhost:8090";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.11.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.11.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;,&quot;php&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                                            <button type="button" class="lang-button" data-language-name="php">php</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-simulacao-de-item" class="tocify-header">
                <li class="tocify-item level-1" data-unique="simulacao-de-item">
                    <a href="#simulacao-de-item">Simulação de item</a>
                </li>
                                    <ul id="tocify-subheader-simulacao-de-item" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="simulacao-de-item-POSTapi-v1-simulacoes-item">
                                <a href="#simulacao-de-item-POSTapi-v1-simulacoes-item">Simular um item</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-produtos" class="tocify-header">
                <li class="tocify-item level-1" data-unique="produtos">
                    <a href="#produtos">Produtos</a>
                </li>
                                    <ul id="tocify-subheader-produtos" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="produtos-GETapi-v1-produtos">
                                <a href="#produtos-GETapi-v1-produtos">Listar produtos</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="produtos-GETapi-v1-produtos--id-">
                                <a href="#produtos-GETapi-v1-produtos--id-">Consultar produto</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="produtos-POSTapi-v1-produtos">
                                <a href="#produtos-POSTapi-v1-produtos">Cadastrar produto</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="produtos-PUTapi-v1-produtos--id-">
                                <a href="#produtos-PUTapi-v1-produtos--id-">Alterar produto</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="produtos-DELETEapi-v1-produtos--id-">
                                <a href="#produtos-DELETEapi-v1-produtos--id-">Excluir produto</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-simulacao-do-catalogo" class="tocify-header">
                <li class="tocify-item level-1" data-unique="simulacao-do-catalogo">
                    <a href="#simulacao-do-catalogo">Simulação do catálogo</a>
                </li>
                                    <ul id="tocify-subheader-simulacao-do-catalogo" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="simulacao-do-catalogo-GETapi-v1-simulacoes">
                                <a href="#simulacao-do-catalogo-GETapi-v1-simulacoes">Listar simulações</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="simulacao-do-catalogo-POSTapi-v1-simulacoes">
                                <a href="#simulacao-do-catalogo-POSTapi-v1-simulacoes">Iniciar simulação do catálogo</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="simulacao-do-catalogo-GETapi-v1-simulacoes--simulacao_id-">
                                <a href="#simulacao-do-catalogo-GETapi-v1-simulacoes--simulacao_id-">Consultar simulação</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="simulacao-do-catalogo-GETapi-v1-simulacoes--simulacao_id--itens">
                                <a href="#simulacao-do-catalogo-GETapi-v1-simulacoes--simulacao_id--itens">Resultado por item</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Atualizado em 27/11/2026</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<p>Simulação de CBS e IBS (LC 214/2025) com a calculadora oficial da Receita Federal.</p>
<aside>
    <strong>Base URL</strong>: <code>{{ config("app.url") }}</code>
</aside>
<p>A API do Reforma360 simula CBS e IBS de produtos e serviços com a calculadora oficial da Receita Federal,
gerencia o catálogo da empresa e simula o catálogo inteiro em lote.</p>
<aside class="warning"><strong>Simulação.</strong> Os valores são estimativas para apoiar a análise e não substituem a
orientação de um contador. De 2027 em diante, as alíquotas informadas ainda não são as definitivas: o resultado vem
marcado com <code>calculo_simulado: true</code>.</aside>
<p>Valores monetários e alíquotas trafegam como <strong>texto decimal</strong> com ponto (<code>"1200.00"</code>, <code>"8.8"</code>), nunca como número
de ponto flutuante. Datas no formato <code>AAAA-MM-DD</code>.</p>
<p>Erros seguem o padrão do Laravel: <code>401</code> sem token, <code>403</code> sem a permissão do token, <code>404</code> recurso inexistente ou
de outra empresa, <code>422</code> com <code>errors</code> por campo, <code>429</code> limite de requisições e <code>503</code> calculadora indisponível.</p>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>To authenticate requests, include an <strong><code>Authorization</code></strong> header with the value <strong><code>"Bearer {SEU_TOKEN}"</code></strong>.</p>
<p>All authenticated endpoints are marked with a <code>requires authentication</code> badge in the documentation below.</p>
<p>Crie um token em <b>Tokens de API</b> (menu do Reforma360), escolhendo as permissões: <code>produtos:ler</code>, <code>produtos:escrever</code> e <code>simulacoes</code>. Envie no cabeçalho <code>Authorization: Bearer {token}</code>.</p>

        <h1 id="simulacao-de-item">Simulação de item</h1>

    <p>Simula CBS e IBS de um produto (NCM) ou serviço (NBS) na hora. Permissão: <code>simulacoes</code>.</p>

                                <h2 id="simulacao-de-item-POSTapi-v1-simulacoes-item">Simular um item</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Calcula CBS, IBS estadual e IBS municipal de um item com a calculadora oficial da Receita Federal e devolve o resultado na resposta.</p>
<p>Informe <strong>NCM</strong> (mercadoria) <strong>ou</strong> <strong>NBS</strong> (serviço). De 2027 em diante, as três alíquotas nominais são obrigatórias e o resultado vem com <code>calculo_simulado: true</code>.</p>

<span id="example-requests-POSTapi-v1-simulacoes-item">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8090/api/v1/simulacoes/item" \
    --header "Authorization: Bearer {SEU_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"nbs\": \"122011200\",
    \"cst\": \"200\",
    \"c_class_trib\": \"200028\",
    \"valor\": 1200,
    \"uf\": \"RS\",
    \"municipio\": \"4314902\",
    \"data\": \"2027-03-10\",
    \"aliquota_cbs\": 8.8,
    \"aliquota_ibs_estadual\": 0.05,
    \"aliquota_ibs_municipal\": 0.05
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8090/api/v1/simulacoes/item"
);

const headers = {
    "Authorization": "Bearer {SEU_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "nbs": "122011200",
    "cst": "200",
    "c_class_trib": "200028",
    "valor": 1200,
    "uf": "RS",
    "municipio": "4314902",
    "data": "2027-03-10",
    "aliquota_cbs": 8.8,
    "aliquota_ibs_estadual": 0.05,
    "aliquota_ibs_municipal": 0.05
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8090/api/v1/simulacoes/item';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {SEU_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'nbs' =&gt; '122011200',
            'cst' =&gt; '200',
            'c_class_trib' =&gt; '200028',
            'valor' =&gt; 1200.0,
            'uf' =&gt; 'RS',
            'municipio' =&gt; '4314902',
            'data' =&gt; '2027-03-10',
            'aliquota_cbs' =&gt; 8.8,
            'aliquota_ibs_estadual' =&gt; 0.05,
            'aliquota_ibs_municipal' =&gt; 0.05,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-simulacoes-item">
            <blockquote>
            <p>Example response (200, Item simulado):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;calculo_simulado&quot;: true,
        &quot;data_fato_gerador&quot;: &quot;2027-03-10&quot;,
        &quot;cst&quot;: &quot;200&quot;,
        &quot;c_class_trib&quot;: &quot;200028&quot;,
        &quot;base_calculo&quot;: &quot;1200.00&quot;,
        &quot;cbs&quot;: {
            &quot;aliquota&quot;: &quot;8.80&quot;,
            &quot;percentual_reducao&quot;: &quot;60.00&quot;,
            &quot;aliquota_efetiva&quot;: &quot;3.52&quot;,
            &quot;valor&quot;: &quot;42.24&quot;
        },
        &quot;ibs_estadual&quot;: {
            &quot;aliquota&quot;: &quot;0.05&quot;,
            &quot;percentual_reducao&quot;: &quot;60.00&quot;,
            &quot;aliquota_efetiva&quot;: &quot;0.02&quot;,
            &quot;valor&quot;: &quot;0.24&quot;
        },
        &quot;ibs_municipal&quot;: {
            &quot;aliquota&quot;: &quot;0.05&quot;,
            &quot;percentual_reducao&quot;: &quot;60.00&quot;,
            &quot;aliquota_efetiva&quot;: &quot;0.02&quot;,
            &quot;valor&quot;: &quot;0.24&quot;
        },
        &quot;valor_ibs&quot;: &quot;0.48&quot;,
        &quot;imposto_seletivo&quot;: null,
        &quot;total_tributos&quot;: &quot;42.72&quot;,
        &quot;classificacao&quot;: {
            &quot;codigo&quot;: &quot;200028&quot;,
            &quot;descricao&quot;: &quot;Fornecimento dos servi&ccedil;os de educa&ccedil;&atilde;o (Anexo II)&quot;,
            &quot;tratamento&quot;: &quot;Al&iacute;quota reduzida em 60%&quot;
        },
        &quot;fundamentacao_legal&quot;: {
            &quot;referencia&quot;: &quot;Art. 129&quot;,
            &quot;texto&quot;: &quot;Art. 129. Ficam reduzidas em 60% (sessenta por cento) as al&iacute;quotas do IBS e da CBS incidentes sobre o fornecimento dos servi&ccedil;os de educa&ccedil;&atilde;o relacionados no Anexo II desta Lei Complementar, [...]&quot;
        },
        &quot;memoria_calculo&quot;: {
            &quot;cbs&quot;: &quot;Opera&ccedil;&atilde;o de consumo com enquadramento legal em Art. 129, tributada conforme Al&iacute;quota reduzida em 60%. A base de c&aacute;lculo utilizada &eacute; de R$ 1.200,00, com al&iacute;quota de 8,80%, sendo que esse regime define uma redu&ccedil;&atilde;o de 60,00% na al&iacute;quota.&quot;,
            &quot;ibs_estadual&quot;: &quot;[...]&quot;,
            &quot;ibs_municipal&quot;: &quot;[...]&quot;
        },
        &quot;versao_base_dados&quot;: &quot;V0073&quot;,
        &quot;aviso&quot;: &quot;Simula&ccedil;&atilde;o feita com a calculadora oficial da Receita Federal. N&atilde;o substitui a orienta&ccedil;&atilde;o de um contador.&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, Dados inválidos):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Informe o NCM (mercadoria) ou o NBS (servi&ccedil;o).&quot;,
    &quot;errors&quot;: {
        &quot;ncm&quot;: [
            &quot;Informe o NCM (mercadoria) ou o NBS (servi&ccedil;o).&quot;
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (503, Calculadora indisponível):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;A calculadora da Receita est&aacute; indispon&iacute;vel no momento. Tente novamente em instantes.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-simulacoes-item" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-simulacoes-item"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-simulacoes-item"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-simulacoes-item" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-simulacoes-item">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-simulacoes-item" data-method="POST"
      data-path="api/v1/simulacoes/item"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-simulacoes-item', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-simulacoes-item"
                    onclick="tryItOut('POSTapi-v1-simulacoes-item');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-simulacoes-item"
                    onclick="cancelTryOut('POSTapi-v1-simulacoes-item');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-simulacoes-item"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/simulacoes/item</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-simulacoes-item"
               value="Bearer {SEU_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {SEU_TOKEN}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-simulacoes-item"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-simulacoes-item"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>ncm</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="ncm"                data-endpoint="POSTapi-v1-simulacoes-item"
               value=""
               data-component="body">
    <br>
<p>NCM da mercadoria (8 dígitos). Informe NCM ou NBS. This field is required when <code>nbs</code> is not present. :Attribute deve ter 8 dígitos.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>nbs</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="nbs"                data-endpoint="POSTapi-v1-simulacoes-item"
               value="122011200"
               data-component="body">
    <br>
<p>NBS do serviço (9 dígitos). Informe NCM ou NBS. This field is required when <code>ncm</code> is not present. :Attribute deve ter 9 dígitos. Example: <code>122011200</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cst</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="cst"                data-endpoint="POSTapi-v1-simulacoes-item"
               value="200"
               data-component="body">
    <br>
<p>CST do IBS/CBS (3 dígitos). :Attribute deve ter 3 dígitos. Example: <code>200</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>c_class_trib</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="c_class_trib"                data-endpoint="POSTapi-v1-simulacoes-item"
               value="200028"
               data-component="body">
    <br>
<p>Código de classificação tributária (6 dígitos). :Attribute deve ter 6 dígitos. Example: <code>200028</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>valor</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="valor"                data-endpoint="POSTapi-v1-simulacoes-item"
               value="1200"
               data-component="body">
    <br>
<p>Valor da operação em reais, texto decimal com até 2 casas. :Attribute deve ser no máximo 9999999999999.99. Example: <code>1200</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>uf</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="uf"                data-endpoint="POSTapi-v1-simulacoes-item"
               value="RS"
               data-component="body">
    <br>
<p>UF da operação (sigla). Example: <code>RS</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>RO</code></li> <li><code>AC</code></li> <li><code>AM</code></li> <li><code>RR</code></li> <li><code>PA</code></li> <li><code>AP</code></li> <li><code>TO</code></li> <li><code>MA</code></li> <li><code>PI</code></li> <li><code>CE</code></li> <li><code>RN</code></li> <li><code>PB</code></li> <li><code>PE</code></li> <li><code>AL</code></li> <li><code>SE</code></li> <li><code>BA</code></li> <li><code>MG</code></li> <li><code>ES</code></li> <li><code>RJ</code></li> <li><code>SP</code></li> <li><code>PR</code></li> <li><code>SC</code></li> <li><code>RS</code></li> <li><code>MS</code></li> <li><code>MT</code></li> <li><code>GO</code></li> <li><code>DF</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>municipio</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="municipio"                data-endpoint="POSTapi-v1-simulacoes-item"
               value="4314902"
               data-component="body">
    <br>
<p>Código IBGE do município (7 dígitos), da UF informada. :Attribute deve ter 7 dígitos. Example: <code>4314902</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>data</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="data"                data-endpoint="POSTapi-v1-simulacoes-item"
               value="2027-03-10"
               data-component="body">
    <br>
<p>Data do fato gerador (AAAA-MM-DD), entre 2026 e 2033. Must be a valid date in the format <code>Y-m-d</code>. :Attribute deve ser uma data igual ou posterior a <code>2026-01-01</code>. :Attribute deve ser uma data igual ou anterior a <code>2033-12-31</code>. Example: <code>2027-03-10</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>aliquota_cbs</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="aliquota_cbs"                data-endpoint="POSTapi-v1-simulacoes-item"
               value="8.8"
               data-component="body">
    <br>
<p>Alíquota nominal da CBS em %, texto decimal. Obrigatória de 2027 em diante e proibida em 2026. :Attribute deve ser no mínimo 0. :Attribute deve ser no máximo 100. Example: <code>8.8</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>aliquota_ibs_estadual</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="aliquota_ibs_estadual"                data-endpoint="POSTapi-v1-simulacoes-item"
               value="0.05"
               data-component="body">
    <br>
<p>Alíquota nominal do IBS estadual em %. Obrigatória de 2027 em diante. :Attribute deve ser no mínimo 0. :Attribute deve ser no máximo 100. Example: <code>0.05</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>aliquota_ibs_municipal</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="aliquota_ibs_municipal"                data-endpoint="POSTapi-v1-simulacoes-item"
               value="0.05"
               data-component="body">
    <br>
<p>Alíquota nominal do IBS municipal em %. Obrigatória de 2027 em diante. :Attribute deve ser no mínimo 0. :Attribute deve ser no máximo 100. Example: <code>0.05</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_aliquota_ad_valorem</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="is_aliquota_ad_valorem"                data-endpoint="POSTapi-v1-simulacoes-item"
               value=""
               data-component="body">
    <br>
<p>Imposto Seletivo (a partir de 2027, só para NCMs tributados): alíquota ad valorem estimada em %. Must match the regex /^\d+(?:.\d+)?$/D. :Attribute deve ser no mínimo 0. :Attribute deve ser no máximo 100.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_aliquota_ad_rem</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="is_aliquota_ad_rem"                data-endpoint="POSTapi-v1-simulacoes-item"
               value=""
               data-component="body">
    <br>
<p>Imposto Seletivo: alíquota ad rem estimada, em R$ por unidade do IS. Exige is_unidade e is_quantidade. Must match the regex /^\d+(?:.\d+)?$/D. :Attribute deve ser no mínimo 0. :Attribute deve ser no máximo 99999999999.9999.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_unidade</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="is_unidade"                data-endpoint="POSTapi-v1-simulacoes-item"
               value=""
               data-component="body">
    <br>
<p>Unidade do IS (ex.: LT, VN, KG). This field is required when <code>is_aliquota_ad_rem</code> is present. Must match the regex /^[A-Z0-9]+$/. :Attribute deve ter no máximo 6 caracteres.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_quantidade</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="is_quantidade"                data-endpoint="POSTapi-v1-simulacoes-item"
               value=""
               data-component="body">
    <br>
<p>Quantidade total do item na unidade do IS. This field is required when <code>is_aliquota_ad_rem</code> is present. Must match the regex /^\d+(?:.\d+)?$/D. :Attribute deve ser no máximo 99999999999.9999.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_cst</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="is_cst"                data-endpoint="POSTapi-v1-simulacoes-item"
               value=""
               data-component="body">
    <br>
<p>CST do IS (3 dígitos). Padrão 000. :Attribute deve ter 3 dígitos.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_c_class_trib</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="is_c_class_trib"                data-endpoint="POSTapi-v1-simulacoes-item"
               value=""
               data-component="body">
    <br>
<p>cClassTrib do IS (6 dígitos). Padrão 000001. :Attribute deve ter 6 dígitos.</p>
        </div>
        </form>

                <h1 id="produtos">Produtos</h1>

    <p>Catálogo de produtos e serviços da empresa. Leitura: <code>produtos:ler</code>. Criação, alteração e exclusão: <code>produtos:escrever</code>.</p>

                                <h2 id="produtos-GETapi-v1-produtos">Listar produtos</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Produtos da empresa em ordem alfabética, paginados.</p>

<span id="example-requests-GETapi-v1-produtos">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8090/api/v1/produtos?busca=arroz&amp;por_pagina=25&amp;page=1" \
    --header "Authorization: Bearer {SEU_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8090/api/v1/produtos"
);

const params = {
    "busca": "arroz",
    "por_pagina": "25",
    "page": "1",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {SEU_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8090/api/v1/produtos';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {SEU_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'query' =&gt; [
            'busca' =&gt; 'arroz',
            'por_pagina' =&gt; '25',
            'page' =&gt; '1',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-produtos">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: null,
            &quot;descricao&quot;: &quot;aut adipisci quidem&quot;,
            &quot;tipo&quot;: &quot;mercadoria&quot;,
            &quot;ncm&quot;: &quot;21700432&quot;,
            &quot;nbs&quot;: null,
            &quot;cst&quot;: &quot;000&quot;,
            &quot;c_class_trib&quot;: &quot;000001&quot;,
            &quot;preco&quot;: &quot;62492.19&quot;,
            &quot;is_cst&quot;: null,
            &quot;is_c_class_trib&quot;: null,
            &quot;is_unidade&quot;: null,
            &quot;is_quantidade&quot;: null,
            &quot;is_aliquota_ad_valorem&quot;: null,
            &quot;is_aliquota_ad_rem&quot;: null,
            &quot;criado_em&quot;: null,
            &quot;atualizado_em&quot;: null
        },
        {
            &quot;id&quot;: null,
            &quot;descricao&quot;: &quot;et et modi&quot;,
            &quot;tipo&quot;: &quot;mercadoria&quot;,
            &quot;ncm&quot;: &quot;59135052&quot;,
            &quot;nbs&quot;: null,
            &quot;cst&quot;: &quot;000&quot;,
            &quot;c_class_trib&quot;: &quot;000001&quot;,
            &quot;preco&quot;: &quot;8317.58&quot;,
            &quot;is_cst&quot;: null,
            &quot;is_c_class_trib&quot;: null,
            &quot;is_unidade&quot;: null,
            &quot;is_quantidade&quot;: null,
            &quot;is_aliquota_ad_valorem&quot;: null,
            &quot;is_aliquota_ad_rem&quot;: null,
            &quot;criado_em&quot;: null,
            &quot;atualizado_em&quot;: null
        }
    ],
    &quot;links&quot;: {
        &quot;first&quot;: &quot;/?page=1&quot;,
        &quot;last&quot;: &quot;/?page=1&quot;,
        &quot;prev&quot;: null,
        &quot;next&quot;: null
    },
    &quot;meta&quot;: {
        &quot;current_page&quot;: 1,
        &quot;from&quot;: 1,
        &quot;last_page&quot;: 1,
        &quot;links&quot;: [
            {
                &quot;url&quot;: null,
                &quot;label&quot;: &quot;&amp;laquo; Previous&quot;,
                &quot;page&quot;: null,
                &quot;active&quot;: false
            },
            {
                &quot;url&quot;: &quot;/?page=1&quot;,
                &quot;label&quot;: &quot;1&quot;,
                &quot;page&quot;: 1,
                &quot;active&quot;: true
            },
            {
                &quot;url&quot;: null,
                &quot;label&quot;: &quot;Next &amp;raquo;&quot;,
                &quot;page&quot;: null,
                &quot;active&quot;: false
            }
        ],
        &quot;path&quot;: &quot;/&quot;,
        &quot;per_page&quot;: 25,
        &quot;to&quot;: 2,
        &quot;total&quot;: 2
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-produtos" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-produtos"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-produtos"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-produtos" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-produtos">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-produtos" data-method="GET"
      data-path="api/v1/produtos"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-produtos', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-produtos"
                    onclick="tryItOut('GETapi-v1-produtos');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-produtos"
                    onclick="cancelTryOut('GETapi-v1-produtos');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-produtos"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/produtos</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-produtos"
               value="Bearer {SEU_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {SEU_TOKEN}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-produtos"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-produtos"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>busca</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="busca"                data-endpoint="GETapi-v1-produtos"
               value="arroz"
               data-component="query">
    <br>
<p>Filtra por descrição, NCM ou NBS. Example: <code>arroz</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>por_pagina</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="por_pagina"                data-endpoint="GETapi-v1-produtos"
               value="25"
               data-component="query">
    <br>
<p>Itens por página (1 a 100). Example: <code>25</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>page</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="page"                data-endpoint="GETapi-v1-produtos"
               value="1"
               data-component="query">
    <br>
<p>Página. Example: <code>1</code></p>
            </div>
                </form>

                    <h2 id="produtos-GETapi-v1-produtos--id-">Consultar produto</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-produtos--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8090/api/v1/produtos/1" \
    --header "Authorization: Bearer {SEU_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8090/api/v1/produtos/1"
);

const headers = {
    "Authorization": "Bearer {SEU_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8090/api/v1/produtos/1';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {SEU_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-produtos--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: null,
        &quot;descricao&quot;: &quot;aut adipisci quidem&quot;,
        &quot;tipo&quot;: &quot;mercadoria&quot;,
        &quot;ncm&quot;: &quot;21700432&quot;,
        &quot;nbs&quot;: null,
        &quot;cst&quot;: &quot;000&quot;,
        &quot;c_class_trib&quot;: &quot;000001&quot;,
        &quot;preco&quot;: &quot;62492.19&quot;,
        &quot;is_cst&quot;: null,
        &quot;is_c_class_trib&quot;: null,
        &quot;is_unidade&quot;: null,
        &quot;is_quantidade&quot;: null,
        &quot;is_aliquota_ad_valorem&quot;: null,
        &quot;is_aliquota_ad_rem&quot;: null,
        &quot;criado_em&quot;: null,
        &quot;atualizado_em&quot;: null
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (404, Produto inexistente ou de outra empresa):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Not Found&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-produtos--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-produtos--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-produtos--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-produtos--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-produtos--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-produtos--id-" data-method="GET"
      data-path="api/v1/produtos/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-produtos--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-produtos--id-"
                    onclick="tryItOut('GETapi-v1-produtos--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-produtos--id-"
                    onclick="cancelTryOut('GETapi-v1-produtos--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-produtos--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/produtos/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-produtos--id-"
               value="Bearer {SEU_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {SEU_TOKEN}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-produtos--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-produtos--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-v1-produtos--id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the produto. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>produto</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="produto"                data-endpoint="GETapi-v1-produtos--id-"
               value="1"
               data-component="url">
    <br>
<p>ID do produto. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="produtos-POSTapi-v1-produtos">Cadastrar produto</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Informe NCM (mercadoria) ou NBS (serviço), CST e cClassTrib.</p>

<span id="example-requests-POSTapi-v1-produtos">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8090/api/v1/produtos" \
    --header "Authorization: Bearer {SEU_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"descricao\": \"Mensalidade escolar\",
    \"nbs\": \"122011200\",
    \"cst\": \"200\",
    \"c_class_trib\": \"200028\",
    \"preco\": 1200
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8090/api/v1/produtos"
);

const headers = {
    "Authorization": "Bearer {SEU_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "descricao": "Mensalidade escolar",
    "nbs": "122011200",
    "cst": "200",
    "c_class_trib": "200028",
    "preco": 1200
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8090/api/v1/produtos';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {SEU_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'descricao' =&gt; 'Mensalidade escolar',
            'nbs' =&gt; '122011200',
            'cst' =&gt; '200',
            'c_class_trib' =&gt; '200028',
            'preco' =&gt; 1200.0,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-produtos">
            <blockquote>
            <p>Example response (201):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: null,
        &quot;descricao&quot;: &quot;architecto eius et&quot;,
        &quot;tipo&quot;: &quot;mercadoria&quot;,
        &quot;ncm&quot;: &quot;12589365&quot;,
        &quot;nbs&quot;: null,
        &quot;cst&quot;: &quot;000&quot;,
        &quot;c_class_trib&quot;: &quot;000001&quot;,
        &quot;preco&quot;: &quot;45117.64&quot;,
        &quot;is_cst&quot;: null,
        &quot;is_c_class_trib&quot;: null,
        &quot;is_unidade&quot;: null,
        &quot;is_quantidade&quot;: null,
        &quot;is_aliquota_ad_valorem&quot;: null,
        &quot;is_aliquota_ad_rem&quot;: null,
        &quot;criado_em&quot;: null,
        &quot;atualizado_em&quot;: null
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, Dados inválidos):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Informe o NCM (mercadoria) ou o NBS (servi&ccedil;o).&quot;,
    &quot;errors&quot;: {
        &quot;ncm&quot;: [
            &quot;Informe o NCM (mercadoria) ou o NBS (servi&ccedil;o).&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-produtos" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-produtos"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-produtos"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-produtos" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-produtos">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-produtos" data-method="POST"
      data-path="api/v1/produtos"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-produtos', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-produtos"
                    onclick="tryItOut('POSTapi-v1-produtos');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-produtos"
                    onclick="cancelTryOut('POSTapi-v1-produtos');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-produtos"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/produtos</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-produtos"
               value="Bearer {SEU_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {SEU_TOKEN}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-produtos"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-produtos"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>descricao</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="descricao"                data-endpoint="POSTapi-v1-produtos"
               value="Mensalidade escolar"
               data-component="body">
    <br>
<p>Descrição do produto ou serviço. :Attribute deve ter no máximo 255 caracteres. Example: <code>Mensalidade escolar</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>ncm</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="ncm"                data-endpoint="POSTapi-v1-produtos"
               value=""
               data-component="body">
    <br>
<p>NCM da mercadoria (8 dígitos). Informe NCM ou NBS. This field is required when <code>nbs</code> is not present. :Attribute deve ter 8 dígitos.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>nbs</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="nbs"                data-endpoint="POSTapi-v1-produtos"
               value="122011200"
               data-component="body">
    <br>
<p>NBS do serviço (9 dígitos). Informe NCM ou NBS. This field is required when <code>ncm</code> is not present. :Attribute deve ter 9 dígitos. Example: <code>122011200</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cst</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="cst"                data-endpoint="POSTapi-v1-produtos"
               value="200"
               data-component="body">
    <br>
<p>CST do IBS/CBS (3 dígitos). :Attribute deve ter 3 dígitos. Example: <code>200</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>c_class_trib</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="c_class_trib"                data-endpoint="POSTapi-v1-produtos"
               value="200028"
               data-component="body">
    <br>
<p>Código de classificação tributária (6 dígitos). :Attribute deve ter 6 dígitos. Example: <code>200028</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>preco</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="preco"                data-endpoint="POSTapi-v1-produtos"
               value="1200"
               data-component="body">
    <br>
<p>Preço em reais, texto decimal com até 2 casas. :Attribute deve ser no máximo 9999999999999.99. Example: <code>1200</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_aliquota_ad_valorem</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="is_aliquota_ad_valorem"                data-endpoint="POSTapi-v1-produtos"
               value=""
               data-component="body">
    <br>
<p>Imposto Seletivo (a partir de 2027, só para NCMs tributados): alíquota ad valorem estimada em %. Must match the regex /^\d+(?:.\d+)?$/D. :Attribute deve ser no mínimo 0. :Attribute deve ser no máximo 100.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_aliquota_ad_rem</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="is_aliquota_ad_rem"                data-endpoint="POSTapi-v1-produtos"
               value=""
               data-component="body">
    <br>
<p>Imposto Seletivo: alíquota ad rem estimada, em R$ por unidade do IS. Exige is_unidade e is_quantidade. Must match the regex /^\d+(?:.\d+)?$/D. :Attribute deve ser no mínimo 0. :Attribute deve ser no máximo 99999999999.9999.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_unidade</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="is_unidade"                data-endpoint="POSTapi-v1-produtos"
               value=""
               data-component="body">
    <br>
<p>Unidade do IS (ex.: LT, VN, KG). This field is required when <code>is_aliquota_ad_rem</code> is present. Must match the regex /^[A-Z0-9]+$/. :Attribute deve ter no máximo 6 caracteres.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_quantidade</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="is_quantidade"                data-endpoint="POSTapi-v1-produtos"
               value=""
               data-component="body">
    <br>
<p>Quantidade na unidade do IS (no produto, por unidade vendida; ex.: 0.35 LT por lata). This field is required when <code>is_aliquota_ad_rem</code> is present. Must match the regex /^\d+(?:.\d+)?$/D. :Attribute deve ser no máximo 99999999999.9999.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_cst</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="is_cst"                data-endpoint="POSTapi-v1-produtos"
               value=""
               data-component="body">
    <br>
<p>CST do IS (3 dígitos). Padrão 000. :Attribute deve ter 3 dígitos.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_c_class_trib</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="is_c_class_trib"                data-endpoint="POSTapi-v1-produtos"
               value=""
               data-component="body">
    <br>
<p>cClassTrib do IS (6 dígitos). Padrão 000001. :Attribute deve ter 6 dígitos.</p>
        </div>
        </form>

                    <h2 id="produtos-PUTapi-v1-produtos--id-">Alterar produto</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Envie todos os campos do produto (substituição completa).</p>

<span id="example-requests-PUTapi-v1-produtos--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost:8090/api/v1/produtos/1" \
    --header "Authorization: Bearer {SEU_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"descricao\": \"Mensalidade escolar\",
    \"nbs\": \"122011200\",
    \"cst\": \"200\",
    \"c_class_trib\": \"200028\",
    \"preco\": 1200
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8090/api/v1/produtos/1"
);

const headers = {
    "Authorization": "Bearer {SEU_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "descricao": "Mensalidade escolar",
    "nbs": "122011200",
    "cst": "200",
    "c_class_trib": "200028",
    "preco": 1200
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8090/api/v1/produtos/1';
$response = $client-&gt;put(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {SEU_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'descricao' =&gt; 'Mensalidade escolar',
            'nbs' =&gt; '122011200',
            'cst' =&gt; '200',
            'c_class_trib' =&gt; '200028',
            'preco' =&gt; 1200.0,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-produtos--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: null,
        &quot;descricao&quot;: &quot;architecto eius et&quot;,
        &quot;tipo&quot;: &quot;mercadoria&quot;,
        &quot;ncm&quot;: &quot;12589365&quot;,
        &quot;nbs&quot;: null,
        &quot;cst&quot;: &quot;000&quot;,
        &quot;c_class_trib&quot;: &quot;000001&quot;,
        &quot;preco&quot;: &quot;45117.64&quot;,
        &quot;is_cst&quot;: null,
        &quot;is_c_class_trib&quot;: null,
        &quot;is_unidade&quot;: null,
        &quot;is_quantidade&quot;: null,
        &quot;is_aliquota_ad_valorem&quot;: null,
        &quot;is_aliquota_ad_rem&quot;: null,
        &quot;criado_em&quot;: null,
        &quot;atualizado_em&quot;: null
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PUTapi-v1-produtos--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-produtos--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-produtos--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-produtos--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-produtos--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-produtos--id-" data-method="PUT"
      data-path="api/v1/produtos/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-produtos--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-produtos--id-"
                    onclick="tryItOut('PUTapi-v1-produtos--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-produtos--id-"
                    onclick="cancelTryOut('PUTapi-v1-produtos--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-produtos--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/produtos/{id}</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/v1/produtos/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="PUTapi-v1-produtos--id-"
               value="Bearer {SEU_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {SEU_TOKEN}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-produtos--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-produtos--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="PUTapi-v1-produtos--id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the produto. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>produto</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="produto"                data-endpoint="PUTapi-v1-produtos--id-"
               value="1"
               data-component="url">
    <br>
<p>ID do produto. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>descricao</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="descricao"                data-endpoint="PUTapi-v1-produtos--id-"
               value="Mensalidade escolar"
               data-component="body">
    <br>
<p>Descrição do produto ou serviço. :Attribute deve ter no máximo 255 caracteres. Example: <code>Mensalidade escolar</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>ncm</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="ncm"                data-endpoint="PUTapi-v1-produtos--id-"
               value=""
               data-component="body">
    <br>
<p>NCM da mercadoria (8 dígitos). Informe NCM ou NBS. This field is required when <code>nbs</code> is not present. :Attribute deve ter 8 dígitos.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>nbs</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="nbs"                data-endpoint="PUTapi-v1-produtos--id-"
               value="122011200"
               data-component="body">
    <br>
<p>NBS do serviço (9 dígitos). Informe NCM ou NBS. This field is required when <code>ncm</code> is not present. :Attribute deve ter 9 dígitos. Example: <code>122011200</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cst</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="cst"                data-endpoint="PUTapi-v1-produtos--id-"
               value="200"
               data-component="body">
    <br>
<p>CST do IBS/CBS (3 dígitos). :Attribute deve ter 3 dígitos. Example: <code>200</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>c_class_trib</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="c_class_trib"                data-endpoint="PUTapi-v1-produtos--id-"
               value="200028"
               data-component="body">
    <br>
<p>Código de classificação tributária (6 dígitos). :Attribute deve ter 6 dígitos. Example: <code>200028</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>preco</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="preco"                data-endpoint="PUTapi-v1-produtos--id-"
               value="1200"
               data-component="body">
    <br>
<p>Preço em reais, texto decimal com até 2 casas. :Attribute deve ser no máximo 9999999999999.99. Example: <code>1200</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_aliquota_ad_valorem</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="is_aliquota_ad_valorem"                data-endpoint="PUTapi-v1-produtos--id-"
               value=""
               data-component="body">
    <br>
<p>Imposto Seletivo (a partir de 2027, só para NCMs tributados): alíquota ad valorem estimada em %. Must match the regex /^\d+(?:.\d+)?$/D. :Attribute deve ser no mínimo 0. :Attribute deve ser no máximo 100.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_aliquota_ad_rem</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="is_aliquota_ad_rem"                data-endpoint="PUTapi-v1-produtos--id-"
               value=""
               data-component="body">
    <br>
<p>Imposto Seletivo: alíquota ad rem estimada, em R$ por unidade do IS. Exige is_unidade e is_quantidade. Must match the regex /^\d+(?:.\d+)?$/D. :Attribute deve ser no mínimo 0. :Attribute deve ser no máximo 99999999999.9999.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_unidade</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="is_unidade"                data-endpoint="PUTapi-v1-produtos--id-"
               value=""
               data-component="body">
    <br>
<p>Unidade do IS (ex.: LT, VN, KG). This field is required when <code>is_aliquota_ad_rem</code> is present. Must match the regex /^[A-Z0-9]+$/. :Attribute deve ter no máximo 6 caracteres.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_quantidade</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="is_quantidade"                data-endpoint="PUTapi-v1-produtos--id-"
               value=""
               data-component="body">
    <br>
<p>Quantidade na unidade do IS (no produto, por unidade vendida; ex.: 0.35 LT por lata). This field is required when <code>is_aliquota_ad_rem</code> is present. Must match the regex /^\d+(?:.\d+)?$/D. :Attribute deve ser no máximo 99999999999.9999.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_cst</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="is_cst"                data-endpoint="PUTapi-v1-produtos--id-"
               value=""
               data-component="body">
    <br>
<p>CST do IS (3 dígitos). Padrão 000. :Attribute deve ter 3 dígitos.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_c_class_trib</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="is_c_class_trib"                data-endpoint="PUTapi-v1-produtos--id-"
               value=""
               data-component="body">
    <br>
<p>cClassTrib do IS (6 dígitos). Padrão 000001. :Attribute deve ter 6 dígitos.</p>
        </div>
        </form>

                    <h2 id="produtos-DELETEapi-v1-produtos--id-">Excluir produto</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-DELETEapi-v1-produtos--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8090/api/v1/produtos/1" \
    --header "Authorization: Bearer {SEU_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8090/api/v1/produtos/1"
);

const headers = {
    "Authorization": "Bearer {SEU_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8090/api/v1/produtos/1';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {SEU_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-produtos--id-">
            <blockquote>
            <p>Example response (204, Produto excluído):</p>
        </blockquote>
                <pre>
<code>Empty response</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-v1-produtos--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-produtos--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-produtos--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-produtos--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-produtos--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-produtos--id-" data-method="DELETE"
      data-path="api/v1/produtos/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-produtos--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-v1-produtos--id-"
                    onclick="tryItOut('DELETEapi-v1-produtos--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-v1-produtos--id-"
                    onclick="cancelTryOut('DELETEapi-v1-produtos--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-v1-produtos--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/produtos/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="DELETEapi-v1-produtos--id-"
               value="Bearer {SEU_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {SEU_TOKEN}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-produtos--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-produtos--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="DELETEapi-v1-produtos--id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the produto. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>produto</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="produto"                data-endpoint="DELETEapi-v1-produtos--id-"
               value="1"
               data-component="url">
    <br>
<p>ID do produto. Example: <code>1</code></p>
            </div>
                    </form>

                <h1 id="simulacao-do-catalogo">Simulação do catálogo</h1>

    <p>Simula todos os produtos da empresa (ou os itens de uma nota importada) em segundo plano. Permissão: <code>simulacoes</code>.</p>

                                <h2 id="simulacao-do-catalogo-GETapi-v1-simulacoes">Listar simulações</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Simulações do catálogo da empresa, da mais recente para a mais antiga.</p>

<span id="example-requests-GETapi-v1-simulacoes">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8090/api/v1/simulacoes?por_pagina=25&amp;page=1" \
    --header "Authorization: Bearer {SEU_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8090/api/v1/simulacoes"
);

const params = {
    "por_pagina": "25",
    "page": "1",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {SEU_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8090/api/v1/simulacoes';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {SEU_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'query' =&gt; [
            'por_pagina' =&gt; '25',
            'page' =&gt; '1',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-simulacoes">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: null,
            &quot;status&quot;: &quot;pendente&quot;,
            &quot;status_descricao&quot;: &quot;Na fila&quot;,
            &quot;em_andamento&quot;: true,
            &quot;data_fato_gerador&quot;: &quot;2027-03-10&quot;,
            &quot;uf&quot;: &quot;RS&quot;,
            &quot;municipio&quot;: 4314902,
            &quot;aliquotas_nominais&quot;: {
                &quot;cbs&quot;: &quot;8.8&quot;,
                &quot;ibs_estadual&quot;: &quot;0.05&quot;,
                &quot;ibs_municipal&quot;: &quot;0.05&quot;
            },
            &quot;calculo_simulado&quot;: true,
            &quot;importacao_id&quot;: null,
            &quot;itens&quot;: {
                &quot;total&quot;: null,
                &quot;processados&quot;: 0,
                &quot;com_erro&quot;: 0
            },
            &quot;totais&quot;: {
                &quot;base_calculo&quot;: null,
                &quot;cbs&quot;: null,
                &quot;ibs&quot;: null,
                &quot;imposto_seletivo&quot;: null,
                &quot;tributos&quot;: null
            },
            &quot;criada_em&quot;: null,
            &quot;concluida_em&quot;: null,
            &quot;aviso&quot;: &quot;Simula&ccedil;&atilde;o feita com a calculadora oficial da Receita Federal. N&atilde;o substitui a orienta&ccedil;&atilde;o de um contador.&quot;
        },
        {
            &quot;id&quot;: null,
            &quot;status&quot;: &quot;pendente&quot;,
            &quot;status_descricao&quot;: &quot;Na fila&quot;,
            &quot;em_andamento&quot;: true,
            &quot;data_fato_gerador&quot;: &quot;2027-03-10&quot;,
            &quot;uf&quot;: &quot;RS&quot;,
            &quot;municipio&quot;: 4314902,
            &quot;aliquotas_nominais&quot;: {
                &quot;cbs&quot;: &quot;8.8&quot;,
                &quot;ibs_estadual&quot;: &quot;0.05&quot;,
                &quot;ibs_municipal&quot;: &quot;0.05&quot;
            },
            &quot;calculo_simulado&quot;: true,
            &quot;importacao_id&quot;: null,
            &quot;itens&quot;: {
                &quot;total&quot;: null,
                &quot;processados&quot;: 0,
                &quot;com_erro&quot;: 0
            },
            &quot;totais&quot;: {
                &quot;base_calculo&quot;: null,
                &quot;cbs&quot;: null,
                &quot;ibs&quot;: null,
                &quot;imposto_seletivo&quot;: null,
                &quot;tributos&quot;: null
            },
            &quot;criada_em&quot;: null,
            &quot;concluida_em&quot;: null,
            &quot;aviso&quot;: &quot;Simula&ccedil;&atilde;o feita com a calculadora oficial da Receita Federal. N&atilde;o substitui a orienta&ccedil;&atilde;o de um contador.&quot;
        }
    ],
    &quot;links&quot;: {
        &quot;first&quot;: &quot;/?page=1&quot;,
        &quot;last&quot;: &quot;/?page=1&quot;,
        &quot;prev&quot;: null,
        &quot;next&quot;: null
    },
    &quot;meta&quot;: {
        &quot;current_page&quot;: 1,
        &quot;from&quot;: 1,
        &quot;last_page&quot;: 1,
        &quot;links&quot;: [
            {
                &quot;url&quot;: null,
                &quot;label&quot;: &quot;&amp;laquo; Previous&quot;,
                &quot;page&quot;: null,
                &quot;active&quot;: false
            },
            {
                &quot;url&quot;: &quot;/?page=1&quot;,
                &quot;label&quot;: &quot;1&quot;,
                &quot;page&quot;: 1,
                &quot;active&quot;: true
            },
            {
                &quot;url&quot;: null,
                &quot;label&quot;: &quot;Next &amp;raquo;&quot;,
                &quot;page&quot;: null,
                &quot;active&quot;: false
            }
        ],
        &quot;path&quot;: &quot;/&quot;,
        &quot;per_page&quot;: 25,
        &quot;to&quot;: 2,
        &quot;total&quot;: 2
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-simulacoes" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-simulacoes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-simulacoes"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-simulacoes" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-simulacoes">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-simulacoes" data-method="GET"
      data-path="api/v1/simulacoes"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-simulacoes', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-simulacoes"
                    onclick="tryItOut('GETapi-v1-simulacoes');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-simulacoes"
                    onclick="cancelTryOut('GETapi-v1-simulacoes');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-simulacoes"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/simulacoes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-simulacoes"
               value="Bearer {SEU_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {SEU_TOKEN}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-simulacoes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-simulacoes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>por_pagina</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="por_pagina"                data-endpoint="GETapi-v1-simulacoes"
               value="25"
               data-component="query">
    <br>
<p>Itens por página (1 a 100). Example: <code>25</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>page</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="page"                data-endpoint="GETapi-v1-simulacoes"
               value="1"
               data-component="query">
    <br>
<p>Página. Example: <code>1</code></p>
            </div>
                </form>

                    <h2 id="simulacao-do-catalogo-POSTapi-v1-simulacoes">Iniciar simulação do catálogo</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Enfileira a simulação de todos os produtos da empresa — ou só dos itens de uma nota importada, com <code>importacao_id</code> — e responde <code>202</code> na hora.</p>
<p>Acompanhe o andamento em <code>GET /api/v1/simulacoes/{id}</code> (campo <code>status</code>) e leia o resultado de cada item em <code>GET /api/v1/simulacoes/{id}/itens</code>.</p>

<span id="example-requests-POSTapi-v1-simulacoes">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8090/api/v1/simulacoes" \
    --header "Authorization: Bearer {SEU_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"uf\": \"RS\",
    \"municipio\": \"4314902\",
    \"data\": \"2027-03-10\",
    \"aliquota_cbs\": 8.8,
    \"aliquota_ibs_estadual\": 0.05,
    \"aliquota_ibs_municipal\": 0.05
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8090/api/v1/simulacoes"
);

const headers = {
    "Authorization": "Bearer {SEU_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "uf": "RS",
    "municipio": "4314902",
    "data": "2027-03-10",
    "aliquota_cbs": 8.8,
    "aliquota_ibs_estadual": 0.05,
    "aliquota_ibs_municipal": 0.05
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8090/api/v1/simulacoes';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {SEU_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'uf' =&gt; 'RS',
            'municipio' =&gt; '4314902',
            'data' =&gt; '2027-03-10',
            'aliquota_cbs' =&gt; 8.8,
            'aliquota_ibs_estadual' =&gt; 0.05,
            'aliquota_ibs_municipal' =&gt; 0.05,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-simulacoes">
            <blockquote>
            <p>Example response (202, Simulação enfileirada):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: null,
        &quot;status&quot;: &quot;pendente&quot;,
        &quot;status_descricao&quot;: &quot;Na fila&quot;,
        &quot;em_andamento&quot;: true,
        &quot;data_fato_gerador&quot;: &quot;2027-03-10&quot;,
        &quot;uf&quot;: &quot;RS&quot;,
        &quot;municipio&quot;: 4314902,
        &quot;aliquotas_nominais&quot;: {
            &quot;cbs&quot;: &quot;8.8&quot;,
            &quot;ibs_estadual&quot;: &quot;0.05&quot;,
            &quot;ibs_municipal&quot;: &quot;0.05&quot;
        },
        &quot;calculo_simulado&quot;: true,
        &quot;importacao_id&quot;: null,
        &quot;itens&quot;: {
            &quot;total&quot;: null,
            &quot;processados&quot;: 0,
            &quot;com_erro&quot;: 0
        },
        &quot;totais&quot;: {
            &quot;base_calculo&quot;: null,
            &quot;cbs&quot;: null,
            &quot;ibs&quot;: null,
            &quot;imposto_seletivo&quot;: null,
            &quot;tributos&quot;: null
        },
        &quot;criada_em&quot;: null,
        &quot;concluida_em&quot;: null,
        &quot;aviso&quot;: &quot;Simula&ccedil;&atilde;o feita com a calculadora oficial da Receita Federal. N&atilde;o substitui a orienta&ccedil;&atilde;o de um contador.&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, Sem produtos ou dados inválidos):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Cadastre ou importe produtos antes de simular o cat&aacute;logo.&quot;,
    &quot;errors&quot;: {
        &quot;simulacao&quot;: [
            &quot;Cadastre ou importe produtos antes de simular o cat&aacute;logo.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-simulacoes" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-simulacoes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-simulacoes"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-simulacoes" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-simulacoes">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-simulacoes" data-method="POST"
      data-path="api/v1/simulacoes"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-simulacoes', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-simulacoes"
                    onclick="tryItOut('POSTapi-v1-simulacoes');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-simulacoes"
                    onclick="cancelTryOut('POSTapi-v1-simulacoes');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-simulacoes"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/simulacoes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-simulacoes"
               value="Bearer {SEU_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {SEU_TOKEN}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-simulacoes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-simulacoes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>uf</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="uf"                data-endpoint="POSTapi-v1-simulacoes"
               value="RS"
               data-component="body">
    <br>
<p>UF da operação (sigla). Example: <code>RS</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>RO</code></li> <li><code>AC</code></li> <li><code>AM</code></li> <li><code>RR</code></li> <li><code>PA</code></li> <li><code>AP</code></li> <li><code>TO</code></li> <li><code>MA</code></li> <li><code>PI</code></li> <li><code>CE</code></li> <li><code>RN</code></li> <li><code>PB</code></li> <li><code>PE</code></li> <li><code>AL</code></li> <li><code>SE</code></li> <li><code>BA</code></li> <li><code>MG</code></li> <li><code>ES</code></li> <li><code>RJ</code></li> <li><code>SP</code></li> <li><code>PR</code></li> <li><code>SC</code></li> <li><code>RS</code></li> <li><code>MS</code></li> <li><code>MT</code></li> <li><code>GO</code></li> <li><code>DF</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>municipio</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="municipio"                data-endpoint="POSTapi-v1-simulacoes"
               value="4314902"
               data-component="body">
    <br>
<p>Código IBGE do município (7 dígitos), da UF informada. :Attribute deve ter 7 dígitos. Example: <code>4314902</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>data</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="data"                data-endpoint="POSTapi-v1-simulacoes"
               value="2027-03-10"
               data-component="body">
    <br>
<p>Data do fato gerador (AAAA-MM-DD), entre 2026 e 2033. Must be a valid date in the format <code>Y-m-d</code>. :Attribute deve ser uma data igual ou posterior a <code>2026-01-01</code>. :Attribute deve ser uma data igual ou anterior a <code>2033-12-31</code>. Example: <code>2027-03-10</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>aliquota_cbs</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="aliquota_cbs"                data-endpoint="POSTapi-v1-simulacoes"
               value="8.8"
               data-component="body">
    <br>
<p>Alíquota nominal da CBS em %, texto decimal. Obrigatória de 2027 em diante e proibida em 2026. :Attribute deve ser no mínimo 0. :Attribute deve ser no máximo 100. Example: <code>8.8</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>aliquota_ibs_estadual</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="aliquota_ibs_estadual"                data-endpoint="POSTapi-v1-simulacoes"
               value="0.05"
               data-component="body">
    <br>
<p>Alíquota nominal do IBS estadual em %. Obrigatória de 2027 em diante. :Attribute deve ser no mínimo 0. :Attribute deve ser no máximo 100. Example: <code>0.05</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>aliquota_ibs_municipal</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="aliquota_ibs_municipal"                data-endpoint="POSTapi-v1-simulacoes"
               value="0.05"
               data-component="body">
    <br>
<p>Alíquota nominal do IBS municipal em %. Obrigatória de 2027 em diante. :Attribute deve ser no mínimo 0. :Attribute deve ser no máximo 100. Example: <code>0.05</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>importacao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="importacao_id"                data-endpoint="POSTapi-v1-simulacoes"
               value=""
               data-component="body">
    <br>
<p>Simula só os itens desta nota importada. Sem ele, simula o catálogo inteiro.</p>
        </div>
        </form>

                    <h2 id="simulacao-do-catalogo-GETapi-v1-simulacoes--simulacao_id-">Consultar simulação</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Status, progresso e totais da simulação.</p>

<span id="example-requests-GETapi-v1-simulacoes--simulacao_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8090/api/v1/simulacoes/1" \
    --header "Authorization: Bearer {SEU_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8090/api/v1/simulacoes/1"
);

const headers = {
    "Authorization": "Bearer {SEU_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8090/api/v1/simulacoes/1';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {SEU_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-simulacoes--simulacao_id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: null,
        &quot;status&quot;: &quot;pendente&quot;,
        &quot;status_descricao&quot;: &quot;Na fila&quot;,
        &quot;em_andamento&quot;: true,
        &quot;data_fato_gerador&quot;: &quot;2027-03-10&quot;,
        &quot;uf&quot;: &quot;RS&quot;,
        &quot;municipio&quot;: 4314902,
        &quot;aliquotas_nominais&quot;: {
            &quot;cbs&quot;: &quot;8.8&quot;,
            &quot;ibs_estadual&quot;: &quot;0.05&quot;,
            &quot;ibs_municipal&quot;: &quot;0.05&quot;
        },
        &quot;calculo_simulado&quot;: true,
        &quot;importacao_id&quot;: null,
        &quot;itens&quot;: {
            &quot;total&quot;: null,
            &quot;processados&quot;: 0,
            &quot;com_erro&quot;: 0
        },
        &quot;totais&quot;: {
            &quot;base_calculo&quot;: null,
            &quot;cbs&quot;: null,
            &quot;ibs&quot;: null,
            &quot;imposto_seletivo&quot;: null,
            &quot;tributos&quot;: null
        },
        &quot;criada_em&quot;: null,
        &quot;concluida_em&quot;: null,
        &quot;aviso&quot;: &quot;Simula&ccedil;&atilde;o feita com a calculadora oficial da Receita Federal. N&atilde;o substitui a orienta&ccedil;&atilde;o de um contador.&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (404, Simulação inexistente ou de outra empresa):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Not Found&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-simulacoes--simulacao_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-simulacoes--simulacao_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-simulacoes--simulacao_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-simulacoes--simulacao_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-simulacoes--simulacao_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-simulacoes--simulacao_id-" data-method="GET"
      data-path="api/v1/simulacoes/{simulacao_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-simulacoes--simulacao_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-simulacoes--simulacao_id-"
                    onclick="tryItOut('GETapi-v1-simulacoes--simulacao_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-simulacoes--simulacao_id-"
                    onclick="cancelTryOut('GETapi-v1-simulacoes--simulacao_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-simulacoes--simulacao_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/simulacoes/{simulacao_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-simulacoes--simulacao_id-"
               value="Bearer {SEU_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {SEU_TOKEN}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-simulacoes--simulacao_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-simulacoes--simulacao_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>simulacao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="simulacao_id"                data-endpoint="GETapi-v1-simulacoes--simulacao_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the simulacao. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>simulacao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="simulacao"                data-endpoint="GETapi-v1-simulacoes--simulacao_id-"
               value="1"
               data-component="url">
    <br>
<p>ID da simulação. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="simulacao-do-catalogo-GETapi-v1-simulacoes--simulacao_id--itens">Resultado por item</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Resultado de cada item da simulação, em ordem alfabética. Itens não calculados trazem o motivo em <code>erros</code>.</p>

<span id="example-requests-GETapi-v1-simulacoes--simulacao_id--itens">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8090/api/v1/simulacoes/1/itens?erros=&amp;por_pagina=25&amp;page=1" \
    --header "Authorization: Bearer {SEU_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8090/api/v1/simulacoes/1/itens"
);

const params = {
    "erros": "0",
    "por_pagina": "25",
    "page": "1",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {SEU_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8090/api/v1/simulacoes/1/itens';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {SEU_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'query' =&gt; [
            'erros' =&gt; '0',
            'por_pagina' =&gt; '25',
            'page' =&gt; '1',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-simulacoes--simulacao_id--itens">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: null,
            &quot;produto_id&quot;: null,
            &quot;descricao&quot;: &quot;aut adipisci quidem&quot;,
            &quot;ncm&quot;: null,
            &quot;nbs&quot;: &quot;122011200&quot;,
            &quot;cst&quot;: &quot;200&quot;,
            &quot;c_class_trib&quot;: &quot;200028&quot;,
            &quot;base_calculo&quot;: &quot;1200.00&quot;,
            &quot;estado&quot;: null,
            &quot;calculado&quot;: false,
            &quot;percentual_reducao&quot;: null,
            &quot;valor_cbs&quot;: null,
            &quot;valor_ibs&quot;: null,
            &quot;valor_imposto_seletivo&quot;: null,
            &quot;total_tributos&quot;: null,
            &quot;erros&quot;: []
        },
        {
            &quot;id&quot;: null,
            &quot;produto_id&quot;: null,
            &quot;descricao&quot;: &quot;incidunt iure odit&quot;,
            &quot;ncm&quot;: null,
            &quot;nbs&quot;: &quot;122011200&quot;,
            &quot;cst&quot;: &quot;200&quot;,
            &quot;c_class_trib&quot;: &quot;200028&quot;,
            &quot;base_calculo&quot;: &quot;1200.00&quot;,
            &quot;estado&quot;: null,
            &quot;calculado&quot;: false,
            &quot;percentual_reducao&quot;: null,
            &quot;valor_cbs&quot;: null,
            &quot;valor_ibs&quot;: null,
            &quot;valor_imposto_seletivo&quot;: null,
            &quot;total_tributos&quot;: null,
            &quot;erros&quot;: []
        }
    ],
    &quot;links&quot;: {
        &quot;first&quot;: &quot;/?page=1&quot;,
        &quot;last&quot;: &quot;/?page=1&quot;,
        &quot;prev&quot;: null,
        &quot;next&quot;: null
    },
    &quot;meta&quot;: {
        &quot;current_page&quot;: 1,
        &quot;from&quot;: 1,
        &quot;last_page&quot;: 1,
        &quot;links&quot;: [
            {
                &quot;url&quot;: null,
                &quot;label&quot;: &quot;&amp;laquo; Previous&quot;,
                &quot;page&quot;: null,
                &quot;active&quot;: false
            },
            {
                &quot;url&quot;: &quot;/?page=1&quot;,
                &quot;label&quot;: &quot;1&quot;,
                &quot;page&quot;: 1,
                &quot;active&quot;: true
            },
            {
                &quot;url&quot;: null,
                &quot;label&quot;: &quot;Next &amp;raquo;&quot;,
                &quot;page&quot;: null,
                &quot;active&quot;: false
            }
        ],
        &quot;path&quot;: &quot;/&quot;,
        &quot;per_page&quot;: 25,
        &quot;to&quot;: 2,
        &quot;total&quot;: 2
    },
    &quot;aviso&quot;: &quot;Simula&ccedil;&atilde;o feita com a calculadora oficial da Receita Federal. N&atilde;o substitui a orienta&ccedil;&atilde;o de um contador.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-simulacoes--simulacao_id--itens" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-simulacoes--simulacao_id--itens"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-simulacoes--simulacao_id--itens"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-simulacoes--simulacao_id--itens" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-simulacoes--simulacao_id--itens">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-simulacoes--simulacao_id--itens" data-method="GET"
      data-path="api/v1/simulacoes/{simulacao_id}/itens"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-simulacoes--simulacao_id--itens', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-simulacoes--simulacao_id--itens"
                    onclick="tryItOut('GETapi-v1-simulacoes--simulacao_id--itens');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-simulacoes--simulacao_id--itens"
                    onclick="cancelTryOut('GETapi-v1-simulacoes--simulacao_id--itens');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-simulacoes--simulacao_id--itens"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/simulacoes/{simulacao_id}/itens</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-simulacoes--simulacao_id--itens"
               value="Bearer {SEU_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {SEU_TOKEN}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-simulacoes--simulacao_id--itens"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-simulacoes--simulacao_id--itens"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>simulacao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="simulacao_id"                data-endpoint="GETapi-v1-simulacoes--simulacao_id--itens"
               value="1"
               data-component="url">
    <br>
<p>The ID of the simulacao. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>simulacao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="simulacao"                data-endpoint="GETapi-v1-simulacoes--simulacao_id--itens"
               value="1"
               data-component="url">
    <br>
<p>ID da simulação. Example: <code>1</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>erros</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="GETapi-v1-simulacoes--simulacao_id--itens" style="display: none">
            <input type="radio" name="erros"
                   value="1"
                   data-endpoint="GETapi-v1-simulacoes--simulacao_id--itens"
                   data-component="query"             >
            <code>true</code>
        </label>
        <label data-endpoint="GETapi-v1-simulacoes--simulacao_id--itens" style="display: none">
            <input type="radio" name="erros"
                   value="0"
                   data-endpoint="GETapi-v1-simulacoes--simulacao_id--itens"
                   data-component="query"             >
            <code>false</code>
        </label>
    <br>
<p>Só os itens que não puderam ser calculados. Example: <code>false</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>por_pagina</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="por_pagina"                data-endpoint="GETapi-v1-simulacoes--simulacao_id--itens"
               value="25"
               data-component="query">
    <br>
<p>Itens por página (1 a 100). Example: <code>25</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>page</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="page"                data-endpoint="GETapi-v1-simulacoes--simulacao_id--itens"
               value="1"
               data-component="query">
    <br>
<p>Página. Example: <code>1</code></p>
            </div>
                </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                                                        <button type="button" class="lang-button" data-language-name="php">php</button>
                            </div>
            </div>
</div>
</body>
</html>
