<?php
/**
 * Classic widget areas retained for content migrated from Flatsome.
 *
 * @package AZ_Theme
 */
defined( 'ABSPATH' ) || exit;

/** Keep the familiar Appearance > Widgets screen for legacy widget data. */
add_filter( 'use_widgets_block_editor', '__return_false' );

function az_flatsome_compat_register_widget_areas() {
    global $wp_registered_sidebars;

    if ( function_exists( 'az_flatsome_compat_enabled' ) && ! az_flatsome_compat_enabled() ) { return; }

    $areas = array(
        'shop-sidebar'     => array( 'name' => 'Danh mục sản phẩm', 'description' => 'Dùng bởi shortcode [ux_sidebar id="shop-sidebar"].' ),
        'sidebar-main'     => array( 'name' => 'Thanh bên', 'description' => 'Vùng sidebar chính tương thích dữ liệu giao diện cũ.' ),
        'sidebar-shop'     => array( 'name' => 'Thanh bên cửa hàng', 'description' => 'Vùng widget danh mục và cửa hàng WooCommerce cũ.' ),
        'sidebar-product'  => array( 'name' => 'Thanh bên sản phẩm', 'description' => 'Vùng widget trang chi tiết sản phẩm cũ.' ),
        'sidebar-footer-1' => array( 'name' => 'Chân trang 1', 'description' => 'Cột widget chân trang thứ nhất.' ),
        'sidebar-footer-2' => array( 'name' => 'Chân trang 2', 'description' => 'Cột widget chân trang thứ hai.' ),
    );

    foreach ( $areas as $id => $area ) {
        if ( isset( $wp_registered_sidebars[ $id ] ) ) { continue; }
        register_sidebar(
            array(
                'name'          => $area['name'],
                'id'            => $id,
                'description'   => $area['description'],
                'before_widget' => '<section id="%1$s" class="az-legacy-widget widget %2$s">',
                'after_widget'  => '</section>',
                'before_title'  => '<h2 class="az-legacy-widget__title widget-title">',
                'after_title'   => '</h2>',
            )
        );
    }
}
add_action( 'widgets_init', 'az_flatsome_compat_register_widget_areas', 20 );
