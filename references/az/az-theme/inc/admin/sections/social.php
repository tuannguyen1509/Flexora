<?php
return array(
    array( 'id' => 'contact_enabled', 'type' => 'switch', 'title' => 'Hiển thị All in One Contact', 'description' => 'Các nút liên hệ nổi ở góc dưới bên trái website.', 'default' => 0, 'group' => 'contact', 'group_title' => 'All in One Contact' ),
    array( 'id' => 'contact_style', 'type' => 'select', 'title' => 'Kiểu hiển thị', 'description' => 'Kiểu 1 hiển thị icon Messenger, Zalo và Hotline khi đã nhập đường dẫn hoặc số điện thoại tương ứng.', 'default' => 'icons', 'options' => array( 'icons' => 'Kiểu 1 – Icon tròn tối giản' ), 'group' => 'contact' ),
    array( 'id' => 'contact_phone', 'type' => 'text', 'title' => 'Hotline All in One', 'description' => 'Để trống để ẩn nút gọi. Nút Zalo và nhắn tin Facebook lấy đường dẫn từ mục Zalo và Messenger bên dưới.', 'default' => '', 'group' => 'contact' ),
    array( 'id' => 'facebook', 'type' => 'url', 'title' => 'Facebook', 'icon' => 'facebook', 'default' => '', 'legacy' => 'az_footer_social_facebook', 'group' => 'networks', 'group_title' => 'Social Networks' ),
    array( 'id' => 'youtube', 'type' => 'url', 'title' => 'YouTube', 'icon' => 'youtube', 'default' => '', 'legacy' => 'az_footer_social_youtube', 'group' => 'networks' ),
    array( 'id' => 'tiktok', 'type' => 'url', 'title' => 'TikTok', 'icon' => 'video-alt3', 'default' => '', 'legacy' => 'az_footer_social_tiktok', 'group' => 'networks' ),
    array( 'id' => 'instagram', 'type' => 'url', 'title' => 'Instagram', 'icon' => 'instagram', 'default' => '', 'group' => 'networks' ),
    array( 'id' => 'linkedin', 'type' => 'url', 'title' => 'LinkedIn', 'icon' => 'linkedin', 'default' => '', 'legacy' => 'az_footer_social_linkedin', 'group' => 'networks' ),
    array( 'id' => 'pinterest', 'type' => 'url', 'title' => 'Pinterest', 'icon' => 'share', 'default' => '', 'group' => 'networks' ),
    array( 'id' => 'x', 'type' => 'url', 'title' => 'X', 'icon' => 'twitter', 'default' => '', 'group' => 'networks' ),
    array( 'id' => 'zalo', 'type' => 'url', 'title' => 'Zalo', 'icon' => 'format-chat', 'default' => '', 'legacy' => 'az_contact_zalo_url', 'group' => 'messaging', 'group_title' => 'Messaging' ),
    array( 'id' => 'messenger', 'type' => 'url', 'title' => 'Messenger', 'description' => 'Đường dẫn nhắn tin Facebook, ví dụ https://m.me/tenfanpage. Dùng cho nút Messenger trong All in One Contact. Để trống để ẩn nút.', 'icon' => 'format-status', 'default' => '', 'legacy' => 'az_contact_messenger_url', 'group' => 'messaging' ),
    array( 'id' => 'whatsapp', 'type' => 'url', 'title' => 'WhatsApp', 'icon' => 'phone', 'default' => '', 'group' => 'messaging' ),
);
