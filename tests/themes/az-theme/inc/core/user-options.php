<?php
defined( 'ABSPATH' ) || exit;
function az_user_feature( $key ) {
    return az_get_option( 'user.enabled', 0 ) && az_get_option( 'user.' . $key, 0 );
}
// Replace legacy unconditional restrictions without changing stored roles/meta.
add_action( 'after_setup_theme', function () {
    remove_action( 'after_setup_theme', 'az_hide_admin_bar_for_non_admins' );
    remove_action( 'get_header', 'az_remove_admin_bar_css' );
    remove_action( 'admin_init', 'az_disable_admin_bar_profile_option' );
    remove_action( 'admin_init', 'az_redirect_non_admin_from_dashboard' );
}, 0 );
add_action( 'admin_init', function () {
    global $pagenow;
    if ( az_user_feature( 'admin_only' ) && is_user_logged_in() && ! current_user_can( 'manage_options' ) && ! wp_doing_ajax() && ! in_array( $pagenow, array( 'admin-post.php', 'admin-ajax.php' ), true ) ) {
        wp_safe_redirect( home_url( '/' ) ); exit;
    }
} );
function az_user_should_hide_admin_bar() {
    return az_user_feature( 'hide_admin_bar' ) && ( 'all' === az_get_option( 'user.admin_bar_scope', 'users' ) || ! current_user_can( 'manage_options' ) );
}
add_filter( 'show_admin_bar', function ( $show ) {
    return az_user_should_hide_admin_bar() ? false : $show;
}, PHP_INT_MAX );
// WordPress always returns true for the toolbar in wp-admin, bypassing the
// show_admin_bar filter. Remove rendering explicitly when both surfaces selected.
add_action( 'init', function () {
    if ( ! az_user_should_hide_admin_bar() ) { return; }
    if ( is_admin() && 'both' !== az_get_option( 'user.admin_bar_location', 'frontend' ) ) { return; }
    remove_action( 'wp_footer', 'wp_admin_bar_render', 1000 );
    remove_action( 'wp_body_open', 'wp_admin_bar_render', 0 );
    remove_action( 'admin_footer', 'wp_admin_bar_render', 1000 );
    remove_action( 'in_admin_header', 'wp_admin_bar_render', 0 );
    remove_action( 'wp_head', '_admin_bar_bump_cb' );
}, PHP_INT_MAX );
add_action( 'admin_head', function () {
    if ( az_user_should_hide_admin_bar() && 'both' === az_get_option( 'user.admin_bar_location', 'frontend' ) ) {
        echo '<style>html.wp-toolbar{padding-top:0}#wpadminbar{display:none}</style>';
    }
} );
function az_user_restrict_content() {
    return az_user_feature( 'own_content' ) && is_user_logged_in() && ! current_user_can( 'manage_options' );
}
add_action( 'pre_get_posts', function ( $query ) {
    if ( ! is_admin() || ! az_user_restrict_content() ) { return; }
    $type = $query->get( 'post_type' ) ?: 'post';
    if ( in_array( $type, array( 'post', 'attachment' ), true ) ) { $query->set( 'author', get_current_user_id() ); $query->set( 'author__in', array( get_current_user_id() ) ); }
} );
add_filter( 'ajax_query_attachments_args', function ( $args ) {
    if ( az_user_restrict_content() ) { $args['author'] = get_current_user_id(); }
    return $args;
} );
foreach ( array( 'post', 'attachment' ) as $type ) {
    add_filter( 'rest_' . $type . '_query', function ( $args ) {
        if ( az_user_restrict_content() ) { $args['author'] = get_current_user_id(); $args['author__in'] = array( get_current_user_id() ); }
        return $args;
    } );
}
add_filter( 'map_meta_cap', function ( $caps, $cap, $user_id, $args ) {
    if ( ! az_user_feature( 'own_content' ) || ! in_array( $cap, array( 'edit_post', 'delete_post' ), true ) || user_can( $user_id, 'manage_options' ) || empty( $args[0] ) ) { return $caps; }
    $post = get_post( $args[0] );
    if ( $post && in_array( $post->post_type, array( 'post', 'attachment' ), true ) && (int) $post->post_author !== (int) $user_id ) { return array( 'do_not_allow' ); }
    return $caps;
}, 20, 4 );
add_filter( 'manage_users_columns', function ( $columns ) {
    if ( az_user_feature( 'show_id' ) ) { $columns['az_user_id'] = 'ID'; }
    return $columns;
} );
add_filter( 'manage_users_custom_column', function ( $value, $column, $id ) {
    return 'az_user_id' === $column && az_user_feature( 'show_id' ) ? (string) (int) $id : $value;
}, 10, 3 );
function az_user_profile_fields( $user ) {
    if ( az_user_feature( 'show_id' ) ) { echo '<h2>ID thành viên: ' . esc_html( $user->ID ) . '</h2>'; }
    if ( ! az_user_feature( 'avatar_upload' ) || ! current_user_can( 'edit_user', $user->ID ) ) { return; }
    wp_nonce_field( 'az_avatar_' . $user->ID, 'az_avatar_nonce' );
    echo '<h2>Avatar cá nhân</h2><p><input type="file" name="az_avatar" accept="image/jpeg,image/png,image/webp"> JPEG/PNG/WebP, tối đa 2 MB.</p><p><label><input type="checkbox" name="az_remove_avatar" value="1"> Gỡ avatar cá nhân (không xóa file)</label></p>';
}
add_action( 'show_user_profile', 'az_user_profile_fields' );
add_action( 'edit_user_profile', 'az_user_profile_fields' );
add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( az_user_feature( 'avatar_upload' ) && in_array( $hook, array( 'profile.php', 'user-edit.php' ), true ) ) {
        wp_enqueue_script( 'az-profile-avatar', get_template_directory_uri() . '/js/profile-avatar.js', array(), '1.0', true );
    }
} );
function az_save_profile_avatar( $errors, $update, $user ) {
    if ( ! az_user_feature( 'avatar_upload' ) || ! $update || ! current_user_can( 'edit_user', $user->ID ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['az_avatar_nonce'] ?? '' ) ), 'az_avatar_' . $user->ID ) ) { return; }
    if ( ! empty( $_POST['az_remove_avatar'] ) ) { delete_user_meta( $user->ID, 'az_avatar_id' ); }
    if ( empty( $_FILES['az_avatar']['name'] ) ) { return; }
    if ( ! az_auth_rate_allowed( 'avatar-upload', (string) get_current_user_id(), 5, 600 ) ) {
        $errors->add( 'az_avatar_rate', 'Tối đa 5 lượt tải avatar trong 10 phút.' ); return;
    }
    $file = $_FILES['az_avatar'];
    if ( ! empty( $file['error'] ) || $file['size'] > 2 * MB_IN_BYTES || ! in_array( wp_get_image_mime( $file['tmp_name'] ), array( 'image/jpeg', 'image/png', 'image/webp' ), true ) ) {
        $errors->add( 'az_avatar_invalid', 'Avatar phải là JPEG/PNG/WebP hợp lệ, tối đa 2 MB.' ); return;
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $id = media_handle_upload( 'az_avatar', 0, array( 'post_author' => $user->ID ), array( 'test_form' => false, 'mimes' => array( 'jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp' ) ) );
    if ( is_wp_error( $id ) ) { $errors->add( 'az_avatar_upload', $id->get_error_message() ); return; }
    update_user_meta( $user->ID, 'az_avatar_id', $id );
}
add_action( 'user_profile_update_errors', 'az_save_profile_avatar', 10, 3 );
add_filter( 'get_avatar_data', function ( $args, $identity ) {
    if ( ! az_user_feature( 'avatar_upload' ) || ! empty( $args['force_default'] ) ) { return $args; }
    $user = false;
    if ( is_numeric( $identity ) ) { $user = get_user_by( 'id', (int) $identity ); }
    elseif ( $identity instanceof WP_User ) { $user = $identity; }
    elseif ( $identity instanceof WP_Comment && $identity->user_id ) { $user = get_user_by( 'id', $identity->user_id ); }
    elseif ( is_string( $identity ) && is_email( $identity ) ) { $user = get_user_by( 'email', $identity ); }
    if ( $user ) {
        $url = wp_get_attachment_image_url( (int) get_user_meta( $user->ID, 'az_avatar_id', true ), 'thumbnail' );
        if ( $url ) { $args['url'] = $url; $args['found_avatar'] = true; }
    }
    return $args;
}, 20, 2 );
