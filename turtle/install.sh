#!/usr/bin/env bash
#
# =============================================================================
#  Turtle Game Panel - One-Command Installer
# =============================================================================
#  Installs the Turtle Game Panel straight from THIS repository.
#
#  This repository already contains the Turtle Game Panel v2.1.2 codebase
#  (Pterodactyl-based, forked from the Hyper Game Panel) with the Turtle
#  theme pre-installed, so there is no separate "theme" step: the panel
#  ships with the theme built in.
#
#  Requirements:
#   - Ubuntu 22.04 / 24.04 x86_64 (Debian is possible with manual PHP setup)
#   - Run as root
#
#  Usage (recommended - clone first, then run):
#     git clone <this-repo> /tmp/ptero
#     cd /tmp/ptero
#     sudo bash turtle/install.sh            # opens the Control Centre menu
#
#  Unattended install (no menu) - pass at least one option, e.g.:
#     sudo bash turtle/install.sh --url https://panel.example.com
#
#  Options:
#     --path PATH         Panel install directory  (default: /var/www/pterodactyl)
#     --url URL           Public panel URL         (default: prompt)
#     --no-discord        Do not run the Discord bot
#     --node              Install a local Wings node after the panel (no prompt)
#     --no-node           Never install a local Wings node (no prompt)
#     --update            Update an existing panel in-place (keeps .env + DB)
#     --skip-prereqs      Skip OS package / PHP install (use on second runs)
#     --skip-nginx        Skip nginx configuration
#     --menu              Force the interactive Control Centre
#     --playit            Install the Playit.gg bridge without prompting
#     --no-playit         Never offer the Playit.gg bridge
#     --playit-provision  Let the bridge auto-install the playit plugin into
#                          existing Java servers (no automatic restart)
#     --admin-email EMAIL      First admin login email (default: admin@<host>)
#     --admin-username USER    First admin username (default: admin)
#     --admin-password PASS    First admin password (default: generated + shown)
#     --no-admin               Do not create an admin user
#     -h, --help          Show this help
#
#  Environment overrides:
#     PANEL_PATH, APP_URL, DB_DATABASE, DB_USERNAME, DB_PASSWORD
#     HYPER_IONCUBE_KEY_DIR (default /etc/hyperv2/ioncube)
# =============================================================================

set -uo pipefail

# -----------------------------------------------------------------------------
# Configuration
# -----------------------------------------------------------------------------
REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PANEL_PATH="${PANEL_PATH:-/var/www/pterodactyl}"
APP_URL="${APP_URL:-}"
DB_DATABASE="${DB_DATABASE:-pterodactyl}"
DB_USERNAME="${DB_USERNAME:-pterodactyl}"
DB_PASSWORD="${DB_PASSWORD:-}"
HYPER_IONCUBE_KEY_DIR="${HYPER_IONCUBE_KEY_DIR:-/etc/hyperv2/ioncube}"
PHP_VERSION="8.4"
WEB_USER="www-data"
DO_DISCORD=1
DO_PREREQS=1
DO_NGINX=1
UPDATE_MODE=0
DO_NODE=2   # Local node: 0 = never, 1 = always, 2 = ask after panel install
DO_PLAYIT=2 # Playit bridge: 0 = never, 1 = always, 2 = ask after panel install
PLAYIT_PROVISION=0 # Let the bridge install the playit plugin into existing servers

# First admin user (created automatically after the database is ready).
ADMIN_EMAIL="${ADMIN_EMAIL:-}"
ADMIN_USERNAME="${ADMIN_USERNAME:-}"
ADMIN_PASSWORD="${ADMIN_PASSWORD:-}"
ADMIN_CREATED=0
DO_ADMIN=1

# Init system: a normal VPS boots with systemd, but many containers (Docker with
# s6/tini as PID 1) do not. When systemd is absent we fall back to SysV init
# scripts / direct commands so services still come up.
HAS_SYSTEMD=0
if [[ -d /run/systemd/system ]] && command -v systemctl >/dev/null 2>&1; then
    HAS_SYSTEMD=1
fi

# Remember whether the caller passed any options. Running with NO options opens
# the interactive Control Centre; any flag (or --menu) drives the installer.
INVOCATION_ARGC=$#
MENU_REQUESTED=0

# Internal flags
SCRIPT_VERSION=$(cat "$REPO_ROOT/hyper_version.json" 2>/dev/null | grep -o '"version"[^,]*' | cut -d'"' -f4 || echo "unknown")
LOG_FILE="/var/log/pterodactyl/install.log"

log()  { echo -e "\e[1;34m[turtle]\e[0m $*"; }
warn() { echo -e "\e[1;33m[turtle][warn]\e[0m $*"; }
err()  { echo -e "\e[1;31m[turtle][error]\e[0m $*" >&2; }

usage() {
    sed -n '2,/^set -/p' "${BASH_SOURCE[0]}" \
        | sed -e 's/^# \{0,1\}//' -e '/^set -/d' -e '/^===/d'
}

# -----------------------------------------------------------------------------
# Argument parsing
# -----------------------------------------------------------------------------
while [[ "$#" -gt 0 ]]; do
    case "$1" in
        --path)         PANEL_PATH="$2"; shift 2 ;;
        --url)          APP_URL="$2"; shift 2 ;;
        --no-discord)   DO_DISCORD=0; shift ;;
        --update)       UPDATE_MODE=1; shift ;;
        --skip-prereqs) DO_PREREQS=0; shift ;;
        --skip-nginx)   DO_NGINX=0; shift ;;
        --node)         DO_NODE=1; shift ;;
        --no-node)      DO_NODE=0; shift ;;
        --playit)       DO_PLAYIT=1; shift ;;
        --no-playit)    DO_PLAYIT=0; shift ;;
        --playit-provision) PLAYIT_PROVISION=1; shift ;;
        --admin-email)     ADMIN_EMAIL="$2"; shift 2 ;;
        --admin-username)  ADMIN_USERNAME="$2"; shift 2 ;;
        --admin-password)  ADMIN_PASSWORD="$2"; shift 2 ;;
        --no-admin)        DO_ADMIN=0; shift ;;
        --menu)         MENU_REQUESTED=1; shift ;;
        --db-database)  DB_DATABASE="$2"; shift 2 ;;
        --db-username)  DB_USERNAME="$2"; shift 2 ;;
        --db-password)  DB_PASSWORD="$2"; shift 2 ;;
        -h|--help)      usage; exit 0 ;;
        *)              err "Unknown option: $1"; usage; exit 1 ;;
    esac
done

# No options at all -> open the interactive Control Centre. Any option (or an
# explicit --menu) drives the installer directly.
if [[ "$INVOCATION_ARGC" -eq 0 || "$MENU_REQUESTED" == "1" ]]; then
    SHOW_MENU=1
else
    SHOW_MENU=0
fi

# -----------------------------------------------------------------------------
# Pre-flight checks
# -----------------------------------------------------------------------------
require_root() {
    if [[ "$(id -u)" -ne 0 ]]; then
        err "This installer must be run as root. Try: sudo bash turtle/install.sh"
        exit 1
    fi
}

require_script_file() {
    # The installer must live inside a checkout of the repo so it can copy
    # the panel code into the install directory.
    if [[ ! -d "$REPO_ROOT/app" || ! -f "$REPO_ROOT/composer.json" ]]; then
        err "Cannot locate the panel code in this checkout ($REPO_ROOT)."
        err "Please run: git clone <this-repo> /tmp/ptero && cd /tmp/ptero && sudo bash turtle/install.sh"
        exit 1
    fi
    log "Repo root: $REPO_ROOT (Turtle Game Panel $SCRIPT_VERSION)"
}

require_os() {
    # The automatic prerequisite path assumes Ubuntu (ondrej/php PPA). Other
    # distros are allowed, but PHP 8.4 + the loader must then be prepared by
    # hand and the installer re-run with --skip-prereqs.
    if [[ -f /etc/os-release ]]; then
        local ID="$(sed -n 's/^ID=//p' /etc/os-release)"
        case "$ID" in
            ubuntu|debian)
                if [[ "$ID" == "debian" ]]; then
                    warn "On Debian the ondrej/php PPA is unavailable."
                    warn "Install PHP 8.4 + the ionCube Loader manually, then re-run with --skip-prereqs."
                fi
                ;;
            *)
                warn "Untested OS family '${ID}' - the automatic package install expects Ubuntu."
                warn "Install nginx, MariaDB, Redis, PHP 8.x + ionCube Loader and Composer yourself,"
                warn "then re-run with --skip-prereqs."
                ;;
        esac
    fi
}

# -----------------------------------------------------------------------------
# OS / prerequisites
# -----------------------------------------------------------------------------
install_prereqs() {
    log "Installing system packages (nginx, MariaDB, Redis, Supervisor, tools)..."

    export DEBIAN_FRONTEND=noninteractive
    apt-get update -y >/dev/null

    apt-get install -y --no-install-recommends \
        curl wget tar unzip git ca-certificates gnupg lsb-release \
        apt-transport-https \
        nginx mariadb-server redis-server supervisor \
        >/dev/null || warn "One or more base packages failed to install; continuing."

    # add-apt-repository is only needed for the ondrej/php PPA (Ubuntu). On
    # Debian it is unavailable, and it must never abort the install above.
    if ! command -v add-apt-repository >/dev/null 2>&1; then
        apt-get install -y software-properties-common >/dev/null 2>&1 || true
    fi

    # PHP (8.4) from the ondrej PPA
    if [[ ! -d /etc/php/${PHP_VERSION} ]]; then
        log "Installing PHP ${PHP_VERSION} (ondrej/php PPA)..."
        LC_ALL=C add-apt-repository -y ppa:ondrej/php >/dev/null 2>&1 || true
        apt-get update -y >/dev/null
    fi

    apt-get install -y --no-install-recommends \
        php${PHP_VERSION}-cli php${PHP_VERSION}-common php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-mysql php${PHP_VERSION}-mbstring php${PHP_VERSION}-xml \
        php${PHP_VERSION}-curl php${PHP_VERSION}-zip php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-gd php${PHP_VERSION}-intl php${PHP_VERSION}-readline \
        >/dev/null

    # Composer
    if ! command -v composer >/dev/null 2>&1; then
        log "Installing Composer..."
        EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
        php -r "copy('https://getcomposer.org/installer', '/tmp/composer-setup.php');"
        ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', '/tmp/composer-setup.php');")"
        if [[ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]]; then
            err "Composer installer checksum mismatch. Aborting."
            exit 1
        fi
        php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer >/dev/null
        rm -f /tmp/composer-setup.php
    fi
}

install_ioncube_loader() {
    log "Installing the ionCube Loader for PHP ${PHP_VERSION} (required to run this panel)..."

    local SRC_DIR=/tmp/ioncube-loader
    local LOADER_DIR=/usr/local/ioncube
    rm -rf "$SRC_DIR"
    mkdir -p "$SRC_DIR" "$LOADER_DIR"

    if ! curl -fsSL --retry 3 "https://downloads.ioncube.com/loader_downloads/ioncube_loaders_lin_x86-64.tar.gz" -o "$SRC_DIR/loaders.tar.gz"; then
        warn "Could not download the ionCube Loader automatically."
        warn "Install it manually from https://get-loader.ioncube.com/ and re-run with --skip-prereqs."
        return 1
    fi

    tar -xzf "$SRC_DIR/loaders.tar.gz" -C "$SRC_DIR"
    local LOADER_BIN
    LOADER_BIN="$(find "$SRC_DIR" -name "ioncube_loader_lin_${PHP_VERSION}.so" | head -n1)"
    if [[ -z "$LOADER_BIN" ]]; then
        err "No ionCube loader found for PHP ${PHP_VERSION} in the downloaded archive."
        return 1
    fi
    install -m 0644 "$LOADER_BIN" "$LOADER_DIR/ioncube_loader_lin_${PHP_VERSION}.so"

    # Enable for both CLI and FPM, and disable the PHP 8 JIT (required by ionCube).
    for scope in cli fpm; do
        local INI_DIR="/etc/php/${PHP_VERSION}/${scope}/conf.d"
        mkdir -p "$INI_DIR"
        cat > "$INI_DIR/00-ioncube.ini" <<EOF
zend_extension=${LOADER_DIR}/ioncube_loader_lin_${PHP_VERSION}.so
opcache.jit_buffer_size=0
EOF
    done

    # Validate the loader loads under the CLI.
    if php -v 2>/dev/null | grep -qi "ionCube"; then
        log "ionCube Loader enabled (PHP ${PHP_VERSION})."
    else
        warn "ionCube Loader file installed, but 'php -v' did not report it."
        warn "Double-check the PHP ini scan dir: $(php --ini 2>/dev/null | grep 'Scan for additional' || true)"
    fi
}

# -----------------------------------------------------------------------------
# Service management (systemd on a VPS, SysV init scripts inside containers)
# -----------------------------------------------------------------------------
svc_start() {
    local name="$1"
    if [[ "$HAS_SYSTEMD" == "1" ]]; then
        systemctl enable --now "$name" >/dev/null 2>&1 && return 0
    fi
    service "$name" start >/dev/null 2>&1 && return 0
    if [[ -x "/etc/init.d/$name" ]]; then
        "/etc/init.d/$name" start >/dev/null 2>&1 && return 0
    fi
    return 1
}

svc_reload() {
    local name="$1"
    if [[ "$HAS_SYSTEMD" == "1" ]]; then
        systemctl reload "$name" >/dev/null 2>&1 && return 0
    fi
    service "$name" reload >/dev/null 2>&1 && return 0
    if [[ -x "/etc/init.d/$name" ]]; then
        "/etc/init.d/$name" reload >/dev/null 2>&1 && return 0
    fi
    return 1
}

svc_restart() {
    local name="$1"
    if [[ "$HAS_SYSTEMD" == "1" ]]; then
        systemctl restart "$name" >/dev/null 2>&1 && return 0
    fi
    service "$name" restart >/dev/null 2>&1 && return 0
    if [[ -x "/etc/init.d/$name" ]]; then
        "/etc/init.d/$name" restart >/dev/null 2>&1 && return 0
    fi
    return 1
}

svc_stop() {
    local name="$1"
    if [[ "$HAS_SYSTEMD" == "1" ]]; then
        systemctl stop "$name" >/dev/null 2>&1 && return 0
    fi
    service "$name" stop >/dev/null 2>&1 && return 0
    if [[ -x "/etc/init.d/$name" ]]; then
        "/etc/init.d/$name" stop >/dev/null 2>&1 && return 0
    fi
    return 1
}

# True when the MariaDB/MySQL client can reach a running server.
db_ready() { mysql -e 'SELECT 1' >/dev/null 2>&1; }

ensure_mariadb() {
    # A normal VPS has MariaDB started by its package postinstall; inside a
    # container it is often installed but never started, so start it here.
    db_ready && return 0

    log "MariaDB is not reachable - attempting to start it..."
    if [[ "$HAS_SYSTEMD" == "1" ]]; then
        systemctl enable --now mariadb >/dev/null 2>&1 \
            || systemctl enable --now mysql >/dev/null 2>&1 || true
    fi
    db_ready || svc_start mariadb || svc_start mysql || true

    local i=0
    while [[ "$i" -lt 15 ]]; do
        db_ready && return 0
        sleep 1
        i=$((i + 1))
    done
    return 1
}

setup_database() {
    log "Setting up MariaDB database '${DB_DATABASE}' / user '${DB_USERNAME}'..."

    if [[ -z "$DB_PASSWORD" ]]; then
        DB_PASSWORD="$(openssl rand -hex 16)"
        RANDOMIZED=1
    fi

    if ! ensure_mariadb; then
        err "MariaDB/MySQL is not running, so the panel database cannot be created."
        err "Install and start it first, for example:"
        err "    apt-get update && apt-get install -y mariadb-server"
        err "    service mariadb start   # containers without systemd"
        exit 1
    fi

    mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -e "CREATE USER IF NOT EXISTS '${DB_USERNAME}'@'127.0.0.1' IDENTIFIED BY '${DB_PASSWORD}';"
    mysql -e "CREATE USER IF NOT EXISTS '${DB_USERNAME}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
    mysql -e "GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'127.0.0.1';"
    mysql -e "GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'localhost';"
    mysql -e "FLUSH PRIVILEGES;"

    # Save the credentials for reference.
    cat > /root/.turtle-db-credentials <<EOF
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}
EOF
    chmod 600 /root/.turtle-db-credentials

    log "Database credentials saved to /root/.turtle-db-credentials"
}

deploy_files() {
    log "Deploying panel files to ${PANEL_PATH}..."
    mkdir -p "$PANEL_PATH"

    if [[ "$PANEL_PATH" == "$REPO_ROOT" ]]; then
        log "Already inside the repo checkout - no file copy needed."
        return
    fi

    # Copy the whole repo (excluding .git and the installer docs) via tar,
    # so there is no rsync dependency.
    ( cd "$REPO_ROOT" && tar --exclude='.git' --exclude='turtle' -cf - . ) | ( cd "$PANEL_PATH" && tar -xf - )
}

ensure_storage() {
    # Laravel needs these directories to boot (the Hyper release does not ship
    # runtime files). The repo carries the skeleton via .gitignore files; this
    # guarantees it also exists on upgraded installs.
    mkdir -p \
        "$PANEL_PATH/storage/app/public" \
        "$PANEL_PATH/storage/framework/cache/data" \
        "$PANEL_PATH/storage/framework/sessions" \
        "$PANEL_PATH/storage/framework/views" \
        "$PANEL_PATH/storage/logs" \
        "$PANEL_PATH/bootstrap/cache"

    # Purge compiled caches left behind by a previous install or the base image.
    # A stale bootstrap/cache/config.php can reference packages that a
    # production vendor/ tree does not contain (e.g. the dev-only
    # barryvdh/laravel-ide-helper), which makes every `php artisan` call die
    # with "Class ... not found". The committed hyperv2_security_manifest.php is
    # intentionally preserved.
    rm -f \
        "$PANEL_PATH/bootstrap/cache/config.php" \
        "$PANEL_PATH/bootstrap/cache/packages.php" \
        "$PANEL_PATH/bootstrap/cache/services.php" \
        "$PANEL_PATH/bootstrap/cache/events.php" \
        "$PANEL_PATH"/bootstrap/cache/routes-*.php 2>/dev/null || true
}

write_env() {
    log "Configuring .env..."

    local ENV_FILE="$PANEL_PATH/.env"
    if [[ ! -f "$ENV_FILE" ]]; then
        cp "$PANEL_PATH/.env.example" "$ENV_FILE"
    fi

    local HASHIDS_SALT="$(awk -F= '/^HASHIDS_SALT=/{print $2}' "$ENV_FILE" 2>/dev/null)"
    if [[ -z "$HASHIDS_SALT" ]]; then
        HASHIDS_SALT="$(openssl rand -hex 16)"
        sed -i "s/^HASHIDS_SALT=.*/HASHIDS_SALT=${HASHIDS_SALT}/" "$ENV_FILE"
    fi

    local SET_APP_URL="${APP_URL}"
    if [[ -z "$SET_APP_URL" ]]; then
        local IP
        IP="$(hostname -I 2>/dev/null | awk '{print $1}')"
        local HOSTNAME_SHORT
        HOSTNAME_SHORT="$(hostname)"
        echo ""
        read -rp "Panel URL [http://${HOSTNAME_SHORT}] ($IP): " SET_APP_URL
        SET_APP_URL="${SET_APP_URL:-http://${HOSTNAME_SHORT}}"
    fi

    sed -i "s|^APP_URL=.*|APP_URL=${SET_APP_URL}|" "$ENV_FILE"
    sed -i "s/^DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE}/" "$ENV_FILE"
    sed -i "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME}/" "$ENV_FILE"
    sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" "$ENV_FILE"

    # Queue worker + Redis defaults are already correct in .env.example
    # (QUEUE_CONNECTION=redis); make sure Redis is started.
    svc_start redis-server || true

    cd "$PANEL_PATH"
    php artisan key:generate --force --no-interaction
}

install_deps() {
    log "Installing PHP dependencies via Composer (this can take a few minutes)..."
    cd "$PANEL_PATH"
    export COMPOSER_ALLOW_SUPERUSER=1

    # Install from the locked dependency set first; then fill in any extras
    # the Hyper panel needs that are not part of the lock file.
    composer install --no-dev --optimize-autoloader --no-interaction || {
        err "composer install failed. Check the output above."
        exit 1
    }

    for PKG in intervention/image laragear/webauthn laravel/socialite socialiteproviders/whmcs team-reflex/discord-php; do
        composer show "$PKG" >/dev/null 2>&1 || \
            composer require "$PKG" --no-interaction --with-all-dependencies >/dev/null 2>&1 || \
            warn "Could not install optional package ${PKG}."
    done

    # Make sure the Turtle addon namespace (composer.json PSR-4 "Turtle\\" =>
    # "turtleapp/") is in the generated autoloader.
    composer dump-autoload --optimize --no-dev --no-interaction >/dev/null 2>&1 || \
        warn "composer dump-autoload failed; the Playit admin page may be unavailable until it runs."
}

migrate_db() {
    log "Running database migrations + seeders..."
    cd "$PANEL_PATH"

    php artisan migrate --force

    log "Migrating legacy hyperv1 settings to Turtle V2 (non-fatal if it fails)..."
    php artisan hyperv2:migrate-legacy-settings || warn "Legacy settings migration skipped."

    # Seed only the Turtle nests/eggs (avoids the default Pterodactyl set).
    if [[ -f database/Seeders/NestSeeder.php ]]; then
        cp database/Seeders/NestSeeder.php database/Seeders/NestSeeder.php.bak
        sed -i 's/\$this->createMinecraftNest/\/\/$this->createMinecraftNest/g' database/Seeders/NestSeeder.php
        sed -i 's/\$this->createSourceEngineNest/\/\/$this->createSourceEngineNest/g' database/Seeders/NestSeeder.php
        sed -i 's/\$this->createVoiceServersNest/\/\/$this->createVoiceServersNest/g' database/Seeders/NestSeeder.php
        sed -i 's/\$this->createRustNest/\/\/$this->createRustNest/g' database/Seeders/NestSeeder.php
    fi
    if [[ -f database/Seeders/EggSeeder.php ]]; then
        cp database/Seeders/EggSeeder.php database/Seeders/EggSeeder.php.bak
        sed -i "/'Minecraft',/d" database/Seeders/EggSeeder.php
        sed -i "/'Source Engine',/d" database/Seeders/EggSeeder.php
        sed -i "/'Voice Servers',/d" database/Seeders/EggSeeder.php
        sed -i "/'Rust',/d" database/Seeders/EggSeeder.php
    fi

    php artisan db:seed --class=NestSeeder --force --no-interaction || warn "NestSeeder skipped (data may already exist)."
    php artisan db:seed --class=EggSeeder --force --no-interaction  || warn "EggSeeder skipped (data may already exist)."

    mv -f database/Seeders/NestSeeder.php.bak database/Seeders/NestSeeder.php 2>/dev/null || true
    mv -f database/Seeders/EggSeeder.php.bak database/Seeders/EggSeeder.php 2>/dev/null || true
}

create_admin_user() {
    # Creates the first admin account non-interactively so a fresh install is
    # ready to log into. Idempotent: does nothing if an admin already exists.
    if [[ "$DO_ADMIN" != "1" ]]; then
        return 0
    fi

    cd "$PANEL_PATH" || return 0

    local existing
    existing="$(php artisan tinker \
        --execute='echo "TURTLEADMINCOUNT=".\Pterodactyl\Models\User::where("root_admin", true)->count();' \
        2>/dev/null | grep -oE 'TURTLEADMINCOUNT=[0-9]+' | head -n1 | cut -d= -f2)"
    if [[ -n "$existing" && "$existing" != "0" ]]; then
        log "An admin user already exists; skipping admin creation."
        ADMIN_CREATED=0
        return 0
    fi

    # Derive sensible defaults from the panel URL / hostname.
    local url host
    url="$(panel_app_url)"
    host="${url#http://}"; host="${host#https://}"
    host="${host%%/*}"; host="${host%%:*}"
    : "${ADMIN_USERNAME:=admin}"
    : "${ADMIN_EMAIL:=admin@${host:-localhost}}"
    local first="${ADMIN_FIRST:-Panel}"
    local last="${ADMIN_LAST:-Admin}"

    if [[ -z "$ADMIN_PASSWORD" ]]; then
        ADMIN_PASSWORD="$(openssl rand -base64 24 | tr -dc 'A-Za-z0-9' | head -c 20)"
    fi

    log "Creating the first admin user (${ADMIN_EMAIL})..."
    if php artisan p:user:make \
            --email="$ADMIN_EMAIL" \
            --username="$ADMIN_USERNAME" \
            --name-first="$first" \
            --name-last="$last" \
            --password="$ADMIN_PASSWORD" \
            --admin=1 >/dev/null 2>&1; then
        ADMIN_CREATED=1
    else
        warn "Could not create the admin user automatically."
        warn "Create it by hand: cd ${PANEL_PATH} && php artisan p:user:make"
        ADMIN_CREATED=0
    fi
}

install_license_key() {
    # The panel PHP is ionCube-encoded. The loader needs the runtime key on disk
    # BEFORE anything boots the app (artisan key:generate, composer's
    # post-autoload-dump -> package:discover, migrations). Installing it later is
    # what made fresh installs die with "an encoding key was not found".
    mkdir -p "$HYPER_IONCUBE_KEY_DIR"

    # www-data exists once nginx is installed; fall back to root if not (e.g.
    # --skip-prereqs on a bare box) so the key is never skipped.
    local KEY_GROUP="$WEB_USER"
    getent group "$KEY_GROUP" >/dev/null 2>&1 || KEY_GROUP="root"

    local KEY_SRC="$REPO_ROOT/turtle/license/hyperv2-release.key"
    local MANIFEST_SRC="$REPO_ROOT/turtle/license/hyperv2-ioncube.json"
    local KEY_DST="$HYPER_IONCUBE_KEY_DIR/hyperv2-release.key"
    local MANIFEST_DST="$HYPER_IONCUBE_KEY_DIR/hyperv2-ioncube.json"

    if [[ -s "$KEY_SRC" ]]; then
        log "Installing the Turtle license (ionCube runtime key)..."
        install -o root -g "$KEY_GROUP" -m 0640 "$KEY_SRC" "$KEY_DST"
    elif [[ -s "$KEY_DST" ]]; then
        # In-place update where the checkout does not carry the key: keep the
        # one already installed rather than failing the update.
        log "License key already present at $KEY_DST - keeping it."
    else
        # Fallback: fetch the key from the Hyper license API.
        log "Repo does not ship a license key - fetching from the update API..."
        local API_URL="https://license.dgenx.net/api/v1/update-check?app_id=7c4efcdc-986e-4e85-9b07-328d6ad6db52&file_slug=ioncube-key"
        local DL_URL
        DL_URL="$(curl -fsSL "$API_URL" 2>/dev/null | grep -o '"download_url":"[^"]*"' | head -n1 | cut -d'"' -f4)"
        if [[ -n "$DL_URL" ]]; then
            local TMP_DIR
            TMP_DIR="$(mktemp -d)"
            if curl -fsSL "$DL_URL" -o "$TMP_DIR/key.zip" && unzip -oq "$TMP_DIR/key.zip" -d "$TMP_DIR"; then
                find "$TMP_DIR" -type f -name 'hyperv2-release.key' -exec install -o root -g "$KEY_GROUP" -m 0640 {} "$KEY_DST" \;
                find "$TMP_DIR" -type f -name 'hyperv2-ioncube.json' -exec install -o root -g "$KEY_GROUP" -m 0640 {} "$MANIFEST_DST" \;
            fi
            rm -rf "$TMP_DIR"
        fi
    fi

    if [[ -s "$MANIFEST_SRC" ]]; then
        install -o root -g "$KEY_GROUP" -m 0640 "$MANIFEST_SRC" "$MANIFEST_DST"
    fi

    if [[ ! -s "$KEY_DST" ]]; then
        err "Could not obtain the ionCube license key. The panel will not start."
        return 1
    fi

    log "License key installed to $HYPER_IONCUBE_KEY_DIR"
}

set_permissions() {
    log "Setting file permissions..."
    chown -R ${WEB_USER}:${WEB_USER} "$PANEL_PATH"/{app,bootstrap,config,database,public,resources,routes,storage} 2>/dev/null || true
    chmod -R 755 "$PANEL_PATH"/storage/* "$PANEL_PATH"/bootstrap/cache/ 2>/dev/null || true
    chmod +x "$PANEL_PATH/hyper_fetch.sh" 2>/dev/null || true
    chmod +x "$PANEL_PATH/hyper_watcher.sh" 2>/dev/null || true
}

clear_caches() {
    log "Clearing + rebuilding Laravel caches..."
    cd "$PANEL_PATH"
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    php artisan config:cache
    php artisan event:cache
    php artisan view:cache
    php artisan route:cache || php artisan route:clear
    php artisan queue:restart || true
}

configure_scheduler() {
    log "Configuring Laravel scheduler via Supervisor..."

    # Remove any legacy cron entries running the scheduler.
    for user in root www-data nginx apache; do
        if id "$user" >/dev/null 2>&1 || [[ "$user" == "root" ]]; then
            if [[ "$user" == "root" ]]; then
                crontab -l 2>/dev/null | grep -Ev "artisan[[:space:]]+schedule:run" | crontab - 2>/dev/null || true
            else
                crontab -u "$user" -l 2>/dev/null | grep -Ev "artisan[[:space:]]+schedule:run" | crontab -u "$user" - 2>/dev/null || true
            fi
        fi
    done

    if ! command -v supervisord >/dev/null 2>&1; then
        apt-get install -y supervisor >/dev/null
    fi
    if [[ "$HAS_SYSTEMD" == "1" ]]; then
        systemctl enable supervisor >/dev/null 2>&1 || true
    fi
    svc_start supervisor || true

    cat > /etc/supervisor/conf.d/pterodactyl-scheduler.conf <<EOF
[program:pterodactyl-scheduler]
command=php ${PANEL_PATH}/artisan schedule:work
directory=${PANEL_PATH}
user=${WEB_USER}
autostart=true
autorestart=true
startretries=3
stopasgroup=true
killasgroup=true
stderr_logfile=/var/log/pterodactyl/scheduler.err.log
stdout_logfile=/dev/null
EOF

    mkdir -p /var/log/pterodactyl
    chown ${WEB_USER}:${WEB_USER} /var/log/pterodactyl

    supervisorctl reread >/dev/null 2>&1 || true
    supervisorctl update >/dev/null 2>&1 || true
    supervisorctl start pterodactyl-scheduler >/dev/null 2>&1 || true
}

configure_queue() {
    cat > /etc/supervisor/conf.d/pterodactyl-queue.conf <<EOF
[program:pterodactyl-queue]
command=php ${PANEL_PATH}/artisan queue:work --queue=high,standard,default,low --sleep=3 --tries=3 --timeout=90 --memory=256
directory=${PANEL_PATH}
user=${WEB_USER}
autostart=true
autorestart=true
startretries=3
stopasgroup=true
killasgroup=true
stderr_logfile=/var/log/pterodactyl/queue.err.log
stdout_logfile=/dev/null
EOF
    supervisorctl reread >/dev/null 2>&1 || true
    supervisorctl update >/dev/null 2>&1 || true
    supervisorctl start pterodactyl-queue >/dev/null 2>&1 || true
}

configure_discord() {
    if [[ "$DO_DISCORD" != "1" ]]; then
        return
    fi
    cat > /etc/supervisor/conf.d/pterodactyl-discord.conf <<EOF
[program:pterodactyl-discord]
command=php ${PANEL_PATH}/artisan DGEN:discord:run
directory=${PANEL_PATH}
user=${WEB_USER}
autostart=true
autorestart=true
startretries=3
stopasgroup=true
killasgroup=true
stderr_logfile=/var/log/pterodactyl/discord-bot.err.log
stdout_logfile=/var/log/pterodactyl/discord-bot.out.log
EOF
    supervisorctl reread >/dev/null 2>&1 || true
    supervisorctl update >/dev/null 2>&1 || true
    supervisorctl start pterodactyl-discord >/dev/null 2>&1 || true
    log "Discord bot supervisor configured (DGEN:discord:run)."
}

configure_nginx() {
    if [[ "$DO_NGINX" != "1" ]]; then
        return
    fi
    log "Configuring nginx..."

    cat > /etc/nginx/sites-available/pterodactyl.conf <<EOF
server {
    listen 80;
    server_name _;

    root ${PANEL_PATH}/public;
    index index.html index.php;

    client_max_body_size 100m;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param PATH_INFO \$fastcgi_path_info;
    }
}
EOF

    ln -sf /etc/nginx/sites-available/pterodactyl.conf /etc/nginx/sites-enabled/pterodactyl.conf
    rm -f /etc/nginx/sites-enabled/default
    if nginx -t; then
        svc_reload nginx || svc_restart nginx || nginx -s reload >/dev/null 2>&1 || \
            warn "Could not reload nginx automatically; reload it manually."
    fi
}

restart_fpm() {
    log "Restarting PHP-FPM to flush ionCube/OPcache state..."
    svc_restart "php${PHP_VERSION}-fpm" || \
        warn "Could not restart PHP-FPM automatically."
}

finish() {
    echo ""
    echo "=========================================================================="
    echo "  Turtle Game Panel $SCRIPT_VERSION installed successfully!"
    echo "=========================================================================="
    echo ""
    echo "  Panel URL      : $(panel_app_url)"
    echo "  Panel path     : ${PANEL_PATH}"
    echo "  DB credentials : /root/.turtle-db-credentials"
    echo ""
    if [[ "${ADMIN_CREATED:-0}" == "1" ]]; then
        echo "  First admin login (change the password after logging in):"
        echo "      URL      : $(panel_app_url)"
        echo "      Email    : ${ADMIN_EMAIL}"
        echo "      Username : ${ADMIN_USERNAME}"
        echo "      Password : ${ADMIN_PASSWORD}"
    else
        echo "  Next steps:"
        echo "    1. Create your first admin user:"
        echo "         cd ${PANEL_PATH} && php artisan p:user:make"
    fi
    echo ""
    echo "  After logging in, check Settings -> Theme for the Turtle look."
    echo "  (Optional) Configure a domain + TLS, e.g. with certbot."
    echo "  To update the panel later:"
    echo "      cd ${PANEL_PATH} && sudo bash hyper_fetch.sh"
    echo ""
    echo "  Services managed by Supervisor:"
    echo "    - pterodactyl-scheduler (Laravel scheduler)"
    echo "    - pterodactyl-queue     (queue worker)"
    [[ "$DO_DISCORD" == "1" ]] && echo "    - pterodactyl-discord   (Discord bot)"
    echo "=========================================================================="
}

# -----------------------------------------------------------------------------
# Local Wings node (optional)
# -----------------------------------------------------------------------------
node_install_step() {
    local INSTALLER="$REPO_ROOT/turtle/install_node.sh"
    if [[ ! -f "$INSTALLER" ]]; then
        warn "Skipping local node install: turtle/install_node.sh is missing."
        return 0
    fi

    # --node / --no-node force the decision; otherwise ask interactively.
    local CHOICE="n"
    if [[ "$DO_NODE" == "1" ]]; then
        CHOICE="y"
    elif [[ "$DO_NODE" == "0" ]]; then
        return 0
    else
        echo ""
        log "Optional local node:"
        echo "  Would you like to install a Local Node on this server?"
        echo "  [Y] Yes - Install and connect a local Wings node (Docker + Wings)."
        echo "  [N] No  - Finish the panel installation without a node."
        read -rp "Choice [y/N]: " CHOICE_INPUT
        CHOICE="${CHOICE_INPUT:-n}"
    fi

    if [[ "$CHOICE" =~ ^[Yy]$ ]]; then
        log "Installing a local Wings node (turtle/install_node.sh)..."
        bash "$INSTALLER" --panel-path "$PANEL_PATH"
    else
        log "Skipping local node installation. To install it later run:"
        log "  sudo bash turtle/install_node.sh --panel-path ${PANEL_PATH}"
    fi
}

# -----------------------------------------------------------------------------
# Control Centre helpers
# -----------------------------------------------------------------------------
PANEL_SERVICES=(nginx "php${PHP_VERSION}-fpm" mariadb redis-server supervisor)

service_state() {
    # Prints: active | inactive | not-installed
    local unit="$1"
    if [[ "$HAS_SYSTEMD" == "1" ]]; then
        if systemctl is-active --quiet "$unit" 2>/dev/null; then
            echo "active"
        elif systemctl list-unit-files "${unit}.service" --no-legend 2>/dev/null | grep -q .; then
            echo "inactive"
        else
            echo "not-installed"
        fi
        return
    fi
    # No systemd: use the SysV init script (or the binary) instead.
    if [[ -x "/etc/init.d/$unit" ]]; then
        if service "$unit" status >/dev/null 2>&1; then echo "active"; else echo "inactive"; fi
    elif command -v "$unit" >/dev/null 2>&1; then
        echo "installed"
    else
        echo "not-installed"
    fi
}

control_service() {
    local action="$1" unit="$2"
    case "$action" in
        start)   svc_start "$unit"   || warn "Could not start ${unit}." ;;
        stop)    svc_stop "$unit"    || warn "Could not stop ${unit}." ;;
        restart) svc_restart "$unit" || warn "Could not restart ${unit}." ;;
        *)       systemctl "$action" "$unit" >/dev/null 2>&1 \
                     || service "$unit" "$action" >/dev/null 2>&1 \
                     || warn "Could not ${action} ${unit}." ;;
    esac
}

services_menu() {
    while true; do
        echo ""
        log "Services"
        echo "  1) Start all"
        echo "  2) Stop all"
        echo "  3) Restart all"
        echo "  4) Status"
        echo "  5) Restart panel workers (supervisorctl)"
        echo "  0) Back"
        read -rp "  Select: " SUBCHOICE
        case "$SUBCHOICE" in
            1) for s in "${PANEL_SERVICES[@]}"; do control_service start "$s"; done; log "Services started." ;;
            2) for s in "${PANEL_SERVICES[@]}"; do control_service stop "$s"; done; log "Services stopped." ;;
            3) for s in "${PANEL_SERVICES[@]}"; do control_service restart "$s"; done; log "Services restarted." ;;
            4) for s in "${PANEL_SERVICES[@]}"; do printf "  %-24s %s\n" "$s" "$(service_state "$s")"; done ;;
            5) supervisorctl restart all >/dev/null 2>&1 && log "Workers restarted." || warn "supervisorctl not available." ;;
            0|"") return ;;
            *) warn "Invalid option." ;;
        esac
    done
}

health_check() {
    echo ""
    log "Health check"
    echo "  --- system services ---"
    for s in "${PANEL_SERVICES[@]}"; do
        printf "  %-24s %s\n" "$s" "$(service_state "$s")"
    done

    echo "  --- panel ---"
    if [[ -f "$PANEL_PATH/artisan" ]]; then
        local code
        code="$(curl -s -o /dev/null -w '%{http_code}' "http://127.0.0.1/" 2>/dev/null || echo '000')"
        printf "  %-24s %s\n" "http://127.0.0.1/" "$code"
        ( cd "$PANEL_PATH" && php artisan --version 2>/dev/null | head -n1 ) || true
    else
        warn "Panel not found at ${PANEL_PATH}."
    fi

    if [[ -f /etc/pterodactyl/config.yml ]]; then
        echo "  --- local node ---"
        printf "  %-24s %s\n" "wings" "$(service_state wings)"
        local token port
        token="$(sed -n 's/^token: *//p' /etc/pterodactyl/config.yml 2>/dev/null | head -n1)"
        port="$(sed -n 's/^ *port: *//p' /etc/pterodactyl/config.yml 2>/dev/null | head -n1)"
        [[ -n "$port" ]] && printf "  %-24s %s\n" "wings api" \
            "$(curl -s -o /dev/null -w '%{http_code}' -H "Authorization: Bearer ${token}" "http://127.0.0.1:${port}/api/system" 2>/dev/null || echo '000')"
    fi
    echo ""
}

show_logs() {
    echo ""
    log "Logs"
    echo "  1) Laravel (storage/logs/laravel.log)"
    echo "  2) Scheduler"
    echo "  3) Queue worker"
    echo "  4) Discord bot"
    echo "  5) nginx error"
    echo "  0) Back"
    read -rp "  Select: " SUBCHOICE
    case "$SUBCHOICE" in
        1) tail -n 60 "$PANEL_PATH/storage/logs/laravel.log" 2>/dev/null || warn "No Laravel log yet." ;;
        2) tail -n 40 /var/log/pterodactyl/scheduler.err.log 2>/dev/null || warn "No scheduler log yet." ;;
        3) tail -n 40 /var/log/pterodactyl/queue.err.log 2>/dev/null || warn "No queue log yet." ;;
        4) tail -n 40 /var/log/pterodactyl/discord-bot.out.log 2>/dev/null || warn "No discord log yet." ;;
        5) tail -n 40 /var/log/nginx/error.log 2>/dev/null || warn "No nginx error log." ;;
        *) return ;;
    esac
    echo ""
}

panel_info() {
    echo ""
    log "Panel info"
    echo "  Version    : ${SCRIPT_VERSION}"
    echo "  Repo       : ${REPO_ROOT}"
    echo "  Panel path : ${PANEL_PATH}"
    if [[ -f "$PANEL_PATH/.env" ]]; then
        local url
        url="$(awk -F= '/^APP_URL=/{print $2}' "$PANEL_PATH/.env" 2>/dev/null)"
        echo "  APP_URL    : ${url:-<unset>}"
    fi
    [[ -f /root/.turtle-db-credentials ]] && echo "  DB creds   : /root/.turtle-db-credentials"
    if [[ -f /etc/pterodactyl/config.yml ]]; then
        echo "  Local node : configured"
    else
        echo "  Local node : not configured"
    fi
    echo ""
}

# -----------------------------------------------------------------------------
# Playit.gg (optional)
# -----------------------------------------------------------------------------
panel_app_url() {
    if [[ -n "$APP_URL" ]]; then
        echo "$APP_URL"
        return 0
    fi
    if [[ -f "$PANEL_PATH/.env" ]]; then
        awk -F= '/^APP_URL=/{print $2}' "$PANEL_PATH/.env" 2>/dev/null | tr -d '"' | head -n1
    fi
}

playit_token() {
    # Ensure and print the shared report token (48 hex chars).
    if [[ ! -f "$PANEL_PATH/artisan" ]]; then
        return 0
    fi
    ( cd "$PANEL_PATH" && php artisan tinker --execute="echo app(\Turtle\Playit\PlayitService::class)->ensureReportToken();" 2>/dev/null ) \
        | grep -oE '[0-9a-f]{48}' | head -n1
}

playit_install_bridge() {
    local installer="$REPO_ROOT/turtle/install_playit.sh"
    local url token provision=""
    url="$(panel_app_url)"
    token="$(playit_token)"

    if [[ -z "$token" ]]; then
        err "Could not create the Playit report token. Run the panel once (php artisan migrate), then try again."
        return 1
    fi
    if [[ -z "$url" ]]; then
        err "The panel URL is unknown. Pass --url at install time, or set APP_URL in ${PANEL_PATH}/.env."
        return 1
    fi

    [[ "$PLAYIT_PROVISION" == "1" ]] && provision="--provision"

    log "Report token : ${token}"
    log "Panel URL    : ${url}"
    log "Installing the Playit bridge (turtle/install_playit.sh)..."
    bash "$installer" --panel "$url" --token "$token" --run $provision
}

playit_step() {
    local installer="$REPO_ROOT/turtle/install_playit.sh"
    if [[ ! -f "$installer" ]]; then
        warn "Skipping Playit integration: turtle/install_playit.sh is missing."
        return 0
    fi
    if [[ ! -f "$PANEL_PATH/artisan" ]]; then
        warn "Skipping Playit integration: panel not found at ${PANEL_PATH}."
        return 0
    fi

    local token url choice="n" choice_input
    token="$(playit_token)"
    url="$(panel_app_url)"

    echo ""
    if [[ -z "$token" ]]; then
        warn "Playit tables are not present yet; re-run the installer and try again."
        warn "Later: sudo bash turtle/install_playit.sh --panel <url> --token <token>"
        return 0
    fi

    log "Playit.gg integration"
    echo "  Report token : ${token}"
    echo "  Panel URL    : ${url:-<unknown>}"
    echo ""

    if [[ "$DO_PLAYIT" == "1" ]]; then
        choice="y"
    elif [[ "$DO_PLAYIT" == "0" ]]; then
        log "Skipping the on-node Playit bridge (--no-playit)."
        log "  Later: sudo bash turtle/install_playit.sh --panel ${url:-<url>} --token ${token}"
        return 0
    else
        echo "  Install the Playit bridge on this machine now? This makes playit.gg"
        echo "  addresses show up in the panel for Minecraft servers."
        echo "  [Y] Yes - install the bridge (recommended)."
        echo "  [N] No  - do it later."
        read -rp "Choice [y/N]: " choice_input
        choice="${choice_input:-n}"
    fi

    if [[ "$choice" =~ ^[Yy]$ ]]; then
        playit_install_bridge
    else
        log "Later: sudo bash turtle/install_playit.sh --panel ${url:-<url>} --token ${token}"
    fi
}

playit_menu() {
    while true; do
        echo ""
        log "Playit.gg"
        echo "  1) Install / refresh the node bridge"
        echo "  2) Show the report token + bridge command"
        echo "  3) Bridge status"
        echo "  4) Uninstall the node bridge"
        echo "  0) Back"
        read -rp "  Select: " SUBCHOICE
        case "$SUBCHOICE" in
            1) playit_install_bridge ;;
            2)
                local token url
                token="$(playit_token)"
                url="$(panel_app_url)"
                echo "  Report token : ${token:-<not created>}"
                echo "  Panel URL    : ${url:-<unknown>}"
                echo "  Bridge cmd   : sudo bash turtle/install_playit.sh --panel ${url:-<url>} --token ${token:-<token>}"
                ;;
            3) systemctl status turtle-playit-sync.timer --no-pager 2>/dev/null || warn "Bridge timer not installed." ;;
            4) bash "$REPO_ROOT/turtle/install_playit.sh" --uninstall ;;
            0|"") return ;;
            *) warn "Invalid option." ;;
        esac
    done
}

command_centre() {
    require_root
    require_script_file

    while true; do
        echo ""
        echo "=========================================================================="
        echo "  Turtle Game Panel - Control Centre   v${SCRIPT_VERSION}"
        echo "=========================================================================="
        echo "  1) Install panel        (fresh install)"
        echo "  2) Update panel         (in place; keeps .env + database)"
        echo "  3) Install / manage local Wings node"
        echo "  4) Uninstall panel      (removes files + services; keeps database)"
        echo "  5) Services             (start / stop / restart / status)"
        echo "  6) Health check"
        echo "  7) View logs"
        echo "  8) Panel info"
        echo "  9) Playit.gg        (public addresses for Minecraft servers)"
        echo "  0) Exit"
        echo "=========================================================================="
        read -rp "Select an option: " CHOICE
        echo ""
        case "$CHOICE" in
            1) UPDATE_MODE=0; run_install ;;
            2) UPDATE_MODE=1; run_install ;;
            3)
                if [[ -f "$REPO_ROOT/turtle/install_node.sh" ]]; then
                    bash "$REPO_ROOT/turtle/install_node.sh" --panel-path "$PANEL_PATH"
                else
                    err "turtle/install_node.sh not found."
                fi
                ;;
            4)
                if [[ -f "$REPO_ROOT/turtle/uninstall.sh" ]]; then
                    bash "$REPO_ROOT/turtle/uninstall.sh" --panel-path "$PANEL_PATH"
                else
                    err "turtle/uninstall.sh not found."
                fi
                ;;
            5) services_menu ;;
            6) health_check ;;
            7) show_logs ;;
            8) panel_info ;;
            9) playit_menu ;;
            0|q|Q|exit) log "Bye."; break ;;
            *) warn "Invalid option." ;;
        esac
    done
}

# -----------------------------------------------------------------------------
# Main
# -----------------------------------------------------------------------------
run_install() {
    require_root
    require_script_file
    require_os
    echo ""
    log "Turtle Game Panel installer - version ${SCRIPT_VERSION}"
    log "Repository : ${REPO_ROOT}"
    log "Install to : ${PANEL_PATH}"
    log "Mode       : $([[ "$UPDATE_MODE" == "1" ]] && echo UPDATE || echo FRESH)"
    echo ""

    if [[ "$DO_PREREQS" == "1" ]]; then
        install_prereqs
        install_ioncube_loader || true
    fi

    # The ionCube key has to be on disk before anything boots the app, otherwise
    # the encoded files cannot be decoded and composer/artisan abort. Do it first
    # and treat failure as fatal (the panel cannot run without it).
    install_license_key || {
        err "Aborting: the panel cannot run without its ionCube license key."
        exit 1
    }

    if [[ "$UPDATE_MODE" == "1" ]]; then
        deploy_files
        ensure_storage
        install_deps
        migrate_db
    else
        deploy_files
        ensure_storage
        setup_database
        write_env
        install_deps
        migrate_db
    fi

    # Never pretend to succeed on an incomplete checkout: without artisan (and
    # bootstrap/app.php) every artisan/composer step above silently no-ops.
    if [[ ! -f "$PANEL_PATH/artisan" || ! -f "$PANEL_PATH/bootstrap/app.php" ]]; then
        err "Panel code is incomplete: missing artisan and/or bootstrap/app.php in ${PANEL_PATH}."
        err "This means the checked-out repository is not the full panel. Aborting."
        exit 1
    fi

    create_admin_user

    restart_fpm
    set_permissions
    clear_caches

    configure_scheduler
    configure_queue
    configure_discord
    configure_nginx
    finish

    node_install_step
    playit_step
}

main() {
    if [[ "${SHOW_MENU:-0}" == "1" ]]; then
        command_centre
    else
        run_install
    fi
}

main