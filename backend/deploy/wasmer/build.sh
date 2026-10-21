#!/usr/bin/env bash
# Builds a clean Wasmer Edge package from the committed backend and (with --deploy) deploys it.
#   bash deploy/wasmer/build.sh            # build only, into deploy/wasmer/build
#   bash deploy/wasmer/build.sh --deploy   # build, then `wasmer deploy`
# Only committed files are packaged: .env, node_modules and local storage never leave this computer.
set -euo pipefail

BACKEND="$(cd "$(dirname "$0")/../.." && pwd)"
REPO="$(git -C "$BACKEND" rev-parse --show-toplevel)"
OUT="$BACKEND/deploy/wasmer/build"
WASMER="${WASMER:-wasmer}"
command -v "$WASMER" >/dev/null || WASMER="$HOME/.wasmer/bin/wasmer"

if [ -n "$(git -C "$REPO" status --porcelain -- backend)" ]; then
    echo "Note: uncommitted changes in backend/ are NOT included (the package is built from the last commit)."
fi

rm -rf "$OUT"
mkdir -p "$OUT"
git -C "$REPO" archive --format=tar HEAD:backend | tar -x -C "$OUT"

cd "$OUT"
composer install --no-dev --optimize-autoloader --no-interaction --no-progress
npm ci --no-audit --no-fund
npm run build

# Guardian web app (for iPhone users: Safari → Add to Home Screen), served at <APP_URL>/app.
APP_URL="$(sed -n 's/^ *APP_URL: *//p' "$BACKEND/deploy/wasmer/app.yaml")"
WEB="$(mktemp -d)"
trap 'rm -rf "$WEB"' EXIT
git -C "$REPO" archive --format=tar HEAD:mobile | tar -x -C "$WEB"
(cd "$WEB" && npm ci --no-audit --no-fund && EXPO_PUBLIC_API_URL="$APP_URL" npx expo export --platform web --output-dir "$OUT/public/app")

rm -rf node_modules tests deploy

cp "$BACKEND/deploy/wasmer/wasmer.toml" "$BACKEND/deploy/wasmer/app.yaml" .
cp -r "$BACKEND/deploy/wasmer/php-config" .

echo "Package ready in $OUT ($(du -sh . | cut -f1))."

if [ "${1:-}" = "--deploy" ]; then
    "$WASMER" deploy --non-interactive --publish-package --bump

    # Post-deploy steps on the live app (Wasmer jobs don't see the app's database, see app.yaml).
    remote() { MSYS_NO_PATHCONV=1 "$WASMER" ssh -a aljund/littlestep -- "$1" < /dev/null; }
    remote "mkdir -p /data/storage/app/private /data/storage/framework/cache/data /data/storage/framework/sessions /data/storage/framework/views /data/storage/logs /data/storage/fonts"
    remote "php /app/artisan migrate --force"
    remote "php /app/artisan db:seed --force"
    remote "php /app/artisan view:cache"
    echo "Deployed: https://littlestep.wasmer.app"
fi
