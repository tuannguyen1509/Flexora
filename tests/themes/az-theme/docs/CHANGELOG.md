# Changelog

## 2026-10-14 — Retire the old Header & Footer feature

- Removed its admin screen, Settings API registration, form, sanitizer and separate output helper. Old administrator bookmarks redirect to AZ Theme → Advanced → Custom Code.
- The parent Custom Code module is the only editor/save/output path. It retains read-only compatibility with existing `az_hfc_*` data; Leads and SMTP configuration are unchanged.
- Isolated checks cover removal of the old route/save group, its redirect and preservation of existing code through the parent module.

## 2026-10-06 — Contact page saves to shared Leads manager

- Contact-page submissions now write to `az9s_leads`, which supplies `page=az9s-leads`, rather than the legacy contact table. Name, phone, email, topic, message and form source remain available; historical rows stay in the legacy table.
- Notifications use AZ9S recipients/SMTP helpers. Failed inserts return an error instead of a stale insert ID; older tables without form columns retain source in the message. Mail failures do not discard saved leads.
- Contact-page AJAX displays server error strings correctly. Added 17 isolated persistence/notification/CAPTCHA regressions and checked inline JavaScript syntax; no live database write or external email was performed.

## 2026-10-06 — Shared homepage Branding SEO

- Moved Homepage Meta description into the parent Branding registry and runtime so new child themes inherit it. Existing `branding.home_meta_description` values remain effective; empty settings use the WordPress tagline.
- Retained the parent H1/site-title fallback and made homepage shared-banner headings H2 to avoid a second H1. Added a metadata context filter for optional child Page templates.
- Removed duplicate child metadata registration/output, documented child inheritance and added standalone-parent/current-child regressions for theme, Rank Math and Yoast ownership. SEO plugin renderers are simulated; live WordPress/database validation remains pending.

## 2026-10-05 — Remove duplicate Settings menu entries

- Removed Header & Footer and Lead liên hệ from the WordPress Settings submenu. Legacy pages remain registered for direct administrator access; saved code, lead records and SMTP settings are preserved.
- Custom Code stays under AZ Theme → Advanced; Quản lý Lead keeps its existing menu and forms.

## 2026-10-05 — Back-to-top visibility repair

- Added independent, content-versioned back-to-top CSS so the component works with cached or disabled parent `style.css`. Its native top link stays usable before JavaScript initializes; SVG strokes are intrinsic.
- Scroll visibility now handles short pages, scrolling-element offsets, resize and browser restoration. Mobile contact docks leave room for the button.
- Added regression coverage for independent assets, Performance disabling the parent CSS, delayed initialization and short-page scrolling. The reported website state is not directly verified.

## 2026-10-05 — Footer back-to-top controls

- Moved the back-to-top switch to Advanced → Footer and added left/right corner placement for desktop and mobile. Previously saved General settings remain effective until a Footer value is saved; an explicit off or Footer reset disables the button.
- The child footer now uses the shared floating button instead of its always-visible duplicate arrow. Scrolling respects reduced-motion preferences.
- Added isolated settings/render/enqueue and scroll interaction checks; live website rendering remains unverified.

## 2026-10-05 — Style 5 layout and asset loading fix

- Moved style 5 into its own dependent stylesheet with explicit full-width/content-height panel rules, compact logo/menu spacing and cream icon controls.
- Header visibility now also follows the actual compact-header markup, preventing duplicate parent/child headers when body classes are modified. Added intrinsic SVG strokes and corrected active-link selector priority.
- Mobile CSS/JS versions now use content hashes so deployments preserving timestamps still request updated assets. Added enqueue order, style isolation and SVG regression coverage; live browser layout remains unverified.

## 2026-10-05 — Coffee mobile dropdown menu

- Added Header → Menu Mobile style 5 (`brand_dropdown`): a white logo header, brown hamburger, full-width dropdown overlay, cream close button and active menu highlight.
- Added independent submenu toggles with translated labels, nested branches and keyboard focus excluding collapsed menus. Parent links retain navigation; destination links close the overlay.
- Reuses configured logos/menus and sticky behavior. Existing styles remain available; isolated PHP/JavaScript checks cover both branded styles and previous navigation behavior.

## 2026-10-05 — Branded mobile header menu

- Added Header → Menu Mobile style 4 (`brand_right`): a compact white logo header, rounded navy hamburger and branded right drawer with separators and dimmed backdrop.
- Reuses the configured mobile logo and navigation menu, supports nested links, keyboard dismissal/focus, reduced motion and sticky Header main. Existing mobile styles/defaults and desktop headers remain unchanged.
- Added isolated settings/render and interaction checks; browser preview could not run because localhost access was denied.

## 2026-10-05 — Upload file size limit

- Added a per-file MB setting under Advanced → Performance, disabled by default (`0`), independent of the Security master switch.
- Media upload limits and server-side upload/sideload checks now honor the setting while preserving lower PHP, multisite and WordPress limits. Oversized files receive a size error, including REST raw uploads without a reported size.
- Added isolated boundary, temporary-file, existing-error and settings validation checks; live database/browser upload validation remains for staging.

## 2026-10-05 — Automatic child theme creation

- Parent activation now automatically creates `az-theme-child` from the bundled starter, including on the first public request; existing child files are never replaced.
- Added success/error notices, a WordPress child activation link, protected manual retry and handling for filesystem policies, conflicting directories and partial writes.
- New starter child imports missing parent theme-mods and Additional CSS once, and enqueues its own CSS after parent module assets. The existing website child is unchanged.
- Documented installation, customization, update boundaries and multisite limits. Added 28 isolated regression checks using core filesystem classes; live WordPress/database/browser validation remains for staging.

## 2026-09-17 — Advanced Homepage Scope

- Removed the duplicate Homepage section from Advanced navigation; Homepage Manager remains the single owner of homepage sections and ordering.
- Preserved existing `az_theme_options.homepage` data and the Homepage Manager implementation.

## 2026-09-17 — Font Library Typography

- Typography family fields now read registered WordPress Font Library families and feed the AZ frontend font variables.

## 2026-09-17 — Site Analytics

- Added whole-site page-view and tracked-click counters to the AZ Dashboard.
- Analytics ignores admin, AJAX, REST, cron and common bots; raw IP addresses are not stored.

## 2026-09-17 — Admin Dashboard Cleanup

- Simplified `page=az-theme` to practical navigation shortcuts.
- Removed unimplemented Quick Setup, Modules, Import / Export, Tools and System Status submenus.
- Restricted media/color picker assets to Advanced settings.

## 2026-09-17 — Social + Performance

- Added Advanced Social fields and `az_get_social_links()` with centralized AZ-to-legacy fallback mapping.
- Added opt-in runtime controls for emoji, embeds, generator output and guest Dashicons.

## 2026-09-17 — Advanced Theme Options

- Added the AZ Theme admin menu with Dashboard, Advanced and non-empty placeholder submenu pages.
- Added registry-driven Advanced settings for General, Branding, Colors, Typography, Layout, Header, Footer, Homepage, Blog, WooCommerce, Social, Performance and Custom Code.
- Added nonce/capability-protected save and section reset handlers, WordPress media picker and color picker integration.
- Added nested `az_theme_options` helpers and type-aware sanitization while preserving legacy option fallbacks.
- Fixed section merge/reset behavior, single-layer POST unslashing, `custom_code` naming, notices, media previews and Advanced-only asset namespacing.

## 2026-09-17 — CSS architecture migration

- Added the `#page.az-site` root wrapper and AZ page scopes for homepage, blog, post, shop, product and cart templates.
- Added AZ component element classes for the homepage product card and moved the active font/container variables to `:root` with `--az-*` names.
- Added a body-independent AZ container path while retaining legacy selectors for compatibility.
- Documented naming, specificity, forbidden patterns and the legacy migration sequence in `docs/CSS-ARCHITECTURE.md` and the coding standards.

## 2026-09-17 — STEP 16: Homepage Framework

- Added homepage module, nine section settings, Appearance submenu and nested option merge.
- Extracted existing sections/interaction JS; preserved legacy option fallbacks, product card helpers and default ordering.
- Added optional featured category/custom Page content sections, disabled by default; guarded product sections when Woo missing.
- Scoped frontend/admin assets; added reusable checkbox sizing to admin CSS.
- Updated module README, architecture, options schema, roadmap, compatibility/database/security/testing docs.
- No migration, no steps 01–15 or 17–30 implementation. PHP/browser runtime tests unavailable in current environment.

## 2026-09-16 — STEP 00: Audit + Documentation Baseline

- Added required 12 documentation files inside theme docs/.
- Added read-only source scanner and generated file/prefix/API/option inventory with source hashes.
- Recorded existing 19-module loader, current contact admin, legacy compatibility, dependencies and planned architecture.
- Recorded static security/performance/runtime risks without modifying behavior.
- No PHP/CSS/JS runtime edits, DB reads/writes, option renames/migration, frontend changes or new module framework.
- Tests: static scan, Node syntax, docs/source integrity checks. PHP/WordPress/Woo OFF runtime tests not run.

## Trước STEP 00 — trạng thái kế thừa

Functions đã được chia vào inc/functions; contact settings được tách khỏi Home UI; form admin và docs plants được tạo ở các lượt trước. Không gán các thay đổi này cho STEP 00 và không khẳng định đã kiểm thử runtime.

STEP 01–15 chưa thực hiện.
