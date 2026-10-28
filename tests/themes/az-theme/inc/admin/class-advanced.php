<?php
defined( 'ABSPATH' ) || exit;

class AZ_Advanced {
    private $sections = array( 'general' => 'General', 'user' => 'User', 'branding' => 'Branding', 'header' => 'Header', 'footer' => 'Footer', 'colors' => 'Colors', 'typography' => 'Typography', 'layout' => 'Layout', 'blog' => 'Blog', 'woocommerce' => 'WooCommerce', 'compatibility' => 'Tương thích', 'social' => 'Social', 'performance' => 'Performance', 'custom_code' => 'Custom Code' );

    public function __construct() {
        $this->sections = array_merge( $this->sections, array( 'google' => 'Google' ) );
        if ( ! az_is_woocommerce_active() ) { unset( $this->sections['woocommerce'] ); }
        $this->register();
        add_action( 'admin_post_az_save_theme_options', array( $this, 'save' ) );
        add_action( 'admin_post_az_reset_theme_section', array( $this, 'reset' ) );
    }

    private function register() {
        foreach ( $this->sections as $id => $title ) {
            $file = str_replace( '_', '-', $id );
            AZ_Theme_Options::add_section( $id, $title, require get_template_directory() . '/inc/admin/sections/' . $file . '.php' );
        }
    }

    private function authorize( $action, $field ) {
        if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Unauthorized.' ); }
        check_admin_referer( $action, $field );
    }

    private function redirect( $section, $status ) {
        wp_safe_redirect( admin_url( 'admin.php?page=az-theme-advanced&section=' . rawurlencode( $section ) . '&' . $status . '=1' ) );
        exit;
    }

    public function render() {
        $active = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : 'general';
        if ( ! isset( $this->sections[ $active ] ) ) { $active = 'general'; }
        $values = az_get_options();
        $custom_code_review = AZ_Custom_Code::needs_review( $values );
        $values['custom_code'] = AZ_Custom_Code::editor_values( $values );
        $description = array( 'header' => 'Cấu hình bố cục, hành vi và thành phần Header.', 'footer' => 'Cấu hình nội dung và hiển thị Footer.', 'general' => 'Thông tin chung của website.', 'compatibility' => 'Dùng lại nội dung và shortcode từ giao diện cũ.', 'custom_code' => 'CSS và mã nhúng áp dụng toàn website, dùng chung khi kích hoạt theme cha hoặc theme con.' );
        echo '<div class="wrap az-theme-admin az-theme-admin--' . esc_attr( $active ) . '"><header class="az-admin-header"><div><h1>AZ Theme Advanced</h1><p>' . esc_html( $this->sections[ $active ] ) . '</p><small>' . esc_html( $description[ $active ] ?? 'Thiết lập giao diện và chức năng toàn website.' ) . '</small></div><div class="az-admin-header__actions"><button form="az-advanced-form" type="submit" class="button button-primary">Save Changes</button><button form="az-reset-section-form" type="submit" class="button az-admin-button-danger">Reset Section</button></div></header>';
        if ( isset( $_GET['updated'] ) ) { echo '<div class="notice notice-success inline" role="status"><p>Settings saved successfully.</p></div>'; }
        if ( isset( $_GET['reset'] ) ) { echo '<div class="notice notice-success inline" role="status"><p>Section restored to defaults.</p></div>'; }
        echo '<div class="az-advanced__layout"><nav class="az-advanced-sidebar-card" aria-label="Advanced sections"><div class="az-advanced__nav">';
        $icons = array( 'general' => 'dashicons-admin-settings', 'branding' => 'dashicons-tag', 'colors' => 'dashicons-art', 'typography' => 'dashicons-editor-textcolor', 'layout' => 'dashicons-layout', 'header' => 'dashicons-align-wide', 'footer' => 'dashicons-align-center', 'blog' => 'dashicons-edit-page', 'woocommerce' => 'dashicons-cart', 'compatibility' => 'dashicons-migrate', 'social' => 'dashicons-share', 'performance' => 'dashicons-performance', 'custom_code' => 'dashicons-editor-code' );
        foreach ( $this->sections as $id => $title ) { echo '<a class="' . ( $id === $active ? 'is-active' : '' ) . '" aria-current="' . ( $id === $active ? 'page' : 'false' ) . '" href="' . esc_url( admin_url( 'admin.php?page=az-theme-advanced&section=' . $id ) ) . '"><span class="dashicons ' . esc_attr( $icons[ $id ] ?? 'dashicons-admin-generic' ) . '"></span>' . esc_html( $title ) . '</a>'; }
        echo '</div></nav><main class="az-advanced__content">';
        if ( 'woocommerce' === $active && ! az_is_woocommerce_active() ) { echo '<div class="notice notice-warning"><p>WooCommerce is not active.</p></div>'; }
        echo '<form id="az-advanced-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="az_save_theme_options"><input type="hidden" name="az_section" value="' . esc_attr( $active ) . '">';
        wp_nonce_field( 'az_save_theme_options', 'az_theme_options_nonce' );
        foreach ( $this->sections as $panel_id => $panel_title ) {
        echo '<div data-az-panel="' . esc_attr( $panel_id ) . '" data-title="' . esc_attr( $panel_title ) . '"' . ( $active === $panel_id ? '' : ' hidden' ) . '>';
        if ( 'custom_code' === $panel_id && $custom_code_review ) {
            echo '<div class="notice notice-warning inline" role="status"><p>Một số mã cũ hoặc mã từ website khác chưa được xác nhận trên website này. Kiểm tra nội dung rồi bấm Save Changes bằng tài khoản có quyền unfiltered_html để áp dụng script.</p></div>';
        }
        $fields = AZ_Options_Registry::get( $panel_id )['fields']; $groups = array();
        foreach ( $fields as $field ) { $group = ! empty( $field['group'] ) ? $field['group'] : 'general'; $groups[ $group ][] = $field; }
        foreach ( $groups as $group => $group_fields ) {
            $grid_class = 1 === count( $group_fields ) ? ' az-settings-grid--single' : '';
            $group_class = ' az-settings-card--' . sanitize_html_class( $group );
            $collapsible = ! empty( $group_fields[0]['group_collapsible'] );
            $title = $group_fields[0]['group_title'] ?? ucfirst( str_replace( '_', ' ', $group ) );
            if ( $collapsible ) {
                echo '<details class="az-settings-card az-settings-card--collapsible' . esc_attr( $group_class ) . '"><summary class="az-settings-card__header"><h2>' . esc_html( $title ) . '</h2><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></summary>';
            } else {
                echo '<section class="az-settings-card' . esc_attr( $group_class ) . '"><header class="az-settings-card__header"><h2>' . esc_html( $title ) . '</h2></header>';
            }
            echo '<div class="az-settings-grid' . esc_attr( $grid_class ) . '">';
            foreach ( $group_fields as $field ) { AZ_Options_Renderer::field( $panel_id, $field, $values ); }
            echo '</div>' . ( $collapsible ? '</details>' : '</section>' );
        }
        echo '</div>';
        }
        echo '<input type="hidden" name="az_complete" value="1">';
        echo '</form><form id="az-reset-section-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="az_reset_theme_section"><input type="hidden" name="az_section" value="' . esc_attr( $active ) . '">';
        wp_nonce_field( 'az_reset_theme_section', 'az_reset_nonce' );
        echo '</form></main></div></div>';
    }

    public function save() {
        $this->authorize( 'az_save_theme_options', 'az_theme_options_nonce' );
        $section = sanitize_key( wp_unslash( $_POST['az_section'] ?? 'general' ) );
        if ( ! isset( $this->sections[ $section ] ) || empty( $_POST['az_complete'] ) || ! is_array( $_POST['az_theme_options'] ?? null ) ) {
            wp_die( 'Dữ liệu gửi chưa đầy đủ. Vui lòng tải lại trang rồi lưu lại; chưa thay đổi cài đặt.' );
        }
        $all = az_get_options();
        $submitted = wp_unslash( $_POST['az_theme_options'] );
        foreach ( $this->sections as $id => $title ) {
            if ( ! isset( $submitted[ $id ] ) || ! is_array( $submitted[ $id ] ) ) { continue; }
            $current = isset( $all[ $id ] ) && is_array( $all[ $id ] ) ? $all[ $id ] : array();
            $registry = AZ_Options_Registry::get( $id );
            foreach ( $registry['fields'] as $field ) {
                if ( ! array_key_exists( $field['id'], $submitted[ $id ] ) ) { continue; }
                if ( 'secret' === $field['type'] && '' === trim( (string) $submitted[ $id ][ $field['id'] ] ) ) { continue; }
                // Do not strip an existing trusted integration when a restricted
                // multisite administrator saves unrelated settings.
                if ( 'code' === $field['type'] && 'custom_css' !== $field['id'] && ! current_user_can( 'unfiltered_html' ) ) { continue; }
                $current[ $field['id'] ] = AZ_Options_Sanitizer::sanitize( $submitted[ $id ][ $field['id'] ], $field['type'], $field );
            }
            $all[ $id ] = $current;
        }
        update_option( 'az_theme_options', $all, false );
        $this->redirect( $section, 'updated' );
    }

    public function reset() {
        $this->authorize( 'az_reset_theme_section', 'az_reset_nonce' );
        $section = sanitize_key( wp_unslash( $_POST['az_section'] ?? '' ) ); $all = az_get_options(); $current = isset( $all[ $section ] ) && is_array( $all[ $section ] ) ? $all[ $section ] : array(); $fields = AZ_Options_Registry::get( $section );
        if ( $fields ) { foreach ( $fields['fields'] as $field ) { if ( 'link' === $field['type'] ) { continue; } $current[ $field['id'] ] = AZ_Options_Sanitizer::sanitize( $field['default'] ?? 0, $field['type'], $field ); } $all[ $section ] = $current; update_option( 'az_theme_options', $all, false ); }
        $this->redirect( $section, 'reset' );
    }
}
