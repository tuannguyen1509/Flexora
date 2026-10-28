<?php
/**
 * Keep WooCommerce appearance settings in AZ Theme Advanced.
 *
 * @package AZ_Theme_Child
 */

defined( 'ABSPATH' ) || exit;

/** Remove the plugin's Customizer registration and its companion assets. */
function az_child_remove_woocommerce_customizer() {
    global $wp_filter;

    if ( ! class_exists( 'WC_Shop_Customizer', false ) ) {
        return;
    }

    // WooCommerce does not expose its Customizer instance. Find only callbacks
    // belonging to that class; preserve menu endpoints and storefront hooks.
    $hooks = array(
        'customize_register',
        'customize_controls_print_styles',
        'customize_controls_print_scripts',
        'customize_controls_enqueue_scripts',
        'wp_enqueue_scripts',
    );

    foreach ( $hooks as $hook ) {
        if ( ! isset( $wp_filter[ $hook ] ) ) {
            continue;
        }

        foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
            foreach ( $callbacks as $callback ) {
                $handler = $callback['function'];
                if ( is_array( $handler ) && $handler[0] instanceof WC_Shop_Customizer ) {
                    remove_action( $hook, $handler, $priority );
                }
            }
        }
    }
}
add_action( 'customize_register', 'az_child_remove_woocommerce_customizer', 0 );
