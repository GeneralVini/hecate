#!/usr/bin/env bash
#
# backup_codex_chats.sh
#
# Backup dos dados de sessão/histórico do OpenAI Codex CLI.
#
# Características:
#   - Suporta múltiplos usuários, por padrão: root e dexter.
#   - Permite apontar diretamente para um CODEX_HOME.
#   - Exclui credenciais por padrão.
#   - Opcionalmente inclui credenciais/configurações com --include-secrets.
#   - Gera arquivo .tar.gz.
#   - Gera checksum SHA-256.
#   - Usa Git normal para arquivos pequenos.
#   - Configura Git LFS automaticamente para backups >= 95 MiB.
#   - Mantém somente os N backups mais recentes no diretório local.
#
# IMPORTANTE:
#   A retenção remove arquivos antigos da árvore de trabalho, mas NÃO elimina
#   objetos já existentes no histórico Git/LFS. A liberação real de espaço
#   histórico exige procedimentos específicos de limpeza/rewrite do Git/LFS.
#
# Uso:
#   ./scripts/backup_codex_chats.sh
#   ./scripts/backup_codex_chats.sh --include-secrets
#
# Autor:
#   CC (EN) HONORATO
#

set -Eeuo pipefail


###############################################################################
# CONFIGURAÇÃO
###############################################################################

CODEX_USERS="${CODEX_USERS:-root dexter}"

BACKUP_DIR="${CODEX_BACKUP_DIR:-$PWD/backups/codex-chats}"

# 95 MiB.
# Mantém margem abaixo do limite de 100 MiB do GitHub.
LFS_THRESHOLD_BYTES="${CODEX_LFS_THRESHOLD_BYTES:-99614720}"

# Quantidade de backups de cada tipo mantidos localmente.
BACKUP_RETENTION="${CODEX_BACKUP_RETENTION:-5}"

INCLUDE_SECRETS=0

STAMP="$(date +%Y%m%d-%H%M%S)"


###############################################################################
# FUNÇÕES AUXILIARES
###############################################################################

usage() {
  cat <<'USAGE'
Uso:

  scripts/backup_codex_chats.sh [opções]

Opções:

  --include-secrets
      Inclui credenciais, configuração, plugins, cache e outros dados
      necessários para uma recuperação mais completa do ambiente Codex.

  -h, --help
      Exibe esta ajuda.


Variáveis de ambiente:

  CODEX_USERS
      Usuários que terão ~/.codex incluído.

      Padrão:
          root dexter


  CODEX_HOME
      Se definido, utiliza exclusivamente este diretório como origem,
      ignorando CODEX_USERS.

      Exemplo:
          CODEX_HOME=/home/dexter/.codex \
              scripts/backup_codex_chats.sh


  CODEX_BACKUP_DIR
      Diretório onde os backups serão criados.

      Padrão:
          ./backups/codex-chats


  CODEX_LFS_THRESHOLD_BYTES
      A partir deste tamanho o arquivo será configurado para Git LFS.

      Padrão:
          99614720 bytes
          95 MiB


  CODEX_BACKUP_RETENTION
      Quantidade máxima de backups de cada tipo mantidos no diretório.

      Padrão:
          5


Backup padrão:

  Inclui:
      sessions/
      session_index.jsonl
      logs_*.sqlite*
      state_*.sqlite*
      shell_snapshots/
      memories/

  Exclui credenciais.


Backup com --include-secrets:

  Também inclui, quando existentes:
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


Git:

  Arquivo < 95 MiB:
      Git normal.

  Arquivo >= 95 MiB:
      Git LFS.


Exemplo:

  scripts/backup_codex_chats.sh

ou:

  scripts/backup_codex_chats.sh --include-secrets

USAGE
}


die() {
  echo "ERRO: $*" >&2
  exit 1
}


warn() {
  echo "AVISO: $*" >&2
}


human_size() {
  local bytes="$1"

  if command -v numfmt >/dev/null 2>&1; then
    numfmt \
      --to=iec-i \
      --suffix=B \
      "$bytes"
  else
    echo "${bytes} bytes"
  fi
}


require_command() {
  local command_name="$1"

  command -v "$command_name" >/dev/null 2>&1 ||
    die "comando obrigatório não encontrado: $command_name"
}


###############################################################################
# PARÂMETROS
###############################################################################

while [[ $# -gt 0 ]]; do

  case "$1" in

    -h|--help)
      usage
      exit 0
      ;;

    --include-secrets)
      INCLUDE_SECRETS=1
      shift
      ;;

    --*)
      die "opção desconhecida: $1"
      ;;

    *)
      die "argumento inválido: $1"
      ;;

  esac

done


###############################################################################
# VALIDAÇÕES
###############################################################################

require_command tar
require_command sha256sum
require_command stat
require_command git
require_command realpath
require_command getent


[[ "$LFS_THRESHOLD_BYTES" =~ ^[0-9]+$ ]] ||
  die "CODEX_LFS_THRESHOLD_BYTES deve ser um número inteiro."

(( LFS_THRESHOLD_BYTES > 0 )) ||
  die "CODEX_LFS_THRESHOLD_BYTES deve ser maior que zero."


[[ "$BACKUP_RETENTION" =~ ^[0-9]+$ ]] ||
  die "CODEX_BACKUP_RETENTION deve ser um número inteiro."

(( BACKUP_RETENTION >= 1 )) ||
  die "CODEX_BACKUP_RETENTION deve ser pelo menos 1."


###############################################################################
# NOMES DOS ARQUIVOS
###############################################################################

if (( INCLUDE_SECRETS == 1 )); then
  ARCHIVE_PREFIX="codex-chats-completo"
else
  ARCHIVE_PREFIX="codex-chats"
fi

mkdir -p "$BACKUP_DIR"

BACKUP_DIR="$(realpath "$BACKUP_DIR")"

ARCHIVE="$BACKUP_DIR/$ARCHIVE_PREFIX-$STAMP.tar.gz"
CHECKSUM="$ARCHIVE.sha256"


###############################################################################
# DIRETÓRIO TEMPORÁRIO
###############################################################################

tmpdir="$(mktemp -d)"

cleanup() {
  rm -rf "$tmpdir"
}

trap cleanup EXIT


###############################################################################
# MANIFESTO
###############################################################################

manifest="$tmpdir/MANIFEST.txt"

{
  echo "Codex Backup"
  echo
  echo "created_at=$(date -Is)"
  echo "host=$(hostname 2>/dev/null || echo unknown)"
  echo "archive=$(basename "$ARCHIVE")"
  echo "include_secrets=$INCLUDE_SECRETS"
  echo "lfs_threshold_bytes=$LFS_THRESHOLD_BYTES"
  echo "backup_retention=$BACKUP_RETENTION"
  echo
  echo "included:"
} > "$manifest"


###############################################################################
# ITENS DO CODEX
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


if (( INCLUDE_SECRETS == 1 )); then

  patterns+=(
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

fi


###############################################################################
# CONSTRUÇÃO DOS ARGUMENTOS DO TAR
###############################################################################

found_any=0

tar_args=(
  --warning=no-file-changed
  --ignore-failed-read
  -czf "$ARCHIVE"
  -C "$tmpdir"
  MANIFEST.txt
)


collect_codex_home() {

  local label="$1"
  local user_codex="$2"

  local -a user_items=()

  local pat
  local path
  local rel
  local it


  if [[ ! -d "$user_codex" ]]; then

    warn "$user_codex não existe; origem '$label' ignorada."

    return 0

  fi


  user_codex="$(realpath "$user_codex")"


  echo "Analisando:"
  echo "  $label -> $user_codex"


  for pat in "${patterns[@]}"; do

    while IFS= read -r path; do

      [[ -n "$path" ]] || continue

      rel="${path#$user_codex/}"

      user_items+=("$rel")

      echo "  $label/$rel" >> "$manifest"

    done < <(
      compgen -G "$user_codex/$pat" 2>/dev/null |
        sort -u ||
        true
    )

  done


  if (( ${#user_items[@]} == 0 )); then

    warn "nenhum artefato encontrado em $user_codex"

    return 0

  fi


  found_any=1


  tar_args+=(
    -C "$user_codex"
    --transform "s#^#codex-$label/#"
  )


  for it in "${user_items[@]}"; do
    tar_args+=("$it")
  done
}


###############################################################################
# LOCALIZAÇÃO DOS CODEX HOMES
###############################################################################

if [[ -n "${CODEX_HOME:-}" ]]; then

  [[ -d "$CODEX_HOME" ]] ||
    die "CODEX_HOME não existe: $CODEX_HOME"

  collect_codex_home \
    "custom" \
    "$CODEX_HOME"

else

  for user in $CODEX_USERS; do

    home_dir="$(
      getent passwd "$user" 2>/dev/null |
        cut -d: -f6 ||
        true
    )"


    if [[ -z "$home_dir" ]]; then

      if [[ "$user" == "root" ]]; then

        home_dir="/root"

      else

        warn "usuário '$user' não encontrado."
        continue

      fi

    fi


    collect_codex_home \
      "$user" \
      "$home_dir/.codex"

  done

fi


if (( found_any == 0 )); then
  die "nenhum artefato do Codex foi encontrado."
fi


###############################################################################
# GERAÇÃO DO BACKUP
###############################################################################

echo
echo "Criando backup..."
echo

set +e

tar "${tar_args[@]}"

tar_status=$?

set -e


# GNU tar pode retornar 1 quando algum arquivo muda enquanto está sendo lido.
# Para sessões do Codex isso pode ocorrer normalmente.
if (( tar_status > 1 )); then

  rm -f "$ARCHIVE"

  die "tar falhou com código $tar_status"

fi


[[ -f "$ARCHIVE" ]] ||
  die "o arquivo de backup não foi criado."


###############################################################################
# PERMISSÕES
###############################################################################

if (( INCLUDE_SECRETS == 1 )); then

  chmod 600 "$ARCHIVE" 2>/dev/null || true

else

  chmod 640 "$ARCHIVE" 2>/dev/null || true

fi


###############################################################################
# CHECKSUM SHA-256
###############################################################################

archive_name="$(basename "$ARCHIVE")"


(
  cd "$BACKUP_DIR"

  sha256sum "$archive_name" \
    > "$(basename "$CHECKSUM")"
)


if (( INCLUDE_SECRETS == 1 )); then
  chmod 600 "$CHECKSUM" 2>/dev/null || true
fi


###############################################################################
# GIT LFS
###############################################################################

configure_git_lfs_if_needed() {

  local archive="$1"

  local archive_size
  local repo_root
  local archive_abs
  local archive_rel
  local attr_result


  archive_size="$(stat -c %s "$archive")"


  echo
  echo "Tamanho:"
  echo "  $(human_size "$archive_size")"
  echo


  if (( archive_size < LFS_THRESHOLD_BYTES )); then

    echo "Git:"
    echo "  arquivo abaixo de $(human_size "$LFS_THRESHOLD_BYTES")"
    echo "  Git LFS não é necessário."

    return 0

  fi


  echo "Git:"
  echo "  arquivo >= $(human_size "$LFS_THRESHOLD_BYTES")"
  echo "  Git LFS será utilizado."


  if ! git lfs version >/dev/null 2>&1; then

    cat >&2 <<EOF

ERRO: o backup possui $(human_size "$archive_size")
e deve ser armazenado usando Git LFS.

O comando 'git lfs' não está disponível.

Em Ubuntu/Kubuntu:

    sudo apt install git-lfs

Depois execute:

    git lfs install

Backup preservado em:

    $archive

EOF

    return 1

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

    cat >&2 <<EOF

ERRO: o backup ultrapassa o limite configurado para Git normal,
mas o diretório não pertence a um repositório Git.

Backup:

    $archive

Coloque CODEX_BACKUP_DIR dentro do repositório Git ou mova o
arquivo para um repositório configurado com Git LFS.

EOF

    return 1

  fi


  repo_root="$(realpath "$repo_root")"
  archive_abs="$(realpath "$archive")"


  case "$archive_abs" in

    "$repo_root"/*)
      archive_rel="${archive_abs#"$repo_root"/}"
      ;;

    *)
      die "não foi possível calcular o caminho relativo do backup no Git."
      ;;

  esac


  #
  # Configura LFS somente para os backups Codex .tar.gz.
  #
  # Usamos o padrão dentro do diretório em vez do nome timestampado
  # individual, evitando uma nova entrada em .gitattributes a cada backup.
  #
  backup_rel_dir="$(
    dirname "$archive_rel"
  )"


  if [[ "$backup_rel_dir" == "." ]]; then

    lfs_pattern="${ARCHIVE_PREFIX}-*.tar.gz"

  else

    lfs_pattern="$backup_rel_dir/${ARCHIVE_PREFIX}-*.tar.gz"

  fi


  git \
    -C "$repo_root" \
    lfs track "$lfs_pattern" \
    >/dev/null


  attr_result="$(
    git \
      -C "$repo_root" \
      check-attr \
      filter \
      -- "$archive_rel"
  )"


  if [[ "$attr_result" != *": lfs" ]]; then

    die "Git LFS não foi aplicado corretamente a $archive_rel"

  fi


  echo
  echo "Git LFS configurado:"
  echo "  $lfs_pattern"

  echo
  echo "Arquivo:"
  echo "  $archive_rel"

  echo
  echo "No próximo commit inclua também:"
  echo "  .gitattributes"

}


configure_git_lfs_if_needed "$ARCHIVE"


###############################################################################
# RETENÇÃO LOCAL
###############################################################################

apply_retention() {

  local prefix="$1"

  local -a archives=()


  mapfile -t archives < <(
    find "$BACKUP_DIR" \
      -maxdepth 1 \
      -type f \
      -name "${prefix}-????????-??????.tar.gz" \
      -printf '%T@ %p\n' |
      sort -nr |
      cut -d' ' -f2-
  )


  if (( ${#archives[@]} <= BACKUP_RETENTION )); then
    return 0
  fi


  echo
  echo "Retenção:"
  echo "  mantendo os $BACKUP_RETENTION backups mais recentes."


  local i
  local old_archive


  for (( i=BACKUP_RETENTION; i<${#archives[@]}; i++ )); do

    old_archive="${archives[$i]}"

    echo "  removendo:"
    echo "    $(basename "$old_archive")"

    rm -f \
      "$old_archive" \
      "$old_archive.sha256"

  done

}


apply_retention "$ARCHIVE_PREFIX"


###############################################################################
# RESULTADO
###############################################################################

archive_size="$(stat -c %s "$ARCHIVE")"


echo
echo "================================================================"
echo " BACKUP CODEX CONCLUÍDO"
echo "================================================================"
echo
echo "Arquivo:"
echo "  $ARCHIVE"
echo
echo "Tamanho:"
echo "  $(human_size "$archive_size")"
echo
echo "SHA-256:"
echo "  $CHECKSUM"
echo


if (( INCLUDE_SECRETS == 1 )); then

  echo "ATENÇÃO:"
  echo
  echo "  Este backup contém credenciais, tokens e configurações."
  echo
  echo "  NÃO envie para repositório público."
  echo
  echo "  Recomenda-se criptografá-lo antes de armazenamento remoto."

else

  echo "Credenciais:"
  echo "  não incluídas."
  echo
  echo "Para backup completo:"
  echo
  echo "  $0 --include-secrets"

fi


echo
echo "Para verificar o backup:"
echo
echo "  cd \"$BACKUP_DIR\""
echo "  sha256sum -c \"$(basename "$CHECKSUM")\""
echo
echo "Para listar o conteúdo:"
echo
echo "  tar -tzf \"$ARCHIVE\""
echo
