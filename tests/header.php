<?php
require __DIR__ . '/wp-fixture.php';
require dirname( __DIR__ ) . '/flexora/bootstrap.php';
function esc_attr( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function get_bloginfo( $key ) { return 'description' === $key ? '<Tagline>' : '<Site & name>'; }
function get_theme_mod( $key ) { return isset( $GLOBALS['header_logo'] ) ? $GLOBALS['header_logo'] : 0; }
function wp_attachment_is_image( $id ) { return 10 === $id; }
function wp_get_attachment_image( $id, $size, $icon = false, $attr = array() ) { return '<img width="200" height="60" alt="Site" src="logo.png">'; }
function home_url( $path = '' ) { return 'https://example.test' . $path; }
function esc_html__( $text, $domain = '' ) { return esc_html( $text ); }
function esc_attr__( $text, $domain = '' ) { return esc_attr( $text ); }
function has_nav_menu( $location ) { return ! empty( $GLOBALS['header_menu'] ); }
function wp_nav_menu( $args ) { return '<ul class="flx-nav__list"><li><a href="/">Home</a><ul class="sub-menu"><li><a href="/child">Child</a></li></ul></li></ul>'; }
function wp_unique_id( $prefix = '' ) { static $id = 0; return $prefix . ++$id; }
FLX\Header\Integration::attach();
do_action( 'after_setup_theme' );
$usage = flx_use_component( 'flx.header' );
check( $usage->accepted, 'header component registered/preflight accepted' );
check( in_array( 'flx.header.style', $usage->assets, true ), 'style resolved by component' );
check( in_array( 'flx.header.script', $usage->assets, true ), 'behavior script resolved' );
check( in_array( 'flx.header.mobile-navigation', array_column( $usage->usage, 'id' ), true ), 'behavior graph' );
check( 0 === $GLOBALS['fx_writes'] && 0 === $GLOBALS['fx_queries'], 'registration/preflight zero data work' );
do_action( 'wp_enqueue_scripts' ); do_action( 'wp_enqueue_scripts' );
check( 1 === $GLOBALS['fx_enqueue_calls']['style:flx-header'] && 1 === $GLOBALS['fx_enqueue_calls']['script:flx-header'], 'idempotent enqueue both WP types' );
$result = flx_component( 'flx.header' ); check( ! $result->errors, 'header renders safely no menu' );
check( false !== strpos( $result->html, '&lt;Site &amp; name&gt;' ), 'site title escaped' );
check( false !== strpos( $result->html, '&lt;Tagline&gt;' ), 'native site description escaped' );
check( false === strpos( $result->html, '<nav' ) && false === strpos( $result->html, '<button' ), 'no-menu fallback no dead toggle' );
$GLOBALS['header_menu'] = true;
$a = flx_component( 'flx.header' ); $b = flx_component( 'flx.header' );
check( ! $a->errors && ! $b->errors, 'multiple instances render' );
check( 1 === substr_count( $a->html, '<header' ) && 1 === substr_count( $a->html, '<nav' ), 'semantic landmarks' );
preg_match( '/aria-controls="([^"]+)"/', $a->html, $aid ); preg_match( '/aria-controls="([^"]+)"/', $b->html, $bid );
check( $aid[1] !== $bid[1] && false !== strpos( $a->html, 'id="' . $aid[1] . '"' ), 'unique aria target per header' );
check( false !== strpos( $a->html, 'aria-expanded="false"' ) && false !== strpos( $a->html, ' hidden' ), 'toggle safe progressive enhancement baseline' );
check( false !== strpos( $a->html, 'sub-menu' ), 'native submenu output retained' );
check( false !== strpos( $a->html, 'data-flx-submenu-open-label="Open submenu"' ) && false !== strpos( $a->html, 'data-flx-submenu-close-label="Close submenu"' ), 'localized disclosure labels supplied by native navigation component' );
check( '' === flx_component( 'flx.header.navigation' )->html, 'missing navigation instance target safely returns empty' );
$GLOBALS['header_logo'] = 10; check( false !== strpos( flx_component( 'flx.header.logo' )->html, '<img' ), 'valid attachment renders logo' );
$GLOBALS['header_logo'] = 999; check( false === strpos( flx_component( 'flx.header.logo' )->html, '<img' ), 'invalid image falls back no broken image' );
add_action( 'flx/header/brand/after', function () { echo '<span class="flx-test-extension">Extension</span>'; } );
check( false !== strpos( flx_component( 'flx.header' )->html, 'flx-test-extension' ), 'Child-style public hook extension captured in render result' );
add_filter( 'flx/component/props', function ( $props, $id ) { if ( 'flx.header.logo' === $id ) { $props['child_fixture'] = true; } return $props; }, 10, 2 );
check( ! flx_component( 'flx.header' )->errors, 'existing component props extension remains compatible' );
check( 0 === $GLOBALS['fx_writes'] && 0 === $GLOBALS['fx_queries'], 'render no persistent writes/network/content queries in fixture' );
$css = file_get_contents( dirname( __DIR__ ) . '/flexora/assets/header/header.css' );
check( false !== strpos( $css, '.flx-header' ) && false !== strpos( $css, ':focus-visible' ) && false !== strpos( $css, 'outline: 3px' ), 'Header CSS landmark and visible focus contract' );
check( false !== strpos( $css, '@media (max-width: 900px)' ) && false !== strpos( $css, '[hidden]' ) && false !== strpos( $css, 'prefers-reduced-motion' ), 'Header responsive, hidden-state and reduced-motion CSS contract' );
echo 'PASS Header portable: ' . $GLOBALS['fx_assertions'] . " assertions\n";
