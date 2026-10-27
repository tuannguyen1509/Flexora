<?php
$az_header_menus = array( 0 => '— Dùng vị trí menu WordPress mặc định —' );
foreach ( wp_get_nav_menus() as $az_header_menu ) {
    $az_header_menus[ $az_header_menu->term_id ] = $az_header_menu->name;
}

return array(
    array( 'id' => 'mobile_menu_enabled', 'type' => 'switch', 'title' => 'Hiển thị menu Mobile', 'default' => 1, 'group' => 'mobile_menu', 'group_title' => 'Menu Mobile' ),
    array( 'id' => 'mobile_menu_style', 'type' => 'select', 'title' => 'Kiểu menu Mobile', 'description' => 'Kiểu 1 có logo và menu trượt phải gọn gàng. Kiểu 2 có logo, tông nâu/kem và menu xổ xuống toàn chiều ngang. Cả hai kiểu có nút mở menu con.', 'default' => 'brand_right', 'options' => array( 'none' => 'None — Ẩn menu', 'brand_right' => 'Kiểu 1 — Logo + menu trượt phải', 'brand_dropdown' => 'Kiểu 2 — Logo + menu xổ xuống nâu/kem' ), 'group' => 'mobile_menu' ),
    array( 'id' => 'mobile_menu', 'type' => 'select', 'title' => 'Menu dùng trên Mobile', 'description' => 'Để mặc định dùng Menu chính hoặc vị trí Primary Menu. Chưa có menu thì không hiển thị nút.', 'default' => 0, 'options' => $az_header_menus, 'group' => 'mobile_menu' ),
    // Top bar: the optional strip above the main header.
    array( 'id' => 'top_bar', 'type' => 'switch', 'title' => 'Hiện Top bar', 'default' => 1, 'group' => 'top_bar', 'group_title' => 'Top bar' ),
    array( 'id' => 'top_bar_text', 'type' => 'text', 'title' => 'Nội dung bên trái', 'default' => '', 'group' => 'top_bar' ),
    array( 'id' => 'top_menu', 'type' => 'select', 'title' => 'Menu bên phải', 'description' => 'Chọn menu hiển thị ở Top bar.', 'default' => 0, 'options' => $az_header_menus, 'group' => 'top_bar' ),

    // Header main: logo and utility elements.
    array( 'id' => 'height', 'type' => 'range', 'title' => 'Chiều cao Header main', 'default' => 96, 'unit' => 'px', 'min' => 64, 'max' => 180, 'step' => 1, 'group' => 'header_main', 'group_title' => 'Header main' ),
    array( 'id' => 'show_search', 'type' => 'switch', 'title' => 'Hiện tìm kiếm', 'default' => 1, 'group' => 'header_main' ),
    array( 'id' => 'show_category', 'type' => 'switch', 'title' => 'Hiện nút danh mục', 'default' => 1, 'group' => 'header_main' ),
    array( 'id' => 'category_menu', 'type' => 'select', 'title' => 'Menu danh mục dự phòng', 'description' => 'Header và trang chủ ưu tiên menu có tên “Danh mục sản phẩm”. Lựa chọn này chỉ dùng khi menu đó không tồn tại.', 'default' => 0, 'options' => $az_header_menus, 'group' => 'header_main' ),
    array( 'id' => 'category_button_text', 'type' => 'text', 'title' => 'Nhãn danh mục', 'default' => 'Danh mục sản phẩm', 'group' => 'header_main' ),
    array( 'id' => 'search_placeholder', 'type' => 'text', 'title' => 'Gợi ý tìm kiếm', 'default' => 'Nhập sản phẩm cần tìm...', 'group' => 'header_main' ),
    array( 'id' => 'show_account', 'type' => 'switch', 'title' => 'Hiện tài khoản', 'default' => 1, 'group' => 'header_main' ),
    array( 'id' => 'account_label', 'type' => 'text', 'title' => 'Nhãn tài khoản', 'default' => 'Tài khoản', 'group' => 'header_main' ),
    array( 'id' => 'show_cart', 'type' => 'switch', 'title' => 'Hiện giỏ hàng', 'default' => 1, 'group' => 'header_main' ),
    array( 'id' => 'cart_label', 'type' => 'text', 'title' => 'Nhãn giỏ hàng', 'default' => 'Giỏ hàng', 'group' => 'header_main' ),
    array( 'id' => 'show_cta', 'type' => 'switch', 'title' => 'Hiện CTA', 'default' => 0, 'group' => 'header_main' ),
    array( 'id' => 'cta_text', 'type' => 'text', 'title' => 'Nhãn CTA', 'default' => '', 'group' => 'header_main' ),
    array( 'id' => 'cta_url', 'type' => 'url', 'title' => 'Liên kết CTA', 'default' => '', 'group' => 'header_main' ),

    // Header bottom: primary navigation only.
    array( 'id' => 'show_primary_menu', 'type' => 'switch', 'title' => 'Hiện Header bottom', 'description' => 'Bật thanh menu điều hướng bên dưới Header main.', 'default' => 1, 'group' => 'header_bottom', 'group_title' => 'Header bottom' ),
    array( 'id' => 'primary_menu', 'type' => 'select', 'title' => 'Menu chính', 'description' => 'Để mặc định sẽ dùng vị trí Primary Menu của WordPress.', 'default' => 0, 'options' => $az_header_menus, 'group' => 'header_bottom' ),

    array( 'id' => 'sticky', 'type' => 'switch', 'title' => 'Bật Sticky header', 'description' => 'Chọn một hoặc nhiều phần header muốn giữ lại khi cuộn.', 'default' => 0, 'group' => 'sticky', 'group_title' => 'Sticky Header' ),
    array( 'id' => 'sticky_top_bar', 'type' => 'switch', 'title' => 'Sticky Top bar', 'default' => 0, 'group' => 'sticky' ),
    array( 'id' => 'sticky_header_main', 'type' => 'switch', 'title' => 'Sticky Header main', 'default' => 0, 'group' => 'sticky' ),
    array( 'id' => 'sticky_header_bottom', 'type' => 'switch', 'title' => 'Sticky Header bottom', 'description' => 'Chỉ hiển thị menu chính dạng sticky.', 'default' => 1, 'group' => 'sticky' ),
    array( 'id' => 'sticky_menu', 'type' => 'select', 'title' => 'Menu Sticky', 'description' => 'Áp dụng cho Header bottom; để mặc định sẽ dùng Menu chính.', 'default' => 0, 'options' => $az_header_menus, 'group' => 'sticky' ),
    array( 'id' => 'sticky_show_search', 'type' => 'switch', 'title' => 'Hiện tìm kiếm trong Sticky main', 'default' => 0, 'group' => 'sticky' ),
    array( 'id' => 'transparent', 'type' => 'switch', 'title' => 'Header trong suốt', 'default' => 0, 'group' => 'sticky' ),
);
