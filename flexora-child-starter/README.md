# Flexora Child Starter

One Child per website. Parent `Template: flexora` supplies the engine, minimal shell, Skip Link and Template Map/Field Engine; do not copy Parent implementations. Preview 0.3.0 ships two generic example Page Templates and their maps, with empty editable content. No demo data, vendor, site branding or automatic database writes ship.

Declare WordPress callbacks in functions.php; register extensions at `flx/register`. Use `flx_use_component()` before asset flush and `flx_render()` in a template. Ordinary use of existing Parent components does not require SpecV1 or asset/provider IDs. Runtime API is preview 0.1.0, not full target 1.0.0.

Maps register during `flx/template-maps/register` with `flx_register_template_map()`. Select Flexora Example or Alternate on a Page, save/reload, and edit Flexora Template Content. Read values using `flx_data('hero.title', '')` or a whole group `flx_data('hero')`; never use storage keys in normal template code. Saved content remains separate for each Page and map when switching templates. Mapped Pages use native Classic editing to support rich repeater editors.

The debug-only synthetic component in `inc/template-maps.php` demonstrates component preflight and conditional Child CSS without rendering or a direct consumer asset call. It is disabled when WP_DEBUG is off. Remove the example templates/maps/component together when creating a site-specific Child, preserving any already-used stable data IDs until an explicit data plan exists. See workspace `docs/TEMPLATE-MAP-QUICKSTART.md`.
