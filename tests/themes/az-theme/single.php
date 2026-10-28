<?php
/**
 * AZ single post template.
 *
 * @package AZ_AI_Theme
 */
defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
    the_post();
    $categories = get_the_category();
    $category_ids = wp_list_pluck( $categories, 'term_id' );
    $sidebar = az_get_option( 'blog.single_sidebar', az_get_option( 'blog.sidebar', 'right' ) );
    $sidebar = in_array( $sidebar, array( 'left', 'right', 'none' ), true ) ? $sidebar : 'right';
    $reading_time = max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) );
    $banner_id = absint( az_get_option( 'blog.single_banner', 0 ) );
    $banner = $banner_id ? wp_get_attachment_image_url( $banner_id, 'full' ) : '';
    $banner_color = sanitize_hex_color( az_get_option( 'blog.single_banner_color', '#222222' ) ) ?: '#222222';
    $banner_overlay = sanitize_hex_color( az_get_option( 'blog.single_banner_overlay', '#222222' ) ) ?: '#222222';
    $banner_height = max( 160, min( 700, absint( az_get_option( 'blog.single_banner_height', 220 ) ) ) );
    $banner_transparent = (bool) az_get_option( 'blog.single_banner_transparent', 0 );
    $author_name = trim( (string) az_get_option( 'blog.author_name', '' ) );
    $author_description = trim( (string) az_get_option( 'blog.author_description', 'Chuyên gia tư vấn sản phẩm và giải pháp phù hợp cho khách hàng.' ) );
    $author_url = trim( (string) az_get_option( 'blog.author_url', '' ) );
    $author_avatar_id = absint( az_get_option( 'blog.author_avatar', 0 ) );
    $author_avatar_id = $author_avatar_id ? $author_avatar_id : az_get_logo_id();
    $related = new WP_Query( array(
        'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 5,
        'post__not_in' => array( get_the_ID() ), 'category__in' => $category_ids,
        'ignore_sticky_posts' => true,
    ) );
?>
<main id="az-main-content" tabindex="-1" class="az-main az-single-post">
    <article <?php post_class( 'az-single-post__article' ); ?>>
        <header class="az-single-post__hero<?php echo $banner ? ' has-image' : ''; ?><?php echo $banner_transparent ? ' is-transparent' : ''; ?>" style="--az-single-color:<?php echo esc_attr( $banner_color ); ?>;--az-single-overlay:<?php echo esc_attr( $banner_overlay ); ?>;--az-single-banner-height:<?php echo esc_attr( $banner_height ); ?>px;<?php echo $banner ? '--az-single-image:url(' . esc_url( $banner ) . ');' : ''; ?>">
            <div class="az-single-post__hero-inner">
                <?php if ( az_blog_show( 'show_category' ) && ! empty( $categories ) ) : ?><a class="az-single-post__category" href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>"><?php echo esc_html( $categories[0]->name ); ?></a><?php endif; ?>
                <h1 class="az-single-post__title"><?php the_title(); ?></h1>
                <div class="az-single-post__meta">
                    <?php if ( az_blog_show( 'show_author' ) ) : ?><span><span class="dashicons dashicons-admin-users" aria-hidden="true"></span><?php the_author(); ?></span><?php endif; ?>
                    <?php if ( az_blog_show( 'show_date' ) ) : ?><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></time><?php endif; ?>
                    <span><span class="dashicons dashicons-clock" aria-hidden="true"></span><?php echo esc_html( $reading_time ); ?> phút đọc</span>
                </div>
            </div>
        </header>

        <div class="az-single-post__layout<?php echo 'none' !== $sidebar ? ' has-sidebar sidebar-' . esc_attr( $sidebar ) : ' no-sidebar'; ?>">
            <div class="az-single-post__main">
                <div class="az-single-post__content">
                    <?php if ( has_post_thumbnail() ) : ?><figure class="az-single-post__featured"><?php the_post_thumbnail( 'full', array( 'loading' => 'eager', 'alt' => esc_attr( get_the_title() ) ) ); ?></figure><?php endif; ?>
                    <div class="az-post-content az-content"><?php the_content(); ?></div>
                    <?php wp_link_pages( array( 'before' => '<nav class="az-single-post__pages" aria-label="Trang bài viết">', 'after' => '</nav>' ) ); ?>
                    <?php if ( has_tag() ) : ?><div class="az-single-post__tags"><strong>Thẻ</strong><?php foreach ( get_the_tags() ?: array() as $tag ) : ?><a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>">#<?php echo esc_html( $tag->name ); ?></a><?php endforeach; ?></div><?php endif; ?>
                    <div class="az-single-post__share"><strong>Chia sẻ bài viết</strong><a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( get_permalink() ) ); ?>" target="_blank" rel="noopener">Facebook</a><a href="<?php echo esc_url( 'https://www.linkedin.com/shareArticle?mini=true&url=' . rawurlencode( get_permalink() ) ); ?>" target="_blank" rel="noopener">LinkedIn</a></div>
                </div>

                <?php if ( $author_name || $author_description || $author_avatar_id ) : ?><section class="az-blog-author-box" aria-label="Tác giả Blog"><div class="az-blog-author-box__avatar"><?php if ( $author_avatar_id ) : ?><?php echo wp_get_attachment_image( $author_avatar_id, 'thumbnail', false, array( 'loading' => 'lazy', 'alt' => $author_name ) ); ?><?php else : ?><span aria-hidden="true"><?php echo esc_html( strtoupper( substr( $author_name ?: 'A', 0, 1 ) ) ); ?></span><?php endif; ?></div><div class="az-blog-author-box__content"><?php if ( $author_name ) : ?><h2><?php echo esc_html( $author_name ); ?></h2><?php endif; ?><?php if ( $author_description ) : ?><p><?php echo esc_html( $author_description ); ?></p><?php endif; ?><a href="<?php echo esc_url( $author_url ?: get_post_type_archive_link( 'post' ) ); ?>">Xem tất cả bài viết <span aria-hidden="true">→</span></a></div></section><?php endif; ?>

                <?php $previous = get_previous_post(); $next = get_next_post(); if ( $previous || $next ) : ?><nav class="az-single-post__navigation" aria-label="Điều hướng bài viết"><?php if ( $previous ) : ?><a href="<?php echo esc_url( get_permalink( $previous ) ); ?>">← Bài trước<span><?php echo esc_html( get_the_title( $previous ) ); ?></span></a><?php endif; ?><?php if ( $next ) : ?><a href="<?php echo esc_url( get_permalink( $next ) ); ?>">Bài tiếp theo →<span><?php echo esc_html( get_the_title( $next ) ); ?></span></a><?php endif; ?></nav><?php endif; ?>
                <?php if ( $related->have_posts() ) : ?><section class="az-single-post__related"><h2>Xem thêm</h2><div><?php while ( $related->have_posts() ) : $related->the_post(); ?><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a><?php endwhile; wp_reset_postdata(); ?></div></section><?php endif; ?>
                <?php if ( comments_open() || get_comments_number() ) : ?><section class="az-single-post__comments" id="comments"><?php comments_template(); ?></section><?php endif; ?>
            </div>
            <?php if ( 'none' !== $sidebar ) : ?><aside class="az-single-post__sidebar" aria-label="Sidebar bài viết"><section class="az-single-post__widget"><h2>Bài viết mới</h2><?php $latest = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 6, 'post__not_in' => array( get_the_ID() ), 'ignore_sticky_posts' => true ) ); while ( $latest->have_posts() ) : $latest->the_post(); ?><a href="<?php the_permalink(); ?>"><?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'thumbnail', array( 'loading' => 'lazy' ) ); endif; ?><span><?php the_title(); ?><small><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></small></span></a><?php endwhile; wp_reset_postdata(); ?></section></aside><?php endif; ?>
        </div>
    </article>
</main>
<?php endwhile; get_footer();
