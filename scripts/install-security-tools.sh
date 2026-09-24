#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TOOLS="$ROOT/.tools"
SEMGREP_VERSION="1.177.0"
ZAP_VERSION="2.17.0"
ZAP_SHA256="efe799aaa3627db683b43f00c9c210aea0b75c00cc8f0a0f0434d12bb3ddde5a"
ZAP_ARCHIVE="ZAP_${ZAP_VERSION}_Linux.tar.gz"
ZAP_URL="https://github.com/zaproxy/zaproxy/releases/download/v${ZAP_VERSION}/${ZAP_ARCHIVE}"
SEMGREP_DIR="$TOOLS/semgrep"
SEMGREP_PYTHON="$SEMGREP_DIR/bin/python"
SEMGREP_BIN="$SEMGREP_DIR/bin/semgrep"
ZAP_BIN="$TOOLS/zap/zap.sh"
PLATFORM="unknown"
OS_LABEL="Linux"

error() {
    printf '[ERRO] %s\n' "$1" >&2
}

info() {
    printf '[INFO] %s\n' "$1"
}

repair() {
    printf '\nPara corrigir:\n\n  %s\n' "$1" >&2
}

detect_platform() {
    local os_id=""
    local os_like=""

    if [[ -r /etc/os-release ]]; then
        # shellcheck disable=SC1091
        . /etc/os-release
        os_id="${ID:-}"
        os_like="${ID_LIKE:-}"
        OS_LABEL="${PRETTY_NAME:-Linux}"
    fi

    case "$os_id" in
        ubuntu|debian)
            PLATFORM="deb"
            ;;
        ol|oraclelinux|rhel|rocky|almalinux|centos|fedora)
            PLATFORM="rpm"
            ;;
        *)
            case "$os_like" in
                *debian*|*ubuntu*)
                    PLATFORM="deb"
                    ;;
                *rhel*|*fedora*|*centos*)
                    PLATFORM="rpm"
                    ;;
            esac
            ;;
    esac

    if [[ "$PLATFORM" == "unknown" ]]; then
        if command -v apt-get >/dev/null 2>&1; then
            PLATFORM="deb"
        elif command -v dnf >/dev/null 2>&1; then
            PLATFORM="rpm"
        fi
    fi
}

package_install_hint() {
    local deb_packages="$1"
    local rpm_packages="$2"

    case "$PLATFORM" in
        deb)
            printf 'sudo apt-get install -y %s' "$deb_packages"
            ;;
        rpm)
            printf 'sudo dnf install -y %s' "$rpm_packages"
            ;;
        *)
            printf 'instale os pacotes necessários para %s e execute novamente: make setup' "$OS_LABEL"
            ;;
    esac
}

python_install_hint() {
    case "$PLATFORM" in
        deb)
            printf 'sudo apt-get install -y python3 python3-venv'
            ;;
        rpm)
            printf 'sudo dnf install -y python3.12 python3.12-pip || sudo dnf install -y python3.11 python3.11-pip'
            ;;
        *)
            printf 'instale Python 3.10 ou superior com suporte a venv e execute novamente: make setup'
            ;;
    esac
}

find_supported_python() {
    local candidate

    for candidate in python3.12 python3.11 python3.10 python3; do
        if ! command -v "$candidate" >/dev/null 2>&1; then
            continue
        fi

        if "$candidate" -c 'import sys; raise SystemExit(0 if sys.version_info >= (3, 10) else 1)' >/dev/null 2>&1; then
            printf '%s\n' "$candidate"
            return 0
        fi
    done

    return 1
}

show_noexec_hint() {
    if command -v findmnt >/dev/null 2>&1; then
        local mount_options
        mount_options="$(findmnt -no OPTIONS --target "$ROOT" 2>/dev/null || true)"
        if [[ ",$mount_options," == *,noexec,* ]]; then
            printf '\n[DIAGNÓSTICO] O filesystem do projeto está montado com noexec.\n' >&2
            printf 'Confirme com:\n\n  findmnt -no OPTIONS --target "%s"\n' "$ROOT" >&2
            printf '\nMova o projeto para um filesystem que permita execução ou ajuste a montagem conforme a política do host.\n' >&2
        fi
    fi
}

require_command() {
    local command_name="$1"
    local message="$2"
    local repair_command="$3"

    if ! command -v "$command_name" >/dev/null 2>&1; then
        error "$message"
        repair "$repair_command"
        exit 1
    fi
}

detect_platform
mkdir -p "$TOOLS"

PYTHON_BIN="$(find_supported_python || true)"
if [[ -z "$PYTHON_BIN" ]]; then
    error 'Python 3.10+ é necessário para Semgrep.'
    if command -v python3 >/dev/null 2>&1; then
        printf 'Python padrão encontrado, mas incompatível:\n\n  ' >&2
        python3 --version >&2 || true
    fi
    printf '\nO HECATE não altera o python3 do sistema. No Oracle Linux/RHEL-like, instale uma versão paralela suportada.\n' >&2
    repair "$(python_install_hint)"
    exit 1
fi

PYTHON_VERSION="$($PYTHON_BIN -c 'import platform; print(platform.python_version())')"
info "Python selecionado para ferramentas de segurança: $PYTHON_BIN ($PYTHON_VERSION)"

if ! "$PYTHON_BIN" -m venv --help >/dev/null 2>&1; then
    error "O módulo venv não está disponível para $PYTHON_BIN."
    repair "$(python_install_hint)"
    exit 1
fi

if [[ ! -e "$SEMGREP_PYTHON" ]]; then
    if [[ -d "$SEMGREP_DIR" ]]; then
        error 'O ambiente virtual do Semgrep está incompleto.'
        repair "rm -rf '$SEMGREP_DIR' && make setup"
        exit 1
    fi

    "$PYTHON_BIN" -m venv "$SEMGREP_DIR"
fi

if [[ ! -x "$SEMGREP_PYTHON" ]]; then
    error "Python do ambiente Semgrep está sem permissão de execução: $SEMGREP_PYTHON"
    show_noexec_hint
    repair "chmod +x '$SEMGREP_PYTHON'"
    exit 1
fi

if [[ ! -e "$SEMGREP_BIN" ]]; then
    "$SEMGREP_PYTHON" -m pip install --disable-pip-version-check "semgrep==${SEMGREP_VERSION}"
fi

if [[ ! -x "$SEMGREP_BIN" ]]; then
    error "Semgrep está instalado, mas sem permissão de execução: $SEMGREP_BIN"
    show_noexec_hint
    repair "chmod +x '$SEMGREP_BIN'"
    exit 1
fi

SEMGREP_VERSION_OUTPUT=""
if ! SEMGREP_VERSION_OUTPUT="$($SEMGREP_BIN --version 2>&1)"; then
    error 'Semgrep foi encontrado, mas não consegue iniciar corretamente.'
    printf '\nSaída do teste:\n%s\n' "$SEMGREP_VERSION_OUTPUT" >&2
    show_noexec_hint
    printf '\nSe o erro for de permissão em binário interno, execute:\n\n' >&2
    printf "  find '%s' -type f \\( -name 'semgrep-core' -o -name 'osemgrep' \\) -exec chmod +x {} +\n" "$SEMGREP_DIR" >&2
    printf '\nValide em seguida:\n\n  %s --version\n' "$SEMGREP_BIN" >&2
    printf '\nSe a instalação estiver inconsistente, recrie-a:\n\n  rm -rf %q\n  make setup\n' "$SEMGREP_DIR" >&2
    exit 1
fi

if [[ "$SEMGREP_VERSION_OUTPUT" != *"$SEMGREP_VERSION"* ]]; then
    error "Versão inesperada do Semgrep. Esperada: $SEMGREP_VERSION. Encontrada: $SEMGREP_VERSION_OUTPUT"
    repair "rm -rf '$SEMGREP_DIR' && make setup"
    exit 1
fi
printf '[OK] Semgrep %s instalado, executável e funcional\n' "$SEMGREP_VERSION"

require_command java 'Java 17+ é necessário para OWASP ZAP.' "$(package_install_hint 'openjdk-17-jre' 'java-17-openjdk-headless')"
JAVA_VERSION="$(java -version 2>&1 | head -n 1 | sed -E 's/.*version "([0-9]+).*/\1/')"
if ! [[ "$JAVA_VERSION" =~ ^[0-9]+$ ]] || (( JAVA_VERSION < 17 )); then
    error 'Java 17+ é necessário para OWASP ZAP.'
    printf 'Versão atual:\n\n' >&2
    java -version >&2 || true
    repair "$(package_install_hint 'openjdk-17-jre' 'java-17-openjdk-headless')"
    exit 1
fi

if [[ ! -e "$ZAP_BIN" ]]; then
    require_command curl 'curl é necessário para instalar OWASP ZAP.' "$(package_install_hint 'curl' 'curl')"
    require_command sha256sum 'sha256sum é necessário para validar OWASP ZAP.' "$(package_install_hint 'coreutils' 'coreutils')"
    tmp="$(mktemp -d)"
    trap 'rm -rf "$tmp"' EXIT
    curl -fL "$ZAP_URL" -o "$tmp/$ZAP_ARCHIVE"
    printf '%s  %s\n' "$ZAP_SHA256" "$tmp/$ZAP_ARCHIVE" | sha256sum -c -
    tar -xzf "$tmp/$ZAP_ARCHIVE" -C "$tmp"
    rm -rf "$TOOLS/zap"
    mv "$tmp/ZAP_${ZAP_VERSION}" "$TOOLS/zap"
fi

if [[ ! -x "$ZAP_BIN" ]]; then
    error "OWASP ZAP está instalado, mas sem permissão de execução: $ZAP_BIN"
    show_noexec_hint
    repair "chmod +x '$ZAP_BIN'"
    exit 1
fi

ZAP_VERSION_OUTPUT=""
if ! ZAP_VERSION_OUTPUT="$($ZAP_BIN -version 2>&1)"; then
    error 'OWASP ZAP foi encontrado, mas não consegue iniciar corretamente.'
    printf '\nSaída do teste:\n%s\n' "$ZAP_VERSION_OUTPUT" >&2
    show_noexec_hint
    printf '\nTeste manual:\n\n  %s -version\n' "$ZAP_BIN" >&2
    printf '\nSe a instalação estiver inconsistente, recrie-a:\n\n  rm -rf %q\n  make setup\n' "$TOOLS/zap" >&2
    exit 1
fi

if [[ "$ZAP_VERSION_OUTPUT" != *"$ZAP_VERSION"* ]]; then
    error "Versão inesperada do OWASP ZAP. Esperada: $ZAP_VERSION. Encontrada: $ZAP_VERSION_OUTPUT"
    repair "rm -rf '$TOOLS/zap' && make setup"
    exit 1
fi
printf '[OK] OWASP ZAP %s instalado, executável e funcional\n' "$ZAP_VERSION"
