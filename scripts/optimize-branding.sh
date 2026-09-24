#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SOURCE_DIR="${ROOT_DIR}/resources/branding/originals"
PUBLIC_DIR="${ROOT_DIR}/public/branding"
MODE="${1:-optimize}"

BG_QUALITY="${HECATE_BRANDING_JPEG_QUALITY:-82}"
PNG_MIN_QUALITY="${HECATE_BRANDING_PNG_MIN_QUALITY:-80}"
PNG_MAX_QUALITY="${HECATE_BRANDING_PNG_MAX_QUALITY:-95}"

mkdir -p "${SOURCE_DIR}" "${PUBLIC_DIR}"

command_exists() {
    command -v "$1" >/dev/null 2>&1
}

if command_exists magick; then
    MAGICK=(magick)
elif command_exists convert; then
    MAGICK=(convert)
else
    echo "ERRO: ImageMagick não encontrado (comando magick/convert)." >&2
    exit 1
fi

source_for() {
    local base="$1"
    local candidate

    for candidate in \
        "${SOURCE_DIR}/${base}.png" \
        "${SOURCE_DIR}/${base}.jpg" \
        "${SOURCE_DIR}/${base}.jpeg" \
        "${SOURCE_DIR}/${base}.webp"; do
        if [[ -f "${candidate}" ]]; then
            printf '%s\n' "${candidate}"
            return 0
        fi
    done

    return 1
}

copy_if_missing() {
    local file="$1"
    local source="${PUBLIC_DIR}/${file}"
    local target="${SOURCE_DIR}/${file}"

    if [[ -f "${target}" ]]; then
        return 0
    fi

    if [[ ! -f "${source}" ]]; then
        echo "AVISO: ${source} não existe; ignorado." >&2
        return 0
    fi

    cp -p "${source}" "${target}"
    echo "Original inicial preservado: resources/branding/originals/${file}"
}

bootstrap() {
    local file

    for file in \
        avatar.png \
        hecate-hero.jpg \
        login-background.jpg \
        dashboard-background.jpg \
        logo-horizontal.png \
        logo-vertical.png \
        symbol.png \
        favicon-16x16.png \
        favicon-32x32.png \
        favicon-48x48.png \
        favicon-180x180.png \
        favicon-192x192.png \
        favicon-512x512.png; do
        copy_if_missing "${file}"
    done

    echo
    echo "Bootstrap concluído. Substitua qualquer arquivo em resources/branding/originals/"
    echo "pelo master de maior qualidade antes da otimização definitiva, quando disponível."
}

optimize_jpeg() {
    local base="$1"
    local output="$2"
    local geometry="$3"
    local source

    if ! source="$(source_for "${base}")"; then
        echo "ERRO: original de ${base} não encontrado em ${SOURCE_DIR}." >&2
        exit 1
    fi

    "${MAGICK[@]}" "${source}" \
        -auto-orient \
        -resize "${geometry}" \
        -strip \
        -colorspace sRGB \
        -sampling-factor 4:2:0 \
        -interlace Plane \
        -quality "${BG_QUALITY}" \
        "${PUBLIC_DIR}/${output}"
}

optimize_png() {
    local file="$1"
    local source="${SOURCE_DIR}/${file}"
    local output="${PUBLIC_DIR}/${file}"

    if [[ ! -f "${source}" ]]; then
        echo "AVISO: ${file} sem original; ignorado." >&2
        return 0
    fi

    if command_exists pngquant; then
        pngquant \
            --quality="${PNG_MIN_QUALITY}-${PNG_MAX_QUALITY}" \
            --speed 1 \
            --strip \
            --force \
            --output "${output}" \
            -- "${source}"
    else
        echo "AVISO: pngquant não instalado; ${file} receberá somente strip/compressão PNG do ImageMagick." >&2
        "${MAGICK[@]}" "${source}" \
            -auto-orient \
            -strip \
            -define png:compression-level=9 \
            "${output}"
    fi
}

optimize_favicon() {
    local size="$1"
    local file="favicon-${size}x${size}.png"
    local source="${SOURCE_DIR}/${file}"
    local tmp

    if [[ ! -f "${source}" ]]; then
        echo "AVISO: ${file} sem original; ignorado." >&2
        return 0
    fi

    tmp="$(mktemp --suffix=.png)"
    trap 'rm -f "${tmp:-}"' RETURN

    "${MAGICK[@]}" "${source}" \
        -auto-orient \
        -resize "${size}x${size}>" \
        -strip \
        -define png:compression-level=9 \
        "${tmp}"

    if command_exists pngquant; then
        pngquant \
            --quality="${PNG_MIN_QUALITY}-${PNG_MAX_QUALITY}" \
            --speed 1 \
            --strip \
            --force \
            --output "${PUBLIC_DIR}/${file}" \
            -- "${tmp}"
    else
        mv "${tmp}" "${PUBLIC_DIR}/${file}"
    fi

    rm -f "${tmp}"
    trap - RETURN
}

report() {
    local file bytes kib

    echo
    echo "Assets de produção:"
    printf '%-30s %12s\n' "arquivo" "tamanho"
    printf '%-30s %12s\n' "------------------------------" "------------"

    for file in \
        hecate-hero.jpg \
        login-background.jpg \
        dashboard-background.jpg \
        avatar.png \
        logo-horizontal.png \
        logo-vertical.png \
        symbol.png \
        favicon-16x16.png \
        favicon-32x32.png \
        favicon-48x48.png \
        favicon-180x180.png \
        favicon-192x192.png \
        favicon-512x512.png; do
        if [[ -f "${PUBLIC_DIR}/${file}" ]]; then
            bytes="$(stat -c '%s' "${PUBLIC_DIR}/${file}")"
            kib="$(( (bytes + 1023) / 1024 )) KiB"
            printf '%-30s %12s\n' "${file}" "${kib}"
        fi
    done
}

optimize() {
    if [[ ! -d "${SOURCE_DIR}" ]] || ! source_for "login-background" >/dev/null 2>&1; then
        echo "Originais ainda não preparados. Executando bootstrap inicial..."
        bootstrap
        echo
    fi

    # Artes pictóricas: JPEG é mais eficiente que PNG para produção.
    # O operador pode substituir os arquivos em resources/branding/originals/
    # por masters PNG/JPEG de maior qualidade antes de executar novamente.
    optimize_jpeg "hecate-hero" "hecate-hero.jpg" "1920x1080>"
    optimize_jpeg "login-background" "login-background.jpg" "1920x1080>"
    optimize_jpeg "dashboard-background" "dashboard-background.jpg" "1920x1080>"

    # O avatar permanece PNG enquanto o manual apontar para avatar.png.
    # O master fica preservado fora de public/ e somente o derivado é quantizado.
    optimize_png "avatar.png"

    optimize_png "logo-horizontal.png"
    optimize_png "logo-vertical.png"
    optimize_png "symbol.png"

    optimize_favicon 16
    optimize_favicon 32
    optimize_favicon 48
    optimize_favicon 180
    optimize_favicon 192
    optimize_favicon 512

    report
}

case "${MODE}" in
    bootstrap|--bootstrap)
        bootstrap
        ;;
    optimize|--optimize)
        optimize
        ;;
    *)
        echo "Uso: $0 [bootstrap|optimize]" >&2
        exit 2
        ;;
esac
