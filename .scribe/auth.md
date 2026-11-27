# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {SEU_TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Crie um token em <b>Tokens de API</b> (menu do Reforma360), escolhendo as permissões: <code>produtos:ler</code>, <code>produtos:escrever</code> e <code>simulacoes</code>. Envie no cabeçalho <code>Authorization: Bearer {token}</code>.
