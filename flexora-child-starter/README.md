# Flexora Child Starter

One Child per website. Parent `Template: flexora` supplies the engine, minimal shell and Skip Link; do not copy or require Parent implementations. This starter has no demo data, vendor, site-specific templates or database writes.

Declare WordPress callbacks in functions.php; register extensions at `flx/register`. Use `flx_use_component()` before asset flush and `flx_render()` in a template. Ordinary use of existing Parent components does not require SpecV1 or asset/provider IDs. Runtime API is preview 0.1.0, not full target 1.0.0.
