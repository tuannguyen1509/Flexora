# FLX Template Map quickstart — preview

Search Parent components, fields and helpers before creating another implementation. A normal Child needs only a WordPress Page Template, one map declaration, and the read APIs below. No container/storage key/internal class is needed.

## 1. Create a Child Page Template

`page-templates/about.php`:

```php
<?php
/* Template Name: About */
$hero = flx_data( 'hero', array() );
get_header();
?>
<main id="flx-main-content">
    <h1><?php echo esc_html( (string) $hero['title'] ); ?></h1>
    <div><?php echo wp_kses_post( (string) $hero['description'] ); ?></div>
    <?php if ( $hero['image'] ) { echo wp_get_attachment_image( $hero['image'], 'large' ); } ?>
</main>
<?php get_footer(); ?>
```

## 2. Register a map from Child functions.php

```php
add_action( 'flx/template-maps/register', function () {
    flx_register_template_map( array(
        'id' => 'about', 'label' => 'About',
        'template' => 'page-templates/about.php', 'version' => 1,
        'description' => 'Editable page content.',
        'groups' => array(
            array( 'id' => 'hero', 'label' => 'Hero', 'fields' => array(
                'title' => array( 'type' => 'text', 'label' => 'Title', 'default' => '' ),
                'description' => array( 'type' => 'editor', 'label' => 'Introduction' ),
                'image' => array( 'type' => 'media', 'label' => 'Image' ),
            ) ),
        ),
        'components' => array(), // IDs of existing registered components only.
    ) );
} );
```

This hook runs at `after_setup_theme:36`, after shared field-type registration and before maps seal. It avoids requiring Child to register a map before its custom field types exist. It is separate from component/asset `flx/register`; do not register late from `init` or templates.

Use the **same associative field schema as Phase06**. Group scopes a short field ID into `hero.title`; do not repeat the map ID. A map ID is a stable local slug (`about`, `example-alt`), not a label/filename/post ID. IDs use lowercase letters/numbers/hyphens/underscores; field IDs are local segments, with no dots. Group IDs are likewise local slugs. Paths are relative `.php` paths; no absolute OS path or traversal. Duplicate map ID/template file, duplicate group and unsupported fields fail explicitly, never overwrite an earlier registration.

## 3. Choose the template and edit the Page

Select the registered WordPress Page Template, save, and reload. Mapped Pages open in WordPress' native **Classic editor**, with **Flexora Template Content** groups rendered by the shared Field Engine. Ordinary unmapped Pages keep their existing editor and have no empty map panel. For a new Page you can open `wp-admin/post-new.php?post_type=page&flx_template_editor=1` to opt into native Classic before choosing a template. No editor plugin is required.

Change A → B: save/reload to see B's fields. Unsaved fields from A are not applied to B. Previously saved A data remains; selecting A again restores it. Default Template also keeps every map payload. No live-switch JS, visual builder, or Gutenberg replacement is implemented. Full block-editor mapping UX is deferred; the native Classic path is the supported Phase07 editing surface.

Choose fields for the editor: short string → text, long plain text → textarea, formatted long content → editor, image → media, repeated cards → repeater, boolean → toggle, link → URL. Never hard-code editable content in the template. Template changes affecting dynamic content require map review in the same task.

## 4. Read through FLX

```php
$title = flx_data( 'hero.title', '' );
$hero  = flx_data( 'hero' );
$other = flx_data( 'hero.title', '', array( 'post_id' => $another_page_id ) );
$map   = flx_template(); // immutable descriptor properties, or null.
$id    = $map ? $map->id : null;
```

Context: explicit Page ID, otherwise the queried singular Page, otherwise the current loop's Page. Only Page instances are supported. The API does not expose storage. Whole-group reads return registered child keys; repeated field values return their ordered row arrays.

Fallback is used only if a field is missing **and has no explicit schema default**. An explicit schema default wins over fallback, without being persisted. Stored `null`, `false`, `0`, `""`, `[]` are returned unchanged. Reads never seed defaults, migrate, or normalize stored values. Unknown paths return fallback; an unmapped Page returns fallback. `flx_template()` returns null when unmapped. Public API remains preview 0.1.0; these additions do not claim complete API v1.

Escape on output even though saves are sanitized: text → `esc_html`, rich → `wp_kses_post`, URL → `esc_url`, media ID → `wp_get_attachment_image`. Guard empty image/URL/label/list; do not create broken image tags or empty button links. Do not call `get_post_meta()` in normal Child templates.

For existing registered shared components, declare `components` in the map. The resolver preflights IDs at `wp_enqueue_scripts:15`, before asset flush/head output. It never renders to discover dependencies. Child consumers need no internal asset IDs. Child page CSS may live in `assets/css/pages/`; it must be conditionally requested through the existing registry, not emitted as a giant inline style block or loaded site-wide.

## Data/version contract

Per-Page `_flx_template_data` envelope (implementation detail, not Child API):

```text
format_version: 1
revision: integer
maps:
  about:
    map: about
    version: 1
    data:
      hero.title: "..."
      hero.description: "..."
      hero.image: attachment_id
```

Unknown root/map/field/row branches survive partial saves. Each map has independent values. Missing submission does not clear; explicit empty list does. Storage uses WordPress post-meta APIs with expected revision/previous-value checks; no persistent FLX cache. One request-level payload cache is invalidated by native meta add/update/delete hooks.

Renaming `hero.title` to `hero.heading` does **not** move data: the old key stays, the new field is missing/default. Changing schema version emits `template_schema_version_mismatch`, never an automatic write/migration. A subsequent explicit valid editor save records the current version, preserving unknown keys. Unknown registered-map IDs emit `template_unknown_map`, keep raw data, and do not fatal. Future/malformed envelopes are refused on save.

Saves verify Page type, object capability, nonce, current server-resolved template/map, posted version/completion/revision, then use the same typed Field Engine validation/sanitization/merge. An invalid map ID cannot select another schema. Invalid values preserve stored fields atomically and show errors after redirect. Error/display data is kept briefly in a user-and-Page-scoped transient; no nonce/secret is placed in diagnostics. Native Page title/content updates are separate from the map payload transaction.

Autosave, revision targets and missing form fields do not mutate map data. Native Page revisions still work, but **Template Map meta revision snapshots/restores are not enabled**. Restoring a Page revision leaves the latest map content intact; it does not restore historical map content. A future meta-revision phase needs explicit support and restore tests. No backup/migration/Content Pack implementation is implied.

Child Starter ships two generic, empty-content examples and maps. Its debug-only synthetic component/CSS demonstrates preflight without adding a production component. No live content/credentials/site branding ships in either ZIP.

Run `tools/Test-Fields.ps1`, then `tools/Test-TemplateMaps.ps1`, with optional portable PHP path, on the existing localhost:8090 Child development site. Browser tests create a local Page fixture and save screenshots/results under ignored `local/results/phase07-*`. They do not clone or reset a site.
