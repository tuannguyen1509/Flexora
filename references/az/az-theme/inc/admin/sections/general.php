<?php
return array(
    array( 'id' => 'classic_editor', 'type' => 'switch', 'title' => 'Dùng Classic Editor', 'description' => 'Bật để dùng trình soạn thảo Classic cho bài viết và trang. Tắt để giữ lựa chọn WordPress/plugin. Không chuyển đổi nội dung đã lưu hoặc thay đổi Site Editor.', 'default' => 0, 'group' => 'editor', 'group_title' => 'Trình soạn thảo' ),
    array( 'id' => 'import_remote_images', 'type' => 'switch', 'title' => 'Nhập ảnh ngoài khi lưu bài', 'description' => 'Mặc định tắt. Khi bật, ảnh từ URL ngoài được tải vào thư viện và thay thế trong nội dung; có thể làm lưu bài chậm.', 'default' => 0, 'group' => 'media', 'group_title' => 'Nhập nội dung' ),
    array( 'id' => 'security_headers', 'type' => 'switch', 'title' => 'Header bảo mật cơ bản', 'description' => 'Tắt khi plugin bảo mật hoặc máy chủ đã quản lý header. SAMEORIGIN không cho website khác nhúng trang bằng iframe.', 'default' => 1, 'group' => 'security', 'group_title' => 'Bảo mật & Tương thích' ),
    array( 'id' => 'generic_login_errors', 'type' => 'switch', 'title' => 'Ẩn chi tiết lỗi đăng nhập', 'default' => 1, 'group' => 'security' ),
    array( 'id' => 'show_leads_menu', 'type' => 'switch', 'title' => 'Hiển thị menu Quản lý Lead', 'description' => 'Ẩn/hiện menu Quản lý Lead trong quản trị. Không tắt biểu mẫu, SMTP hoặc xoá dữ liệu lead.', 'default' => 1, 'group' => 'admin_menu', 'group_title' => 'Menu quản trị' ),
    array( 'id' => 'company_name', 'type' => 'text', 'title' => 'Tên công ty', 'default' => '', 'legacy' => 'az_contact_company', 'group' => 'company', 'group_title' => 'Thông tin chính', 'width' => 'half' ),
    array( 'id' => 'brand_name', 'type' => 'text', 'title' => 'Tên thương hiệu', 'default' => '', 'legacy' => 'az_contact_brand', 'group' => 'company', 'width' => 'half' ),
    array( 'id' => 'website', 'type' => 'url', 'title' => 'Website', 'default' => '', 'legacy' => 'az_contact_website', 'group' => 'company', 'width' => 'half' ),
    array( 'id' => 'tax_code', 'type' => 'text', 'title' => 'Mã số thuế', 'default' => '', 'legacy' => 'az_contact_tax_code', 'group' => 'company', 'width' => 'half' ),

    array( 'id' => 'phones', 'type' => 'repeater', 'title' => 'Hotline', 'description' => 'Thêm tên hiển thị và số điện thoại.', 'default' => array(), 'group' => 'contact', 'group_title' => 'Hotline & Email', 'group_collapsible' => true, 'width' => 'full', 'fields' => array( array( 'id' => 'label', 'type' => 'text', 'title' => 'Tên hotline' ), array( 'id' => 'value', 'type' => 'text', 'title' => 'Số điện thoại' ) ) ),
    array( 'id' => 'emails', 'type' => 'repeater', 'title' => 'Email', 'description' => 'Thêm tên hiển thị và địa chỉ email.', 'default' => array(), 'group' => 'contact', 'width' => 'full', 'fields' => array( array( 'id' => 'label', 'type' => 'text', 'title' => 'Tên email' ), array( 'id' => 'value', 'type' => 'email', 'title' => 'Địa chỉ email' ) ) ),
    array( 'id' => 'working_hours', 'type' => 'text', 'title' => 'Giờ làm việc', 'default' => '', 'legacy' => 'az_contact_hours', 'group' => 'contact', 'width' => 'half' ),
    array( 'id' => 'contact_person', 'type' => 'text', 'title' => 'Người liên hệ', 'default' => '', 'legacy' => 'az_contact_person', 'group' => 'contact', 'width' => 'half' ),

    array( 'id' => 'addresses', 'type' => 'repeater', 'title' => 'Danh sách địa chỉ', 'description' => 'Mỗi chi nhánh gồm tên, địa chỉ, link chỉ đường và iframe Google Maps. Iframe ở đây dùng chung cho trang Liên hệ và các vị trí chèn [az_google_map]. Có thể dùng [az_google_map index="2"] để chỉ hiện bản đồ của địa chỉ thứ hai.', 'default' => array(), 'group' => 'location', 'group_title' => 'Địa chỉ & Bản đồ', 'group_collapsible' => true, 'width' => 'full', 'fields' => array( array( 'id' => 'label', 'type' => 'text', 'title' => 'Tên địa điểm' ), array( 'id' => 'value', 'type' => 'textarea', 'title' => 'Địa chỉ' ), array( 'id' => 'maps_url', 'type' => 'url', 'title' => 'Link Google Maps / Chỉ đường' ), array( 'id' => 'maps_embed', 'type' => 'map_embed', 'title' => 'Iframe Google Maps' ) ) ),

    array( 'id' => 'representative', 'type' => 'text', 'title' => 'Người đại diện', 'default' => '', 'legacy' => 'az_contact_representative', 'group' => 'legal', 'group_title' => 'Thông tin pháp lý', 'group_collapsible' => true, 'width' => 'half' ),
    array( 'id' => 'business_registration', 'type' => 'text', 'title' => 'Đăng ký kinh doanh', 'default' => '', 'legacy' => 'az_contact_registration', 'group' => 'legal', 'width' => 'half' ),

    array( 'id' => 'contact_page_title', 'type' => 'text', 'title' => 'Tiêu đề', 'default' => 'Tư vấn & Hỗ trợ', 'legacy' => 'az_contact_title_vi', 'group' => 'contact_page', 'group_title' => 'Nội dung trang Liên hệ', 'group_collapsible' => true, 'width' => 'full' ),
    array( 'id' => 'contact_page_description', 'type' => 'textarea', 'title' => 'Mô tả', 'default' => 'Đội ngũ Công ty chúng tôi sẵn sàng tư vấn sản phẩm, báo giá và hỗ trợ kỹ thuật nhanh chóng.', 'legacy' => 'az_contact_desc_vi', 'group' => 'contact_page', 'width' => 'full' ),

    array( 'id' => 'copyright', 'type' => 'text', 'title' => 'Copyright', 'default' => '', 'group' => 'site', 'group_title' => 'Website & Hiển thị', 'group_collapsible' => true, 'width' => 'full' ),
    array( 'id' => 'ministry_of_trade_url', 'type' => 'url', 'title' => 'Link Bộ Công Thương', 'description' => 'Dán link hồ sơ tại online.gov.vn; để trống để ẩn logo.', 'shortcode' => '[az_ministry_of_trade]', 'default' => '', 'group' => 'site', 'width' => 'full' ),
    array( 'id' => 'breadcrumb', 'type' => 'switch', 'title' => 'Hiển thị breadcrumb', 'default' => 1, 'group' => 'site' ),
    array( 'id' => 'preloader', 'type' => 'switch', 'title' => 'Hiển thị hiệu ứng tải trang', 'default' => 0, 'group' => 'site' ),

);
