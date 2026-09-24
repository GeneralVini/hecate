#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

SEMGREP_BIN="${HECATE_SEMGREP_BIN:-$PROJECT_ROOT/.tools/semgrep/bin/semgrep}"
SEMGREP_CONFIG="$PROJECT_ROOT/security/semgrep.yml"
SEMGREP_TESTS="$PROJECT_ROOT/security/semgrep-tests"

if [[ ! -x "$SEMGREP_BIN" ]]; then
    printf '[ERRO] Semgrep local não encontrado ou não executável: %s\n' "$SEMGREP_BIN" >&2
    printf '\nExecute primeiro:\n\n  make setup\n' >&2
    exit 1
fi

if [[ ! -r "$SEMGREP_CONFIG" ]]; then
    printf '[ERRO] Configuração do Semgrep ausente ou ilegível: %s\n' "$SEMGREP_CONFIG" >&2
    exit 1
fi

if [[ ! -d "$SEMGREP_TESTS" ]]; then
    printf '[ERRO] Fixtures do Semgrep não encontradas: %s\n' "$SEMGREP_TESTS" >&2
    exit 1
fi

exec "$SEMGREP_BIN" \
    --test \
    --config "$SEMGREP_CONFIG" \
    --metrics=off \
    "$SEMGREP_TESTS"
