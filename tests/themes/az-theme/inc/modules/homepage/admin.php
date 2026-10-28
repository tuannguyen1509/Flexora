<?php
defined( 'ABSPATH' ) || exit;

function az_homepage_admin_menu() {
    if ( locate_template( 'template-parts/home-reference.php', false, false ) ) { return; }
    add_theme_page( 'Trang chủ', 'Trang chủ', 'manage_options', 'az-homepage', 'az_homepage_admin_page' );
}
add_action( 'admin_menu', 'az_homepage_admin_menu' );

function az_homepage_admin_assets( $hook ) {
    if ( 'appearance_page_az-homepage' !== $hook ) { return; }
    wp_enqueue_style( 'az-admin', get_template_directory_uri() . '/css/admin.css', array(), filemtime( get_template_directory() . '/css/admin.css' ) );
}
add_action( 'admin_enqueue_scripts', 'az_homepage_admin_assets' );

function az_homepage_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    $settings = az_homepage_settings();
    ?>
    <div class="wrap az-admin">
        <h1>Trang chủ</h1>
        <?php if ( isset( $_GET['az-saved'] ) ) : ?><div class="notice notice-success"><p>Đã lưu cấu hình trang chủ.</p></div><?php endif; ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="az_homepage_save">
            <?php wp_nonce_field( 'az_homepage_save', 'az_homepage_nonce' ); ?>
            <?php foreach ( $settings as $id => $section ) : ?>
                <section class="az-admin-section">
                    <h2 class="az-admin-section-heading"><?php echo esc_html( $section['label'] ); ?></h2>
                    <div class="az-admin-fields">
                        <div class="az-admin-field">
                            <label for="az-home-<?php echo esc_attr( $id ); ?>-enabled">Hiển thị</label>
                            <input type="hidden" name="sections[<?php echo esc_attr( $id ); ?>][enabled]" value="0">
                            <input type="checkbox" id="az-home-<?php echo esc_attr( $id ); ?>-enabled" name="sections[<?php echo esc_attr( $id ); ?>][enabled]" value="1" <?php checked( $section['enabled'] ); ?>>
                        </div>
                        <?php foreach ( array( 'title' => 'Tiêu đề', 'order' => 'Thứ tự', 'limit' => 'Số lượng', 'columns' => 'Số cột desktop (0: mặc định)' ) as $field => $label ) :
                            if ( in_array( $id, array( 'hero', 'wide-banners', 'custom-content', 'services' ), true ) && in_array( $field, array( 'limit', 'columns' ), true ) ) { continue; }
                            $input_id = 'az-home-' . $id . '-' . $field;
                            ?>
                            <div class="az-admin-field">
                                <label for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $label ); ?></label>
                                <input id="<?php echo esc_attr( $input_id ); ?>" name="sections[<?php echo esc_attr( $id ); ?>][<?php echo esc_attr( $field ); ?>]" value="<?php echo esc_attr( $section[ $field ] ); ?>" type="<?php echo 'title' === $field ? 'text' : 'number'; ?>" <?php if ( 'title' !== $field ) : ?>min="<?php echo 'limit' === $field ? '1' : '0'; ?>" max="<?php echo 'order' === $field ? '999' : ( 'columns' === $field ? '6' : '30' ); ?>" step="1" required<?php endif; ?>>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
            <div class="az-admin-actions"><button class="az-admin-button az-admin-button-primary" type="submit">Lưu trang chủ</button></div>
        </form>
    </div>
    <?php
}

function az_homepage_save() {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Không có quyền chỉnh sửa.', '', array( 'response' => 403 ) ); }
    check_admin_referer( 'az_homepage_save', 'az_homepage_nonce' );
    if ( ! isset( $_POST['sections'] ) || ! is_array( $_POST['sections'] ) ) { wp_die( 'Dữ liệu không hợp lệ.', '', array( 'response' => 400 ) ); }
    $input = wp_unslash( $_POST['sections'] );
    $options = az_homepage_options();
    if ( ! isset( $options['homepage'] ) || ! is_array( $options['homepage'] ) ) { $options['homepage'] = array(); }
    if ( ! isset( $options['homepage']['sections'] ) || ! is_array( $options['homepage']['sections'] ) ) { $options['homepage']['sections'] = array(); }
    foreach ( az_homepage_settings() as $id => $defaults ) {
        if ( ! isset( $input[ $id ] ) || ! is_array( $input[ $id ] ) ) { continue; }
        if ( ! isset( $options['homepage']['sections'][ $id ] ) || ! is_array( $options['homepage']['sections'][ $id ] ) ) { $options['homepage']['sections'][ $id ] = array(); }
        $clean = az_homepage_normalize( $input[ $id ], $defaults );
        foreach ( array( 'enabled', 'title', 'order', 'limit', 'columns' ) as $field ) {
            if ( array_key_exists( $field, $input[ $id ] ) ) { $options['homepage']['sections'][ $id ][ $field ] = $clean[ $field ]; }
        }
    }
    update_option( 'az_theme_options', $options );
    wp_safe_redirect( admin_url( 'themes.php?page=az-homepage&az-saved=1' ) );
    exit;
}
add_action( 'admin_post_az_homepage_save', 'az_homepage_save' );
