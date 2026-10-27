<?php
/** Simulated Child functions.php: no immediate Parent API call. */
use Flexora\Contracts\V1\SpecV1;

$GLOBALS['fx_order'][] = 'child';
$GLOBALS['fx_render_count'] = 0;
$GLOBALS['fx_boot_count'] = 0;
function fx_card( $props ) {
	$GLOBALS['fx_render_count']++;
	return '<article class="fixture-card"><h2>' . esc_html( array_key_exists( 'title', $props ) ? $props['title'] : '' ) . '</h2></article>';
}
function fx_feature_callback( $value = null ) { return $value; }
function fx_module_boot( $hooks, $context ) {
	$GLOBALS['fx_boot_count']++;
	$hooks->action( 'fixture/feature', 'fx_feature_callback', 17, 2 );
}
function fx_register_fixture( $r ) {
	$GLOBALS['fx_order'][] = 'registration';
	$GLOBALS['fx_registrar'] = $r;
	$r->asset( new SpecV1( array( 'id' => 'fixture.base', 'owner' => 'child:fixture', 'version' => '1.0.0', 'type' => 'style', 'handle' => 'fixture-base', 'source' => 'fixture-base.css', 'dependencies' => array(), 'context' => array( 'frontend' ) ) ) );
	$r->asset( new SpecV1( array( 'id' => 'fixture.card', 'owner' => 'child:fixture', 'version' => '1.0.0', 'type' => 'style', 'handle' => 'fixture-card', 'source' => 'fixture-card.css', 'dependencies' => array( 'fixture.base' ), 'context' => array( 'frontend' ) ) ) );
	$r->asset( new SpecV1( array( 'id' => 'fixture.unneeded', 'owner' => 'child:fixture', 'version' => '1.0.0', 'type' => 'script', 'handle' => 'fixture-unneeded', 'source' => 'fixture-unneeded.js', 'dependencies' => array(), 'context' => array( 'frontend' ) ) ) );
	$r->provider( new SpecV1( array( 'id' => 'fixture.native', 'owner' => 'parent', 'version' => '1.0.0', 'dependencies' => array(), 'assets' => array() ) ) );
	$r->behavior( new SpecV1( array( 'id' => 'fixture.static', 'owner' => 'parent', 'version' => '1.0.0', 'dependencies' => array(), 'provider' => 'fixture.native', 'assets' => array() ) ) );
	$r->component( new SpecV1( array( 'id' => 'fixture.test-card', 'owner' => 'child:fixture', 'version' => '1.0.0', 'dependencies' => array(), 'renderer' => 'fx_card', 'validate_props' => function ( $props ) { return ! array_key_exists( 'title', $props ) || is_string( $props['title'] ); }, 'assets' => array( 'fixture.card' ), 'behaviors' => array( 'fixture.static' ) ) ) );
	foreach ( array( 'enabled' => true, 'disabled' => false ) as $name => $enabled ) {
		$r->module( new SpecV1( array( 'id' => 'fixture.' . $name, 'owner' => 'child:fixture', 'version' => '1.0.0', 'enabled' => $enabled, 'available' => true, 'dependencies' => array(), 'scope' => array( 'frontend', 'admin', 'ajax', 'rest', 'cron', 'cli' ), 'capabilities' => array( 'fixture.' . $name ), 'boot' => 'fx_module_boot' ) ) );
	}
}
add_action( 'flexora/register', 'fx_register_fixture', 10, 1 );
add_action( 'after_setup_theme', function () { $GLOBALS['fx_order'][] = 'legacy20'; }, 20 );
add_action( 'after_setup_theme', function () { $GLOBALS['fx_order'][] = 'legacy25'; }, 25 );
add_action( 'after_setup_theme', function () { $GLOBALS['fx_order'][] = 'sealed'; }, 41 );
