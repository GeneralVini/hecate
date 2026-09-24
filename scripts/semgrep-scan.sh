#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

SEMGREP_BIN="${HECATE_SEMGREP_BIN:-$PROJECT_ROOT/.tools/semgrep/bin/semgrep}"
SEMGREP_PYTHON="${HECATE_SEMGREP_PYTHON:-$PROJECT_ROOT/.tools/semgrep/bin/python}"
SEMGREP_CONFIG="$PROJECT_ROOT/security/semgrep-rules/hecate.yml"

line() {
    printf '%s\n' '============================================================'
}

if [[ "$SEMGREP_BIN" == */* ]]; then
    if [[ ! -x "$SEMGREP_BIN" ]]; then
        printf '[ERRO] Semgrep não encontrado ou não executável: %s\n' "$SEMGREP_BIN" >&2
        printf '\nPara instalar a versão homologada do projeto:\n\n  make setup\n' >&2
        exit 1
    fi
else
    RESOLVED_SEMGREP_BIN="$(command -v "$SEMGREP_BIN" || true)"
    if [[ -z "$RESOLVED_SEMGREP_BIN" ]]; then
        printf '[ERRO] Semgrep não encontrado no PATH: %s\n' "$SEMGREP_BIN" >&2
        exit 1
    fi
    SEMGREP_BIN="$RESOLVED_SEMGREP_BIN"
fi

if [[ ! -x "$SEMGREP_PYTHON" ]]; then
    RESOLVED_PYTHON="$(command -v python3 || true)"
    if [[ -z "$RESOLVED_PYTHON" ]]; then
        printf '[ERRO] Python 3 não encontrado para interpretar a saída JSON do Semgrep.\n' >&2
        printf '\nPara recriar a instalação local homologada:\n\n  rm -rf "%s/.tools/semgrep"\n  make setup\n' "$PROJECT_ROOT" >&2
        exit 1
    fi
    SEMGREP_PYTHON="$RESOLVED_PYTHON"
fi

if [[ ! -r "$SEMGREP_CONFIG" ]]; then
    printf '[ERRO] Configuração do Semgrep ausente ou ilegível: %s\n' "$SEMGREP_CONFIG" >&2
    exit 1
fi

if ! "$SEMGREP_BIN" --validate --config "$SEMGREP_CONFIG" --metrics=off >/dev/null 2>&1; then
    printf '[ERRO] As regras Semgrep não passaram pela validação.\n' >&2
    printf '\nExecute para obter o diagnóstico:\n\n  composer security:semgrep:validate\n' >&2
    exit 2
fi

RULE_COUNT="$(grep -cE '^  - id:' "$SEMGREP_CONFIG" || true)"
RESULT_FILE="$(mktemp)"
trap 'rm -f "$RESULT_FILE"' EXIT

line
printf ' HECATE — Semgrep / análise do código\n'
line
printf '[INFO] Regras configuradas: %s\n' "$RULE_COUNT"
printf '[INFO] Escopo: src, config e public; arquivos novos ainda não rastreados pelo Git também são analisados.\n'
printf '[INFO] public/assets permanece excluído por ser conteúdo gerado em runtime.\n'
printf '[INFO] ERROR = finding bloqueante; WARNING = hotspot para revisão.\n\n'

if ! "$SEMGREP_BIN" \
    --json \
    --config "$SEMGREP_CONFIG" \
    --metrics=off \
    --no-git-ignore \
    --exclude 'public/assets/**' \
    src config public >"$RESULT_FILE"; then
    printf '\n[ERRO] O mecanismo Semgrep falhou antes da triagem dos resultados.\n' >&2
    printf 'Execute composer security:semgrep:validate e revise a saída técnica acima.\n' >&2
    exit 2
fi

set +e
"$SEMGREP_PYTHON" - "$RESULT_FILE" "$PROJECT_ROOT" "$RULE_COUNT" <<'PY'
import json
import os
import pathlib
import sys

result_file = pathlib.Path(sys.argv[1])
root = pathlib.Path(sys.argv[2])
rule_count = sys.argv[3]

try:
    data = json.loads(result_file.read_text(encoding="utf-8"))
except Exception as exc:
    print(f"[ERRO] Não foi possível interpretar a saída JSON do Semgrep: {exc}", file=sys.stderr)
    raise SystemExit(2)

results = data.get("results") or []
engine_errors = data.get("errors") or []
blocking = [item for item in results if (item.get("extra") or {}).get("severity") == "ERROR"]
hotspots = [item for item in results if (item.get("extra") or {}).get("severity") == "WARNING"]
other = [item for item in results if item not in blocking and item not in hotspots]

paths = data.get("paths") or {}
scanned = paths.get("scanned") or []
skipped = paths.get("skipped") or []


def skipped_path(item: object) -> str:
    if isinstance(item, str):
        return item
    if isinstance(item, dict):
        return str(item.get("path") or item.get("file") or "")
    return ""


def skipped_reason(item: object) -> str:
    if isinstance(item, dict):
        return str(item.get("reason") or item.get("details") or "sem motivo informado")
    return "sem motivo informado"


def excluded_by_policy(item: object) -> bool:
    path = skipped_path(item).replace("\\", "/")
    return path.startswith("public/assets/") or "/public/assets/" in path


policy_skips = [item for item in skipped if excluded_by_policy(item)]
unexpected_skips = [item for item in skipped if not excluded_by_policy(item)]

use_color = sys.stdout.isatty() and "NO_COLOR" not in os.environ
RESET = "\033[0m" if use_color else ""
BOLD = "\033[1m" if use_color else ""
GREEN = "\033[32m" if use_color else ""
YELLOW = "\033[33m" if use_color else ""
RED = "\033[31m" if use_color else ""
CYAN = "\033[36m" if use_color else ""
DIM = "\033[2m" if use_color else ""


def paint(text: str, color: str = "", bold: bool = False) -> str:
    prefix = (BOLD if bold else "") + color
    return f"{prefix}{text}{RESET}" if prefix else text


def source_line(path_value: str, line_number: int | None) -> str:
    if not path_value or not line_number:
        return ""
    path = pathlib.Path(path_value)
    if not path.is_absolute():
        path = root / path
    try:
        lines = path.read_text(encoding="utf-8", errors="replace").splitlines()
        if 1 <= line_number <= len(lines):
            return lines[line_number - 1].strip()
    except OSError:
        pass
    return ""


def show_findings(title: str, icon: str, label: str, items: list[dict], color: str) -> None:
    if not items:
        return
    print()
    print(paint(f"{icon} {title}", color, bold=True))
    print(DIM + "-" * 60 + RESET)
    for item in items:
        extra = item.get("extra") or {}
        path_value = str(item.get("path") or "?")
        start = item.get("start") or {}
        line_number = start.get("line")
        check_id = str(item.get("check_id") or "regra-desconhecida")
        message = str(extra.get("message") or "Sem descrição")
        code = source_line(path_value, line_number)
        location = f"{path_value}:{line_number}" if line_number else path_value
        print(paint(f"{icon} [{label}] {location}", color, bold=True))
        print(f"   Regra : {check_id}")
        print(f"   Motivo: {message}")
        if code:
            print(f"   Código: {code}")
        print()


show_findings("FINDINGS BLOQUEANTES", "❌", "BLOQUEANTE", blocking, RED)
show_findings("HOTSPOTS PARA REVISÃO", "⚠️", "HOTSPOT", hotspots, YELLOW)
show_findings("OUTROS FINDINGS", "ℹ️", "INFO", other, CYAN)

if unexpected_skips:
    print()
    print(paint("⏭️  SKIPS INESPERADOS", RED, bold=True))
    print(DIM + "-" * 60 + RESET)
    for item in unexpected_skips:
        path = skipped_path(item) or "path desconhecido"
        print(paint(f"❌ {path}", RED))
        print(f"   Motivo: {skipped_reason(item)}")

if engine_errors:
    print()
    print(paint("💥 ERROS DO MECANISMO", RED, bold=True))
    print(DIM + "-" * 60 + RESET)
    for error in engine_errors:
        print(paint(f"❌ {error}", RED))

print()
print(paint("=" * 60, CYAN))
print(paint(" 🛡️  RESULTADO SEMGREP", CYAN, bold=True))
print(paint("=" * 60, CYAN))
print(f"🔐 Regras configuradas  : {rule_count}")
print(f"📄 Arquivos analisados   : {len(scanned)}")
print(f"🚫 Excluídos por política: {len(policy_skips)}")
print(f"⏭️  Skips inesperados    : {paint(str(len(unexpected_skips)), RED if unexpected_skips else GREEN, bold=bool(unexpected_skips))}")
print(f"❌ Bloqueantes (ERROR)   : {paint(str(len(blocking)), RED if blocking else GREEN, bold=bool(blocking))}")
print(f"⚠️  Hotspots (WARNING)    : {paint(str(len(hotspots)), YELLOW if hotspots else GREEN, bold=bool(hotspots))}")
if other:
    print(f"ℹ️  Outros findings       : {paint(str(len(other)), CYAN, bold=True)}")
print(f"🧩 Erros do mecanismo    : {paint(str(len(engine_errors)), RED if engine_errors else GREEN, bold=bool(engine_errors))}")
print("📦 Escopo Git            : inclui arquivos rastreados e não rastreados em src/config/public")
print("🚫 Exclusão operacional  : public/assets/**")

if engine_errors:
    print(paint("💥 STATUS                : ERRO DO SCANNER", RED, bold=True))
    raise SystemExit(2)
if unexpected_skips:
    print(paint("⏭️  STATUS                : COBERTURA PARCIAL", RED, bold=True))
    print(paint("   Há arquivos ignorados fora da política explícita; o scan não é confiável.", RED))
    raise SystemExit(2)
if blocking:
    print(paint("❌ STATUS                : REPROVADO", RED, bold=True))
    raise SystemExit(1)
if hotspots:
    print(paint("⚠️  STATUS                : APROVADO COM HOTSPOTS", YELLOW, bold=True))
    print(paint("   Revisar os hotspots antes de considerar a alteração concluída.", YELLOW))
    raise SystemExit(0)

print(paint("✅ STATUS                : APROVADO", GREEN, bold=True))
raise SystemExit(0)
PY
STATUS=$?
set -e

case "$STATUS" in
    0)
        exit 0
        ;;
    1)
        printf '\n[ERRO] O Semgrep encontrou findings bloqueantes.\n' >&2
        exit 1
        ;;
    *)
        printf '\n[ERRO] O Semgrep não concluiu a análise de forma confiável.\n' >&2
        exit 2
        ;;
esac
