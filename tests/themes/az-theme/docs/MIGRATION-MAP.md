# Flatsome Settings Migration Map

## Status

STEP 10 is implemented as a mapping and preview foundation. It does not scan or guess Flatsome option names, does not import automatically and does not delete legacy data.

The current repository does not contain verified evidence for a concrete Flatsome settings option. Therefore no `flatsome_options` key is hard-coded. STEP 09 scanner output must provide each source key and evidence before it can appear in this map.

## Mapping contract

Each verified entry has:

| Field | Meaning |
| --- | --- |
| `source_type` | `option` or `theme_mod` |
| `source_key` | Exact key returned by the scanner |
| `target_path` | Nested path under `az_theme_options` |
| `category` | `general`, `branding`, `colors`, `typography`, `layout`, `header`, `footer` or `woocommerce` |
| `evidence` | Scanner/source evidence for the key |
| `value` | Verified legacy value; absent values are skipped |

Conceptual target paths are allowed only after the source is verified:

| Legacy value | AZ target | Status |
| --- | --- | --- |
| Verified legacy logo | `branding.logo` | Awaiting STEP 09 evidence |
| Verified legacy primary color | `colors.primary` | Awaiting STEP 09 evidence |
| Verified legacy container | `layout.container` | Awaiting STEP 09 evidence |
| Verified legacy typography | `typography.*` | Awaiting STEP 09 evidence |
| Verified legacy header setting | `header.*` | Awaiting STEP 09 evidence |
| Verified legacy footer setting | `footer.*` | Awaiting STEP 09 evidence |
| Verified legacy WooCommerce setting | `woocommerce.*` | Awaiting STEP 09 evidence |

WordPress `custom_logo` may be considered only when the scanner confirms that it is the intended source for the migration; it is not automatically treated as a Flatsome setting.

## Preview

`AZ_Flatsome_Settings_Map::preview()` returns rows for the admin UI:

| Legacy Value | AZ Value | Action |
| --- | --- | --- |
| verified value | current nested value | `add`, `preserve-unless-selected` or `skip-no-verified-value` |

Existing AZ values are preserved by default. The importer writes only rows whose IDs the user explicitly selected.

## Import safety

`AZ_Flatsome_Settings_Importer::import()` requires:

- a logged-in user with `manage_options`
- preview rows and explicit selected IDs
- a callable backup callback that succeeds before the first write
- the existing `az_theme_options` array

The importer updates only `az_theme_options`. It does not overwrite content, delete legacy options or execute serialized/untrusted PHP. The backup revision engine belongs to STEP 11, so STEP 10 callers must provide that callback and must not expose import UI without it.

## Files

- `inc/modules/flatsome-compat/migrations/class-settings-map.php`
- `inc/modules/flatsome-compat/migrations/class-settings-importer.php`

The classes are not wired into a migration admin screen in STEP 10. That UI belongs to the later migration workflow.
