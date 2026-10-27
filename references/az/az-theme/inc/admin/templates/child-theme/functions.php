<?php
/**
 * Website-specific code for AZ Theme Child.
 *
 * Keep custom PHP for this website here. Updates to the parent theme will not
 * replace this file.
 */

defined( 'ABSPATH' ) || exit;

/** Preserve existing child settings; import parent defaults only once. */
if ( ! function_exists( 'az_child_migrate_parent_theme_mods' ) ) {
    function az_child_migrate_parent_theme_mods() {
        if ( 'az-theme-child' !== get_stylesheet() || get_option( 'az_child_parent_mods_migrated', false ) ) {
            return;
        }

        $parent_mods = get_option( 'theme_mods_az-theme', array() );
        $child_mods  = get_theme_mods();
        $child_mods  = is_array( $child_mods ) ? $child_mods : array();
        if ( is_array( $parent_mods ) ) {
            foreach ( $parent_mods as $name => $value ) {
                // Additional CSS belongs to a stylesheet-specific post below.
                if ( 'custom_css_post_id' !== $name && ! array_key_exists( $name, $child_mods ) ) {
                    set_theme_mod( $name, $value );
                }
            }
        }

        // Preserve the parent's Customizer CSS without linking to its post ID.
        $parent_css = wp_get_custom_css( 'az-theme' );
        if ( '' !== $parent_css && ! wp_get_custom_css_post( get_stylesheet() ) ) {
            $result = wp_update_custom_css_post( $parent_css, array( 'stylesheet' => get_stylesheet() ) );
            if ( is_wp_error( $result ) ) {
                return;
            }
        }

        update_option( 'az_child_parent_mods_migrated', 1, false );
    }
}
add_action( 'after_switch_theme', 'az_child_migrate_parent_theme_mods', 5 );

if ( ! function_exists( 'az_child_enqueue_assets' ) ) {
    function az_child_enqueue_assets() {
        $az_child_style = get_stylesheet_directory() . '/style.css';

        if ( file_exists( $az_child_style ) ) {
            wp_enqueue_style(
                'az-theme-child',
                get_stylesheet_uri(),
                array( 'az-container-sync' ),
                filemtime( $az_child_style )
            );
        }
    }
}
// Parent homepage/module styles are also enqueued at priority 20.
add_action( 'wp_enqueue_scripts', 'az_child_enqueue_assets', 100 );
