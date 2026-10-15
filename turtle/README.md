# turtle — Installer & Theme Notes

This folder contains everything needed to turn this repository into a running
**Turtle Game Panel** — the Turtle theme is already inside the panel code in
this repo, so there is nothing extra to download.

| File | Purpose |
|------|---------|
| `install.sh` | Installer **and Control Centre** (fresh install, update, services, health, logs) |
| `install_node.sh` | Optional module: install a real **local Wings node** on the same server |
| `uninstall.sh` | Removes the panel (keeps the database by default) |
| `license/` | The ionCube runtime license key + manifest shipped with the panel v2.1.2 (Hyper product, kept for compatibility) |

## Quick install (fresh server)

Requirements: **Ubuntu 22.04/24.04 x86_64**, run as **root** (Debian works if you
prepare PHP 8.4 + the ionCube Loader manually and re-run with `--skip-prereqs`).

```bash
git clone <this-repo-url> /tmp/ptero
cd /tmp/ptero
sudo bash turtle/install.sh
```

With no options this opens the interactive **Control Centre** menu — choose
**1) Install panel** for a fresh install (**2) Update panel** on an existing
server). To skip the menu and install unattended, pass at least one option:

```bash
sudo bash turtle/install.sh --url https://panel.example.com
```

The installer:

1. Installs nginx, MariaDB, Redis, Supervisor, PHP 8.4 and the ionCube Loader.
2. Copies this repo's panel code (theme included) to `/var/www/pterodactyl`.
3. Runs `composer install`, creates the database, runs migrations and seeds.
4. Installs the license key from `turtle/license/` to `/etc/hyperv2/ioncube/`.
5. Configures nginx, the Laravel scheduler, a queue worker, and the
   Discord bot (disable with `--no-discord`).

When it finishes:

```bash
cd /var/www/pterodactyl && php artisan p:user:make   # create your admin user
```

Open the panel URL — the Turtle theme is live out of the box.

## Options

```text
--path PATH         Panel install directory  (default: /var/www/pterodactyl)
--url URL           Public panel URL         (default: prompt)
--node              Always install a local Wings node (no prompt)
--no-node           Never install a local node (panel-only; current default behaviour)
--playit            Install the Playit.gg node bridge (no prompt)
--no-playit         Never offer the Playit.gg bridge
--playit-provision  Let the bridge add the playit plugin to existing Java servers
--no-discord        Do not run the Discord bot
--update            Update an existing panel in place (keeps .env + DB)
--skip-prereqs      Skip OS package / PHP install
--skip-nginx        Skip nginx configuration
--menu              Force the interactive Control Centre
-h, --help          Show help
```

After the panel finishes, the installer asks whether to also set up a **Local
Node** (see below). The question defaults to **No**, so pressing Enter keeps the
current panel-only behaviour. Use `--node` / `--no-node` to skip the prompt.

## Control Centre

Run the installer with **no arguments** (or with `--menu`) to get an interactive
menu — the same entry point as a fresh install:

```bash
sudo bash turtle/install.sh
```

```text
  1) Install panel        (fresh install)
  2) Update panel         (in place; keeps .env + database)
  3) Install / manage local Wings node
  4) Uninstall panel      (removes files + services; keeps database)
  5) Services             (start / stop / restart / status)
  6) Health check
  7) View logs
  8) Panel info
  9) Playit.gg            (public addresses for Minecraft servers)
  0) Exit
```

Everything the menu does is also scriptable standalone (`install.sh`,
`install_node.sh`, `uninstall.sh`) or through flags, so the menu is optional.

## Uninstalling

From the menu (**4) Uninstall panel**) or directly:

```bash
sudo bash turtle/uninstall.sh
```

By default it stops and removes the panel's Supervisor programs, removes the
panel nginx site, deletes `/var/www/pterodactyl`, removes the local Wings node
(if present) and removes the Playit.gg node bridge. It **keeps your database,
server data volumes and server plugin files**, and asks you to type `uninstall`
to confirm. To also drop the database:

```bash
sudo bash turtle/uninstall.sh --purge-db
```

System packages, the ionCube loader and the license key are left in place (they
may be used by other apps). Add `--keep-wings` to leave the Wings node running
or `--purge-volumes` to delete `/var/lib/pterodactyl` (server data — careful).

## Local Wings node (optional)

`turtle/install_node.sh` installs a **real, registered Wings daemon** on the same
machine as the panel, so a single server can host both. It uses the official
Pterodactyl mechanisms end-to-end — no fake node, no UI simulator — so normal
server management (allocations, console, power, files, SFTP) works afterwards.

It is invoked automatically at the end of `install.sh` if you answer **Yes**
(or pass `--node`), and can also be run on its own:

```bash
sudo bash turtle/install_node.sh --panel-path /var/www/pterodactyl
```

**No FQDN?** Add `--local` — the node binds the panel↔Wings link to `127.0.0.1`
and allocations to `0.0.0.0`, so you never have to type a domain name:

```bash
sudo bash turtle/install_node.sh --local
```

> `--local` still needs a working Docker daemon. If Docker cannot start because
> the host lacks `CAP_NET_ADMIN` (common on free/edge container platforms), the
> installer now says so explicitly instead of failing silently — move the node to
> a real VPS/KVM host in that case.

What it does:

1. Pre-flight checks (root, supported OS, panel health, free disk, free ports).
2. Installs or verifies **Docker**.
3. Obtains a **Pterodactyl Application API key** (auto-minted and removed on exit) and validates it.
4. Finds or creates a *Location* and the **"Local Node"** via the Application API.
5. Creates default allocations (`25565-25570`) if the node has none.
6. Fetches the official node configuration and writes it to
   `/etc/pterodactyl/config.yml` (any existing file is backed up first, `chmod 600`).
7. Installs the official **Wings** binary from GitHub releases, pinned to the
   latest `v1.x` release compatible with this panel.
8. Installs the Wings systemd service, starts it and enables it on boot.
9. Verifies the node is genuinely online using the same check the panel's admin
   UI uses (`GET {scheme}://{fqdn}:8080/api/system`).

### ⚠️ Before you start

- **No API key needed.** The installer mints a temporary Application API key
  itself (Read + Write on *Nodes*, *Locations* and *Allocations*) and deletes it
  again when it exits. Supply your own with `--api-key`/`PTERODACTYL_API_KEY`, or
  pass `--no-auto-key` to be prompted instead. Keys are **never written to disk**.
- **Docker must actually run.** Wings runs every server in a container. On hosts
  without the `CAP_NET_ADMIN` kernel capability (most free/edge container
  platforms) Docker cannot create its network bridge and the installer will say
  so — use a real VPS/KVM host there.
- **Use `--local` when the panel and node share a machine.** It binds the
  panel↔Wings link to `127.0.0.1` and allocations to `0.0.0.0`, so no public FQDN
  is required. Only set a real FQDN when the panel must reach Wings across a
  network, and then **do not assume `127.0.0.1`**.
- **Ports must be reachable** by players for whatever game ports you allocate.
  `8080/tcp` (Wings API) and `2022/tcp` (SFTP) are only needed when the panel and
  node are on different machines.
- **DNS matters** for a remote node. If you use a hostname it must resolve back to
  *this* machine. Cloudflare proxy (orange cloud) and Cloudflare Tunnel do **not**
  expose raw Wings ports — use a direct DNS record (grey cloud) or the server IP.
- TLS verification is **never** disabled.

Options:

```text
--panel-path PATH   Panel directory            (default: /var/www/pterodactyl)
--url URL           Panel URL                  (default: read from panel .env)
--api-key KEY       Application API key        (auto-minted if omitted)
--no-auto-key       Prompt for a key instead of auto-minting
--local             Same-box node: 127.0.0.1 + 0.0.0.0, no FQDN
--fqdn HOST         Node FQDN or IP            (default: server primary IP)
--scheme http|https                            (default: http)
--node-name NAME    Node name                  (default: "Local Node")
--ports RANGE       Allocation ports           (default: 25565-25570)
--memory MB / --disk MB                        (default: detected resources)
--wings-version TAG Wings release tag          (default: latest v1.x)
--yes               Non-interactive
```

The script is **idempotent**: re-running it reuses the existing location and
node and does not create duplicates. A failure while installing the node never
touches the panel.

## Playit.gg public addresses (optional)

Minecraft servers on a node without a public IPv4 (or behind CGNAT) can still be
reachable: the panel ships a **playit.gg** integration that gives each server a
public `host:port` and shows it in the normal address field of the client area.

There are two halves, and you can use either or both:

1. **On the game server** — the Playit-enabled Paper egg installs the official
   `playit-minecraft-plugin` automatically. The plugin reads an agent secret
   from the panel (see the key pool below) or, if none is available, prints a
   one-time `https://playit.gg/claim/<code>` link in the server console.
2. **On the node** — `turtle/install_playit.sh` installs a small bridge that
   reads each server's plugin config, asks playit for the connectable address
   and reports it to the panel. The panel mirrors it into the server's address.

### Set up the key pool (recommended)

1. Open **Admin → Playit** in the panel.
2. Create one or more agents at [playit.gg](https://playit.gg) and paste their
   **secret keys** into *Add agent secret keys* (one per line). Each server is
   handed its own key, so each server gets its own address.
3. Note the **Report token** shown at the top — the node bridge needs it.

### Install the node bridge

```bash
sudo bash turtle/install_playit.sh \
     --panel https://panel.example.com \
     --token <report token from Admin → Playit>
```

Add `--provision` to also drop the plugin into existing Java servers that do not
have it yet (they pick it up on their next restart), and `--restart` to restart
them immediately. Other options:

```text
--interval SECONDS   Sync interval, minimum 60   (default: 120)
--agent-secret KEY   Also run the standalone playit agent (Docker)
--run                Run one sync immediately
--status             Show bridge status + last run
--uninstall          Remove the bridge / standalone agent
--yes                Non-interactive
```

The bridge installs a `turtle-playit-sync.timer` (systemd) that runs every
`--interval` seconds. It never touches the panel; it only reads server volumes
and calls the panel's token-protected `/api/public/playit/*` endpoints. Run it
from the menu too: **Control Centre → 9) Playit.gg**.

### Notes

- Server plugin files live under
  `/var/lib/pterodactyl/volumes/<uuid>/plugins/playit-gg/` and are left in place
  by the uninstaller.
- Without the key pool the plugin's own **claim link** still works: open the
  server console and visit the printed URL once. The bridge reports the address
  either way.
- The address is written to `allocations.ip_alias` (what the client shows) and
  to the `PLAYIT_ADDRESS` startup variable (the full `host:port`).

## Updating the panel

```bash
cd /var/www/pterodactyl && sudo bash hyper_fetch.sh
```

`hyper_fetch.sh` and `hyper_watcher.sh` ship inside the panel code (repo root)
and are maintained by the Hyper project. `hyper_watcher.sh` can be added to
cron/systemd if you want automatic updates.

## ⚠️ Important notes

- **The panel PHP code is ionCube-encoded (proprietary).** It only runs with
  the ionCube Loader installed and the valid license key in
  `/etc/hyperv2/ioncube/hyperv2-release.key`. The `turtle/license/` files came
  bundled with the v2.1.2 release.
- **Redistribution:** You are responsible for checking the Hyper Game Panel
  terms of service before publishing this repository or forking it publicly.
  The code is the commercial Hyper product, not stock Pterodactyl.
- The panel phones home to `license.dgenx.net` / `hyper-r2.dgenx.net` for
  update checks and license validation.
- This is *not* upstream Pterodactyl: it is Pterodactyl 1.15.x-based, heavily
  modified by the Hyper author, so upstream `git pull` will not apply cleanly.