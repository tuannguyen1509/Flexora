<?php
require __DIR__ . '/wp-fixture.php';
use Flexora\Contracts\V1\SpecV1;

require __DIR__ . '/../bootstrap.php';
function asset_spec( $id, $extra = array() ) {
	return new SpecV1( array_merge( array( 'id' => $id, 'owner' => 'parent', 'version' => '1.0.0', 'type' => 'style', 'handle' => $id, 'source' => $id . '.css', 'dependencies' => array(), 'context' => array( 'frontend' ) ), $extra ) );
}
wp_register_style( 'external-owned', 'plugin.css', array(), '9.0.0' );
add_action( 'flexora/register', function ( $r ) {
	$r->asset( asset_spec( 'base' ) );
	$r->asset( asset_spec( 'conflict', array( 'handle' => 'external-owned', 'dependencies' => array( 'base' ) ) ) );
	$r->asset( asset_spec( 'reference', array( 'handle' => 'core-fixture', 'override_policy' => 'reference' ) ) );
	$r->asset( asset_spec( 'script', array( 'type' => 'script', 'source' => 'script.js', 'strategy' => 'footer' ) ) );
	$r->asset( asset_spec( 'cross-type', array( 'dependencies' => array( 'script' ) ) ) );
	$r->asset( asset_spec( 'late-style' ) );
	$r->asset( asset_spec( 'duplicate-handle-a', array( 'handle' => 'collision' ) ) );
	$r->asset( asset_spec( 'duplicate-handle-b', array( 'handle' => 'collision' ) ) );
} );
wp_register_style( 'core-fixture', 'core.css', array(), '2.0.0' );
do_action( 'after_setup_theme' );
check( flexora_asset( 'conflict' )->accepted, 'WP handle conflict detected at flush, not speculative registration' );
do_action( 'wp_enqueue_scripts' );
check( array() === $GLOBALS['fx_queue']['style'], 'root conflict does not enqueue half dependency graph' );
check( 'plugin.css' === $GLOBALS['fx_assets']['style']['external-owned']['src'], 'existing plugin handle never overwritten' );
check( ! flexora_asset( 'cross-type' )->accepted, 'cross-type dependency rejected' );
check( ! flexora_asset( 'duplicate-handle-a' )->accepted && ! flexora_asset( 'duplicate-handle-b' )->accepted, 'physical handle collision blocks both definitions' );
check( flexora_asset( 'reference' )->accepted && flexora_asset( 'script' )->accepted, 'explicit reference and script usage accepted' );
do_action( 'wp_enqueue_scripts' );
check( 'core.css' === $GLOBALS['fx_assets']['style']['core-fixture']['src'] && '2.0.0' === $GLOBALS['fx_assets']['style']['core-fixture']['version'], 'core reference does not re-register its source/version' );
check( true === $GLOBALS['fx_assets']['script']['script']['footer'], 'script uses backward-compatible WP footer argument' );
check( 1 === $GLOBALS['fx_enqueue_calls']['script:script'] && 1 === $GLOBALS['fx_enqueue_calls']['style:core-fixture'], 'needed handles enqueue exactly once' );
do_action( 'wp_head' );
check( ! flexora_asset( 'late-style' )->accepted, 'unexpected late CSS rejected visibly via diagnostic' );
check( in_array( 'late_style_usage', array_column( flexora()->diagnostics(), 'code' ), true ), 'late CSS diagnostic present' );
check( 0 === $GLOBALS['fx_writes'] && 0 === $GLOBALS['fx_queries'], 'asset flow no database work' );
echo 'PASS assets: ' . $GLOBALS['fx_assertions'] . " assertions\n";
