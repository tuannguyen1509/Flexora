<?php
require __DIR__ . '/wp-fixture.php';
$mode = isset( $argv[1] ) ? $argv[1] : 'enabled';
$enabled = 'disabled' !== $mode;
if ( 'admin' === $mode ) { $GLOBALS['fx_admin'] = true; }
add_filter( 'flx/modules/enabled', function ( $value, $id ) use ( $enabled ) { return 'flx.accessibility' === $id ? $enabled : $value; }, 20, 2 );
require __DIR__ . '/../flexora/bootstrap.php';
require __DIR__ . '/../flexora/bootstrap.php';
do_action( 'after_setup_theme' );
$callback = array( 'FLX\\Modules\\Accessibility\\Module', 'render' );
check( false === has_action( 'wp_body_open', $callback ), 'declarations do not attach feature hooks' );
check( array() === $GLOBALS['fx_queue']['style'] && array() === $GLOBALS['fx_queue']['script'], 'registration does not enqueue feature assets' );
check( $enabled === flx_module( 'flx.accessibility' )->enabled, 'module state follows explicit code configuration without DB' );
if ( 'component' === $mode ) {
	// Simulated Child consumer: no direct asset call and no module boot needed.
	$usage = flx_use_component( 'flx.accessibility.skip-link' );
	check( $usage->accepted, 'component-only preflight accepted' );
	check( array( 'flx.accessibility.skip-link.style', 'flx.accessibility.skip-link.script' ) === $usage->assets, 'component traverses style and behavior script' );
	check( in_array( array( 'type' => 'behavior', 'id' => 'flx.accessibility.skip-link' ), $usage->usage, true ), 'behavior is recorded in resolved usage' );
	check( flx_use_component( 'flx.accessibility.skip-link' )->accepted, 'production preflight is idempotent' );
} else {
	do_action( 'wp' );
	do_action( 'wp' );
	do_action( 'admin_init' );
}
do_action( 'wp_enqueue_scripts' );
do_action( 'wp_head' );
ob_start();
if ( 'component' === $mode ) {
	flx_render( 'flx.accessibility.skip-link' );
} else {
	do_action( 'wp_body_open' );
	do_action( 'wp_body_open' );
}
$html = ob_get_clean();
if ( ! $enabled || 'admin' === $mode ) {
	check( false === has_action( 'wp_body_open', $callback ), 'disabled or other scope has zero feature render hooks' );
	check( '' === $html && array() === flx_module( 'flx.accessibility' )->booted, 'disabled or other scope never boots or renders' );
	check( array() === $GLOBALS['fx_queue']['style'] && array() === $GLOBALS['fx_queue']['script'], 'disabled or other scope has zero CSS/JS' );
	check( ! flx_use_component( 'flx.accessibility.skip-link' )->accepted, 'disabled or other scope cannot request component assets' );
} else {
	check( 1 === substr_count( $html, '<a ' ) && 1 === substr_count( $html, 'class="flx-skip-link"' ), 'one skip link even if body hook repeats' );
	check( false !== strpos( $html, 'href="#flx-main-content"' ), 'default FLX fragment target' );
	check( false !== strpos( $html, 'Bỏ qua tới nội dung' ), 'default translated label' );
	check( array( 'style:flx-accessibility-skip-link' => 1, 'script:flx-accessibility-skip-link-js' => 1 ) === $GLOBALS['fx_enqueue_calls'], 'module preflights CSS/JS once through registry' );
	check( true === $GLOBALS['fx_assets']['script']['flx-accessibility-skip-link-js']['footer'], 'behavior script is registered for footer' );
	check( '/flx-preview/assets/accessibility/skip-link.css' === $GLOBALS['fx_assets']['style']['flx-accessibility-skip-link']['src'], 'asset URI belongs to native Parent package' );
	$custom = flx_component( 'flx.accessibility.skip-link', array( 'label' => '<Go & "main">' ) );
	check( false !== strpos( $custom->html, '&lt;Go &amp; &quot;main&quot;&gt;' ), 'label escaped as text' );
	check( 'invalid_props' === flx_component( 'flx.accessibility.skip-link', array( 'label' => array() ) )->errors[0]['code'] && 'invalid_props' === flx_component( 'flx.accessibility.skip-link', array( 'label' => ' ' ) )->errors[0]['code'], 'malformed or blank accessible label rejected' );
	if ( 'enabled' === $mode ) {
		check( 1 === has_action( 'wp_body_open', $callback ), 'FLX owned body callback priority 1' );
		check( array( 'frontend' ) === flx_module( 'flx.accessibility' )->booted, 'enabled module booted once in frontend scope' );
	}
}
do_action( 'wp_footer' );
check( 0 === $GLOBALS['fx_queries'] && 0 === $GLOBALS['fx_writes'], 'no query or persistent write' );
echo 'PASS accessibility ' . $mode . ': ' . $GLOBALS['fx_assertions'] . " assertions\n";
