#!/bin/sh
set -eu

VERSION="1.0.5"
SCRIPT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
ROOT_DIR=$(CDPATH= cd -- "$SCRIPT_DIR/.." && pwd)
SOURCE_DIR="$ROOT_DIR/addons/mapa-cto"
ADMIN_DIR="${MKAUTH_ADMIN:-/opt/mk-auth/admin}"
ADDONS_DIR="$ADMIN_DIR/addons"
ADDON_DIR="$ADDONS_DIR/caixas"
STAMP=$(date +%Y%m%d-%H%M%S)
BACKUP_ROOT="${MKAUTH_BACKUP_ROOT:-/root/backups}"
BACKUP_DIR="$BACKUP_ROOT/mkauth-mapa-cto-$STAMP-v$VERSION"

fail() { echo "ERRO: $*" >&2; exit 1; }

find_addon_js() {
    for file in "$ADMIN_DIR/addons/addon.js" "$ADMIN_DIR/scripts/addon.js" "$ADMIN_DIR/addon.js" "$ADMIN_DIR/assets/js/addon.js"; do
        if [ -f "$file" ]; then
            printf '%s\n' "$file"
            return 0
        fi
    done
    found=$(find "$ADMIN_DIR" -type f -name addon.js 2>/dev/null | head -1)
    if [ -n "$found" ]; then
        printf '%s\n' "$found"
        return 0
    fi
    return 1
}

[ "$(id -u)" -eq 0 ] || fail "execute como root"
[ -d "$ADMIN_DIR" ] || fail "diretorio MK-AUTH nao encontrado: $ADMIN_DIR"
[ -d "$SOURCE_DIR/src/cto/componente/mapadectos" ] || fail "pacote do Mapa CTO incompleto"

ADDON_JS=$(find_addon_js || true)
[ -n "$ADDON_JS" ] || fail "addon.js do MK-AUTH nao encontrado"

mkdir -p "$BACKUP_DIR" "$ADDONS_DIR"
if [ -d "$ADDON_DIR" ]; then
    cp -a "$ADDON_DIR" "$BACKUP_DIR/caixas"
fi
cp -a "$ADDON_JS" "$BACKUP_DIR/addon.js"

rm -rf "$ADDON_DIR"
mkdir -p "$ADDON_DIR"
cp -a "$SOURCE_DIR"/. "$ADDON_DIR"/
printf '%s\n' "$VERSION" > "$ADDON_DIR/VERSION"

if grep -q 'GERENCIADOR FTTH - Caixas' "$ADDON_JS"; then
    sed -i '/GERENCIADOR FTTH - Caixas/,+2d' "$ADDON_JS"
fi
cat >> "$ADDON_JS" <<'MENU_SNIPPET'

/** GERENCIADOR FTTH - Caixas **/
const caixas = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ":" + window.location.port : "") + "/admin/addons/caixas/";
add_menu.opcoes(JSON.stringify({plink: caixas + "?_route=painel", ptext: "Gerenciador FTTH"}));
MENU_SNIPPET

php -l "$ADDON_DIR/index.php" >/dev/null
php -l "$ADDON_DIR/src/cto/componente/mapadectos/mapadectos.view.php" >/dev/null
php -l "$ADDON_DIR/src/cto/config/api.php" >/dev/null
grep -q 'GERENCIADOR FTTH - Caixas' "$ADDON_JS"

printf 'Instalacao concluida.\nVersao: %s\nAddon: %s\nMenu: %s\nBackup: %s\n' "$VERSION" "$ADDON_DIR" "$ADDON_JS" "$BACKUP_DIR"
