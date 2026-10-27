# Isolated Flexora WordPress development

Docker Compose project `flx-development`, separate from other local sites. Web binds loopback **http://localhost:8090**. Defaults tested: WordPress **7.1.3**, PHP **8.3.35**, MariaDB **11.4.13**, WP-CLI **2.12.0**. Image tags in `.env.example`; local `.env` can choose another reviewed image. These are tested versions, not a new minimum-support policy.

## Start

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File local/start.ps1
```

First run creates ignored `local/.env` with independent random local secrets and starts WordPress/database. WP-CLI installation is idempotent and uses mounted install.sh so Windows shell quoting never exposes or splits credentials. Site title **Flexora Development**. Read local credentials directly in your ignored file when logging in; do not copy them into docs or Git. `.env.example` contains placeholders only.

If port8090 is occupied on first run, pass `-Port 8091` (or another free port). Existing `.env` controls subsequent runs; `-Port` does not silently change a configured site's URL. To change an existing URL, plan both `.env` and core home/siteurl changes explicitly.

## Source and data

- Host `../flexora` → `/var/www/html/wp-content/themes/flexora`.
- Host `../flexora-child-starter` → `/var/www/html/wp-content/themes/flexora-child-starter`.
- Save PHP/CSS/JS on host, refresh browser. Local php-dev.ini sets OPcache timestamp validation with revalidate_freq=0; bind-edit/HTTP test confirmed served CSS updates without copy/build.
- Named volumes `flx-development_db_data` (database) and `flx-development_wp_data` (core/uploads/runtime logs). No DB/uploads/cache directory in source. DB port not published.
- `mu-plugins/` is a read-only **local-only test probe**, gated by env flag and `?flx_probe=1`. It reads actual WP runtime state and appends a non-secret diagnostic comment. Not in Parent/Child ZIPs, not needed for normal theme behavior.
- `WP_DEBUG`/log enabled locally, display disabled; theme logs only redacted diagnostic identifiers, never raw props/passwords/stack traces. A diagnostic from the initial enqueue-timing issue remains in volume log as history; fixed requests have no FLX errors.

```powershell
docker compose -f local/docker-compose.yml ps
docker compose -f local/docker-compose.yml run --rm cli wp theme activate flexora
docker compose -f local/docker-compose.yml run --rm cli wp theme activate flexora-child-starter
docker compose -f local/docker-compose.yml down
```

`down` retains data volumes. Do not add `-v` unless you explicitly intend to destroy this local site's data. This is a new development DB, not an import of any old website.

## Test

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File tools/Test-LocalWordPress.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File tools/Test-Browser.ps1
docker run --rm --mount "type=bind,source=$($PWD.Path),target=/workspace,readonly" --entrypoint sh wordpress:php8.3-apache /workspace/tools/test-php.sh
```

Local WP integration runs Parent then Child, leaves Child active. It checks actual hooks/classes/enqueued-and-printed assets, HTTP/DOM and a reversible bind-mount edit (bytes restored in finally). Browser script checks keyboard Tab/Enter, focus target, CSS/JS network and errors in actual Chromium. Disposable browser image uses Playwright1.64.0 only for tooling; no browser/npm vendor copied to themes. Its internal localhost proxy forwards to the real WordPress service so stored localhost URLs remain correct; no fake HTML or response fixtures in browser checks.

PHP7.4 host fixture command is in root README. Host PHP8.4 executable remains policy-blocked; Docker PHP8.3 is an independently permitted runtime actually tested, not a claim that Windows8.4 ran. No security policy bypass.
