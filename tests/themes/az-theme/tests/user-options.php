<?php
define( 'ABSPATH', '/' );
$hooks = array(); $options = array(); $admin = false;
function add_action( ...$args ) {}
function add_filter( $name, $callback, ...$args ) { $GLOBALS['hooks'][$name] = $callback; }
function az_get_option( $key, $default = null ) { return $GLOBALS['options'][$key] ?? $default; }
function is_user_logged_in() { return true; }
function current_user_can( ...$args ) { return $GLOBALS['admin']; }
function user_can( ...$args ) { return $GLOBALS['admin']; }
function get_current_user_id() { return 5; }
function get_post( $id ) { return (object) array( 'post_type' => 'post', 'post_author' => 6 ); }
function check( $ok, $label ) { if ( ! $ok ) { throw new RuntimeException( $label ); } echo "PASS $label\n"; }
require dirname( __DIR__ ) . '/inc/core/user-options.php';
check( ! az_user_feature( 'own_content' ), 'master off by default' );
$options = array( 'user.enabled' => 1, 'user.own_content' => 1, 'user.hide_admin_bar' => 1 );
check( 5 === $hooks['ajax_query_attachments_args']( array() )['author'], 'Media restricted to current author' );
check( array( 'do_not_allow' ) === $hooks['map_meta_cap']( array( 'edit_posts' ), 'edit_post', 5, array( 7 ) ), 'cannot edit another author post' );
check( ! $hooks['show_admin_bar']( true ), 'non-admin bar hidden' );
$admin = true;
check( array() === $hooks['ajax_query_attachments_args']( array() ), 'administrator sees all Media' );
check( $hooks['show_admin_bar']( true ), 'administrator bar preserved in users mode' );
$options['user.admin_bar_scope'] = 'all';
check( ! $hooks['show_admin_bar']( true ), 'all mode also hides administrator bar' );
$options['user.enabled'] = 0;
check( $hooks['show_admin_bar']( true ), 'master off restores existing bar behavior' );
