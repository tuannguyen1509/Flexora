<?php
/**
 * AZ AI Theme Functions
 *
 * @package AZ_AI_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once get_template_directory() . '/inc/bootstrap.php';

/** Hide only the navigation entry, including when the standalone plugin is active. */
add_action( 'admin_menu', function() {
    if ( ! az_get_option( 'general.show_leads_menu', 1 ) ) {
        remove_menu_page( 'az9s-leads' );
    }
}, 999 );

// Plugins load before themes. Reuse AZ9S Leads when active to avoid duplicate hooks.
if ( ! defined( 'AZ9S_LEADS_VERSION' ) && ! function_exists( 'az9s_create_leads_table' ) ) {
    require_once get_template_directory() . '/inc/modules/leads/module.php';
}
