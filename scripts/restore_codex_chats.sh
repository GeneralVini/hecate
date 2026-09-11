#!/usr/bin/env bash
#
# restore_codex_chats.sh
#
# Restaura backups criados por backup_codex_chats.sh.
#
# Características:
#   - Suporta backups multiusuário:
#       codex-root/
#       codex-dexter/
#       codex-<usuario>/
#
#   - Suporta backup custom:
#       codex-custom/
#
#   - Suporta formato legado:
#       codex/
#
#   - Detecta e materializa arquivos Git LFS.
#   - Valida checksum SHA-256 quando disponível.
#   - Valida caminhos internos do TAR antes da extração.
#   - Faz backup preventivo do estado atual antes de restaurar.
#   - Remove corretamente arquivos antigos, inclusive padrões com glob.
#   - Restaura ownership correto para cada usuário.
#   - Não restaura credenciais por padrão.
#
# Uso:
#   scripts/restore_codex_chats.sh BACKUP.tar.gz
#   scripts/restore_codex_chats.sh BACKUP.tar.gz --force
#   scripts/restore_codex_chats.sh BACKUP.tar.gz --with-secrets
#
# Autor:
#   CC (EN) HONORATO
#

set -Eeuo pipefail


###############################################################################
# CONFIGURAÇÃO
###############################################################################

CODEX_HOME="${CODEX_HOME:-$HOME/.codex}"

ARCHIVE="${1:-}"

FORCE=0
WITH_SECRETS=0

STAMP="$(date +%Y%m%d-%H%M%S)"


###############################################################################
# FUNÇÕES BÁSICAS
###############################################################################

usage() {
  cat <<'USAGE'
Uso:

  scripts/restore_codex_chats.sh ARQUIVO_BACKUP.tar.gz [opções]


Opções:

  --force
      Não solicita confirmação interativa.

  --with-secrets
      Também restaura:
          auth.json
          config.toml
          installation_id
          AGENTS.md
          .personality_migration
          models_cache.json
          cache/
          plugins/
          skills/
          vendor_imports/

  -h, --help
      Exibe esta ajuda.


Variáveis:

  CODEX_HOME
      Destino utilizado para backups legados ou codex-custom/.

      Padrão:
          ~/.codex


Antes de restaurar:

  O conteúdo atual é preservado em:

      ~/.codex.pre-restore-YYYYMMDD-HHMMSS

  ou, em restauração multiusuário:

      /home/usuario/.codex.pre-restore-YYYYMMDD-HHMMSS


Git LFS:

  Se o arquivo informado for apenas um ponteiro Git LFS, o script tenta
  executar automaticamente:

      git lfs pull

  para materializar o arquivo antes da restauração.


Checksum:

  Quando existir:

      BACKUP.tar.gz.sha256

  ele será validado automaticamente antes da restauração.

USAGE
}


die() {
  echo "ERRO: $*" >&2
  exit 1
}


warn() {
  echo "AVISO: $*" >&2
}


require_command() {
  local cmd="$1"

  command -v "$cmd" >/dev/null 2>&1 ||
    die "comando obrigatório não encontrado: $cmd"
}


###############################################################################
# PARÂMETROS
###############################################################################

if [[ "$ARCHIVE" == "-h" || "$ARCHIVE" == "--help" ]]; then
  usage
  exit 0
fi


if [[ -z "$ARCHIVE" ]]; then
  usage
  exit 1
fi


shift || true


while [[ $# -gt 0 ]]; do

  case "$1" in

    --force)
      FORCE=1
      shift
      ;;

    --with-secrets)
      WITH_SECRETS=1
      shift
      ;;

    -h|--help)
      usage
      exit 0
      ;;

    *)
      die "opção desconhecida: $1"
      ;;

  esac

done


###############################################################################
# DEPENDÊNCIAS
###############################################################################

require_command tar
require_command sha256sum
require_command grep
require_command awk
require_command sed
require_command sort
require_command realpath
require_command cp
require_command git


[[ -f "$ARCHIVE" ]] ||
  die "backup não encontrado: $ARCHIVE"


ARCHIVE="$(realpath "$ARCHIVE")"


###############################################################################
# GIT LFS
###############################################################################

is_lfs_pointer() {

  LC_ALL=C \
    head -n 1 "$1" 2>/dev/null |
    grep -Fqx \
      'version https://git-lfs.github.com/spec/v1'

}


materialize_lfs_archive() {

  local archive="$1"

  local repo_root
  local archive_abs
  local archive_rel


  if ! is_lfs_pointer "$archive"; then
    return 0
  fi


  echo
  echo "Git LFS:"
  echo "  ponteiro LFS detectado."


  if ! git lfs version >/dev/null 2>&1; then

    die \
      "$archive é um ponteiro Git LFS, mas git-lfs não está instalado."

  fi


  repo_root="$(
    git \
      -C "$(dirname "$archive")" \
      rev-parse \
      --show-toplevel \
      2>/dev/null ||
      true
  )"


  if [[ -z "$repo_root" ]]; then

    die \
      "$archive é um ponteiro LFS fora de um repositório Git."

  fi


  repo_root="$(realpath "$repo_root")"
  archive_abs="$(realpath "$archive")"


  case "$archive_abs" in

    "$repo_root"/*)
      archive_rel="${archive_abs#"$repo_root"/}"
      ;;

    *)
      die "não foi possível determinar o caminho Git do backup."
      ;;

  esac


  echo "  baixando objeto:"
  echo "    $archive_rel"


  git \
    -C "$repo_root" \
    lfs pull \
    --include="$archive_rel" \
    --exclude=""


  if is_lfs_pointer "$archive"; then

    die \
      "Git LFS não materializou o backup: $archive_rel"

  fi


  echo "  objeto LFS materializado."

}


materialize_lfs_archive "$ARCHIVE"


###############################################################################
# CHECKSUM SHA-256
###############################################################################

verify_checksum() {

  local archive="$1"
  local checksum="${archive}.sha256"


  if [[ ! -f "$checksum" ]]; then

    warn \
      "checksum não encontrado: $(basename "$checksum")"

    return 0

  fi


  echo
  echo "SHA-256:"
  echo "  verificando $(basename "$checksum")..."


  (
    cd "$(dirname "$archive")"

    sha256sum \
      -c \
      "$(basename "$checksum")"
  ) || {

    die \
      "falha na validação SHA-256 do backup."

  }


  echo "  checksum válido."

}


verify_checksum "$ARCHIVE"


###############################################################################
# VALIDAÇÃO DO TAR
###############################################################################

if ! tar -tzf "$ARCHIVE" >/dev/null 2>&1; then

  die \
    "arquivo inválido, corrompido ou não é um tar.gz válido: $ARCHIVE"

fi


archive_listing="$(
  tar -tzf "$ARCHIVE"
)"


validate_archive_paths() {

  local listing="$1"
  local entry


  while IFS= read -r entry; do

    [[ -n "$entry" ]] || continue


    case "$entry" in

      /*)
        die \
          "backup contém caminho absoluto inseguro: $entry"
        ;;

      ../*|*/../*|*/..)
        die \
          "backup contém path traversal inseguro: $entry"
        ;;

    esac


  done <<< "$listing"

}


validate_archive_paths "$archive_listing"


###############################################################################
# DETECÇÃO DO FORMATO
###############################################################################

users_in_archive="$(
  awk -F/ \
    '/^codex-[^/]+\// {print $1}' \
    <<< "$archive_listing" |
  sed 's/^codex-//' |
  sort -u |
  tr '\n' ' '
)"


has_legacy=0

if grep -qE '^codex/' <<< "$archive_listing"; then
  has_legacy=1
fi


if [[ -z "$users_in_archive" && "$has_legacy" -eq 0 ]]; then

  die \
    "backup não contém uma estrutura reconhecida do Codex."

fi


###############################################################################
# PADRÕES RESTAURÁVEIS
###############################################################################

patterns=(
  "sessions"
  "session_index.jsonl"

  "logs_*.sqlite"
  "logs_*.sqlite-shm"
  "logs_*.sqlite-wal"

  "state_*.sqlite"
  "state_*.sqlite-shm"
  "state_*.sqlite-wal"

  "shell_snapshots"
  "memories"
)


secret_patterns=(
  "auth.json"
  "config.toml"
  "installation_id"
  "AGENTS.md"
  ".personality_migration"
  "models_cache.json"
  "cache"
  "plugins"
  "skills"
  "vendor_imports"
)


if (( WITH_SECRETS == 1 )); then

  patterns+=(
    "${secret_patterns[@]}"
  )

fi


###############################################################################
# CONFIRMAÇÃO
###############################################################################

if (( FORCE != 1 )); then

  echo
  echo "============================================================"
  echo " RESTAURAÇÃO CODEX"
  echo "============================================================"
  echo
  echo "Backup:"
  echo "  $ARCHIVE"
  echo


  if [[ -n "$users_in_archive" ]]; then

    echo "Origens detectadas:"
    echo

    for user in $users_in_archive; do
      echo "  codex-$user"
    done

  else

    echo "Formato legado detectado."
    echo
    echo "Destino:"
    echo "  $CODEX_HOME"

  fi


  if (( WITH_SECRETS == 1 )); then

    echo
    echo "ATENÇÃO:"
    echo "  credenciais, tokens e configurações serão restaurados."

  else

    echo
    echo "Credenciais:"
    echo "  NÃO serão restauradas."

  fi


  echo

  read -r -p \
    "Continuar com a restauração? [s/N] " \
    answer


  case "$answer" in

    s|S|sim|SIM|Sim)
      ;;

    *)
      echo "Cancelado."
      exit 0
      ;;

  esac

fi


###############################################################################
# EXTRAÇÃO TEMPORÁRIA
###############################################################################

tmpdir="$(mktemp -d)"


cleanup() {
  rm -rf "$tmpdir"
}


trap cleanup EXIT


echo
echo "Extraindo backup para área temporária..."


tar \
  -xzf "$ARCHIVE" \
  -C "$tmpdir"


###############################################################################
# FUNÇÕES DE ARQUIVOS
###############################################################################

backup_current_to() {

  local target_root="$1"
  shift

  local pre_restore_dir="$1"
  shift

  local rel
  local path
  local item


  [[ -d "$target_root" ]] ||
    return 0


  for rel in "$@"; do

    while IFS= read -r path; do

      [[ -n "$path" ]] || continue


      item="${path#$target_root/}"


      mkdir -p \
        "$pre_restore_dir/$(dirname "$item")"


      cp -a \
        -- \
        "$path" \
        "$pre_restore_dir/$item"


    done < <(
      compgen \
        -G "$target_root/$rel" \
        2>/dev/null ||
      true
    )

  done

}


remove_matching() {

  local target_root="$1"
  shift

  local rel
  local path


  for rel in "$@"; do

    while IFS= read -r path; do

      [[ -n "$path" ]] || continue

      rm -rf -- "$path"

    done < <(
      compgen \
        -G "$target_root/$rel" \
        2>/dev/null ||
      true
    )

  done

}


copy_from_restore_root() {

  local restore_root="$1"
  shift

  local target_root="$1"
  shift

  local rel
  local path
  local item


  for rel in "$@"; do

    while IFS= read -r path; do

      [[ -n "$path" ]] || continue


      item="${path#$restore_root/}"


      mkdir -p \
        "$target_root/$(dirname "$item")"


      cp -a \
        -- \
        "$path" \
        "$target_root/$item"


    done < <(
      compgen \
        -G "$restore_root/$rel" \
        2>/dev/null ||
      true
    )

  done

}


restore_one_target() {

  local label="$1"
  local restore_root="$2"
  local target_root="$3"
  local owner_user="${4:-}"


  local pre_restore


  [[ -d "$restore_root" ]] || {

    warn \
      "origem de restauração não encontrada: $restore_root"

    return 0

  }


  echo
  echo "------------------------------------------------------------"
  echo "Restaurando:"
  echo "  origem : $label"
  echo "  destino: $target_root"
  echo "------------------------------------------------------------"


  mkdir -p "$target_root"


  pre_restore="${target_root}.pre-restore-$STAMP"

  mkdir -p "$pre_restore"


  echo
  echo "Backup preventivo:"
  echo "  $pre_restore"


  backup_current_to \
    "$target_root" \
    "$pre_restore" \
    "${patterns[@]}"


  echo
  echo "Removendo artefatos antigos..."


  remove_matching \
    "$target_root" \
    "${patterns[@]}"


  echo
  echo "Copiando artefatos restaurados..."


  copy_from_restore_root \
    "$restore_root" \
    "$target_root" \
    "${patterns[@]}"


  #
  # Segurança mínima de permissões.
  #
  chmod -R \
    go-rwx \
    "$target_root" \
    2>/dev/null ||
    true


  #
  # Corrige ownership quando conhecemos o usuário.
  #
  if [[ -n "$owner_user" ]]; then

    if id "$owner_user" >/dev/null 2>&1; then

      local owner_group

      owner_group="$(
        id -gn "$owner_user"
      )"


      chown -R \
        "$owner_user:$owner_group" \
        "$target_root" \
        2>/dev/null ||
        warn \
          "não foi possível ajustar ownership de $target_root"

    fi

  fi


  echo
  echo "OK:"
  echo "  restaurado em $target_root"
  echo
  echo "Estado anterior:"
  echo "  $pre_restore"

}


###############################################################################
# RESTAURAÇÃO MULTIUSUÁRIO / CUSTOM
###############################################################################

if [[ -n "$users_in_archive" ]]; then

  for user in $users_in_archive; do

    restore_root="$tmpdir/codex-$user"


    if [[ "$user" == "custom" ]]; then

      restore_one_target \
        "codex-custom" \
        "$restore_root" \
        "$CODEX_HOME" \
        ""

      continue

    fi


    home_dir="$(
      getent passwd "$user" 2>/dev/null |
      cut -d: -f6 ||
      true
    )"


    if [[ -z "$home_dir" ]]; then

      if [[ "$user" == "root" ]]; then

        home_dir="/root"

      else

        warn \
          "usuário '$user' não existe neste sistema; restauração ignorada."

        continue

      fi

    fi


    CODEX_HOME_USER="$home_dir/.codex"


    restore_one_target \
      "codex-$user" \
      "$restore_root" \
      "$CODEX_HOME_USER" \
      "$user"

  done


###############################################################################
# RESTAURAÇÃO LEGADA
###############################################################################

else

  restore_root="$tmpdir"


  if [[ -d "$tmpdir/codex" ]]; then
    restore_root="$tmpdir/codex"
  fi


  restore_one_target \
    "codex legado" \
    "$restore_root" \
    "$CODEX_HOME" \
    "$(id -un 2>/dev/null || true)"

fi


###############################################################################
# AVISO SOBRE SECRETS NÃO RESTAURADOS
###############################################################################

if (( WITH_SECRETS == 0 )); then

  secrets_present=0


  for secret in "${secret_patterns[@]}"; do

    if grep -qE \
      "(^|/)${secret//./\\.}(/|$)" \
      <<< "$archive_listing"; then

      secrets_present=1
      break

    fi

  done


  if (( secrets_present == 1 )); then

    echo
    echo "Obs.:"
    echo "  o backup contém credenciais/configurações adicionais,"
    echo "  mas elas NÃO foram restauradas."
    echo
    echo "Para restaurá-las:"
    echo
    echo "  $0 \"$ARCHIVE\" --with-secrets"

  fi

fi


###############################################################################
# RESULTADO
###############################################################################

echo
echo "================================================================"
echo " RESTAURAÇÃO CODEX CONCLUÍDA"
echo "================================================================"
echo
echo "Backup utilizado:"
echo "  $ARCHIVE"
echo

if (( WITH_SECRETS == 1 )); then

  echo "Modo:"
  echo "  chats + configurações + credenciais"

else

  echo "Modo:"
  echo "  chats/sessões sem credenciais"

fi

echo
