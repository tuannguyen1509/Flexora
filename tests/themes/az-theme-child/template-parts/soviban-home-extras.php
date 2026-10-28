<?php
/** Optional existing homepage groups. Shared queries/renderers retain their owners. */
defined( 'ABSPATH' ) || exit;
$az_child_extras = $args['home'];
?>
<div class="az-child-home__extras az-child-home__container">
	<?php if ( ! empty( $az_child_extras['products']['enabled'] ) && az_is_woocommerce_active() ) : foreach ( $az_child_extras['products']['sections'] as $az_child_section ) : ?>
	<section class="az-child-home__extra"><h2><?php echo esc_html( $az_child_section['title'] ); ?></h2><div class="az-child-home__cards">
		<?php foreach ( az_child_steel_products( $az_child_section['category_ids'] ?? array( $az_child_section['category_id'] ?? 0 ), false, 4 ) as $az_child_product ) { az_render_product_card( $az_child_product, array( 'context' => 'child-home' ) ); } ?>
	</div></section>
	<?php endforeach; endif; ?>
	<?php foreach ( array( 'blog' => 4, 'projects' => 3 ) as $az_child_group => $az_child_limit ) : if ( empty( $az_child_extras[ $az_child_group ]['enabled'] ) ) { continue; } ?>
	<section class="az-child-home__extra"><h2><?php echo esc_html( $az_child_extras[ $az_child_group ]['title'] ); ?></h2><div class="az-child-home__cards">
		<?php foreach ( az_child_home_articles( $az_child_extras[ $az_child_group ], $az_child_limit ) as $az_child_article ) : ?>
		<article><a href="<?php echo esc_url( get_permalink( $az_child_article ) ); ?>"><?php az_child_home_post_image( $az_child_article ); ?><h3><?php echo esc_html( get_the_title( $az_child_article ) ); ?></h3></a></article>
		<?php endforeach; ?>
	</div><a href="<?php echo esc_url( az_child_home_archive_url( $az_child_extras[ $az_child_group ], 'projects' === $az_child_group ? '/du-an/' : '/tin-tuc/' ) ); ?>"><?php esc_html_e( 'Xem thêm', 'az-theme-child' ); ?> →</a></section>
	<?php endforeach; ?>
	<?php if ( ! empty( $az_child_extras['video']['enabled'] ) ) : ?><section class="az-child-home__extra"><h2><?php echo esc_html( $az_child_extras['video']['title'] ); ?></h2><div class="az-child-home__cards">
		<?php foreach ( $az_child_extras['video']['items'] as $az_child_index => $az_child_video ) : $az_child_video_url = az_home_youtube_embed_url( $az_child_video ); if ( ! $az_child_video_url ) { continue; } ?>
		<iframe class="az-child-home__video" src="<?php echo esc_url( $az_child_video_url ); ?>" title="<?php echo esc_attr( $az_child_extras['video']['title'] . ' ' . ( $az_child_index + 1 ) ); ?>" loading="lazy" allow="encrypted-media; picture-in-picture" allowfullscreen></iframe>
		<?php endforeach; ?>
	</div></section><?php endif; ?>
</div>
