#!/usr/bin/env bash
# Updates the server to the latest code on GitHub:  sudo bash /var/www/littlestep/backend/deploy/deploy.sh
# The site shows a maintenance page while updating and comes back up even if a step fails.
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/littlestep}"
BRANCH="${BRANCH:-main}"
PHP="8.3"
BACKEND="$APP_DIR/backend"
OWNER=$(stat -c %U "$APP_DIR")

[ "$(id -u)" -eq 0 ] || { echo "Run with sudo."; exit 1; }
as_owner() { sudo -u "$OWNER" -H "$@"; }
as_web() { sudo -u www-data "$@"; }

cd "$BACKEND"
as_web php artisan down --retry=60 || true
trap 'as_web php artisan up' EXIT

as_owner git -C "$APP_DIR" pull --ff-only origin "$BRANCH"
as_owner composer install --no-dev --optimize-autoloader --no-interaction
as_owner npm ci
as_owner npm run build
as_web php artisan migrate --force
as_web php artisan optimize
systemctl reload "php$PHP-fpm"

echo "Updated to $(as_owner git -C "$APP_DIR" log -1 --format='%h %s')"
