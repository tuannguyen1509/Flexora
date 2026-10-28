<?php
defined( 'ABSPATH' ) || exit;
class AZ_Admin_Assets {
    public function __construct() { add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) ); }
    public function enqueue( $hook ) {
        $screen = get_current_screen();
        $page = sanitize_key( wp_unslash( $_GET['page'] ?? '' ) );
        if ( ! $screen || ! in_array( $page, array( 'az-theme', 'az-theme-advanced' ), true ) ) { return; }
        $base = get_template_directory();
        wp_enqueue_style( 'az-theme-admin', get_template_directory_uri() . '/assets/admin/css/az-admin.css', array(), filemtime( $base . '/assets/admin/css/az-admin.css' ) );
        wp_enqueue_style( 'az-admin-colors', get_template_directory_uri() . '/assets/admin/css/az-admin-colors.css', array( 'az-theme-admin' ), filemtime( $base . '/assets/admin/css/az-admin-colors.css' ) );
        wp_enqueue_style( 'az-admin-notices', get_template_directory_uri() . '/assets/admin/css/az-notices.css', array( 'az-theme-admin' ), filemtime( $base . '/assets/admin/css/az-notices.css' ) );
        wp_enqueue_style( 'az-switch-layout', get_template_directory_uri() . '/assets/admin/css/az-switch-layout.css', array( 'az-admin-notices' ), filemtime( $base . '/assets/admin/css/az-switch-layout.css' ) );
        wp_enqueue_style( 'az-media-actions', get_template_directory_uri() . '/assets/admin/css/az-media-actions.css', array( 'az-switch-layout' ), filemtime( $base . '/assets/admin/css/az-media-actions.css' ) );
        wp_enqueue_style( 'az-header-actions', get_template_directory_uri() . '/assets/admin/css/az-header-actions.css', array( 'az-media-actions' ), filemtime( $base . '/assets/admin/css/az-header-actions.css' ) );
        wp_enqueue_style( 'az-dashboard-admin', get_template_directory_uri() . '/assets/admin/css/az-dashboard.css', array( 'az-theme-admin' ), filemtime( $base . '/assets/admin/css/az-dashboard.css' ) );
        wp_enqueue_style( 'az-analytics-admin', get_template_directory_uri() . '/assets/admin/css/az-analytics.css', array( 'az-theme-admin' ), filemtime( $base . '/assets/admin/css/az-analytics.css' ) );
        wp_enqueue_style( 'az-advanced-fields', get_template_directory_uri() . '/assets/admin/css/az-advanced-fields.css', array( 'az-theme-admin' ), filemtime( $base . '/assets/admin/css/az-advanced-fields.css' ) );
        wp_enqueue_style( 'az-performance-admin', get_template_directory_uri() . '/assets/admin/css/az-performance.css', array( 'az-advanced-fields', 'az-switch-layout' ), filemtime( $base . '/assets/admin/css/az-performance.css' ) );
        wp_enqueue_style( 'az-layout-fields', get_template_directory_uri() . '/assets/admin/css/az-layout-fields.css', array( 'az-theme-admin' ), filemtime( $base . '/assets/admin/css/az-layout-fields.css' ) );
        wp_enqueue_style( 'az-general-fields', get_template_directory_uri() . '/assets/admin/css/az-general-fields.css', array( 'az-layout-fields', 'az-advanced-fields' ), filemtime( $base . '/assets/admin/css/az-general-fields.css' ) );
        wp_enqueue_style( 'az-toggle-titles', get_template_directory_uri() . '/assets/admin/css/az-toggle-titles.css', array( 'az-general-fields', 'az-performance-admin' ), filemtime( $base . '/assets/admin/css/az-toggle-titles.css' ) );
        if ( 'az-theme-advanced' !== $page ) { return; }
        wp_enqueue_script( 'az-theme-admin', get_template_directory_uri() . '/assets/admin/js/az-advanced.js', array( 'jquery', 'wp-color-picker' ), az_get_theme_version(), true );
        wp_enqueue_script( 'az-advanced-tabs', get_template_directory_uri() . '/assets/admin/js/az-advanced-tabs.js', array( 'az-theme-admin' ), filemtime( $base . '/assets/admin/js/az-advanced-tabs.js' ), true );
        wp_enqueue_script( 'az-repeater-sort', get_template_directory_uri() . '/assets/admin/js/az-repeater-sort.js', array( 'az-advanced-tabs' ), filemtime( $base . '/assets/admin/js/az-repeater-sort.js' ), true );
        wp_enqueue_script( 'az-reset-confirm', get_template_directory_uri() . '/assets/admin/js/az-reset-confirm.js', array( 'az-repeater-sort' ), filemtime( $base . '/assets/admin/js/az-reset-confirm.js' ), true );
        wp_enqueue_media(); wp_enqueue_style( 'wp-color-picker' ); wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'az-user-status', get_template_directory_uri() . '/assets/admin/js/az-user-status.js', array( 'az-advanced-tabs' ), filemtime( $base . '/assets/admin/js/az-user-status.js' ), true );
    }
}
new AZ_Admin_Assets();
