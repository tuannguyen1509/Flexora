# Vui Khỏe — restored original and Flexora comparison

Test date: 2026-10-28 (Asia/Saigon). Scope: restore the supplied backup locally, retain the original site, and test a separate copy with Flexora. No production cut-over or feature migration.

## Running sites

| URL | Purpose | Active theme |
| --- | --- | --- |
| http://localhost:8090/ | Existing Flexora development site, preserved | Flexora Child Starter |
| http://localhost:8091/ | Restored Vui Khỏe original | Bricks / Bricks Child |
| http://localhost:8092/ | Independent database/content copy for comparison | Flexora 0.1.0 preview |

Sandbox Docker project: `flx-vuikhoe-test`. WordPress 7.1.3, PHP 8.3.35, MariaDB 11.4 image. The two restored sites have independent databases and wp-content volumes. Canonical `flexora/` and Child Starter are mounted read-only. No Flexora runtime source changes were needed.

## Restore and local adjustments

Input: `vuikhoe-vn-20261019-162931-2j3msb7mlo95.wpress` (2,092,923,388 bytes). Read-only archive inspection/extraction with `tools/inspect-wpress.php`: 46,807 entries, 1,888,044,772 payload bytes, checked supplied entry CRC32 values. Source backup was not overwritten.

Restored package metadata specifies Bricks/Bricks Child and 26 active plugins. Restore reinstated those metadata values because the archive SQL intentionally leaves activation options blank. Published content includes 106 products and 37 pages.

Local-only changes: URLs rewritten with serialized-aware WP-CLI search-replace; hosting MU plugins and cache drop-ins quarantined; FlyingPress deactivated after a local filesystem/output-buffer fatal; 25 plugins remain active. PHP WordPress memory limit raised to 512 MB for this plugin stack. Content moved to Linux Docker volumes to avoid NTFS bind-mount overhead.

Both restored PHP services run on an internal Docker network. Separate HTTP gateways publish only loopback ports. A local MU guard blocks WordPress mail and HTTP requests; cron is disabled. Browser checks also block external requests. No order, payment, form submission, or live service interaction was tested.

Private extracted files, database dumps, environment secrets, screenshots, and raw results are under ignored `local/runtime/vuikhoe-test/`; they must not be committed or shipped in theme ZIPs.

## Actual Chromium results

Four pages per theme were visited in a real headless Chromium browser: homepage, one product, shop archive, empty cart. All eight visits returned HTTP 200, with no detected fatal-error text, no uncaught page JS errors, and no local asset HTTP failures in the observed requests.

| Page | Original body text length | Flexora body text length | FLX skip link / keyboard focus |
| --- | ---: | ---: | --- |
| Homepage | 7,442 | 77 | Exactly 1; Tab then Enter focuses main |
| Product | 4,290 | 3,224 | Exactly 1; Tab then Enter focuses main |
| Shop | 2,386 | 1,701 | Exactly 1; Tab then Enter focuses main |
| Cart | 963 | 136 | Exactly 1; Tab then Enter focuses main |

Original Bricks element counts: 1,166 / 514 / 503 / 250. Flexora has zero Bricks elements and exactly one main landmark on each tested page. Original cart had no detected main landmark. Screenshots were captured after the keyboard check, so Flexora main focus outlines are expected.

Evidence: ignored `results/original.json`, `results/flx.json`, and `results/original-0.png` through `original-3.png`, `flx-0.png` through `flx-3.png`, all beneath the private sandbox directory. Browser harness: `browser.cjs` in the same directory. These observations are a frontend smoke check, not a complete business-function test suite.

## Findings and limits

**High — Bricks layout dependency:** front page 7164 stores `_bricks_page_content_2`, while its WordPress `post_content` length is zero. Switching to Flexora successfully boots WordPress but does not render the Bricks homepage. The screenshot confirms only the generic shell and page title. This is not a drop-in storefront replacement. No Bricks compatibility renderer or automatic content migration was added.

**High — commerce readiness not established:** product/shop content renders without a fatal in this sample, but appearance, cart operations, checkout, shipping, tax, payment, customer sessions, and transactional email have not been validated. The empty cart HTTP 200 is not proof that cart functionality is equivalent.

**Medium — plugin warnings:** ACF triggers an early translation-loading notice. WooCommerce logs a `wc_enqueue_js` deprecation on the tested stack. These are not suppressed as proof of compatibility; frontend debug display is disabled and warnings remain in the private logs.

**Medium — local restore differences:** FlyingPress is off, hosting integrations are quarantined, and outbound services are blocked. Some WP-CLI serialized replacements skipped uninitialized `WC_Shipping_Rate` objects. Full session/cache URL conversion is therefore not claimed.

Image loading is not a visual parity assertion: original homepage had 88 of 107 images loaded at capture, with lazy loading/external restrictions in effect. Responsive layouts, authenticated administration, accessibility beyond the skip-link keyboard check, and all plugin settings remain untested. No full foundation regression rerun was needed for this read-only theme comparison; historical Phase05 counts are not reported as new test results.

## Integrity and restart

AZ reference content verified again: **435 expected / 435 actual; 0 hash mismatches**. Existing Flexora site at 8090 also returned HTTP 200 after restarting its stopped containers. No AZ source, canonical theme code, or original backup content was modified.

Restart this sandbox from workspace root:

```powershell
docker compose -f local/runtime/vuikhoe-test/docker-compose.yml up -d --wait db wordpress gateway flx-preview flx-gateway
```

Do not run `down -v` if these local restored datasets need to be retained. No credentials or live user records are included in this report.
