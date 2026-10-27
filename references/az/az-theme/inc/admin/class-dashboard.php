<?php
defined( 'ABSPATH' ) || exit;
class AZ_Dashboard {
    private static function shortcut( $label, $url, $icon = 'dashicons-admin-page', $primary = false ) {
        return '<a class="az-dashboard-card' . ( $primary ? ' az-dashboard-card--primary' : '' ) . '" href="' . esc_url( $url ) . '"><span class="dashicons ' . esc_attr( $icon ) . '" aria-hidden="true"></span><span>' . esc_html( $label ) . '</span></a>';
    }
    public static function render() {
        echo '<div class="wrap az-theme-admin az-admin-dashboard"><header class="az-admin-dashboard__header"><div><h1>AZ Theme</h1><p>Quản lý giao diện và các thiết lập chính của website.</p></div></header><section class="az-admin-dashboard__actions" aria-label="Thao tác nhanh">';
        echo self::shortcut( 'Tất cả trang', admin_url( 'edit.php?post_type=page' ), 'dashicons-media-document' );
        echo self::shortcut( 'Trang mới', admin_url( 'post-new.php?post_type=page' ), 'dashicons-welcome-add-page' );
        echo self::shortcut( 'Tất cả bài viết', admin_url( 'edit.php' ), 'dashicons-admin-post' );
        echo self::shortcut( 'Thêm bài viết mới', admin_url( 'post-new.php' ), 'dashicons-welcome-add-page' );
        echo self::shortcut( 'Upload media', admin_url( 'media-new.php' ), 'dashicons-format-gallery' );
        if ( class_exists( 'WooCommerce' ) && post_type_exists( 'product' ) && current_user_can( 'edit_products' ) ) {
            echo self::shortcut( 'Tất cả sản phẩm', admin_url( 'edit.php?post_type=product' ), 'dashicons-products' );
            echo self::shortcut( 'Thêm sản phẩm mới', admin_url( 'post-new.php?post_type=product' ), 'dashicons-welcome-add-page' );
        }
        echo self::shortcut( 'Cài đặt giao diện', admin_url( 'admin.php?page=az-theme-advanced' ), 'dashicons-admin-customizer', true );
        echo self::shortcut( 'Thông tin liên hệ', admin_url( 'admin.php?page=az-theme-advanced&section=general' ), 'dashicons-phone' );
        if ( class_exists( 'AZ_Child_Theme_Installer' ) && current_user_can( 'install_themes' ) && AZ_Child_Theme_Installer::is_parent_active() && ! AZ_Child_Theme_Installer::child_theme_exists() ) {
            echo self::shortcut( 'Tạo theme child', wp_nonce_url( admin_url( 'admin-post.php?action=az_create_child_theme' ), 'az_create_child_theme' ), 'dashicons-admin-appearance', true );
        }
        echo '</section></div>';
    }
}
