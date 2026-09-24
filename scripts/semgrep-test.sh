#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

SEMGREP_BIN="${HECATE_SEMGREP_BIN:-$PROJECT_ROOT/.tools/semgrep/bin/semgrep}"
SEMGREP_RULES="$PROJECT_ROOT/security/semgrep-rules"
SEMGREP_CONFIG="$SEMGREP_RULES/hecate.yml"
SEMGREP_TESTS="$PROJECT_ROOT/security/semgrep-tests"

line() {
    printf '%s\n' '============================================================'
}

if [[ "$SEMGREP_BIN" == */* ]]; then
    if [[ ! -x "$SEMGREP_BIN" ]]; then
        printf '[ERRO] Semgrep não encontrado ou não executável: %s\n' "$SEMGREP_BIN" >&2
        printf '\nExecute primeiro:\n\n  make setup\n' >&2
        exit 1
    fi
else
    RESOLVED_SEMGREP_BIN="$(command -v "$SEMGREP_BIN" || true)"
    if [[ -z "$RESOLVED_SEMGREP_BIN" ]]; then
        printf '[ERRO] Semgrep não encontrado no PATH: %s\n' "$SEMGREP_BIN" >&2
        exit 1
    fi
    SEMGREP_BIN="$RESOLVED_SEMGREP_BIN"
fi

if [[ ! -r "$SEMGREP_CONFIG" ]]; then
    printf '[ERRO] Configuração do Semgrep ausente ou ilegível: %s\n' "$SEMGREP_CONFIG" >&2
    exit 1
fi

if [[ ! -r "$SEMGREP_TESTS/hecate.php" ]]; then
    printf '[ERRO] Fixture correspondente à regra não encontrada: %s\n' "$SEMGREP_TESTS/hecate.php" >&2
    exit 1
fi

# Evita executar --test com regra sintaticamente inválida e produzir traceback pouco útil.
if ! "$SEMGREP_BIN" --validate --config "$SEMGREP_CONFIG" --metrics=off >/dev/null 2>&1; then
    printf '[ERRO] As regras Semgrep não passaram pela validação.\n' >&2
    printf '\nExecute para obter o diagnóstico:\n\n  composer security:semgrep:validate\n' >&2
    exit 2
fi

RULE_COUNT="$(grep -cE '^  - id:' "$SEMGREP_CONFIG" || true)"

line
printf ' HECATE — Semgrep / testes de regressão\n'
line
printf '[INFO] Regras configuradas: %s\n' "$RULE_COUNT"
printf '[INFO] Pareamento: semgrep-rules/hecate.yml <-> semgrep-tests/hecate.php\n\n'

if "$SEMGREP_BIN" \
    --test \
    --config "$SEMGREP_RULES" \
    --metrics=off \
    "$SEMGREP_TESTS"; then
    printf '\n[OK] Testes positivos e negativos das regras Semgrep aprovados.\n'
    exit 0
fi

printf '\n[ERRO] Os testes das regras Semgrep falharam.\n' >&2
printf 'Revise as linhas ruleid/ok exibidas acima antes de executar o scan do projeto.\n' >&2
exit 2
