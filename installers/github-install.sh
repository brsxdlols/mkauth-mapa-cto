#!/bin/sh
set -eu

REPO="${MKAUTH_MAPA_CTO_REPO:-brsxdlols/mkauth-mapa-cto}"
API="https://api.github.com/repos/$REPO/releases/latest"
TMP=$(mktemp -d /tmp/mkauth-mapa-cto.XXXXXX)
trap 'rm -rf "$TMP"' EXIT HUP INT TERM

fail() { echo "ERRO: $*" >&2; exit 1; }
need() { command -v "$1" >/dev/null 2>&1 || fail "comando ausente: $1"; }

need curl
need grep
need sed
need sha256sum

json=$(curl -fsSL "$API")
run_url=$(printf '%s\n' "$json" | sed -n 's/.*"browser_download_url"[[:space:]]*:[[:space:]]*"\([^"]*mkauth-mapa-cto-[^"]*\.run\)".*/\1/p' | head -1)
sha_url=$(printf '%s\n' "$json" | sed -n 's/.*"browser_download_url"[[:space:]]*:[[:space:]]*"\([^"]*mkauth-mapa-cto-[^"]*\.run\.sha256\)".*/\1/p' | head -1)
[ -n "$run_url" ] || fail "release sem arquivo .run"
[ -n "$sha_url" ] || fail "release sem checksum .run.sha256"

curl -fsSL "$run_url" -o "$TMP/install.run"
curl -fsSL "$sha_url" -o "$TMP/install.run.sha256"
run_name=$(sed -n 's/^[0-9a-fA-F][0-9a-fA-F]*  //p' "$TMP/install.run.sha256" | head -1)
[ -n "$run_name" ] || fail "checksum invalido"
mv "$TMP/install.run" "$TMP/$run_name"
(cd "$TMP" && sha256sum -c install.run.sha256)
sh "$TMP/$run_name"
