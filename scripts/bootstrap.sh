#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

line() {
    printf '%s\n' '=================================================='
}

ok() {
    printf '[OK] %s\n' "$1"
}

info() {
    printf '[INFO] %s\n' "$1"
}

error() {
    printf '[ERRO] %s\n' "$1" >&2
}

repair() {
    printf '\nPara corrigir:\n\n  %s\n' "$1" >&2
}

show_noexec_hint() {
    if command -v findmnt >/dev/null 2>&1; then
        local mount_options
        mount_options="$(findmnt -no OPTIONS --target "$PROJECT_ROOT" 2>/dev/null || true)"
        if [[ ",$mount_options," == *,noexec,* ]]; then
            printf '\n[DIAGNÓSTICO] O filesystem do projeto está montado com noexec.\n' >&2
            printf 'Confirme com:\n\n  findmnt -no OPTIONS --target "%s"\n' "$PROJECT_ROOT" >&2
            printf '\nMova o projeto para um filesystem que permita execução ou ajuste a montagem conforme a política do host.\n' >&2
        fi
    fi
}

require_command() {
    local command_name="$1"
    local label="$2"
    shift 2

    if ! command -v "$command_name" >/dev/null 2>&1; then
        error "$label não encontrado."
        repair "$*"
        exit 1
    fi

    if ! "$command_name" --version >/dev/null 2>&1; then
        error "$label foi encontrado, mas não pôde ser executado."
        show_noexec_hint
        repair "$*"
        exit 1
    fi

    ok "$label encontrado e funcional"
}

require_lefthook() {
    if command -v lefthook >/dev/null 2>&1 && lefthook version >/dev/null 2>&1; then
        ok 'Lefthook encontrado e funcional'
        return
    fi

    error 'Lefthook não encontrado ou não executável.'
    printf '\nUbuntu/Kubuntu/Debian:\n\n' >&2
    printf "  curl -1sLf 'https://dl.cloudsmith.io/public/evilmartians/lefthook/setup.deb.sh' | sudo -E bash\n" >&2
    printf '  sudo apt install lefthook\n\n' >&2
    printf 'Depois confirme e repita o setup:\n\n' >&2
    printf '  lefthook version\n' >&2
    printf '  make setup\n' >&2
    exit 1
}

require_file() {
    local path="$1"
    local label="$2"

    if [[ ! -f "$path" ]]; then
        error "$label não encontrado: $path"
        exit 1
    fi

    if [[ ! -r "$path" ]]; then
        error "$label existe, mas não está legível: $path"
        repair "chmod u+r '$path'"
        exit 1
    fi

    ok "$label encontrado e legível"
}

verify_php_tool() {
    local executable="$1"
    local label="$2"
    local path="vendor/bin/$executable"

    if [[ ! -e "$path" ]]; then
        error "$label não encontrado: $path"
        repair "rm -rf vendor && composer install --no-interaction --prefer-dist"
        exit 1
    fi

    if [[ ! -x "$path" ]]; then
        error "$label está instalado, mas sem permissão de execução: $path"
        show_noexec_hint
        repair "chmod +x '$path'"
        exit 1
    fi

    if ! "$path" --version >/dev/null 2>&1; then
        error "$label está instalado, mas falhou no teste de execução: $path --version"
        show_noexec_hint
        printf '\nTeste manual:\n\n  %s --version\n' "$path" >&2
        repair "rm -rf vendor && composer install --no-interaction --prefer-dist"
        exit 1
    fi

    ok "$label instalado, executável e funcional"
}

run_qa_step() {
    local label="$1"
    local composer_script="$2"

    printf '\n[CHECK] %s\n' "$label"
    if composer "$composer_script"; then
        ok "$label"
    else
        error "$label encontrou problemas."
        printf '\nExecute novamente para diagnóstico isolado:\n\n  composer %s\n' "$composer_script" >&2
        exit 1
    fi
}

line
printf ' HECATE Yii3 - Preparação do ambiente de desenvolvimento\n'
line
printf '\n'

require_command php PHP 'sudo apt install php-cli'
require_command composer Composer 'instale o Composer conforme o procedimento oficial da distribuição'
require_command git Git 'sudo apt install git'
require_lefthook

require_file composer.json 'composer.json'
require_file composer.lock 'composer.lock'

printf '\n'
info 'Instalando dependências a partir do composer.lock...'
composer install --no-interaction --prefer-dist
ok 'Dependências instaladas de forma reproduzível'

printf '\n'
info 'Validando composer.json...'
composer validate --no-interaction
ok 'Composer válido'

printf '\n'
info 'Validando ferramentas PHP de qualidade...'
verify_php_tool ecs ECS
verify_php_tool rector Rector
verify_php_tool phpstan PHPStan
verify_php_tool psalm Psalm
verify_php_tool phpunit PHPUnit

printf '\n'
info 'Validando arquivos de configuração das ferramentas...'
require_file ecs.php 'Configuração do ECS'
require_file rector.php 'Configuração do Rector'
require_file phpstan.neon 'Configuração do PHPStan'
require_file psalm.xml 'Configuração do Psalm'
require_file phpunit.xml 'Configuração do PHPUnit'
require_file security/semgrep.yml 'Configuração do Semgrep'
require_file lefthook.yml 'Configuração do Lefthook'

printf '\n'
info 'Configurando e validando ferramentas nativas de segurança em .tools/...'
bash scripts/install-security-tools.sh
ok 'Semgrep CE e OWASP ZAP configurados e funcionais sem Docker'

printf '\n'
info 'Instalando hooks Git do Lefthook...'
lefthook install
lefthook validate
ok 'Hooks Git instalados e configuração validada'

printf '\n'
info 'Executando verificações de qualidade e segurança...'
run_qa_step 'ECS / PSR-12' lint
run_qa_step 'Rector dry-run' rector
run_qa_step 'PHPStan' stan
run_qa_step 'Psalm' psalm
run_qa_step 'PHPUnit' test
run_qa_step 'SCA + Psalm Taint + Semgrep CE' security

printf '\n'
line
printf ' Ambiente HECATE Yii3 pronto para desenvolvimento.\n'
line
printf '\nComandos disponíveis:\n\n'
printf '  composer fix\n'
printf '  composer qa\n'
printf '  composer security\n'
printf '  composer check\n'
printf '  composer psalm:taint\n'
printf '  composer security:dependencies\n'
printf '  composer security:semgrep\n'
printf '  HECATE_ZAP_TARGET=http://127.0.0.1:8080 bash scripts/zap-scan.sh\n'
printf '  composer serve\n'
printf '  lefthook run pre-commit\n'
printf '  lefthook run pre-push\n'
