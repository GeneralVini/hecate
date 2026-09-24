#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

SEMGREP_BIN="${HECATE_SEMGREP_BIN:-$PROJECT_ROOT/.tools/semgrep/bin/semgrep}"
SEMGREP_CONFIG="$PROJECT_ROOT/security/semgrep-rules/hecate.yml"

line() {
    printf '%s\n' '============================================================'
}

if [[ ! -x "$SEMGREP_BIN" ]]; then
    printf '[ERRO] Semgrep local não encontrado ou não executável: %s\n' "$SEMGREP_BIN" >&2
    printf '\nExecute primeiro:\n\n  make setup\n' >&2
    exit 1
fi

if [[ ! -r "$SEMGREP_CONFIG" ]]; then
    printf '[ERRO] Configuração do Semgrep ausente ou ilegível: %s\n' "$SEMGREP_CONFIG" >&2
    exit 1
fi

RULE_COUNT="$(grep -cE '^  - id:' "$SEMGREP_CONFIG" || true)"

line
printf ' HECATE — Semgrep / validação de regras\n'
line
printf '[INFO] Regras configuradas: %s\n\n' "$RULE_COUNT"

if "$SEMGREP_BIN" --validate --config "$SEMGREP_CONFIG" --metrics=off; then
    printf '\n[OK] %s regras Semgrep válidas.\n' "$RULE_COUNT"
    exit 0
fi

printf '\n[ERRO] A configuração Semgrep é inválida.\n' >&2
printf 'Corrija a regra indicada acima antes de executar testes ou scan.\n' >&2
exit 2
