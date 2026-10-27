<?php
/** Shared homepage metadata inherited by all AZ child themes. */
defined( 'ABSPATH' ) || exit;

/** Child themes may extend the context for their own homepage templates. */
function az_home_meta_description_context() {
    return ! is_admin() && (bool) apply_filters( 'az_home_meta_description_context', is_front_page() );
}

function az_get_home_meta_description() {
    $saved = az_get_option( 'branding.home_meta_description', '' );
    $description = is_scalar( $saved ) ? trim( wp_strip_all_tags( (string) $saved ) ) : '';
    if ( '' === $description ) {
        $description = wp_strip_all_tags( (string) get_bloginfo( 'description', 'display' ) );
    }
    $description = html_entity_decode( $description, ENT_QUOTES, 'UTF-8' );
    return trim( preg_replace( '/\s+/u', ' ', $description ) ?? '' );
}

function az_filter_home_meta_description( $description ) {
    return az_home_meta_description_context() ? az_get_home_meta_description() : $description;
}
// SEO plugins own the tag when active; only replace their homepage description.
add_filter( 'rank_math/frontend/description', 'az_filter_home_meta_description', 99 );
add_filter( 'wpseo_metadesc', 'az_filter_home_meta_description', 99 );

function az_render_home_meta_description() {
    if ( ! az_home_meta_description_context() || defined( 'RANK_MATH_VERSION' ) || defined( 'WPSEO_VERSION' ) ) { return; }
    $description = az_get_home_meta_description();
    if ( '' !== $description ) {
        printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
    }
}
add_action( 'wp_head', 'az_render_home_meta_description', 5 );
