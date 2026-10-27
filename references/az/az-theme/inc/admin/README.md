# AZ Admin

## Overview

Provides the AZ Theme top-level admin menu, shortcut Dashboard and registry-driven Advanced settings screen.

## Scope

Dashboard is navigation-only; Advanced owns registered settings. Child-theme installation listens for theme activation on both admin and public requests; its notices and manual retry are admin-only. It does not change frontend markup.

## Files

`class-admin.php`, `class-dashboard.php`, `class-advanced.php`, `class-admin-assets.php`, `class-child-theme-installer.php`, `templates/child-theme/` and section field configs.

## Dependencies

WordPress admin APIs, the AZ options registry and optional WooCommerce detection.

## Options

New values are stored in `az_theme_options` as nested arrays.

## Hooks

`admin_menu`, `admin_enqueue_scripts`, `admin_post_az_save_theme_options` and `admin_post_az_reset_theme_section`.

## Security

Save/reset require `manage_options` and action-specific nonces. Custom code fields are available only to administrators and are never evaluated by PHP.

Manual child installation requires `switch_themes`, `install_themes` and an action-specific nonce. Automatic creation runs only on `after_switch_theme` while `az-theme` itself is active; the next request can be public. Creation respects WordPress file-modification policy, requires a super admin on multisite, and never overwrites a pre-existing child directory. Filesystem failure rolls back only files in the newly created directory and shows a retry notice.

## Performance

Assets load only on AZ Theme admin pages.

## Migration

Legacy values are display fallbacks only; saves do not write back to legacy keys.

## Rollback

Remove the bootstrap admin loads; existing legacy admin settings remain separately registered.

## Testing

Static checks passed. Live WordPress admin testing requires PHP and a running WordPress installation.

Run `php wp-content/themes/az-theme/tests/child-theme-installer.php` from the WordPress root for isolated creation/migration regression checks using WordPress filesystem classes, without a database bootstrap. See `docs/CHILD-THEME.md` for setup and staging checks.

## Version

STEP Advanced Theme Options.
