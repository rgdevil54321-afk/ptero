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
#     sudo bash turtle/install.sh
#
#  Options:
#     --path PATH         Panel install directory  (default: /var/www/pterodactyl)
#     --url URL           Public panel URL         (default: prompt)
#     --no-discord        Do not run the Discord bot
#     --update            Update an existing panel in-place (keeps .env + DB)
#     --skip-prereqs      Skip OS package / PHP install (use on second runs)
#     --skip-nginx        Skip nginx configuration
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

# Internal flags
SCRIPT_VERSION=$(cat "$REPO_ROOT/hyper_version.json" 2>/dev/null | grep -o '"version"[^,]*' | cut -d'"' -f4 || echo "unknown")
LOG_FILE="/var/log/pterodactyl/install.log"

log()  { echo -e "\e[1;34m[turtle]\e[0m $*"; }
warn() { echo -e "\e[1;33m[turtle][warn]\e[0m $*"; }
err()  { echo -e "\e[1;31m[turtle][error]\e[0m $*" >&2; }

usage() {
    sed -n '2,30p' "${BASH_SOURCE[0]}" | sed -e 's/^# \{0,1\}//' -e '/^===/d'
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
        --db-database)  DB_DATABASE="$2"; shift 2 ;;
        --db-username)  DB_USERNAME="$2"; shift 2 ;;
        --db-password)  DB_PASSWORD="$2"; shift 2 ;;
        -h|--help)      usage; exit 0 ;;
        *)              err "Unknown option: $1"; usage; exit 1 ;;
    esac
done

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
        software-properties-common apt-transport-https \
        nginx mariadb-server redis-server supervisor \
        >/dev/null

    if ! command -v add-apt-repository >/dev/null 2>&1; then
        apt-get install -y software-properties-common >/dev/null
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

setup_database() {
    log "Setting up MariaDB database '${DB_DATABASE}' / user '${DB_USERNAME}'..."

    if [[ -z "$DB_PASSWORD" ]]; then
        DB_PASSWORD="$(openssl rand -hex 16)"
        RANDOMIZED=1
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

    log "Database credentials saved to /root/.hyper-db-credentials"
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
    systemctl enable --now redis-server >/dev/null 2>&1 || true

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

install_license_key() {
    log "Installing the Turtle license (ionCube runtime key)..."
    mkdir -p "$HYPER_IONCUBE_KEY_DIR"

    local KEY_SRC="$REPO_ROOT/turtle/license/hyperv2-release.key"
    local MANIFEST_SRC="$REPO_ROOT/turtle/license/hyperv2-ioncube.json"

    if [[ -f "$KEY_SRC" ]]; then
        install -o root -g "$WEB_USER" -m 0640 "$KEY_SRC" "$HYPER_IONCUBE_KEY_DIR/hyperv2-release.key"
    else
        # Fallback: fetch the key from the Hyper license API.
        log "Repo does not ship a license key - fetching from the update API..."
        local API_URL="https://license.dgenx.net/api/v1/update-check?app_id=7c4efcdc-986e-4e85-9b07-328d6ad6db52&file_slug=ioncube-key"
        local DL_URL
        DL_URL="$(curl -fsSL "$API_URL" 2>/dev/null | grep -o '"download_url":"[^"]*"' | head -n1 | cut -d'"' -f4)"
        if [[ -n "$DL_URL" ]]; then
            local TMP_DIR
            TMP_DIR="$(mktemp -d)"
            curl -fsSL "$DL_URL" -o "$TMP_DIR/key.zip"
            unzip -oq "$TMP_DIR/key.zip" -d "$TMP_DIR"
            find "$TMP_DIR" -type f -name 'hyperv2-release.key' -exec install -o root -g "$WEB_USER" -m 0640 {} "$HYPER_IONCUBE_KEY_DIR/hyperv2-release.key" \;
            find "$TMP_DIR" -type f -name 'hyperv2-ioncube.json' -exec install -o root -g "$WEB_USER" -m 0640 {} "$HYPER_IONCUBE_KEY_DIR/hyperv2-ioncube.json" \;
            rm -rf "$TMP_DIR"
        else
            err "Could not obtain the license key. The panel will not start."
            return 1
        fi
    fi

    if [[ -f "$MANIFEST_SRC" ]]; then
        install -o root -g "$WEB_USER" -m 0640 "$MANIFEST_SRC" "$HYPER_IONCUBE_KEY_DIR/hyperv2-ioncube.json"
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
    systemctl enable supervisor >/dev/null 2>&1 || true
    systemctl start supervisor >/dev/null 2>&1 || service supervisor start >/dev/null 2>&1 || true

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
    nginx -t && systemctl reload nginx
}

restart_fpm() {
    log "Restarting PHP-FPM to flush ionCube/OPcache state..."
    systemctl restart php${PHP_VERSION}-fpm >/dev/null 2>&1 || service php${PHP_VERSION}-fpm restart >/dev/null 2>&1 || \
        warn "Could not restart PHP-FPM automatically."
}

finish() {
    echo ""
    echo "=========================================================================="
    echo "  Turtle Game Panel $SCRIPT_VERSION installed successfully!"
    echo "=========================================================================="
    echo ""
    echo "  Panel URL      : ${APP_URL}"
    echo "  Panel path     : ${PANEL_PATH}"
    echo "  DB credentials : /root/.turtle-db-credentials"
    echo ""
    echo "  Next steps:"
    echo "    1. Create your first admin user:"
    echo "         cd ${PANEL_PATH} && php artisan p:user:make"
    echo "    2. Open the panel, log in, and bank on the Turtle theme being live."
    echo "    3. (Optional) Configure a domain + TLS, e.g. with certbot."
    echo "    4. To update the panel later:"
    echo "         cd ${PANEL_PATH} && sudo bash hyper_fetch.sh"
    echo ""
    echo "  Services managed by Supervisor:"
    echo "    - pterodactyl-scheduler (Laravel scheduler)"
    echo "    - pterodactyl-queue     (queue worker)"
    [[ "$DO_DISCORD" == "1" ]] && echo "    - pterodactyl-discord   (Discord bot)"
    echo "=========================================================================="
}

# -----------------------------------------------------------------------------
# Main
# -----------------------------------------------------------------------------
main() {
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

    install_license_key || true
    restart_fpm
    set_permissions
    clear_caches

    configure_scheduler
    configure_queue
    configure_discord
    configure_nginx
    finish
}

main "$@"