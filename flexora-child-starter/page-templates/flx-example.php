<?php
/* Template Name: Flexora Example */
defined( 'ABSPATH' ) || exit;
$hero = flx_data( 'hero', array() );
$features = flx_data( 'features.items', array() );
get_header();
?>
<main id="flx-main-content" class="flx-shell-main flx-example">
	<?php if ( '' !== (string) $hero['title'] ) : ?><h1><?php echo esc_html( $hero['title'] ); ?></h1><?php endif; ?>
	<div class="flx-example-description"><?php echo wp_kses_post( (string) $hero['description'] ); ?></div>
	<?php if ( $hero['image'] ) { echo wp_get_attachment_image( $hero['image'], 'large' ); } ?>
	<?php if ( $hero['button_url'] && $hero['button_text'] ) : ?><a class="flx-example-button" href="<?php echo esc_url( $hero['button_url'] ); ?>"><?php echo esc_html( $hero['button_text'] ); ?></a><?php endif; ?>
	<div class="flx-example-features">
	<?php foreach ( is_array( $features ) ? $features : array() as $feature ) : if ( ! isset( $feature['enabled'] ) || ! $feature['enabled'] ) { continue; } ?>
		<article class="flx-example-feature"><h2><?php echo esc_html( $feature['title'] ); ?></h2><div><?php echo wp_kses_post( $feature['description'] ); ?></div><?php if ( $feature['image'] ) { echo wp_get_attachment_image( $feature['image'], 'medium' ); } ?></article>
	<?php endforeach; ?>
	</div>
</main>
<?php get_footer(); ?>
