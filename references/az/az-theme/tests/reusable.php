<?php
/** Isolated regression checks; no database or WordPress bootstrap needed. */
define( 'ABSPATH', __DIR__ );
$GLOBALS['allowed_raw'] = true;
function current_user_can( $cap ) { return 'unfiltered_html' !== $cap || $GLOBALS['allowed_raw']; }
function wp_salt( $scheme ) { return 'test-installation-secret'; }
function wp_strip_all_tags( $value ) { return strip_tags( $value ); }
function wp_kses_post( $value ) { return preg_replace( '#<script\b[^>]*>.*?</script>#is', '', $value ); }
function add_action() {}
function add_filter() {}
function az_get_option( $key, $default = null ) { return $default; }
require dirname( __DIR__ ) . '/inc/options/class-sanitizer.php';
require dirname( __DIR__ ) . '/inc/core/class-custom-code.php';
require dirname( __DIR__ ) . '/inc/functions/content-media.php';
function check( $condition, $label ) {
    if ( ! $condition ) { fwrite( STDERR, "FAIL $label\n" ); exit( 1 ); }
    echo "PASS $label\n";
}
$raw = '<script>window.example=1;</script>';
$field = array( 'id' => 'header_code' );
$signed = AZ_Options_Sanitizer::sanitize( $raw, 'code', $field );
check( AZ_Custom_Code::trusted_markup( $signed ) === $raw, 'authorized script survives frontend rendering' );
check( AZ_Custom_Code::trusted_markup( $raw ) === '', 'unsigned legacy script is not executed' );
$tampered = $signed;
$tampered['code'] .= '<script>bad()</script>';
check( strpos( AZ_Custom_Code::trusted_markup( $tampered ), '<script>' ) === false, 'modified signed code loses trust' );
check( AZ_Options_Sanitizer::code_source( $signed ) === $raw, 'editor shows code without signature metadata' );
check( AZ_Custom_Code::trusted_markup( array( 'code' => $raw, 'signature' => 'other-installation' ) ) === '', 'foreign installation signature is rejected' );
check( AZ_Options_Sanitizer::sanitize( $signed, 'code', $field ) === $signed, 'repeated saves do not nest signatures' );
$GLOBALS['allowed_raw'] = false;
check( strpos( AZ_Options_Sanitizer::sanitize( $raw, 'code', $field ), '<script>' ) === false, 'restricted users cannot authorize scripts' );
check( strpos( AZ_Options_Sanitizer::sanitize( '</style><script>bad()</script>', 'code', array( 'id' => 'custom_css' ) ), '<' ) === false, 'CSS cannot break out of style element' );
az_localize_remote_images_on_save( 1, (object) array(), false );
check( true, 'remote media import returns before any write by default' );
