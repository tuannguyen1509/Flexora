<?php
/**
 * Index Template — Storefront
 * @package AZ_AI_Theme
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
$az_index_home_h1 = is_front_page() && function_exists( 'az_get_home_h1' ) ? az_get_home_h1() : '';
?>
<main id="az-main-content" tabindex="-1" class="az-main az-blog-page pt-28 pb-20 min-h-screen" style="background:#F7F7F7;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ( '' !== $az_index_home_h1 ) : ?>
            <h1 class="az-home-h1"><?php echo esc_html( $az_index_home_h1 ); ?></h1>
        <?php endif; ?>
        <?php if ( have_posts() ) : ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'group' ); ?>>
                        <div class="rounded-xl overflow-hidden h-full flex flex-col transition-all duration-300 hover:-translate-y-1"
                             style="background:#FFFFFF;border:1px solid rgba(17,17,17,.12);box-shadow:0 2px 16px rgba(17,17,17,.08);">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="overflow-hidden h-48">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail( 'medium', array( 'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500' ) ); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div class="p-6 flex flex-col flex-1">
                                <h2 class="text-xl font-bold mb-3 transition-colors" style="color:#0D1B2A;">
                                    <a href="<?php the_permalink(); ?>" style="color:inherit;"
                                       onmouseover="this.style.color='#181818';" onmouseout="this.style.color='#0D1B2A';">
                                        <?php the_title(); ?>
                                    </a>
                                </h2>
                                <div class="text-sm mb-4 flex-1 leading-relaxed" style="color:#333333;">
                                    <?php the_excerpt(); ?>
                                </div>
                                <div class="flex items-center justify-between text-xs pt-4" style="border-top:1px solid rgba(17,17,17,.12);color:#6B8FAD;">
                                    <span><?php echo esc_html( get_the_date() ); ?></span>
                                    <a href="<?php the_permalink(); ?>" class="font-semibold transition-colors" style="color:#181818;"
                                       onmouseover="this.style.color='#2d78c4';" onmouseout="this.style.color='#181818';">
                                        <?php esc_html_e( 'Đọc thêm', 'az-ai-theme' ); ?> →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            <div class="mt-12 flex justify-center">
                <?php the_posts_pagination( array(
                    'mid_size'  => 2,
                    'prev_text' => '← ' . __( 'Trước', 'az-ai-theme' ),
                    'next_text' => __( 'Tiếp', 'az-ai-theme' ) . ' →',
                ) ); ?>
            </div>
        <?php else : ?>
            <div class="text-center py-20">
                <h2 class="text-3xl font-bold mb-4" style="color:#181818;"><?php esc_html_e( 'Không có bài viết', 'az-ai-theme' ); ?></h2>
                <p style="color:#333333;"><?php esc_html_e( 'Xin lỗi, không tìm thấy bài viết nào.', 'az-ai-theme' ); ?></p>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
