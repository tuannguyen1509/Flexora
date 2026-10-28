<?php
/**
 * Template Name: Trang Nội Dung Trung Tính
 * Template Post Type: page
 *
 * Reusable editorial Page layout inherited by child themes.
 * Keeps shared banners, content filters and WordPress content pagination.
 *
 * @package AZ_AI_Theme
 */
defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	$az_neutral_locked = post_password_required();
	?>
	<main id="main" class="az-main az-neutral-page" tabindex="-1">
		<div id="az-main-content" class="az-container az-neutral-page__container" tabindex="-1">
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'az-neutral-page__article' ); ?>>
				<?php if ( ! $az_neutral_locked && has_post_thumbnail() ) : ?>
				<figure class="az-neutral-page__featured">
					<?php the_post_thumbnail( 'full', array( 'class' => 'az-neutral-page__image', 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
				</figure>
				<?php endif; ?>
				<div class="az-neutral-page__body">
					<div class="az-content az-neutral-page__content"><?php the_content(); ?></div>
					<?php wp_link_pages( array(
						'before' => '<nav class="az-neutral-page__pagination" aria-label="' . esc_attr__( 'Phân trang nội dung', 'az-ai-theme' ) . '"><span>' . esc_html__( 'Trang:', 'az-ai-theme' ) . '</span>',
						'after' => '</nav>',
						'link_before' => '<span>',
						'link_after' => '</span>',
					) ); ?>
				</div>
			</article>
			<?php if ( ! $az_neutral_locked && ( comments_open() || get_comments_number() ) ) : ?>
			<section class="az-neutral-page__comments" aria-label="<?php esc_attr_e( 'Bình luận', 'az-ai-theme' ); ?>"><?php comments_template(); ?></section>
			<?php endif; ?>
		</div>
	</main>
	<?php
endwhile;
get_footer();
