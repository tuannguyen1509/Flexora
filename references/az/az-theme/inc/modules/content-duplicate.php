<?php
/**
 * Duplicate posts, pages and visible custom post types as drafts.
 *
 * @package AZ_AI_Theme
 */
defined( 'ABSPATH' ) || exit;

function az_content_duplicate_enabled() {
    return (bool) az_get_option( 'performance.duplicate_content', 1 );
}

function az_content_duplicate_supported( $post ) {
    if ( ! ( $post instanceof WP_Post ) || in_array( $post->post_status, array( 'trash', 'auto-draft' ), true ) ) { return false; }
    $post_type = get_post_type_object( $post->post_type );
    if ( ! $post_type || ! $post_type->show_ui || 'attachment' === $post->post_type ) { return false; }
    return in_array( $post->post_type, array( 'post', 'page' ), true ) || ! $post_type->_builtin;
}

function az_content_duplicate_can_create( $post ) {
    if ( ! az_content_duplicate_supported( $post ) || ! current_user_can( 'edit_post', $post->ID ) ) { return false; }
    $post_type = get_post_type_object( $post->post_type );
    $create_cap = ! empty( $post_type->cap->create_posts ) ? $post_type->cap->create_posts : $post_type->cap->edit_posts;
    return current_user_can( $create_cap );
}

function az_content_duplicate_row_action( $actions, $post ) {
    if ( ! az_content_duplicate_enabled() || ! az_content_duplicate_can_create( $post ) ) { return $actions; }
    foreach ( $actions as $key => $action ) {
        if ( false !== stripos( (string) $key, 'duplicate' ) || false !== stripos( (string) $action, 'duplicate' ) ) { return $actions; }
    }
    $url = wp_nonce_url(
        admin_url( 'admin-post.php?action=az_duplicate_content&post=' . absint( $post->ID ) ),
        'az_duplicate_content_' . absint( $post->ID )
    );
    $actions['az_duplicate'] = '<a href="' . esc_url( $url ) . '" aria-label="' . esc_attr( sprintf( 'Nhân bản “%s”', get_the_title( $post ) ) ) . '">Nhân bản</a>';
    return $actions;
}
add_filter( 'post_row_actions', 'az_content_duplicate_row_action', 20, 2 );
add_filter( 'page_row_actions', 'az_content_duplicate_row_action', 20, 2 );

function az_content_duplicate_copy_terms( $source_id, $duplicate_id, $post_type ) {
    foreach ( get_object_taxonomies( $post_type ) as $taxonomy ) {
        $term_ids = wp_get_object_terms( $source_id, $taxonomy, array( 'fields' => 'ids' ) );
        if ( ! is_wp_error( $term_ids ) ) {
            wp_set_object_terms( $duplicate_id, array_map( 'intval', $term_ids ), $taxonomy, false );
        }
    }
}

function az_content_duplicate_copy_meta( $source_id, $duplicate_id ) {
    $excluded = array( '_edit_lock', '_edit_last', '_wp_old_slug', '_wp_trash_meta_status', '_wp_trash_meta_time' );
    foreach ( get_post_meta( $source_id ) as $key => $values ) {
        if ( in_array( $key, $excluded, true ) ) { continue; }
        foreach ( $values as $value ) {
            add_post_meta( $duplicate_id, $key, maybe_unserialize( $value ) );
        }
    }
}

function az_content_duplicate_handle() {
    if ( ! az_content_duplicate_enabled() ) { wp_die( 'Chức năng nhân bản đang tắt.' ); }
    $source_id = absint( $_GET['post'] ?? 0 );
    check_admin_referer( 'az_duplicate_content_' . $source_id );
    $source = get_post( $source_id );
    if ( ! $source || ! az_content_duplicate_can_create( $source ) ) { wp_die( 'Bạn không có quyền nhân bản nội dung này.' ); }

    $duplicate_id = wp_insert_post( wp_slash( array(
        'post_author'           => get_current_user_id(),
        'post_content'          => $source->post_content,
        'post_title'            => sprintf( 'Bản sao – %s', $source->post_title ),
        'post_excerpt'          => $source->post_excerpt,
        'post_status'           => 'draft',
        'post_type'             => $source->post_type,
        'comment_status'        => $source->comment_status,
        'ping_status'           => $source->ping_status,
        'post_password'         => $source->post_password,
        'post_parent'           => $source->post_parent,
        'menu_order'            => $source->menu_order,
        'post_mime_type'        => $source->post_mime_type,
        'to_ping'               => $source->to_ping,
        'pinged'                => $source->pinged,
        'post_content_filtered' => $source->post_content_filtered,
    ) ), true );

    if ( is_wp_error( $duplicate_id ) ) { wp_die( esc_html( $duplicate_id->get_error_message() ) ); }
    az_content_duplicate_copy_terms( $source_id, $duplicate_id, $source->post_type );
    az_content_duplicate_copy_meta( $source_id, $duplicate_id );
    do_action( 'az_after_duplicate_content', $duplicate_id, $source_id );

    wp_safe_redirect( add_query_arg( 'az_duplicated', '1', get_edit_post_link( $duplicate_id, 'url' ) ) );
    exit;
}
add_action( 'admin_post_az_duplicate_content', 'az_content_duplicate_handle' );

function az_content_duplicate_notice() {
    if ( empty( $_GET['az_duplicated'] ) ) { return; }
    echo '<div class="notice notice-success is-dismissible"><p>Nội dung đã được nhân bản thành bản nháp.</p></div>';
}
add_action( 'admin_notices', 'az_content_duplicate_notice' );
