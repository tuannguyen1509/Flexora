<?php
/**
 * Blog archive template.
 *
 * @package AZ_AI_Theme
 */
defined( 'ABSPATH' ) || exit;

$title       = 'Blog';
$description = '';

if ( is_category() ) {
    $title       = single_cat_title( '', false );
    $description = term_description();
} elseif ( is_tag() ) {
    $title = single_tag_title( '', false );
} elseif ( is_author() ) {
    $title = get_the_author_meta( 'display_name', get_queried_object_id() );
} elseif ( is_day() || is_month() || is_year() ) {
    $title = get_the_archive_title();
} elseif ( is_home() ) {
    $posts_page_id = absint( get_option( 'page_for_posts' ) );
    $title = $posts_page_id ? get_the_title( $posts_page_id ) : $title;
}

$archive_banner_id = absint( az_get_option( 'blog.archive_banner', 0 ) );
$archive_banner = $archive_banner_id ? wp_get_attachment_image_url( $archive_banner_id, 'full' ) : '';
$archive_hero_layout = 'banner' === az_get_option( 'blog.archive_hero_layout', 'plain' ) ? 'banner' : 'plain';
// Match the admin checkbox: only an explicit 1 enables the title block.
$archive_show_title = in_array( az_get_option( 'blog.archive_show_title', 0 ), array( 1, '1', true ), true );
$archive_banner_color = sanitize_hex_color( az_get_option( 'blog.archive_banner_color', '#10233f' ) ) ?: '#10233f';
$archive_banner_overlay = sanitize_hex_color( az_get_option( 'blog.archive_banner_overlay', '#10233f' ) ) ?: '#10233f';
$archive_banner_height = max( 160, min( 700, absint( az_get_option( 'blog.archive_banner_height', 290 ) ) ) );
$archive_banner_transparent = (bool) az_get_option( 'blog.archive_banner_transparent', 0 );

$columns = max( 1, min( 4, absint( az_get_option( 'blog.columns_desktop', 3 ) ) ) );
$layout = 'list' === az_get_option( 'blog.layout', 'grid' ) ? 'list' : 'grid';
$grid_class = 'az-blog-archive__grid--' . $columns . ' az-blog-archive__grid--' . $layout;
$sidebar = az_get_option( 'blog.archive_sidebar', az_get_option( 'blog.sidebar', 'right' ) );
$sidebar = in_array( $sidebar, array( 'left', 'right', 'none' ), true ) ? $sidebar : 'right';
$show_author = az_blog_show( 'show_author' ); $show_date = az_blog_show( 'show_date' ); $show_category = az_blog_show( 'show_category' ); $show_reading = az_blog_show( 'show_reading_time' );
get_header();
?>
<main id="az-main-content" tabindex="-1" class="az-main az-blog-page az-blog-archive">
    <?php if ( ! az_shared_banner_active() && $archive_show_title ) : ?>
    <header class="az-blog-archive__hero az-blog-archive__hero--<?php echo esc_attr( $archive_hero_layout ); ?><?php echo $archive_banner ? ' has-image' : ''; ?><?php echo $archive_banner_transparent ? ' is-transparent' : ''; ?>" style="--az-archive-color:<?php echo esc_attr( $archive_banner_color ); ?>;--az-archive-overlay:<?php echo esc_attr( $archive_banner_overlay ); ?>;--az-archive-banner-height:<?php echo esc_attr( $archive_banner_height ); ?>px;<?php echo $archive_banner ? '--az-archive-image:url(' . esc_url( $archive_banner ) . ');' : ''; ?>">
        <div class="az-blog-archive__hero-inner">
            <span class="az-blog-archive__eyebrow">Blog</span>
            <?php $heading_tag = is_front_page() ? 'h2' : 'h1'; ?>
            <<?php echo $heading_tag; ?>><?php echo esc_html( $title ); ?></<?php echo $heading_tag; ?>>
            <?php if ( $description ) : ?><div class="az-blog-archive__description"><?php echo wp_kses_post( wpautop( $description ) ); ?></div><?php endif; ?>
        </div>
    </header>

    <?php else : ?>
        <?php // The shared banner or homepage Branding heading already owns the H1. ?>
        <?php if ( ! az_shared_banner_active() && ! is_front_page() ) : ?>
            <h1 class="az-blog-archive__title az-sr-only"><?php echo esc_html( $title ); ?></h1>
        <?php endif; ?>
        <?php if ( $description ) : ?>
        <div class="az-shared-container az-shared-category-description"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
        <?php endif; ?>
    <?php endif; ?>
    <div class="az-blog-archive__body">
        <div class="az-blog-archive__container<?php echo 'none' !== $sidebar ? ' has-sidebar sidebar-' . esc_attr( $sidebar ) : ' no-sidebar'; ?>">
            <div class="az-blog-archive__main">
                <?php if ( have_posts() ) : ?>
                <div class="az-blog-archive__grid <?php echo esc_attr( $grid_class ); ?>" style="<?php
                foreach ( array( 'desktop' => 3, 'tablet' => 2, 'mobile' => 1 ) as $device => $default_columns ) {
                    echo '--az-blog-columns-' . esc_attr( $device ) . ':' . max( 1, min( 6, absint( az_get_option( 'blog.columns_' . $device, $default_columns ) ) ) ) . ';';
                }
                ?>">
                    <?php while ( have_posts() ) : the_post();
                        $categories = get_the_category();
                        $reading_time = max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) );
                    ?>
                        <article <?php post_class( 'az-post-card' ); ?>>
                            <a class="az-post-card__media" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
                                <?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); else : ?><span class="az-post-card__placeholder" aria-hidden="true"></span><?php endif; ?>
                                <?php if ( $show_category && ! empty( $categories ) ) : ?><span class="az-post-card__category"><?php echo esc_html( $categories[0]->name ); ?></span><?php endif; ?>
                            </a>
                            <div class="az-post-card__content">
                                <div class="az-post-card__meta"><?php if ( $show_date ) : ?><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><svg class="az-post-card__meta-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"></rect><path d="M16 2v4M8 2v4M3 10h18"></path></svg><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></time><?php endif; ?><?php if ( $show_reading ) : ?><span><svg class="az-post-card__meta-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg><?php echo esc_html( $reading_time ); ?> phút đọc</span><?php endif; ?></div>
                                <h2 class="az-post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                                <p class="az-post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
                                <a class="az-post-card__link" href="<?php the_permalink(); ?>">Đọc bài <span aria-hidden="true">&#8594;</span></a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                <?php $pagination = paginate_links( array( 'mid_size' => 2, 'prev_text' => '← Trước', 'next_text' => 'Tiếp →', 'type' => 'list' ) ); if ( $pagination ) : ?><nav class="az-blog-archive__pagination" aria-label="Phân trang"><?php echo wp_kses_post( $pagination ); ?></nav><?php endif; ?>
            <?php else : ?>
                <section class="az-blog-archive__empty"><h2>Chưa có bài viết</h2><p>Không có bài viết nào trong khu vực này.</p><a class="az-button" href="<?php echo esc_url( home_url( '/' ) ); ?>">Về trang chủ</a></section>
            <?php endif; ?>
            </div>
            <?php if ( 'none' !== $sidebar ) : ?><aside class="az-blog-archive__sidebar" aria-label="Sidebar Blog"><section class="az-blog-sidebar-widget"><h2>Bài viết mới</h2><div class="az-blog-sidebar-list"><?php $sidebar_query = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 6, 'ignore_sticky_posts' => true ) ); while ( $sidebar_query->have_posts() ) : $sidebar_query->the_post(); ?><a href="<?php the_permalink(); ?>" class="az-blog-sidebar-item"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'thumbnail', array( 'loading' => 'lazy' ) ); } ?><span><?php the_title(); ?><small><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></small></span></a><?php endwhile; wp_reset_postdata(); ?></div></section></aside><?php endif; ?>
        </div>
    </div>
</main>
<?php get_footer(); ?>
