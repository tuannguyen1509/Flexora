<?php
return array(
    array( 'id' => 'enabled', 'type' => 'switch', 'title' => 'Bật tích hợp Google', 'default' => 0, 'group' => 'login', 'group_title' => 'Đăng nhập Google' ),
    array( 'id' => 'login_enabled', 'type' => 'switch', 'title' => 'Bật đăng nhập Google', 'default' => 0, 'group' => 'login' ),
    array( 'id' => 'client_id', 'type' => 'text', 'title' => 'OAuth Client ID', 'default' => '', 'group' => 'login' ),
    array( 'id' => 'client_secret', 'type' => 'secret', 'title' => 'OAuth Client Secret', 'description' => 'Để trống giữ Secret đã lưu. Reset tab sẽ xóa cấu hình. Secret lưu trong database, không gửi ra frontend.', 'default' => '', 'group' => 'login' ),
    array( 'id' => 'callback', 'type' => 'link', 'title' => 'Authorized redirect URI', 'description' => 'Đăng ký chính xác URL này trong OAuth client loại Web application: ' . admin_url( 'admin-post.php?action=az_google_callback' ), 'url' => 'https://console.cloud.google.com/apis/credentials', 'link_label' => 'Mở Google Cloud Console', 'group' => 'login' ),
    array( 'id' => 'show_login', 'type' => 'switch', 'title' => 'Hiện trên form đăng nhập WordPress', 'description' => 'Hoặc chèn shortcode [google-login] / [az_google_login] vào nội dung.', 'default' => 1, 'group' => 'display', 'group_title' => 'Hiển thị' ),
    array( 'id' => 'allow_registration', 'type' => 'switch', 'title' => 'Cho tạo tài khoản mới qua Google', 'description' => 'Chỉ tạo Subscriber. Email đã có tài khoản WordPress không tự liên kết; cần đăng nhập bằng mật khẩu. Tài khoản tạo qua Google được nhận diện bằng Google subject ID.', 'default' => 0, 'group' => 'display' ),
    array( 'id' => 'captcha_type', 'type' => 'select', 'title' => 'reCAPTCHA cho đăng nhập WordPress', 'options' => array( 'none' => 'None', 'v2' => 'reCAPTCHA v2 — Checkbox' ), 'default' => 'none', 'group' => 'captcha', 'group_title' => 'Google reCAPTCHA' ),
    array( 'id' => 'site_key', 'type' => 'text', 'title' => 'reCAPTCHA Site Key', 'default' => '', 'group' => 'captcha' ),
    array( 'id' => 'captcha_secret', 'type' => 'secret', 'title' => 'reCAPTCHA Secret Key', 'description' => 'Dùng key loại v2 Checkbox. Kiểm tra trên phiên trình duyệt khác trước khi đăng xuất; cấu hình sai có thể chặn đăng nhập mật khẩu. Không áp dụng cho REST/Application Passwords hoặc form plugin.', 'default' => '', 'group' => 'captcha' ),
);
