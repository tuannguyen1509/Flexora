# Phase05 — Project normalization + real local WordPress

Ngày: **27/10/2026**. Product Flexora, author Tunlamweb, code prefix FLX. Version Parent **0.1.0 preview**, runtime API **0.1.0**, target contract1.0.0 chưa đầy đủ. Không feature mới ngoài minimal theme shell được yêu cầu; không site production cut-over hoặc AZ migration.

## Project structure / canonical source

```text
Flexora/
  flexora/                         # CANONICAL WordPress Parent
    style.css, functions.php, theme.json
    index.php, header.php, footer.php
    autoload.php, bootstrap.php, public-api.php
    src/{Core,Contracts,Assets,Components,Modules,Data,Fields,Templates,Support}/
    assets/{css,accessibility}/
    profiles/flx-native.php
  flexora-child-starter/            # Template: flexora
  docs/
    audit-phase-01/
    architecture-phase-02/
    implementation-history/
      phase-03/                    # complete 62-file Phase03–04 snapshot
      implementation-phase-03.zip # existing archive retained
      PHASE-05-PROJECT-LOCAL-RUNTIME.md
  references/az/{az-theme,az-theme-child}/
  tests/{fixtures,browser}/
  tools/
  local/
  dist/
  .gitignore, .gitattributes, README.md
```

**`flexora/` là source of truth duy nhất.** Bootstrap không include phase history hoặc references. Tests root đã chuyển path sang canonical source; không dùng runtime snapshot để lấy PASS. Bản snapshot vẫn đầy đủ runtime/test/report/raw output cũ, chỉ relink Markdown khi cần. Không tiếp tục phát triển trong snapshot. Source namespaces/module folder chưa cần class mới không được làm đầy bằng placeholder classes; CSS/JS Skip Link giữ co-location accessibility đã hoạt động thay vì di chuyển asset vô ích.

Parent self-contained metadata Theme Name Flexora / Author Tunlamweb / Text Domain flexora / Version0.1.0. `functions.php` chỉ load engine và ThemeSetup. Minimal shell có site title/core logo/menu nếu có, main semantic, core Loop và footer; wp_head/wp_body_open/wp_footer đầy đủ. Không phải Header/Footer builder, blog system hoặc site-specific branding. ThemeSetup khai basic supports và một shell stylesheet qua FLX registry; không data writes.

Child Starter metadata đúng Template flexora, không copy engine/vendor/assets hoặc demo data. Child functions chạy trước Parent, declarations/lifecycle của WP vẫn hoạt động. Child không cần enqueue Skip Link CSS/JS; Parent module tự preflight/render.

## Local WordPress environment

| Item | Actual runtime |
|---|---|
| Method | Docker Desktop Linux engine, Compose project `flx-development` |
| URL | **http://localhost:8090/**, bind127.0.0.1 |
| Admin | http://localhost:8090/wp-admin/ |
| Site title | Flexora Development |
| WordPress | **7.1.3** |
| Web/PHP8 regression | **PHP8.3.35**, image wordpress:php8.3-apache |
| Database | **11.4.13-MariaDB-ubu2404**, image mariadb:11.4 |
| WP-CLI | **2.12.0**, cli-php8.3 tools profile |
| Browser tools | Playwright1.64.0 / Node24.21.0 / Chromium156.0.8078.4 |
| Parent mount | Host `D:\Website Wordpress\Themes\Flexora\flexora` → `/var/www/html/wp-content/themes/flexora` |
| Child mount | Host `D:\Website Wordpress\Themes\Flexora\flexora-child-starter` → `/var/www/html/wp-content/themes/flexora-child-starter` |
| Persistent local data | named volumes `flx-development_db_data`, `flx-development_wp_data` |

Docker cached official images were available; no other local site/container was changed. Port8090 was unused. The new local DB was installed by WP-CLI, no old website data imported. Credentials generated randomly into ignored local/.env, not included here or in Git/ZIP. DB port not exposed. Admin account is local-only; read credentials from ignored file when needed.

Source is directly bind-mounted. Integration appended a unique CSS marker on host, fetched it via HTTP immediately, and restored exact original bytes in finally. Local OPcache validation explicitly set to every request (revalidate_freq0). No copy/build required for normal save/refresh. Startup is idempotent; install.sh avoids PowerShell nested-shell argument splitting and supplies local HTTP_HOST. No automated data wipe or `down -v` performed.

## Activation / real integration

| Acceptance | Result |
|---|---|
| WordPress theme discovery | **PASS** — both themes appear in real wp theme list, no broken-theme error |
| Parent activation / frontend | **PASS** — wp theme activate flexora, HTTP200 |
| Child activation / inheritance | **PASS** — wp theme activate flexora-child-starter, template flexora, HTTP200 |
| Actual WP hooks | **PASS** — after_setup_theme, wp, wp_enqueue_scripts, wp_head, wp_body_open, wp_footer all observed exactly once/request |
| Actual WP classes | **PASS** — WP_Hook, WP_Styles, WP_Scripts from real WP core |
| Registry assets | **PASS** — Skip Link CSS + shell CSS in actual styles done, footer script in scripts done; files HTTP200 |
| Skip Link | **PASS** — exactly one `.flx-skip-link`, correct main target and FLX-owned body callback priority1 |
| Canonical runtime | **PASS** — Reflection filename resolves Parent/public-api.php, even with Child active |
| Diagnostics | **PASS** — fixed requests show no FLX diagnostics; normal nested enqueue preflight accepted |
| Bind mount | **PASS** — reversible host-edit marker immediately served |

A **local-only** MU probe reads actual runtime states and appends an encoded comment only when env flag and query switch are present. It does not mock WP_Hook, WP_Styles, WP_Scripts or theme behavior, write data or register a feature. It is not part of either theme package. Browser requests go through a container-local transport proxy forwarding unchanged content from the real WordPress service; this handles localhost networking without fixtures/fake HTML.

Final active theme: **Flexora Child Starter**. Parent standalone was tested first. No external production site/theme activated or migrated.

## Integration-discovered fix

Real WordPress dispatches `wp_enqueue_scripts` **inside wp_head before stylesheet printing**. Prototype only checked did_action(wp_head), so a valid request during enqueue could incorrectly report late_style_usage (first observed for flx.shell.style). Fixed **only canonical** AssetRegistry: allow the active nested enqueue window before flush; retain strict late CSS and script cutoffs after flush/head. Added 4 regression assertions. Snapshot remains unchanged; no architecture workaround or loading all CSS.

Initial failed local install command was fixed with mounted install.sh rather than complex Windows quote nesting. A core CLI HTTP_HOST warning on first install was addressed by setting host from local URL; subsequent idempotent startup is clean. The initial FLX false diagnostic remains in Docker log as history; current HTTP probe is clean. No failure evidence was hidden or called PASS before repair.

## Tests

| Suite | Execution / result |
|---|---|
| Existing foundation/Accessibility/naming/CSS | **319 assertions retained PASS** against canonical flexora/ |
| New WP head timing regression | **4 assertions PASS** |
| PHP total | **323 assertions**, lint47 files; host **PHP7.4.33** and Docker **PHP8.3.35** PASS |
| JS behavior fixture | **23 assertions PASS**, now executed in Node24.21.0 against canonical JS |
| Real WordPress HTTP/runtime integration | **61 assertions PASS**, Parent then Child, actual hooks/classes/assets/DOM/bind mounts |
| Real browser | **19 assertions per Parent/Child**, **38 total PASS**, Chromium156.0.8078.4 |
| Baseline integrity | **435 expected / 435 actual, identical SHA-256 + byte sizes** |

Browser coverage: homepage200/title, one skip link/main, actual JS execution, correct target, offscreen-before-focus, first Tab focus, visible focus ring, Enter→main focus/tabindex, CSS+JS network200 exactly once, no uncaught JS or console errors, active Parent/Child identity, reduced-motion computed scroll behavior. No claim of full WCAG certification, complete browser matrix or visual design acceptance.

Host PHP8.4 remains policy-blocked; not executed and not bypassed. Docker PHP8.3 is a separate permitted runtime actually used for WordPress and fixture testing. No new minimum-version claim based on one local matrix. PHP7.4 is only CLI regression, not public server runtime.

Reproducible tools:

- `tools/Test-Foundation.ps1 -Php <path>` (canonical fixtures + source integrity).
- `tools/test-php.sh` in Docker PHP image (canonical PHP8 regressions).
- `tools/Test-LocalWordPress.ps1` (actual site acceptance).
- `tools/Test-Browser.ps1` (Parent/Child real Chromium + JS fixture).
- `tools/Verify-SourceIntegrity.ps1` (relocated AZ manifest comparison).

New run logs are under ignored local/results; historical raw results remain under phase-03/tests. Browser tooling installed only into a disposable Docker image; no Node/npm/vendor files in Parent/Child.

## Build / source safety

`tools/build-theme.ps1` creates:

- **dist/flexora-0.1.0.zip** — 38 production files under single root flexora/.
- **dist/flexora-child-starter-0.1.0.zip** — 2 production files under Child root.

ZIP checks required style/functions/index and excludes docs, references, history, tests, local configuration/credentials, Git and raw results. Theme runtime is self-contained. ZIPs are ignored preview artifacts, not a 1.0 production release.

AZ original Parent330 + Child105 = **435 hashes identical**, modified0/missing0/added0 in reference theme trees. Manifest still relative by original theme names, physical root now references/az. Source was moved, not renamed/refactored internally. `.gitattributes` prevents Git line-ending conversion of reference and historical snapshot content. Markdown links in moved docs were checked/relinked without rewriting audit conclusions.

Git already existed (HEAD f45375e); initial working tree contained 56 pre-existing Phase04 paths changed/untracked. No init, reset, stage or commit performed. Staging preserved. A normalization commit was not automatically combined with that pre-existing work; review/commit remains explicit. No unrelated containers or website databases modified.

## Limitations / scope

Local integration gate is now passed for this recorded matrix; still preview, not full Public API1.0 or production release. No Advanced, Template Map, Header/Footer system, Mega Menu, WooCommerce, product/blog/home builders, Content Pack, application module or migration tooling implemented. No old website cut-over. Live data portability and full platform/browser/plugin compatibility remain future work. Named volumes contain development data only and are not part of code backups.

Canonical runtime must now be changed **only in flexora/**, current tests/tools at repository root. Stop here; no next feature/seam started.

**PHASE 05 PROJECT NORMALIZED — FLEXORA RUNNING ON REAL LOCAL WORDPRESS**
