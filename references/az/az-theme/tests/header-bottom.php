<?php
/** Isolated child Header bottom rendering regression. */
define( 'ABSPATH', __DIR__ );
$enabled = true;
$selected = 12;
function az_get_option( $key, $default = null ) { global $enabled; return 'header.show_primary_menu' === $key ? $enabled : $default; }
function az_header_options() { global $selected; return array( 'primary_menu' => $selected ); }
function add_action() {}
function add_filter() {}
function has_nav_menu() { return true; }
function absint( $value ) { return abs( (int) $value ); }
function wp_nav_menu( $args ) { echo 'MENU:' . $args['menu'] . ':' . $args['theme_location']; }
require dirname( dirname( __DIR__ ) ) . '/az-theme-child/inc/steel-storefront.php';
function verify_menu( $expected, $label ) {
    ob_start(); az_child_steel_main_menu(); $actual = ob_get_clean();
    if ( $actual !== $expected ) { fwrite( STDERR, "FAIL $label\n" ); exit( 1 ); }
    echo "PASS $label\n";
}
verify_menu( 'MENU:12:primary', 'enabled Header bottom renders selected menu' );
$enabled = false;
verify_menu( '', 'disabled Header bottom renders no primary navigation' );
$enabled = true; $selected = 0;
verify_menu( 'MENU:0:primary', 'default menu uses WordPress primary location' );
