<?php
/**
 * Typography controls shared by the parent theme and child themes.
 *
 * Values are exposed as CSS custom properties by AZ_Dynamic_CSS.
 */

$weights = array( 300 => '300', 400 => '400', 500 => '500', 600 => '600', 700 => '700', 800 => '800', 900 => '900' );
$fonts   = az_get_font_options();

return array(
    array( 'id' => 'font_library', 'type' => 'link', 'title' => 'WordPress Font Library', 'description' => 'Be Vietnam Pro đã có sẵn trong giao diện và được dùng mặc định, không cần tải hoặc kích hoạt. Mở thư viện nếu muốn thêm font khác.', 'url' => admin_url( 'font-library.php?p=%2Ffont-list' ), 'link_label' => 'Mở Font Library', 'group' => 'font_library', 'group_title' => 'Font Library' ),
    array( 'id' => 'body_family', 'type' => 'select', 'title' => 'Font family', 'description' => 'Be Vietnam Pro có sẵn trong giao diện. Có thể chọn font khác từ WordPress Font Library.', 'default' => az_default_font_family(), 'options' => $fonts, 'group' => 'body', 'group_title' => 'Body', 'width' => 'full' ),
    array( 'id' => 'body_size', 'type' => 'range', 'title' => 'Cỡ chữ desktop', 'default' => 16, 'unit' => 'px', 'min' => 12, 'max' => 24, 'step' => 1, 'group' => 'body' ),
    array( 'id' => 'body_size_tablet', 'type' => 'range', 'title' => 'Cỡ chữ tablet', 'default' => 16, 'unit' => 'px', 'min' => 12, 'max' => 22, 'step' => 1, 'group' => 'body' ),
    array( 'id' => 'body_size_mobile', 'type' => 'range', 'title' => 'Cỡ chữ mobile', 'default' => 16, 'unit' => 'px', 'min' => 12, 'max' => 20, 'step' => 1, 'group' => 'body' ),
    array( 'id' => 'body_weight', 'type' => 'select', 'title' => 'Độ đậm', 'default' => 400, 'options' => $weights, 'group' => 'body' ),
    array( 'id' => 'body_line_height', 'type' => 'range', 'title' => 'Giãn dòng', 'default' => 1.55, 'min' => 1.2, 'max' => 2.2, 'step' => 0.05, 'group' => 'body' ),
    array( 'id' => 'body_letter_spacing', 'type' => 'range', 'title' => 'Khoảng cách ký tự', 'default' => 0, 'unit' => 'em', 'min' => -0.05, 'max' => 0.15, 'step' => 0.01, 'group' => 'body' ),

    array( 'id' => 'heading_family', 'type' => 'select', 'title' => 'Font family', 'description' => 'Để đồng bộ, chọn cùng font Body hoặc một font heading riêng.', 'default' => az_default_font_family(), 'options' => $fonts, 'group' => 'heading', 'group_title' => 'Heading', 'width' => 'full' ),
    array( 'id' => 'heading_weight', 'type' => 'select', 'title' => 'Độ đậm', 'description' => 'Mặc định 600 để tương thích font. Chỉ chọn weight cao hơn khi font đã có đúng biến thể đó.', 'default' => 600, 'options' => $weights, 'group' => 'heading' ),
    array( 'id' => 'heading_line_height', 'type' => 'range', 'title' => 'Giãn dòng', 'default' => 1.2, 'min' => 1, 'max' => 1.6, 'step' => 0.05, 'group' => 'heading' ),
    array( 'id' => 'heading_letter_spacing', 'type' => 'range', 'title' => 'Khoảng cách ký tự', 'default' => 0, 'unit' => 'em', 'min' => -0.05, 'max' => 0.1, 'step' => 0.01, 'group' => 'heading' ),

    array( 'id' => 'h1_desktop', 'type' => 'range', 'title' => 'Desktop', 'default' => 48, 'unit' => 'px', 'min' => 24, 'max' => 96, 'step' => 1, 'group' => 'h1', 'group_title' => 'H1' ),
    array( 'id' => 'h1_tablet', 'type' => 'range', 'title' => 'Tablet', 'default' => 40, 'unit' => 'px', 'min' => 22, 'max' => 80, 'step' => 1, 'group' => 'h1' ),
    array( 'id' => 'h1_mobile', 'type' => 'range', 'title' => 'Mobile', 'default' => 32, 'unit' => 'px', 'min' => 20, 'max' => 64, 'step' => 1, 'group' => 'h1' ),
    array( 'id' => 'h2_desktop', 'type' => 'range', 'title' => 'Desktop', 'default' => 34, 'unit' => 'px', 'min' => 20, 'max' => 72, 'step' => 1, 'group' => 'h2', 'group_title' => 'H2' ),
    array( 'id' => 'h2_tablet', 'type' => 'range', 'title' => 'Tablet', 'default' => 30, 'unit' => 'px', 'min' => 20, 'max' => 64, 'step' => 1, 'group' => 'h2' ),
    array( 'id' => 'h2_mobile', 'type' => 'range', 'title' => 'Mobile', 'default' => 26, 'unit' => 'px', 'min' => 18, 'max' => 56, 'step' => 1, 'group' => 'h2' ),
    array( 'id' => 'h3_desktop', 'type' => 'range', 'title' => 'Desktop', 'default' => 26, 'unit' => 'px', 'min' => 18, 'max' => 56, 'step' => 1, 'group' => 'h3', 'group_title' => 'H3' ),
    array( 'id' => 'h3_tablet', 'type' => 'range', 'title' => 'Tablet', 'default' => 24, 'unit' => 'px', 'min' => 18, 'max' => 48, 'step' => 1, 'group' => 'h3' ),
    array( 'id' => 'h3_mobile', 'type' => 'range', 'title' => 'Mobile', 'default' => 22, 'unit' => 'px', 'min' => 17, 'max' => 42, 'step' => 1, 'group' => 'h3' ),
    array( 'id' => 'h4_desktop', 'type' => 'range', 'title' => 'Desktop', 'default' => 18, 'unit' => 'px', 'min' => 16, 'max' => 40, 'step' => 1, 'group' => 'h4', 'group_title' => 'H4' ),
    array( 'id' => 'h4_tablet', 'type' => 'range', 'title' => 'Tablet', 'default' => 18, 'unit' => 'px', 'min' => 16, 'max' => 36, 'step' => 1, 'group' => 'h4' ),
    array( 'id' => 'h4_mobile', 'type' => 'range', 'title' => 'Mobile', 'default' => 18, 'unit' => 'px', 'min' => 16, 'max' => 32, 'step' => 1, 'group' => 'h4' ),

    array( 'id' => 'navigation_size', 'type' => 'range', 'title' => 'Cỡ chữ', 'default' => 16, 'unit' => 'px', 'min' => 12, 'max' => 24, 'step' => 1, 'group' => 'navigation', 'group_title' => 'Navigation' ),
    array( 'id' => 'navigation_weight', 'type' => 'select', 'title' => 'Độ đậm', 'description' => 'Mặc định 600 để tương thích font. Chỉ chọn weight cao hơn khi font đã có đúng biến thể đó.', 'default' => 600, 'options' => $weights, 'group' => 'navigation' ),
    array( 'id' => 'navigation_letter_spacing', 'type' => 'range', 'title' => 'Khoảng cách ký tự', 'default' => 0, 'unit' => 'em', 'min' => -0.05, 'max' => 0.15, 'step' => 0.01, 'group' => 'navigation' ),
);
