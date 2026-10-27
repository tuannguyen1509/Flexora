<?php
/** Isolated logic checks; run with php tests/performance-security.php. */
define( 'ABSPATH', '/' );
$options = array(); $hooks = array(); $logged_in = false;
function add_filter( $name, $callback, ...$args ) { $GLOBALS['hooks'][$name] = $callback; }
function add_action( ...$args ) {}
function az_get_option( $key, $default = null ) { return $GLOBALS['options'][$key] ?? $default; }
function is_user_logged_in() { return $GLOBALS['logged_in']; }
function apply_filters( $name, $value ) { return $value; }
function __( $text, $domain ) { return $text; }
class WP_Error { public function __construct( ...$args ) {} }
function check( $value, $label ) { if ( ! $value ) { throw new RuntimeException( $label ); } echo "PASS $label\n"; }
require dirname( __DIR__ ) . '/inc/core/performance-security.php';
check( ! az_performance_security_enabled( 'disable_xmlrpc' ), 'all options off by default' );
$options['performance.disable_xmlrpc'] = 1;
check( $hooks['xmlrpc_enabled']( true ), 'master off preserves XML-RPC' );
$options['performance.security_enabled'] = 1;
check( array() === $hooks['xmlrpc_methods']( array( 'pingback.ping' => 'a', 'wp.getPosts' => 'b' ) ), 'XML-RPC methods disabled' );
$options['performance.disable_xmlrpc'] = 0;
$options['performance.disable_pingback'] = 1;
check( array( 'wp.getPosts' => 'b' ) === $hooks['xmlrpc_methods']( array( 'pingback.ping' => 'a', 'wp.getPosts' => 'b' ) ), 'pingback removed without disabling other methods' );
check( array( 'Other' => 'ok' ) === $hooks['wp_headers']( array( 'x-pingback' => 'url', 'Other' => 'ok' ) ), 'only pingback header removed' );
$options['performance.restrict_rest_guests'] = 1;
check( $hooks['rest_authentication_errors']( null ) instanceof WP_Error, 'anonymous REST restricted' );
$logged_in = true;
check( null === $hooks['rest_authentication_errors']( null ), 'authenticated REST preserved' );
$error = new WP_Error();
check( $error === $hooks['rest_authentication_errors']( $error ), 'existing authentication errors preserved' );
$file = array( 'error' => 'existing error' );
check( $file === az_security_validate_image_upload( $file ), 'uploads unchanged when option disabled' );
$options['performance.images_only_uploads'] = 1;
check( $file === az_security_validate_image_upload( $file ), 'existing upload errors preserved' );
