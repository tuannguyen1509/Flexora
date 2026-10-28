# Phase08 — FLX Header Foundation

Product **Flexora**, code prefix **FLX**. Parent/Child preview **0.4.0**; runtime API **0.1.0**, not full target v1. Canonical runtime remains `flexora/`; only the existing primary WordPress site **http://localhost:8090** is used.

## Baseline and scope

Phase07 baseline: `3de78ffc0ba1d58e616ac0ce118c299167de0b50`, `feat: add FLX Template Map foundation`. Working tree and staging were clean before Phase08 began. During work, user reference inputs `tests/themes/` and `tests/themes.zip` appeared as untracked files. They are read-only review inputs, excluded from the Phase08 commit and production ZIPs; they are not removed or hidden. An unrelated untracked `img/` directory also appeared later and is preserved/excluded.

No Footer work, Mega Menu, Header Builder, sticky header, search overlay, cart/account/social/CTA system, business module, migration, vendor or settings were added. Parent already supported native `custom-logo`; that support is retained. The prototype menu location `flx-primary` is replaced in registration by the requested official `primary` location. Existing stored assignments are not deleted/renamed/migrated; the local test fixture explicitly assigns its own development menu to `primary`.

## Header architecture

`functions.php` attaches `FLX\Header\Integration`; `header.php` keeps only the document shell, `wp_head()`, `wp_body_open()` once and `flx_render('flx.header')`.

```text
Header Integration (mandatory theme shell)
  flx/register priority12 → declare descriptors, no enqueue/query/write
  wp_enqueue_scripts priority10 → flx_use_component('flx.header')
    flx.header
      flx.header.logo
      flx.header.navigation
      flx.header.mobile-toggle
      flx.header.style
      flx.header.mobile-navigation (behavior)
        flx.header.native (native provider descriptor)
        flx.header.script
  Asset Registry flush100 → WordPress queues → wp_head/styles → footer/script
  header.php → component RenderResult → output once
```

Header is mandatory theme integration, not an optional application module or a fake enabled/disabled setting. No disabled-module test is claimed for it; existing optional-module disabled/scope regressions remain intact. Components return HTML, do not enqueue or add lifecycle hooks, and do not persist data. Native WordPress menu/image reads are expected; this is not a claim of zero real menu queries.

| Contract | Implementation |
|---|---|
| Root | `FLX\Header\Layout`, semantic `header.flx-header[data-flx-header]`, reusable `.flx-container` |
| Logo | `FLX\Header\Logo`, native `custom_logo`, attachment helper/dimensions, site title/home fallback, escaped site description |
| Navigation | `FLX\Navigation\Primary`, native `wp_nav_menu()` hierarchy/current classes; `fallback_cb=false`, no custom Walker |
| Toggle | `FLX\Header\MobileToggle`, native button, Parent-owned static SVG, `aria-expanded`, instance `aria-controls` |
| Instance IDs | `wp_unique_id`; native `nav_menu_item_id` filter scopes LI IDs only for this renderer, leaves other menus untouched |
| CSS asset | `flx.header.style`, physical style handle `flx-header`, `assets/header/header.css` |
| Script asset | `flx.header.script`, physical script handle `flx-header`, `assets/header/header.js`, footer |
| Assets scope | Frontend only; component preflight, no direct consumer asset IDs; WP style/script queues are separate namespaces |

No assigned Primary Menu means no nav/toggle markup, with site identity still rendered. No logo or an invalid/missing attachment produces readable site title, not a broken image. Menu data is rendered through WordPress APIs; text/URLs/attributes are escaped. No raw user SVG is accepted.

## REUSE AUDIT

The [complete reuse matrix](PHASE-08-REUSE-AUDIT.md) was written before follow-up edits and covers all six requested source files. Their `tests/themes` and `references/az` copies are byte-identical. No reference file is required by runtime.

| Area | Source | Decision | FLX implementation | Reason |
|---|---|---|---|---|
| Logo / title / escaping | AZ `header-components.php` | ADAPT | `Header/Logo.php` | Retain native image/home/title/escaping logic; replace AZ lookup with WP custom-logo, return markup and validate attachment. |
| Menu / empty state | AZ `header-components.php`, `core/mobile-menu.php` | ADAPT | `Navigation/Primary.php`, `Header/Layout.php` | Retain native menu rendering and early empty return; remove AZ options/style selection and duplicate mobile menu. |
| Mobile submenu | AZ `js/mobile-menu.js` | ADAPT | `assets/header/header.js` | Retain direct-child discovery, separate disclosure buttons, hidden/ARIA state, Escape/focus return, breakpoint reset; port localized changing labels and destination-click closing. |
| Focus / viewport clamp | AZ Child `js/header-mega-menu.js` | ADAPT | Same ordinary Header behavior | Retain equivalent focusout/pointer logic; port bounded positioning and remeasure on resize, without Mega Menu feature. |
| Keyboard regressions | AZ `tests/mobile-menu.cjs` | ADAPT / PORT | `tests/browser/header-regression.cjs` | Preserve valuable nested/state/label/Escape/focus/selection/resize cases in actual Chromium DOM, not a mock that only matches new code. |
| Init / invalid target / ancestor reset | Both source versions reviewed | REWRITE / retain FLX where stronger | FLX per-root guard, safe no-op, descendant reset | AZ lacks repeated-init/missing-panel guards; both needed better reset of descendant expanded state. |
| Structure and CSS | Legacy branded drawer versus current FLX | REWRITE / retain current FLX | One nonmodal semantic navigation, FLX responsive CSS | A modal drawer and duplicate branding are outside the foundation's scope; no engine copy or unused storefront CSS. |
| Storefront Woo/hotline/CTA/sticky | AZ Child `inc/header-storefront.php` | DEFER | None | Business/storefront dependencies and explicitly excluded features; generic identity/menu helpers already covered. |
| Mega Menu/modal variants | AZ Child Mega Menu; Parent drawer logic/tests | DEFER | Candidate only | No global mega manager, modal focus trap, scroll lock, shade or legacy style matrix is shipped. |

No whole-file `REUSE DIRECTLY` is claimed: these files couple AZ names/options, different modal structures or excluded features. Proven native primitives and behavioral algorithms are reused through adaptation. Documentation records origin, retained logic, changes and reasons; runtime contains only FLX identifiers. `docs/AI-CODING-RULES.md` now requires Parent/reference search and reuse/adapt before creation.

## Desktop, mobile and accessibility

Desktop above900px: wrapped native menu, pointer and focus-accessible ordinary submenus, current-item styling, separate disclosure buttons. Tab/Shift+Tab traverse native links/buttons; Enter/Space use native button activation. Escape closes the innermost expanded branch and returns focus to its control. Focus/pointer leaving a desktop branch closes it. Ordinary first-level submenus are clamped to the viewport and repositioned on resize, including within the desktop breakpoint.

Mobile at/below900px: JS enhances a visible baseline nav into closed/open disclosure navigation. Root toggle visibility and `aria-expanded` agree with `hidden`. Escape closes/root focus returns; hidden links are excluded from normal tab order by actual hidden DOM. Sibling branches remain independent on mobile; closing an ancestor resets child expanded states. Destination click closes the mobile nav without preventing native navigation; a native fragment link may focus its target after the close handler, and focus is not left inside hidden nav.

Breakpoint changes reset nested/root states and repair focus when a focused control becomes hidden. Per-header initialization is idempotent, including a second script evaluation. Missing navigation or mismatched controls target safely retains the no-JS baseline. No modal role, body scroll lock or focus trap is needed for this ordinary navigation pattern.

No-JS is tested in actual Chromium with JS disabled: mobile navigation/nested links remain visible and reachable, inert toggle stays hidden; desktop submenu is reachable with focus. Skip Link remains first keyboard stop, targets the real main, and `wp_body_open` is not duplicated. Focus uses an explicit 3px outline. No pixel parity or formal accessibility certification is claimed.

## Child extension contract

Child Starter inherits Parent Header without copying `header.php`, internal classes, SpecV1 registration or knowledge of CSS/JS/provider IDs. Native WordPress controls supply logo/site identity/menu.

Supported slots: `flx/header/before`, `flx/header/after`, `flx/header/brand/after`, `flx/header/navigation/after`. Trusted Child callback output is captured into the component RenderResult; Child must escape its own markup. Portable regression proves a Child-style hook extension and compatibility with the existing component-props filter. This fixture does not ship a demo override in the Child.

Native WordPress Child `header.php` override remains available when changing the whole document shell is necessary; prefer extension hooks first and retain head/body/footer lifecycle and semantic main. No new public replacement/variant/provider-swapping API is invented: duplicate component IDs remain rejected. See [Child guide](../CHILD-API-QUICKSTART.md).

## Verification

All suites below PASS on the final runtime. No baseline assertions were removed:

| Suite | Assertions |
|---|---:|
| PHP7.4.33 portable | 510 = Phase07 486 + Header24 |
| PHP8.3.35 Docker | 510, same cases |
| Existing skip-link JS fixture | 23 |
| Real WP lifecycle/Parent/Child | 61 |
| Real WP Field Engine/storage | 16 |
| Real WP Template Map | 56 |
| Header real WP | 21 |
| Real WP total | 154 |
| Existing real Chromium Parent/Child | 38 |
| Existing Advanced Chromium | 76 |
| Existing Template Map Chromium | 42 |
| Header real-site Chromium | 83, including22 keyboard/focus assertions |
| Header native custom-logo Chromium | 14 |
| Header reuse regression Chromium DOM fixture | 39 |
| Browser total | 292 = Phase07 156 + Header136 |

The39 reuse assertions are isolated DOM fixtures using the production behavior/CSS, not39 WordPress integration assertions. They complement actual primary-site navigation and no-JS coverage. Keyboard22 is a subset of the83 real Header assertions, not added again. PHP fixtures use WordPress primitive stubs; actual native queues, menu/image functions, lifecycle, settings and map persistence are checked separately in real WordPress.

Runtime: existing WordPress7.1.3, PHP8.3.35, MariaDB11.4, Chromium156.0.8078.4. Header/Advanced/maps checks require active Child Starter; the preserved61/38 shell regressions also intentionally activate Parent standalone, then restore Child. No new Docker stack/site/reset is created. Test-only generic pages/menu/logo are local data, not Parent defaults or shipped content.

Responsive widths1440/1024/768/430/390; actual mobile390×844 and430×932. Checks include root/subcomponent graph, style/behavior/script dependency, idempotent WordPress handles, native nested/current menu, valid/invalid logo, unique IDs, real HTTP/DOM/assets, keyboard/mouse/resize, no-JS desktop/mobile, Advanced asset isolation, no console errors, Template A→B→A and Advanced editor/media/gallery/repeater regressions. Header render paths have no persistent write or network calls; real native option/post-meta action tracking records zero writes during identity/menu rendering. Fixture setup itself intentionally writes only local test data.

One Template Map browser run stopped at the existing panel-after-save assertion. Diagnostic-only failure context was added (URL/title/template/error notices; no credentials/nonces), preserving the assertion and its count. Two subsequent complete Template Map runs PASS42 browser +56 native assertions each, with the existing assertion unchanged. The first failure was not reproduced and its cause remains unconfirmed; it is recorded rather than presented as a fixed runtime bug. A preliminary Header CLS check mixed automated open/close/resize actions with initial load; it now records the same strict threshold on separate initial desktop/mobile loads and retains the interaction aggregate separately, rather than calling automation-induced layout changes initial-load CLS.

## Performance and safety

Header CSS **3094bytes**, JS **5583bytes** (unminified), **8677bytes total**, **2 additional same-origin requests/page**. No vendor, remote font or icon bundle. Global frontend load is justified by mandatory Header component usage, not unconditional direct enqueue. Actual authenticated admin has no Header CSS/JS; frontend has no FLX admin controls/assets.

Minimal tokens: container width/gutter and Header border/surface; no full Design System. Logo intrinsic dimensions are retained, constrained without viewport overflow. Initial-load local CLS is a smoke test, not a Core Web Vitals field guarantee or a throttled-network benchmark. No build/minification pipeline is added.

## Acceptance checklist

Final gates PASS:

- [x] Architecture: root Header, subcomponents, behavior, asset graph, no direct consumer asset dependency.
- [x] WordPress: native site identity, custom logo, primary/nested menu, Child inheritance.
- [x] Desktop: navigation, submenu, keyboard.
- [x] Mobile: toggle, ARIA, Escape, resize, no horizontal overflow.
- [x] Accessibility: Skip Link unchanged, header/nav landmarks, visible focus, keyboard.
- [x] Performance: no vendor, sizes/requests recorded, no unnecessary admin/frontend asset crossover.
- [x] Regression: Phase07 baseline, Template Map, Advanced and Accessibility PASS.
- [x] Reuse review, reference integrity, PHP matrix/lint, browser/no-JS and production ZIP gates PASS.
- [x] Git: only reviewed Phase08 files committed after PASS; user reference inputs preserved unstaged.

## Build, integrity and Git

- `dist/flexora-0.4.0.zip`: **67 production files**, SHA256 `84035CD60915B8D95866E9751C11DB3C4ABF4612E96CAD2C3112108D8437B9D0`.
- `dist/flexora-child-starter-0.4.0.zip`: **6 production files**, SHA256 `7C8248598F5A814F9BB680E0F3E6C00DAD22C352B808F7E141E2DD289BD3992D`.
- ZIP entry/required-root checks PASS; no docs/tests/reference/history/local/credentials/DB/uploads/logs.
- **435/435 AZ reference SHA-256 hashes identical**, zero modified/missing/added.
- Commit after PASS: `feat: add FLX header foundation`; resulting hash and file count are provided in the final response. Untracked user inputs remain outside staging. No historical architecture rewrite: current user decisions govern naming, reference-only reuse and Header scope.

## Open gates / deferred work

No blocking WordPress/browser/build gate remains. The single non-reproduced Template Map browser save failure is noted above; diagnostic context remains for future recurrence. Manual screen-reader testing, Safari/Firefox, slow-network performance and formal WCAG audit are not run. Drawer/modal variants, Mega Menu, sticky/search/social/cart/CTA, visual builder and new public override/provider-replacement APIs are deferred. Future work must follow the reuse-before-creation rule; no next phase starts automatically.


**PHASE 08 FLX HEADER FOUNDATION COMPLETED — COMMITTED**
