<?php
require __DIR__ . '/wp-fixture.php';
require __DIR__ . '/fixtures/child.php';
require __DIR__ . '/../flexora/bootstrap.php';
do_action( 'after_setup_theme' );
add_action( 'wp_head', function () { do_action( 'wp_enqueue_scripts' ); }, 1 );
add_action( 'wp_enqueue_scripts', function () {
	check( doing_action( 'wp_head' ), 'fixture reproduces nested core enqueue timing' );
	check( flx_use_component( 'fixture.test-card' )->accepted, 'component preflight permitted in head nested enqueue before flush' );
}, 20 );
do_action( 'wp_head' );
check( isset( $GLOBALS['fx_queue']['style']['fixture-card'] ), 'nested enqueue flush sends required stylesheet' );
check( array() === flx()->diagnostics(), 'normal core hook timing has no false late CSS diagnostics' );
echo 'PASS head enqueue timing: ' . $GLOBALS['fx_assertions'] . " assertions\n";
