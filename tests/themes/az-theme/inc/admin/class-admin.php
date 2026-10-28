<?php
defined( 'ABSPATH' ) || exit;
class AZ_Admin {
    public function __construct() { add_action( 'admin_menu', array( $this, 'menu' ) ); }
    public function menu() {
        add_menu_page( 'AZ Theme', 'AZ Theme', 'manage_options', 'az-theme', array( 'AZ_Dashboard', 'render' ), 'dashicons-admin-customizer', 60 );
        add_submenu_page( 'az-theme', 'Dashboard', 'Dashboard', 'manage_options', 'az-theme', array( 'AZ_Dashboard', 'render' ) );
        add_submenu_page( 'az-theme', 'Advanced', 'Advanced', 'manage_options', 'az-theme-advanced', array( $GLOBALS['az_advanced_admin'], 'render' ) );
        // Keep the top-level menu intentionally small until these modules have real screens.
    }
}
