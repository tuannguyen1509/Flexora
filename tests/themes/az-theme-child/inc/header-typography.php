<?php
/** Menu bar sizing uses the shared Typography settings and CSS variables. */
defined( 'ABSPATH' ) || exit;

function az_child_register_menu_typography() {
    if ( ! is_admin() || ! class_exists( 'AZ_Options_Registry' ) ) { return; }
    $section = AZ_Options_Registry::get( 'typography' );
    if ( ! $section ) { return; }
    foreach ( $section['fields'] as &$field ) {
        if ( 'navigation' === ( $field['group'] ?? '' ) ) {
            $field['group_title'] = 'Thanh menu';
        }
        if ( 'navigation_size' === $field['id'] ) {
            $field['title'] = 'Cỡ chữ thanh menu';
            $field['description'] = 'Chọn cỡ chữ 12–24px cho menu chính, nút danh mục sản phẩm và menu sticky. Nhấn Save Changes để áp dụng. Menu tự xuống dòng khi không đủ chỗ.';
        }
    }
    unset( $field );
    AZ_Options_Registry::add_section( 'typography', $section['title'], $section['fields'] );
}
add_action( 'after_setup_theme', 'az_child_register_menu_typography', 20 );
