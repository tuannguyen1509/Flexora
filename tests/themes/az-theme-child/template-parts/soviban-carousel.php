<?php
/** Child carousel markup, using the existing steel-storefront.js DOM contract. */
defined( 'ABSPATH' ) || exit;
$az_child_carousel = $args;
$az_child_slides = $az_child_carousel['slides'];
if ( ! $az_child_slides ) { return; }
$az_child_banner = $az_child_carousel['banner'] ?? array();
$az_child_features = 'skyline' === $az_child_carousel['type'] && is_array( $az_child_carousel['features'] ?? null ) ? $az_child_carousel['features'] : array();
$az_child_height = is_numeric( $az_child_banner['height'] ?? '' ) ? max( 180, min( 1000, absint( $az_child_banner['height'] ) ) ) . 'px' : 'auto';
$az_child_mobile_height = is_numeric( $az_child_banner['mobile_height'] ?? '' ) ? max( 140, min( 700, absint( $az_child_banner['mobile_height'] ) ) ) . 'px' : 'auto';
?>
<section class="az-child-carousel az-child-carousel--<?php echo esc_attr( $az_child_carousel['type'] ); ?>" data-az-steel-slider data-autoplay="<?php echo ! empty( $az_child_carousel['autoplay'] ) ? '1' : '0'; ?>" tabindex="0" role="region" aria-roledescription="carousel" aria-label="<?php echo esc_attr( $az_child_carousel['label'] ); ?>" style="--az-child-banner-height:<?php echo esc_attr( $az_child_height ); ?>;--az-child-banner-mobile-height:<?php echo esc_attr( $az_child_mobile_height ); ?>;--az-child-banner-overlay:<?php echo esc_attr( max( 0, min( 90, absint( $az_child_banner['overlay'] ?? 0 ) ) ) / 100 ); ?>">
	<div class="az-child-carousel__stage<?php echo ! empty( $az_child_features[0] ) ? ' az-child-carousel__stage--feature' : ''; ?>">
	<?php foreach ( $az_child_slides as $az_child_index => $az_child_slide ) : ?>
		<div class="az-child-carousel__slide" data-az-steel-slide <?php echo $az_child_index ? 'hidden' : ''; ?>>
			<?php $az_child_link = az_child_soviban_url( $az_child_slide['url'] ?? '' ); if ( $az_child_link ) : ?><a href="<?php echo esc_url( $az_child_link ); ?>"><?php endif; ?>
			<?php if ( ! empty( $az_child_slide['id'] ) ) {
				echo wp_get_attachment_image( $az_child_slide['id'], 'full', false, array( 'loading' => $az_child_index ? 'lazy' : 'eager', 'fetchpriority' => $az_child_index ? 'auto' : 'high' ) );
			} else { ?>
				<img src="<?php echo esc_url( $az_child_slide['image'] ); ?>" alt="<?php echo esc_attr( $az_child_slide['title'] ?? '' ); ?>" width="1920" height="<?php echo 'hero' === $az_child_carousel['type'] ? '700' : '600'; ?>" loading="<?php echo 'hero' === $az_child_carousel['type'] && ! $az_child_index ? 'eager' : 'lazy'; ?>" <?php if ( 'hero' === $az_child_carousel['type'] && ! $az_child_index ) { echo 'fetchpriority="high"'; } ?>>
			<?php } ?>
			<?php if ( $az_child_link ) : ?></a><?php endif; ?>
		</div>
	<?php endforeach; ?>
	<?php foreach ( $az_child_features as $az_child_feature_index => $az_child_feature ) : ?>
		<article class="az-child-skyline-feature<?php echo $az_child_feature['image'] ? '' : ' az-child-skyline-feature--text-only'; ?>" data-az-steel-feature="<?php echo absint( $az_child_feature_index ); ?>" <?php echo $az_child_feature_index ? 'hidden' : ''; ?>>
			<?php if ( $az_child_feature['image'] ) : ?><img class="az-child-skyline-feature__image" src="<?php echo esc_url( $az_child_feature['image'] ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $az_child_feature['title'] ) ); ?>" width="1000" height="667" loading="lazy" decoding="async"><?php endif; ?>
			<div class="az-child-skyline-feature__copy">
				<?php if ( '' !== $az_child_feature['title'] ) : ?><h3 class="az-child-skyline-feature__title"><?php if ( $az_child_feature['url'] ) : ?><a href="<?php echo esc_url( $az_child_feature['url'] ); ?>"><?php endif; ?><?php echo esc_html( $az_child_feature['title'] ); ?><?php if ( $az_child_feature['url'] ) : ?></a><?php endif; ?></h3><?php endif; ?>
				<?php if ( '' !== $az_child_feature['description'] ) : ?><div class="az-child-skyline-feature__description"><?php echo wp_kses_post( wpautop( $az_child_feature['description'] ) ); ?></div><?php endif; ?>
				<?php if ( $az_child_feature['url'] ) : ?><a class="az-child-skyline-feature__link" href="<?php echo esc_url( $az_child_feature['url'] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Xem chi tiết: %s', 'az-theme-child' ), $az_child_feature['title'] ) ); ?>"><span aria-hidden="true">›</span></a><?php endif; ?>
			</div>
		</article>
	<?php endforeach; ?>
	<?php if ( '' !== ( $az_child_banner['text'] ?? '' ) || '' !== ( $az_child_banner['subtitle'] ?? '' ) || ( '' !== ( $az_child_banner['button_text'] ?? '' ) && ! empty( $az_child_banner['button_url'] ) ) ) : ?>
		<div class="az-child-carousel__copy">
			<?php if ( '' !== ( $az_child_banner['text'] ?? '' ) ) : ?><h2><?php echo esc_html( $az_child_banner['text'] ); ?></h2><?php endif; ?>
			<?php if ( '' !== ( $az_child_banner['subtitle'] ?? '' ) ) : ?><p><?php echo esc_html( $az_child_banner['subtitle'] ); ?></p><?php endif; ?>
			<?php if ( '' !== ( $az_child_banner['button_text'] ?? '' ) && ! empty( $az_child_banner['button_url'] ) ) : ?><a class="az-button" href="<?php echo esc_url( az_child_soviban_url( $az_child_banner['button_url'] ) ); ?>"><?php echo esc_html( $az_child_banner['button_text'] ); ?></a><?php endif; ?>
		</div>
	<?php endif; ?>
	<?php if ( count( $az_child_slides ) > 1 ) : ?>
		<button class="az-child-carousel__arrow az-child-carousel__arrow--prev" type="button" data-az-steel-prev aria-label="<?php esc_attr_e( 'Ảnh trước', 'az-theme-child' ); ?>">‹</button>
		<button class="az-child-carousel__arrow az-child-carousel__arrow--next" type="button" data-az-steel-next aria-label="<?php esc_attr_e( 'Ảnh tiếp theo', 'az-theme-child' ); ?>">›</button>
	<?php endif; ?>
	</div>
	<?php if ( count( $az_child_slides ) > 1 ) : ?>
	<div class="az-child-carousel__tools">
		<?php foreach ( $az_child_slides as $az_child_index => $az_child_slide ) : ?><button class="az-child-carousel__dot" type="button" data-az-steel-dot="<?php echo absint( $az_child_index ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Xem ảnh %d', 'az-theme-child' ), $az_child_index + 1 ) ); ?>" aria-pressed="<?php echo $az_child_index ? 'false' : 'true'; ?>"></button><?php endforeach; ?>
		<span class="az-sr-only" data-az-steel-status aria-live="polite">1 / <?php echo count( $az_child_slides ); ?></span>
		<?php if ( ! empty( $az_child_carousel['autoplay'] ) ) : ?><button class="az-child-carousel__pause" type="button" data-az-steel-pause aria-label="<?php esc_attr_e( 'Tạm dừng tự chuyển banner', 'az-theme-child' ); ?>" aria-pressed="false">Ⅱ</button><?php endif; ?>
	</div>
	<?php endif; ?>
</section>
