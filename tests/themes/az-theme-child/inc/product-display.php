<?php
/** Product display settings owned by the child theme. */
defined( 'ABSPATH' ) || exit;

function az_child_register_product_limits() {
    if ( ! is_admin() || ! class_exists( 'AZ_Options_Registry' ) ) { return; }
    $section = AZ_Options_Registry::get( 'woocommerce' );
    if ( ! $section ) { return; }
    $ids = array_column( $section['fields'], 'id' );
    foreach ( array(
        'related_products_count' => 'Số sản phẩm liên quan',
        'recently_viewed_count' => 'Số sản phẩm đã xem',
    ) as $id => $title ) {
        if ( in_array( $id, $ids, true ) ) { continue; }
        AZ_Options_Registry::add_field( 'woocommerce', array(
            'id' => $id,
            'type' => 'number',
            'title' => $title,
            'default' => 5,
            'min' => 1,
            'max' => 50,
            'step' => 1,
            'group' => 'product_related',
            'group_title' => 'Sản phẩm bổ sung',
            'description' => 'Số lượng tối đa trên trang chi tiết sản phẩm (1–50). Dùng công tắc hiển thị để bật/tắt khối này.',
        ) );
    }
}
add_action( 'after_setup_theme', 'az_child_register_product_limits', 20 );

function az_child_register_product_image_fields() {
    if ( ! is_admin() || ! class_exists( 'AZ_Options_Registry' ) ) { return; }
    $section = AZ_Options_Registry::get( 'woocommerce' );
    if ( ! $section ) { return; }
    $ids = array_column( $section['fields'], 'id' );
    $fields = array(
        array( 'id' => 'gallery_image_ratio', 'type' => 'select', 'title' => 'Tỷ lệ khung ảnh sản phẩm', 'default' => 'square', 'options' => array(
            'square' => 'Hình vuông — 1:1',
            'landscape' => 'Chữ nhật ngang — 4:3',
            'wide' => 'Chữ nhật ngang — 3:2',
            'widescreen' => 'Chữ nhật ngang — 16:9',
            'original' => 'Theo tỷ lệ ảnh gốc',
            'custom' => 'Tỷ lệ tùy chỉnh',
        ), 'description' => 'Dùng chung cho ảnh ở trang chi tiết sản phẩm, trang cửa hàng, danh mục sản phẩm, Sản phẩm liên quan và Sản phẩm đã xem. Ảnh hiển thị đầy đủ, không cắt và không kéo méo.', 'group' => 'product_images', 'group_title' => 'Kích thước ảnh sản phẩm', 'width' => 'full' ),
        array( 'id' => 'gallery_ratio_width', 'type' => 'number', 'title' => 'Tỷ lệ tùy chỉnh: chiều rộng', 'default' => 4, 'min' => 1, 'max' => 100, 'step' => 1, 'description' => 'Chỉ áp dụng khi chọn Tỷ lệ tùy chỉnh. Ví dụ rộng 4, cao 3 tương ứng 4:3.', 'group' => 'product_images', 'width' => 'half' ),
        array( 'id' => 'gallery_ratio_height', 'type' => 'number', 'title' => 'Tỷ lệ tùy chỉnh: chiều cao', 'default' => 3, 'min' => 1, 'max' => 100, 'step' => 1, 'description' => 'Nhập cùng số với chiều rộng để có hình vuông; số nhỏ hơn để có chữ nhật ngang.', 'group' => 'product_images', 'width' => 'half' ),
    );
    foreach ( $fields as $field ) {
        if ( ! in_array( $field['id'], $ids, true ) ) { AZ_Options_Registry::add_field( 'woocommerce', $field ); }
    }
}
add_action( 'after_setup_theme', 'az_child_register_product_image_fields', 20 );

/** Return only validated values suitable for the CSS aspect-ratio property. */
function az_child_product_image_ratio() {
    $mode = az_get_option( 'woocommerce.gallery_image_ratio', 'square' );
    $ratios = array( 'square' => '1 / 1', 'landscape' => '4 / 3', 'wide' => '3 / 2', 'widescreen' => '16 / 9', 'original' => 'auto' );
    if ( 'custom' !== $mode ) { return is_string( $mode ) && isset( $ratios[ $mode ] ) ? $ratios[ $mode ] : $ratios['square']; }
    $dimensions = array();
    foreach ( array( 'width' => 4, 'height' => 3 ) as $key => $default ) {
        $value = az_get_option( 'woocommerce.gallery_ratio_' . $key, $default );
        $dimensions[] = is_numeric( $value ) ? max( 1, min( 100, (int) $value ) ) : $default;
    }
    return implode( ' / ', $dimensions );
}

function az_child_is_product_catalogue() {
    return ( function_exists( 'is_shop' ) && is_shop() )
        || ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() );
}

/** Publish the same saved ratio for every product image on these pages. */
function az_child_product_image_css() {
    $ratio = az_child_product_image_ratio();
    $original = 'auto' === $ratio;
    return '.az-site{--az-product-image-ratio:' . $ratio . ';--az-product-card-image-position:' . ( $original ? 'static' : 'absolute' ) . ';--az-product-card-image-height:' . ( $original ? 'auto' : '100%' ) . ';}';
}

function az_child_product_image_assets() {
    if ( ! ( function_exists( 'is_product' ) && is_product() ) && ! az_child_is_product_catalogue() ) { return; }
    $dependencies = array( 'az-theme-child' );
    foreach ( array( 'az-woocommerce', 'az-product-card' ) as $handle ) {
        if ( wp_style_is( $handle, 'enqueued' ) ) { $dependencies[] = $handle; }
    }
    $file = '/css/product-gallery.css';
    wp_enqueue_style( 'az-child-product-gallery', get_stylesheet_directory_uri() . $file, $dependencies, filemtime( get_stylesheet_directory() . $file ) );
    wp_add_inline_style( 'az-child-product-gallery', az_child_product_image_css() );
}
add_action( 'wp_enqueue_scripts', 'az_child_product_image_assets', 35 );

/** Use complete artwork for catalogue, related and recently viewed card images. */
function az_child_catalogue_product_image( $image, $product, $size, $attr ) {
    $product_page = function_exists( 'is_product' ) && is_product();
    if ( ( ! $product_page && ! az_child_is_product_catalogue() ) || 'woocommerce_thumbnail' !== $size ) { return $image; }
    $image_id = $product->get_image_id();
    if ( ! $image_id ) { return $image; }
    return wp_get_attachment_image( $image_id, 'full', false, $attr ) ?: $image;
}
add_filter( 'woocommerce_product_get_image', 'az_child_catalogue_product_image', 20, 4 );

function az_child_product_section_count( $key ) {
    $value = az_get_option( 'woocommerce.' . $key, 5 );
    return is_numeric( $value ) ? max( 1, min( 50, (int) $value ) ) : 5;
}

/** Override the parent's pluggable tracker before parent functions.php loads. */
if ( ! function_exists( 'az_track_recently_viewed_product' ) ) {
    function az_track_recently_viewed_product() {
        if ( is_admin() || ! function_exists( 'is_product' ) || ! is_product() ) { return; }
        $product_id = get_queried_object_id();
        if ( ! $product_id ) { return; }

        $viewed = array();
        if ( ! empty( $_COOKIE['az_recent_products'] ) ) {
            $viewed = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_COOKIE['az_recent_products'] ) ) ) ) );
        }
        $viewed = array_values( array_diff( $viewed, array( (int) $product_id ) ) );
        array_unshift( $viewed, (int) $product_id );
        // Include the current product, which the display section excludes.
        $capacity = max( 12, az_child_product_section_count( 'recently_viewed_count' ) + 1 );
        $viewed = array_slice( array_unique( $viewed ), 0, $capacity );
        $cookie = implode( ',', $viewed );

        setcookie( 'az_recent_products', $cookie, time() + MONTH_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true );
        $_COOKIE['az_recent_products'] = $cookie;
    }
}
