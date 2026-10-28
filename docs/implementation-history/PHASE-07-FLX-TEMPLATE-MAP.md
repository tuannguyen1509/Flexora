# PHASE 07 — FLX Template Map Foundation

Date: 2026-10-28 (Asia/Saigon). Baseline: **f379dac** (`feat: add FLX field engine and Advanced admin UI`). Before coding, status/working diff/cached diff were clean. Only the existing **localhost:8090** WordPress stack was used; final active theme is **Flexora Child Starter**. No real-site backup, clone, migration, database reset or new stack was used.

## Registry and lifecycle

Canonical Parent `flexora/`; Child examples `flexora-child-starter/`. New internal classes:

- `Fields/Runtime.php`: one shared instance of the existing Phase06 Engine, with unchanged field schema/renderer/type contracts.
- `Fields/AdminAssets.php`: shared asset declarations/conditional controls, extracted from Advanced. Shared `controls.css` separates field presentation from Advanced navigation/layout.
- `Templates/MapRegistry.php`: register, validation, ID/file lookup, list, seal; duplicate ID/file/group and late/invalid registrations throw explicit exceptions.
- `Templates/Runtime.php`: map registration lifecycle, public API composition, meta-cache invalidation, Page admin wiring.
- `Templates/Resolver.php`: Page context, WordPress template slug lookup, map metadata, request-cached payload reads and component preflight.
- `Templates/PageAdmin.php`: native Page edit panel, save authorization/validation/merge and errors.
- `Data/PostMetaStorage.php`: native Page meta envelope/expected-revision updates.

Public registration is `flx_register_template_map(array)` during **`flx/template-maps/register`** (`after_setup_theme:36`), after custom field types at Phase06 priority35. This is intentionally separate from component `flx/register`: maps must validate against already-registered field types. Maps seal immediately after that event. Child authors never need an internal registry/container.

Map ID convention: local stable slug (`example`, `example-alt`); label and file can change independently. Template path is portable/relative PHP path. Groups have stable ID/label/optional description and the **same associative field definitions used by Phase06**. Group scope creates `hero.title`; map ID is not repeated in each field. This scopes the existing engine; it does not introduce another field type system.

## Storage and API

One protected meta root per Page:

```text
_flx_template_data = {
  format_version: 1,
  revision: integer,
  maps: {
    example: {map: example, version: 1, data: {hero.title: value, features.items: [rows]}},
    example-alt: {map: example-alt, version: 1, data: {...}}
  }
}
```

Values use stable scoped field keys and ordered repeater rows with `_id`. Existing unknown root/map/field/row branches are preserved. Saving one Page/map does not write `flx_settings` or modify another map. A → B → A preserves A; Default Template also preserves all maps. Unknown IDs are retained, not merged into another map. Future/malformed envelopes and malformed active map entries are refused on write.

Read APIs:

```php
flx_data( 'hero.title', '' );
flx_data( 'hero' );
flx_data( 'hero.title', '', array( 'post_id' => $page_id ) );
flx_template(); // immutable descriptor properties, e.g. ->id, or null.
```

Explicit Page context takes priority, then queried Page, then current loop Page. Non-Page context is unavailable. Stored null/false/zero/empty string/empty list never trigger fallback. Missing fields return an explicit schema default if defined, otherwise caller fallback. Group reads gather registered fields. Defaults are not persisted by reading. No migration/normalization occurs in reads.

Resolver caches the envelope once per Page/request; native meta add/update/delete invalidates the canonical request cache. There is no new persistent cache. No query-all-posts/schema-discovery content scan is introduced. Registry descriptors are code-owned and eagerly validated; only the selected Page/map is resolved/read/rendered, not data for all maps/pages.

## Page Admin, save and template switching

Mapped Pages use the native WordPress **Classic editor**, with **Flexora Template Content** meta box. This provides a supported `wp_editor` lifecycle for rich repeater fields. Unmapped Pages retain their existing editor; there is no empty map box. New Pages may explicitly opt into Classic via `flx_template_editor=1`, choose a WordPress template, save/reload, and receive the matching field panel. Full block-editor mapping integration is deferred; no Gutenberg replacement or visual builder was created.

Template change is save/reload, not a brittle JS live switch. The server resolves the new template through `get_page_template_slug`; submitted old-map fields cannot select the target schema and are not applied to the new map. Previously saved data is preserved. Native WordPress controls discovery of Parent/Child template files.

Save verifies Page type, `edit_post` capability, Page-bound nonce, autosave/revision rules, active map, posted schema version, completion marker and revision; it then uses the shared Field Engine normalize/validate/sanitize/merge pipeline. Failed map validation does not partially write valid siblings. Invalid scalar input/errors are briefly retained in a user+Page transient for correction; diagnostics contain bookkeeping only, not raw input/nonces/secrets. Native Page title/content updates are separate from the map payload transaction.

Native actual autosave revisions were created/tested. They and revision-target submissions leave map data intact. Native Page revision restore was exercised and did not corrupt current map data. **Map meta historical snapshot/restore is not enabled**: restoring a Page revision leaves latest map data, not historical map data. This is a documented future revision-support gate, not claimed PASS for map-meta restore.

## Schema and diagnostics

Stored map/schema version is explicit. A descriptor bumped to v2 detects stored v1 through `template_schema_version_mismatch`; reading leaves version1 untouched. No automatic migration/rename inference is implemented. An explicit valid editor save writes the current schema version while preserving unknown fields. Old IDs remain old data; renamed IDs get missing/default values.

Unknown map IDs emit `template_unknown_map`, retain data and do not fatal. Mapped admin panels display diagnostics; an unmapped Page with orphaned map data receives an admin warning without an empty fields panel. Frontend diagnostics are available through `flx()->diagnostics()` and existing debug logging, not echoed as stack traces.

## Components, Child fixture and frontend

The Child ships `page-templates/flx-example.php`, `flx-example-alt.php` and `inc/template-maps.php`. These generic starter examples intentionally ship in Child ZIP; they contain no prefilled live content or branding. Example maps Hero text/editor/media/button and Features typed repeater with title/editor/image/enabled.

Templates exclusively use FLX data APIs and context escaping (`esc_html`, `wp_kses_post`, `esc_url`, `wp_get_attachment_image`). Empty images, URLs/labels and lists are guarded. Disabled features are omitted by template logic; row order is retained.

A **debug-only synthetic** component (`flx.fixture.example`) owns Child `assets/css/pages/example.css`. The map declares its component ID, resolver preflights at `wp_enqueue_scripts:15` before flush/head, and real browser verifies one stylesheet in head. Renderer is not called to discover assets; no consumer calls the asset directly. No production Hero/component/provider feature was added. When WP_DEBUG is off this synthetic declaration is absent; templates remain valid with base shell styling.

Admin Page edit reuses media/editor/repeater JS and shared control CSS; it does not load Advanced navigation CSS. Default Page and frontend do not load FLX field admin assets. All third-party providers are native WordPress APIs; no vendor was added.

## Verification

| Suite | Actual result |
| --- | --- |
| PHP 7.4.33 portable | **486 assertions**, 69 PHP files lint |
| PHP 8.3.35 Docker | **486 assertions**, 69 PHP files lint |
| Existing JS fixture | **23 PASS** |
| Existing real WP lifecycle/Parent/Child | **61 PASS** |
| Real WP field/storage primitives | **16 PASS** (15 retained + valid URL regression) |
| Real WP Template Map | **56 PASS** |
| Existing real Chromium frontend | **38 PASS**, 19 Parent +19 Child |
| Existing Advanced Chromium | **76 PASS** |
| New Template Map Chromium | **42 PASS** |
| Totals | **133 real WP**, **156 browser**, no baseline coverage removed |
| Browser/runtime | Chromium156.0.8078.4, WordPress7.1.3, PHP8.3.35, existing MariaDB11.4 |
| Integrity | **435/435 AZ hashes identical**, zero missing/added/changed |

Portable engine tests explicitly use WP primitive stubs; real WP tests cover actual primitives/storage/nonce/capabilities/revisions separately. The two preflight assertions that previously required absent data/template APIs now require their real implementation; count/preview version protections remain. Full API v1 is still not claimed.

Real WP checks cover Page/Child template discovery; API field/group/default/fallback/explicit context; falsey presence; request cache invalidation; read-without-write; unknown preservation; A/B isolation; duplicate/schema guards; partial saves; invalid nonce/capability/post type/map/field/repeater/version/completion; stale revision; actual autosave/native revision restore; v1→v2 diagnostics; future/malformed storage refusal.

Browser covers native UI Page creation/template selection; panel/controls; actual Media Library; rich top-level and repeated editors; duplicate/stable IDs/order; save/reload; frontend text/rich/image/button/order/disabled rows; component CSS preflight; A→B→A; A→Default→A; absent default panel/assets; mobile390×844 without horizontal overflow; native HTML5 URL validation and separately forced server-side invalid URL rejection without data corruption. The server check explicitly bypasses client validation only in automation, not runtime.

One real integration bug in Phase06 was discovered/fixed: passing the field schema as a second argument to `esc_url_raw` made it an incorrect allowed-protocol list and blanked a valid URL. Built-in sanitizer adapters now pass only their intended value argument; a real WP regression confirms URL persistence. No second field engine was introduced.

Evidence is ignored under `local/results/phase07-browser.json`, `phase07-frontend.png`, `phase07-admin-mobile.png`. Latest browser fixture URL/ID is in that JSON for local review. `tools/Test-TemplateMaps.ps1` runs portable maps + real WP + authenticated browser; run `Test-Fields.ps1` first for local image/security fixtures. Tests create only local development fixtures; no credentials/data are committed or shipped.

## Build and Git

Parent/Child preview versions both **0.3.0**, runtime API still **0.1.0**; `api ^1.0` false. This minor preview introduces the next foundation capability without claiming production1.0.

- `dist/flexora-0.3.0.zip`: **60 production files**, SHA256 `9843352C4167A934F6600858145708056B48AFD51CEECC9018A5CA7593249781`.
- `dist/flexora-child-starter-0.3.0.zip`: **6 production files**, SHA256 `2AFC077DB7D25FD2A04ED6E3519A4D8AC8D8C0D4C93AE97C28EF66BCDAB5B683`.
- Verified theme roots/entrypoints; no docs, tests, reference, local infrastructure, credentials, DB, uploads, logs or historical source in ZIPs.
- Commit after PASS: **`feat: add FLX Template Map foundation`**. Final response gives resulting hash and clean Git status; the report is included in that commit.

## Phase07 files committed (38)

- `README.md`
- `docs/AI-CODING-RULES.md`
- `docs/CHILD-API-QUICKSTART.md`
- `docs/FIELD-ENGINE.md`
- `docs/TEMPLATE-MAP-QUICKSTART.md`
- `docs/implementation-history/PHASE-07-FLX-TEMPLATE-MAP.md`
- `flexora-child-starter/README.md`
- `flexora-child-starter/assets/css/pages/example.css`
- `flexora-child-starter/functions.php`
- `flexora-child-starter/inc/template-maps.php`
- `flexora-child-starter/page-templates/flx-example-alt.php`
- `flexora-child-starter/page-templates/flx-example.php`
- `flexora-child-starter/style.css`
- `flexora/assets/admin/advanced.css`
- `flexora/assets/admin/controls.css`
- `flexora/assets/admin/fields.js`
- `flexora/functions.php`
- `flexora/public-api.php`
- `flexora/src/Admin/Advanced.php`
- `flexora/src/Core/Kernel.php`
- `flexora/src/Data/PostMetaStorage.php`
- `flexora/src/Fields/AdminAssets.php`
- `flexora/src/Fields/BuiltinTypes.php`
- `flexora/src/Fields/Runtime.php`
- `flexora/src/Templates/MapRegistry.php`
- `flexora/src/Templates/PageAdmin.php`
- `flexora/src/Templates/Resolver.php`
- `flexora/src/Templates/Runtime.php`
- `flexora/style.css`
- `tests/browser/template-maps.cjs`
- `tests/component-preflight.php`
- `tests/fields-wp.php`
- `tests/foundation.php`
- `tests/template-maps-wp.php`
- `tests/template-maps.php`
- `tools/Test-Foundation.ps1`
- `tools/Test-TemplateMaps.ps1`
- `tools/test-php.sh`

## Limits and scope

Deferred: block-editor mapping UX, template live-switch, map-meta historical revision restore, general CPT/term/user mapping, nested repeaters, migration/rename tools, Content Pack/backups, production page design, Header/Footer/WooCommerce and all other modules. No real-site testing/clone was performed. Current native meta previous-value/revision checks are not a distributed migration/locking coordinator. Manual screen-reader and browser matrix beyond Chromium were not run.

**PHASE 07 FLX TEMPLATE MAP FOUNDATION COMPLETED — COMMITTED**
