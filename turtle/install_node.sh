#!/usr/bin/env bash
#
# =============================================================================
#  Turtle Game Panel - Local Wings Node Installer (optional module)
# =============================================================================
#  Installs a real, registered Pterodactyl/Wings node on the same machine as
#  the panel. Run AFTER the panel (turtle/install.sh) has been installed.
#
#  The node is created through the official Pterodactyl Application API. A
#  temporary Application API key is minted automatically for the install and
#  deleted again on exit; it is never stored on disk or written into the
#  repository.
#
#  What this script does:
#    1. Pre-flight checks (root, OS, panel health, disk space, free ports)
#    2. Installs / verifies Docker
#    3. Obtains an Application API key automatically and validates it
#    4. Finds or creates a Location and the "Local Node" through the API
#    5. Creates default allocations for the node
#    6. Fetches the official node configuration and writes it securely to
#       /etc/pterodactyl/config.yml (backing up any existing file)
#    7. Installs the official Wings binary (GitHub releases), pinned to the
#       latest v1.x release compatible with the panel
#    8. Installs the Wings systemd service, starts it and enables it
#    9. Verifies the node is genuinely reachable using the SAME check the
#       panel admin UI uses (GET {scheme}://{fqdn}:{daemon_port}/api/system)
#
#  The panel is NEVER modified or removed by this script. If the node
#  installation fails, the panel is left exactly as it was.
#
#  Usage:
#     sudo bash turtle/install_node.sh --panel-path /var/www/pterodactyl
#     sudo bash turtle/install_node.sh --local     # no FQDN needed (same box)
#
#  API key: by default the installer mints a temporary Application API key
#  through the panel (READ+WRITE on nodes/locations/allocations) and deletes it
#  again on exit, so no key has to be created by hand. Pass --no-auto-key to be
#  prompted instead, or supply your own key with --api-key.
#
#  Options:
#     --panel-path PATH   Panel directory   (default: /var/www/pterodactyl)
#     --url URL           Panel URL         (default: read from panel .env)
#     --api-key KEY       Application API key (never stored; auto-minted if omitted)
#     --no-auto-key       Do not auto-mint a key; prompt for one instead
#     --fqdn HOST         Node FQDN/IP      (default: server primary IP)
#     --scheme http|https                  (default: http)
#     --local             Local node mode  - no public FQDN needed; the panel
#                         reaches Wings at 127.0.0.1 and allocations bind 0.0.0.0
#     --node-name NAME    Node name         (default: "Local Node")
#     --ports RANGE       Allocation ports, e.g. 25565-25570 (default)
#     --memory MB         Node memory       (default: detected system memory)
#     --disk MB           Node disk         (default: detected disk)
#     --wings-version TAG Wings release tag (default: latest v1.x)
#     --yes               Non-interactive; use env vars / defaults
#
#  Environment overrides:
#     PANEL_PATH APP_URL PTERODACTYL_API_KEY NODE_ALLOW_AUTO_KEY
#     NODE_NAME NODE_FQDN NODE_SCHEME NODE_LOCAL NODE_ALLOC_IP
#     NODE_LOCATION_SHORT NODE_LOCATION_ID NODE_PORTS NODE_MEMORY NODE_DISK
#     NODE_DAEMON_BASE NODE_DAEMON_LISTEN NODE_DAEMON_SFTP NODE_BEHIND_PROXY
#     WINGS_VERSION WINGS_REMOTE_URL
# =============================================================================

set -uo pipefail

# -----------------------------------------------------------------------------
# Config
# -----------------------------------------------------------------------------
NODE_PANEL_PATH="${PANEL_PATH:-/var/www/pterodactyl}"
NODE_URL="${APP_URL:-}"
NODE_API_KEY="${PTERODACTYL_API_KEY:-}"
NODE_ALLOW_AUTO_KEY="${NODE_ALLOW_AUTO_KEY:-1}"
NODE_KEY_CREATED=0
NODE_KEY_IDENT=""
NODE_NAME="${NODE_NAME:-Local Node}"
NODE_FQDN="${NODE_FQDN:-}"
NODE_SCHEME="${NODE_SCHEME:-}"
NODE_LOCAL="${NODE_LOCAL:-0}"
NODE_ALLOC_IP="${NODE_ALLOC_IP:-}"
NODE_LOCATION_SHORT="${NODE_LOCATION_SHORT:-Local}"
NODE_LOCATION_ID="${NODE_LOCATION_ID:-}"
NODE_DESCRIPTION="${NODE_DESCRIPTION:-Installed by the Turtle node installer}"
NODE_PUBLIC="${NODE_PUBLIC:-true}"
NODE_BEHIND_PROXY="${NODE_BEHIND_PROXY:-false}"
NODE_MEMORY="${NODE_MEMORY:-}"
NODE_MEMORY_OVERALLOCATE="${NODE_MEMORY_OVERALLOCATE:-0}"
NODE_DISK="${NODE_DISK:-}"
NODE_DISK_OVERALLOCATE="${NODE_DISK_OVERALLOCATE:-0}"
NODE_DAEMON_BASE="${NODE_DAEMON_BASE:-/var/lib/pterodactyl/volumes}"
NODE_DAEMON_LISTEN="${NODE_DAEMON_LISTEN:-8080}"
NODE_DAEMON_SFTP="${NODE_DAEMON_SFTP:-2022}"
NODE_UPLOAD_SIZE="${NODE_UPLOAD_SIZE:-100}"
NODE_PORTS="${NODE_PORTS:-25565-25570}"
WINGS_VERSION="${WINGS_VERSION:-}"
WINGS_REMOTE_URL="${WINGS_REMOTE_URL:-}"
WINGS_BIN="${WINGS_BIN:-/usr/local/bin/wings}"
WINGS_CFG_DIR="${WINGS_CFG_DIR:-/etc/pterodactyl}"
WINGS_CFG="${WINGS_CFG_DIR}/config.yml"
WINGS_SERVICE="${WINGS_SERVICE:-/etc/systemd/system/wings.service}"
WINGS_LOG="${WINGS_LOG:-/var/log/pterodactyl/wings.log}"
NODE_YES=0

API_HTTP_CODE=""
API_BODY=""
TMP="$(mktemp -d)"

cleanup() {
    # Remove any API key this run minted, then drop scratch space. Guarded so an
    # early exit (bad flag) cannot error out inside the trap.
    if declare -F delete_application_api_key >/dev/null 2>&1; then
        delete_application_api_key
    fi
    rm -rf "$TMP"
}
trap cleanup EXIT

log()  { echo -e "\e[1;34m[turtle][node]\e[0m $*"; }
warn() { echo -e "\e[1;33m[turtle][node][warn]\e[0m $*"; }
err()  { echo -e "\e[1;31m[turtle][node][error]\e[0m $*" >&2; }

usage() {
    # Print the header comment block: from just under the top border through the
    # line above the closing border (skips the title-box separators).
    awk '
        NR == 1 { next }
        /^# =+$/ { n++; if (n == 3) exit; next }
        n >= 1 { sub(/^# ?/, ""); print }
    ' "${BASH_SOURCE[0]}"
}

need() {
    # need DESCRIPTION VALUE
    if [[ -z "$2" ]]; then
        err "$1 is required but was not provided."
        exit 1
    fi
}

# -----------------------------------------------------------------------------
# Argument parsing
# -----------------------------------------------------------------------------
while [[ "$#" -gt 0 ]]; do
    case "$1" in
        --panel-path)   NODE_PANEL_PATH="$2"; shift 2 ;;
        --url)          NODE_URL="$2"; shift 2 ;;
        --api-key)      NODE_API_KEY="$2"; shift 2 ;;
        --fqdn)         NODE_FQDN="$2"; shift 2 ;;
        --scheme)       NODE_SCHEME="$2"; shift 2 ;;
        --local)        NODE_LOCAL=1; shift ;;
        --node-name)    NODE_NAME="$2"; shift 2 ;;
        --ports)        NODE_PORTS="$2"; shift 2 ;;
        --memory)       NODE_MEMORY="$2"; shift 2 ;;
        --disk)         NODE_DISK="$2"; shift 2 ;;
        --wings-version) WINGS_VERSION="$2"; shift 2 ;;
        --remote-url)   WINGS_REMOTE_URL="$2"; shift 2 ;;
        --yes)          NODE_YES=1; shift ;;
        --no-auto-key)  NODE_ALLOW_AUTO_KEY=0; shift ;;
        -h|--help)      usage; exit 0 ;;
        *)              err "Unknown option: $1"; usage; exit 1 ;;
    esac
done

# -----------------------------------------------------------------------------
# Small JSON helpers (php-cli ships with the panel server)
# -----------------------------------------------------------------------------
# php_json <expression>  -- reads a JSON document on stdin, echoes the value at
#                          the dot-path expression, or exits 3 when missing.
php_json() {
    php -r '
        $j = json_decode(stream_get_contents(STDIN), true);
        if ($j === null) { fwrite(STDERR, "invalid-json\n"); exit 2; }
        $cur = $j;
        foreach (explode(".", $argv[1]) as $k) {
            if (is_array($cur) && array_key_exists($k, $cur)) { $cur = $cur[$k]; }
            else { exit 3; }
        }
        if (is_bool($cur)) { echo $cur ? "true" : "false"; }
        elseif (is_array($cur)) { echo json_encode($cur); }
        else { echo (string) $cur; }
    ' "$1" 2>/dev/null
}

# json_enc <value>  -- safely JSON-encodes a string for use in API payloads.
json_enc() {
    php -r 'echo json_encode($argv[1], JSON_UNESCAPED_SLASHES);' "$1" 2>/dev/null
}

# -----------------------------------------------------------------------------
# Application API helpers
# -----------------------------------------------------------------------------
api_call() {
    # api_call METHOD PATH [DATA]  -- calls the panel Application API.
    local method="$1" path="$2" data="${3:-}"
    local headers=(-H "Authorization: Bearer ${NODE_API_KEY}"
                  -H "Accept: application/vnd.pterodactyl.v1+json"
                  -H "Content-Type: application/json")
    local args=(-sS --max-time 30 -o "$TMP/api_body" -w '%{http_code}' -X "$method" "${NODE_API_BASE}${path}" "${headers[@]}")
    if [[ -n "$data" ]]; then
        args+=(-d "$data")
    fi
    API_HTTP_CODE="$(curl "${args[@]}" 2>"$TMP/api_err" || echo "000")"
    API_BODY="$(cat "$TMP/api_body" 2>/dev/null || true)"
    if [[ "$API_HTTP_CODE" == "000" ]]; then
        warn "API request to ${path} failed: $(head -c 300 "$TMP/api_err" 2>/dev/null || echo 'connection error')"
    fi
}

# -----------------------------------------------------------------------------
# Pre-flight checks
# -----------------------------------------------------------------------------
check_root() {
    if [[ "$(id -u)" -ne 0 ]]; then
        err "This installer must be run as root. Try: sudo bash turtle/install_node.sh"
        exit 1
    fi
}

check_os() {
    if [[ -f /etc/os-release ]]; then
        local ID="$(sed -n 's/^ID=//p' /etc/os-release)"
        case "$ID" in
            ubuntu|debian)
                log "OS check passed (${ID})."
                ;;
            *)
                warn "Untested OS family '${ID}'. Wings officially supports Ubuntu/Debian."
                warn "Continuing, but the automatic Docker/package steps may need adjusting."
                ;;
        esac
    fi
}

check_panel() {
    # The panel must already be installed and serving the API base URL.
    if [[ ! -d "$NODE_PANEL_PATH" || ! -f "$NODE_PANEL_PATH/artisan" ]]; then
        err "No panel found at ${NODE_PANEL_PATH}."
        err "Install the panel first with: sudo bash turtle/install.sh"
        exit 1
    fi

    if [[ -z "$NODE_URL" ]]; then
        local env_url
        env_url="$(grep -E '^APP_URL=' "$NODE_PANEL_PATH/.env" 2>/dev/null | tail -n1 | cut -d= -f2- | tr -d ' "')"
        NODE_URL="${env_url:-http://127.0.0.1}"
    fi
    NODE_URL="${NODE_URL%/}"
    NODE_API_BASE="${NODE_URL}"

    log "Panel URL : ${NODE_URL}"
    log "Panel path: ${NODE_PANEL_PATH}"

    local code
    code="$(curl -sS --max-time 15 -o /dev/null -w '%{http_code}' "$NODE_URL" 2>/dev/null || echo '000')"
    if [[ "$code" == "000" || "$code" == "502" || "$code" == "503" || "$code" == "504" ]]; then
        err "The panel is not reachable at ${NODE_URL} (HTTP ${code})."
        err "Fix the panel first, or pass --url with the correct panel URL."
        exit 1
    fi
    log "Panel is reachable (HTTP ${code})."
}

check_disk() {
    # We need room for the volumes dir, the Wings binary and Docker images.
    local avail_mb
    avail_mb="$(df -Pm "${NODE_DAEMON_BASE}" 2>/dev/null | awk 'NR==2{print $4}' || true)"
    if [[ -n "$avail_mb" && "$avail_mb" -lt 2048 ]]; then
        err "Only ${avail_mb} MB free on ${NODE_DAEMON_BASE}. At least 2048 MB is recommended."
        exit 1
    fi
    log "Disk space OK."
}

check_ports() {
    # The daemon API + SFTP ports must not be in use by another process.
    for port in "$NODE_DAEMON_LISTEN" "$NODE_DAEMON_SFTP"; do
        if ss -ltn 2>/dev/null | awk '{print $4}' | grep -Eq ":${port}$"; then
            err "Port ${port} is already in use. Chose another with"
            err "NODE_DAEMON_LISTEN / NODE_DAEMON_SFTP or free the port."
            exit 1
        fi
    done
    log "Daemon ports ${NODE_DAEMON_LISTEN}/${NODE_DAEMON_SFTP} are free."
}

# -----------------------------------------------------------------------------
# Docker
# -----------------------------------------------------------------------------
install_docker() {
    log "Checking Docker..."
    if command -v docker >/dev/null 2>&1 && docker info >/dev/null 2>&1; then
        log "Docker is already installed and running."
        return 0
    fi

    if command -v docker >/dev/null 2>&1; then
        log "Docker is installed but not running - starting it."
        systemctl enable docker >/dev/null 2>&1 || true
        systemctl start docker >/dev/null 2>&1 || true
    else
        log "Docker not found - installing via the official get.docker.com script."
        if ! curl -fsSL --retry 3 "https://get.docker.com" -o "$TMP/get-docker.sh"; then
            err "Could not download the Docker installation script."
            exit 1
        fi
        sh "$TMP/get-docker.sh" >/dev/null 2>&1 || {
            err "Docker installation failed. Please install Docker manually and re-run."
            exit 1
        }
        systemctl enable docker >/dev/null 2>&1 || true
        systemctl start docker >/dev/null 2>&1 || true
    fi

    if command -v docker >/dev/null 2>&1 && docker info >/dev/null 2>&1; then
        log "Docker is now running."
    else
        err "Could not get Docker running. Run 'docker info' to diagnose."
        diagnose_docker_failure
        exit 1
    fi
}

diagnose_docker_failure() {
    # Wings needs a Docker daemon that can create bridge networks (it uses the
    # default bridge plus published ports for every game server). On shared or
    # edge container platforms the most common cause of failure is a missing
    # CAP_NET_ADMIN capability, which makes iptables/nftables fail.
    local capeff dec
    capeff="$(awk '/^CapEff:/{print $2}' /proc/self/status 2>/dev/null || true)"
    if [[ -n "$capeff" ]]; then
        dec=$((16#$capeff))
        # CAP_NET_ADMIN is bit 12.
        if (( (dec >> 12) & 1 == 0 )); then
            err "This host is missing CAP_NET_ADMIN."
            err "Docker cannot create its network bridge here, so Wings - and any"
            err "panel that runs servers in containers - cannot work on this host."
            err "This normally means you are inside an unprivileged container."
            err "Run the node on a real VPS/KVM host that supports Docker."
            return 0
        fi
    fi
    if [[ -f /var/log/dockerd.log ]]; then
        warn "Recent dockerd output:"
        tail -n 12 /var/log/dockerd.log 2>/dev/null | sed 's/^/    /' || true
    fi
}

# -----------------------------------------------------------------------------
# Application API key
# -----------------------------------------------------------------------------
# The installer needs an Application API key to register the node. By default
# it mints one itself (READ+WRITE on nodes/locations/allocations) and deletes
# it again on exit, so a fully automatic install never has to visit the panel
# UI. Pass --no-auto-key to be prompted instead.
write_key_helper_php() {
    # write_key_helper_php DEST MODE   (MODE = create | delete)
    local dest="$1" mode="$2"
    local panel="$NODE_PANEL_PATH"
    if [[ "$mode" == "create" ]]; then
        cat > "$dest" <<PHP
<?php
require '${panel}/vendor/autoload.php';
\$app = require_once '${panel}/bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

\$admin = Pterodactyl\Models\User::query()->where('root_admin', 1)->orderBy('id')->first();
if (!\$admin) {
    fwrite(STDERR, "no-admin-user\n");
    exit(2);
}

// AdminAcl permission values are a bitmask: can() tests permission AND action.
// WRITE alone is 2, and 2 AND READ(1) is 0, so every GET would 403.
// The installer lists nodes/locations/allocations before it writes to them.
\$perm = Pterodactyl\Services\Acl\Api\AdminAcl::READ
      | Pterodactyl\Services\Acl\Api\AdminAcl::WRITE;

\$key = app(Pterodactyl\Services\Api\KeyCreationService::class)
    ->setKeyType(Pterodactyl\Models\ApiKey::TYPE_APPLICATION)
    ->handle(
        ['user_id' => \$admin->id, 'memo' => 'Turtle node installer (temporary)'],
        ['r_nodes' => \$perm, 'r_locations' => \$perm, 'r_allocations' => \$perm]
    );

// The user-facing key is the 16-char identifier ("ptla_...") plus the
// decrypted 32-char token; findToken() splits on that same boundary.
echo \$key->identifier . decrypt(\$key->token);
PHP
    else
        cat > "$dest" <<PHP
<?php
require '${panel}/vendor/autoload.php';
\$app = require_once '${panel}/bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Pterodactyl\Models\ApiKey::query()->where('identifier', '${NODE_KEY_IDENT}')->delete();
PHP
    fi
}

auto_create_api_key() {
    command -v php >/dev/null 2>&1 || return 1
    [[ -f "$NODE_PANEL_PATH/vendor/autoload.php" ]] || return 1
    [[ -f "$NODE_PANEL_PATH/bootstrap/app.php" ]] || return 1

    local script="$TMP/turtle-api-key-create.php" out
    write_key_helper_php "$script" create
    out="$(cd "$NODE_PANEL_PATH" && php "$script" 2>"$TMP/api-key-create.err" | tr -d '\r\n')" || true
    if [[ -z "$out" || "${#out}" -lt 32 ]]; then
        return 1
    fi

    NODE_API_KEY="$out"
    NODE_KEY_CREATED=1
    NODE_KEY_IDENT="${out:0:16}"
    return 0
}

delete_application_api_key() {
    [[ "$NODE_KEY_CREATED" == "1" ]] || return 0
    [[ -n "$NODE_KEY_IDENT" ]] || return 0
    command -v php >/dev/null 2>&1 || return 0

    local script="$TMP/turtle-api-key-delete.php"
    write_key_helper_php "$script" delete
    ( cd "$NODE_PANEL_PATH" && php "$script" >/dev/null 2>&1 ) || true
    log "Removed the temporary Application API key."
    NODE_KEY_CREATED=0
}

ask_api_key() {
    if [[ -n "$NODE_API_KEY" ]]; then
        log "Using the Application API key provided via option/env."
        return 0
    fi

    if [[ "$NODE_ALLOW_AUTO_KEY" == "1" ]]; then
        if auto_create_api_key; then
            log "Minted a temporary Application API key (deleted when this script exits)."
            return 0
        fi
        warn "Could not auto-create an Application API key; falling back to a prompt."
    fi

    if [[ "$NODE_YES" == "1" ]]; then
        err "An Application API key is required. Provide one with"
        err "  --api-key KEY   or   PTERODACTYL_API_KEY=KEY  or run interactively."
        exit 1
    fi

    echo ""
    log "====================================================================="
    log " Application API key required"
    log "====================================================================="
    log "  1. Ensure you created the first admin user:"
    log "       cd ${NODE_PANEL_PATH} && php artisan p:user:make"
    log "  2. Log into the panel, go to Admin -> Application API -> Create."
    log "  3. Grant the key these permissions (Read & Write):"
    log "       Nodes, Locations, Allocations   (Read&Write All is easiest)"
    log "  4. Copy the key. It is shown ONLY once."
    log "====================================================================="
    read -r -p "Paste the Application API key (hidden): " -s NODE_API_KEY
    echo ""
    need "An Application API key" "$NODE_API_KEY"
}

validate_api_key() {
    log "Validating the Application API key..."
    api_call GET "/api/application/nodes?per_page=1"
    case "$API_HTTP_CODE" in
        200)
            log "API key accepted."
            ;;
        401|403)
            err "The panel rejected the API key (HTTP ${API_HTTP_CODE})."
            err "Check the key is correct and has the required permissions."
            exit 1
            ;;
        *)
            err "Unexpected API response (HTTP ${API_HTTP_CODE})."
            err "Body: $(echo "$API_BODY" | head -c 400)"
            exit 1
            ;;
    esac
}

# -----------------------------------------------------------------------------
# Location & node registration
# -----------------------------------------------------------------------------
find_or_create_location() {
    if [[ -n "$NODE_LOCATION_ID" ]]; then
        log "Using configured location ID ${NODE_LOCATION_ID}."
        return 0
    fi

    api_call GET "/api/application/locations?per_page=100"
    local loc_id
    loc_id="$(printf '%s' "$API_BODY" | php_json 'data' 2>/dev/null | php -r '
        $j = json_decode(stream_get_contents(STDIN), true);
        foreach ($j as $row) {
            if (($row["attributes"]["short"] ?? "") === $argv[1]) { echo $row["attributes"]["id"]; exit; }
        }
    ' "$NODE_LOCATION_SHORT" 2>/dev/null)"
    if [[ -n "$loc_id" ]]; then
        NODE_LOCATION_ID="$loc_id"
        log "Reusing existing location '${NODE_LOCATION_SHORT}' (id ${NODE_LOCATION_ID})."
        return 0
    fi

    log "Creating location '${NODE_LOCATION_SHORT}'..."
    local payload
    payload="{\"short\":$(json_enc "$NODE_LOCATION_SHORT"),\"long\":$(json_enc "$NODE_DESCRIPTION")}"
    api_call POST "/api/application/locations" "$payload"
    if [[ "$API_HTTP_CODE" != "201" && "$API_HTTP_CODE" != "200" ]]; then
        err "Could not create location (HTTP ${API_HTTP_CODE})."
        err "Body: $(echo "$API_BODY" | head -c 400)"
        exit 1
    fi
    NODE_LOCATION_ID="$(printf '%s' "$API_BODY" | php_json 'attributes.id' || true)"
    need "Location id from API response" "$NODE_LOCATION_ID"
    log "Location created (id ${NODE_LOCATION_ID})."
}

choose_networking() {
    # Local node mode: the panel and Wings share this machine, so the panel
    # reaches the daemon over loopback and no public FQDN/hostname is needed.
    if [[ "$NODE_LOCAL" == "1" ]]; then
        NODE_FQDN="${NODE_FQDN:-127.0.0.1}"
        NODE_SCHEME="${NODE_SCHEME:-http}"
        NODE_PUBLIC="false"
        NODE_BEHIND_PROXY="false"
        NODE_ALLOC_IP="${NODE_ALLOC_IP:-0.0.0.0}"
        case "$NODE_SCHEME" in
            http|https) ;;
            *) err "Scheme must be http or https (got '${NODE_SCHEME}')."; exit 1 ;;
        esac
        log "Local node mode - no public FQDN required."
        log "  Panel -> Wings : ${NODE_SCHEME}://${NODE_FQDN}:${NODE_DAEMON_LISTEN}"
        log "  Allocations    : ${NODE_ALLOC_IP} ports ${NODE_PORTS}"
        return 0
    fi

    # The node FQDN must be reachable from the panel (which lives on this same
    # box). The safest default is the server's own IP address. A hostname is
    # only accepted when it resolves directly back to this machine so that we
    # never silently route panel<->wings traffic through a proxy/tunnel.
    if [[ -z "$NODE_FQDN" ]]; then
        local ip
        ip="$(hostname -I 2>/dev/null | awk '{print $1}' || true)"
        NODE_FQDN="${ip:-127.0.0.1}"
        if [[ "$NODE_YES" != "1" ]]; then
            read -r -p "Node FQDN/IP address (used by the panel to reach Wings) [${NODE_FQDN}]: " NODE_FQDN_INPUT
            NODE_FQDN="${NODE_FQDN_INPUT:-${NODE_FQDN}}"
        fi
    fi

    if [[ -z "$NODE_SCHEME" ]]; then
        NODE_SCHEME="http"
        if [[ "$NODE_YES" != "1" ]]; then
            read -r -p "Scheme for the node daemon (http recommended for a local node) [http]: " NODE_SCHEME_INPUT
            NODE_SCHEME="${NODE_SCHEME_INPUT:-http}"
        fi
    fi
    case "$NODE_SCHEME" in
        http|https) ;;
        *) err "Scheme must be http or https (got '${NODE_SCHEME}')."; exit 1 ;;
    esac

    # IP-based FQDN can never carry a valid TLS certificate for https.
    if [[ "$NODE_SCHEME" == "https" ]]; then
        if [[ "$NODE_FQDN" =~ ^[0-9.]+$ ]]; then
            err "You selected https but the node FQDN is an IP (${NODE_FQDN})."
            err "TLS on a node requires a domain name. Use scheme http, or set NODE_FQDN to a domain."
            exit 1
        fi
        log "Checking TLS certificates for ${NODE_FQDN}..."
        if [[ ! -f "/etc/letsencrypt/live/${NODE_FQDN}/fullchain.pem" ]]; then
            warn "No Let's Encrypt certificate found for ${NODE_FQDN}."
            warn "Wings will look for /etc/letsencrypt/live/${NODE_FQDN}/fullchain.pem at boot."
            warn "You should issue the certificate before starting Wings, or use http."
        fi
    else
        log "Node scheme: http (no TLS on the local node)."
    fi

    # Warn about proxied hostnames: the panel (browser/API) must be able to
    # reach the daemon port directly, and a Cloudflare/Tunnel hostname does not
    # proxy random ports.
    if [[ ! "$NODE_FQDN" =~ ^[0-9.]+$ ]]; then
        local resolved
        resolved="$(getent hosts "$NODE_FQDN" 2>/dev/null | awk '{print $1; exit}' || true)"
        local local_ips
        local_ips="$(hostname -I 2>/dev/null | tr ' ' '\n' || true)"
        if [[ -z "$resolved" ]]; then
            warn "FQDN '${NODE_FQDN}' does not resolve on this machine."
            warn "The panel may be unable to reach the Wings daemon API."
        elif ! grep -q "^${resolved}$" <<< "$local_ips"; then
            warn "FQDN '${NODE_FQDN}' resolves to ${resolved} (not this machine's IP)."
            warn "If this hostname is behind Cloudflare/Tunnel, panel<->Wings and game"
            warn "server connections will not work unless those ports are forwarded."
        else
            log "FQDN '${NODE_FQDN}' resolves to a local address (${resolved})."
        fi
    fi

    log "Node ${NODE_NAME} will listen at ${NODE_SCHEME}://${NODE_FQDN}:${NODE_DAEMON_LISTEN} (SFTP ${NODE_DAEMON_SFTP})."
}

detect_resources() {
    if [[ -z "$NODE_MEMORY" ]]; then
        NODE_MEMORY="$(free -m 2>/dev/null | awk '/^Mem:/{print $2}' || true)"
        if [[ -z "$NODE_MEMORY" ]]; then
            if [[ "$NODE_YES" == "1" ]]; then
                err "Could not detect system memory. Pass --memory MB."
                exit 1
            fi
            read -r -p "Total memory for the node (MB): " NODE_MEMORY
        fi
    fi
    if [[ -z "$NODE_DISK" ]]; then
        NODE_DISK="$(df -Pm "${NODE_DAEMON_BASE}" 2>/dev/null | awk 'NR==2{print $2}' || true)"
        if [[ -z "$NODE_DISK" ]]; then
            if [[ "$NODE_YES" == "1" ]]; then
                err "Could not detect system disk. Pass --disk MB."
                exit 1
            fi
            read -r -p "Total disk for the node (MB): " NODE_DISK
        fi
    fi
    log "Node resources: memory=${NODE_MEMORY} MB, disk=${NODE_DISK} MB."
}

find_or_create_node() {
    # Re-use the existing node when one with the same name is already
    # registered, so re-runs never create duplicates.
    api_call GET "/api/application/nodes?per_page=100"
    local existing_id
    existing_id="$(printf '%s' "$API_BODY" | php_json 'data' 2>/dev/null | php -r '
        $j = json_decode(stream_get_contents(STDIN), true);
        foreach ($j as $row) {
            if (($row["attributes"]["name"] ?? "") === $argv[1]) { echo $row["attributes"]["id"]; exit; }
        }
    ' "$NODE_NAME" 2>/dev/null)"
    if [[ -n "$existing_id" ]]; then
        NODE_ID="$existing_id"
        log "Reusing existing node '${NODE_NAME}' (id ${NODE_ID})."
        # Refresh networking fields so the registered record matches this run.
        api_call PATCH "/api/application/nodes/${NODE_ID}" "$(build_node_payload)"
        if [[ "$API_HTTP_CODE" == "200" ]]; then
            log "Node record refreshed."
        else
            warn "Could not refresh node record (HTTP ${API_HTTP_CODE}). Continuing with the stored record."
        fi
        return 0
    fi

    log "Creating node '${NODE_NAME}' on the panel..."
    api_call POST "/api/application/nodes" "$(build_node_payload)"
    if [[ "$API_HTTP_CODE" != "201" && "$API_HTTP_CODE" != "200" ]]; then
        err "Could not create node (HTTP ${API_HTTP_CODE})."
        err "Body: $(echo "$API_BODY" | head -c 600)"
        exit 1
    fi
    NODE_ID="$(printf '%s' "$API_BODY" | php_json 'attributes.id' || true)"
    need "Node id from API response" "$NODE_ID"
    log "Node created (id ${NODE_ID})."
}

build_node_payload() {
    printf '{"name":%s,"description":%s,"location_id":%s,"fqdn":%s,"scheme":%s,"behind_proxy":%s,"public":%s,"memory":%s,"memory_overallocate":%s,"disk":%s,"disk_overallocate":%s,"daemon_base":%s,"daemon_listen":%s,"daemon_sftp":%s,"upload_size":%s}' \
        "$(json_enc "$NODE_NAME")" \
        "$(json_enc "$NODE_DESCRIPTION")" \
        "$NODE_LOCATION_ID" \
        "$(json_enc "$NODE_FQDN")" \
        "$(json_enc "$NODE_SCHEME")" \
        "$([[ "$NODE_BEHIND_PROXY" == "true" ]] && echo true || echo false)" \
        "$([[ "$NODE_PUBLIC" == "true" ]] && echo true || echo false)" \
        "$NODE_MEMORY" \
        "$NODE_MEMORY_OVERALLOCATE" \
        "$NODE_DISK" \
        "$NODE_DISK_OVERALLOCATE" \
        "$(json_enc "$NODE_DAEMON_BASE")" \
        "$NODE_DAEMON_LISTEN" \
        "$NODE_DAEMON_SFTP" \
        "$NODE_UPLOAD_SIZE"
}

create_allocations() {
    # Only create allocations when the node has none yet (idempotency).
    api_call GET "/api/application/nodes/${NODE_ID}/allocations?per_page=100"
    if [[ "$API_HTTP_CODE" != "200" ]]; then
        warn "Could not list allocations (HTTP ${API_HTTP_CODE})."
        return 1
    fi
    local count
    count="$(printf '%s' "$API_BODY" | php_json 'data' 2>/dev/null | php -r '
        $j = json_decode(stream_get_contents(STDIN), true);
        echo is_array($j) ? count($j) : 0;
    ' 2>/dev/null)"
    if [[ -n "$count" && "$count" -gt 0 ]]; then
        log "Node already has ${count} allocation(s) - skipping allocation creation."
        return 0
    fi

    local alloc_ip="${NODE_ALLOC_IP:-$NODE_FQDN}"
    local ports_json
    ports_json="$(php -r '
        $ports = array_map("trim", explode(",", $argv[1]));
        echo json_encode($ports);
    ' "$NODE_PORTS" 2>/dev/null)"
    log "Creating allocations on ${alloc_ip} for ports: ${NODE_PORTS}"
    api_call POST "/api/application/nodes/${NODE_ID}/allocations" \
        "{\"ip\":$(json_enc "$alloc_ip"),\"ports\":${ports_json}}"
    if [[ "$API_HTTP_CODE" == "201" || "$API_HTTP_CODE" == "200" ]]; then
        log "Allocations created."
    else
        warn "Could not create allocations (HTTP ${API_HTTP_CODE})."
        warn "You can add allocations later in the panel: Admin -> Nodes -> Allocation."
    fi
}

# -----------------------------------------------------------------------------
# Wings configuration
# -----------------------------------------------------------------------------
fetch_config() {
    log "Fetching the official Wings configuration for node ${NODE_ID}..."
    api_call GET "/api/application/nodes/${NODE_ID}/configuration"
    if [[ "$API_HTTP_CODE" != "200" ]]; then
        err "Could not fetch node configuration (HTTP ${API_HTTP_CODE})."
        err "Body: $(echo "$API_BODY" | head -c 400)"
        exit 1
    fi

    mkdir -p "$WINGS_CFG_DIR"

    # Back up any existing config before overwriting it.
    if [[ -f "$WINGS_CFG" ]]; then
        local backup="${WINGS_CFG}.bak.$(date +%s)"
        cp -p "$WINGS_CFG" "$backup"
        log "Backed up existing configuration to ${backup}"
    fi

    # The panel returns JSON which is valid YAML, so Wings can read it either
    # way. Allow an explicit remote override for unusual networks.
    if [[ -n "$WINGS_REMOTE_URL" ]]; then
        API_BODY="$(printf '%s' "$API_BODY" | php -r '
            $j = json_decode(stream_get_contents(STDIN), true);
            if ($j === null) { fwrite(STDERR, "bad-json\n"); exit 2; }
            $j["remote"] = $argv[1];
            echo json_encode($j, JSON_UNESCAPED_SLASHES);
        ' "$WINGS_REMOTE_URL" 2>/dev/null || printf '%s' "$API_BODY")"
        log "Overrode the panel->Wings remote URL with ${WINGS_REMOTE_URL}"
    fi

    printf '%s\n' "$API_BODY" > "$WINGS_CFG"
    chmod 600 "$WINGS_CFG"
    log "Wings configuration written to ${WINGS_CFG} (mode 600)."
}

# -----------------------------------------------------------------------------
# Wings binary + service
# -----------------------------------------------------------------------------
resolve_wings_version() {
    if [[ -n "$WINGS_VERSION" ]]; then
        echo "$WINGS_VERSION"
        return 0
    fi
    # Pick the newest v1.x release (compatible with this panel 1.x line).
    local tag
    tag="$(curl -fsSL --max-time 30 "https://api.github.com/repos/pterodactyl/wings/releases?per_page=100" 2>/dev/null | php -r '
        $j = json_decode(stream_get_contents(STDIN), true);
        if (!is_array($j) || !$j) { exit 1; }
        $tags = array_column($j, "tag_name");
        $v1 = array_values(array_filter($tags, function ($t) { return preg_match("/^v1\./", (string) $t); }));
        if (!$v1) { exit 1; }
        usort($v1, "version_compare");
        echo end($v1);
    ' 2>/dev/null || true)"
    if [[ -z "$tag" ]]; then
        warn "Could not determine the latest v1.x Wings release from GitHub."
        warn "Falling back to the latest release (should also be fine for panel 1.x)."
        tag="latest"
    fi
    echo "$tag"
}

install_wings_binary() {
    local tag="$1"
    local url="https://github.com/pterodactyl/wings/releases/download/${tag}/wings_linux_amd64"
    if [[ "$tag" == "latest" ]]; then
        url="https://github.com/pterodactyl/wings/releases/latest/download/wings_linux_amd64"
    fi
    log "Installing Wings ${tag}..."

    if [[ -x "$WINGS_BIN" ]]; then
        local have
        have="$("$WINGS_BIN" --version 2>/dev/null | head -n1 || true)"
        if [[ -n "$have" && "$tag" != "latest" && "$have" == *"$tag"* ]]; then
            log "Wings is already at ${tag} - skipping download."
            return 0
        fi
    fi

    if ! curl -fsSL --retry 3 --max-time 300 -o "$TMP/wings_linux_amd64" "$url"; then
        err "Could not download Wings from ${url}"
        exit 1
    fi
    install -m 0755 "$TMP/wings_linux_amd64" "$WINGS_BIN"
    "$WINGS_BIN" --version >/dev/null 2>&1 || {
        err "The downloaded Wings binary does not run on this machine."
        exit 1
    }
    log "Wings installed: $("$WINGS_BIN" --version 2>/dev/null | head -n1)"
}

write_systemd_unit() {
    log "Installing the Wings systemd service..."
    mkdir -p /var/run/wings 2>/dev/null || true
    cat > "$WINGS_SERVICE" <<EOF
[Unit]
Description=Pterodactyl Wings Daemon
After=docker.service
Requires=docker.service

[Service]
User=root
WorkingDirectory=${WINGS_CFG_DIR}
LimitNOFILE=4096
PIDFile=/var/run/wings/daemon.pid
ExecStart=${WINGS_BIN}
Restart=on-failure
StartLimitInterval=600
StartLimitBurst=10

[Install]
WantedBy=multi-user.target
EOF
    systemctl daemon-reload >/dev/null 2>&1 || true
}

# -----------------------------------------------------------------------------
# Start & verify
# -----------------------------------------------------------------------------
start_wings() {
    log "Starting Wings..."
    systemctl enable wings >/dev/null 2>&1 || true
    systemctl restart wings >/dev/null 2>&1 || {
        warn "systemctl restart wings returned an error; checking status."
    }

    local i
    for i in $(seq 1 15); do
        if [[ "$(systemctl is-active wings 2>/dev/null || true)" == "active" ]]; then
            log "Wings service is active."
            return 0
        fi
        sleep 2
    done
    return 1
}

verify_node_online() {
    log "Verifying the node using the panel's own connectivity check..."

    # Extract the daemon token from the configuration we fetched.
    local token=""
    if head -c1 "$WINGS_CFG" 2>/dev/null | grep -q '{'; then
        token="$(php -r '$j=json_decode(stream_get_contents(STDIN),true); echo $j["token"] ?? "";' < "$WINGS_CFG" 2>/dev/null || true)"
    else
        token="$(awk -F': *' '/^token:/{print $2; exit}' "$WINGS_CFG" 2>/dev/null | tr -d ' "' || true)"
    fi

    local url="${NODE_SCHEME}://${NODE_FQDN}:${NODE_DAEMON_LISTEN}/api/system"
    local resp code
    code="$(curl -sS --max-time 10 -H "Authorization: Bearer ${token}" -o "$TMP/system.json" -w '%{http_code}' "$url" 2>/dev/null || echo '000')"
    resp="$(cat "$TMP/system.json" 2>/dev/null || true)"

    if [[ "$code" == "200" && "$resp" == *'"version"'* ]]; then
        local version
        version="$(printf '%s' "$resp" | php_json 'version' 2>/dev/null || true)"
        log "Node ONLINE (${url}) - Wings ${version}."
        return 0
    fi

    warn "The daemon answered HTTP ${code} at ${url}."
    warn "Wings may still be connecting to the panel. Check the log:"
    warn "  journalctl -u wings -n 50"
    if [[ -f "$WINGS_LOG" ]]; then
        tail -n 20 "$WINGS_LOG" 2>/dev/null | sed 's/^/    /' || true
    fi
    warn "The node will show as online in the panel once Wings connects back."
    return 1
}

finish_report() {
    echo ""
    echo "======================================================================"
    echo "  Local Wings node installed on this server"
    echo "======================================================================"
    echo "  Node name     : ${NODE_NAME}"
    echo "  Node id       : ${NODE_ID}"
    echo "  Daemon URL    : ${NODE_SCHEME}://${NODE_FQDN}:${NODE_DAEMON_LISTEN}"
    echo "  SFTP port     : ${NODE_DAEMON_SFTP}"
    echo "  Allocations   : ${NODE_PORTS} on ${NODE_ALLOC_IP:-$NODE_FQDN}"
    echo "  Config file   : ${WINGS_CFG}"
    echo "  Service       : wings (systemd, enabled)"
    echo ""
    echo "  Next steps:"
    echo "    1. Create servers in the panel; allocate the ports created above."
    echo "    2. If game clients connect from the internet, forward/open these"
    echo "       ports (daemon ${NODE_DAEMON_LISTEN} is only needed by the panel)."
    echo "    3. The node status indicator in the panel should now be green."
    echo "    4. Update Wings later with:  sudo systemctl restart wings"
    echo "======================================================================"
}

# -----------------------------------------------------------------------------
# Main
# -----------------------------------------------------------------------------
node_main() {
    check_root
    check_os
    check_panel
    check_disk
    check_ports
    install_docker
    ask_api_key
    validate_api_key
    find_or_create_location
    choose_networking
    detect_resources
    find_or_create_node
    create_allocations
    fetch_config
    local wings_tag
    wings_tag="$(resolve_wings_version)"
    install_wings_binary "$wings_tag"
    write_systemd_unit
    start_wings || {
        err "Wings did not stay active. The panel was not modified."
        err "Diagnose with: journalctl -u wings -n 50"
        exit 1
    }
    verify_node_online || {
        warn "Node installation finished, but the online check did not pass."
        warn "The panel and Docker/Wings service are intact; see the diagnostics above."
        exit 1
    }
    finish_report
}

node_main "$@"