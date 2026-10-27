# Shared banner verification

Configuration: edit a Page → Banner trang; edit/create a blog category or product category → Banner danh mục. Save using the existing Update/Add button. Theme-wide colours and container remain in Advanced → Colors / Layout; breadcrumb visibility remains in General.

Defaults: no banner, plain template heading, breadcrumb below header. Existing sample page/term metadata is read when the new settings are absent. On a static front page, enabling the shared banner replaces the homepage hero section. Custom HTML headings inside page content are not removed.

Automated: run `node tests/shared-banner.cjs` from the theme with `php-parser@3` and `jsdom@26` available through NODE_PATH. PHP checks use a parser, not execution of WordPress. Tests cover JS carousel controls/autoplay/pause/focus/reduced motion, admin image order/removal and theme tokens.

WordPress integration checklist (requires running WordPress):

- Save/reopen Page and both taxonomies; verify separate metadata and unchanged other settings.
- Quick Edit/autosave must preserve banner settings; failed nonce/capability must not save.
- Create two terms consecutively via AJAX; second term must not inherit first banner.
- Test all six layouts, no image, one image and multiple images; check media picker.
- Breadcrumb: nested pages/categories, paged archives, General switch off, no duplicate title/nav.
- Product category description expand/collapse and sorting remain usable.
- Desktop/mobile: header overlay on/off, sticky header, logged-in toolbar, menu dropdowns.
- Change Colors and Layout; verify matching container edges and colours.
- Check existing custom page templates and static homepage for duplicate headings.

No hardcoded page/category IDs or translation synchronization that overwrites other objects.
