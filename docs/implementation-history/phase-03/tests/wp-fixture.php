<?php
/** Deliberately a WP API fixture, NOT WordPress integration. */
define( 'ABSPATH', __DIR__ . '/not-a-wordpress-install/' );
$GLOBALS['fx_hooks'] = array();
$GLOBALS['fx_actions'] = array();
$GLOBALS['fx_stack'] = array();
$GLOBALS['fx_assets'] = array( 'style' => array(), 'script' => array() );
$GLOBALS['fx_queue'] = array( 'style' => array(), 'script' => array() );
$GLOBALS['fx_enqueue_calls'] = array();
$GLOBALS['fx_writes'] = 0;
$GLOBALS['fx_queries'] = 0;
function fx_callback_id( $callback ) {
	if ( is_string( $callback ) ) { return $callback; }
	if ( is_array( $callback ) ) { return ( is_object( $callback[0] ) ? spl_object_hash( $callback[0] ) : $callback[0] ) . '::' . $callback[1]; }
	return spl_object_hash( $callback );
}
function add_filter( $hook, $callback, $priority = 10, $args = 1 ) {
	$GLOBALS['fx_hooks'][ $hook ][ $priority ][ fx_callback_id( $callback ) ] = array( $callback, $args );
	return true;
}
function add_action( $hook, $callback, $priority = 10, $args = 1 ) { return add_filter( $hook, $callback, $priority, $args ); }
function remove_filter( $hook, $callback, $priority = 10 ) {
	$id = fx_callback_id( $callback );
	$exists = isset( $GLOBALS['fx_hooks'][ $hook ][ $priority ][ $id ] );
	unset( $GLOBALS['fx_hooks'][ $hook ][ $priority ][ $id ] );
	return $exists;
}
function remove_action( $hook, $callback, $priority = 10 ) { return remove_filter( $hook, $callback, $priority ); }
function has_action( $hook, $callback ) {
	foreach ( isset( $GLOBALS['fx_hooks'][ $hook ] ) ? $GLOBALS['fx_hooks'][ $hook ] : array() as $priority => $callbacks ) {
		if ( isset( $callbacks[ fx_callback_id( $callback ) ] ) ) { return $priority; }
	}
	return false;
}
function do_action( $hook, ...$args ) {
	$GLOBALS['fx_actions'][ $hook ] = did_action( $hook ) + 1;
	$GLOBALS['fx_stack'][] = $hook;
	$callbacks = isset( $GLOBALS['fx_hooks'][ $hook ] ) ? $GLOBALS['fx_hooks'][ $hook ] : array();
	ksort( $callbacks );
	foreach ( $callbacks as $priority => $entries ) {
		foreach ( $entries as $entry ) { call_user_func_array( $entry[0], array_slice( $args, 0, $entry[1] ) ); }
	}
	array_pop( $GLOBALS['fx_stack'] );
}
function apply_filters( $hook, $value, ...$args ) {
	$callbacks = isset( $GLOBALS['fx_hooks'][ $hook ] ) ? $GLOBALS['fx_hooks'][ $hook ] : array();
	ksort( $callbacks );
	foreach ( $callbacks as $entries ) {
		foreach ( $entries as $entry ) { $value = call_user_func_array( $entry[0], array_slice( array_merge( array( $value ), $args ), 0, $entry[1] ) ); }
	}
	return $value;
}
function current_filter() { return end( $GLOBALS['fx_stack'] ); }
function did_action( $hook ) { return isset( $GLOBALS['fx_actions'][ $hook ] ) ? $GLOBALS['fx_actions'][ $hook ] : 0; }
function is_admin() { return ! empty( $GLOBALS['fx_admin'] ); }
function esc_html( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function __( $value, $domain = 'default' ) { return $value; }
function get_template_directory_uri() { return '/flx-preview'; }
// Foundation tests isolate the native module; production default remains enabled.
add_filter( 'flx/modules/enabled', function ( $enabled, $id ) { return 'flx.accessibility' === $id ? false : $enabled; }, 10, 2 );
function fx_register( $type, $handle, $source, $deps, $version, $footer = null ) {
	$GLOBALS['fx_assets'][ $type ][ $handle ] = array( 'src' => $source, 'deps' => $deps, 'version' => $version, 'footer' => $footer );
	return true;
}
function wp_register_style( $handle, $source, $deps = array(), $version = false, $media = 'all' ) { return fx_register( 'style', $handle, $source, $deps, $version ); }
function wp_register_script( $handle, $source, $deps = array(), $version = false, $footer = false ) { return fx_register( 'script', $handle, $source, $deps, $version, $footer ); }
function wp_style_is( $handle, $status = 'enqueued' ) { return isset( $GLOBALS[ 'registered' === $status ? 'fx_assets' : 'fx_queue' ]['style'][ $handle ] ); }
function wp_script_is( $handle, $status = 'enqueued' ) { return isset( $GLOBALS[ 'registered' === $status ? 'fx_assets' : 'fx_queue' ]['script'][ $handle ] ); }
function fx_enqueue( $type, $handle ) {
	$key = $type . ':' . $handle;
	$GLOBALS['fx_enqueue_calls'][ $key ] = isset( $GLOBALS['fx_enqueue_calls'][ $key ] ) ? $GLOBALS['fx_enqueue_calls'][ $key ] + 1 : 1;
	$GLOBALS['fx_queue'][ $type ][ $handle ] = true;
}
function wp_enqueue_style( $handle ) { fx_enqueue( 'style', $handle ); }
function wp_enqueue_script( $handle ) { fx_enqueue( 'script', $handle ); }
function update_option() { $GLOBALS['fx_writes']++; throw new RuntimeException( 'DB write forbidden' ); }
function add_option() { return update_option(); }
function delete_option() { return update_option(); }
function update_post_meta() { return update_option(); }
function wp_insert_post() { return update_option(); }
function wp_schedule_event() { return update_option(); }
function get_posts() { $GLOBALS['fx_queries']++; throw new RuntimeException( 'Content query forbidden' ); }
class WP_Query { public function __construct() { get_posts(); } }
function check( $condition, $message ) {
	if ( ! $condition ) { fwrite( STDERR, 'FAIL: ' . $message . PHP_EOL ); exit( 1 ); }
	$GLOBALS['fx_assertions'] = isset( $GLOBALS['fx_assertions'] ) ? $GLOBALS['fx_assertions'] + 1 : 1;
}
set_error_handler( function ( $severity, $message, $file, $line ) { throw new ErrorException( $message, 0, $severity, $file, $line ); } );
