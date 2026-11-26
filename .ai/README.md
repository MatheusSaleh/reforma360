# Pastas de IA

Guias, regras e skills para agentes de IA. `.ai/` é a **fonte canônica**; as pastas de cada ferramenta só têm symlinks para cá.

```
AGENTS.md            entrada para todos os agentes
CLAUDE.md            importa AGENTS.md (+ notas do Claude Code)
.ai/guidelines/      regras de domínio (tributário, dinheiro, dados sensíveis)
.ai/rules/           regras por caminho de arquivo — index.md mapeia globs → arquivo
.ai/skills/          skills (SKILL.md + referências)
.claude/skills/      → .ai/skills/*   (Claude Code)
.agents/skills/      → .ai/skills/*   (Codex / Amp)
.cursor/skills/      → .ai/skills/*   (Cursor)
.cursor/rules/       rule alwaysApply do Cursor
.github/skills/      → .ai/skills/*   (GitHub Copilot)
```

## Skills

| Skill | Conteúdo |
|-------|----------|
| `laravel-best-practices` | Boas práticas Laravel, indexadas por assunto (`rules/*.md`) |
| `calculadora-rtc` | Integração com as calculadoras da Receita e o DANFSe |
| `catalog-import` | Importadores CSV/XLSX/XML (Strategy) e validação |
| `pest-testing` | Padrões de teste Pest do projeto |
| `conventional-commit` | Commits e branches |
| `pull-request` | Abertura de PR com cenários de validação |

## Como manter

- Nova skill: crie `.ai/skills/<nome>/SKILL.md` e rode `scripts/ai-sync.sh` para criar os symlinks.
- Nova regra por caminho: crie `.ai/rules/<area>.md` com frontmatter `paths:` e adicione a linha em `.ai/rules/index.md`.
- Decisão tomada durante o desenvolvimento (ex.: "dinheiro em centavos") → registre na regra da área, curta e com o porquê.

## Laravel Boost (opcional)

Se `laravel/boost` for instalado (`composer require laravel/boost --dev && php artisan boost:install`), ele passa a gerar o bloco `<laravel-boost-guidelines>` em `AGENTS.md`/`CLAUDE.md`, o MCP (`php artisan boost:mcp`) e skills oficiais. Mantenha as skills custom em `.ai/skills/` e as guidelines em `.ai/guidelines/` — o Boost as compõe — e revise o diff de `AGENTS.md`/`CLAUDE.md` depois do `boost:install`.
