# hyper — Installer & Theme Notes

This folder contains everything needed to turn this repository into a running
**Hyper Game Panel** — the Hyper theme is already inside the panel code in
this repo, so there is nothing extra to download.

| File | Purpose |
|------|---------|
| `install.sh` | One-command installer (fresh install or in-place update) |
| `license/` | The Hyper ionCube runtime license key + manifest shipped with the panel v2.1.2 |

## Quick install (fresh server)

Requirements: **Ubuntu 22.04/24.04 x86_64**, run as **root** (Debian works if you
prepare PHP 8.4 + the ionCube Loader manually and re-run with `--skip-prereqs`).

```bash
git clone <this-repo-url> /tmp/ptero
cd /tmp/ptero
sudo bash hyper/install.sh
```

The installer:

1. Installs nginx, MariaDB, Redis, Supervisor, PHP 8.4 and the ionCube Loader.
2. Copies this repo's panel code (theme included) to `/var/www/pterodactyl`.
3. Runs `composer install`, creates the database, runs migrations and seeds.
4. Installs the license key from `hyper/license/` to `/etc/hyperv2/ioncube/`.
5. Configures nginx, the Laravel scheduler, a queue worker, and the Hyper
   Discord bot (disable with `--no-discord`).

When it finishes:

```bash
cd /var/www/pterodactyl && php artisan p:user:make   # create your admin user
```

Open the panel URL — the Hyper theme is live out of the box.

## Options

```text
--path PATH         Panel install directory  (default: /var/www/pterodactyl)
--url URL           Public panel URL         (default: prompt)
--no-discord        Do not run the Discord bot
--update            Update an existing panel in place (keeps .env + DB)
--skip-prereqs      Skip OS package / PHP install
--skip-nginx        Skip nginx configuration
```

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
  `/etc/hyperv2/ioncube/hyperv2-release.key`. The `hyper/license/` files came
  bundled with the v2.1.2 release.
- **Redistribution:** You are responsible for checking the Hyper Game Panel
  terms of service before publishing this repository or forking it publicly.
  The code is the commercial Hyper product, not stock Pterodactyl.
- The panel phones home to `license.dgenx.net` / `hyper-r2.dgenx.net` for
  update checks and license validation.
- This is *not* upstream Pterodactyl: it is Pterodactyl 1.15.x-based, heavily
  modified by the Hyper author, so upstream `git pull` will not apply cleanly.