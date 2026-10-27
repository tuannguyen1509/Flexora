<?php
/**
 * Website-specific assets for AZ Theme Child.
 *
 * @package AZ_Theme_Child
 */

defined( 'ABSPATH' ) || exit;

require_once get_stylesheet_directory() . '/inc/steel-storefront.php';
require_once get_stylesheet_directory() . '/inc/floating-contact.php';
require_once get_stylesheet_directory() . '/inc/home-storefront.php';
require_once get_stylesheet_directory() . '/inc/home-partners.php';
require_once get_stylesheet_directory() . '/inc/soviban-home.php';
require_once get_stylesheet_directory() . '/inc/footer-storefront.php';
require_once get_stylesheet_directory() . '/inc/header-storefront.php';
require_once get_stylesheet_directory() . '/inc/header-typography.php';
require_once get_stylesheet_directory() . '/inc/header-mega-menu.php';
require_once get_stylesheet_directory() . '/inc/header-colors.php';
require_once get_stylesheet_directory() . '/inc/product-display.php';
require_once get_stylesheet_directory() . '/inc/product-permalinks.php';
require_once get_stylesheet_directory() . '/inc/product-admin.php';
require_once get_stylesheet_directory() . '/inc/branding-seo.php';
require_once get_stylesheet_directory() . '/inc/customizer.php';

/**
 * Copy parent theme mods once so activation does not unset logo/menu locations.
 */
function az_child_migrate_parent_theme_mods() {
    if ( get_option( 'az_child_parent_mods_migrated', false ) ) { return; }

    $parent_mods = get_option( 'theme_mods_az-theme', array() );
    $child_mods  = get_theme_mods();
    $child_mods  = is_array( $child_mods ) ? $child_mods : array();

    if ( is_array( $parent_mods ) ) {
        foreach ( $parent_mods as $name => $value ) {
            if ( ! array_key_exists( $name, $child_mods ) ) {
                set_theme_mod( $name, $value );
            }
        }
    }

    update_option( 'az_child_parent_mods_migrated', 1, false );
}
add_action( 'after_switch_theme', 'az_child_migrate_parent_theme_mods' );

function az_child_enqueue_assets() {
    $child_style = get_stylesheet_directory() . '/style.css';
    wp_enqueue_style(
        'az-theme-child',
        get_stylesheet_uri(),
        array( 'az-container-sync' ),
        filemtime( $child_style )
    );

}
add_action( 'wp_enqueue_scripts', 'az_child_enqueue_assets', 15 );

/** Shared storefront assets; prevent the parent's alternate homepage assets. */
function az_child_steel_assets() {
    if ( az_child_is_steel_home() ) {
        wp_dequeue_style( 'az-homepage' );
        wp_dequeue_script( 'az-homepage' );
    }
    $base = get_stylesheet_directory();
    wp_enqueue_style( 'az-steel-storefront', get_stylesheet_directory_uri() . '/css/steel-storefront.css', array( 'az-theme-child', 'az-container-sync' ), filemtime( $base . '/css/steel-storefront.css' ) );
    wp_enqueue_script( 'az-steel-storefront', get_stylesheet_directory_uri() . '/js/steel-storefront.js', array(), filemtime( $base . '/js/steel-storefront.js' ), true );
    wp_enqueue_style( 'az-header-storefront', get_stylesheet_directory_uri() . '/css/header-storefront.css', array( 'az-steel-storefront' ), filemtime( $base . '/css/header-storefront.css' ) );
    if ( az_child_is_steel_home() ) {
        wp_enqueue_style( 'az-home-storefront', get_stylesheet_directory_uri() . '/css/home-storefront.css', array( 'az-steel-storefront' ), filemtime( $base . '/css/home-storefront.css' ) );
    }
    wp_enqueue_style( 'az-footer-storefront', get_stylesheet_directory_uri() . '/css/footer-storefront.css', array( 'az-steel-storefront' ), filemtime( $base . '/css/footer-storefront.css' ) );
}
add_action( 'wp_enqueue_scripts', 'az_child_steel_assets', 30 );

/** Catalogue sidebar overrides follow the parent WooCommerce stylesheet. */
function az_child_catalog_sidebar_assets() {
    if ( ! function_exists( 'is_shop' ) || ! ( is_shop() || is_product_taxonomy() ) ) { return; }
    $dependencies = array( 'az-theme-child' );
    if ( wp_style_is( 'az-woocommerce', 'enqueued' ) ) { $dependencies[] = 'az-woocommerce'; }
    wp_enqueue_style( 'az-catalog-sidebar', get_stylesheet_directory_uri() . '/css/catalog-sidebar.css', $dependencies, filemtime( get_stylesheet_directory() . '/css/catalog-sidebar.css' ) );
}
add_action( 'wp_enqueue_scripts', 'az_child_catalog_sidebar_assets', 35 );

/** Compact cart and checkout controls, loaded after WooCommerce styles. */
function az_child_commerce_button_assets() {
    if ( ! function_exists( 'is_cart' ) || ! ( is_cart() || is_checkout() ) ) { return; }
    $dependencies = array( 'az-theme-child' );
    if ( wp_style_is( 'az-woocommerce', 'enqueued' ) ) { $dependencies[] = 'az-woocommerce'; }
    $path = get_stylesheet_directory() . '/css/commerce-buttons.css';
    wp_enqueue_style( 'az-commerce-buttons', get_stylesheet_directory_uri() . '/css/commerce-buttons.css', $dependencies, filemtime( $path ) );
}
add_action( 'wp_enqueue_scripts', 'az_child_commerce_button_assets', 35 );

// Xóa menu bar admin
        function remove_admin_bar_links() {
          global $wp_admin_bar;
          $wp_admin_bar->remove_menu('wp-logo');          // Remove the WordPress logo
          $wp_admin_bar->remove_menu('flatsome_panel');   // Remove the about WordPress link
          $wp_admin_bar->remove_menu('wp-logo');          // Remove the WordPress logo
          $wp_admin_bar->remove_menu('about');            // Remove the about WordPress link
          $wp_admin_bar->remove_menu('updates');          // Remove the updates link
          $wp_admin_bar->remove_menu('comments');         // Remove the comments link
          $wp_admin_bar->remove_menu('wpeditor_admin');   // Remove the comments link
          $wp_admin_bar->remove_menu('wpseo-menu');
          $wp_admin_bar->remove_menu('new-content');
        }
        add_action( 'wp_before_admin_bar_render', 'remove_admin_bar_links' );
    // Ẩn tin tức wordpress
    function remove_wp_dashboard_widgets() {
        remove_meta_box('dashboard_primary', 'dashboard', 'side'); // Tin tức WordPress
        remove_meta_box('dashboard_secondary', 'dashboard', 'side'); // Sự kiện và Tin tức
    }
    add_action('wp_dashboard_setup', 'remove_wp_dashboard_widgets');
    // Ẩn xin chào
    function remove_welcome_panel() {
        remove_action('welcome_panel', 'wp_welcome_panel');
    }
    add_action('admin_init', 'remove_welcome_panel');

// Xoá tuỳ chỉnh admin
        function hk_remove_dashboard_widgets() {

        global $wp_meta_boxes;

        remove_meta_box( 'dashboard_primary','dashboard','side' ); // WordPress.com Blog
        remove_meta_box( 'dashboard_plugins','dashboard','normal' ); // Plugins
        remove_meta_box( 'dashboard_right_now','dashboard', 'normal' ); // Tin nhanh
        remove_action( 'welcome_panel','wp_welcome_panel' ); // Welcome Panel
        remove_action( 'try_gutenberg_panel', 'wp_try_gutenberg_panel'); // Giới thiệu Gutenberg
        remove_meta_box('dashboard_quick_press','dashboard','side'); // Bản nháp
        remove_meta_box('dashboard_recent_drafts','dashboard','side'); // Bản nháp gần đây
        remove_meta_box('dashboard_secondary','dashboard','side'); // WordPress News
        remove_meta_box('dashboard_recent_comments','dashboard','normal'); // Bình luận
        remove_meta_box('dashboard_activity','dashboard', 'normal'); // Hoạt động
        unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_site_health'] ); // Tình trạng website
        }
        add_action( 'wp_dashboard_setup', 'hk_remove_dashboard_widgets' );
        
        //Ẩn Welcome Panel:
        add_action( 'load-index.php', 'hide_welcome_panel' );

        function hide_welcome_panel() {
                $user_id = get_current_user_id();

                if ( 1 == get_user_meta( $user_id, 'show_welcome_panel', true ) )
                        update_user_meta( $user_id, 'show_welcome_panel', 0 );
        }
