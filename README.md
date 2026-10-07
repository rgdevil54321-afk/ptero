<div align="center">

# Hyper Game Panel

**Pterodactyl + the Hyper theme, merged into one installable panel.**

The Hyper theme (v2.1.2) is **already inside this repository** — installing the
panel installs the themed panel. There is no separate theme step.

</div>

---

## What is this?

This repository is a merge of two things:

1. **[Pterodactyl Panel](https://github.com/pterodactyl/panel)** — the
   open-source game server management panel this is built on
   (Pterodactyl 1.15.x line, MIT-licensed upstream code).
2. **Hyper Game Panel v2.1.2** — a commercial, heavily themed Pterodactyl
   distribution ("Hyper" theme, DGEN addons: Discord bot, server stats,
   DDOS alerts, custom eggs, and more).

The Hyper codebase (with the theme applied) is committed directly in this repo,
so a single install flow gives you the full themed panel out of the box.

## One-command install

Fresh Ubuntu 22.04 / 24.04 (or Debian 12) server, as root:

```bash
git clone <this-repo-url> /tmp/ptero
cd /tmp/ptero
sudo bash hyper/install.sh
```

Then create your admin account and open the panel:

```bash
cd /var/www/pterodactyl && php artisan p:user:make
```

Full installer documentation, options, and the update flow are in
[`hyper/README.md`](hyper/README.md).

## Repository layout

```text
app/  bootstrap/  config/  database/   <- Hyper Game Panel (ionCube-encoded PHP)
public/DGEN/      public/themes/       <- Hyper theme assets
resources/views/  routes/              <- themed views & routes
hyper/install.sh                       <- one-command installer
hyper/license/                         <- ionCube license key for this release
hyper_fetch.sh   hyper_watcher.sh      <- Hyper update scripts (shipped by Hyper)
composer.json    composer.lock         <- panel dependencies
```

## What changed vs. stock Pterodactyl?

Stock Pterodactyl `1.0-develop` has been **replaced** by the Hyper Game Panel
codebase (v2.1.2, based on Pterodactyl 1.15.x). Changes include:

- The **Hyper theme pre-applied** (views, `public/DGEN`, `public/themes/`).
- Hyper/DGEN app code: `app/**/DGEN/*`, extra artisan commands, custom eggs.
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
- **License key.** `hyper/license/` contains the ionCube runtime key for this
  release. The panel will not run without it (and the ionCube Loader).
- **Phone-home.** The panel contacts `license.dgenx.net` / `hyper-r2.dgenx.net`
  for update checks and license verification.
- This repo is provided for the repository owner's own use. If you did not
  obtain a Hyper Game Panel license, do not deploy it.

## Updating

```bash
cd /var/www/pterodactyl && sudo bash hyper_fetch.sh
```

See [`hyper/README.md`](hyper/README.md) for details and `--update` support in
`hyper/install.sh`.