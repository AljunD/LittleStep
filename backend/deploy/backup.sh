#!/usr/bin/env bash
# Nightly backup of the LittleStep database and the private child photos (run by /etc/cron.d/littlestep).
# Run by hand any time:  sudo bash /var/www/littlestep/backend/deploy/backup.sh
# Backups stay on this server: copy /var/backups/littlestep somewhere else regularly.
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/littlestep}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/littlestep}"
KEEP_DAYS="${KEEP_DAYS:-14}"
BACKEND="$APP_DIR/backend"
STAMP=$(date +%Y-%m-%d_%H%M)

[ "$(id -u)" -eq 0 ] || { echo "Run with sudo."; exit 1; }
env_value() { grep -E "^$1=" "$BACKEND/.env" | tail -1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'; }

umask 077
mkdir -p "$BACKUP_DIR"

credentials=$(mktemp)
trap 'rm -f "$credentials"' EXIT
printf '[client]\nuser=%s\npassword=%s\nhost=%s\nport=%s\n' \
    "$(env_value DB_USERNAME)" "$(env_value DB_PASSWORD)" "$(env_value DB_HOST)" "$(env_value DB_PORT)" > "$credentials"

mysqldump --defaults-extra-file="$credentials" --single-transaction --routines --no-tablespaces \
    "$(env_value DB_DATABASE)" | gzip > "$BACKUP_DIR/database-$STAMP.sql.gz"

if [ -d "$BACKEND/storage/app/private" ]; then
    tar -czf "$BACKUP_DIR/photos-$STAMP.tar.gz" -C "$BACKEND/storage/app" private
fi

find "$BACKUP_DIR" -type f -mtime +"$KEEP_DAYS" -delete
echo "$(date '+%F %T') backup done: $(du -sh "$BACKUP_DIR" | cut -f1) in $BACKUP_DIR"
