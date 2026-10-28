# FLX Field Engine — preview extension contract

Canonical implementation: `flexora/src/Fields`, `src/Admin`, `src/Data/SettingsStore.php`. Parent/package 0.2.0; runtime Public API stays **0.1.0**, target v1 remains incomplete. No Template Map engine, legacy adapter, or website migration is included.

## Choose fields for the person editing

| Content | Type |
| --- | --- |
| Short title, label, hotline | `text` |
| Long plain multiline | `textarea` |
| Long formatted content, lists, links | `editor` |
| Numeric value | `number` |
| Link / email | `url` / `email` |
| Few choices / many choices | `radio` / `select` |
| Boolean | `toggle` |
| Hex color | `color` |
| One image / multiple images | `media` / `gallery` |
| Repeated structure | `repeater` |

Do not choose textarea merely because rich editor integration is harder. Media stores attachment IDs, not environment-specific URLs. Gallery stores an ordered ID array, not CSV.

## Register a new section from Child

Ordinary use of Advanced needs no registration. A Child that introduces genuinely site-specific settings declares a hook in its `functions.php`, which loads before Parent:

```php
add_action( 'flx/settings/register', function () {
    flx_register_settings_section( 'site.contact', array(
        'title' => 'Contact',
        'description' => 'Website contact information.',
        'groups' => array(
            array(
                'title' => 'Details',
                'fields' => array(
                    'contact.title' => array(
                        'type' => 'text', 'label' => 'Title',
                        'default' => '', 'required' => true, 'maxlength' => 120,
                    ),
                    'contact.body' => array( 'type' => 'editor', 'label' => 'Introduction' ),
                    'contact.image' => array( 'type' => 'media', 'label' => 'Image' ),
                    'contact.items' => array(
                        'type' => 'repeater', 'label' => 'Services', 'item_label' => 'dịch vụ',
                        'fields' => array(
                            'title' => array( 'type' => 'text', 'label' => 'Title' ),
                            'description' => array( 'type' => 'editor', 'label' => 'Description' ),
                            'enabled' => array( 'type' => 'toggle', 'label' => 'Enabled', 'default' => true ),
                        ),
                    ),
                ),
            ),
        ),
    ) );
} );
```

Every valid registered section automatically appears. IDs match `[a-z][a-z0-9_.-]*`; duplicate sections/field IDs and late registration throw explicit developer exceptions. Field IDs are opaque keys, **not dot-path instructions**. Changing a label/group/layout must not rename an ID. Registry order is deterministic insertion order. Registration and render do not write defaults.

Common properties: `type`, `label`, `description`, `default`, `required`, optional `nullable`. Text supports `placeholder`/`maxlength`; textarea adds `rows`. Number supports `min`/`max`/positive numeric `step`. Select/radio require associative `options` (value => label). Color supports hex #RGB/#RRGGBB and empty clear. Repeater requires typed `fields` and optional `item_label`.

## Types and extension boundary

`TypeRegistry` is callback based. During `flx/fields/register`, its registration surface accepts an ID and five callbacks:

| Callback | Signature / result |
| --- | --- |
| `default` | `(field) -> native default` |
| `normalize` | `(raw, field) -> typed candidate` |
| `validate` | `(candidate, field) -> null or human-readable error string` |
| `sanitize` | `(validated candidate, field) -> safe canonical value` |
| `render` | `(renderer, field, value, html_name, dom_id) -> output escaped admin markup` |

No arbitrary fallback type. Unknown types are rejected before rendering. Built-ins use WordPress sanitizers; editor always uses `wp_kses_post`, even for administrators with unfiltered HTML capability. URL allows http/https, email must validate, choices must match their allowlist, numbers reject range/step violations without clamping, media validates readable image attachments. Gallery and repeater currently cap at 100 items. Schema is trusted code; submitted values are untrusted.

The field engine has no option/post/term/user storage call. `Renderer` takes schema, values and error paths. Future meta adapters can reuse these same classes without changes to field behavior; those adapters are **not implemented yet**. Child extensions should use the registration hooks, not construct an application/container or call internal admin methods.

## Presence, validation, and storage

| Input | Meaning |
| --- | --- |
| Key absent | Preserve existing value, no default write |
| `null` | Allowed only for nullable, non-required field; remains null |
| `false` | Boolean false for toggle |
| `0` | Numeric zero; valid value, including required numbers |
| `""` | Explicit empty string for optional string/number/color fields |
| `[]` | Explicit empty gallery/repeater |
| Removed schema field | Existing stored value preserved, not purged |

HTTP hidden controls encode an unchecked switch as `0`, cleared media as `0`, and a cleared list as `""`; typed normalization converts only the list sentinel to `[]`. Missing controls are never interpreted as clear. Unknown submitted keys reject the save; unknown **stored** keys survive updates.

`Engine::patch` returns `values`, `errors` (field/stable-row paths => messages), and `display` (escaped on render; includes invalid scalar input for correction). Never persist `display`. A failed form writes nothing, including otherwise-valid fields. Missing request values are a no-op. A completion marker at the end of the form rejects truncated requests, including common `max_input_vars` truncation.

Advanced uses the native `flx_settings` option: `{_revision, section_id: {field_id: value}}`. One section save preserves other sections and unknown branches. The adapter uses a binary serialized compare-and-swap to reject stale revisions, invalidates the WP option caches, and initially creates the option with autoload disabled. The direct CAS update does not run WordPress `pre_update_option` filters; callers must use the explicit FLX validation/storage boundary. This is a narrow settings adapter, not a completed general-purpose data API.

## Repeater and editor lifecycle

Rows are ordered arrays of objects with `_id` strings. Add and duplicate generate new IDs; reorder/save/reload preserve IDs. IDs are validated for uniqueness and syntax. Unknown data inside an existing row is merged by stable ID; numeric position is not identity. Explicit row removal removes that row. Nested repeater is explicitly rejected; all other required typed subfields and gallery are supported.

Initial editors use `wp_editor`; new rows use WordPress `wp.editor.initialize`. Before duplicate/move/remove, synchronize editor values and call `wp.editor.remove`; remove stale wrappers and inline hidden state before initializing again. Code/Visual mode is preserved across moves. Duplicate changes DOM/editor IDs and the row ID; old instances are destroyed on removal. No textarea fallback is used.

Media uses `wp_enqueue_media`/`wp.media`; color uses WordPress' picker. Own admin CSS/JS uses FLX logical IDs and the WP-backed asset registry. Only the selected section requests needed editor/media/color/repeater dependencies. No admin asset is requested on frontend or dashboard.

## Advanced UI and verification

Open **Flexora → Advanced** on the primary development site. General/Design are neutral settings containers; their values do not yet automatically alter frontend presentation. Field Playground is registered only under `WP_DEBUG` or `FLX_DEVELOPMENT`. Turning development off hides the section without deleting its data. No reset button is implemented.

Use `tools/Test-Fields.ps1` (optional `-Php <portable PHP>`), alongside the existing foundation/integration/browser runners. It requires the existing localhost:8090 Child development site and cached browser tooling image; it does not create another WordPress stack. It creates two tiny local image fixtures, a subscriber security fixture, and playground values; credentials/results remain under ignored `local/results`. The option adapter integration test temporarily exercises only `flx_settings` and restores its prior value in `finally`.

Template Map, template detection, meta storage adapters, nested repeaters, settings import/export/reset, frontend design-token application, Content Pack, and real-site migration remain deferred.
