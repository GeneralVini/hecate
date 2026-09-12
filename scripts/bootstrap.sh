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

require_command() {
    local command_name="$1"
    local label="$2"

    if ! command -v "$command_name" >/dev/null 2>&1; then
        error "$label não encontrado."
        printf 'Instale %s e execute novamente:\nmake setup\n' "$label" >&2
        exit 1
    fi

    ok "$label encontrado"
}

require_lefthook() {
    if command -v lefthook >/dev/null 2>&1; then
        ok 'Lefthook encontrado'
        return
    fi

    error 'Lefthook não encontrado.'
    printf '\nO Lefthook é obrigatório para os hooks Git locais.\n' >&2
    printf 'Ubuntu/Kubuntu/Debian:\n\n' >&2
    printf "  curl -1sLf 'https://dl.cloudsmith.io/public/evilmartians/lefthook/setup.deb.sh' | sudo -E bash\n" >&2
    printf '  sudo apt install lefthook\n\n' >&2
    printf 'Depois confirme e repita o setup:\n\n' >&2
    printf '  lefthook version\n' >&2
    printf '  make setup\n' >&2
    exit 1
}

run_qa_step() {
    local label="$1"
    local composer_script="$2"

    printf '\n[CHECK] %s\n' "$label"
    if composer "$composer_script"; then
        ok "$label"
    else
        error "$label encontrou problemas."
        printf '\nExecute:\n\ncomposer %s\n' "$composer_script" >&2
        exit 1
    fi
}

line
printf ' HECATE Yii3 - Preparação do ambiente de desenvolvimento\n'
line
printf '\n'

require_command php PHP
require_command composer Composer
require_command git Git
require_lefthook

if [[ ! -f composer.json ]]; then
    error 'composer.json não encontrado na raiz do projeto.'
    exit 1
fi
ok 'composer.json encontrado'

if [[ ! -f composer.lock ]]; then
    error 'composer.lock não encontrado. O lockfile é obrigatório e deve estar versionado.'
    exit 1
fi
ok 'composer.lock encontrado'

printf '\n'
info 'Instalando dependências a partir do composer.lock...'
composer install --no-interaction --prefer-dist
ok 'Dependências instaladas de forma reproduzível'

printf '\n'
info 'Validando composer.json...'
composer validate --no-interaction
ok 'Composer válido'

for executable in ecs rector phpstan psalm phpunit; do
    if [[ ! -x "vendor/bin/$executable" ]]; then
        error "Executável esperado não encontrado: vendor/bin/$executable"
        exit 1
    fi
done
ok 'Ferramentas PHP de qualidade instaladas em vendor/bin'

printf '\n'
info 'Configurando ferramentas nativas de segurança em .tools/...'
bash scripts/install-security-tools.sh
ok 'Semgrep CE e OWASP ZAP configurados sem Docker'

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
