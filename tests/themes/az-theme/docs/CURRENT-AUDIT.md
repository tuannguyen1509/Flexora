# AZ Theme Current Source Audit

## Scope

STEP 00 audit only. The scan covers `functions.php`, `inc/`, `template-parts/`, `woocommerce/`, `css/`, `js/` and existing documentation. No frontend, database, option or runtime behavior was changed for this audit.

The repository already contains unrelated working-tree changes from earlier work. This report describes the current source as inspected and does not treat those changes as a clean baseline.

## Source inventory

The existing read-only source scanner reports 63 PHP files, 8 CSS files and 9 JavaScript files in the theme source set, excluding documentation from source classification. Generated inventories are stored under `docs/audit/`.

Main ownership areas:

- `functions.php`: theme bootstrap and legacy include orchestration.
- `inc/functions/`: security, setup, contact/admin, pricing, product, translation, integrations and WooCommerce contracts.
- `inc/modules/homepage/`: the existing homepage module, templates, admin screen and assets.
- Root templates and `template-parts/`: public page, blog, product and shared rendering.
- `woocommerce/`: cart override; product rendering remains in root templates and legacy helpers.
- `style.css`, `css/`, `login.css`: frontend, shared mobile/page/container, admin and login styles.
- `js/`: product, language, contact, pricing and homepage behavior.

## Prefix audit

### AZ contracts

The theme already uses `az_` PHP functions/options, `AZ_` classes/constants, `az-` CSS classes, `--az-*` variables, `az_*` admin actions and `az/v1` as the intended REST namespace. The homepage module is the clearest current AZ-owned implementation.

### Legacy contracts

`az_` remains widespread in functions, option keys, metadata, cookies, JavaScript data attributes and CSS classes. Existing `az_*` helpers are used for contact, pricing, product videos, header cart, product fields and compatibility fallbacks. They must not be renamed mechanically.

Other legacy prefixes observed include `cp_`, `wv_`, `wpc_`, `ct-`, `arc-`, `gcg-`, `pg-` and `pp-`. These are compatibility or feature-specific contracts and require ownership review before migration.

## CSS audit

The current theme contains `body.az-storefront` selectors in `css/container-sync.css` and the single-post template, plus legacy `.az-*` selectors in WooCommerce and product-related code. `!important` is also present in legacy styling, especially single-post and container compatibility rules.

The AZ path now has `#page.az-site`, AZ page scopes and body-independent container selectors. These additions are intentionally additive. The legacy selectors remain until each affected template is verified in the browser.

`body_class()` is retained in `header.php` for WordPress/plugin compatibility, third-party integrations and debugging. It must not become the primary architecture scope for new CSS.

## WordPress options and theme mods

The source contains direct `get_option()` calls for contact, footer, homepage, pricing, catalog, SMTP, advisor, product and integration settings. It also reads dynamic option families and legacy `az_*` keys. `get_theme_mod()` is used by contact/settings and theme setup paths. `register_setting()` is used by the existing admin settings implementation, with legacy field names and sanitizers.

The planned `az_theme_options` nested store exists as an option contract in documentation/homepage code, but a general `az_get_option()` options engine is not present in this STEP 00 baseline. Direct reads must be mapped and validated before a future options migration. No values were read from the database by this audit and no data was written.

## Hooks and integrations

Observed integration categories:

- WordPress actions/filters for setup, enqueueing, admin menus, settings, contact forms, translations, metadata and content rendering.
- WooCommerce hooks for cart fragments, price/text filters, product behavior, checkout/cart translations, variation UI and product media.
- Admin-post and AJAX-style flows for homepage/admin/contact and product functionality; nonces and capability checks require review per endpoint.
- REST/API and license/prompt integration code exists behind configuration/dependency guards; external provider functions are not universally defined by the theme.
- Scheduled/transient and other persistence calls are present in legacy integration code and must not be moved or triggered during a read-only scanner.

The scanner records literal and dynamic callsites in `docs/audit/CALLSITES.md` and full expressions in `docs/audit/source-inventory.json`. Those files are lexical evidence, not proof that a hook or feature is active at runtime.

## Legacy storefront dependency map

The main legacy dependency clusters are:

1. `az_*` contact, pricing, product metadata and video helpers.
2. `az-*` product/shop/cart CSS and markup contracts.
3. `body.az-storefront` layout compatibility selectors.
4. Legacy `az_*` option, theme-mod, metadata and cookie keys.
5. WooCommerce availability checks and custom template behavior.
6. Existing admin/settings and external license/prompt integrations.

No Flatsome detector or shortcode compatibility renderer is implemented in STEP 00. No Flatsome data, shortcode content or WordPress settings were modified.

## Risks and follow-up

- Direct option reads are numerous and heterogeneous; do not assume one schema or default type.
- Legacy body-scoped CSS and `!important` rules can override new AZ selectors during migration.
- WooCommerce templates use compatibility classes and must be tested with WooCommerce both active and inactive.
- PHP runtime lint and browser rendering were unavailable in the current environment because PHP, WP-CLI and a running WordPress server were not available.
- STEP 01 should first preserve the existing include order, then introduce a coordinator without changing frontend output.

## Audit commands

```powershell
node docs/tools/audit-source.cjs
node --check docs/tools/audit-source.cjs
node --check docs/tools/check-homepage.cjs
git diff --check
```

These checks are static. They do not bootstrap WordPress, read the database, activate/deactivate WooCommerce or execute migration actions.

## Status

STEP 00 audit completed. Steps 01-09 remain unimplemented and were not started.
