#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

TARGET="${HECATE_ZAP_TARGET:-}"
ZAP_BIN="${HECATE_ZAP_BIN:-$PROJECT_ROOT/.tools/zap/zap.sh}"
REPORT="${HECATE_ZAP_REPORT:-$PROJECT_ROOT/runtime/security/zap-report.html}"

if [[ -z "$TARGET" ]]; then
    printf '[ERRO] Defina HECATE_ZAP_TARGET para uma URL local de desenvolvimento.\n' >&2
    printf 'Exemplo: HECATE_ZAP_TARGET=http://127.0.0.1:8080 composer security:dast\n' >&2
    exit 1
fi

if [[ ! "$TARGET" =~ ^https?://(127\.0\.0\.1|localhost)(:[0-9]+)?(/|$) ]]; then
    printf '[ERRO] O DAST automatizado do projeto aceita somente localhost.\n' >&2
    exit 1
fi

if [[ ! -e "$ZAP_BIN" ]]; then
    printf '[ERRO] OWASP ZAP local não encontrado: %s\n' "$ZAP_BIN" >&2
    printf '\nPara instalar a versão homologada do projeto:\n\n  make setup\n' >&2
    exit 1
fi

if [[ ! -x "$ZAP_BIN" ]]; then
    printf '[ERRO] OWASP ZAP local está sem permissão de execução: %s\n' "$ZAP_BIN" >&2
    printf '\nPara corrigir:\n\n  chmod +x "%s"\n' "$ZAP_BIN" >&2
    exit 1
fi

VERSION_OUTPUT=""
if ! VERSION_OUTPUT="$($ZAP_BIN -version 2>&1)"; then
    printf '[ERRO] OWASP ZAP local foi encontrado, mas não consegue iniciar.\n' >&2
    printf '\nSaída do teste:\n%s\n' "$VERSION_OUTPUT" >&2
    printf '\nTeste manual:\n\n  %s -version\n' "$ZAP_BIN" >&2
    printf '\nSe a instalação estiver inconsistente:\n\n  rm -rf "%s/.tools/zap"\n  make setup\n' "$PROJECT_ROOT" >&2
    exit 1
fi

mkdir -p "$(dirname "$REPORT")"
printf '[SECURITY] OWASP ZAP active scan local: %s\n' "$TARGET"
printf '[SECURITY] Relatório: %s\n' "$REPORT"
exec "$ZAP_BIN" -cmd -quickurl "$TARGET" -quickout "$REPORT" -quickprogress
