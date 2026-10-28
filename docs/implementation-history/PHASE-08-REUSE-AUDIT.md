# Phase08 — Header reuse review

Review performed before the follow-up Header edits, 2026-10-28. The uncommitted FLX Header is compared with the six complete source files requested by the user. All six files under `tests/themes/` have the same SHA-256 as their counterparts under `references/az/`. References remain read-only; no runtime include or source-file copy is introduced.

## REUSE AUDIT

| Area | Source (relative to `references/az/`) | Decision | FLX implementation / follow-up | Reason |
|---|---|---|---|---|
| Logo and title fallback | `az-theme/inc/helpers/header-components.php`: `az_render_header_logo()` | ADAPT | `src/Header/Logo.php`: retain home link, site name fallback, eager native attachment image and escaping; return HTML instead of echo | Replace `az_get_logo_id()` with native `custom_logo`; validate image/fallback if invalid, supply accessible image text. No replacement image engine needed. |
| Native menu rendering | Same file: `az_render_header_menu()` | ADAPT | `src/Navigation/Primary.php`: retain native `wp_nav_menu`, location `primary`, no container, `fallback_cb=false`; add `echo=false` and instance-scoped IDs | Keep WordPress hierarchy/current states; registry renderer must return markup. No custom Walker or duplicated menu engine. |
| Empty menu | `az-theme/inc/core/mobile-menu.php`: `az_mobile_menu_markup()`, `az_render_mobile_menu()` | ADAPT | `Primary` returns empty; `Layout` omits navigation and toggle | Preserve early-empty return; no dead button or automatic page/menu creation. AZ option-selected menu/style slots are unnecessary here. |
| Semantic desktop/mobile layout | AZ `header-components.php`, `mobile-menu.php` versus FLX `Layout.php` | REWRITE (retain current FLX) | One semantic header/nav and native button, progressive enhancement | AZ mobile layout is an independent branded modal/drawer. Phase08 needs one ordinary disclosure navigation, not duplicate logo/menu/modal DOM. Existing FLX component boundaries remain. |
| Submenu discovery and independent branches | `az-theme/js/mobile-menu.js`: direct-child UL/link discovery, per-branch button | ADAPT | `assets/header/header.js`: retain direct-child discovery, hidden state, independent per-branch ARIA controls | Same proven small algorithm; use FLX IDs and delegated ownership without branded modes. Keep current collision-safe IDs and idempotence. |
| Translated open/close labels | Same JS + `mobile-menu.php` panel label data | ADAPT | Add localized `data-flx-submenu-open-label` / `data-flx-submenu-close-label` to nav; update label with expanded state | AZ covers this better than FLX's static English submenu label. Port this behavior. |
| Escape and focus return | Same JS: `close()`, keydown handler | ADAPT | Keep FLX `menuState(false,true)` plus innermost submenu Escape | Preserve Escape/return-to-trigger pattern; FLX adds level-aware submenu closing. No modal trap in a nonmodal nav. |
| Destination click | Same JS: branded `nav a[href]` listener | ADAPT | Close mobile navigation on destination click, restore trigger focus, preserve native link activation | Handles same-page links without leaving an open mobile panel. Desktop clicks unaffected. |
| Breakpoint cleanup | Same JS: media-query `change` listener | ADAPT | Keep FLX MQL cleanup at 900/901px and hidden-focus repair | Preserve close-on-breakpoint; FLX handles desktop and mobile focus, without AZ scroll-lock classes. |
| Repeated initialization / invalid control target | FLX current versus AZ JS | REWRITE (retain/extend FLX) | Keep per-header initialized marker; reject mismatched/missing ARIA target before enhancement | AZ IIFE has no idempotence guard and assumes panel exists. FLX's safe no-op and no-JS visibility are preferable. |
| Descendant state on closing ancestor | Both versions | REWRITE | Reset descendant expanded states when ancestor closes | Both previously could retain `aria-expanded=true` inside a hidden ancestor. Explicit reset improves deterministic state; add regression. |
| Viewport clamp | `az-theme-child/js/header-mega-menu.js`: `position()` and resize | ADAPT | Ordinary first-level submenu clamp; remeasure open menus on resize, using document client width | Reuse bounded-offset arithmetic without Mega Menu root/panel/provider or outside-click system. Current FLX clamps only on opening; resize is an edge case to port. |
| Focus/pointer closing | Same Child JS: focusout/pointerleave | ADAPT | Retain current FLX focusout/leave handling | FLX already implements the equivalent small pattern; no second implementation needed. |
| Mobile keyboard regression cases | `az-theme/tests/mobile-menu.cjs` | ADAPT (PORT) | `tests/browser/header-regression.cjs`, production FLX script in isolated Chromium DOM | Port nested initial state, controls/IDs, open without closing root, re-collapse, nested selection, Escape/focus return and breakpoint reset. Test actual DOM instead of expanding the old mock to emulate FLX APIs. |
| Drawer-specific regression assertions | Same test file | DEFER | No port of modal focus wrap, shade/close button, scroll lock, left/right/brand style matrix | Those assertions describe out-of-scope modal variants. Preserve source for future drawer review; do not weaken or pretend to pass these cases. Nonmodal closed-content focus safety tested separately. |
| Storefront helpers | `az-theme-child/inc/header-storefront.php` | DEFER | No Parent promotion in Phase08 | Logo/menu already provided by generic Parent helpers. Remaining functions depend on storefront options, hotline, cart/account, search, sticky layout or brand-specific icons. No additional generic helper needed now. |
| Mega Menu feature / arrow navigation | `az-theme-child/js/header-mega-menu.js` | DEFER | Candidate only | Ordinary menu uses Tab/Shift+Tab and native disclosure buttons. Mega Menu UI, global menu manager, ArrowUp/Down and outside-click behavior are not required for this foundation. |
| Direct enqueue / option-driven registration | AZ `mobile-menu.php` | REWRITE (retain FLX registry) | `Integration → flx.header → subcomponents → behavior → assets → WordPress` | Reuse behavior, not legacy side-effect coupling. Consumers request component ID before head. |
| Header CSS | Current FLX versus branded legacy layout | REWRITE (retain current FLX) | Minimal responsive `.flx-container`, header/nav, focus and no-JS styles | Different nonmodal structure and scope; importing drawer/storefront CSS would carry unused layout and naming dependencies. |

`REUSE DIRECTLY` means unchanged reusable code, not unchanged whole files. No whole-file direct reuse is justified for these six files: their functions/selectors are AZ-owned, the modal tests target a different structure, or their features are out of scope. Small native WordPress calls, escaping policies and state-transition patterns are retained through ADAPT; this is not described as a wholesale new navigation algorithm.

## Regression mapping

| Legacy case | FLX coverage |
|---|---|
| Closed trigger / collapsed nested branches | Ported browser fixture, real mobile browser |
| Submenu opens without closing root, separate nested controls | Ported browser fixture |
| Expanded/collapsed translated labels, unique controls | Ported browser fixture + real PHP output |
| Re-collapse branches, nested destination click | Ported browser fixture |
| Escape closes + trigger focus | Ported browser fixture + real browser keyboard |
| Breakpoint closes/reset | Ported browser fixture + real browser resize |
| Modal Tab wrapping / scroll lock / shade | Deferred; ordinary navigation is nonmodal. Real keyboard tests check hidden content is unreachable. |
| Additional gaps not protected by legacy suite | No menu, one-level, missing target, repeated init, independent sibling branches, descendant reset, no-JS visibility and viewport resize coverage |

Follow-up changes and the final test results, sizes and commit outcome are recorded in `PHASE-08-FLX-HEADER.md` after the gates run. Reference provenance belongs in documentation; production runtime keeps FLX naming only.

Follow-up completed: dynamic localized labels and destination-click close were ported; desktop-only focusout preserves independent mobile branches; ancestor close clears descendant state; viewport clamp remeasures on resize; invalid ARIA target stays unenhanced. The port/adaptation suite passes39 Chromium assertions. Escape returns focus; native fragment activation is preserved and may subsequently focus the destination rather than the toggle, with no focus stranded in hidden navigation.
