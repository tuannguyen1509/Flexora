<?php
/** Isolated mobile navigation render/settings checks; no WordPress database. */
defined( 'ABSPATH' ) || define( 'ABSPATH', __DIR__ );
$options = $options ?? array( 'header.mobile_menu_style' => 'brand_right', 'header.mobile_menu' => 12 );
$hooks = array();
$menu_exists = true;
$mobile_logo = 22;
$desktop_logo = 11;
$menu_args = array();
$enqueued_styles = array();
$enqueued_scripts = array();
function az_get_option( $key, $default = null ) { return $GLOBALS['options'][ $key ] ?? $default; }
function add_action( $hook, $callback ) { $GLOBALS['hooks'][ $hook ] = $callback; }
function absint( $value ) { return abs( (int) $value ); }
function sanitize_key( $value ) { return $value; }
function sanitize_html_class( $value ) { return $value; }
function esc_attr( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $value ) { return esc_attr( $value ); }
function esc_url( $value ) { return esc_attr( $value ); }
function selected( $value, $expected, $echo = false ) { return (string) $value === (string) $expected ? 'selected="selected"' : ''; }
function esc_attr__( $text, $domain ) { return esc_attr( $text ); }
function esc_html__( $text, $domain ) { return esc_html( $text ); }
function home_url( $path ) { return '/' === $path ? '/' : $path; }
function get_bloginfo( $key ) { return 'AZ Theme'; }
function az_get_logo_id( $variant = 'desktop' ) { return 'mobile' === $variant ? $GLOBALS['mobile_logo'] : $GLOBALS['desktop_logo']; }
function wp_get_attachment_image( $id, ...$args ) { return '<img src="logo.svg" alt="AZ Theme" data-logo-id="' . $id . '">'; }
function wp_get_nav_menus() { return array( (object) array( 'term_id' => 12, 'name' => 'Main-menu' ) ); }
function get_template_directory() { return dirname( __DIR__ ); }
function get_template_directory_uri() { return '/wp-content/themes/az-theme'; }
function wp_enqueue_style( $handle, $url, $dependencies = array(), $version = null ) { $GLOBALS['enqueued_styles'][ $handle ] = array( 'url' => $url, 'dependencies' => $dependencies, 'version' => $version ); }
function wp_enqueue_script( $handle, $url, $dependencies = array(), $version = null, $footer = false ) { $GLOBALS['enqueued_scripts'][ $handle ] = array( 'url' => $url, 'version' => $version ); }
function wp_nav_menu( $args ) {
    $GLOBALS['menu_args'] = $args;
    if ( ! $GLOBALS['menu_exists'] ) { return ''; }
    $items = array( 'Về chúng tôi', 'Vấn đề thường gặp', 'Giải pháp', 'Tối ưu', 'Quy trình', 'Chi phí trọn gói', 'Bảng giá chi tiết', 'Liên hệ' );
    $html = '<ul id="az-mobile-navigation" class="az-mobile-menu__list">';
    foreach ( $items as $index => $label ) {
        $html .= '<li><a href="#section-' . $index . '">' . $label . '</a>';
        if ( ! empty( $GLOBALS['preview_submenu'] ) && 2 === $index ) { $html .= '<ul class="sub-menu"><li><a href="#section-2">Thiết kế website</a></li></ul>'; }
        $html .= '</li>';
    }
    return $html . '</ul>';
}
function check( $ok, $message ) {
    if ( ! $ok ) { throw new RuntimeException( $message ); }
    echo "PASS $message\n";
}
require dirname( __DIR__ ) . '/inc/helpers/header-components.php';
require dirname( __DIR__ ) . '/inc/core/mobile-menu.php';
require dirname( __DIR__ ) . '/inc/options/class-sanitizer.php';
require dirname( __DIR__ ) . '/inc/options/class-renderer.php';
ob_start();
az_render_mobile_menu();
$html = ob_get_clean();
if ( defined( 'AZ_MOBILE_MENU_PREVIEW' ) ) { return; }

check( strpos( $html, 'az-mobile-menu--brand_right' ) !== false, 'new style renders branded right drawer' );
check( strpos( $html, 'az-mobile-header__logo' ) !== false && strpos( $html, 'az-mobile-menu__brand' ) !== false, 'header and drawer both render branding' );
check( substr_count( $html, 'data-logo-id="22"' ) === 2, 'mobile logo is used in both positions' );
check( substr_count( $html, 'id="az-mobile-navigation"' ) === 1 && substr_count( $html, 'id="az-mobile-menu-panel"' ) === 1, 'navigation IDs remain unique' );
check( strpos( $html, 'aria-label="Mở menu"' ) !== false && strpos( $html, 'role="dialog" aria-modal="true"' ) !== false, 'icon trigger and modal panel have accessible labels' );
check( strpos( $html, 'Vấn đề thường gặp' ) !== false && $menu_args['menu'] === 12, 'drawer uses selected WordPress menu' );
check( $menu_args['fallback_cb'] === false && $menu_args['depth'] === 0, 'preserves full menu hierarchy and no fallback pages' );
ob_start(); az_render_mobile_menu(); $duplicate = ob_get_clean();
check( '' === $duplicate, 'multiple header calls do not duplicate drawer' );
$classes = $hooks['body_class']( array() );
check( in_array( 'az-mobile-menu-brand-right', $classes, true ), 'compact mobile header class is opt-in' );

$mobile_logo = 0;
ob_start(); az_render_mobile_brand_menu( '<ul></ul>' ); $fallback = ob_get_clean();
check( substr_count( $fallback, 'data-logo-id="11"' ) === 2, 'falls back to desktop logo when mobile logo is absent' );
$desktop_logo = 0;
ob_start(); az_render_mobile_brand_menu( '<ul></ul>' ); $fallback = ob_get_clean();
check( strpos( $fallback, '<img' ) === false && substr_count( $fallback, '>AZ Theme</a>' ) === 2, 'site name remains usable when no logo is configured' );
$options['header.sticky'] = 1;
$options['header.sticky_header_main'] = 1;
ob_start(); az_render_mobile_brand_menu( '<ul></ul>' ); $sticky = ob_get_clean();
check( strpos( $sticky, 'az-mobile-header is-sticky' ) !== false, 'respects sticky Header main setting' );

foreach ( array( 'left', 'right', 'dropdown', 'unknown' ) as $style ) {
    $options['header.mobile_menu_style'] = $style;
    check( 'brand_right' === az_mobile_menu_style() && in_array( 'az-mobile-menu-brand-right', $hooks['body_class']( array() ), true ), 'removed/invalid style uses style 1: ' . $style );
}
unset( $options['header.mobile_menu_style'] );
check( 'brand_right' === az_mobile_menu_style(), 'new installs use style 1' );
$options['header.mobile_menu_style'] = 'brand_right';
$options['header.mobile_menu_enabled'] = 0;
check( '' === az_mobile_menu_markup() && ! in_array( 'az-mobile-menu-brand-right', $hooks['body_class']( array() ), true ), 'disabled menu keeps original header visible' );
$options['header.mobile_menu_enabled'] = 1;
$options['header.mobile_menu_style'] = 'none';
check( '' === az_mobile_menu_markup(), 'None still hides menu' );
$options['header.mobile_menu_style'] = 'brand_right';
$menu_exists = false;
check( '' === az_mobile_menu_markup() && ! in_array( 'az-mobile-menu-brand-right', $hooks['body_class']( array() ), true ), 'missing menu never hides original header' );
$menu_exists = true;
$options['header.mobile_menu'] = 0;
$options['header.primary_menu'] = 9;
az_mobile_menu_markup();
check( $menu_args['menu'] === 9, 'falls back to configured primary menu' );
$options['header.primary_menu'] = 0;
az_mobile_menu_markup();
check( $menu_args['menu'] === 0 && $menu_args['theme_location'] === 'primary', 'uses primary location when no explicit menu is chosen' );
$fields = require dirname( __DIR__ ) . '/inc/admin/sections/header.php';
$field = array_values( array_filter( $fields, static function ( $item ) { return 'mobile_menu_style' === $item['id']; } ) )[0];
check( AZ_Options_Sanitizer::sanitize( 'brand_right', 'select', $field ) === 'brand_right', 'Advanced save accepts new style' );
check( array_keys( $field['options'] ) === array( 'none', 'brand_right', 'brand_dropdown' ), 'Advanced offers only None and styles 1 and 2' );
check( strpos( $field['options']['brand_right'], 'Kiểu 1 —' ) === 0 && strpos( $field['options']['brand_dropdown'], 'Kiểu 2 —' ) === 0, 'styles are numbered 1 and 2 without changing saved option keys' );
check( $field['default'] === 'brand_right' && AZ_Options_Sanitizer::sanitize( 'bad', 'select', $field ) === 'brand_right', 'default/reset and unknown submitted style use style 1' );
check( AZ_Options_Sanitizer::sanitize( 'none', 'select', $field ) === 'none', 'save preserves None' );
foreach ( array( 'left', 'right', 'dropdown' ) as $legacy_style ) {
    check( AZ_Options_Sanitizer::sanitize( $legacy_style, 'select', $field ) === 'brand_right', 'save rejects removed style: ' . $legacy_style );
    ob_start(); AZ_Options_Renderer::field( 'header', $field, array( 'header' => array( 'mobile_menu_style' => $legacy_style ) ) ); $admin = ob_get_clean();
    check( strpos( $admin, 'value="brand_right" selected="selected"' ) !== false, 'admin reflects style 1 for saved legacy value: ' . $legacy_style );
}

$options['header.mobile_menu_style'] = 'brand_dropdown';
$options['header.mobile_menu'] = 12;
$mobile_logo = 22;
ob_start(); az_render_mobile_brand_menu( az_mobile_menu_markup(), 'brand_dropdown' ); $coffee = ob_get_clean();
check( strpos( $coffee, 'az-mobile-header--brand_dropdown' ) !== false && strpos( $coffee, 'az-mobile-menu--brand_dropdown' ) !== false, 'coffee style renders compact header and its own overlay' );
check( substr_count( $coffee, 'data-logo-id="22"' ) === 2, 'coffee style uses configured mobile logo in header and overlay' );
check( strpos( $coffee, 'Vấn đề thường gặp' ) !== false && $menu_args['menu'] === 12, 'coffee style uses existing selected menu content' );
check( strpos( $coffee, 'data-submenu-open-label="Mở menu con"' ) !== false && strpos( $coffee, 'data-submenu-close-label="Đóng menu con"' ) !== false, 'submenu labels are supplied by WordPress translation functions' );
$classes = $hooks['body_class']( array() );
check( in_array( 'az-mobile-menu-brand-dropdown', $classes, true ) && ! in_array( 'az-mobile-menu-brand-right', $classes, true ), 'coffee header class is separate from right drawer' );
check( AZ_Options_Sanitizer::sanitize( 'brand_dropdown', 'select', $field ) === 'brand_dropdown', 'Advanced save accepts coffee style' );
$options['header.mobile_menu_enabled'] = 0;
check( ! in_array( 'az-mobile-menu-brand-dropdown', $hooks['body_class']( array() ), true ), 'disabled coffee menu preserves original header' );
$options['header.mobile_menu_enabled'] = 1;
$menu_exists = false;
check( ! in_array( 'az-mobile-menu-brand-dropdown', $hooks['body_class']( array() ), true ), 'missing coffee menu preserves original header' );

check( substr_count( $coffee, 'fill="none" stroke="currentColor"' ) === 2, 'menu and close icons carry their own stroke without external CSS' );
$hooks['wp_enqueue_scripts']();
check( isset( $enqueued_styles['az-mobile-menu-brand-dropdown'] ), 'style 2 registers a dedicated stylesheet' );
check( $enqueued_styles['az-mobile-menu-brand-dropdown']['dependencies'] === array( 'az-mobile-menu' ), 'style 2 stylesheet follows shared drawer rules' );
$file = get_template_directory() . '/css/mobile-menu-brand-dropdown.css';
check( is_file( $file ) && $enqueued_styles['az-mobile-menu-brand-dropdown']['version'] === substr( hash_file( 'sha256', $file ), 0, 12 ), 'style 2 CSS version is derived from deployed content' );
check( $enqueued_styles['az-mobile-menu']['version'] === substr( hash_file( 'sha256', get_template_directory() . '/css/mobile-menu.css' ), 0, 12 ), 'shared CSS version is derived from content rather than timestamp' );
check( $enqueued_scripts['az-mobile-menu']['version'] === substr( hash_file( 'sha256', get_template_directory() . '/js/mobile-menu.js' ), 0, 12 ), 'mobile JS also uses content version' );
$enqueued_styles = array();
$options['header.mobile_menu_style'] = 'brand_right';
$hooks['wp_enqueue_scripts']();
check( ! isset( $enqueued_styles['az-mobile-menu-brand-dropdown'] ), 'style 1 does not receive style 2 overrides' );
$enqueued_styles = array();
$options['header.mobile_menu_enabled'] = 0;
$hooks['wp_enqueue_scripts']();
check( empty( $enqueued_styles ), 'disabled mobile menu loads no menu styles' );
