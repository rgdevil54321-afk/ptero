#!/bin/bash

set -uo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"
PANEL_PATH="${PANEL_PATH:-$SCRIPT_DIR}"
PARENT_DIR="$(dirname "$PANEL_PATH")"
CHECK_INTERVAL="${CHECK_INTERVAL:-300}"
MIN_UPDATE_INTERVAL="${MIN_UPDATE_INTERVAL:-3600}"
LOG_FILE="${LOG_FILE:-/var/log/pterodactyl/hyper_watcher.log}"
LOCK_FILE="/var/lock/hyper_update.lock"
STATE_DIR="/var/lib/hyper"
UPDATE_SCRIPT="$SCRIPT_DIR/hyper_auto_update.sh"
API_URL="https://license.dgenx.net/api/v1/update-check?app_id=7c4efcdc-986e-4e85-9b07-328d6ad6db52"
BETA_VERSION_URL="https://hyper-r2.dgenx.net/hyperv2/beta/version.json"
HYPER_RELEASE_CHANNEL="${HYPER_RELEASE_CHANNEL:-}"
HYPER_RELEASE_CHANNEL_FROM_ENV=0
if [[ -n "$HYPER_RELEASE_CHANNEL" ]]; then
    HYPER_RELEASE_CHANNEL_FROM_ENV=1
fi

mkdir -p "$STATE_DIR" /var/log/pterodactyl 2>/dev/null || true
touch "$LOG_FILE" 2>/dev/null || true

log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] [watcher] $*" | tee -a "$LOG_FILE"
}


get_version_field() {
    local field="$1"
    local vfile="$PANEL_PATH/hyper_version.json"
    [[ -f "$vfile" ]] || return 1
    php -r '
        $data = json_decode((string) @file_get_contents($argv[2]), true);
        $field = $argv[1] ?? "";
        if (is_array($data) && isset($data[$field]) && is_scalar($data[$field])) echo $data[$field];
    ' "$field" "$vfile" 2>/dev/null || true
}

load_release_channel() {
    if [[ "$HYPER_RELEASE_CHANNEL_FROM_ENV" != "1" ]]; then
        HYPER_RELEASE_CHANNEL=""
    fi

    if [[ -z "${HYPER_RELEASE_CHANNEL:-}" && -f "$PANEL_PATH/.hyper_release_channel" ]]; then
        HYPER_RELEASE_CHANNEL="$(head -n 1 "$PANEL_PATH/.hyper_release_channel" 2>/dev/null || true)"
    fi

    if [[ -z "${HYPER_RELEASE_CHANNEL:-}" ]]; then
        HYPER_RELEASE_CHANNEL="$(get_version_field release_channel)"
    fi

    if [[ -z "${HYPER_RELEASE_CHANNEL:-}" ]]; then
        HYPER_RELEASE_CHANNEL="$(get_version_field channel)"
    fi

    HYPER_RELEASE_CHANNEL="$(printf '%s' "${HYPER_RELEASE_CHANNEL:-stable}" | tr '[:upper:]' '[:lower:]')"
    case "$HYPER_RELEASE_CHANNEL" in
        stable|beta) ;;
            log "Invalid release channel '$HYPER_RELEASE_CHANNEL'; falling back to stable."
            HYPER_RELEASE_CHANNEL="stable"
            ;;
    esac

    printf '%s\n' "$HYPER_RELEASE_CHANNEL" > "$PANEL_PATH/.hyper_release_channel" 2>/dev/null || true
    chown www-data:www-data "$PANEL_PATH/.hyper_release_channel" 2>/dev/null || true
    chmod 0644 "$PANEL_PATH/.hyper_release_channel" 2>/dev/null || true
}

get_remote_version() {
    local response=""
    if [[ "$HYPER_RELEASE_CHANNEL" == "beta" ]]; then
        if command -v curl >/dev/null 2>&1; then
            response=$(curl -fsSL --retry 2 --retry-delay 5 --max-time 20 "$BETA_VERSION_URL" 2>/dev/null) || true
        elif command -v wget >/dev/null 2>&1; then
            response=$(wget -qO- --timeout=20 --tries=2 "$BETA_VERSION_URL" 2>/dev/null) || true
        fi

        if [[ -n "$response" ]]; then
            local beta_version
            beta_version=$(php -r '
                $r = json_decode(trim($argv[1]), true);
                echo is_array($r) && isset($r["version"]) ? trim($r["version"]) : "";
            ' "$response" 2>/dev/null || true)
            if [[ -n "$beta_version" ]]; then
                echo "$beta_version"
                return
            fi
        fi

        log "Beta version metadata unavailable; falling back to stable update API for comparison."
    fi

    if command -v curl >/dev/null 2>&1; then
        response=$(curl -fsSL --retry 2 --retry-delay 5 --max-time 20 "$API_URL" 2>/dev/null) || true
    elif command -v wget >/dev/null 2>&1; then
        response=$(wget -qO- --timeout=20 --tries=2 "$API_URL" 2>/dev/null) || true
    fi
    [[ -z "$response" ]] && return 1
    php -r "\$r=json_decode(trim(\$argv[1]),true); echo isset(\$r['latest_version']['version']) ? trim(\$r['latest_version']['version']) : '';" "$response" 2>/dev/null || true
}

get_local_version() {
    local version
    version="$(get_version_field version)"
    echo "${version:-0.0.0}"
}

version_gt() {
    [[ "$1" != "$2" && "$(printf '%s\n%s' "$1" "$2" | sort -V | tail -1)" == "$1" ]]
}


health_check() {
    [[ -f "$PANEL_PATH/vendor/autoload.php" ]] || { log "  Health: vendor/autoload.php missing"; return 1; }
    php "$PANEL_PATH/artisan" --version >/dev/null 2>&1   || { log "  Health: php artisan failed"; return 1; }
    return 0
}

wait_for_healthy() {
    local max_wait="${1:-120}"
    local waited=0
    while [[ $waited -lt $max_wait ]]; do
        health_check && return 0
        sleep 10
        waited=$((waited + 10))
    done
    log "  Health: still failing after ${max_wait}s"
    return 1
}


do_rollback() {
    local backup_path="$1"
    log "=== AUTO-ROLLBACK INITIATED ==="

    if [[ ! -f "$backup_path" ]]; then
        log "CRITICAL: Rollback backup not found: $backup_path"
        return 1
    fi

    cd "$PARENT_DIR" || { log "CRITICAL: Cannot cd to $PARENT_DIR"; return 1; }

    local broken_dir
    broken_dir="$(basename "$PANEL_PATH")_broken_$(date +%Y%m%d_%H%M%S)"
    if [[ -d "$(basename "$PANEL_PATH")" ]]; then
        mv "$(basename "$PANEL_PATH")" "$broken_dir" && \
            log "Broken panel moved to $PARENT_DIR/$broken_dir" || true
    fi

    log "Extracting backup: $backup_path"
    if ! tar -xzf "$backup_path"; then
        log "CRITICAL: tar extraction failed during rollback — restoring broken dir"
        [[ -d "$broken_dir" ]] && mv "$broken_dir" "$(basename "$PANEL_PATH")" || true
        return 1
    fi

    cd "$PANEL_PATH" || { log "CRITICAL: Panel dir missing after rollback extract"; return 1; }

    export HOME="${HOME:-/root}"
    export COMPOSER_ALLOW_SUPERUSER=1
    log "Rollback: running composer install..."
    composer install --no-dev --optimize-autoloader --no-interaction >/dev/null 2>&1 || \
        log "WARNING: composer install failed during rollback"

    log "Rollback: clearing caches..."
    php "$PANEL_PATH/artisan" config:clear >/dev/null 2>&1 || true
    php "$PANEL_PATH/artisan" cache:clear  >/dev/null 2>&1 || true
    php "$PANEL_PATH/artisan" optimize     >/dev/null 2>&1 || true

    log "Rollback: restarting supervisor processes..."
    supervisorctl restart pterodactyl-discord    >/dev/null 2>&1 || true
    supervisorctl restart pterodactyl-scheduler  >/dev/null 2>&1 || true
    systemctl reload nginx 2>/dev/null || service nginx reload 2>/dev/null || true

    log "=== AUTO-ROLLBACK COMPLETE ==="
    return 0
}


run_update() {
    local remote_version="$1"

    exec 9>"$LOCK_FILE"
    if ! flock -n 9; then
        log "Update lock held by another process. Skipping this cycle."
        return 0
    fi

    local state_file="$STATE_DIR/last_update"
    if [[ -f "$state_file" ]]; then
        local last_ts now elapsed
        last_ts=$(cat "$state_file" 2>/dev/null || echo 0)
        now=$(date +%s)
        elapsed=$((now - last_ts))
        if [[ $elapsed -lt $MIN_UPDATE_INTERVAL ]]; then
            log "Last update was ${elapsed}s ago (min: ${MIN_UPDATE_INTERVAL}s). Skipping."
            exec 9>&-
            return 0
        fi
    fi

    log "=== UPDATE START: local=$(get_local_version) → remote=$remote_version ==="

    date +%s > "$state_file"

    local exit_code=0
    PANEL_PATH="$PANEL_PATH" HYPER_RELEASE_CHANNEL="$HYPER_RELEASE_CHANNEL" SKIP_BACKUP=0 "$UPDATE_SCRIPT" --channel "$HYPER_RELEASE_CHANNEL" >> "$LOG_FILE" 2>&1 || exit_code=$?

    local update_backup=""
    update_backup=$(find "$PARENT_DIR" -maxdepth 1 -name "pterodactyl_backup_*.tar.gz" \
        -newer "$state_file" 2>/dev/null | sort -r | head -1 || echo "")

    if [[ -z "$update_backup" ]]; then
        update_backup=$(find "$PARENT_DIR" -maxdepth 1 -name "pterodactyl_backup_*.tar.gz" \
            2>/dev/null | sort -r | head -1 || echo "")
    fi

    if [[ $exit_code -ne 0 ]]; then
        log "Update script exited with code $exit_code."
        if [[ -n "$update_backup" ]]; then
            do_rollback "$update_backup" && log "Rollback OK." || log "Rollback ALSO FAILED."
        else
            log "No backup found — cannot rollback."
        fi
        exec 9>&-
        return 1
    fi

    log "Update script finished. Checking application health (up to 120s)..."
    if wait_for_healthy 120; then
        log "=== UPDATE SUCCESS: version $remote_version applied and healthy ==="
        echo "$remote_version" > "$STATE_DIR/last_version"
    else
        log "Health check failed after update. Rolling back..."
        if [[ -n "$update_backup" ]]; then
            do_rollback "$update_backup" && log "Rollback OK." || log "Rollback ALSO FAILED."
        else
            log "No backup found — cannot rollback."
        fi
        exec 9>&-
        return 1
    fi

    exec 9>&-
    return 0
}


_shutdown=0
trap '_shutdown=1; log "Shutdown signal received — will exit after current sleep."' SIGTERM SIGINT


log "Hyper auto-updater watcher started."
log "  Panel:              $PANEL_PATH"
log "  Update script:      $UPDATE_SCRIPT"
log "  Check interval:     ${CHECK_INTERVAL}s"
log "  Min update interval: ${MIN_UPDATE_INTERVAL}s"
load_release_channel
log "  Release channel:    ${HYPER_RELEASE_CHANNEL}"

if [[ ! -f "$UPDATE_SCRIPT" ]]; then
    log "ERROR: Update script not found at $UPDATE_SCRIPT. Watcher cannot function."
    exit 1
fi

chmod +x "$UPDATE_SCRIPT" 2>/dev/null || true

while true; do
    if [[ $_shutdown -eq 1 ]]; then
        log "Exiting cleanly."
        exit 0
    fi

    if [[ ! -d "$PANEL_PATH" ]]; then
        log "Panel path $PANEL_PATH not found. Will retry."
        sleep "$CHECK_INTERVAL"
        continue
    fi

    load_release_channel
    remote_ver=$(get_remote_version 2>/dev/null) || remote_ver=""

    if [[ -z "$remote_ver" ]]; then
        log "Could not fetch remote version (API unreachable or empty). Skipping check."
        sleep "$CHECK_INTERVAL"
        continue
    fi

    local_ver=$(get_local_version)
    log "Version check — channel: $HYPER_RELEASE_CHANNEL  local: $local_ver  remote: $remote_ver"

    if version_gt "$remote_ver" "$local_ver"; then
        log "Newer version available: $remote_ver (current: $local_ver)"
        run_update "$remote_ver" || log "Update cycle failed. Will retry next interval."
    fi

    sleep "$CHECK_INTERVAL"
done
