#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

SEMGREP_BIN="${HECATE_SEMGREP_BIN:-$PROJECT_ROOT/.tools/semgrep/bin/semgrep}"
SEMGREP_CONFIG="$PROJECT_ROOT/security/semgrep.yml"

if [[ ! -e "$SEMGREP_BIN" ]]; then
    printf '[ERRO] Semgrep local não encontrado: %s\n' "$SEMGREP_BIN" >&2
    printf '\nPara instalar a versão homologada do projeto:\n\n  make setup\n' >&2
    exit 1
fi

if [[ ! -x "$SEMGREP_BIN" ]]; then
    printf '[ERRO] Semgrep local está sem permissão de execução: %s\n' "$SEMGREP_BIN" >&2
    printf '\nPara corrigir:\n\n  chmod +x "%s"\n' "$SEMGREP_BIN" >&2
    exit 1
fi

if [[ ! -f "$SEMGREP_CONFIG" ]]; then
    printf '[ERRO] Configuração do Semgrep não encontrada: %s\n' "$SEMGREP_CONFIG" >&2
    exit 1
fi

if [[ ! -r "$SEMGREP_CONFIG" ]]; then
    printf '[ERRO] Configuração do Semgrep não está legível: %s\n' "$SEMGREP_CONFIG" >&2
    printf '\nPara corrigir:\n\n  chmod u+r "%s"\n' "$SEMGREP_CONFIG" >&2
    exit 1
fi

VERSION_OUTPUT=""
if ! VERSION_OUTPUT="$($SEMGREP_BIN --version 2>&1)"; then
    printf '[ERRO] Semgrep local foi encontrado, mas não consegue iniciar.\n' >&2
    printf '\nSaída do teste:\n%s\n' "$VERSION_OUTPUT" >&2
    printf '\nSe houver erro de permissão em binário interno:\n\n' >&2
    printf "  find '%s/.tools/semgrep' -type f \\( -name 'semgrep-core' -o -name 'osemgrep' \\) -exec chmod +x {} +\n" "$PROJECT_ROOT" >&2
    printf '\nDepois valide:\n\n  %s --version\n' "$SEMGREP_BIN" >&2
    printf '\nSe necessário, recrie a instalação homologada:\n\n  rm -rf "%s/.tools/semgrep"\n  make setup\n' "$PROJECT_ROOT" >&2
    exit 1
fi

exec "$SEMGREP_BIN" \
    --config "$SEMGREP_CONFIG" \
    --error \
    --metrics=off \
    src config public
