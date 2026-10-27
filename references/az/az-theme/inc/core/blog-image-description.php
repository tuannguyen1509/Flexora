<?php
defined( 'ABSPATH' ) || exit;

/** Run after attachment metadata is saved; never overwrite an existing description. */
function az_blog_image_description( $id, $post, $update, $post_before = null ) {
    static $saving = false;
    if ( $saving || ! az_get_option( 'blog.image_title_description', 0 ) || 'attachment' !== $post->post_type || ! wp_attachment_is_image( $id ) || ! $post->post_parent ) {
        return;
    }
    $parent = get_post( $post->post_parent );
    if ( ! $parent || 'post' !== $parent->post_type || 'auto-draft' === $parent->post_status ) { return; }
    $title = sanitize_text_field( $parent->post_title );
    if ( '' === $title ) { return; }
    // WordPress meta APIs expect slashed strings.
    if ( '' === (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) ) {
        update_post_meta( $id, '_wp_attachment_image_alt', wp_slash( $title ) );
    }
    $changes = array( 'ID' => $id );
    if ( '' === trim( (string) $post->post_content ) ) { $changes['post_content'] = $title; }
    $file_title = pathinfo( (string) get_attached_file( $id ), PATHINFO_FILENAME );
    // A fresh upload normally receives its filename as its Media title.
    if ( '' === trim( (string) $post->post_title ) || ( ! $update && $file_title === $post->post_title ) ) { $changes['post_title'] = $title; }
    if ( count( $changes ) > 1 ) {
        $saving = true;
        wp_update_post( wp_slash( $changes ) );
        $saving = false;
    }
}
add_action( 'wp_after_insert_post', 'az_blog_image_description', 20, 4 );

/** Fill missing attributes at render time, including existing/external content images. */
function az_blog_content_image_descriptions( $content ) {
    if ( ! az_get_option( 'blog.image_title_description', 0 ) || is_admin() || ! is_singular( 'post' ) ) {
        return $content;
    }
    $title = sanitize_text_field( get_the_title() );
    return az_blog_fill_image_attributes( $content, $title );
}

function az_blog_fill_image_attributes( $content, $title ) {
    if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) { return $content; }
    if ( '' === $title ) { return $content; }
    $html = new WP_HTML_Tag_Processor( $content );
    while ( $html->next_tag( 'IMG' ) ) {
        if ( 'presentation' === $html->get_attribute( 'role' ) || 'none' === $html->get_attribute( 'role' ) || 'true' === $html->get_attribute( 'aria-hidden' ) ) { continue; }
        $alt = $html->get_attribute( 'alt' );
        if ( null === $alt || '' === trim( (string) $alt ) ) {
            // Prefer a manually entered Media ALT over the generic post title.
            $custom_alt = '';
            if ( preg_match( '/(?:^|\s)wp-image-(\d+)(?:\s|$)/', (string) $html->get_attribute( 'class' ), $matches ) ) {
                $custom_alt = (string) get_post_meta( (int) $matches[1], '_wp_attachment_image_alt', true );
            }
            $html->set_attribute( 'alt', '' !== trim( $custom_alt ) ? $custom_alt : $title );
        }
        if ( null === $html->get_attribute( 'title' ) || '' === trim( (string) $html->get_attribute( 'title' ) ) ) {
            $html->set_attribute( 'title', $title );
        }
    }
    return $html->get_updated_html();
}
// Run after shortcode/builders and other content formatters have produced images.
add_filter( 'the_content', 'az_blog_content_image_descriptions', 999 );

/** Classic Editor inserts HTML independently of attachment metadata. */
function az_blog_insert_image_title( $html ) {
    if ( ! az_get_option( 'blog.image_title_description', 0 ) ) { return $html; }
    $post_id = absint( $_REQUEST['post_id'] ?? $_REQUEST['post_ID'] ?? 0 );
    $post = $post_id ? get_post( $post_id ) : null;
    if ( ! $post || 'post' !== $post->post_type || ! current_user_can( 'edit_post', $post_id ) || 'auto-draft' === $post->post_status ) { return $html; }
    return az_blog_fill_image_attributes( $html, sanitize_text_field( $post->post_title ) );
}
add_filter( 'image_send_to_editor', 'az_blog_insert_image_title', 999 );
add_filter( 'image_send_to_editor_url', 'az_blog_insert_image_title', 999 );

// Persist attributes when a post is saved, including images uploaded to Media
// before they were attached to the post. No bulk database rewrite on activation.
function az_blog_save_image_titles( $data, $postarr ) {
    // Do not mutate serialized block HTML: Gutenberg owns its saved markup.
    // The frontend filter still fills attributes after blocks are rendered.
    if ( has_blocks( wp_unslash( $data['post_content'] ?? '' ) ) ) { return $data; }
    if ( az_get_option( 'blog.image_title_description', 0 ) && 'post' === ( $data['post_type'] ?? '' ) && ! in_array( $data['post_status'] ?? '', array( 'auto-draft', 'trash' ), true ) ) {
        $title = sanitize_text_field( wp_unslash( $data['post_title'] ?? '' ) );
        $data['post_content'] = wp_slash( az_blog_fill_image_attributes( wp_unslash( $data['post_content'] ?? '' ), $title ) );
    }
    return $data;
}
add_filter( 'wp_insert_post_data', 'az_blog_save_image_titles', 999, 2 );
add_action( 'admin_notices', function () {
    if ( current_user_can( 'manage_options' ) && az_get_option( 'blog.image_title_description', 0 ) && ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
        echo '<div class="notice notice-warning"><p>Tự điền title ảnh chưa hoạt động: cần WordPress 6.2 trở lên. Không thay đổi HTML khi thiếu bộ xử lý an toàn.</p></div>';
    }
} );
