<div align="center">

# Turtle Game Panel

**Pterodactyl + the Turtle theme, merged into one installable panel.**

The Turtle theme (v2.1.2, forked from Hyper) is **already inside this
repository** — installing the panel installs the themed panel. There is no
separate theme step.

</div>

---

## What is this?

This repository is a merge of two things:

1. **[Pterodactyl Panel](https://github.com/pterodactyl/panel)** — the
   open-source game server management panel this is built on
   (Pterodactyl 1.15.x line, MIT-licensed upstream code).
2. **Hyper Game Panel v2.1.2** — the commercial, heavily themed Pterodactyl
   distribution this fork is based on (the "Hyper" theme, DGEN addons: Discord
   bot, server stats, DDOS alerts, custom eggs, and more). This repository
   rebrands it as **Turtle Game Panel**.

The Turtle codebase (the Hyper codebase with the theme applied, rebranded) is
committed directly in this repo, so a single install flow gives you the full
themed panel out of the box.

## One-command install

Fresh Ubuntu 22.04 / 24.04 (or Debian 12) server, as root:

```bash
git clone <this-repo-url> /tmp/ptero
cd /tmp/ptero
sudo bash turtle/install.sh
```

This opens an interactive **Control Centre** (Install / Update / Uninstall /
Services / Health / Logs). Choose **1) Install panel**. To install unattended
without the menu, pass an option, e.g. `sudo bash turtle/install.sh --url https://panel.example.com`.

Then create your admin account and open the panel:

```bash
cd /var/www/pterodactyl && php artisan p:user:make
```

Full installer documentation, options, the Control Centre and the uninstall flow
are in [`turtle/README.md`](turtle/README.md).

### Managing the panel later

Re-run `sudo bash turtle/install.sh` any time to get the menu again — update the
panel in place, manage services, run a health check, or **uninstall** it
(`turtle/uninstall.sh`, which keeps your database by default).

### Optional: a local Wings node on the same server

At the end of the install you are asked whether to also set up a **Local Node**
(defaults to **No**). Answering yes (or passing `--node`) installs a real,
registered **Wings** daemon on the same machine using an Application API key you
create in the panel — Docker, the official Wings binary, config, systemd service,
and an online verification. It is fully optional and never affects the panel.
See [`turtle/README.md`](turtle/README.md#local-wings-node-optional) for details.

## Repository layout

```text
app/  bootstrap/  config/  database/   <- Turtle Game Panel (ionCube-encoded PHP)
public/DGEN/      public/themes/       <- Turtle theme assets
resources/views/  routes/              <- themed views & routes
turtle/install.sh                       <- installer + Control Centre menu
turtle/install_node.sh                  <- optional: real local Wings node on same server
turtle/uninstall.sh                     <- remove the panel (keeps database by default)
turtle/license/                         <- ionCube license key for this release
hyper_fetch.sh   hyper_watcher.sh      <- Hyper update scripts (shipped by Hyper)
composer.json    composer.lock         <- panel dependencies
```

## What changed vs. stock Pterodactyl?

Stock Pterodactyl `1.0-develop` has been **replaced** by the Turtle Game Panel
codebase (v2.1.2, based on the Hyper Game Panel / Pterodactyl 1.15.x).
Changes include:

- The **Turtle theme pre-applied** (views, `public/DGEN`, `public/themes/`).
- Turtle/DGEN app code (forked from Hyper): `app/**/DGEN/*`, extra artisan
  commands, custom eggs.
- PHP code is **ionCube-encoded** and requires the bundled license key at
  runtime — this is the Hyper author's commercial licensing, kept in place.
- Standard Pterodactyl install steps still apply (composer, artisan migrate,
  queue worker), which the installer automates.

Pull requests from upstream Pterodactyl will **not** apply cleanly.

## ⚠️ Licensing & security notice

Read this before publishing or forking publicly:

- **Proprietary code.** Most PHP in this repo is ionCube-encoded and is the
  commercial **Hyper Game Panel** product, distributed under Hyper/
  dgenx.net's own terms — *not* under Pterodactyl's MIT license. You are
  responsible for complying with those terms.
- **License key.** `turtle/license/` contains the ionCube runtime key for this
  release. The panel will not run without it (and the ionCube Loader).
- **Phone-home.** The panel contacts `license.dgenx.net` / `hyper-r2.dgenx.net`
  for update checks and license verification.
- This repo is provided for the repository owner's own use. If you did not
  obtain a Hyper Game Panel license, do not deploy it.

## Updating

```bash
cd /var/www/pterodactyl && sudo bash hyper_fetch.sh
```

See [`turtle/README.md`](turtle/README.md) for details and `--update` support in
`turtle/install.sh`.