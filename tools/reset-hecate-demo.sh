#!/usr/bin/env bash
set -euo pipefail

DB_HOST="${HECATE_DB_HOST:-127.0.0.1}"
DB_PORT="${HECATE_DB_PORT:-5432}"
DB_USER="${HECATE_DB_USER:-hecate}"
DB_PASSWORD="${HECATE_DB_PASSWORD:-}"
DB_ADMIN_USER="${HECATE_DB_ADMIN_USER:-postgres}"
DB_ADMIN_PASSWORD="${HECATE_DB_ADMIN_PASSWORD:-}"
DCTIM_DB="${HECATE_DEMO_DCTIM_DB_NAME:-hecate_demo_dctim}"
CTIM_DB="${HECATE_DEMO_CTIM_DB_NAME:-hecate_demo_ctim}"

run_admin_dropdb() {
    local database="$1"

    if [[ "$DB_ADMIN_USER" == "postgres" && ("$DB_HOST" == "127.0.0.1" || "$DB_HOST" == "localhost") ]] && command -v sudo >/dev/null 2>&1; then
        sudo -u postgres dropdb --if-exists --port="$DB_PORT" "$database"
        return
    fi

    PGPASSWORD="$DB_ADMIN_PASSWORD" dropdb \
        --if-exists \
        --host="$DB_HOST" \
        --port="$DB_PORT" \
        --username="$DB_ADMIN_USER" \
        "$database"
}

run_admin_createdb() {
    local database="$1"

    if [[ "$DB_ADMIN_USER" == "postgres" && ("$DB_HOST" == "127.0.0.1" || "$DB_HOST" == "localhost") ]] && command -v sudo >/dev/null 2>&1; then
        sudo -u postgres createdb --port="$DB_PORT" --owner="$DB_USER" "$database"
        return
    fi

    PGPASSWORD="$DB_ADMIN_PASSWORD" createdb \
        --host="$DB_HOST" \
        --port="$DB_PORT" \
        --username="$DB_ADMIN_USER" \
        --owner="$DB_USER" \
        "$database"
}

reset_database() {
    local scenario="$1"
    local database="$2"
    local seed_file="$3"

    run_admin_dropdb "$database"
    run_admin_createdb "$database"

    HECATE_EDITION=demo \
    HECATE_DEMO_SCENARIO="$scenario" \
    HECATE_DEMO_DCTIM_DB_NAME="$DCTIM_DB" \
    HECATE_DEMO_CTIM_DB_NAME="$CTIM_DB" \
    HECATE_DB_HOST="$DB_HOST" \
    HECATE_DB_PORT="$DB_PORT" \
    HECATE_DB_USER="$DB_USER" \
    HECATE_DB_PASSWORD="$DB_PASSWORD" \
    php ./yii migrate:up

    PGPASSWORD="$DB_PASSWORD" psql \
        --host="$DB_HOST" \
        --port="$DB_PORT" \
        --username="$DB_USER" \
        --dbname="$database" \
        --set=ON_ERROR_STOP=1 \
        --file="$seed_file"
}

reset_database dctim "$DCTIM_DB" demo/seed-dctim.sql
reset_database ctim "$CTIM_DB" demo/seed-ctim.sql

echo "HECATE Demo recriado: $DCTIM_DB e $CTIM_DB"
