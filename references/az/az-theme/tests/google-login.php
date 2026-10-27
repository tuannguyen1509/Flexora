<?php
ob_start(); // Callback tests set cookies, just as the real pre-output endpoint does.
define( 'ABSPATH', '/' );
$options = array();
function add_shortcode( ...$args ) {}
function add_action( ...$args ) {}
function add_filter( ...$args ) {}
function az_get_option( $key, $default = null ) { return $GLOBALS['options'][$key] ?? $default; }
function is_email( $email ) { return filter_var( $email, FILTER_VALIDATE_EMAIL ); }
function check( $ok, $label ) { if ( ! $ok ) { throw new RuntimeException( $label ); } echo "PASS $label\n"; }
require dirname( __DIR__ ) . '/inc/core/google-login.php';
check( ! az_google_ready(), 'Google login disabled by default' );
$options = array( 'google.enabled' => 1, 'google.login_enabled' => 1, 'google.client_id' => 'test' );
check( ! az_google_ready(), 'missing secret disables login' );
$options['google.client_secret'] = 'secret';
check( (bool) az_google_ready(), 'fully configured login enabled' );
check( ! az_google_valid_identity( array( 'sub' => '123', 'email' => 'test@example.com', 'email_verified' => false ) ), 'unverified email rejected' );
check( ! az_google_valid_identity( array( 'email' => 'test@example.com', 'email_verified' => true ) ), 'missing subject rejected' );
check( az_google_valid_identity( array( 'sub' => '123', 'email' => 'test@example.com', 'email_verified' => true ) ), 'verified Google identity accepted' );
check( ! az_google_captcha_ready(), 'captcha off by default' );
$options['google.captcha_type'] = 'v2'; $options['google.site_key'] = 'site';
check( ! az_google_captcha_ready(), 'incomplete captcha not activated' );
$options['google.captcha_secret'] = 'secret';
check( (bool) az_google_captcha_ready(), 'configured v2 captcha enabled' );
class WP_Error { public function __construct( ...$args ) {} }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function wp_unslash( $value ) { return $value; }
function sanitize_text_field( $value ) { return trim( $value ); }
function wp_remote_post( ...$args ) { return $GLOBALS['response']; }
function wp_remote_retrieve_response_code( $value ) { return $value['code']; }
function wp_remote_retrieve_body( $value ) { return $value['body']; }
function site_url() { return 'https://example.com'; }
function wp_parse_url( $url, $component ) { return parse_url( $url, $component ); }
function is_user_logged_in() { return false; }
function nocache_headers() {}
function az_auth_rate_allowed( ...$args ) { return true; }
function az_auth_client_identity() { return 'test'; }
function wp_die( $message, ...$args ) { throw new RuntimeException( $message ); }
function get_transient( $key ) { return false; }
function delete_transient( $key ) {}
function is_ssl() { return true; }
$pagenow = 'wp-login.php'; $_SERVER['REQUEST_METHOD'] = 'POST'; $user = (object) array( 'ID' => 1 );
check( is_wp_error( az_google_verify_captcha( $user, '' ) ), 'missing captcha rejected' );
$_POST['g-recaptcha-response'] = 'test'; $response = new WP_Error();
check( is_wp_error( az_google_verify_captcha( $user, '' ) ), 'Google network failure fails closed' );
$response = array( 'code' => 200, 'body' => '{"success":true,"hostname":"evil.example"}' );
check( is_wp_error( az_google_verify_captcha( $user, '' ) ), 'foreign captcha hostname rejected' );
$response['body'] = '{"success":false,"error-codes":["timeout-or-duplicate"]}';
check( is_wp_error( az_google_verify_captcha( $user, '' ) ), 'expired captcha rejected' );
$response['body'] = '{"success":true,"hostname":"example.com"}';
check( $user === az_google_verify_captcha( $user, '' ), 'valid captcha preserves authenticated user' );
$_GET['state'] = str_repeat( 'a', 64 ); $_COOKIE['az_google_state'] = str_repeat( 'b', 64 );
try { az_google_callback(); throw new Exception( 'callback accepted mismatched state' ); }
catch ( RuntimeException $error ) { check( false !== strpos( $error->getMessage(), 'không hợp lệ' ), 'OAuth mismatched state rejected' ); }
$_COOKIE['az_google_state'] = $_GET['state'];
try { az_google_callback(); throw new Exception( 'callback accepted expired state' ); }
catch ( RuntimeException $error ) { check( false !== strpos( $error->getMessage(), 'hết hạn' ), 'OAuth expired callback rejected' ); }
