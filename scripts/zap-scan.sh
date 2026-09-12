#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

TARGET="${HECATE_ZAP_TARGET:-}"
ZAP_BIN="${HECATE_ZAP_BIN:-$PROJECT_ROOT/.tools/zap/zap.sh}"

if [[ -z "$TARGET" ]]; then
    printf '[ERRO] Defina HECATE_ZAP_TARGET para uma URL local de desenvolvimento.\n' >&2
    printf 'Exemplo: HECATE_ZAP_TARGET=http://127.0.0.1:8080 composer security:dast\n' >&2
    exit 1
fi

if [[ ! "$TARGET" =~ ^https?://(127\.0\.0\.1|localhost)(:[0-9]+)?(/|$) ]]; then
    printf '[ERRO] O comando automatizado do projeto aceita somente localhost.\n' >&2
    exit 1
fi

if [[ ! -x "$ZAP_BIN" ]]; then
    if command -v zaproxy >/dev/null 2>&1; then
        ZAP_BIN="$(command -v zaproxy)"
    else
        printf '[ERRO] OWASP ZAP não encontrado. Execute: make setup\n' >&2
        exit 1
    fi
fi

printf '[SECURITY] OWASP ZAP reconnaissance scan: %s\n' "$TARGET"
exec "$ZAP_BIN" -cmd -zapit "$TARGET"
