<?php
/** Header setting/render regressions without a WordPress database. */
define( 'ABSPATH', __DIR__ );
$theme = dirname( __DIR__ );
$parent = dirname( $theme ) . '/az-theme';
$options = array();
$woo = true;
$purchase = true;
$homepage = true;
$menu_exists = true;
$hooks = array();
function add_action( $name, $callback, ...$args ) { $GLOBALS['hooks'][ $name ][] = $callback; }
function add_filter( ...$args ) {}
function az_get_options() { return $GLOBALS['options']; }
function az_get_option( $path, $fallback = null ) { $value = az_get_options(); foreach ( explode( '.', $path ) as $key ) { if ( ! is_array( $value ) || ! array_key_exists( $key, $value ) ) { return $fallback; } $value = $value[ $key ]; } return $value; }
function az_get_phone() { return '0900 111 222'; }
function az_get_logo_id( $variant = 'desktop' ) { return array( 'desktop' => 11, 'mobile' => 22, 'sticky' => 33 )[ $variant ] ?? 0; }
function absint( $value ) { return abs( (int) $value ); }
function wp_parse_args( $values, $defaults ) { return array_merge( $defaults, $values ); }
function esc_attr( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $value ) { return esc_attr( $value ); }
function esc_url( $value ) { return esc_attr( $value ); }
function esc_attr__( $value, $domain ) { return esc_attr( $value ); }
function esc_html__( $value, $domain ) { return esc_html( $value ); }
function sanitize_key( $value ) { return $value; }
function sanitize_html_class( $value ) { return $value; }
function sanitize_hex_color( $value ) { return preg_match( '/^#(?:[a-f\d]{3}|[a-f\d]{6})$/i', (string) $value ) ? $value : null; }
function get_stylesheet_directory() { return $GLOBALS['theme']; }
function get_stylesheet_directory_uri() { return '/theme'; }
function home_url( $path = '/' ) { return $path; }
function get_bloginfo( $name ) { return 'AZ Header Test'; }
function language_attributes() { echo 'lang="vi"'; }
function bloginfo( $name ) { echo 'UTF-8'; }
function body_class() { $classes = array(); foreach ( $GLOBALS['hooks']['body_class'] ?? array() as $callback ) { $classes = $callback( $classes ); } echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"'; }
function wp_body_open() {}
function do_action( ...$args ) {}
function wp_head() {
    echo '<link rel="stylesheet" href="/parent/style.css"><link rel="stylesheet" href="/parent/css/mobile-menu.css"><link rel="stylesheet" href="/parent/css/mobile-menu-brand-dropdown.css">';
    foreach ( array( 'steel-storefront', 'header-storefront', 'home-reference-layout' ) as $name ) { echo '<link rel="stylesheet" href="/theme/css/' . $name . '.css">'; }
    echo '<style>' . az_child_menu_colors_css() . '</style>';
}
function get_search_query() { return ''; }
function is_admin() { return false; }
function is_front_page() { return $GLOBALS['homepage']; }
function is_page_template( $template ) { return false; }
function az_is_woocommerce_active() { return $GLOBALS['woo']; }
function az_storefront_show_purchase_actions() { return $GLOBALS['purchase']; }
function wc_get_page_permalink( $name ) { return '/woo/' . $name . '/'; }
function wc_get_cart_url() { return '/woo/cart/'; }
function WC() { return (object) array( 'cart' => new class { function get_cart_contents_count() { return 3; } } ); }
function wp_get_attachment_image( $id, ...$args ) { return '<img src="/theme/assets/images/solar-home.svg" alt="Logo test" data-logo-id="' . $id . '">'; }
function has_nav_menu( $location ) { return $GLOBALS['menu_exists']; }
function wp_get_nav_menu_object( $name ) { return false; }
function wp_nav_menu( $args ) {
    if ( ! $GLOBALS['menu_exists'] ) { return ''; }
    $id = $args['menu'] ?? 0;
    $class = $args['menu_class'] ?? '';
    $html = '<ul class="' . $class . '" data-menu="' . $id . '" data-depth="' . ( $args['depth'] ?? 0 ) . '"><li class="current-menu-item"><a href="#first">Menu ' . $id . '</a><ul class="sub-menu"><li><a href="#second">Menu con</a></li></ul></li><li><a href="#third">Liên hệ</a></li></ul>';
    if ( isset( $args['echo'] ) && ! $args['echo'] ) { return $html; }
    echo $html;
    return $html;
}
function taxonomy_exists( $name ) { return false; }
function az_home_page_get_data( ...$args ) { return array( 'products' => array( 'sections' => array( array( 'title' => 'SẢN PHẨM' ) ) ) ); }
function get_option( $name, $fallback = false ) { return $fallback; }
function get_terms( $args ) { return array(); }
function is_wp_error( $value ) { return false; }

require $parent . '/inc/helpers/header-components.php';
require $parent . '/inc/core/mobile-menu.php';
require $theme . '/inc/steel-storefront.php';
require $theme . '/inc/home-storefront.php';
require $theme . '/inc/header-storefront.php';
require $theme . '/inc/header-colors.php';

function base_options() {
    return array( 'header' => array_merge( az_header_default_options(), array( 'mobile_menu_enabled' => 0, 'mobile_menu_style' => 'left', 'primary_menu' => 10, 'category_menu' => 20, 'top_menu' => 30, 'top_bar_text' => 'Top text', 'height' => 150, 'show_hotline' => 1, 'hotline' => '0900 333 444', 'hotline_label' => 'Tư vấn', 'show_cta' => 1, 'cta_text' => 'Đặt lịch', 'cta_url' => '/dat-lich/', 'account_label' => 'Khách hàng', 'cart_label' => 'Giỏ của tôi', 'search_placeholder' => 'Tìm thiết bị', 'category_button_text' => 'Chọn danh mục' ) ) );
}
function render_header( $overrides = array() ) {
    $GLOBALS['options'] = array_replace_recursive( base_options(), $overrides );
    ob_start(); require $GLOBALS['theme'] . '/header.php'; return ob_get_clean();
}
function check( $ok, $message ) { if ( ! $ok ) { throw new RuntimeException( $message ); } echo "PASS $message\n"; }
function capture( $callback ) { ob_start(); $callback(); return ob_get_clean(); }

if ( isset( $argv[1] ) && '--preview' === $argv[1] ) {
    $style = $argv[2] ?? 'left';
    $height = max( 64, min( 180, absint( $argv[4] ?? 150 ) ) );
    $GLOBALS['homepage'] = ( $argv[5] ?? 'home' ) !== 'inner';
    echo render_header( array( 'header' => array( 'mobile_menu_enabled' => 1, 'mobile_menu_style' => $style, 'mobile_menu' => 40, 'sticky' => 1, 'sticky_top_bar' => 1, 'sticky_header_main' => 1, 'sticky_header_bottom' => 1, 'sticky_show_search' => 1, 'sticky_menu' => 50, 'transparent' => (int) ( $argv[3] ?? 0 ), 'height' => $height, 'show_category' => (int) ( $argv[6] ?? 1 ) ), 'colors' => array( 'menu_background' => '#336699', 'menu_text' => '#ffffff', 'menu_category_background' => '#224466' ) ) );
    echo '<main id="main" style="padding:40px;min-height:1600px;background:#eef4f6"><h1>Bản kiểm tra Header</h1><p>Header main ' . $height . 'px · Tìm thiết bị · Tư vấn · Khách hàng · Giỏ của tôi · Đặt lịch</p><a id="first" href="#third">Cuộn để kiểm tra sticky</a><div id="third" style="margin-top:700px">Nội dung kiểm tra</div></main></div><script src="/parent/js/mobile-menu.js"></script><script src="/parent/js/az-sticky-header.js"></script><script src="/theme/js/steel-storefront.js"></script></body></html>';
    exit;
}
if ( isset( $argv[1] ) && '--mobile' === $argv[1] ) {
    $GLOBALS['options'] = base_options();
    $GLOBALS['options']['header'] = array_merge( $GLOBALS['options']['header'], array( 'mobile_menu_enabled' => 1, 'mobile_menu_style' => $argv[2], 'mobile_menu' => 40, 'sticky' => 1, 'sticky_header_main' => 1 ) );
    $off = ( $argv[3] ?? '' ) === 'off';
    if ( $off ) { foreach ( array( 'show_search', 'show_hotline', 'show_account', 'show_cart', 'show_cta', 'show_category' ) as $key ) { $GLOBALS['options']['header'][ $key ] = 0; } }
    $category_off = ( $argv[3] ?? '' ) === 'category-off';
    if ( $category_off ) { $GLOBALS['options']['header']['show_category'] = 0; }
    $html = capture( function() { az_child_render_mobile_menu( az_child_header_options() ); } );
    if ( 'none' === $argv[2] ) { check( '' === $html, 'None hides mobile menu' ); exit; }
    $resolved_style = in_array( $argv[2], array( 'brand_right', 'brand_dropdown' ), true ) ? $argv[2] : 'brand_right';
    check( strpos( $html, 'az-mobile-menu--' . $resolved_style ) !== false, 'mobile style ' . $argv[2] . ' resolves to ' . $resolved_style );
    check( strpos( $html, 'data-menu="40"' ) !== false, 'selected mobile menu ' . $argv[2] );
    if ( $off ) { foreach ( array( 'az-mobile-search', 'az-shared-hotline', 'az-header-action--account', 'az-header-action--cart', 'az-header-action--cta', 'az-child-mobile-category' ) as $marker ) { check( strpos( $html, $marker ) === false, 'mobile turns off ' . $marker ); } }
    elseif ( $category_off ) {
        check( strpos( $html, 'az-child-mobile-category' ) === false && strpos( $html, 'az-steel-category-list' ) === false, 'mobile category switch removes button and menu ' . $argv[2] );
        check( strpos( $html, 'az-mobile-search' ) !== false && strpos( $html, 'az-header-action--account' ) !== false, 'mobile category switch preserves other controls ' . $argv[2] );
    }
    else {
        foreach ( array( 'az-mobile-search', '0900 333 444', 'az-header-action--account', 'az-header-action--cart', 'az-header-action--cta', 'Chọn danh mục', 'is-sticky' ) as $marker ) { check( strpos( $html, $marker ) !== false, 'branded mobile receives ' . $marker ); }
        check( strpos( $html, 'data-logo-id="22"' ) !== false, 'mobile logo variant' );
    }
    exit;
}

$html = render_header();
foreach ( array( '--az-header-main-height:150px', 'Top text', 'data-menu="30"', 'data-menu="20"', 'data-menu="10"', 'Tìm thiết bị', 'Chọn danh mục', 'Khách hàng', '/woo/myaccount/', 'Giỏ của tôi', 'Đặt lịch', '/dat-lich/', '0900 333 444', 'Tư vấn', 'data-depth="0"' ) as $marker ) { check( strpos( $html, $marker ) !== false, 'main header receives ' . $marker ); }
$hidden = array( 'top_bar' => 'az-steel-topbar', 'show_search' => 'az-shared-search', 'show_category' => 'az-steel-category-control', 'show_account' => 'az-header-action--account', 'show_cart' => 'az-header-action--cart', 'show_cta' => 'az-header-action--cta', 'show_hotline' => 'az-shared-hotline', 'show_primary_menu' => 'az-steel-primary' );
foreach ( $hidden as $key => $marker ) { $html = render_header( array( 'header' => array( $key => 0 ) ) ); check( strpos( $html, $marker ) === false, 'turn off ' . $key ); }
// Apply the real Advanced switch sanitizer before rendering each saved state.
require $parent . '/inc/options/class-sanitizer.php';
foreach ( array( true, false ) as $home ) {
    $GLOBALS['homepage'] = $home;
    foreach ( array( '1', '0', '1' ) as $submitted ) {
        $enabled = AZ_Options_Sanitizer::sanitize( $submitted, 'switch' );
        $html = render_header( array( 'header' => array( 'show_category' => $enabled, 'category_menu' => 72 ) ) );
        $menu = capture( function() { az_child_steel_category_links( true ); } );
        $page = $home ? 'home' : 'inner';
        if ( $enabled ) {
            check( strpos( $html, 'az-steel-category-toggle' ) !== false && strpos( $html, 'id="az-steel-categories"' ) !== false, $page . ' saved switch enables category button and dropdown' );
            check( strpos( $menu, 'data-menu="72"' ) !== false, $page . ' re-enabled category uses the selected menu' );
        } else {
            check( strpos( $html, 'az-steel-category-toggle' ) === false && strpos( $html, 'id="az-steel-categories"' ) === false && '' === $menu, $page . ' saved switch removes category button and all category markup' );
            check( strpos( $html, 'az-shared-navrow--no-category' ) !== false && strpos( $html, 'data-menu="10"' ) !== false, $page . ' disabled category preserves primary menu without an empty column' );
            check( 72 === $GLOBALS['options']['header']['category_menu'], $page . ' disabled category preserves menu selection' );
        }
    }
}
$GLOBALS['homepage'] = true;
check( strpos( render_header( array( 'header' => array( 'show_category' => 0, 'show_primary_menu' => 0 ) ) ), 'az-steel-navband' ) === false, 'both menus disabled leave no empty navigation band' );
check( strpos( render_header( array( 'header' => array( 'hotline' => '' ) ) ), 'az-shared-hotline' ) === false, 'empty hotline stays hidden' );
check( strpos( render_header( array( 'header' => array( 'cta_url' => '' ) ) ), 'az-header-action--cta' ) === false, 'incomplete CTA stays hidden' );
check( strpos( render_header( array( 'header' => array( 'transparent' => 1 ) ) ), 'az-shared-header is-transparent' ) !== false, 'transparent header flag' );
$GLOBALS['woo'] = false;
$html = render_header();
check( strpos( $html, 'az-header-action--cart' ) === false && strpos( $html, '/tai-khoan/' ) !== false && strpos( $html, 'name="post_type"' ) === false, 'no WooCommerce: no cart/product search and account fallback' );
$GLOBALS['woo'] = true; $GLOBALS['purchase'] = false;
check( strpos( render_header(), 'az-header-action--cart' ) === false, 'catalogue purchase policy' );
$GLOBALS['purchase'] = true;
$GLOBALS['options'] = base_options();
$header = array_merge( az_child_header_options(), array( 'sticky' => 1, 'sticky_top_bar' => 1, 'sticky_header_main' => 1, 'sticky_header_bottom' => 1, 'sticky_show_search' => 1, 'sticky_menu' => 50 ) );
$html = capture( function() use ( $header ) { az_child_render_sticky_header( $header ); } );
foreach ( array( 'az-sticky-topbar', 'az-sticky-main', 'az-sticky-bottom', 'az-sticky-search', 'data-menu="50"', 'data-logo-id="33"', '/woo/myaccount/', 'az-header-action--cart', 'az-header-action--cta', '0900 333 444' ) as $marker ) { check( strpos( $html, $marker ) !== false, 'sticky receives ' . $marker ); }
foreach ( array( 'sticky' => 'data-az-sticky-header', 'sticky_top_bar' => 'az-sticky-topbar', 'sticky_header_main' => 'az-sticky-main', 'sticky_header_bottom' => 'az-sticky-bottom', 'sticky_show_search' => 'az-sticky-search' ) as $key => $marker ) { $changed = $header; $changed[ $key ] = 0; $html = capture( function() use ( $changed ) { az_child_render_sticky_header( $changed ); } ); check( strpos( $html, $marker ) === false, 'sticky turns off ' . $key ); }
$header['top_bar'] = 0; $header['show_primary_menu'] = 0;
$html = capture( function() use ( $header ) { az_child_render_sticky_header( $header ); } );
check( strpos( $html, 'az-sticky-topbar' ) === false && strpos( $html, 'az-sticky-bottom' ) === false, 'sticky respects disabled top/bottom sources' );
$GLOBALS['options']['header']['hotline'] = '0900 777 888';
$html = render_header( array( 'header' => array( 'primary_menu' => 71, 'category_menu' => 72, 'top_menu' => 73 ) ) );
foreach ( array( 71, 72, 73 ) as $id ) { check( strpos( $html, 'data-menu="' . $id . '"' ) !== false, 'selected menu replaces default ' . $id ); }
$GLOBALS['homepage'] = false;
check( strpos( render_header(), '--az-header-main-height:150px' ) !== false, 'inner page shares Header settings' );
$reference_header = null;
foreach ( array( true, false ) as $home ) {
    $GLOBALS['homepage'] = $home;
    foreach ( array( 'header.php', 'header-shop.php', 'template-parts/header-home.php' ) as $entry ) {
        $GLOBALS['options'] = base_options();
        $html = capture( function() use ( $entry ) { require $GLOBALS['theme'] . '/' . $entry; } );
        preg_match( '/<header class="az-steel-header[\s\S]*?<\/header>/', $html, $match );
        $reference_header = $reference_header ?? ( $match[0] ?? '' );
        check( ! empty( $match[0] ) && $match[0] === $reference_header, ( $home ? 'home' : 'inner' ) . ' ' . $entry . ' renders identical configured header' );
        check( 1 === substr_count( $html, '<!DOCTYPE html>' ) && 1 === substr_count( $html, '<header class="az-steel-header' ), $entry . ' renders one document and one main header' );
    }
}
$GLOBALS['options'] = base_options();
$snapshot = array_merge( az_child_header_options(), array( 'primary_menu' => 81, 'top_menu' => 82, 'category_menu' => 83, 'sticky_menu' => 84 ) );
check( strpos( capture( function() use ( $snapshot ) { az_child_steel_main_menu( 'az-steel-menu', $snapshot ); } ), 'data-menu="81"' ) !== false, 'primary uses supplied Header option snapshot' );
check( strpos( capture( function() use ( $snapshot ) { az_child_steel_main_menu( 'az-sticky-menu', $snapshot, 'sticky' ); } ), 'data-menu="84"' ) !== false, 'sticky uses supplied Header option snapshot' );
check( strpos( capture( function() use ( $snapshot ) { az_child_steel_top_menu( $snapshot ); } ), 'data-menu="82"' ) !== false, 'top bar uses supplied Header option snapshot' );
check( strpos( capture( function() use ( $snapshot ) { az_child_steel_category_links( true, $snapshot ); } ), 'data-menu="83"' ) !== false, 'category uses supplied Header option snapshot' );
$snapshot['show_primary_menu'] = 0;
check( '' === capture( function() use ( $snapshot ) { az_child_steel_main_menu( 'az-steel-menu', $snapshot ); } ), 'supplied disabled primary switch wins over saved enabled setting' );
$GLOBALS['menu_exists'] = false;
foreach ( array( true, false ) as $home ) {
    $GLOBALS['homepage'] = $home;
    $GLOBALS['options'] = array( 'header' => array( 'primary_menu' => 0, 'show_primary_menu' => 1 ) );
    check( '' === capture( function() { az_child_home_menu(); } ) && '' === capture( function() { az_child_steel_main_menu(); } ), ( $home ? 'home' : 'inner' ) . ' missing primary menu never renders hardcoded demo links' );
    $html = render_header( array( 'header' => array( 'primary_menu' => 0, 'show_category' => 0 ) ) );
    check( strpos( $html, 'az-steel-navband' ) === false, ( $home ? 'home' : 'inner' ) . ' no assigned menu leaves no empty navigation band' );
    $options = az_child_header_options();
    $options['sticky'] = 1; $options['sticky_menu'] = 0; $options['sticky_header_main'] = 0; $options['sticky_top_bar'] = 0;
    check( '' === capture( function() use ( $options ) { az_child_render_sticky_header( $options ); } ), 'missing sticky/primary menu leaves no empty sticky band' );
}
$GLOBALS['menu_exists'] = true;
$GLOBALS['options'] = array( 'colors' => array( 'menu_background' => '#112233', 'menu_text' => '#abcdef' ) );
$css = az_child_menu_colors_css();
check( strpos( $css, ':root{--az-menu-background:#112233;--az-menu-text:#abcdef;' ) !== false && strpos( $css, '#page.az-home-storefront' ) === false, 'all pages receive one set of explicitly saved menu colors' );
$GLOBALS['options'] = array();
check( strpos( az_child_menu_colors_css(), ':root{--az-menu-background:#ef1429;' ) !== false, 'unsaved header shares SOVIBAN palette on every page' );
$css = file_get_contents( $GLOBALS['theme'] . '/css/home-reference-layout.css' );
check( strpos( $css, '.az-shared-header' ) === false && strpos( $css, '.az-shared-brandbar' ) === false, 'homepage stylesheet no longer overrides shared header layout' );
$css = file_get_contents( $GLOBALS['theme'] . '/css/header-storefront.css' );
check( preg_match( '/\.az-shared-brandbar\s*\{[^}]*min-height:\s*var\(--az-header-main-height, 96px\)/', $css ) === 1, 'shared height reads setting variable on every page' );
check( strpos( $css, '.az-shared-header.is-transparent .az-steel-navband { background: transparent; }' ) !== false, 'shared transparency overrides palette on every page' );
foreach ( array( 'none', 'left', 'right', 'dropdown', 'brand_right', 'brand_dropdown' ) as $style ) { passthru( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __FILE__ ) . ' --mobile ' . escapeshellarg( $style ), $code ); check( 0 === $code, 'mobile subprocess ' . $style ); }

foreach ( array( 'brand_right', 'brand_dropdown' ) as $style ) { passthru( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __FILE__ ) . ' --mobile ' . escapeshellarg( $style ) . ' off', $code ); check( 0 === $code, 'mobile controls disabled ' . $style ); }
foreach ( array( 'brand_right', 'brand_dropdown' ) as $style ) { passthru( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __FILE__ ) . ' --mobile ' . escapeshellarg( $style ) . ' category-off', $code ); check( 0 === $code, 'mobile category disabled independently ' . $style ); }
