<?php
return array(
    array( 'id' => 'custom_css', 'type' => 'code', 'title' => 'Custom CSS', 'description' => 'CSS áp dụng toàn website. Chỉ nhập CSS, không cần thẻ <style>.', 'default' => '' ),
    array( 'id' => 'header_code', 'type' => 'code', 'title' => 'Header Code', 'description' => 'Chèn trong <head> trên mọi trang, dùng cho Google tag, mã xác minh và các thẻ HTML/script đầy đủ.', 'default' => '' ),
    array( 'id' => 'body_open_code', 'type' => 'code', 'title' => 'Body Open Code', 'description' => 'Chèn ngay sau <body> trên mọi trang, dùng cho Google Tag Manager noscript hoặc mã cần đặt đầu body.', 'default' => '' ),
    array( 'id' => 'footer_code', 'type' => 'code', 'title' => 'Footer Code', 'description' => 'Chèn cuối trang qua wp_footer(), dùng cho widget chat hoặc script cuối body. Hiển thị cả mã Body bottom/Footer cũ khi chưa lưu tại đây.', 'default' => '' ),
);
