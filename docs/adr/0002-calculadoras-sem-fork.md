# ADR 0002 — Consumir as calculadoras da Receita como serviços internos sem fork

**Status** · Aceita

## Contexto

A Receita Federal distribui as calculadoras do regime regular e do Simples Nacional e o gerador de DANFSe como projetos separados. O Reforma360 precisa usar essas implementações e manter no Laravel as regras do seu catálogo, da simulação e da apresentação dos resultados.

## Decisão

Clonar os repositórios upstream em `services/` e construir os serviços no Docker Compose a partir desse código, sem modificações. As duas calculadoras Java leem o mesmo banco SQLite, preparado pelo Flyway do regime regular e montado como somente leitura nos dois serviços. As calculadoras e o DANFSe ficam na rede interna `calculadoras`, sem portas publicadas no host.

O Laravel acessa os serviços por clientes HTTP em `App\Integrations\Calculadora`. As URLs vêm da configuração do ambiente. Os clientes convertem contratos externos em DTOs e exceções da aplicação. Os testes substituem os serviços por respostas de exemplo com `Http::fake()`.

## Alternativas consideradas

- **Manter forks.** Permitiria alterar os serviços, mas exigiria conciliar as mudanças locais com cada atualização da Receita.
- **Reimplementar as calculadoras no Laravel.** Reduziria a quantidade de serviços, ao custo de acompanhar e reproduzir a evolução das regras tributárias e da base de referência.

## Consequências

- A atualização do upstream exige reconstruir as imagens, atualizar o banco de referência e verificar a compatibilidade dos contratos antes de subir a nova versão.
- A aplicação pode evoluir seus fluxos e testar a integração sem executar os serviços externos. Em execução, o cálculo depende da disponibilidade dos serviços internos.

Configuração e código de referência em [compose.yaml](../../compose.yaml), [script de atualização](../../scripts/calculadoras-clone.sh) e [clientes HTTP](../../app/Integrations/Calculadora).
