---
name: conventional-commit
description: "Cria commits no padrão Conventional Commits (tipo em inglês, descrição em PT-BR, escopo = issue do GitHub) e nomeia branches do Reforma360. Use quando o usuário pedir commit, mensagem de commit, criar branch ou registrar alterações."
---

# Conventional Commit (Reforma360)

Nunca commitar sem pedido explícito.

## Formato

```
tipo(#issue): descrição curta no presente/imperativo
```

- Tipo em inglês, descrição em português, sem ponto final, até ~72 caracteres.
- Escopo = número da issue (`#12`). Sem issue: módulo (`importacao`, `simulacao`, `docker`).
- Corpo opcional explicando o **porquê**; `Closes #12` no corpo quando o commit fecha a issue.
- Se a branch for `feat/12-...`, o escopo é `#12`.

| Tipo | Uso |
|------|-----|
| `feat` | Nova funcionalidade |
| `fix` | Correção de bug |
| `refactor` | Sem mudança de comportamento |
| `test` | Só testes |
| `docs` | Só documentação |
| `style` | Formatação |
| `chore` | Tooling, deps, config, Docker |
| `perf` | Performance |
| `ci` | GitHub Actions |

Exemplos:

```
feat(#12): adiciona autocomplete de NCM via dados abertos
fix(#31): corrige arredondamento da CBS por item
chore(#3): sobe calculadoras da Receita no docker-compose
test(#18): cobre importação de CSV com linhas inválidas
```

## Workflow

Em paralelo: `git status`, `git diff` + `git diff --staged`, `git log -10 --oneline`.

Depois:
1. Redigir a mensagem a partir do diff (foco no porquê).
2. `git add` só dos arquivos relevantes — nunca `.env`, dumps, XML/planilhas reais, `services/`.
3. Commit via HEREDOC.
4. `git status` para confirmar.

Segurança: não alterar `git config`; sem `--no-verify`; sem `--amend` salvo pedido; hook falhou → corrigir e novo commit.

## Branches

`{tipo}/{issue}-{slug-kebab}` a partir de `main`:

```
feat/12-autocomplete-ncm
fix/31-arredondamento-cbs
chore/3-docker-compose
```

Evitar `feature/`, `bugfix/`, `hotfix/`.
