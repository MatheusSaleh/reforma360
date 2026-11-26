---
name: pull-request
description: "Abre pull requests do Reforma360 com resumo, vínculo à issue, tabelas de cenários e checklist de validação. Use quando o usuário pedir PR, pull request, abrir PR ou gh pr create."
---

# Pull Request (Reforma360)

## Workflow

Em paralelo:
1. `git status`
2. `git diff` (staged + unstaged)
3. Branch tem upstream? precisa de push?
4. `git log main..HEAD` e `git diff main...HEAD` — revisar **todos** os commits.

Depois:
1. Confirmar que Pest, Larastan e Pint passam localmente (ou avisar o que falhou).
2. `git push -u origin <branch>` se necessário.
3. `gh pr create --base main` com corpo via HEREDOC.
4. Devolver a URL.

Título = mesmo padrão do commit: `feat(#12): adiciona autocomplete de NCM`.

## Template

```markdown
## Resumo
- <1–3 bullets: o quê e por quê>

Closes #<issue>

## Como testar

| # | Cenário | Passos | Resultado esperado |
|---|---|---|---|
| 1 | **Fluxo principal** | … | … |
| 2 | Validação | … | … |
| 3 | Falha da calculadora ⚠️ | … | … |

## Checklist
- [ ] Testes Pest cobrindo a mudança
- [ ] `./vendor/bin/pest`, `vendor/bin/phpstan analyse`, `vendor/bin/pint --test` verdes
- [ ] Sem `float` em dinheiro/alíquota
- [ ] Aviso de simulação presente onde há resultado de cálculo
- [ ] Fluxo exercitado no navegador (se tocar UI)
- [ ] Migrations com `down()` e seeders idempotentes (se houver)
```

Cenários vêm do diff real (não genéricos). Marque ⚠️ o que envolve valores, dados fiscais, segurança ou concorrência. Nunca commitar o arquivo da descrição do PR.
