<?php
require __DIR__ . '/wp-fixture.php';
use FLX\Contracts\V1\SpecV1;
require __DIR__ . '/../flexora/bootstrap.php';
$order = array();
add_action( 'flx/register', function ( $r ) use ( &$order ) {
	$order[] = 5;
	check( $r->provider( new SpecV1( array( 'id' => 'native', 'owner' => 'parent', 'version' => '1.0.0', 'dependencies' => array(), 'assets' => array() ) ) ), 'successful provider declaration' );
	check( ! $r->asset( new SpecV1( array( 'id' => '../input', 'owner' => 'child', 'version' => '1.0.0' ) ) ), 'invalid IDs rejected' );
	check( ! $r->provider( new SpecV1( array( 'id' => 'future', 'owner' => 'parent', 'version' => '1.0.0', 'dependencies' => array(), 'assets' => array(), 'engine' => '>=2.0.0' ) ) ), 'unimplemented metadata semantics rejected instead of silently ignored' );
	check( ! $r->component( new SpecV1( array( 'id' => 'invalid', 'owner' => 'child', 'version' => '1.0.0', 'dependencies' => array(), 'assets' => array(), 'behaviors' => array(), 'renderer' => 'missing_function' ) ) ), 'invalid renderer rejected before runtime' );
	$r->behavior( new SpecV1( array( 'id' => 'missing-provider', 'owner' => 'parent', 'version' => '1.0.0', 'dependencies' => array(), 'assets' => array(), 'provider' => 'absent' ) ) );
	$r->component( new SpecV1( array( 'id' => 'unavailable', 'owner' => 'parent', 'version' => '1.0.0', 'dependencies' => array(), 'assets' => array(), 'behaviors' => array( 'missing-provider' ), 'renderer' => function () { throw new RuntimeException( 'Must never render' ); } ) ) );
}, 5 );
add_action( 'flx/register', function () use ( &$order ) { $order[] = 15; }, 15 );
do_action( 'after_setup_theme' );
do_action( 'after_setup_theme' );
check( array( 5, 15 ) === $order && 1 === did_action( 'flx/register' ), 'deterministic priorities, registration dispatched once even if setup repeated' );
check( 'dependency_unavailable' === flx_component( 'unavailable' )->errors[0]['code'], 'unsupported provider blocks component' );
$version = 'FLX\\Support\\VersionRange';
check( $version::matches( '1.2.3', '^1.0' ) && ! $version::matches( '2.0.0', '^1.0' ), 'caret major range bounds' );
check( $version::matches( '0.2.3', '^0.2.0' ) && ! $version::matches( '0.3.0', '^0.2.0' ), 'caret zero-major bounds' );
check( $version::matches( '0.0.5', '^0.0' ) && ! $version::matches( '0.0.5', '^0.0.0' ), 'caret partial zero version differs from exact zero patch' );
check( $version::matches( '1.2.3', '>=1.2.0' ) && ! $version::matches( '1.2.3', '<unsupported>' ), 'unsupported range fails closed' );
echo 'PASS registration: ' . $GLOBALS['fx_assertions'] . " assertions\n";
