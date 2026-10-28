<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'send_headers', function() {
    if ( is_admin() || headers_sent() || ! az_get_option( 'general.security_headers', 1 ) ) {
        return;
    }

    header_remove( 'X-Powered-By' );
    $headers = apply_filters( 'az_security_headers', array(
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
    ) );
    foreach ( $headers as $name => $value ) {
        if ( is_string( $value ) && preg_match( '/^[A-Za-z0-9-]+$/', $name ) && false === strpbrk( $value, "\r\n" ) ) {
            header( $name . ': ' . $value );
        }
    }
} );
add_filter( 'login_errors', function( $error ) {
    return az_get_option( 'general.generic_login_errors', 1 ) ? __( 'Thông tin đăng nhập không chính xác.', 'az-ai-theme' ) : $error;
} );
