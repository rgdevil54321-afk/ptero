#!/usr/bin/env bash
#
# =============================================================================
#  Turtle Game Panel - Playit.gg node bridge (optional module)
# =============================================================================
#  Makes the playit.gg addresses of Minecraft servers visible in the panel.
#
#  Minecraft servers installed from Turtle's Paper egg run the official playit
#  plugin automatically. The plugin creates a tunnel and either uses a key from
#  the panel's key pool or asks playit for one and prints a claim link in the
#  server console. Either way the key ends up in
#      /var/lib/pterodactyl/volumes/<uuid>/plugins/playit-gg/config.yml
#
#  This script installs a small bridge on the node that reads those configs,
#  asks playit for the connectable address and reports it to the panel. The
#  panel mirrors it into the server's address section (allocations.ip_alias) and
#  the PLAYIT_ADDRESS startup variable.
#
#  The panel is NEVER modified by this script. It only writes:
#      /usr/local/bin/turtle-playit-sync
#      /etc/turtle/playit-sync.conf                (chmod 600)
#      /etc/systemd/system/turtle-playit-sync.service
#      /etc/systemd/system/turtle-playit-sync.timer
#  ...and, only with --agent-secret, a standalone playit agent container.
#
#  Usage:
#     sudo bash turtle/install_playit.sh --panel https://panel.example.com \
#          --token <report token from Admin -> Playit>
#
#  Options:
#     --panel URL          Panel base URL                 (required)
#     --token TOKEN        Playit report token            (required)
#     --interval SECONDS   Sync interval, min 60          (default: 120)
#     --provision          Also install the plugin into existing servers that
#                          do not have it yet, and restart them with --restart
#     --restart            Allow the bridge to restart servers it provisions
#     --agent-secret KEY   Also run the standalone playit agent (Docker) with
#                          this secret, for non-plugin game servers
#     --run                Run one sync immediately after installing
#     --status             Show bridge status and the last run result
#     --uninstall          Remove the bridge (and the standalone agent)
#     --yes                Non-interactive
#     -h | --help          This help
#
#  Environment overrides: PANEL_URL PANEL_TOKEN INTERVAL PROVISION RESTART
#                         PLAYIT_AGENT_SECRET
# =============================================================================

set -uo pipefail

PANEL_URL="${PANEL_URL:-}"
PANEL_TOKEN="${PANEL_TOKEN:-}"
INTERVAL="${INTERVAL:-120}"
PROVISION="${PROVISION:-0}"
RESTART="${RESTART:-0}"
PLAYIT_AGENT_SECRET="${PLAYIT_AGENT_SECRET:-}"
ASSUME_YES=0
DO_RUN=0
DO_STATUS=0
DO_UNINSTALL=0

SYSTEMD_DIR="${SYSTEMD_DIR:-/etc/systemd/system}"
BRIDGE_BIN="${PLAYIT_BRIDGE_BIN:-/usr/local/bin/turtle-playit-sync}"
CONF_DIR="${PLAYIT_CONF_DIR:-/etc/turtle}"
CONF_FILE="${PLAYIT_CONF_FILE:-${CONF_DIR}/playit-sync.conf}"
SYNC_SERVICE="${PLAYIT_SYNC_SERVICE:-${SYSTEMD_DIR}/turtle-playit-sync.service}"
SYNC_TIMER="${PLAYIT_SYNC_TIMER:-${SYSTEMD_DIR}/turtle-playit-sync.timer}"
AGENT_SERVICE="${PLAYIT_AGENT_SERVICE:-${SYSTEMD_DIR}/turtle-playit-agent.service}"
VOLUMES_DIR="${TURTLE_VOLUMES:-/var/lib/pterodactyl/volumes}"

log()  { echo -e "\e[1;34m[turtle][playit]\e[0m $*"; }
warn() { echo -e "\e[1;33m[turtle][playit][warn]\e[0m $*"; }
err()  { echo -e "\e[1;31m[turtle][playit][error]\e[0m $*" >&2; }
die()  { err "$*"; exit 1; }

usage() {
    sed -n '4,48p' "${BASH_SOURCE[0]}" \
        | sed -e 's/^#[[:space:]]\{1,2\}//' -e 's/^#$//' -e '/^[[:space:]]*=\{3,\}[[:space:]]*$/d'
}

require_root() {
    if [[ "$(id -u)" -ne 0 ]]; then
        die "This installer must be run as root (use sudo)."
    fi
}

require_linux() {
    if [[ ! -f /etc/os-release ]]; then
        warn "Cannot detect the OS (/etc/os-release not found). Continuing."
        return 0
    fi
    # shellcheck disable=SC1091
    . /etc/os-release
    case "${ID:-}" in
        ubuntu|debian) : ;;
        *) warn "Untested distribution '${ID:-unknown}'. Continuing." ;;
    esac
}

ensure_dependencies() {
    local missing=()
    command -v curl >/dev/null 2>&1 || missing+=(curl)

    if [[ "${#missing[@]}" -gt 0 ]]; then
        log "Installing missing dependencies: ${missing[*]}"
        export DEBIAN_FRONTEND=noninteractive
        apt-get update -y -qq >/dev/null 2>&1 || true
        apt-get install -y -qq "${missing[@]}" >/dev/null 2>&1 || die "Could not install: ${missing[*]}"
    fi

    command -v curl >/dev/null 2>&1 || die "curl is required."
}

parse_args() {
    while [[ "$#" -gt 0 ]]; do
        case "$1" in
            --panel)         PANEL_URL="$2"; shift 2 ;;
            --token)         PANEL_TOKEN="$2"; shift 2 ;;
            --interval)      INTERVAL="$2"; shift 2 ;;
            --provision)     PROVISION=1; shift ;;
            --restart)       RESTART=1; shift ;;
            --agent-secret)  PLAYIT_AGENT_SECRET="$2"; shift 2 ;;
            --run)           DO_RUN=1; shift ;;
            --status)        DO_STATUS=1; shift ;;
            --uninstall)     DO_UNINSTALL=1; shift ;;
            --yes|-y)        ASSUME_YES=1; shift ;;
            -h|--help)       usage; exit 0 ;;
            *)               err "Unknown option: $1"; usage; exit 1 ;;
        esac
    done
}

write_bridge() {
    log "Installing the bridge at ${BRIDGE_BIN}"
    cat > "$BRIDGE_BIN" <<'TURTLE_BRIDGE'
#!/usr/bin/env bash
#
# Turtle Game Panel - playit.gg address bridge.
# Reads the playit plugin config of every server volume, resolves the public
# address through the playit API and reports it to the panel.
#
# This file is generated by turtle/install_playit.sh. Edit at your own risk.
set -uo pipefail

CONF_FILE="${TURTLE_CONF:-/etc/turtle/playit-sync.conf}"
VOLUMES_DIR="${TURTLE_VOLUMES:-/var/lib/pterodactyl/volumes}"
PLAYIT_API="${TURTLE_PLAYIT_API:-https://api.playit.gg}"
WINGS_CONFIG="${TURTLE_WINGS_CONFIG:-/etc/pterodactyl/config.yml}"

PANEL_URL=""
PANEL_TOKEN=""
PROVISION="0"
RESTART="0"

if [[ -r "$CONF_FILE" ]]; then
    # shellcheck disable=SC1090
    . "$CONF_FILE"
fi

log() { echo "[turtle-playit] $*"; }

report() {
    # report UUID ADDRESS
    curl -fsS -m 20 -X POST "${PANEL_URL}/api/public/playit/report" \
        -H "X-Playit-Token: ${PANEL_TOKEN}" \
        -H "Content-Type: application/json" \
        -d "{\"uuid\":\"$1\",\"address\":\"$2\"}" >/dev/null 2>&1
}

claim() {
    # claim UUID -> prints a secret key (may be empty)
    curl -fsS -m 20 "${PANEL_URL}/api/public/playit/claim?uuid=$1" \
        -H "X-Playit-Token: ${PANEL_TOKEN}" 2>/dev/null \
        | sed -n 's/.*"secret"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p' \
        | head -n1
}

extract_secret() {
    # extract_secret FILE -> prints the agent-secret from a plugin config.yml
    awk -F: '
        /^[[:space:]]*agent-secret[[:space:]]*:/ {
            sub(/^[[:space:]]*agent-secret[[:space:]]*:[[:space:]]*/, "", $0)
            gsub(/^["'\'']|["'\''[:space:]]*$/, "", $0)
            print $0
            exit
        }
    ' "$1" 2>/dev/null
}

address_for() {
    # address_for SECRET -> prints the connectable display address (may be empty)
    curl -fsS -m 20 -X POST "${PLAYIT_API}/v1/agents/rundata" \
        -H "Authorization: agent-key $1" \
        -H "Content-Type: application/json" \
        -d '{}' 2>/dev/null \
        | grep -oE '"display_address"[[:space:]]*:[[:space:]]*"[^"]*"' \
        | head -n1 \
        | sed 's/^"display_address"[[:space:]]*:[[:space:]]*"//; s/"$//'
}

wings_power() {
    # wings_power UUID SIGNAL - best effort, only used with --restart/--provision
    [[ -r "$WINGS_CONFIG" ]] || return 0
    local token port host scheme
    token="$(sed -n 's/^[[:space:]]*token:[[:space:]]*//p' "$WINGS_CONFIG" | head -n1)"
    port="$(sed -n 's/^[[:space:]]*port:[[:space:]]*//p' "$WINGS_CONFIG" | head -n1)"
    host="$(sed -n 's/^[[:space:]]*host:[[:space:]]*//p' "$WINGS_CONFIG" | head -n1)"
    [[ -n "$token" ]] || return 0
    [[ -n "$port" ]] || port=8080
    case "${host:-}" in
        0.0.0.0|::|"") host="127.0.0.1" ;;
    esac
    scheme="http"
    sed -n 's/^[[:space:]]*ssl:[[:space:]]*\(true\|false\)/\1/p' "$WINGS_CONFIG" | head -n1 | grep -q true && scheme="https"
    curl -fsS -m 15 -X POST "${scheme}://${host}:${port}/api/servers/$1/power" \
        -H "Authorization: Bearer ${token}" \
        -H "Content-Type: application/json" \
        -d "{\"signal\":\"$2\"}" >/dev/null 2>&1
}

provision() {
    # provision VOLUME UUID
    local vol="$1" uuid="$2" secret
    [[ -d "$vol" ]] || return 1
    # Only touch things that look like a Java server.
    if [[ ! -f "$vol/server.properties" && ! -d "$vol/plugins" ]]; then
        return 1
    fi

    secret="$(claim "$uuid")"
    [[ -n "$secret" ]] || return 1

    mkdir -p "$vol/plugins/playit-gg"

    if ! curl -fsSL -m 60 -o "$vol/plugins/playit-gg/playit-minecraft-plugin.jar.new" \
        "https://github.com/playit-cloud/playit-minecraft-plugin/releases/latest/download/playit-minecraft-plugin.jar" 2>/dev/null; then
        rm -f "$vol/plugins/playit-gg/playit-minecraft-plugin.jar.new"
        return 1
    fi

    mv -f "$vol/plugins/playit-gg/playit-minecraft-plugin.jar.new" \
          "$vol/plugins/playit-gg/playit-minecraft-plugin.jar"
    printf 'mc-timeout-sec: 30\nagent-secret: "%s"\n' "$secret" \
        > "$vol/plugins/playit-gg/config.yml"

    chown -R --reference="$vol" "$vol/plugins" 2>/dev/null || true

    log "provisioned playit for ${uuid}"
    if [[ "${RESTART}" == "1" ]]; then
        wings_power "$uuid" restart && log "restarted ${uuid}"
    fi
    return 0
}

shopt -s nullglob

main() {
    if [[ -z "${PANEL_URL:-}" || -z "${PANEL_TOKEN:-}" ]]; then
        log "PANEL_URL/PANEL_TOKEN not configured in ${CONF_FILE}; nothing to do."
        return 0
    fi

    if [[ ! -d "$VOLUMES_DIR" ]]; then
        log "Volume directory ${VOLUMES_DIR} does not exist; nothing to do."
        return 0
    fi

    local reported=0
    for vol in "${VOLUMES_DIR}"/*; do
        [[ -d "$vol" ]] || continue
        uuid="$(basename "$vol")"
        config="$vol/plugins/playit-gg/config.yml"

        if [[ ! -f "$config" && "${PROVISION}" == "1" ]]; then
            provision "$vol" "$uuid" || true
        fi

        [[ -f "$config" ]] || continue

        secret="$(extract_secret "$config")"
        [[ -n "$secret" ]] || continue

        addr="$(address_for "$secret")"
        [[ -n "$addr" ]] || continue

        if report "$uuid" "$addr"; then
            log "reported ${uuid} -> ${addr}"
            reported=$((reported + 1))
        fi
    done

    log "done (${reported} server(s) reported)"
}

if [[ "${BASH_SOURCE[0]}" == "$0" ]]; then
    main
fi
TURTLE_BRIDGE
    chmod 0755 "$BRIDGE_BIN"
    bash -n "$BRIDGE_BIN" || die "Generated bridge failed its syntax check."
}

write_config() {
    log "Writing ${CONF_FILE}"
    mkdir -p "$CONF_DIR"
    umask 077
    cat > "$CONF_FILE" <<EOF
# Turtle playit.gg bridge configuration. Keep this file private.
PANEL_URL="${PANEL_URL}"
PANEL_TOKEN="${PANEL_TOKEN}"
PROVISION="${PROVISION}"
RESTART="${RESTART}"
EOF
    chmod 600 "$CONF_FILE"
    umask 022
}

write_units() {
    log "Installing systemd service + timer"
    cat > "$SYNC_SERVICE" <<EOF
[Unit]
Description=Turtle Game Panel - playit.gg address bridge
After=network-online.target
Wants=network-online.target

[Service]
Type=oneshot
ExecStart=${BRIDGE_BIN}
EOF

    cat > "$SYNC_TIMER" <<EOF
[Unit]
Description=Periodically report playit.gg addresses to the Turtle Panel

[Timer]
OnBootSec=2min
OnUnitActiveSec=${INTERVAL}s
AccuracySec=30s
Persistent=true

[Install]
WantedBy=timers.target
EOF

    systemctl daemon-reload
    systemctl enable --now turtle-playit-sync.timer >/dev/null 2>&1 \
        || warn "Could not enable the timer automatically; start it with: systemctl enable --now turtle-playit-sync.timer"
}

install_agent() {
    # Optional standalone playit agent, headless, for non-plugin servers.
    local secret="$1"
    command -v docker >/dev/null 2>&1 || { warn "Docker not found; skipping the standalone playit agent."; return 0; }

    if docker ps -a --format '{{.Names}}' | grep -qx 'turtle-playit-agent'; then
        docker rm -f turtle-playit-agent >/dev/null 2>&1 || true
    fi
    docker pull ghcr.io/playit-cloud/playit-agent:latest >/dev/null 2>&1 \
        || { warn "Could not pull the playit agent image; skipping."; return 0; }

    cat > "$AGENT_SERVICE" <<EOF
[Unit]
Description=Turtle Game Panel - standalone playit.gg agent
After=network-online.target docker.service
Requires=docker.service
Wants=network-online.target

[Service]
Restart=always
RestartSec=5
ExecStartPre=-/usr/bin/docker rm -f turtle-playit-agent
ExecStart=/usr/bin/docker run --rm --name turtle-playit-agent --net=host -e SECRET_KEY=${secret} ghcr.io/playit-cloud/playit-agent:latest
ExecStop=/usr/bin/docker stop turtle-playit-agent

[Install]
WantedBy=multi-user.target
EOF

    systemctl daemon-reload
    systemctl enable --now turtle-playit-agent.service >/dev/null 2>&1 \
        || warn "Could not start the standalone playit agent."
}

do_status() {
    if [[ -x "$BRIDGE_BIN" ]]; then
        echo "bridge:            installed (${BRIDGE_BIN})"
    else
        echo "bridge:            not installed"
    fi
    if [[ -r "$CONF_FILE" ]]; then
        echo "config:            ${CONF_FILE}"
        sed -e 's/^PANEL_TOKEN=.*/PANEL_TOKEN="********"/' "$CONF_FILE" | sed 's/^/  /'
    else
        echo "config:            missing"
    fi
    if systemctl list-unit-files 2>/dev/null | grep -q '^turtle-playit-sync.timer'; then
        echo "timer:             $(systemctl is-enabled turtle-playit-sync.timer 2>/dev/null || echo unknown) / $(systemctl is-active turtle-playit-sync.timer 2>/dev/null || echo unknown)"
    fi
    if systemctl list-unit-files 2>/dev/null | grep -q '^turtle-playit-agent.service'; then
        echo "standalone agent:  $(systemctl is-active turtle-playit-agent.service 2>/dev/null || echo unknown)"
    fi
    if [[ -x "$BRIDGE_BIN" ]]; then
        echo "--- last run ---"
        "$BRIDGE_BIN"
    fi
}

do_uninstall() {
    log "Removing the playit bridge"
    systemctl disable --now turtle-playit-sync.timer >/dev/null 2>&1 || true
    systemctl disable --now turtle-playit-agent.service >/dev/null 2>&1 || true
    rm -f "$SYNC_TIMER" "$SYNC_SERVICE" "$AGENT_SERVICE" "$BRIDGE_BIN" "$CONF_FILE"
    rmdir "$CONF_DIR" >/dev/null 2>&1 || true
    command -v docker >/dev/null 2>&1 && docker rm -f turtle-playit-agent >/dev/null 2>&1 || true
    systemctl daemon-reload
    log "Done. Server plugin files under ${VOLUMES_DIR} were left untouched."
}

main() {
    parse_args "$@"

    if [[ "$DO_STATUS" == "1" ]]; then
        do_status
        exit 0
    fi

    require_root

    if [[ "$DO_UNINSTALL" == "1" ]]; then
        do_uninstall
        exit 0
    fi

    require_linux
    ensure_dependencies

    [[ -n "$PANEL_URL" ]] || die "--panel is required (the panel base URL)."
    [[ -n "$PANEL_TOKEN" ]] || die "--token is required (Admin -> Playit -> Report token)."

    # Normalise the panel URL (no trailing slash).
    PANEL_URL="${PANEL_URL%/}"

    if ! [[ "$INTERVAL" =~ ^[0-9]+$ ]] || [[ "$INTERVAL" -lt 60 ]]; then
        warn "Interval must be a number >= 60; using 120."
        INTERVAL=120
    fi

    if ! curl -fsS -m 15 -o /dev/null "${PANEL_URL}/api/public/playit/claim?uuid=00000000-0000-0000-0000-000000000000" \
        -H "X-Playit-Token: ${PANEL_TOKEN}" 2>/dev/null; then
        # 401/404 are fine here; we only reject a missing/unreachable panel.
        warn "Could not reach the panel at ${PANEL_URL} (continuing; the token will be validated on the first run)."
    fi

    write_bridge
    write_config
    write_units

    if [[ -n "$PLAYIT_AGENT_SECRET" ]]; then
        install_agent "$PLAYIT_AGENT_SECRET"
    fi

    if [[ "$DO_RUN" == "1" ]]; then
        log "Running one sync now..."
        "$BRIDGE_BIN" || true
    fi

    log "Playit bridge installed. It will report addresses every ${INTERVAL}s."
    log "Next: create Minecraft servers, or run 'sudo bash turtle/install_playit.sh --provision' to add playit to existing servers."
}

main "$@"
