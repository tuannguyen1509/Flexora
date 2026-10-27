<?php
/** Shared mobile navigation for parent and bundled child headers. */
defined( 'ABSPATH' ) || exit;
/** Removed or unset styles use style 1 without changing saved menu assignments. */
function az_mobile_menu_style() {
    $style = az_get_option( 'header.mobile_menu_style', 'brand_right' );
    return in_array( $style, array( 'none', 'brand_right', 'brand_dropdown' ), true ) ? $style : 'brand_right';
}
function az_mobile_menu_markup() {
    if ( ! az_get_option( 'header.mobile_menu_enabled', 1 ) || 'none' === az_mobile_menu_style() ) { return ''; }
    $menu = absint( az_get_option( 'header.mobile_menu', 0 ) ) ?: absint( az_get_option( 'header.primary_menu', 0 ) );
    return (string) wp_nav_menu( array( 'menu' => $menu, 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'az-mobile-menu__list', 'menu_id' => 'az-mobile-navigation', 'fallback_cb' => false, 'echo' => false, 'depth' => 0 ) );
}
function az_render_mobile_menu() {
    static $rendered = false;
    if ( $rendered ) { return; }
    $menu = az_mobile_menu_markup();
    if ( ! $menu ) { return; }
    $rendered = true;
    az_render_mobile_brand_menu( $menu, az_mobile_menu_style() );
}
/** Compact mobile header and branded overlay, using the configured logo/menu. */
function az_render_mobile_brand_menu( $menu, $style = 'brand_right' ) {
    $style = 'brand_dropdown' === $style ? 'brand_dropdown' : 'brand_right';
    $logo_id = az_get_logo_id( 'mobile' ) ?: az_get_logo_id();
    $sticky = az_get_option( 'header.sticky', 0 ) && az_get_option( 'header.sticky_header_main', 0 );
    echo '<div class="az-mobile-header' . ( $sticky ? ' is-sticky' : '' ) . ( 'brand_dropdown' === $style ? ' az-mobile-header--brand_dropdown' : '' ) . '">';
    az_render_header_logo( array( 'logo_id' => $logo_id, 'class' => 'az-mobile-header__logo' ) );
    echo '<details class="az-mobile-menu az-mobile-menu--' . esc_attr( $style ) . '"><summary class="az-mobile-menu__toggle" aria-controls="az-mobile-menu-panel" aria-label="' . esc_attr__( 'Mở menu', 'az-ai-theme' ) . '"><svg viewBox="0 0 32 32" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 8h22M8 16h19M11 24h16"/></svg></summary><div class="az-mobile-menu__content"><button class="az-mobile-menu__shade" type="button" tabindex="-1" aria-label="' . esc_attr__( 'Đóng menu', 'az-ai-theme' ) . '"></button><div id="az-mobile-menu-panel" class="az-mobile-menu__panel" role="dialog" aria-modal="true" aria-label="' . esc_attr__( 'Menu Mobile', 'az-ai-theme' ) . '" data-submenu-open-label="' . esc_attr__( 'Mở menu con', 'az-ai-theme' ) . '" data-submenu-close-label="' . esc_attr__( 'Đóng menu con', 'az-ai-theme' ) . '"><div class="az-mobile-menu__heading">';
    az_render_header_logo( array( 'logo_id' => $logo_id, 'class' => 'az-mobile-menu__brand' ) );
    echo '<button class="az-mobile-menu__close" type="button" aria-label="' . esc_attr__( 'Đóng menu', 'az-ai-theme' ) . '"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m6 6 12 12M18 6 6 18"/></svg></button></div><nav aria-label="' . esc_attr__( 'Menu Mobile', 'az-ai-theme' ) . '">' . $menu . '</nav></div></div></details></div>';
}
add_action( 'body_class', function( $classes ) {
    if ( az_get_option( 'header.mobile_menu_enabled', 1 ) && 'none' === az_mobile_menu_style() ) { $classes[] = 'az-mobile-menu-none'; }
    if ( az_mobile_menu_markup() ) {
        $classes[] = 'az-has-mobile-menu';
        if ( 'brand_right' === az_mobile_menu_style() ) { $classes[] = 'az-mobile-menu-brand-right'; }
        if ( 'brand_dropdown' === az_mobile_menu_style() ) { $classes[] = 'az-mobile-menu-brand-dropdown'; }
    }
    return $classes;
} );
add_action( 'wp_enqueue_scripts', function() {
    if ( ! az_get_option( 'header.mobile_menu_enabled', 1 ) ) { return; }
    foreach ( array( 'css/mobile-menu.css', 'js/mobile-menu.js' ) as $file ) {
        $url = get_template_directory_uri() . '/' . $file;
        $version = substr( hash_file( 'sha256', get_template_directory() . '/' . $file ), 0, 12 );
        if ( substr( $file, -4 ) === '.css' ) { wp_enqueue_style( 'az-mobile-menu', $url, array(), $version ); }
        else { wp_enqueue_script( 'az-mobile-menu', $url, array(), $version, true ); }
    }
    if ( 'brand_dropdown' === az_mobile_menu_style() ) {
        $file = 'css/mobile-menu-brand-dropdown.css';
        wp_enqueue_style( 'az-mobile-menu-brand-dropdown', get_template_directory_uri() . '/' . $file, array( 'az-mobile-menu' ), substr( hash_file( 'sha256', get_template_directory() . '/' . $file ), 0, 12 ) );
    }
}, 110 );
