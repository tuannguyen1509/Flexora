<?php
/** Maps use General > Addresses exclusively, including iframe-only locations. */
defined( 'ABSPATH' ) || exit;

function az_get_google_maps() {
    $rows = az_get_option( 'general.addresses', array() );
    $maps = array();
    foreach ( is_array( $rows ) ? array_values( $rows ) : array() as $index => $row ) {
        if ( ! is_array( $row ) ) { continue; }
        $raw = $row['maps_embed'] ?? '';
        $embed = is_string( $raw ) ? AZ_Options_Sanitizer::sanitize( $raw, 'map_embed' ) : '';
        if ( ! $embed ) { continue; }
        $maps[] = array(
            'index' => $index + 1,
            'label' => sanitize_text_field( is_scalar( $row['label'] ?? null ) ? $row['label'] : '' ),
            'value' => sanitize_textarea_field( is_scalar( $row['value'] ?? null ) ? $row['value'] : '' ),
            'embed' => $embed,
        );
    }
    return $maps;
}

/** Compatibility shortcodes now read the address list rather than retired fields. */
function az_get_location_field( $field ) {
    if ( 'address' === $field || 'address_secondary' === $field ) {
        $items = az_get_contact_items( 'address' );
        return $items[ 'address_secondary' === $field ? 1 : 0 ]['value'] ?? '';
    }
    $rows = az_get_option( 'general.addresses', array() );
    foreach ( is_array( $rows ) ? $rows : array() as $row ) {
        if ( ! is_array( $row ) || ! is_string( $row['maps_url'] ?? null ) ) { continue; }
        $url = esc_url_raw( $row['maps_url'], array( 'https', 'http' ) );
        if ( $url ) { return $url; }
    }
    return '';
}

function az_google_map_assets() {
    if ( ! az_get_google_maps() ) { return; }
    $file = get_template_directory() . '/css/contact-branches.css';
    wp_enqueue_style( 'az-contact-branches', get_template_directory_uri() . '/css/contact-branches.css', array(), substr( hash_file( 'sha256', $file ), 0, 12 ) );
}
add_action( 'wp_enqueue_scripts', 'az_google_map_assets' );

/** [az_google_map] renders all maps; index="2" selects the second address row. */
function az_google_map_shortcode( $atts = array() ) {
    $atts = shortcode_atts( array( 'index' => '', 'title' => '' ), $atts, 'az_google_map' );
    $maps = az_get_google_maps();
    if ( '' !== (string) $atts['index'] ) {
        $maps = array_filter( $maps, static function( $map ) use ( $atts ) { return (string) $map['index'] === (string) $atts['index']; } );
    }
    if ( ! $maps ) { return ''; }
    $html = '<section class="az-contact-branches az-contact-map-full" aria-label="Bản đồ các chi nhánh">';
    if ( $atts['title'] ) { $html .= '<h2>' . esc_html( $atts['title'] ) . '</h2>'; }
    $html .= '<div class="az-contact-branches__grid' . ( 1 === count( $maps ) ? ' az-contact-branches__grid--single' : '' ) . '">';
    foreach ( $maps as $map ) {
        $html .= '<article class="az-contact-branch">';
        if ( $map['label'] || $map['value'] ) {
            $html .= '<div class="az-contact-branch__info">';
            if ( $map['label'] ) { $html .= '<h3>' . esc_html( $map['label'] ) . '</h3>'; }
            if ( $map['value'] ) { $html .= '<p>' . nl2br( esc_html( $map['value'] ) ) . '</p>'; }
            $html .= '</div>';
        }
        $html .= '<div class="az-contact-branch__map">' . $map['embed'] . '</div></article>';
    }
    return $html . '</div></section>';
}
add_shortcode( 'az_google_map', 'az_google_map_shortcode' );
