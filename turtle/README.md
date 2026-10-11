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
panel nginx site, deletes `/var/www/pterodactyl`, and removes the local Wings
node if present. It **keeps your database and server data volumes**, and asks
you to type `uninstall` to confirm. To also drop the database:

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

What it does:

1. Pre-flight checks (root, supported OS, panel health, free disk, free ports).
2. Installs or verifies **Docker**.
3. Prompts for a **Pterodactyl Application API key** and validates it.
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

- **Create an Application API key** in the panel: *Admin → Application API →
  Create*. Give it **Read & Write** on *Nodes*, *Locations* and *Allocations*.
  Paste it when prompted (or set `PTERODACTYL_API_KEY`). The key is used only
  during installation and is **never written to disk**.
- **Ports must be reachable:** `8080/tcp` (Wings API) and `2022/tcp` (SFTP) must
  be open/served, plus whatever game ports you allocate. Wings and the panel
  talk over the node's FQDN, so **do not assume `127.0.0.1`**.
- **DNS matters.** Default FQDN is the server's primary IP. If you use a
  hostname, it must resolve back to *this* machine. Cloudflare proxy (orange
  cloud) and Cloudflare Tunnel do **not** expose raw Wings ports — use a direct
  DNS record (grey cloud) or the server IP for the node.
- TLS verification is **never** disabled.

Options:

```text
--panel-path PATH   Panel directory            (default: /var/www/pterodactyl)
--url URL           Panel URL                  (default: read from panel .env)
--api-key KEY       Application API key        (never stored; prompts if omitted)
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