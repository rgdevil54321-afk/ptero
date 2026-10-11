#!/usr/bin/env bash
#
# =============================================================================
#  Turtle Game Panel - Uninstaller
# =============================================================================
#  Removes the Turtle Game Panel from this server. By default it:
#    - stops + removes the panel's Supervisor programs (scheduler/queue/discord)
#    - removes the panel's nginx site config
#    - removes the panel files (/var/www/pterodactyl)
#    - stops + removes the local Wings node (service, config, binary) if present
#  and KEEPS the database and server data volumes.
#
#  The database is only dropped when you explicitly confirm it (or pass
#  --purge-db). Wings server volumes (/var/lib/pterodactyl) are only purged
#  after a second confirmation.
#
#  System packages (nginx, MariaDB, PHP, ionCube, Docker) and the ionCube
#  license key are left in place — they may be used by other applications.
#
#  Usage:
#     sudo bash turtle/uninstall.sh
#
#  Options:
#     --panel-path PATH   Panel directory (default: /var/www/pterodactyl)
#     --yes               Do not prompt (keeps DB and volumes)
#     --purge-db          Also drop the panel database + DB user
#     --keep-wings        Leave the local Wings node installed
#     --purge-volumes     Also delete /var/lib/pterodactyl (server data!)
#     -h, --help          Show this help
#
#  Environment overrides (mainly for testing):
#     PANEL_PATH DB_DATABASE DB_USERNAME SUPERVISOR_CONF_DIR
#     NGINX_AVAIL NGINX_ENABLED LOG_DIR DB_CRED_FILE
#     WINGS_BIN WINGS_CFG_DIR WINGS_SERVICE WINGS_DATA_DIR
# =============================================================================

set -uo pipefail

PANEL_PATH="${PANEL_PATH:-/var/www/pterodactyl}"
DB_DATABASE="${DB_DATABASE:-pterodactyl}"
DB_USERNAME="${DB_USERNAME:-pterodactyl}"
SUPERVISOR_CONF_DIR="${SUPERVISOR_CONF_DIR:-/etc/supervisor/conf.d}"
NGINX_AVAIL="${NGINX_AVAIL:-/etc/nginx/sites-available}"
NGINX_ENABLED="${NGINX_ENABLED:-/etc/nginx/sites-enabled}"
LOG_DIR="${LOG_DIR:-/var/log/pterodactyl}"
DB_CRED_FILE="${DB_CRED_FILE:-/root/.turtle-db-credentials}"
WINGS_BIN="${WINGS_BIN:-/usr/local/bin/wings}"
WINGS_CFG_DIR="${WINGS_CFG_DIR:-/etc/pterodactyl}"
WINGS_SERVICE="${WINGS_SERVICE:-/etc/systemd/system/wings.service}"
WINGS_DATA_DIR="${WINGS_DATA_DIR:-/var/lib/pterodactyl}"

FORCE=0
PURGE_DB=0
KEEP_WINGS=0
PURGE_VOLUMES=0

log()  { echo -e "\e[1;34m[turtle]\e[0m $*"; }
warn() { echo -e "\e[1;33m[turtle][warn]\e[0m $*"; }
err()  { echo -e "\e[1;31m[turtle][error]\e[0m $*" >&2; }

usage() {
    sed -n '2,/^set -/p' "${BASH_SOURCE[0]}" \
        | sed -e 's/^# \{0,1\}//' -e '/^set -/d' -e '/^===/d'
}

while [[ "$#" -gt 0 ]]; do
    case "$1" in
        --panel-path)    PANEL_PATH="$2"; shift 2 ;;
        --yes|-y)        FORCE=1; shift ;;
        --purge-db)      PURGE_DB=1; shift ;;
        --keep-wings)    KEEP_WINGS=1; shift ;;
        --purge-volumes) PURGE_VOLUMES=1; shift ;;
        -h|--help)       usage; exit 0 ;;
        *)               err "Unknown option: $1"; usage; exit 1 ;;
    esac
done

require_root() {
    if [[ "$(id -u)" -ne 0 ]]; then
        err "This uninstaller must be run as root. Try: sudo bash turtle/uninstall.sh"
        exit 1
    fi
}

guard_path() {
    case "$PANEL_PATH" in
        ""|"/"|"/var"|"/var/www"|"/usr"|"/usr/local"|"/etc"|"/root"|"$HOME")
            err "Refusing to operate on unsafe path: '${PANEL_PATH}'"
            exit 1
            ;;
    esac
    if [[ "$PANEL_PATH" != */* ]]; then
        err "Refusing to operate on suspicious path: '${PANEL_PATH}'"
        exit 1
    fi
}

# -----------------------------------------------------------------------------
# Steps
# -----------------------------------------------------------------------------
remove_supervisor_programs() {
    log "Removing panel Supervisor programs..."
    if command -v supervisorctl >/dev/null 2>&1; then
        for prog in pterodactyl-scheduler pterodactyl-queue pterodactyl-discord; do
            supervisorctl stop "$prog" >/dev/null 2>&1 || true
        done
    fi
    rm -f "${SUPERVISOR_CONF_DIR}/pterodactyl-scheduler.conf" \
          "${SUPERVISOR_CONF_DIR}/pterodactyl-queue.conf" \
          "${SUPERVISOR_CONF_DIR}/pterodactyl-discord.conf"
    if command -v supervisorctl >/dev/null 2>&1; then
        supervisorctl reread >/dev/null 2>&1 || true
        supervisorctl update >/dev/null 2>&1 || true
    fi
}

remove_nginx_site() {
    log "Removing panel nginx site config..."
    rm -f "${NGINX_AVAIL}/pterodactyl.conf" "${NGINX_ENABLED}/pterodactyl.conf"
    if command -v nginx >/dev/null 2>&1; then
        nginx -t >/dev/null 2>&1 && systemctl reload nginx >/dev/null 2>&1 || \
            warn "nginx reload skipped (check 'nginx -t' manually)."
    fi
}

remove_panel_files() {
    if [[ -d "$PANEL_PATH" ]]; then
        log "Removing panel files at ${PANEL_PATH}..."
        rm -rf "$PANEL_PATH"
    else
        log "Panel directory ${PANEL_PATH} not present - skipping."
    fi
}

remove_wings() {
    local present=0
    [[ -f "$WINGS_SERVICE" ]] && present=1
    [[ -f "$WINGS_CFG_DIR/config.yml" ]] && present=1
    [[ -f "$WINGS_BIN" ]] && present=1

    if [[ "$present" -eq 0 ]]; then
        log "No local Wings node detected - skipping."
        return 0
    fi

    local answer="y"
    if [[ "$KEEP_WINGS" == "1" ]]; then
        log "Keeping the local Wings node (--keep-wings)."
        return 0
    fi
    if [[ "$FORCE" != "1" ]]; then
        echo ""
        read -rp "Remove the local Wings node (service, config, binary)? [Y/n]: " answer
        answer="${answer:-y}"
    fi
    if [[ ! "$answer" =~ ^[Yy]$ ]]; then
        log "Keeping the local Wings node."
        return 0
    fi

    log "Removing the local Wings node..."
    systemctl stop wings >/dev/null 2>&1 || true
    systemctl disable wings >/dev/null 2>&1 || true
    rm -f "$WINGS_SERVICE"
    systemctl daemon-reload >/dev/null 2>&1 || true
    rm -f "$WINGS_BIN"
    rm -rf "$WINGS_CFG_DIR"

    # Server volumes are data - only purge after an explicit confirmation.
    local purge="$PURGE_VOLUMES"
    if [[ -d "$WINGS_DATA_DIR" && "$purge" != "1" && "$FORCE" != "1" ]]; then
        read -rp "Also delete server data volumes in ${WINGS_DATA_DIR}? [y/N]: " purge_input
        [[ "${purge_input:-n}" =~ ^[Yy]$ ]] && purge=1
    fi
    if [[ "$purge" == "1" && -d "$WINGS_DATA_DIR" ]]; then
        warn "Deleting all server data in ${WINGS_DATA_DIR}..."
        rm -rf "$WINGS_DATA_DIR"
    else
        [[ -d "$WINGS_DATA_DIR" ]] && log "Kept server data volumes in ${WINGS_DATA_DIR}."
    fi
}

drop_database() {
    local drop="$PURGE_DB"
    if [[ "$drop" != "1" && "$FORCE" != "1" ]]; then
        echo ""
        read -rp "Also DROP the database '${DB_DATABASE}' and user '${DB_USERNAME}'? [y/N]: " db_input
        [[ "${db_input:-n}" =~ ^[Yy]$ ]] && drop=1
    fi
    if [[ "$drop" != "1" ]]; then
        log "Keeping database '${DB_DATABASE}' and DB user '${DB_USERNAME}'."
        return 0
    fi
    if ! command -v mysql >/dev/null 2>&1; then
        warn "mysql client not found - cannot drop the database automatically."
        return 0
    fi
    log "Dropping database '${DB_DATABASE}' and DB user '${DB_USERNAME}'..."
    mysql -e "DROP DATABASE IF EXISTS \`${DB_DATABASE}\`;" 2>/dev/null || warn "Could not drop database."
    mysql -e "DROP USER IF EXISTS '${DB_USERNAME}'@'127.0.0.1';" 2>/dev/null || true
    mysql -e "DROP USER IF EXISTS '${DB_USERNAME}'@'localhost';" 2>/dev/null || true
    mysql -e "FLUSH PRIVILEGES;" 2>/dev/null || true
    rm -f "$DB_CRED_FILE"
}

# -----------------------------------------------------------------------------
# Main
# -----------------------------------------------------------------------------
require_root
guard_path

echo ""
log "Turtle Game Panel uninstaller"
echo "  Panel path : ${PANEL_PATH}"
echo "  Database   : ${DB_DATABASE} ($([[ "$PURGE_DB" == "1" ]] && echo 'DROP' || echo 'keep'))"
echo "  Local node : $([[ "$KEEP_WINGS" == "1" ]] && echo 'keep' || echo 'remove if present')"
echo ""

if [[ "$FORCE" != "1" ]]; then
    read -rp "Type 'uninstall' to continue (anything else aborts): " CONFIRM
    if [[ "$CONFIRM" != "uninstall" ]]; then
        log "Aborted. Nothing was changed."
        exit 0
    fi
fi

remove_supervisor_programs
remove_nginx_site
remove_panel_files
remove_wings
drop_database

echo ""
echo "=========================================================================="
echo "  Turtle Game Panel uninstalled."
echo "=========================================================================="
echo "  Removed : panel files, nginx site, supervisor programs"
[[ "$KEEP_WINGS" != "1" ]] && echo "  Node    : local Wings node removed (if it was present)"
[[ "$PURGE_DB" != "1" ]] && echo "  Kept    : database '${DB_DATABASE}' (drop it later with --purge-db)"
echo "  Kept    : system packages, ionCube loader + license (${HYPER_IONCUBE_KEY_DIR:-/etc/hyperv2/ioncube})"
echo "=========================================================================="