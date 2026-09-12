#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

SEMGREP_BIN="${HECATE_SEMGREP_BIN:-$PROJECT_ROOT/.tools/semgrep/bin/semgrep}"

if [[ ! -x "$SEMGREP_BIN" ]]; then
    if command -v semgrep >/dev/null 2>&1; then
        SEMGREP_BIN="$(command -v semgrep)"
    else
        printf '[ERRO] Semgrep não encontrado. Execute: make setup\n' >&2
        exit 1
    fi
fi

exec "$SEMGREP_BIN" \
    --config "$PROJECT_ROOT/security/semgrep.yml" \
    --error \
    --metrics=off \
    src config public
