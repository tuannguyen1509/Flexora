<?php
/** Isolated upload checks using the core sideload handler; no database/bootstrap. */
define( 'ABSPATH', dirname( __DIR__, 4 ) . '/' );
define( 'MB_IN_BYTES', 1024 * 1024 );
$options = array();
$hooks = array();
$server_limit = 32 * MB_IN_BYTES;
function az_get_option( $key, $default = null ) { return $GLOBALS['options'][ $key ] ?? $default; }
function add_filter( $hook, $callback, $priority = 10 ) { $GLOBALS['hooks'][ $hook ][ $priority ][] = $callback; }
function apply_filters( $hook, $value, ...$args ) {
    $callbacks = $GLOBALS['hooks'][ $hook ] ?? array();
    ksort( $callbacks );
    foreach ( $callbacks as $group ) {
        foreach ( $group as $callback ) { $value = $callback( $value, ...$args ); }
    }
    return $value;
}
function wp_max_upload_size() { return apply_filters( 'upload_size_limit', $GLOBALS['server_limit'] ); }
function wp_filesize( $path ) { return filesize( $path ); }
function __( $text, $domain = '' ) { return $text; }
function size_format( $bytes, $decimals = 0 ) { return number_format( $bytes / MB_IN_BYTES, $decimals ) . ' MB'; }
function admin_url( $path ) { return '/wp-admin/' . $path; }
function sanitize_key( $value ) { return $value; }
function sanitize_html_class( $value ) { return $value; }
function esc_attr( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $value ) { return esc_attr( $value ); }
function check( $ok, $message ) {
    if ( ! $ok ) { throw new RuntimeException( $message ); }
    echo "PASS $message\n";
}
require dirname( __DIR__ ) . '/inc/core/upload-limits.php';
require dirname( __DIR__ ) . '/inc/options/class-sanitizer.php';
require dirname( __DIR__ ) . '/inc/options/class-renderer.php';
require ABSPATH . 'wp-admin/includes/file.php';
$fields = require dirname( __DIR__ ) . '/inc/admin/sections/performance.php';
$field = array_values( array_filter( $fields, static function ( $item ) { return 'upload_max_file_size_mb' === $item['id']; } ) )[0];
$key = 'performance.upload_max_file_size_mb';
$file = array( 'name' => 'sample.pdf', 'size' => 6 * MB_IN_BYTES, 'error' => 0 );

check( wp_max_upload_size() === $server_limit, 'missing option keeps existing upload ceiling' );
check( az_validate_upload_file_size( $file ) === $file, 'disabled option leaves uploads unchanged' );
foreach ( array( 0, -2, '', 'bad', array(), INF, NAN ) as $invalid ) {
    $options[ $key ] = $invalid;
    check( az_upload_file_limit_bytes() === 0, 'zero/invalid saved value safely disables custom limit: ' . gettype( $invalid ) );
}
$options[ $key ] = 5;
check( wp_max_upload_size() === 5 * MB_IN_BYTES, 'Media ceiling reflects configured MB' );
foreach ( array( 5 * MB_IN_BYTES - 1, 5 * MB_IN_BYTES ) as $size ) {
    $allowed = array_replace( $file, array( 'size' => $size ) );
    check( az_validate_upload_file_size( $allowed ) === $allowed, 'allows file below or exactly at limit: ' . $size );
}
$oversized = array_replace( $file, array( 'size' => 5 * MB_IN_BYTES + 1 ) );
$rejected = apply_filters( 'wp_handle_upload_prefilter', $oversized );
check( is_string( $rejected['error'] ) && strpos( $rejected['error'], '5.00 MB' ) !== false, 'direct upload one byte over limit receives readable error' );
$result = wp_handle_upload( $oversized, array( 'test_form' => false ) );
check( ! empty( $result['error'] ) && strpos( $result['error'], '5.00 MB' ) !== false, 'core upload handler rejects oversized file before moving it' );
$existing_error = array_replace( $file, array( 'error' => 'Existing image validation error' ) );
check( az_validate_upload_file_size( $existing_error ) === $existing_error, 'preserves prior validation errors' );
$php_error = array_replace( $file, array( 'error' => UPLOAD_ERR_INI_SIZE ) );
check( az_validate_upload_file_size( $php_error ) === $php_error, 'preserves PHP upload errors' );

$server_limit = MB_IN_BYTES;
check( wp_max_upload_size() === MB_IN_BYTES, 'configured limit cannot raise server ceiling' );
check( ! empty( az_validate_upload_file_size( array_replace( $file, array( 'size' => MB_IN_BYTES + 1 ) ) )['error'] ), 'server ceiling also enforced by upload prefilter' );
add_filter( 'upload_size_limit', static function ( $size ) { return min( $size, MB_IN_BYTES / 2 ); }, 10 );
check( wp_max_upload_size() === MB_IN_BYTES / 2, 'preserves lower network/plugin ceiling' );
unset( $hooks['upload_size_limit'][10] );
$server_limit = 32 * MB_IN_BYTES;
$options[ $key ] = 0.5;
check( wp_max_upload_size() === MB_IN_BYTES / 2, 'supports fractional MB limits' );

$temporary = tempnam( sys_get_temp_dir(), 'az-upload-test-' );
try {
    file_put_contents( $temporary, str_repeat( 'x', MB_IN_BYTES / 2 + 1 ) );
    $raw = array( 'name' => 'sample.pdf', 'tmp_name' => $temporary, 'error' => null );
    $result = wp_handle_sideload( $raw, array( 'test_form' => false ) );
    check( ! empty( $result['error'] ) && strpos( $result['error'], '0.50 MB' ) !== false, 'core sideload handler rejects raw REST/import file with missing size' );
    $raw['size'] = 1;
    check( ! empty( az_validate_upload_file_size( $raw )['error'] ), 'actual temporary file size defeats underreported size' );
    check( is_file( $temporary ), 'validation does not delete temporary file' );
} finally {
    unlink( $temporary );
}

check( AZ_Options_Sanitizer::sanitize( '-5', $field['type'], $field ) === 0, 'save sanitization clamps negative limit to zero' );
check( AZ_Options_Sanitizer::sanitize( '0.5', $field['type'], $field ) === 0.5, 'save sanitization preserves fractional MB' );
check( AZ_Options_Sanitizer::sanitize( $field['default'], $field['type'], $field ) == 0, 'Reset Section default disables custom limit' );
ob_start();
AZ_Options_Renderer::field( 'performance', $field, array() );
$html = ob_get_clean();
check( strpos( $html, 'type="number"' ) !== false && strpos( $html, 'min="0"' ) !== false && strpos( $html, '>MB</span>' ) !== false, 'Performance renders numeric MB field with nonnegative minimum' );
ob_start();
AZ_Options_Renderer::field( 'performance', $field, array( 'performance' => array( 'upload_max_file_size_mb' => 0.5 ) ) );
$html = ob_get_clean();
check( strpos( $html, 'value="0.5"' ) !== false, 'Performance renders saved limit' );
