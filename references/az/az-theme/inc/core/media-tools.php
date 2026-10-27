<?php
defined( 'ABSPATH' ) || exit;

/** Return true only for a missing local primary image, never external/offloaded metadata. */
function az_missing_local_image( $id ) {
    if ( ! wp_attachment_is_image( $id ) ) { return false; }
    $relative = get_post_meta( $id, '_wp_attached_file', true );
    $uploads = wp_get_upload_dir();
    if ( ! is_string( $relative ) || ! $relative || ! empty( $uploads['error'] ) || false !== strpos( $relative, ':' ) || false !== strpos( $relative, '..' ) || false !== strpos( $relative, '\\' ) || '/' === $relative[0] ) { return false; }
    $local = trailingslashit( $uploads['basedir'] ) . $relative;
    $resolved = get_attached_file( $id );
    if ( wp_normalize_path( (string) $resolved ) !== wp_normalize_path( $local ) ) { return false; }
    $url = wp_get_attachment_url( $id );
    if ( ! $url || strpos( $url, trailingslashit( $uploads['baseurl'] ) ) !== 0 ) { return false; }
    return ! file_exists( $local ) && (bool) apply_filters( 'az_allow_missing_media_cleanup', true, $id );
}
add_action( 'admin_menu', function () {
    $hook = add_submenu_page( null, 'Kiểm tra Media', 'Kiểm tra Media', 'manage_options', 'az-missing-media', 'az_missing_media_page' );
    if ( $hook ) {
        add_action( 'load-' . $hook, 'az_missing_media_admin_title' );
    }
} );
/** Hidden submenu pages are not found by get_admin_page_title's menu lookup. */
function az_missing_media_admin_title() {
    $GLOBALS['title'] = 'Kiểm tra ảnh bị thiếu trong Media';
}
function az_missing_media_page() {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Không có quyền truy cập.' ); }
    echo '<div class="wrap"><h1>Kiểm tra ảnh bị thiếu trong Media</h1><p>Chỉ kiểm tra file ảnh chính trên máy chủ, không kiểm tra HTTP 404 từ CDN. Mỗi lượt quét 100 bản ghi. Website dùng offload Media cần kiểm tra với nhà cung cấp trước khi xóa.</p>';
    if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
        check_admin_referer( 'az_delete_missing_media' );
        $deleted = 0;
        if ( ! empty( $_POST['confirm_delete'] ) ) {
            foreach ( array_slice( array_unique( array_map( 'absint', (array) ( $_POST['media_ids'] ?? array() ) ) ), 0, 100 ) as $id ) {
                if ( current_user_can( 'delete_post', $id ) && az_missing_local_image( $id ) && wp_delete_attachment( $id, true ) ) { $deleted++; }
            }
        }
        echo '<div class="notice notice-info"><p>Đã xóa vĩnh viễn ' . esc_html( $deleted ) . ' bản ghi. Khôi phục cần bản sao lưu database/file.</p></div>';
    }
    $page = max( 1, absint( $_GET['scan_page'] ?? 1 ) );
    $query = new WP_Query( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'image', 'posts_per_page' => 100, 'paged' => $page, 'orderby' => 'ID', 'order' => 'ASC' ) );
    echo '<form method="post">'; wp_nonce_field( 'az_delete_missing_media' );
    echo '<ul>'; $found = 0;
    foreach ( $query->posts as $post ) {
        if ( ! az_missing_local_image( $post->ID ) ) { continue; } $found++;
        echo '<li><label><input type="checkbox" name="media_ids[]" value="' . esc_attr( $post->ID ) . '"> #' . esc_html( $post->ID ) . ' — ' . esc_html( $post->post_title ) . ' — ' . esc_html( get_post_meta( $post->ID, '_wp_attached_file', true ) ) . '</label></li>';
    }
    echo '</ul><p>Tìm thấy ' . esc_html( $found ) . ' ảnh bị thiếu trong lượt này.</p><p><label><input type="checkbox" name="confirm_delete" value="1" required> Tôi đã sao lưu và xác nhận xóa vĩnh viễn các bản ghi đã chọn cùng file liên quan còn lại. Nội dung đang dùng ảnh này có thể bị ảnh hưởng.</label></p>';
    submit_button( 'Xóa các bản ghi đã chọn', 'delete' ); echo '</form>';
    for ( $i = max( 1, $page - 1 ); $i <= min( (int) $query->max_num_pages, $page + 1 ); $i++ ) {
        echo '<a class="button" href="' . esc_url( admin_url( 'admin.php?page=az-missing-media&scan_page=' . $i ) ) . '">Lượt ' . esc_html( $i ) . '</a> ';
    }
    echo '</div>';
}
