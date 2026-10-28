<?php
/**
 * Default page template.
 *
 * @package AZ_AI_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( function_exists( 'az_is_woocommerce_system_page' ) && az_is_woocommerce_system_page() ) :
    while ( have_posts() ) :
        the_post();
        ?>
        <main id="az-main-content" tabindex="-1" class="az-main az-woocommerce-system-page">
            <?php the_content(); ?>
        </main>
        <?php
    endwhile;

    get_footer();
    return;
endif;

while ( have_posts() ) :
    the_post();
    $az_page_heading = is_front_page() && function_exists( 'az_get_home_h1' )
        ? az_get_home_h1()
        : get_the_title();
    ?>
    <main id="az-main-content" tabindex="-1" class="az-main az-page az-page-simple">
        <div class="az-container az-page-simple__container">
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'az-page-simple__article' ); ?>>
                <header class="az-page-simple__header">
                    <h1><?php echo esc_html( $az_page_heading ); ?></h1>
                </header>

                <div class="az-page-simple__content az-content">
                    <?php the_content(); ?>
                </div>

                <?php
                wp_link_pages(
                    array(
                        'before'      => '<nav class="az-page-links" aria-label="' . esc_attr__( 'Phân trang nội dung', 'az-ai-theme' ) . '"><span>' . esc_html__( 'Trang:', 'az-ai-theme' ) . '</span>',
                        'after'       => '</nav>',
                        'link_before' => '<span>',
                        'link_after'  => '</span>',
                    )
                );
                ?>
            </article>
        </div>
    </main>
    <?php
endwhile;

get_footer();
