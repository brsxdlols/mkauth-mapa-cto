#!/bin/sh
set -eu

VERSION="1.1.56"
SCRIPT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
ROOT_DIR=$(CDPATH= cd -- "$SCRIPT_DIR/.." && pwd)
SOURCE_DIR="$ROOT_DIR/addons/mapa-cto"
ADMIN_DIR="${MKAUTH_ADMIN:-/opt/mk-auth/admin}"
ADDONS_DIR="$ADMIN_DIR/addons"
ADDON_DIR="$ADDONS_DIR/caixas"
REPORT_SOURCE="$ROOT_DIR/addons/rel_cto_cad_cli"
REPORT_DIR="$ADDONS_DIR/rel_cto_cad_cli"
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

[ -f "$REPORT_SOURCE/index.php" ] || fail "pacote sem relatorio de caixas"
for file in index.php report-clients.php report-model.php config.php addons.class.php; do
    php -l "$REPORT_SOURCE/$file" >/dev/null || fail "PHP invalido no relatorio: $file"
done

ADDON_JS=$(find_addon_js || true)
[ -n "$ADDON_JS" ] || fail "addon.js do MK-AUTH nao encontrado"

# Refuse directory links: replacing leaf files must not traverse another tree.
for directory in "$ADDONS_DIR" "$ADDON_DIR" "$REPORT_DIR"; do
    [ ! -L "$directory" ] || fail "diretorio simbolico nao suportado: $directory"
done
find "$REPORT_SOURCE" -type d | while IFS= read -r directory; do
    relative=${directory#"$REPORT_SOURCE"}
    [ ! -L "$REPORT_DIR$relative" ] || fail "diretorio simbolico no relatorio: $REPORT_DIR$relative"
done


mkdir -p "$BACKUP_ROOT" "$ADDONS_DIR"
BACKUP_DIR=$(mktemp -d "$BACKUP_DIR.XXXXXX")
if [ -d "$ADDON_DIR" ]; then
    cp -a --remove-destination "$ADDON_DIR" "$BACKUP_DIR/caixas"
fi
# Save both the menu content and the original link (if present).
cp -aL --remove-destination "$ADDON_JS" "$BACKUP_DIR/addon.js"
if [ -L "$ADDON_JS" ]; then
    cp -a --remove-destination "$ADDON_JS" "$BACKUP_DIR/addon-js-link"
fi
printf '%s\n' "$ADDON_JS" > "$BACKUP_DIR/addon-js-path"
if [ -d "$REPORT_DIR" ]; then
    cp -a --remove-destination "$REPORT_DIR" "$BACKUP_DIR/rel_cto_cad_cli"
    printf 'existing\n' > "$BACKUP_DIR/report-state"
else
    printf 'absent\n' > "$BACKUP_DIR/report-state"
fi

rm -rf "$ADDON_DIR"
mkdir -p "$ADDON_DIR"
cp -a --remove-destination "$SOURCE_DIR"/. "$ADDON_DIR"/
printf '%s\n' "$VERSION" > "$ADDON_DIR/VERSION"

mkdir -p "$REPORT_DIR"
cp -a --remove-destination "$REPORT_SOURCE"/. "$REPORT_DIR"/
if [ -f "$BACKUP_DIR/rel_cto_cad_cli/config.php" ]; then
    cp -a --remove-destination "$BACKUP_DIR/rel_cto_cad_cli/config.php" "$REPORT_DIR/config.php"
fi
rm -f "$REPORT_DIR/VERSION"
printf '%s\n' "$VERSION" > "$REPORT_DIR/VERSION"

# Edit an independent file, never the referent of an addon.js symlink.
cp -a --remove-destination "$BACKUP_DIR/addon.js" "$ADDON_JS"

if grep -q 'GERENCIADOR FTTH - Caixas' "$ADDON_JS"; then
    sed -i '/GERENCIADOR FTTH - Caixas/,+2d' "$ADDON_JS"
fi
if grep -q 'MAPA CTO CLIENTE PICKER' "$ADDON_JS"; then
    sed -i '/MAPA CTO CLIENTE PICKER INICIO/,+7d' "$ADDON_JS"
fi
if grep -q '^// Mapa CTO$' "$ADDON_JS"; then
    sed -i '/^\/\/ Mapa CTO$/,+1d' "$ADDON_JS"
fi

tmp_menu=$(mktemp /tmp/mkauth-addon-js.XXXXXX)
awk '
    /Mapa Das CTO/ || /add_menu\.[^(]*\(.*addons\/caixas\// || /href="\/admin\/addons\/caixas\// {
        seen++
        if (seen > 1) {
            next
        }
    }
    { print }
' "$ADDON_JS" > "$tmp_menu"
cat "$tmp_menu" > "$ADDON_JS"
rm -f "$tmp_menu"

if grep -q 'Mapa Das CTO' "$ADDON_JS"; then
    printf 'Menu Mapa CTO ja existe em %s; duplicados foram removidos.\n' "$ADDON_JS"
else
    cat >> "$ADDON_JS" <<'MENU_SNIPPET'

// Mapa CTO
add_menu.provedor('{"plink": "' + minha_url + 'addons/caixas/", "ptext": "📦 Mapa Das CTO"}');
MENU_SNIPPET
fi

cat >> "$ADDON_JS" <<'PICKER_SNIPPET'

// MAPA CTO CLIENTE PICKER INICIO
if (/\/admin\/cliente_(alt|ins)\.hhvm/.test(window.location.pathname)) {
    const mapaCtoClientePicker = document.createElement('script');
    mapaCtoClientePicker.src = minha_url + 'addons/caixas/assets/js/client_cto_picker.js?v=1.1.56';
    mapaCtoClientePicker.async = true;
    document.body.appendChild(mapaCtoClientePicker);
}
// MAPA CTO CLIENTE PICKER FIM
PICKER_SNIPPET

# Replace only this report's shortcut; preserve other addon menu entries.
tmp_report_menu=$(mktemp /tmp/mkauth-report-menu.XXXXXX)
awk '
    /^\/\/ MAPA CTO RELATORIO CAIXAS INICIO$/ { skip=1; next }
    /^\/\/ MAPA CTO RELATORIO CAIXAS FIM$/ { skip=0; next }
    skip { next }
    /add_menu\.[^(]*\(.*rel_cto_cad_cli\// { next }
    { print }
' "$ADDON_JS" > "$tmp_report_menu"
cat "$tmp_report_menu" > "$ADDON_JS"
rm -f "$tmp_report_menu"
cat >> "$ADDON_JS" <<'REPORT_MENU'

// MAPA CTO RELATORIO CAIXAS INICIO
add_menu.provedor('{"plink": "' + minha_url + 'addons/rel_cto_cad_cli/", "ptext": "Relatório de Caixas"}');
// MAPA CTO RELATORIO CAIXAS FIM
REPORT_MENU

for file in index.php report-clients.php report-model.php config.php addons.class.php; do
    php -l "$REPORT_DIR/$file" >/dev/null
done
grep -q 'addons/rel_cto_cad_cli/' "$ADDON_JS"

php -l "$ADDON_DIR/index.php" >/dev/null
php -l "$ADDON_DIR/src/cto/componente/mapadectos/mapadectos.view.php" >/dev/null
php -l "$ADDON_DIR/src/cto/config/api.php" >/dev/null
php -l "$ADDON_DIR/src/cto/api/ctos_disponiveis.php" >/dev/null
grep -q 'Mapa Das CTO' "$ADDON_JS"
grep -q 'client_cto_picker.js' "$ADDON_JS"

printf 'Instalacao concluida.\nVersao: %s\nAddon: %s\nRelatorio: %s\nMenu: %s\nBackup: %s\n' "$VERSION" "$ADDON_DIR" "$REPORT_DIR" "$ADDON_JS" "$BACKUP_DIR"
