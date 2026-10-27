<?php
require __DIR__ . '/wp-fixture.php';
use Flexora\Contracts\V1\SpecV1;
use Flexora\Contracts\V1\LookupV1;

$flexora_profile = isset( $argv[1] ) ? $argv[1] : 'flexora-native';
require __DIR__ . '/fixtures/child.php';
check( ! function_exists( 'flexora' ), 'Child declarations precede Parent API' );
$GLOBALS['fx_order'][] = 'parent';
require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../bootstrap.php';
check( 30 === has_action( 'after_setup_theme', array( Flexora\Core\Runtime::kernel(), 'register' ) ), 'registration priority 30' );
check( 40 === has_action( 'after_setup_theme', array( Flexora\Core\Runtime::kernel(), 'seal' ) ), 'seal priority 40' );
check( array() === $GLOBALS['fx_queue']['style'], 'initialization does not enqueue' );
check( array() === $GLOBALS['fx_assets']['style'], 'initialization does not WP-register unused styles' );
check( '0.1.0' === flexora()->api_version() && '0.1.0' === flexora()->parent_version(), 'runtime preview and foundation versions' );
$facade_methods = get_class_methods( flexora() );
sort( $facade_methods );
check( array( '__construct', 'api_version', 'diagnostics', 'parent_version', 'supports' ) === $facade_methods, 'facade exposes only read-only methods' );
check( ! method_exists( flexora(), 'get_service' ) && ! method_exists( flexora(), 'register' ), 'facade has no internal service or mutation methods' );
check( ! flexora_supports( 'api', '^1.0' ) && flexora_supports( 'api', '^0.1' ) && ! flexora_supports( 'api', '^2.0' ), 'runtime preview does not claim full target API' );
check( ! flexora_supports( 'slider' ) && ! flexora_supports( 'fixture.enabled' ), 'no invented capability or preseal availability' );
add_action( 'flexora/register', function ( $r ) {
	$asset = new SpecV1( array( 'id' => 'fixture.base', 'owner' => 'other', 'version' => '1.0.0', 'type' => 'style', 'handle' => 'wrong', 'source' => 'wrong.css', 'dependencies' => array(), 'context' => array( 'frontend' ) ) );
	check( false === $r->asset( $asset ), 'duplicate asset rejected, original owner retained' );
	check( false === $r->component( new SpecV1( array( 'id' => 'fixture.test-card', 'owner' => 'other', 'version' => '1.0.0' ) ) ), 'duplicate component rejected' );
	check( false === $r->module( new SpecV1( array( 'id' => 'fixture.enabled', 'owner' => 'other', 'version' => '1.0.0' ) ) ), 'duplicate module rejected' );
	$r->asset( new SpecV1( array( 'id' => 'fixture.broken', 'owner' => 'parent', 'version' => '1.0.0', 'type' => 'style', 'handle' => 'fixture-broken', 'source' => 'broken.css', 'dependencies' => array( 'absent' ), 'context' => array( 'frontend' ) ) ) );
	foreach ( array( 'cycle-a' => 'cycle-b', 'cycle-b' => 'cycle-a' ) as $id => $dependency ) {
		$r->asset( new SpecV1( array( 'id' => $id, 'owner' => 'parent', 'version' => '1.0.0', 'type' => 'style', 'handle' => $id, 'source' => $id . '.css', 'dependencies' => array( $dependency ), 'context' => array( 'frontend' ) ) ) );
	}
	$r->asset( new SpecV1( array( 'id' => 'fixture.disabled', 'owner' => 'parent', 'version' => '1.0.0', 'module' => 'fixture.disabled', 'type' => 'style', 'handle' => 'fixture-disabled', 'source' => 'disabled.css', 'dependencies' => array(), 'context' => array( 'frontend' ) ) ) );
	$r->component( new SpecV1( array( 'id' => 'fixture.disabled', 'owner' => 'parent', 'version' => '1.0.0', 'module' => 'fixture.disabled', 'renderer' => 'fx_card', 'dependencies' => array(), 'assets' => array(), 'behaviors' => array() ) ) );
	$r->component( new SpecV1( array( 'id' => 'fixture.throw', 'owner' => 'parent', 'version' => '1.0.0', 'renderer' => function () { echo 'SECRET'; throw new RuntimeException( 'SECRET' ); }, 'dependencies' => array(), 'assets' => array(), 'behaviors' => array() ) ) );
	$r->module( new SpecV1( array( 'id' => 'fixture.dependent', 'owner' => 'parent', 'version' => '1.0.0', 'enabled' => true, 'available' => true, 'dependencies' => array( 'fixture.disabled' ), 'scope' => array( 'frontend' ), 'capabilities' => array( 'fixture.dependent' ), 'boot' => 'fx_module_boot' ) ) );
	$r->module( new SpecV1( array( 'id' => 'fixture.throw', 'owner' => 'parent', 'version' => '1.0.0', 'enabled' => true, 'available' => true, 'dependencies' => array(), 'scope' => array( 'frontend' ), 'capabilities' => array( 'fixture.throw' ), 'boot' => function ( $hooks ) { $hooks->action( 'fixture/partial', 'fx_feature_callback' ); throw new RuntimeException( 'SECRET' ); } ) ) );
}, 20 );
do_action( 'after_setup_theme' );
check( array( 'child', 'parent', 'legacy20', 'legacy25', 'registration', 'sealed' ) === $GLOBALS['fx_order'], 'Child-first and 20/25/30/40 lifecycle order' );
check( false === $GLOBALS['fx_registrar']->asset( new SpecV1( array( 'id' => 'late' ) ) ), 'late registration rejects retained registrar' );
check( flexora_supports( 'fixture.enabled', '^1.0' ), 'enabled available capability can be read without boot' );
check( 0 === $GLOBALS['fx_boot_count'] && array() === flexora_module( 'fixture.enabled' )->booted, 'supports and module status never boot' );
check( ! flexora_supports( 'fixture.disabled' ) && ! flexora_supports( 'fixture.dependent' ), 'disabled dependency never implicitly enabled' );
check( 'undeclared' === flexora_module( 'absent' )->reason, 'missing module has explicit status' );
check( 'disabled' === flexora_module( 'fixture.disabled' )->reason, 'disabled distinct from missing' );
check( ! function_exists( 'flexora_map' ) && ! function_exists( 'flexora_option' ), 'no fake map or storage APIs' );
$before = $GLOBALS['fx_render_count'];
$result = flexora_component( 'fixture.test-card', array( 'title' => '<Hello>' ) );
check( $before + 1 === $GLOBALS['fx_render_count'], 'component calls renderer once' );
check( '<article class="fixture-card"><h2>&lt;Hello&gt;</h2></article>' === $result->html, 'trusted fixture view escapes input' );
check( array( array( 'type' => 'asset', 'id' => 'fixture.base' ), array( 'type' => 'asset', 'id' => 'fixture.card' ), array( 'type' => 'behavior', 'id' => 'fixture.static' ) ) === $result->usage, 'component resolves provider/behavior and dependency asset usage' );
check( array() === $GLOBALS['fx_queue']['style'], 'render collects usage but does not enqueue immediately' );
ob_start();
$status = flexora_render( 'fixture.test-card', array( 'title' => 'Echo' ) );
$output = ob_get_clean();
check( $before + 2 === $GLOBALS['fx_render_count'] && 1 === substr_count( $output, '<article' ) && array() === $status->errors, 'render echo wrapper executes and echoes once' );
check( '' === flexora_component( 'missing' )->html, 'missing component fails safely' );
check( 'component_unknown' === flexora_component( 'missing' )->errors[0]['code'], 'missing component structured error' );
check( '' === flexora_component( 'fixture.disabled' )->html, 'disabled module blocks component' );
check( 'invalid_props' === flexora_component( 'fixture.test-card', array( 'title' => array() ) )->errors[0]['code'], 'typed props validation' );
ob_start();
$failure = flexora_component( 'fixture.throw' );
check( '' === ob_get_clean() && '' === $failure->html && 'renderer_failed' === $failure->errors[0]['code'], 'renderer exception and stray output do not expose secrets' );
$mutated = false;
try { $result->html = 'override'; } catch ( LogicException $error ) { $mutated = true; }
check( $mutated, 'render result immutable' );
check( flexora_asset( 'fixture.card' )->accepted && flexora_asset( 'fixture.card' )->accepted, 'repeated usage succeeds idempotently' );
check( ! flexora_asset( 'missing' )->accepted, 'missing asset safe failure' );
check( ! flexora_asset( 'fixture.broken' )->accepted && ! flexora_asset( 'cycle-a' )->accepted, 'missing and cyclic dependency reject without enqueue' );
check( ! flexora_asset( 'fixture.disabled' )->accepted, 'disabled module asset blocked' );
do_action( 'wp' );
do_action( 'wp' );
check( 1 === $GLOBALS['fx_boot_count'], 'enabled module boots exactly once; disabled/dependent do not boot' );
check( 17 === has_action( 'fixture/feature', 'fx_feature_callback' ), 'module hook callback identity and priority retained' );
check( 2 === $GLOBALS['fx_hooks']['fixture/feature'][17]['fx_feature_callback'][1], 'module hook accepted_args retained' );
check( false === has_action( 'fixture/partial', 'fx_feature_callback' ) && ! flexora_supports( 'fixture.throw' ), 'failed boot removes partial hooks and becomes unavailable' );
check( array( 'frontend' ) === flexora_module( 'fixture.enabled' )->booted, 'booted scopes reported' );
do_action( 'wp_enqueue_scripts' );
do_action( 'wp_enqueue_scripts' );
check( array( 'fixture-base' => true, 'fixture-card' => true ) === $GLOBALS['fx_queue']['style'], 'only required styles enqueued in dependency order' );
check( array( 'style:fixture-base' => 1, 'style:fixture-card' => 1 ) === $GLOBALS['fx_enqueue_calls'], 'three consumers/repeated flush enqueue each handle once' );
check( array( 'fixture-base' ) === $GLOBALS['fx_assets']['style']['fixture-card']['deps'], 'logical dependencies mapped to WP handles' );
check( array() === $GLOBALS['fx_queue']['script'], 'unneeded scripts never enqueued' );
check( 'fixture-base.css' === $GLOBALS['fx_assets']['style']['fixture-base']['src'], 'duplicate owner did not overwrite source' );
do_action( 'wp_head' );
check( flexora_asset( 'fixture.card' )->accepted, 'already flushed stylesheet can be used again after head' );
foreach ( array( null, false, 0, '', array() ) as $value ) {
	$lookup = LookupV1::found( $value, 'fixture', '1.0.0', 'r1' );
	check( true === $lookup->present && $value === $lookup->value, 'presence preserves explicit empty scalar/list value' );
}
check( false === LookupV1::missing()->present, 'missing distinct from null' );
check( 0 === $GLOBALS['fx_writes'] && 0 === $GLOBALS['fx_queries'], 'initialization/registration/read/render/boot did not write DB or query content' );
$codes = array_column( flexora()->diagnostics(), 'code' );
foreach ( array( 'duplicate_asset_id', 'duplicate_component_id', 'duplicate_module_id', 'late_registration', 'unsupported_dependency', 'dependency_cycle', 'component_unknown', 'missing_asset', 'unavailable_module' ) as $code ) {
	check( in_array( $code, $codes, true ), 'diagnostic ' . $code );
}
check( false === strpos( json_encode( flexora()->diagnostics() ), 'SECRET' ), 'diagnostics redact exception/output data' );
echo 'PASS foundation ' . $flexora_profile . ': ' . $GLOBALS['fx_assertions'] . " assertions\n";
