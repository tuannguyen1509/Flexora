<?php
/**
 * Generic parent-theme header.
 *
 * Website-specific headers belong in the active child theme.
 *
 * @package AZ_AI_Theme
 */

defined( 'ABSPATH' ) || exit;

$az_header_options = function_exists( 'az_header_options' ) ? az_header_options() : array();
$az_logo_id        = function_exists( 'az_get_logo_id' ) ? az_get_logo_id() : ( absint( az_get_option( 'branding.logo', 0 ) ) ?: absint( get_theme_mod( 'custom_logo' ) ) );
$az_phones         = function_exists( 'az_get_contact_items' ) ? az_get_contact_items( 'phone' ) : array();
$az_cart_url       = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
$az_cart_count     = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
$az_category_menu  = function_exists( 'az_product_category_menu_id' )
    ? az_product_category_menu_id( $az_header_options['category_menu'] ?? 0 )
    : absint( $az_header_options['category_menu'] ?? 0 );
$az_woo_active     = function_exists( 'az_is_woocommerce_active' ) && az_is_woocommerce_active();
$az_show_cart       = $az_woo_active && ! empty( $az_header_options['show_cart'] ) && ( ! function_exists( 'az_storefront_show_purchase_actions' ) || az_storefront_show_purchase_actions() );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="az-site">
<header class="az-site-header<?php echo ! empty( $az_header_options['transparent'] ) ? ' is-transparent' : ''; ?>" style="--az-header-main-height:<?php echo esc_attr( absint( $az_header_options['height'] ?? 96 ) ); ?>px">
    <?php if ( ! array_key_exists( 'top_bar', $az_header_options ) || ! empty( $az_header_options['top_bar'] ) ) : ?>
        <div class="az-topbar">
            <div class="az-wrap az-topbar-inner">
                <span><?php echo esc_html( $az_header_options['top_bar_text'] ?? '' ); ?></span>
                <?php if ( ! empty( $az_header_options['top_menu'] ) || has_nav_menu( 'top' ) ) : ?>
                    <nav aria-label="Liên kết nhanh"><?php az_render_header_menu( absint( $az_header_options['top_menu'] ?? 0 ), 'top', 'az-topbar-menu' ); ?></nav>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="az-mainbar">
        <div class="az-wrap az-mainbar-inner">
            <a class="az-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
                <?php echo $az_logo_id ? wp_get_attachment_image( $az_logo_id, 'full', false, array( 'loading' => 'eager' ) ) : esc_html( get_bloginfo( 'name' ) ); ?>
            </a>

            <?php if ( ! array_key_exists( 'show_search', $az_header_options ) || ! empty( $az_header_options['show_search'] ) ) : ?>
                <?php get_search_form(); ?>
            <?php endif; ?>

            <?php if ( $az_phones ) : ?>
                <div class="az-hotlines">
                    <?php foreach ( array_slice( $az_phones, 0, 2 ) as $az_phone ) : ?>
                        <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $az_phone['value'] ?? '' ) ); ?>">
                            <small><?php echo esc_html( $az_phone['label'] ?? 'Hotline' ); ?></small>
                            <strong><?php echo esc_html( $az_phone['value'] ?? '' ); ?></strong>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ( ! empty( $az_header_options['show_primary_menu'] ) || ! empty( $az_header_options['show_category'] ) || ! empty( $az_header_options['show_account'] ) || $az_show_cart || ! empty( $az_header_options['show_cta'] ) ) : ?><div class="az-navrow">
        <div class="az-wrap az-navrow-inner">
            <?php if ( ! empty( $az_header_options['show_category'] ) ) : ?><details class="az-header-category">
                <summary class="az-cat-button"><i class="az-icon-menu" aria-hidden="true"></i><b><?php echo esc_html( $az_header_options['category_button_text'] ?? 'Danh mục sản phẩm' ); ?></b></summary>
                <?php if ( $az_category_menu ) : ?><nav class="az-header-category__menu" aria-label="Danh mục sản phẩm"><?php az_render_header_menu( $az_category_menu, 'primary', 'az-category-menu' ); ?></nav><?php endif; ?>
            </details><?php endif; ?>
            <?php if ( ! empty( $az_header_options['show_primary_menu'] ) ) : ?><nav aria-label="Menu chính"><?php az_render_header_menu( absint( $az_header_options['primary_menu'] ?? 0 ), 'primary', 'az-primary-menu' ); ?></nav><?php endif; ?>
            <div class="az-header-actions">
                <?php if ( ! empty( $az_header_options['show_account'] ) ) { az_render_header_action( 'account', array( 'label' => $az_header_options['account_label'] ?? 'Tài khoản' ) ); } ?>
                <?php if ( ! empty( $az_header_options['show_cta'] ) ) { az_render_header_action( 'cta', array( 'label' => $az_header_options['cta_text'] ?? '', 'url' => $az_header_options['cta_url'] ?? '' ) ); } ?>
                <?php if ( $az_show_cart ) { az_render_header_action( 'cart', array( 'label' => $az_header_options['cart_label'] ?? 'Giỏ hàng' ) ); } ?>
            </div>
        </div>
    </div><?php endif; ?>
</header>
<?php az_render_mobile_menu(); ?>
<?php az_render_sticky_header( $az_header_options ); ?>
<?php do_action( 'az_after_site_header' ); ?>
