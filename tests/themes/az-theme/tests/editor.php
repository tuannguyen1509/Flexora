<?php
define( 'ABSPATH', __DIR__ );
$enabled = false;
function az_get_option( $key, $default ) { global $enabled; return $enabled; }
function apply_filters( $name, $value ) { return $value; }
function add_filter() {}
require dirname( __DIR__ ) . '/inc/core/editor.php';
function check_editor( $actual, $expected, $label ) {
    if ( $actual !== $expected ) { fwrite( STDERR, "FAIL $label\n" ); exit( 1 ); }
    echo "PASS $label\n";
}
check_editor( az_theme_use_block_editor( true, 'post' ), true, 'disabled switch preserves block editor' );
check_editor( az_theme_use_block_editor( false, 'post' ), false, 'disabled switch preserves plugin Classic preference' );
$enabled = true;
check_editor( az_theme_use_block_editor( true, 'post' ), false, 'enabled switch selects Classic for posts' );
check_editor( az_theme_use_block_editor_for_post( true, (object) array( 'post_type' => 'page' ) ), false, 'enabled switch selects Classic for pages' );
check_editor( az_theme_use_block_editor( true, 'product' ), true, 'unrelated custom post types unchanged' );
check_editor( az_theme_use_block_editor( true, 'wp_template' ), true, 'site templates unchanged' );
