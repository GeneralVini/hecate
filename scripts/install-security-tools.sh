#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TOOLS="$ROOT/.tools"
SEMGREP_VERSION="1.177.0"
ZAP_VERSION="2.17.0"
ZAP_SHA256="efe799aaa3627db683b43f00c9c210aea0b75c00cc8f0a0f0434d12bb3ddde5a"
ZAP_ARCHIVE="ZAP_${ZAP_VERSION}_Linux.tar.gz"
ZAP_URL="https://github.com/zaproxy/zaproxy/releases/download/v${ZAP_VERSION}/${ZAP_ARCHIVE}"

mkdir -p "$TOOLS"

if [[ ! -x "$TOOLS/semgrep/bin/semgrep" ]]; then
    command -v python3 >/dev/null || { echo '[ERRO] Python 3 é necessário para Semgrep.' >&2; exit 1; }
    python3 -m venv "$TOOLS/semgrep" || { echo '[ERRO] Instale python3-venv e repita make setup.' >&2; exit 1; }
    "$TOOLS/semgrep/bin/python" -m pip install --disable-pip-version-check "semgrep==${SEMGREP_VERSION}"
fi
"$TOOLS/semgrep/bin/semgrep" --version

if [[ ! -x "$TOOLS/zap/zap.sh" ]]; then
    command -v java >/dev/null || { echo '[ERRO] Java 17+ é necessário para OWASP ZAP.' >&2; exit 1; }
    command -v curl >/dev/null || { echo '[ERRO] curl é necessário para instalar OWASP ZAP.' >&2; exit 1; }
    command -v sha256sum >/dev/null || { echo '[ERRO] sha256sum é necessário para validar OWASP ZAP.' >&2; exit 1; }
    tmp="$(mktemp -d)"
    trap 'rm -rf "$tmp"' EXIT
    curl -fL "$ZAP_URL" -o "$tmp/$ZAP_ARCHIVE"
    printf '%s  %s\n' "$ZAP_SHA256" "$tmp/$ZAP_ARCHIVE" | sha256sum -c -
    tar -xzf "$tmp/$ZAP_ARCHIVE" -C "$tmp"
    rm -rf "$TOOLS/zap"
    mv "$tmp/ZAP_${ZAP_VERSION}" "$TOOLS/zap"
fi
"$TOOLS/zap/zap.sh" -version
