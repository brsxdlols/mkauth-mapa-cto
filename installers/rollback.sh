#!/bin/sh
set -eu

BACKUP_DIR="${1:-}"
ADMIN_DIR="${MKAUTH_ADMIN:-/opt/mk-auth/admin}"
ADDON_DIR="$ADMIN_DIR/addons/caixas"

fail() { echo "ERRO: $*" >&2; exit 1; }

[ "$(id -u)" -eq 0 ] || fail "execute como root"
[ -n "$BACKUP_DIR" ] || fail "informe o diretorio de backup"
[ -d "$BACKUP_DIR" ] || fail "backup nao encontrado: $BACKUP_DIR"
[ -f "$BACKUP_DIR/addon.js" ] || fail "backup sem addon.js"

find_addon_js() {
    for file in "$ADMIN_DIR/scripts/addon.js" "$ADMIN_DIR/addon.js" "$ADMIN_DIR/assets/js/addon.js"; do
        if [ -f "$file" ]; then
            printf '%s\n' "$file"
            return 0
        fi
    done
    return 1
}

ADDON_JS=$(find_addon_js || true)
[ -n "$ADDON_JS" ] || fail "addon.js do MK-AUTH nao encontrado"

rm -rf "$ADDON_DIR"
if [ -d "$BACKUP_DIR/caixas" ]; then
    cp -a "$BACKUP_DIR/caixas" "$ADDON_DIR"
fi
cp -a "$BACKUP_DIR/addon.js" "$ADDON_JS"

printf 'Rollback concluido a partir de: %s\n' "$BACKUP_DIR"

