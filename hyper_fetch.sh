#!/usr/bin/env bash
set -euo pipefail

if [[ "${EUID:-0}" -ne 0 ]]; then
	echo "Error: This script must be run as root (sudo). Exiting."
	exit 1
fi


HYPER_RELEASE_CHANNEL="${HYPER_RELEASE_CHANNEL:-}"
HYPER_APP_ID="${HYPER_APP_ID:-7c4efcdc-986e-4e85-9b07-328d6ad6db52}"
HYPER_UPDATE_API_ENDPOINT="${HYPER_UPDATE_API_ENDPOINT:-https://license.dgenx.net/api/v1/update-check}"
HYPER_UPDATE_SCRIPT_FILE_SLUG="${HYPER_UPDATE_SCRIPT_FILE_SLUG:-game-auto-update}"

while [[ "$#" -gt 0 ]]; do
	case "$1" in
		--stable)
			HYPER_RELEASE_CHANNEL="stable"
			shift
			;;
		--beta)
			HYPER_RELEASE_CHANNEL="beta"
			shift
			;;
		--channel)
			if [[ -z "${2:-}" ]]; then
				echo "Error: --channel requires stable or beta" >&2
				exit 1
			fi
			HYPER_RELEASE_CHANNEL="$2"
			shift 2
			;;
		--channel=*)
			HYPER_RELEASE_CHANNEL="${1#*=}"
			shift
			;;
		-h|--help)
			echo "Usage: $0 [--stable|--beta|--channel stable|beta]"
			exit 0
			;;
		*)
			shift
			;;
	esac
done

script_dir()
{
	local src
	src="${BASH_SOURCE[0]}"
	printf "%s" "$(cd "$(dirname "$src")" >/dev/null 2>&1 && pwd)"
}

DEST_DIR=$(script_dir)
DEST_FILE="$DEST_DIR/hyper_auto_update.sh"

log() { printf "[%s] %s\n" "$(date +'%Y-%m-%d %H:%M:%S')" "$*"; }

read_json_channel() {
	local file="$1"
	[[ -f "$file" ]] || return 1
	php -r '
		$data = json_decode((string) @file_get_contents($argv[1]), true);
		$channel = is_array($data) ? ($data["release_channel"] ?? $data["channel"] ?? "") : "";
		if (is_string($channel)) echo $channel;
	' "$file" 2>/dev/null || true
}

if [[ -z "$HYPER_RELEASE_CHANNEL" && -f "$DEST_DIR/.hyper_release_channel" ]]; then
	HYPER_RELEASE_CHANNEL="$(head -n 1 "$DEST_DIR/.hyper_release_channel" 2>/dev/null || true)"
fi

if [[ -z "$HYPER_RELEASE_CHANNEL" ]]; then
	HYPER_RELEASE_CHANNEL="$(read_json_channel "$DEST_DIR/hyper_version.json")"
fi

HYPER_RELEASE_CHANNEL="$(printf '%s' "${HYPER_RELEASE_CHANNEL:-stable}" | tr '[:upper:]' '[:lower:]')"
case "$HYPER_RELEASE_CHANNEL" in
	stable|beta) ;;
	*)
		log "Error: unsupported release channel '$HYPER_RELEASE_CHANNEL'. Use stable or beta."
		exit 1
		;;
esac

CHANNEL_FILE="$DEST_DIR/.hyper_release_channel"
printf '%s\n' "$HYPER_RELEASE_CHANNEL" > "$CHANNEL_FILE"
chown www-data:www-data "$CHANNEL_FILE" 2>/dev/null || true
chmod 0644 "$CHANNEL_FILE" 2>/dev/null || true

release_api_url() {
	printf '%s?app_id=%s&file_slug=%s\n' "$HYPER_UPDATE_API_ENDPOINT" "$HYPER_APP_ID" "$HYPER_UPDATE_SCRIPT_FILE_SLUG"
}

fetch_release_json() {
	local api_url
	api_url="$(release_api_url)"
	if command -v curl >/dev/null 2>&1; then
		curl -fsSL --retry 3 --retry-delay 2 "$api_url"
	elif command -v wget >/dev/null 2>&1; then
		wget -qO- "$api_url"
	else
		return 127
	fi
}

extract_download_url() {
	php -r '
		$res = json_decode((string) stream_get_contents(STDIN), true);
		$url = "";
		if (is_array($res)) {
			$latest = isset($res["latest_version"]) && is_array($res["latest_version"]) ? $res["latest_version"] : [];
			$url = $latest["download_url"] ?? $res["download_url"] ?? $res["url"] ?? $res["file_url"] ?? "";
		}
		if (is_string($url)) echo trim($url);
	' 2>/dev/null || true
}

RELEASE_JSON="$(fetch_release_json || true)"
URL="$(printf '%s' "$RELEASE_JSON" | extract_download_url)"

if [[ -z "$URL" ]]; then
	log "Error: license API did not return a download_url for file_slug=$HYPER_UPDATE_SCRIPT_FILE_SLUG"
	exit 1
fi

tmpfile=""
cleanup() {
	if [[ -n "$tmpfile" && -f "$tmpfile" ]]; then
		rm -f "$tmpfile" || true
	fi
}
trap cleanup EXIT

download_with_curl() {
	tmpfile=$(mktemp)
	if curl -fsSL --retry 3 --retry-delay 2 -o "$tmpfile" "$URL"; then
		return 0
	else
		rm -f "$tmpfile" || true
		tmpfile=""
		return 1
	fi
}

download_with_wget() {
	tmpfile=$(mktemp)
	if wget -q -O "$tmpfile" "$URL"; then
		return 0
	else
		rm -f "$tmpfile" || true
		tmpfile=""
		return 1
	fi
}

log "Release channel: $HYPER_RELEASE_CHANNEL"
log "Fetching $URL to $DEST_FILE"

if command -v curl >/dev/null 2>&1; then
	log "Trying curl..."
	if download_with_curl; then
		log "Downloaded with curl"
	else
		log "curl failed, will try wget if available"

		if [[ -z "$tmpfile" ]]; then
		if command -v wget >/dev/null 2>&1; then
			log "Trying wget..."
			if download_with_wget; then
				log "Downloaded with wget"
			else
				log "wget failed to download $URL"
				exit 1
			fi
		else
			log "wget not found; cannot download $URL"
			exit 1
		fi
		fi
	fi
elif command -v wget >/dev/null 2>&1; then
	log "curl not found; using wget"
	if download_with_wget; then
		log "Downloaded with wget"
	else
		log "wget failed to download $URL"
		exit 1
	fi
else
	log "Error: neither curl nor wget is installed. Aborting."
	exit 1
fi

log "Writing to $DEST_FILE (overwriting if exists)"
cp -f "$tmpfile" "$DEST_FILE"
if chmod 0755 "$DEST_FILE"; then
	log "Set executable permission on $DEST_FILE"
else
	log "Warning: failed to set executable permission on $DEST_FILE"
fi
WEB_USER="www-data"
id "$WEB_USER" >/dev/null 2>&1 || WEB_USER="root"
if chown "$WEB_USER:$WEB_USER" "$DEST_FILE" 2>/dev/null; then
	log "Set ownership on $DEST_FILE to $WEB_USER"
else
	log "Warning: failed to chown $DEST_FILE to $WEB_USER"
fi

SELF_PATH=$(script_dir)/hyper_fetch.sh
if [[ -f "$SELF_PATH" ]]; then
    if chmod +x "$SELF_PATH"; then
        log "Ensured executable permission on $SELF_PATH"
    fi
fi

log "Download complete and installed to $DEST_FILE"

exit 0
