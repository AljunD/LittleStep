#!/bin/sh
# LittleStep: bring this computer's setup up to date after new code arrives
# (git pull, VS Code Sync/Pull, GitHub Desktop, or switching branches).
# Called by post-merge, post-rewrite and post-checkout with: <old commit> <new commit>.
# Turned on by `composer install` / `npm install` (git config core.hooksPath .githooks).
# Never fails the pull: a step that can't run prints what to do instead.

old="$1"
new="$2"
[ -z "$old" ] || [ -z "$new" ] || [ "$old" = "$new" ] && exit 0

changed=$(git diff --name-only "$old" "$new" 2>/dev/null)
[ -z "$changed" ] && exit 0

root=$(git rev-parse --show-toplevel)
has() { printf '%s\n' "$changed" | grep -qE "$1"; }
step() { printf '\n\033[36m[littlestep]\033[0m %s\n' "$1"; }
warn() { printf '\033[33m[littlestep] %s\033[0m\n' "$1"; }

if has '^backend/'; then
    cd "$root/backend" || exit 0

    # Settings added to .env.example are copied into .env with their example value, so new features don't crash.
    if [ -f .env ] && [ -f .env.example ]; then
        missing=$(tr -d '\r' < .env.example | grep -E '^[A-Z0-9_]+=' | while IFS= read -r line; do
            key=${line%%=*}
            grep -qE "^$key=" .env || printf '%s\n' "$line"
        done)
        if [ -n "$missing" ]; then
            step 'New settings in .env.example: adding them to .env'
            [ -n "$(tail -c 1 .env)" ] && printf '\n' >> .env
            printf '\n# Added automatically after git pull (from .env.example); adjust if needed.\n%s\n' "$missing" >> .env
            printf '%s\n' "$missing" | sed 's/=.*//; s/^/  + /'
        fi
    elif [ ! -f .env ]; then
        warn 'backend/.env is missing. Run "composer setup" in backend/.'
    fi

    if has '^backend/composer\.(json|lock)$' || [ ! -d vendor ]; then
        step 'PHP packages changed: composer install'
        composer install --no-interaction || warn 'composer install failed. Run it yourself in backend/.'
    fi

    if has '^backend/package(-lock)?\.json$' || [ ! -d node_modules ]; then
        step 'Backend npm packages changed: npm install'
        npm install --no-audit --no-fund || warn 'npm install failed. Run it yourself in backend/.'
    fi

    if has '^backend/(resources/|vite\.config|package(-lock)?\.json|postcss|tailwind)' || [ ! -f public/build/manifest.json ]; then
        step 'Design files changed: rebuilding styles (npm run build)'
        npm run build || warn 'npm run build failed. Run it yourself in backend/.'
    fi

    if has '^backend/database/migrations/'; then
        step 'New database changes: php artisan migrate'
        php artisan migrate --no-interaction || warn 'php artisan migrate failed (is MySQL running in Laragon?). Run it yourself in backend/.'
    fi

    has '^backend/(config/|\.env)' && php artisan config:clear >/dev/null 2>&1
    has '^backend/routes/' && php artisan route:clear >/dev/null 2>&1
    php artisan view:clear >/dev/null 2>&1
fi

if has '^mobile/package(-lock)?\.json$'; then
    cd "$root/mobile" || exit 0
    step 'Mobile packages changed: npm install'
    npm install --no-audit --no-fund || warn 'npm install failed. Run it yourself in mobile/.'
fi

step 'Up to date. Hard-refresh the browser (Ctrl+F5) to see design changes.'
exit 0
