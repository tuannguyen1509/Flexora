<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="flx-main-content" class="flx-main">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'flx-entry' ); ?> id="post-<?php the_ID(); ?>">
			<?php if ( is_singular() ) : ?>
				<h1><?php the_title(); ?></h1>
			<?php else : ?>
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<?php endif; ?>
			<?php the_content(); ?>
			<?php wp_link_pages(); ?>
		</article>
		<?php endwhile; ?>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<h1><?php echo esc_html__( 'No content found', 'flexora' ); ?></h1>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
