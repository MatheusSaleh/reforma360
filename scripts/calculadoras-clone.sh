#!/usr/bin/env bash
# Clona (ou atualiza) as calculadoras da Receita Federal em services/.
# O código upstream não é versionado neste repositório e é usado sem modificações.
set -euo pipefail

root="$(cd "$(dirname "$0")/.." && pwd)"
repos=(
    calculadora-rtc-regime-regular
    calculadora-rtc-simples-nacional
    calculadora-rtc-gerador-danfse
)

mkdir -p "$root/services"

for repo in "${repos[@]}"; do
    dir="$root/services/$repo"
    if [ -d "$dir/.git" ]; then
        echo "atualizando $repo"
        git -C "$dir" pull --ff-only
    else
        echo "clonando $repo"
        git clone --depth 1 "https://github.com/ReceitaFederal/$repo.git" "$dir"
    fi
done
