# AZ Options

## Overview

Registry, sanitizer, renderer and helpers for the nested `az_theme_options` store.

## Scope

Supports text, textarea, email, URL, number, select, checkbox/switch, color, media/image and code fields.

## Files

`class-options.php`, `class-registry.php`, `class-sanitizer.php` and `class-renderer.php`.

## Dependencies

WordPress option, escaping, media and color picker APIs.

## Options

The public helpers are `az_get_options()`, `az_get_option()` and `az_array_get()`.

## Security

Values are sanitized according to field type. Admin save authorization and nonces are enforced by `AZ_Advanced`.

## Performance

The option is read once per helper call and the registry is built only in admin bootstrap.

## Migration

Legacy values may be used as display fallbacks; no legacy option is overwritten.

## Rollback

Stop loading the options/admin classes. The existing option keys remain untouched.

## Testing

Static checks passed. Runtime save/load testing requires WordPress.

## Version

STEP Advanced Theme Options.
