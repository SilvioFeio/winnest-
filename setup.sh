#!/usr/bin/env bash
# One-command setup and update for WinNest. Run it with: bash setup.sh   (or: bash setup.sh --local)
# Docker mode: used when this folder sits inside the course Docker env's app/ folder (PHP + MariaDB in containers).
# Local mode: used when there is no Docker env above this folder, or with --local; needs PHP 8.2+ and Composer, uses SQLite.
# Works on macOS and in Git Bash on Windows. Safe to run again: it skips what is done, and import/sync only add or update rows.
set -euo pipefail
export MSYS_NO_PATHCONV=1 # stop Git Bash from turning /app/... into a Windows path

step() { printf '\n==> %s\n' "$1"; }
fail() { printf '\nSetup stopped: %s\n' "$1" >&2; exit 1; }

PROJECT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
MODE=docker
case "${1:-}" in
    "") ;;
    --local) MODE=local ;;
    *) fail "unknown option '$1'. Usage: bash setup.sh [--local]" ;;
esac

# Docker mode: walk up until we reach the Docker env (the folder with docker-compose.yaml); none found means local mode
if [ "$MODE" = docker ]; then
    ENV_ROOT="$PROJECT"
    until [ -f "$ENV_ROOT/docker-compose.yaml" ] || [ -f "$ENV_ROOT/docker-compose.yml" ]; do
        if [ "$ENV_ROOT" = "$(dirname "$ENV_ROOT")" ]; then
            MODE=local
            break
        fi
        ENV_ROOT="$(dirname "$ENV_ROOT")"
    done
fi
echo "Project: $PROJECT"

if [ "$MODE" = docker ]; then
    COMPOSE_NAME=docker-compose.yaml
    [ -f "$ENV_ROOT/$COMPOSE_NAME" ] || COMPOSE_NAME=docker-compose.yml

    # Docker on Windows needs D:/... instead of Git Bash's /d/...; pwd -W gives that, macOS just uses pwd
    host_path() { (cd "$1" && (pwd -W 2>/dev/null || pwd)); }
    COMPOSE_FILE="$(host_path "$ENV_ROOT")/$COMPOSE_NAME"

    # The Docker env mounts its app/ folder at /app, so the container path is /app + our path inside app/
    case "$PROJECT/" in
        "$ENV_ROOT/app/"*) CONTAINER_DIR="/app${PROJECT#"$ENV_ROOT/app"}" ;;
        *) fail "$PROJECT is not inside $ENV_ROOT/app, so the PHP container cannot see it. Move it there, or run: bash setup.sh --local" ;;
    esac
    echo "Mode: Docker (in the PHP container: $CONTAINER_DIR)"

    dc() { docker compose -f "$COMPOSE_FILE" "$@"; }
    artisan() { dc exec -T -w "$CONTAINER_DIR" php php artisan "$@"; }

    dc ps --status running --services | tr -d '\r' | grep -x php >/dev/null \
        || fail "the php container is not running. Start the Docker env first: cd \"$ENV_ROOT\" && docker compose up -d"
else
    echo "Mode: local (PHP on this computer, SQLite database in database/database.sqlite)"

    # Check the tools up front, so setup does not stop halfway with a confusing error
    command -v php >/dev/null || fail "PHP is not installed (or not on PATH). Install PHP 8.2 or newer, then run this again."
    command -v composer >/dev/null || fail "Composer is not installed (or not on PATH). See https://getcomposer.org/download/"
    # tr strips Windows line endings; grep without -q reads all output so pipefail does not see a broken pipe
    has_ext() { php -m 2>/dev/null | tr -d '\r' | grep -ix "$1" >/dev/null; }
    has_ext pdo_sqlite || fail "PHP's SQLite driver is off. In php.ini (php --ini shows where), remove the ; before extension=pdo_sqlite"
    has_ext openssl || fail "PHP's openssl extension is off. In php.ini, remove the ; before extension=openssl"

    artisan() { (cd "$PROJECT" && php artisan "$@"); }
fi

# vendor/ is not in git; Docker mode uses the official Composer image because the php container has no Composer
if [ -f "$PROJECT/vendor/autoload.php" ]; then
    step "PHP packages already installed"
elif [ "$MODE" = docker ]; then
    step "Installing PHP packages (first time only, takes a minute)"
    HOST_DIR="$(host_path "$PROJECT")"
    USER_FLAG=()
    case "$(uname -s)" in MINGW*|MSYS*|CYGWIN*) ;; *) USER_FLAG=(--user "$(id -u):$(id -g)") ;; esac # macOS: keep vendor/ owned by you
    docker run --rm ${USER_FLAG[@]+"${USER_FLAG[@]}"} -v "$HOST_DIR":/app -w /app composer:2 install --no-interaction --no-progress
else
    step "Installing PHP packages (first time only, takes a minute)"
    (cd "$PROJECT" && composer install --no-interaction --no-progress)
fi

# .env is not in git; create it once from .env.example
if [ -f "$PROJECT/.env" ]; then
    step ".env already exists, leaving it as it is"
elif [ "$MODE" = docker ]; then
    step "Creating .env"
    PASSWORD="$(grep -E '^DB_ROOT_PASSWORD=' "$ENV_ROOT/.env" 2>/dev/null | head -1 | cut -d= -f2- | tr -d '"\r' || true)"
    # ENVIRON (not awk -v) so backslashes in the password are copied exactly
    PW="$PASSWORD" awk '/^DB_PASSWORD=/ { print "DB_PASSWORD=" ENVIRON["PW"]; next } { print }' "$PROJECT/.env.example" > "$PROJECT/.env"
    [ -n "$PASSWORD" ] || echo "Could not read DB_ROOT_PASSWORD from $ENV_ROOT/.env; fill in DB_PASSWORD in .env yourself."
else
    step "Creating .env (SQLite)"
    # SQLite needs no server: switch the connection and comment out the MySQL lines, so Laravel uses database/database.sqlite
    awk '/^DB_CONNECTION=/ { print "DB_CONNECTION=sqlite"; next }
         /^DB_(HOST|PORT|DATABASE|USERNAME|PASSWORD)=/ { print "# " $0; next }
         { print }' "$PROJECT/.env.example" > "$PROJECT/.env"
fi

# Local mode keeps the whole database in one file; an .env left over from Docker mode would point at MySQL instead
if [ "$MODE" = local ]; then
    grep -qE '^DB_CONNECTION=mysql' "$PROJECT/.env" \
        && fail ".env has the Docker (MySQL) settings, but local mode uses SQLite. Rename .env to .env.docker and run this again."
    [ -f "$PROJECT/database/database.sqlite" ] || touch "$PROJECT/database/database.sqlite"
fi

# Only generate a key when there is none: a new key would log everyone out and break encrypted data
if grep -qE '^APP_KEY=.+' "$PROJECT/.env"; then
    step "App key already set"
else
    step "Generating app key"
    artisan key:generate
fi

step "Updating the database tables (creates the database if needed)"
artisan migrate --force

step "Importing pigeons, pairs, races and results from database/data"
artisan import:pigeons

# Needs the internet; if oneloftrace.live is down, setup still finishes and you can run this script again later
step "Fetching race results from oneloftrace.live"
artisan olr:sync --season=3335 --race="Algarve Pigeons Golden Race 2026" \
    && artisan olr:sync --season=3172 --race="Mira One Loft Race 2026" \
    || echo "OneLoftRace sync failed (see above). Everything else is ready; run 'bash setup.sh' again later."

if [ "$MODE" = local ]; then
    step "Done. Start the site with: php artisan serve   then open http://127.0.0.1:8000"
    exit 0
fi

# The env's stock web server shares app/public on port 80, which would hand out .env (DB password, app key) to anyone
case "$CONTAINER_DIR" in
    /app/public/*)
        URL="http://localhost${CONTAINER_DIR#/app/public}/.env"
        if [ "$(curl -s -o /dev/null -w '%{http_code}' --max-time 20 "$URL" 2>/dev/null || true)" = "200" ]; then
            printf '\nSECURITY WARNING: %s is publicly readable.\n' "$URL"
            echo "Move WinNest out of app/public (for example to app/winnest), or block it in nginx.conf with:"
            echo "    location ^~ ${CONTAINER_DIR#/app/public} { return 404; }"
        fi
        ;;
esac

step "Done. Open http://localhost:8000"
