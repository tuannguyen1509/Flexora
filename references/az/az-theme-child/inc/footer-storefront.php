<?php
/** Shared footer data and child-owned additions to Theme Advanced > General. */
defined( 'ABSPATH' ) || exit;

function az_child_footer_policy_defaults() {
    return array(
        array( 'label' => 'Chính sách đổi trả kiểm hàng', 'url' => '/chinh-sach-doi-tra/' ),
        array( 'label' => 'Chính sách bảo hành', 'url' => '/chinh-sach-bao-hanh/' ),
        array( 'label' => 'Chính sách bảo mật', 'url' => '/chinh-sach-bao-mat/' ),
        array( 'label' => 'Điều khoản dịch vụ', 'url' => '/dieu-khoan-dich-vu/' ),
        array( 'label' => 'Chính sách thanh toán', 'url' => '/hinh-thuc-thanh-toan/' ),
        array( 'label' => 'Chính sách vận chuyển', 'url' => '/chinh-sach-van-chuyen/' ),
        array( 'label' => 'Chính sách bảo vệ thông tin người tiêu dùng', 'url' => '/chinh-sach-bao-ve-thong-tin-nguoi-tieu-dung/' ),
    );
}

/** Parent builds its registry while loading; add fields before admin render/save. */
function az_child_footer_register_general_fields() {
    if ( ! is_admin() || ! class_exists( 'AZ_Options_Registry' ) ) { return; }
    $section = AZ_Options_Registry::get( 'general' );
    if ( ! $section ) { return; }
    $existing = array_column( $section['fields'], 'id' );
    $fields = array(
        array( 'id' => 'fax', 'type' => 'text', 'title' => 'Fax', 'default' => '', 'legacy' => 'az_contact_fax', 'group' => 'contact', 'width' => 'half' ),
        array( 'id' => 'registration_date', 'shortcode' => '', 'type' => 'text', 'title' => 'Ngày cấp đăng ký kinh doanh', 'description' => 'Ví dụ: 08-06-2020. Hiển thị trong cột Chứng nhận ở chân trang.', 'default' => '', 'group' => 'legal', 'width' => 'half' ),
        array( 'id' => 'registration_authority', 'shortcode' => '', 'type' => 'text', 'title' => 'Nơi cấp đăng ký kinh doanh', 'default' => '', 'group' => 'legal', 'width' => 'full' ),
        array( 'id' => 'footer_profile_title', 'shortcode' => '', 'type' => 'text', 'title' => 'Tiêu đề hồ sơ ở chân trang', 'default' => 'Hồ sơ năng lực', 'group' => 'footer_profile', 'group_title' => 'Hồ sơ ở chân trang', 'group_collapsible' => true, 'width' => 'full' ),
        array( 'id' => 'footer_profile_image', 'shortcode' => '', 'type' => 'image', 'title' => 'Ảnh bìa hồ sơ', 'description' => 'Chọn ảnh từ thư viện Media để thay danh sách sản phẩm ở footer. Chưa chọn ảnh thì ẩn cột hồ sơ. Ảnh giữ nguyên tỷ lệ gốc.', 'default' => 0, 'group' => 'footer_profile', 'width' => 'full' ),
        array( 'id' => 'footer_profile_url', 'shortcode' => '', 'type' => 'url', 'title' => 'Link file hồ sơ', 'description' => 'Dán URL file PDF hoặc trang xem hồ sơ. Bấm ảnh sẽ mở liên kết trong tab mới để xem; không ép tải xuống. Để trống thì chỉ hiển thị ảnh.', 'default' => '', 'group' => 'footer_profile', 'width' => 'full' ),
        array( 'id' => 'footer_policies', 'shortcode' => '', 'type' => 'repeater', 'title' => 'Liên kết chính sách ở chân trang', 'description' => 'Đổi tên, URL và thứ tự các chính sách. Có thể dùng đường dẫn nội bộ bắt đầu bằng /.', 'default' => az_child_footer_policy_defaults(), 'group' => 'footer_content', 'group_title' => 'Nội dung chân trang', 'group_collapsible' => true, 'width' => 'full', 'fields' => array(
            array( 'id' => 'label', 'type' => 'text', 'title' => 'Tên chính sách' ),
            array( 'id' => 'url', 'type' => 'text', 'title' => 'Đường dẫn' ),
        ) ),
    );
    foreach ( $fields as $field ) {
        if ( ! in_array( $field['id'], $existing, true ) ) { AZ_Options_Registry::add_field( 'general', $field ); }
    }
    $section = AZ_Options_Registry::get( 'general' );
    foreach ( $section['fields'] as &$field ) {
        if ( 'addresses' !== $field['id'] ) { continue; }
        $field['description'] = 'Iframe trong danh sách này là nguồn bản đồ cho trang Liên hệ và shortcode [az_google_map] ở các vị trí chèn bản đồ. Link Google Maps / Chỉ đường dùng cho cụm hai icon ở footer; để trống link sẽ ẩn cụm icon. Đỗ ô tô chỉ là hình ảnh, không có link.';
    }
    unset( $field );
    AZ_Options_Registry::add_section( 'general', $section['title'], $section['fields'] );
}
add_action( 'after_setup_theme', 'az_child_footer_register_general_fields', 20 );

/** Explicitly empty saved values stay empty; legacy data is only a fallback. */
function az_child_footer_value( $general, $key, $legacy = '', $fallback = '' ) {
    $value = array_key_exists( $key, $general ) ? $general[ $key ] : ( $legacy ? get_option( $legacy, $fallback ) : $fallback );
    return is_scalar( $value ) ? trim( (string) $value ) : '';
}

function az_child_footer_data() {
    $options = az_get_options();
    $general = isset( $options['general'] ) && is_array( $options['general'] ) ? $options['general'] : array();
    $social = az_get_social_links();
    $data = array(
        'company' => az_child_footer_value( $general, 'company_name', 'az_contact_company', get_bloginfo( 'name' ) ),
        'addresses' => az_get_contact_items( 'address' ),
        'phones' => az_get_contact_items( 'phone' ),
        'emails' => az_get_contact_items( 'email' ),
        'fax' => az_child_footer_value( $general, 'fax', 'az_contact_fax' ),
        'website' => az_child_footer_value( $general, 'website', 'az_contact_website' ),
        'tax_code' => az_child_footer_value( $general, 'tax_code', 'az_contact_tax_code' ),
        'business_registration' => az_child_footer_value( $general, 'business_registration', 'az_contact_registration' ),
        'registration_date' => az_child_footer_value( $general, 'registration_date' ),
        'registration_authority' => az_child_footer_value( $general, 'registration_authority' ),
        'copyright' => az_child_footer_value( $general, 'copyright' ),
        // The parent helper returns display-escaped URLs; decode before embedding.
        'facebook' => esc_url_raw( html_entity_decode( $social['facebook'] ?? '', ENT_QUOTES, 'UTF-8' ), array( 'http', 'https' ) ),
        'locations' => az_child_footer_locations( $general ),
        'profile' => az_child_footer_profile_data( $general ),
        'policies' => array(),
    );
    $policies = array_key_exists( 'footer_policies', $general ) ? $general['footer_policies'] : az_child_footer_policy_defaults();
    foreach ( is_array( $policies ) ? $policies : array() as $policy ) {
        if ( ! is_array( $policy ) ) { continue; }
        $label = sanitize_text_field( $policy['label'] ?? '' );
        $url = is_string( $policy['url'] ?? null ) ? trim( $policy['url'] ) : '';
        if ( '/' === substr( $url, 0, 1 ) && '//' !== substr( $url, 0, 2 ) ) { $url = home_url( $url ); }
        $url = esc_url_raw( $url, array( 'http', 'https' ) );
        if ( $label && $url ) { $data['policies'][] = array( 'label' => $label, 'url' => $url ); }
    }
    $facebook_host = strtolower( (string) wp_parse_url( $data['facebook'], PHP_URL_HOST ) );
    if ( ! in_array( $facebook_host, array( 'facebook.com', 'www.facebook.com', 'm.facebook.com', 'web.facebook.com' ), true ) || ! in_array( wp_parse_url( $data['facebook'], PHP_URL_SCHEME ), array( 'https', 'http' ), true ) ) {
        $data['facebook'] = '';
    }
    $data['website'] = esc_url_raw( $data['website'], array( 'http', 'https' ) );
    return $data;
}

/** An image cover replaces the old product-category list on every page. */
function az_child_footer_profile_data( $general ) {
    $title = sanitize_text_field( az_child_footer_value( $general, 'footer_profile_title', '', 'Hồ sơ năng lực' ) ) ?: 'Hồ sơ năng lực';
    $image_id = absint( az_child_footer_value( $general, 'footer_profile_image' ) );
    $image = $image_id && wp_attachment_is_image( $image_id ) ? wp_get_attachment_image( $image_id, 'medium_large', false, array( 'class' => 'az-footer-profile-image', 'alt' => $title, 'loading' => 'lazy', 'decoding' => 'async' ) ) : '';
    return array(
        'title' => $title,
        'image' => $image,
        'url' => esc_url_raw( az_child_footer_value( $general, 'footer_profile_url' ), array( 'http', 'https' ) ),
    );
}

function az_child_footer_icon( $name ) {
    $paths = array(
        'map' => '<path d="m3 5 6-2 6 2 6-2v16l-6 2-6-2-6 2Zm6-2v16m6-14v16"/>',
        'pin' => '<path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 0 1 14 0Z"/><circle cx="12" cy="10" r="2"/>',
        'phone' => '<path d="m5 3 4 1 2 5-3 2c1 3 3 5 6 6l2-3 5 2v4c0 2-3 3-5 2C9 19 5 15 3 8 2 5 3 3 5 3Z"/>',
        'fax' => '<path d="M9 8V3h11v5M8 19h13V8H8ZM3 7h5v14H3Zm10 5h1m3 0h1m-5 4h5"/>',
        'email' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 6 10 7L22 6"/>',
        'globe' => '<circle cx="12" cy="12" r="10"/><ellipse cx="12" cy="12" rx="4" ry="10"/><path d="M2 12h20M4 6h16M4 18h16"/>',
    );
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ( $paths[ $name ] ?? $paths['pin'] ) . '</svg>';
}

function az_child_footer_contact_row( $icon, $label, $value, $href = '' ) {
    if ( '' === trim( (string) $value ) ) { return; }
    $label = rtrim( trim( (string) $label ), ':' );
    ?>
    <li class="az-footer-contact-row"><?php echo az_child_footer_icon( $icon ); ?><span><?php if ( $label ) : ?><span><?php echo esc_html( $label ); ?>: </span><?php endif; ?><?php if ( $href ) : ?><a href="<?php echo esc_url( $href ); ?>"><?php echo nl2br( esc_html( $value ) ); ?></a><?php else : ?><?php echo nl2br( esc_html( $value ) ); ?><?php endif; ?></span></li>
    <?php
}

/** Keep Maps fields that the parent contact-list helper omits. */
function az_child_footer_locations( $general ) {
    $locations = array();
    foreach ( is_array( $general['addresses'] ?? null ) ? $general['addresses'] : array() as $row ) {
        if ( ! is_array( $row ) ) { continue; }
        $location = array(
            'label' => sanitize_text_field( $row['label'] ?? '' ),
            'value' => sanitize_textarea_field( az_child_footer_value( $row, 'value' ) ),
            'maps_url' => esc_url_raw( az_child_footer_value( $row, 'maps_url' ), array( 'http', 'https' ) ),
        );
        if ( $location['maps_url'] ) { $locations[] = $location; }
    }
    return $locations;
}

function az_child_footer_location_url( $location ) {
    return esc_url_raw( az_child_footer_value( $location, 'maps_url' ), array( 'https', 'http' ) );
}

function az_child_footer_location_icon( $index ) {
    $file = '/icon/location-button-' . absint( $index ) . '.png';
    $label = 1 === $index ? 'Chỉ đường' : 'Đỗ ô tô';
    return '<img src="' . esc_url( get_stylesheet_directory_uri() . $file ) . '" width="350" height="100" alt="' . esc_attr( $label ) . '" loading="lazy" decoding="async">';
}

/** Directions is a link; parking is a non-interactive image. */
function az_child_footer_location_actions( $location ) {
    $url = az_child_footer_location_url( $location );
    if ( ! $url ) { return; }
    $name = trim( (string) ( $location['value'] ?? '' ) ) ?: ( $location['label'] ?? '' );
    ?>
    <div class="az-footer-location-actions" role="group" aria-label="Bản đồ và điểm đỗ ô tô">
      <a class="az-footer-location-action" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $name ? 'Chỉ đường đến ' . $name : 'Chỉ đường trên Google Maps' ); ?>"><?php echo az_child_footer_location_icon( 1 ); ?></a>
      <span class="az-footer-location-action"><?php echo az_child_footer_location_icon( 2 ); ?></span>
    </div>
    <?php
}
