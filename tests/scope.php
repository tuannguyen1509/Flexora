<?php
require __DIR__ . '/wp-fixture.php';
$scope = $argv[1];
if ( 'admin' === $scope ) { $GLOBALS['fx_admin'] = true; }
if ( 'ajax' === $scope ) { define( 'DOING_AJAX', true ); $GLOBALS['fx_admin'] = true; }
if ( 'rest' === $scope ) { define( 'REST_REQUEST', true ); }
if ( 'cron' === $scope ) { define( 'DOING_CRON', true ); }
if ( 'cli' === $scope ) { define( 'WP_CLI', true ); }
require __DIR__ . '/fixtures/child.php';
require __DIR__ . '/../flexora/bootstrap.php';
do_action( 'after_setup_theme' );
check( 0 === $GLOBALS['fx_boot_count'], 'registration does not boot' );
do_action( 'init' );
do_action( 'admin_init' );
do_action( 'rest_api_init' );
do_action( 'wp' );
do_action( 'init' );
do_action( 'admin_init' );
do_action( 'rest_api_init' );
check( 1 === $GLOBALS['fx_boot_count'], 'correct scope dispatcher boots once' );
check( array( $scope ) === flx_module( 'fixture.enabled' )->booted, 'scope identity correctly retained' );
check( ! flx_asset( 'fixture.card' )->accepted, 'frontend asset blocked in other scope' );
check( array() === $GLOBALS['fx_queue']['style'] && 0 === $GLOBALS['fx_writes'] && 0 === $GLOBALS['fx_queries'], 'no accidental frontend loading or DB work' );
echo 'PASS scope ' . $scope . ': ' . $GLOBALS['fx_assertions'] . " assertions\n";
