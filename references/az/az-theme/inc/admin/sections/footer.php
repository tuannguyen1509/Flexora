<?php
return array(
    array( 'id' => 'show_top_links', 'type' => 'switch', 'title' => 'Hiện khối liên kết phía trên', 'default' => 1, 'group' => 'display', 'group_title' => 'Hiển thị' ),
    array( 'id' => 'show_social', 'type' => 'switch', 'title' => 'Hiện liên kết mạng xã hội', 'default' => 1, 'group' => 'display' ),
    array( 'id' => 'show_copyright', 'type' => 'switch', 'title' => 'Hiện thanh bản quyền', 'default' => 1, 'group' => 'display' ),
    array( 'id' => 'back_to_top', 'type' => 'switch', 'title' => 'Hiển thị nút lên đầu trang', 'description' => 'Nút xuất hiện ở góc dưới màn hình khi cuộn trang xuống. Nhấn để cuộn về đầu trang.', 'default' => 0, 'legacy_path' => 'general.back_to_top', 'group' => 'back_to_top', 'group_title' => 'Nút lên đầu trang' ),
    array( 'id' => 'back_to_top_position', 'type' => 'select', 'title' => 'Vị trí nút lên đầu trang', 'description' => 'Chọn góc dưới bên trái hoặc bên phải trên máy tính và điện thoại.', 'default' => 'right', 'options' => array( 'right' => 'Bên phải', 'left' => 'Bên trái' ), 'group' => 'back_to_top' ),
);
