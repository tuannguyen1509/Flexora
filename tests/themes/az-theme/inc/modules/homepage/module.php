<?php
/** Homepage framework with legacy defaults. */
defined( 'ABSPATH' ) || exit;

function az_homepage_definitions() {
    return array(
        'hero' => array( 'label' => 'Hero / banner', 'title' => '', 'enabled' => true, 'order' => 10, 'limit' => 0, 'columns' => 0 ),
        'featured-categories' => array( 'label' => 'Danh mục nổi bật', 'title' => 'Danh mục nổi bật', 'enabled' => false, 'order' => 15, 'limit' => 8, 'columns' => 4, 'woo' => true ),
        'weekly' => array( 'label' => 'Ưu đãi nổi bật', 'title' => 'Sản phẩm ưu đãi nổi bật', 'enabled' => true, 'order' => 20, 'limit' => 6, 'columns' => 0, 'woo' => true ),
        'wide-banners' => array( 'label' => 'Banner ngang', 'title' => '', 'enabled' => true, 'order' => 30, 'limit' => 0, 'columns' => 0 ),
        'category-products' => array( 'label' => 'Sản phẩm theo danh mục', 'title' => '', 'enabled' => true, 'order' => 40, 'limit' => 4, 'columns' => 0, 'woo' => true ),
        'suggested' => array( 'label' => 'Sản phẩm gợi ý', 'title' => 'Có thể bạn thích:', 'enabled' => true, 'order' => 50, 'limit' => 5, 'columns' => 0, 'woo' => true ),
        'news' => array( 'label' => 'Tin tức hiện có', 'title' => 'Tin tức - bài viết', 'enabled' => true, 'order' => 60, 'limit' => 4, 'columns' => 0 ),
        'services' => array( 'label' => 'Cam kết dịch vụ hiện có', 'title' => '', 'enabled' => true, 'order' => 65, 'limit' => 0, 'columns' => 0 ),
        'custom-content' => array( 'label' => 'Nội dung tùy chỉnh', 'title' => '', 'enabled' => false, 'order' => 70, 'limit' => 0, 'columns' => 0 ),
    );
}

function az_homepage_options() {
    // Single storage adapter until the shared options helper is implemented.
    $options = function_exists( 'az_get_options' ) ? az_get_options() : get_option( 'az_theme_options', array() );
    return is_array( $options ) ? $options : array();
}

function az_homepage_normalize( $input, $defaults ) {
    if ( ! is_array( $input ) ) { return $defaults; }
    foreach ( array( 'enabled', 'title', 'limit', 'columns', 'order' ) as $field ) {
        if ( ! array_key_exists( $field, $input ) || ! is_scalar( $input[ $field ] ) ) { continue; }
        $value = $input[ $field ];
        if ( 'enabled' === $field ) {
            if ( in_array( $value, array( true, false, 0, 1, '0', '1' ), true ) ) { $defaults[ $field ] = in_array( $value, array( true, 1, '1' ), true ); }
        } elseif ( 'title' === $field ) {
            $defaults[ $field ] = sanitize_text_field( $value );
        } elseif ( is_numeric( $value ) ) {
            $min = 'limit' === $field ? 1 : 0;
            $max = 'columns' === $field ? 6 : ( 'order' === $field ? 999 : 30 );
            $defaults[ $field ] = max( $min, min( $max, (int) $value ) );
        }
    }
    return $defaults;
}

function az_homepage_settings() {
    static $settings;
    if ( null !== $settings ) { return $settings; }
    $settings = az_homepage_definitions();
    foreach ( array( 'weekly', 'suggested' ) as $id ) {
        $legacy = array(
            'enabled' => get_option( 'az_home_' . $id . '_enabled', '1' ),
            'title' => get_option( 'az_home_' . $id . '_title', $settings[ $id ]['title'] ) ?: $settings[ $id ]['title'],
            'limit' => max( 1, min( 18, (int) get_option( 'az_home_' . $id . '_count', $settings[ $id ]['limit'] ) ) ),
        );
        $settings[ $id ] = az_homepage_normalize( $legacy, $settings[ $id ] );
    }
    $settings['category-products']['limit'] = max( 1, min( 30, (int) get_option( 'az_home_products_per_category', 4 ) ) );
    $options = az_homepage_options();
    $stored = $options['homepage']['sections'] ?? array();
    if ( ! is_array( $stored ) ) { $stored = array(); }
    foreach ( $settings as $id => $defaults ) {
        $settings[ $id ] = az_homepage_normalize( $stored[ $id ] ?? array(), $defaults );
    }
    return $settings;
}

function az_homepage_enabled( $id ) {
    $settings = az_homepage_settings();
    if ( empty( $settings[ $id ]['enabled'] ) ) { return false; }
    return empty( $settings[ $id ]['woo'] ) || ( class_exists( 'WooCommerce' ) && post_type_exists( 'product' ) && taxonomy_exists( 'product_cat' ) );
}

function az_homepage_ordered_sections() {
    $settings = az_homepage_settings();
    $ids = array_keys( $settings );
    $positions = array_flip( $ids );
    usort( $ids, function( $a, $b ) use ( $settings, $positions ) {
        $comparison = $settings[ $a ]['order'] <=> $settings[ $b ]['order'];
        return $comparison ?: $positions[ $a ] <=> $positions[ $b ];
    } );
    return array_values( array_filter( $ids, 'az_homepage_enabled' ) );
}

function az_homepage_assets() {
    if ( ! is_front_page() && ! is_page_template( 'front-page.php' ) ) { return; }
    $reference_css = get_theme_file_path( 'css/home-reference.css' );
    $reference_php = locate_template( 'template-parts/home-reference.php', false, false );
    if ( file_exists( $reference_php ) && file_exists( $reference_css ) ) {
        $reference_dependencies = wp_style_is( 'az-piano-header', 'registered' ) ? array( 'az-piano-header' ) : array( 'az-container-sync' );
        if ( wp_style_is( 'az-product-card', 'registered' ) ) {
            $reference_dependencies[] = 'az-product-card';
        }
        wp_enqueue_style( 'az-home-reference', get_theme_file_uri( 'css/home-reference.css' ), $reference_dependencies, filemtime( $reference_css ) );
        $reference_js = get_theme_file_path( 'js/home-reference.js' );
        if ( file_exists( $reference_js ) ) {
            wp_enqueue_script( 'az-home-reference', get_theme_file_uri( 'js/home-reference.js' ), array(), filemtime( $reference_js ), true );
        }
        return;
    }
    if ( ! az_homepage_ordered_sections() ) { return; }
    $base = '/inc/modules/homepage/';
    wp_enqueue_style( 'az-homepage', get_template_directory_uri() . $base . 'homepage.css', array( 'az-ai-theme-style' ), filemtime( get_template_directory() . $base . 'homepage.css' ) );
    if ( az_homepage_enabled( 'hero' ) || az_homepage_enabled( 'category-products' ) ) {
        wp_enqueue_script( 'az-homepage', get_template_directory_uri() . $base . 'homepage.js', array(), filemtime( get_template_directory() . $base . 'homepage.js' ), true );
    }
}
add_action( 'wp_enqueue_scripts', 'az_homepage_assets', 20 );
if ( is_admin() ) { require_once __DIR__ . '/admin.php'; }
$az_homepage_page_fields = locate_template( 'inc/modules/homepage/page-fields.php', false, false );
if ( $az_homepage_page_fields ) { require_once $az_homepage_page_fields; }
