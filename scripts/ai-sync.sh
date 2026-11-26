#!/usr/bin/env bash
# Cria/atualiza symlinks das skills de .ai/skills/ para as pastas de cada ferramenta de IA.
set -euo pipefail

root="$(cd "$(dirname "$0")/.." && pwd)"
targets=(.claude/skills .agents/skills .cursor/skills .github/skills)

for target in "${targets[@]}"; do
    mkdir -p "$root/$target"
    find "$root/$target" -maxdepth 1 -type l -delete
    for skill in "$root"/.ai/skills/*/; do
        name="$(basename "$skill")"
        ln -s "../../.ai/skills/$name" "$root/$target/$name"
    done
    echo "ok: $target"
done
