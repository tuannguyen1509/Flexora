# FLX Child API — canonical Parent preview

`flexora/` is the only runtime source. `flexora-child-starter` uses `Template: flexora`, inheriting the minimal shell and Skip Link automatically. Do not require history/reference files or copy Parent engine/assets into Child.

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

Runtime API0.1.0 preview is separate from target contract1.0.0. Data/Map/icon/template helpers remain unimplemented rather than faked. Templates must retain wp_head/wp_body_open/wp_footer and semantic main. Site-specific templates/styles stay in Child; no new production feature or data migration is included in Phase05.
