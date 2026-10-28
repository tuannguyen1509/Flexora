# PHASE 06 — FLX Field Engine + Advanced Admin UI

Date: 2026-10-28 (Asia/Saigon). Author: Tunlamweb. Canonical runtime: `flexora/`. Primary integration/browser environment: **http://localhost:8090**, active **Flexora Child Starter**. No other WordPress site was tested or cloned for this phase.

## Git baseline

Before coding, inspected `git status`, working diff and cached diff. Existing canonical foundation was already committed as `40855b7`; staging was empty. Two valid, previously untracked deliverables from the earlier task were committed separately after baseline verification:

- **`592aabc` — `chore: finalize Flexora local runtime foundation`**.
- Files: earlier restore report and generic archive inspection tool. They are not part of the Phase06 runtime/feature commit.
- Working tree after baseline commit: clean.

Baseline rerun: 323 PHP assertions on portable PHP 7.4.33 and Docker PHP 8.3.35; 23 JS fixtures; 61 real WordPress assertions; 38 real Chromium assertions (19 Parent + 19 Child). AZ reference 435 hashes unchanged.

## Implemented architecture

| Boundary | Implementation |
| --- | --- |
| Field type contracts | `flexora/src/Fields/TypeRegistry.php`, `BuiltinTypes.php` |
| Typed schema/defaults/partial patch/validation | `flexora/src/Fields/Engine.php` |
| Storage-neutral WordPress renderer | `flexora/src/Fields/Renderer.php` |
| Section/group registry | `flexora/src/Admin/SectionRegistry.php` |
| Admin lifecycle/save/menu/assets/UI | `flexora/src/Admin/Advanced.php` |
| Neutral settings and dev-only fixtures | `flexora/src/Admin/DefaultSections.php` |
| Native option/revision adapter | `flexora/src/Data/SettingsStore.php` |
| Conditional UI/behaviors | `flexora/assets/admin/{advanced.css,fields.js,editor.js,media.js,color.js,repeater.js}` |

All 13 required field types are implemented: **text, textarea, editor, number, url, email, select, radio, toggle, color, media, gallery, repeater**. Typed repeater children include every non-repeater primitive, including gallery. Nested repeater throws an explicit unsupported-schema exception; no text fallback.

Extension: Child declares `flx_register_settings_section()` inside `flx/settings/register`. All registered sections appear automatically; there is no section allowlist. Custom field types register callback contracts inside `flx/fields/register`. Type callbacks are `render`, `normalize`, `validate`, `sanitize`, `default`. See [full schema and extension contract](../FIELD-ENGINE.md) and [current AI rules](../AI-CODING-RULES.md).

Parent/package version is **0.2.0 preview**. Runtime Public API version deliberately remains **0.1.0**; `api ^1.0` remains unsupported. Adding this engine does not falsely claim the complete v1 target contract.

## Advanced UI

WordPress menu: **Flexora → Advanced**. Desktop uses section navigation beside grouped cards; mobile changes to wrapping section links above the form. CSS tokens cover primary/bg/surface/border/text/muted/radius/shadow. Controls include help/error text, keyboard focus, accessible switch semantics, row collapse/duplicate/remove, drag handles and keyboard up/down buttons.

General and Design are neutral native settings containers; they do not yet apply settings to frontend presentation. Field Playground is registered only with `WP_DEBUG` or `FLX_DEVELOPMENT`, with all types and Services repeater fixture. Development-off omission is covered at registry level; the running development site's debug constant was not changed. Reset/import/export UI was not implemented.

## Editor and repeater gate

Top-level and initial repeater fields use actual **`wp_editor()`**; dynamic rows use **`wp.editor.initialize()`**. Browser acceptance covers add, duplicate/new editor ID, keyboard input, Code/Visual switching, move up/down, HTML5 drag-event reorder, remove/instance cleanup, rich formatting persistence and reload. Reorder preserves `_id`; duplicate generates a new `_id`; save/reload preserve both order and identity. Code mode and unsaved Code input survive a move.

An intermediate browser failure exposed stale hidden textarea style during clone/reinitialization. Fixed by synchronizing/destroying the WordPress editor, clearing stale hidden state, rebuilding wrappers and restoring Code/Visual mode. Final tests confirm visible editor usability and real iframe keyboard edits survive save/reload. There is no textarea fallback.

Drag event paths were exercised using browser `DataTransfer`/HTML5 events; a separate cross-browser native pointer-drag matrix was not run. Chromium keyboard controls and focus paths were exercised directly.

## Data and security

`flx_settings` is the only native settings option used. Field Engine itself has no option/meta storage dependency; renderer receives schema, values and errors. A future post/term/user meta adapter can reuse it, but those adapters are not implemented in this phase.

Missing input preserves existing values; explicit strings/false/0/null/empty lists have distinct typed semantics. Null requires nullable, non-required schema. Empty repeater/gallery HTTP sentinels normalize to `[]`; absence does not clear them. Unknown stored sections/fields/row branches survive partial updates. Unexpected submitted keys reject the form. Error paths use stable row IDs, and invalid scalar input is retained separately for safe correction, never persisted as display data.

Save order: capability → nonce → section/schema/completion lookup → normalization → validation → sanitization → compare-and-swap write → redirect → notice. Any validation failure is atomic. Completion marker detects truncated forms; revision check rejects stale saves. Option creation is non-autoload; CAS uses binary serialized comparison and invalidates WP caches. Direct CAS intentionally does not run generic WordPress `pre_update_option` filters; this is a scoped native adapter, not a claim of a complete data/migration API.

Browser security checks cover bad nonce HTTP403, subscriber page/save HTTP403, unexpected submitted key, truncated payload HTTP400, invalid choice, malformed repeater, invalid media and invalid input preservation. Real WP tests confirm `wp_kses_post` retains bold/list/link formatting and removes script/event attributes.

## Asset/performance checks

All own admin assets use FLX Asset Registry metadata and WordPress handles. WordPress editor/media/color remain core providers, not bundled copies. Only the current Advanced section requests required dependencies. Browser checks confirm no `assets/admin/` requests on frontend or Dashboard.

Native WP color picker exposed a foundation assumption: `wp-color-picker` is a valid physical handle in both WP_Styles and WP_Scripts. Duplicate detection now keys **type + handle**. Same-type collisions remain rejected, with two new regression assertions. This is a WordPress integration correctness fix; no new vendor/provider architecture or feature scope was introduced.

Field registration/default rendering does not seed the database or query content. Media validation/preview reads actual attachment data only when needed. No large performance benchmark is claimed.

## Final verification

| Suite | Result |
| --- | --- |
| Portable PHP 7.4.33 | **466 assertions PASS**, PHP lint 57 files |
| Docker PHP 8.3.35 | **466 assertions PASS**, PHP lint 57 files |
| Count breakdown | 323 existing + 2 handle regressions + 141 field contracts |
| Existing JS fixture | **23 assertions PASS** |
| Existing real WordPress integration | **61 assertions PASS**, Parent then Child |
| Real WP fields/storage/security primitives | **15 assertions PASS** |
| Existing Chromium frontend | **38 assertions PASS**, 19 per Parent/Child |
| Advanced authenticated Chromium | **76 assertions PASS** |
| Browser engine | Chromium **156.0.8078.4** |
| Responsive | Desktop 1440×1000 and mobile 390×844; editor/repeater included; no horizontal overflow |
| Native naming/source scan | No forbidden runtime prefixes or site-specific branding matches |
| AZ reference | **435/435**, zero content hash mismatches, zero missing/added |

Portable field tests explicitly use WP primitive stubs; actual sanitization, media and storage are verified separately on WordPress 7.1.3/PHP 8.3.35/MariaDB 11.4. No mocked suite is labelled WordPress integration. Existing tests were preserved; the version assertion was updated for Parent 0.2.0 while API stays 0.1.0.

Raw browser assertions/screenshots are ignored under `local/results/phase06-*`, including desktop/mobile with open repeater editor. Test-only images, subscriber account and playground values are local fixtures; their credentials are not in Git/report/ZIP. The storage test restores the prior settings option in `finally`. No database reset or Docker stack recreation was performed.

## Build and review scope

- **`dist/flexora-0.2.0.zip`**: 52 production files; SHA-256 `E7107B6B770B52E58D0E4A6063BC28E04426B0E35B6B83635457567E27A59B5E`.
- `dist/flexora-child-starter-0.1.0.zip`: existing minimal Child, 2 production files.
- ZIP structure verified: correct theme root and entrypoints; no docs/references/local/tests/credentials/database/uploads/logs.
- No runtime code was developed in history or reference trees. Child Starter source was unchanged.

Commit after all gates: **`feat: add FLX field engine and Advanced admin UI`**. The final response provides the resulting commit hash; this report is part of that same commit, avoiding a self-referential hash.

## Phase06 files committed (32)

- `README.md`
- `docs/AI-CODING-RULES.md`
- `docs/CHILD-API-QUICKSTART.md`
- `docs/FIELD-ENGINE.md`
- `docs/implementation-history/PHASE-06-FLX-FIELD-ENGINE.md`
- `flexora/assets/admin/advanced.css`
- `flexora/assets/admin/color.js`
- `flexora/assets/admin/editor.js`
- `flexora/assets/admin/fields.js`
- `flexora/assets/admin/media.js`
- `flexora/assets/admin/repeater.js`
- `flexora/functions.php`
- `flexora/public-api.php`
- `flexora/src/Admin/Advanced.php`
- `flexora/src/Admin/DefaultSections.php`
- `flexora/src/Admin/SectionRegistry.php`
- `flexora/src/Core/Definitions.php`
- `flexora/src/Core/Kernel.php`
- `flexora/src/Data/SettingsStore.php`
- `flexora/src/Fields/BuiltinTypes.php`
- `flexora/src/Fields/Engine.php`
- `flexora/src/Fields/Renderer.php`
- `flexora/src/Fields/TypeRegistry.php`
- `flexora/style.css`
- `tests/assets.php`
- `tests/browser/advanced.cjs`
- `tests/fields-wp.php`
- `tests/fields.php`
- `tests/foundation.php`
- `tools/Test-Fields.ps1`
- `tools/Test-Foundation.ps1`
- `tools/test-php.sh`

## Limitations / deferred

No Template Map runtime, automatic template detection, Header/Footer builder, frontend settings application, WooCommerce/business modules, general meta adapters, nested repeater, reset, Content Pack, backup/migration or real-site compatibility implementation. No additional local WordPress site or frontend vendor was introduced. Manual screen-reader review and Safari/Firefox/device matrix were not performed. PHP below 7.4 was not tested, and no new minimum requirement was declared.

**PHASE 06 FLX FIELD ENGINE COMPLETED — COMMITTED**
