# FLX Child API — canonical Parent preview

Phase07 adds `flx_register_template_map()`, `flx_data()` and `flx_template()` for mapped Page content. See the four-step [Template Map quickstart](TEMPLATE-MAP-QUICKSTART.md). Runtime API stays preview; not all target v1 APIs are implemented.

`flexora/` is the only runtime source. `flexora-child-starter` uses `Template: flexora`, inheriting the shell, Header and Skip Link automatically. Do not require history/reference files or copy Parent engine/assets into Child.

Parent 0.2.0 adds **Flexora → Advanced** and a storage-neutral Field Engine. Existing components still use the same preflight API. For site-specific settings, declare `flx_register_settings_section()` during `flx/settings/register`; see [Field Engine contract](FIELD-ENGINE.md). Do not instantiate internal admin classes. Runtime API remains preview 0.1.0, with target v1 incomplete.

| API | Purpose |
|---|---|
| `flx()` | Read-only versions/supports/diagnostics |
| `flx_supports(feature, range)` | Available capability, no boot; API ^1.0 remains false |
| `flx_module(id)` | Read module state, no enable/install/write |
| `flx_use_component(id)` | Component preflight, no rendering |
| `flx_component(id, props)` | RenderResult without echo |
| `flx_render(id, props)` | Render and echo once |
| `flx_asset(id)` | Standalone/integration asset, not normal component workflow |

Child functions.php runs before Parent. Declare a callback to `flx/register` when registering a genuinely new extension; ordinary use of Parent features needs no SpecV1 or internal classes. Example preflight window for an already registered component:

```php
add_action( 'wp_enqueue_scripts', function () {
    // Gate to the route/template that actually needs your registered component.
    flx_use_component( 'flx.your-component' );
}, 20 );
```

Then render once in the template with `flx_render('flx.your-component', $props)`. ID must actually exist; this example does not install a new component. Engine resolves component → behavior/provider → assets. Child does not know CSS/script handles.

WordPress dispatches enqueue **inside wp_head before style printing**. Priority20 preflight works before registry flush100; after the flush/head emission, new dependencies fail explicitly. Do not render twice or load all CSS to hide late discovery. The native Skip Link module self-preflights at wp and self-renders at wp_body_open1, so Child does not manually add a second skip link or enqueue its assets.

Runtime API0.1.0 preview is separate from target contract1.0.0. Phase07 implements the documented Page data/template subset; full target option/map/icon APIs are still not claimed. Templates must retain wp_head/wp_body_open/wp_footer and semantic main. Site-specific templates/styles stay in Child.

## Header (Parent 0.4.0)

Ordinary Child usage requires no `header.php`, registration, provider knowledge or asset calls. Parent preflights and renders `flx.header` with `flx.header.logo`, `flx.header.navigation` and `flx.header.mobile-toggle`. Set **Site Identity → Logo** through native WordPress and assign a menu to **Primary navigation**. Without a logo, the site title is shown; without an assigned menu, navigation/toggle are omitted. Site description uses native WordPress data. No Header settings are added to Advanced.

Prefer the public hooks `flx/header/before`, `flx/header/after`, `flx/header/brand/after`, `flx/header/navigation/after` before copying the shell. For example, a Child can add escaped site-only markup:

```php
add_action( 'flx/header/brand/after', function () {
    echo '<span class="site-header-note">' . esc_html__( 'Site note', 'your-child' ) . '</span>';
} );
```

Hooks run while the component builds its RenderResult; trusted extension output is captured. Escape your own output. Existing `flx/component/props` remains available for documented component props; Header subcomponents receive `navigation_id` internally, so ordinary consumers should use the root Header.

WordPress Child `header.php` override remains possible for a substantially different document shell. Retain `wp_head()`, `wp_body_open()` exactly once and a semantic main; retain the existing `wp_footer()` lifecycle. Parent's default Header graph is preflighted automatically. Any additional Child component must be preflighted before head/asset flush. Duplicate IDs are rejected: Phase08 does not introduce a public registry replacement/provider-swap or Header Builder API. See [Header result](implementation-history/PHASE-08-FLX-HEADER.md) and [reuse decisions](implementation-history/PHASE-08-REUSE-AUDIT.md).
