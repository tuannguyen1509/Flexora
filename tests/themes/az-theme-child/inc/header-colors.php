<?php
/** Child-owned menu color controls in Theme Advanced > Colors. */
defined( 'ABSPATH' ) || exit;

function az_child_menu_color_definitions() {
    return array(
        'menu_background' => array( 'Màu nền thanh menu', '#ef1429' ),
        'menu_text' => array( 'Màu chữ menu', '#ffffff' ),
        'menu_hover_background' => array( 'Màu nền menu khi rê chuột', '#cf1023' ),
        'menu_hover_text' => array( 'Màu chữ menu khi rê chuột', '#ffffff' ),
        'menu_active_background' => array( 'Màu nền mục đang chọn', '#ef1429' ),
        'menu_active_text' => array( 'Màu chữ mục đang chọn', '#ffffff' ),
        'menu_category_background' => array( 'Màu nền nút danh mục', '#cf1023' ),
        'menu_category_text' => array( 'Màu chữ nút danh mục', '#ffffff' ),
        'menu_category_hover_background' => array( 'Màu nền nút danh mục khi rê chuột', '#b30d1e' ),
        'menu_category_hover_text' => array( 'Màu chữ nút danh mục khi rê chuột', '#ffffff' ),
    );
}

function az_child_register_menu_colors() {
    if ( ! is_admin() || ! class_exists( 'AZ_Options_Registry' ) ) { return; }
    $section = AZ_Options_Registry::get( 'colors' );
    if ( ! $section ) { return; }
    $ids = array_column( $section['fields'], 'id' );
    foreach ( az_child_menu_color_definitions() as $id => $definition ) {
        if ( in_array( $id, $ids, true ) ) { continue; }
        AZ_Options_Registry::add_field( 'colors', array(
            'id' => $id,
            'type' => 'color',
            'title' => $definition[0],
            'default' => $definition[1],
            'group' => 'menu_bar',
            'group_title' => 'Màu thanh menu',
            'description' => 'Áp dụng cho thanh menu dùng chung trên toàn website và menu sticky. Để trống để dùng màu mặc định.',
            'width' => 'half',
        ) );
    }
}
add_action( 'after_setup_theme', 'az_child_register_menu_colors', 20 );

function az_child_menu_colors_css() {
    $css = ':root{';
    foreach ( az_child_menu_color_definitions() as $key => $definition ) {
        $saved = az_get_option( 'colors.' . $key, '' );
        $color = is_string( $saved ) ? sanitize_hex_color( $saved ) : '';
        $css .= '--az-' . str_replace( '_', '-', $key ) . ':' . ( $color ?: $definition[1] ) . ';';
    }
    return $css . '}';
}

add_action( 'wp_enqueue_scripts', function() {
    wp_add_inline_style( 'az-header-storefront', az_child_menu_colors_css() );
}, 40 );
