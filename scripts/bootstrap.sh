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

run_qa_step() {
    local label="$1"
    local composer_script="$2"

    printf '\n[QA] %s\n' "$label"
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

if [[ ! -f composer.json ]]; then
    error 'composer.json não encontrado na raiz do projeto.'
    exit 1
fi
ok 'composer.json encontrado'

printf '\n'
if [[ -f composer.lock ]]; then
    info 'Instalando dependências a partir do composer.lock...'
    composer install --no-interaction --prefer-dist
    ok 'Dependências instaladas de forma reproduzível'
else
    info 'composer.lock ainda não existe nesta branch Yii3.'
    info 'Resolvendo a nova árvore de dependências e gerando o lockfile inicial...'
    composer update --no-interaction --prefer-dist
    ok 'composer.lock inicial gerado; ele deve ser versionado após a validação.'
fi

printf '\n'
info 'Validando composer.json...'
composer validate --no-interaction
ok 'Composer válido'

for executable in phpcs phpstan psalm phpunit; do
    if [[ ! -x "vendor/bin/$executable" ]]; then
        error "Executável esperado não encontrado: vendor/bin/$executable"
        exit 1
    fi
done
ok 'Ferramentas de qualidade instaladas em vendor/bin'

printf '\n'
info 'Executando verificações de qualidade...'
run_qa_step 'PHPCS / PSR-12' lint
run_qa_step 'PHPStan' stan
run_qa_step 'Psalm' psalm
run_qa_step 'PHPUnit' test

printf '\n'
line
printf ' Ambiente HECATE Yii3 pronto para desenvolvimento.\n'
line
printf '\nComandos disponíveis:\n\n'
printf '  composer lint\n'
printf '  composer stan\n'
printf '  composer psalm\n'
printf '  composer test\n'
printf '  composer qa\n'
printf '  composer serve\n'
