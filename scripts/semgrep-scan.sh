#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

SEMGREP_BIN="${HECATE_SEMGREP_BIN:-$PROJECT_ROOT/.tools/semgrep/bin/semgrep}"
SEMGREP_PYTHON="$PROJECT_ROOT/.tools/semgrep/bin/python"
SEMGREP_CONFIG="$PROJECT_ROOT/security/semgrep-rules/hecate.yml"

line() {
    printf '%s\n' '============================================================'
}

if [[ ! -x "$SEMGREP_BIN" ]]; then
    printf '[ERRO] Semgrep local não encontrado ou não executável: %s\n' "$SEMGREP_BIN" >&2
    printf '\nPara instalar a versão homologada do projeto:\n\n  make setup\n' >&2
    exit 1
fi

if [[ ! -x "$SEMGREP_PYTHON" ]]; then
    printf '[ERRO] Python do ambiente Semgrep não encontrado ou não executável: %s\n' "$SEMGREP_PYTHON" >&2
    printf '\nPara recriar a instalação homologada:\n\n  rm -rf "%s/.tools/semgrep"\n  make setup\n' "$PROJECT_ROOT" >&2
    exit 1
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
printf '[INFO] ERROR = finding bloqueante; WARNING = hotspot para revisão.\n\n'

if ! "$SEMGREP_BIN" \
    --json \
    --config "$SEMGREP_CONFIG" \
    --metrics=off \
    src config public >"$RESULT_FILE"; then
    printf '\n[ERRO] O mecanismo Semgrep falhou antes da triagem dos resultados.\n' >&2
    printf 'Execute composer security:semgrep:validate e revise a saída técnica acima.\n' >&2
    exit 2
fi

set +e
"$SEMGREP_PYTHON" - "$RESULT_FILE" "$PROJECT_ROOT" "$RULE_COUNT" <<'PY'
import json
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


def show_findings(title: str, label: str, items: list[dict]) -> None:
    if not items:
        return
    print(f"\n{title}")
    print("-" * 60)
    for item in items:
        extra = item.get("extra") or {}
        path_value = str(item.get("path") or "?")
        start = item.get("start") or {}
        line_number = start.get("line")
        check_id = str(item.get("check_id") or "regra-desconhecida")
        message = str(extra.get("message") or "Sem descrição")
        code = source_line(path_value, line_number)
        location = f"{path_value}:{line_number}" if line_number else path_value
        print(f"[{label}] {location}")
        print(f"  Regra: {check_id}")
        print(f"  Motivo: {message}")
        if code:
            print(f"  Código: {code}")
        print()


show_findings("FINDINGS BLOQUEANTES", "BLOQUEANTE", blocking)
show_findings("HOTSPOTS PARA REVISÃO", "HOTSPOT", hotspots)
show_findings("OUTROS FINDINGS", "INFO", other)

if engine_errors:
    print("\nERROS DO MECANISMO")
    print("-" * 60)
    for error in engine_errors:
        print(f"[ERRO] {error}")

print("\n" + "=" * 60)
print(" RESULTADO SEMGREP")
print("=" * 60)
print(f"Regras configuradas : {rule_count}")
if scanned:
    print(f"Arquivos analisados  : {len(scanned)}")
if skipped:
    print(f"Arquivos ignorados   : {len(skipped)}")
print(f"Bloqueantes (ERROR)  : {len(blocking)}")
print(f"Hotspots (WARNING)   : {len(hotspots)}")
if other:
    print(f"Outros findings      : {len(other)}")
print(f"Erros do mecanismo   : {len(engine_errors)}")

if engine_errors:
    print("STATUS               : ERRO DO SCANNER")
    raise SystemExit(2)
if blocking:
    print("STATUS               : REPROVADO")
    raise SystemExit(1)

print("STATUS               : APROVADO")
if hotspots:
    print("Observação            : hotspots exigem revisão, mas não bloqueiam o gate.")
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
