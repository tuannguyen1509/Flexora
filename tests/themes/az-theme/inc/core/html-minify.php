<?php
/** Conservative HTML whitespace reduction, not JS/CSS or transport compression. */
defined( 'ABSPATH' ) || exit;
function az_minify_html_output( $html ) {
    if ( ! is_string( $html ) || ! preg_match( '/^\s*(?:<!doctype html[^>]*>\s*)?<html\b/i', $html ) ) { return $html; }
    foreach ( headers_list() as $header ) {
        if ( stripos( $header, 'Content-Type:' ) === 0 && stripos( $header, 'text/html' ) === false ) { return $html; }
        if ( stripos( $header, 'Content-Disposition:' ) === 0 || stripos( $header, 'Content-Length:' ) === 0 || stripos( $header, 'Content-Encoding:' ) === 0 ) { return $html; }
    }
    // Preserve comments (including block/conditional markers) and raw-text regions.
    $parts = preg_split( '~(<!--.*?-->|<(?:script|style|pre|textarea|code|svg|math)\b[^>]*>.*?</(?:script|style|pre|textarea|code|svg|math)\s*>)~is', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
    if ( false === $parts ) { return $html; }
    foreach ( $parts as $index => $part ) {
        if ( 0 === $index % 2 ) {
            // A space is retained so adjacent inline elements never join words.
            $parts[ $index ] = preg_replace( '/>[\t\r\n ]+</', '> <', $part );
        }
    }
    return implode( '', $parts );
}
add_action( 'template_redirect', function() {
    if ( ! az_get_option( 'performance.minify_html', 0 ) || is_admin() || wp_doing_ajax() || is_feed() || is_preview() || is_customize_preview() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) { return; }
    if ( ! apply_filters( 'az_allow_html_minification', true ) ) { return; }
    ob_start( 'az_minify_html_output' );
}, 99 );
