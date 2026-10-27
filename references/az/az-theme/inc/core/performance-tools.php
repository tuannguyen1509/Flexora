<?php
/** Optional frontend utilities, independent of the security master switch. */
defined( 'ABSPATH' ) || exit;

function az_should_redirect_404_home() {
    if ( ! az_get_option( 'performance.redirect_404_home', 0 ) || ! is_404() || is_admin() || wp_doing_ajax() || is_feed() || is_preview() || is_customize_preview() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
        return false;
    }
    if ( ! in_array( strtoupper( $_SERVER['REQUEST_METHOD'] ?? 'GET' ), array( 'GET', 'HEAD' ), true ) ) {
        return false;
    }
    $path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH );
    // Keep missing resources, REST routes and homepage requests as real 404s.
    if ( preg_match( '~\.[a-z0-9]{1,10}/?$~i', (string) $path ) || preg_match( '~/(?:wp-json|wp-admin|wp-content|wp-includes)(?:/|$)~i', (string) $path ) || isset( $_GET['rest_route'] ) || untrailingslashit( (string) $path ) === untrailingslashit( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) ) ) {
        return false;
    }
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if ( $accept && false === stripos( $accept, 'text/html' ) && false === strpos( $accept, '*/*' ) ) {
        return false;
    }
    return (bool) apply_filters( 'az_allow_404_home_redirect', true );
}
add_action( 'template_redirect', function () {
    if ( az_should_redirect_404_home() && ! headers_sent() ) {
        nocache_headers();
        if ( wp_safe_redirect( home_url( '/' ), 302, 'AZ Theme' ) ) {
            exit;
        }
    }
}, 20 );

add_action( 'wp_enqueue_scripts', function () {
    if ( ! az_get_option( 'performance.content_protection', 0 ) || is_user_logged_in() || is_preview() || is_customize_preview() ) {
        return;
    }
    wp_enqueue_script( 'az-content-protection', get_template_directory_uri() . '/js/content-protection.js', array(), filemtime( get_template_directory() . '/js/content-protection.js' ), true );
} );
