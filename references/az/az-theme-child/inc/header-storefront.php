<?php
/** Shared header options and child-only fields in Theme Advanced > Header. */
defined( 'ABSPATH' ) || exit;

function az_child_header_register_fields() {
    if ( ! is_admin() || ! class_exists( 'AZ_Options_Registry' ) ) { return; }
    $section = AZ_Options_Registry::get( 'header' );
    if ( ! $section ) { return; }
    foreach ( $section['fields'] as &$field ) {
        if ( 'show_category' === $field['id'] ) {
            $field['description'] = 'Bật/tắt cả nút và menu danh mục sản phẩm trên Header. Khi tắt, menu chính dùng toàn bộ chiều rộng thanh điều hướng. Nhấn Save Changes để áp dụng.';
        }
        if ( 'category_menu' === $field['id'] ) {
            $field['description'] = 'Ưu tiên menu đã chọn tại đây cho tất cả trang. Để mặc định sẽ dùng menu “Danh mục sản phẩm”, sau đó danh mục WooCommerce.';
        }
    }
    unset( $field );
    AZ_Options_Registry::add_section( 'header', $section['title'], $section['fields'] );
    $ids = array_column( $section['fields'], 'id' );
    foreach ( array(
        array( 'id' => 'show_hotline', 'type' => 'switch', 'title' => 'Hiện hotline', 'default' => 1, 'group' => 'header_main' ),
        array( 'id' => 'hotline', 'type' => 'text', 'title' => 'Số hotline trên Header', 'description' => 'Dùng chung trên tất cả trang. Khi chưa lưu, lấy hotline đầu tiên từ General; để trống và lưu để ẩn số.', 'default' => az_get_phone(), 'group' => 'header_main', 'width' => 'half' ),
        array( 'id' => 'hotline_label', 'type' => 'text', 'title' => 'Nhãn hotline', 'default' => 'Hotline tư vấn và đặt hàng', 'group' => 'header_main', 'width' => 'half' ),
    ) as $field ) {
        if ( ! in_array( $field['id'], $ids, true ) ) { AZ_Options_Registry::add_field( 'header', $field ); }
    }
}
add_action( 'after_setup_theme', 'az_child_header_register_fields', 20 );

function az_child_header_admin_assets() {
    if ( 'az-theme-advanced' !== sanitize_key( wp_unslash( $_GET['page'] ?? '' ) ) ) { return; }
    $path = get_stylesheet_directory() . '/css/header-admin.css';
    wp_enqueue_style( 'az-child-header-admin', get_stylesheet_directory_uri() . '/css/header-admin.css', array( 'az-toggle-titles' ), filemtime( $path ) );
}
add_action( 'admin_enqueue_scripts', 'az_child_header_admin_assets', 30 );

function az_child_header_options() {
    $options = wp_parse_args( az_header_options(), array( 'show_hotline' => 1, 'hotline' => az_get_phone(), 'hotline_label' => 'Hotline tư vấn và đặt hàng' ) );
    if ( function_exists( 'az_child_soviban_header_defaults' ) ) {
        $saved = az_get_option( 'header', array() );
        $saved = is_array( $saved ) ? $saved : array();
        foreach ( az_child_soviban_header_defaults() as $key => $value ) {
            if ( ! array_key_exists( $key, $saved ) ) { $options[ $key ] = $value; }
        }
    }
    $options['height'] = max( 64, min( 180, absint( $options['height'] ) ) );
    return $options;
}

/** Shared controls keep desktop, sticky and branded mobile options consistent. */
function az_child_header_show_cart( $options ) {
    return az_is_woocommerce_active() && ! empty( $options['show_cart'] )
        && ( ! function_exists( 'az_storefront_show_purchase_actions' ) || az_storefront_show_purchase_actions() );
}

function az_child_render_header_utilities( $options, $class = 'az-shared-utilities' ) {
    $hotline = ! empty( $options['show_hotline'] ) && '' !== trim( (string) $options['hotline'] );
    $cart = az_child_header_show_cart( $options );
    $cta = ! empty( $options['show_cta'] ) && $options['cta_text'] && $options['cta_url'];
    if ( ! $hotline && ! $cart && ! $cta && empty( $options['show_account'] ) ) { return; }
    ?>
    <div class="<?php echo esc_attr( $class ); ?>">
      <?php if ( $hotline ) : ?><a class="az-shared-hotline" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $options['hotline'] ) ); ?>"><?php echo az_child_steel_icon( 'phone' ); ?><span><small><?php echo esc_html( $options['hotline_label'] ); ?></small><strong><?php echo esc_html( $options['hotline'] ); ?></strong></span></a><?php endif; ?>
      <?php if ( ! empty( $options['show_account'] ) ) { az_render_header_action( 'account', array( 'label' => $options['account_label'], 'url' => az_is_woocommerce_active() ? wc_get_page_permalink( 'myaccount' ) : home_url( '/tai-khoan/' ) ) ); } ?>
      <?php if ( $cart ) { az_render_header_action( 'cart', array( 'label' => $options['cart_label'] ) ); } ?>
      <?php if ( $cta ) { az_render_header_action( 'cta', array( 'label' => $options['cta_text'], 'url' => $options['cta_url'] ) ); } ?>
    </div>
    <?php
}

function az_child_render_header_search( $options, $class = 'az-steel-search', $id = 'az-shared-search' ) {
    ?>
    <form class="<?php echo esc_attr( $class ); ?>" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
      <label class="az-sr-only" for="<?php echo esc_attr( $id ); ?>">Tìm kiếm</label>
      <input id="<?php echo esc_attr( $id ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( $options['search_placeholder'] ); ?>">
      <?php if ( az_is_woocommerce_active() && 'products' === ( $options['child_search_scope'] ?? 'products' ) ) : ?><input type="hidden" name="post_type" value="product"><?php endif; ?>
      <button class="az-button az-steel-search__button" type="submit" aria-label="Tìm kiếm"><?php echo az_child_steel_icon( 'search' ); ?></button>
    </form>
    <?php
}

/** The child owns this layout; the parent's scroll script still controls visibility. */
function az_child_render_sticky_header( $options ) {
    if ( empty( $options['sticky'] ) ) { return; }
    $top = ! empty( $options['sticky_top_bar'] ) && ! empty( $options['top_bar'] );
    $main = ! empty( $options['sticky_header_main'] );
    $bottom = ! empty( $options['sticky_header_bottom'] ) && ! empty( $options['show_primary_menu'] )
        && ( az_header_menu_id( 'sticky', $options ) || has_nav_menu( 'primary' ) );
    if ( ! $top && ! $main && ! $bottom ) { return; }
    ?>
    <div class="az-sticky-header az-child-sticky-header" data-az-sticky-header aria-hidden="true">
      <?php if ( $top ) : ?><div class="az-sticky-topbar"><div class="az-steel-container az-sticky-topbar__inner"><span><?php echo esc_html( $options['top_bar_text'] ); ?></span><?php if ( $options['top_menu'] || has_nav_menu( 'top' ) ) : ?><nav aria-label="Menu sticky thanh trên"><?php az_child_steel_top_menu( $options ); ?></nav><?php endif; ?></div></div><?php endif; ?>
      <?php if ( $main ) : ?><div class="az-sticky-main"><div class="az-steel-container az-sticky-main__inner">
        <?php az_render_header_logo( array( 'class' => 'az-sticky-header__logo', 'logo_id' => az_get_logo_id( 'sticky' ) ?: az_get_logo_id() ) ); ?>
        <?php if ( ! empty( $options['sticky_show_search'] ) ) { az_child_render_header_search( $options, 'az-steel-search az-sticky-header__search', 'az-sticky-search' ); } ?>
        <?php az_child_render_header_utilities( $options, 'az-shared-utilities az-sticky-header__actions' ); ?>
      </div></div><?php endif; ?>
      <?php if ( $bottom ) : ?><div class="az-sticky-bottom"><div class="az-steel-container az-sticky-bottom__inner"><nav aria-label="Menu sticky">
        <?php az_child_steel_main_menu( 'az-sticky-menu', $options, 'sticky' ); ?>
      </nav></div></div><?php endif; ?>
    </div>
    <?php
}

/** Use the parent's supported styles and add the child utility controls. */
function az_child_render_mobile_menu( $options ) {
    $style = az_mobile_menu_style();
    if ( ! in_array( $style, array( 'brand_right', 'brand_dropdown' ), true ) ) { az_render_mobile_menu(); return; }
    static $rendered = false;
    if ( $rendered ) { return; }
    $menu = az_mobile_menu_markup();
    if ( ! $menu ) { return; }
    $rendered = true;
    $logo = az_get_logo_id( 'mobile' ) ?: az_get_logo_id();
    $sticky = ! empty( $options['sticky'] ) && ! empty( $options['sticky_header_main'] );
    require get_stylesheet_directory() . '/template-parts/mobile-header.php';
}
