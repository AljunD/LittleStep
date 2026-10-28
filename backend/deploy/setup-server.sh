#!/usr/bin/env bash
# One-time setup of a fresh Ubuntu 24.04 server for LittleStep (Oracle Cloud, RackNerd, Hostinger VPS, ...).
# Point your domain at the server first, then run:
#   curl -fsSL https://raw.githubusercontent.com/AljunD/LittleStep/main/backend/deploy/setup-server.sh -o setup-server.sh
#   sudo DOMAIN=littlestep.example.com EMAIL=you@example.com bash setup-server.sh
# Safe to run again: existing code, database and .env are kept.
set -euo pipefail

DOMAIN="${DOMAIN:?Set DOMAIN, e.g. DOMAIN=littlestep.example.com}"
EMAIL="${EMAIL:?Set EMAIL (for HTTPS certificate notices)}"
REPO="${REPO:-https://github.com/AljunD/LittleStep.git}"
BRANCH="${BRANCH:-main}"
APP_DIR="${APP_DIR:-/var/www/littlestep}"
DB_NAME="${DB_NAME:-littlestep}"
DB_USER="${DB_USER:-littlestep}"
PHP="8.3"
OWNER="${SUDO_USER:-root}"
BACKEND="$APP_DIR/backend"
ENV_FILE="$BACKEND/.env"

step() { printf '\n\033[36m==> %s\033[0m\n' "$1"; }
warn() { printf '\033[33m[!] %s\033[0m\n' "$1"; }
as_owner() { sudo -u "$OWNER" -H "$@"; }
as_web() { sudo -u www-data "$@"; }

[ "$(id -u)" -eq 0 ] || { echo "Run with sudo."; exit 1; }
. /etc/os-release
[ "${ID:-}" = ubuntu ] && [ "${VERSION_ID:-}" = "24.04" ] || { echo "This script needs Ubuntu 24.04 (it provides PHP 8.3)."; exit 1; }

step "Swap file (only on servers with less than 2 GB of memory)"
mem_mb=$(awk '/MemTotal/ {print int($2 / 1024)}' /proc/meminfo)
if [ "$mem_mb" -lt 2000 ] && [ -z "$(swapon --show)" ]; then
    fallocate -l 2G /swapfile && chmod 600 /swapfile && mkswap /swapfile && swapon /swapfile
    echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi

step "System packages"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get upgrade -y
apt-get install -y nginx mysql-server git unzip curl composer certbot python3-certbot-nginx \
    "php$PHP-fpm" "php$PHP-cli" "php$PHP-mysql" "php$PHP-mbstring" "php$PHP-xml" "php$PHP-curl" \
    "php$PHP-zip" "php$PHP-gd" "php$PHP-intl" "php$PHP-bcmath"
if ! command -v node >/dev/null || [ "$(node -p 'process.versions.node.split(".")[0]')" -lt 22 ]; then
    curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
    apt-get install -y nodejs
fi

step "Firewall: allow web traffic (ports 80 and 443)"
if [ -f /etc/iptables/rules.v4 ] && command -v netfilter-persistent >/dev/null; then
    for port in 443 80; do
        iptables -C INPUT -p tcp --dport "$port" -m state --state NEW -j ACCEPT 2>/dev/null \
            || iptables -I INPUT 1 -p tcp --dport "$port" -m state --state NEW -j ACCEPT
    done
    netfilter-persistent save
fi
if command -v ufw >/dev/null && ufw status | grep -q "Status: active"; then
    ufw allow OpenSSH && ufw allow 'Nginx Full'
fi
warn "Also open ports 80 and 443 in your provider's dashboard if it has its own firewall (Oracle: VCN Security List)."

step "PHP settings (photo uploads, hide version)"
cat > "/etc/php/$PHP/fpm/conf.d/99-littlestep.ini" <<'INI'
upload_max_filesize = 5M
post_max_size = 8M
expose_php = Off
INI

step "Code"
mkdir -p "$(dirname "$APP_DIR")"
if [ ! -d "$APP_DIR/.git" ]; then
    git clone --branch "$BRANCH" "$REPO" "$APP_DIR"
fi
chown -R "$OWNER":www-data "$APP_DIR"

step "Database and .env"
set_env() {
    local key="$1" value="$2"
    if grep -qE "^#? ?$key=" "$ENV_FILE"; then
        sed -i -E "s|^#? ?$key=.*|$key=$value|" "$ENV_FILE"
    else
        printf '%s=%s\n' "$key" "$value" >> "$ENV_FILE"
    fi
}
if [ -f "$ENV_FILE" ]; then
    DB_PASS=$(grep -E '^DB_PASSWORD=' "$ENV_FILE" | cut -d= -f2- | tr -d '"')
    echo "Keeping the existing .env."
else
    DB_PASS=$(openssl rand -base64 32 | tr -dc 'A-Za-z0-9' | head -c 32)
    cp "$BACKEND/.env.example" "$ENV_FILE"
    set_env APP_KEY "base64:$(openssl rand -base64 32)"
    set_env APP_NAME LittleStep
    set_env APP_ENV production
    set_env APP_DEBUG false
    set_env APP_URL "https://$DOMAIN"
    set_env LOG_LEVEL warning
    set_env DB_CONNECTION mysql
    set_env DB_HOST 127.0.0.1
    set_env DB_PORT 3306
    set_env DB_DATABASE "$DB_NAME"
    set_env DB_USERNAME "$DB_USER"
    set_env DB_PASSWORD "$DB_PASS"
    set_env SESSION_DRIVER database
    set_env SESSION_SECURE_COOKIE true
    set_env CACHE_STORE database
    set_env QUEUE_CONNECTION sync
    set_env GUARDIAN_APP_LOGIN_URL littlestep://login
fi
chown "$OWNER":www-data "$ENV_FILE"
chmod 640 "$ENV_FILE"
mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
SQL

step "Install dependencies and build"
cd "$BACKEND"
as_owner composer install --no-dev --optimize-autoloader --no-interaction
as_owner npm ci
as_owner npm run build
chgrp -R www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod g+s {} +
grep -qE '^APP_KEY=.+' "$ENV_FILE" || set_env APP_KEY "base64:$(openssl rand -base64 32)"
as_web php artisan migrate --force
as_web php artisan db:seed --force
as_web php artisan optimize

step "Web server"
cat > /etc/nginx/sites-available/littlestep <<NGINX
server {
    listen 80;
    server_name $DOMAIN;
    root $BACKEND/public;
    index index.php;
    client_max_body_size 8M;
    server_tokens off;

    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php$PHP-fpm.sock;
    }
    location ~ /\.(?!well-known) { deny all; }
}
NGINX
ln -sf /etc/nginx/sites-available/littlestep /etc/nginx/sites-enabled/littlestep
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
systemctl restart "php$PHP-fpm"

step "HTTPS certificate"
if ! certbot --nginx -d "$DOMAIN" -m "$EMAIL" --agree-tos --redirect -n; then
    warn "HTTPS failed. Check that $DOMAIN points to this server's IP, then run:"
    warn "  sudo certbot --nginx -d $DOMAIN -m $EMAIL --agree-tos --redirect"
fi

step "Scheduled tasks (Laravel scheduler + nightly backup)"
cat > /etc/cron.d/littlestep <<CRON
* * * * * www-data cd $BACKEND && php artisan schedule:run >> /dev/null 2>&1
30 2 * * * root bash $BACKEND/deploy/backup.sh >> /var/log/littlestep-backup.log 2>&1
CRON
chmod 644 /etc/cron.d/littlestep

step "Done"
cat <<DONE
LittleStep is running at https://$DOMAIN

Before real use, edit $ENV_FILE (sudo nano $ENV_FILE) and set:
  MAIL_*                      the email account that sends verification links
  TEACHER_REGISTRATION_CODE   the code teachers enter to register (registration is closed without one)
  PRIVACY_CONTROLLER_NAME, PRIVACY_CONTACT_EMAIL, PRIVACY_CONTACT_PHONE   shown on /privacy
then apply the changes with:  sudo -u www-data php $BACKEND/artisan optimize

Database password (also saved in .env): $DB_PASS
Backups: /var/backups/littlestep (nightly at 02:30, kept 14 days). Copy them off this server regularly.
Updates: sudo bash $BACKEND/deploy/deploy.sh
DONE
