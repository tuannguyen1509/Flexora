<?php
/**
 * Reusable header components. Keep markup and accessible labels in one place
 * so header layouts can use the same controls without duplicating their logic.
 */
defined( 'ABSPATH' ) || exit;

function az_header_default_options() {
    return array(
        'primary_menu'         => 0,
        'top_menu'             => 0,
        'category_menu'        => 0,
        'show_primary_menu'    => 1,
        'height'               => 96,
        'top_bar'              => 1,
        'top_bar_text'         => '',
        'sticky'               => 0,
        'sticky_top_bar'       => 0,
        'sticky_header_main'   => 0,
        'sticky_header_bottom' => 1,
        'sticky_menu'          => 0,
        'sticky_show_search'   => 0,
        'transparent'          => 0,
        'show_search'          => 1,
        'show_category'        => 1,
        'show_account'         => 1,
        'show_cart'            => 1,
        'category_button_text' => 'Danh mục sản phẩm',
        'search_placeholder'   => 'Nhập sản phẩm cần tìm...',
        'account_label'        => 'Tài khoản',
        'cart_label'           => 'Giỏ hàng',
        'show_cta'             => 0,
        'cta_text'             => '',
        'cta_url'              => '',
    );
}

function az_header_options() {
    $options = az_get_options();
    $saved   = isset( $options['header'] ) && is_array( $options['header'] ) ? $options['header'] : array();

    return wp_parse_args( $saved, az_header_default_options() );
}

/** Resolve the shared product-category menu used by the header and homepage. */
function az_product_category_menu_id( $fallback_menu_id = 0 ) {
    $menu = wp_get_nav_menu_object( 'Danh mục sản phẩm' );
    if ( ! $menu ) {
        $menu = wp_get_nav_menu_object( 'danh-muc-san-pham' );
    }

    return $menu instanceof WP_Term ? (int) $menu->term_id : absint( $fallback_menu_id );
}

function az_render_header_menu( $menu_id = 0, $location = 'primary', $class = 'az-primary-menu' ) {
    $args = array(
        'container'  => false,
        'menu_class' => $class,
        'fallback_cb' => false,
    );

    if ( $menu_id ) {
        $args['menu'] = absint( $menu_id );
    } else {
        $args['theme_location'] = $location;
    }

    wp_nav_menu( $args );
}

function az_header_menu_id( $slot, $options = array() ) {
    $options = $options ? $options : az_header_options();
    $slot    = sanitize_key( $slot );
    if ( 'sticky' === $slot ) {
        return absint( $options['sticky_menu'] ?? 0 ) ?: absint( $options['primary_menu'] ?? 0 );
    }

    return absint( $options[ $slot . '_menu' ] ?? 0 );
}

function az_render_header_logo( $args = array() ) {
    $logo_id = isset( $args['logo_id'] ) ? absint( $args['logo_id'] ) : az_get_logo_id();
    $class   = sanitize_html_class( $args['class'] ?? 'az-logo' );
    $label   = get_bloginfo( 'name' );
    echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( home_url( '/' ) ) . '" aria-label="' . esc_attr( $label ) . '">';
    echo $logo_id ? wp_get_attachment_image( $logo_id, 'full', false, array( 'loading' => 'eager' ) ) : esc_html( $label );
    echo '</a>';
}

function az_render_header_search( $options = array(), $class = 'az-search' ) {
    $placeholder = $options['search_placeholder'] ?? 'Tìm kiếm...';
    echo '<form class="' . esc_attr( sanitize_html_class( $class ) ) . '" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '"><label class="screen-reader-text" for="az-header-search">Tìm kiếm</label><input id="az-header-search" type="search" name="s" value="' . esc_attr( get_search_query() ) . '" placeholder="' . esc_attr( $placeholder ) . '">';
    if ( az_is_woocommerce_active() ) {
        echo '<input type="hidden" name="post_type" value="product">';
    }
    echo '<button type="submit" aria-label="Tìm kiếm"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="5.7"></circle><path d="m15.1 15.1 4.5 4.5"></path></svg></button></form>';
}

function az_render_sticky_header( $options = array() ) {
    $options = $options ? $options : az_header_options();
    if ( empty( $options['sticky'] ) ) {
        return;
    }

    $show_top    = ! empty( $options['sticky_top_bar'] ) && ! empty( $options['top_bar'] );
    $show_main   = ! empty( $options['sticky_header_main'] );
    $show_bottom = ! empty( $options['sticky_header_bottom'] ) && ! empty( $options['show_primary_menu'] );
    if ( ! $show_top && ! $show_main && ! $show_bottom ) {
        return;
    }

    echo '<div class="az-sticky-header" data-az-sticky-header aria-hidden="true">';
    if ( $show_top ) {
        echo '<div class="az-sticky-topbar"><div class="az-wrap az-sticky-topbar__inner"><span>' . esc_html( $options['top_bar_text'] ?? '' ) . '</span>';
        if ( ! empty( $options['top_menu'] ) || has_nav_menu( 'top' ) ) {
            echo '<nav aria-label="Menu sticky thanh trên">';
            az_render_header_menu( absint( $options['top_menu'] ?? 0 ), 'top', 'az-topbar-menu' );
            echo '</nav>';
        }
        echo '</div></div>';
    }
    if ( $show_main ) {
        echo '<div class="az-sticky-main"><div class="az-wrap az-sticky-main__inner">';
        az_render_header_logo( array( 'class' => 'az-sticky-header__logo' ) );
        if ( ! empty( $options['sticky_show_search'] ) ) { az_render_header_search( $options, 'az-sticky-header__search' ); }
        echo '<div class="az-sticky-header__actions">';
        if ( ! empty( $options['show_account'] ) ) { az_render_header_action( 'account', array( 'label' => $options['account_label'] ?? 'Tài khoản' ) ); }
        if ( az_is_woocommerce_active() && ! empty( $options['show_cart'] ) && ( ! function_exists( 'az_storefront_show_purchase_actions' ) || az_storefront_show_purchase_actions() ) ) { az_render_header_action( 'cart', array( 'label' => $options['cart_label'] ?? 'Giỏ hàng' ) ); }
        echo '</div></div></div>';
    }
    if ( $show_bottom ) {
        $menu_id = az_header_menu_id( 'sticky', $options );
        if ( $menu_id || has_nav_menu( 'primary' ) ) {
            echo '<div class="az-sticky-bottom"><div class="az-wrap az-sticky-bottom__inner"><nav aria-label="Menu sticky">';
            az_render_header_menu( $menu_id, 'primary', 'az-sticky-menu' );
            echo '</nav></div></div>';
        }
    }
    echo '</div>';
}

function az_render_header_action( $type, $args = array() ) {
    $type = sanitize_key( $type );
    $class = 'az-header-action az-header-action--' . $type;

    if ( 'account' === $type ) {
        $url   = $args['url'] ?? home_url( '/tai-khoan/' );
        $label = $args['label'] ?? 'Tài khoản';
        echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '"><span>' . esc_html( $label ) . '</span><svg class="az-header-action__icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.7"></circle><path d="M4.5 20c.8-4 3.3-6 7.5-6s6.7 2 7.5 6"></path></svg></a>';
        return;
    }

    if ( 'cart' === $type ) {
        if ( function_exists( 'az_storefront_show_purchase_actions' ) && ! az_storefront_show_purchase_actions() ) {
            return;
        }
        $url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
        $count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
        $label = $args['label'] ?? 'Giỏ hàng';
        echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '" aria-label="' . esc_attr( sprintf( '%s có %d sản phẩm', $label, $count ) ) . '"><span>' . esc_html( $label ) . '</span><svg class="az-header-action__icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.1 10.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 1.9-1.4L20 8H6"></path><circle cx="9" cy="20" r="1"></circle><circle cx="17" cy="20" r="1"></circle></svg>' . ( $count ? '<b>' . esc_html( $count ) . '</b>' : '' ) . '</a>';
        return;
    }

    if ( 'cta' === $type ) {
        $url   = $args['url'] ?? '';
        $label = $args['label'] ?? '';
        if ( ! $url || ! $label ) {
            return;
        }
        echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '"><span>' . esc_html( $label ) . '</span><svg class="az-header-action__icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></a>';
    }
}
