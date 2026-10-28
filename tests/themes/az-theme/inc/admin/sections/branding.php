<?php
return array(
    array( 'id' => 'logo', 'type' => 'media', 'title' => 'Logo website', 'description' => 'Logo chính hiển thị trên desktop. Nếu chưa chọn sẽ dùng logo WordPress.', 'default' => 0, 'legacy_theme_mod' => 'custom_logo', 'group' => 'logo', 'group_title' => 'Logo' ),
    array( 'id' => 'logo_mobile', 'type' => 'media', 'title' => 'Logo mobile', 'description' => 'Không bắt buộc. Để trống sẽ tự dùng Logo website.', 'default' => 0, 'group' => 'logo' ),
    array( 'id' => 'favicon', 'type' => 'media', 'title' => 'Favicon', 'default' => 0, 'group' => 'metadata', 'group_title' => 'Metadata' ),
    array( 'id' => 'og_image', 'type' => 'media', 'title' => 'OG Image', 'default' => 0, 'group' => 'metadata' ),
    array( 'id' => 'home_h1', 'type' => 'text', 'title' => 'Homepage SEO H1', 'description' => 'Hiển thị H1 trên trang chủ. Để trống sẽ dùng Tiêu đề website.', 'default' => '', 'group' => 'metadata' ),
    array( 'id' => 'home_meta_description', 'type' => 'textarea', 'title' => 'Homepage Meta description', 'description' => 'Mô tả SEO của trang chủ. Để trống sẽ dùng Dòng mô tả trong Cài đặt → Tổng quan hoặc Nhận diện trang web.', 'default' => '', 'group' => 'metadata', 'width' => 'full' ),
    array( 'id' => 'logo_width', 'type' => 'range', 'title' => 'Logo Width Desktop', 'description' => 'Kéo thanh trượt để thay đổi chiều rộng logo trên desktop.', 'default' => 180, 'unit' => 'px', 'min' => 40, 'max' => 600, 'step' => 1, 'group' => 'sizing', 'group_title' => 'Sizing' ),
    array( 'id' => 'logo_width_mobile', 'type' => 'range', 'title' => 'Logo Width Mobile', 'description' => 'Kéo thanh trượt để thay đổi chiều rộng logo trên điện thoại.', 'default' => 140, 'unit' => 'px', 'min' => 32, 'max' => 400, 'step' => 1, 'group' => 'sizing' ),
);
