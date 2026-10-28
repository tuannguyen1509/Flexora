<?php
/**
 * Shared footer settings used by the parent fallback and child footers.
 *
 * @package AZ_AI_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'az_footer_default_options' ) ) {
    function az_footer_default_options() {
        return array(
            'show_top_links' => 1,
            'show_social'    => 1,
            'show_copyright' => 1,
        );
    }
}

if ( ! function_exists( 'az_footer_options' ) ) {
    function az_footer_options() {
        $az_options = az_get_options();
        $az_saved   = isset( $az_options['footer'] ) && is_array( $az_options['footer'] ) ? $az_options['footer'] : array();

        return wp_parse_args( $az_saved, az_footer_default_options() );
    }
}
