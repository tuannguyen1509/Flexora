<?php
defined( 'ABSPATH' ) || exit;
$az_content_page = get_post( (int) get_queried_object_id() );
if ( ! $az_content_page || 'page' !== $az_content_page->post_type || 'publish' !== $az_content_page->post_status ) { return; }
$az_custom_content = post_password_required( $az_content_page ) ? get_the_password_form( $az_content_page ) : apply_filters( 'the_content', $az_content_page->post_content );
if ( '' === trim( $az_custom_content ) ) { return; }
?>
<section class="az-wrap az-home-custom">
    <?php if ( '' !== $az_homepage_section['title'] ) : ?><h2><?php echo esc_html( $az_homepage_section['title'] ); ?></h2><?php endif; ?>
    <div class="az-home-custom-content"><?php echo $az_custom_content; // WordPress content filter pipeline, like the_content(). ?></div>
</section>
