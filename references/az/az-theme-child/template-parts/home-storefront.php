<?php
/** SOVIBAN homepage. Website layout; parent banner data and child content contracts retained. */
defined( 'ABSPATH' ) || exit;
if ( post_password_required() ) { echo '<main id="main" class="az-steel-container">' . get_the_password_form() . '</main>'; return; }
$az_child_page_id = get_queried_object_id();
$az_child_home = az_child_soviban_data( $az_child_page_id );
$az_child_banner = az_child_soviban_banner( $az_child_page_id );
$az_child_slides = array();
foreach ( $az_child_banner['ids'] as $az_child_id ) { $az_child_slides[] = array( 'id' => $az_child_id, 'url' => '' !== $az_child_banner['button_text'] ? '' : $az_child_banner['button_url'] ); }
if ( ! $az_child_slides && $az_child_banner['has_media'] ) { $az_child_slides[] = array( 'image' => az_child_soviban_asset( 'hero.png' ), 'title' => 'Đảm bảo chất lượng lâu dài', 'url' => '' !== $az_child_banner['button_text'] ? '' : $az_child_banner['button_url'] ); }
?>
<main id="main" class="az-child-home" tabindex="-1">
	<?php if ( ! is_front_page() ) : ?><h1 class="az-sr-only"><?php echo esc_html( az_get_home_h1() ); ?></h1><?php endif; ?>
	<?php get_template_part( 'template-parts/soviban-carousel', null, array( 'type' => 'hero', 'label' => __( 'Banner trang chủ', 'az-theme-child' ), 'slides' => $az_child_slides, 'autoplay' => $az_child_banner['autoplay'], 'banner' => $az_child_banner ) ); ?>
	<?php if ( ! empty( $az_child_home['contact_band'] ) ) : $az_child_contact = az_child_footer_data(); ?>
	<div class="az-child-home__contact"><div class="az-child-home__container">
		<?php foreach ( $az_child_contact['phones'] as $az_child_item ) : ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $az_child_item['value'] ) ); ?>"><?php echo esc_html( $az_child_item['value'] ); ?></a><?php endforeach; ?>
		<?php foreach ( $az_child_contact['emails'] as $az_child_item ) : ?><a href="<?php echo esc_url( 'mailto:' . $az_child_item['value'] ); ?>"><?php echo esc_html( $az_child_item['value'] ); ?></a><?php endforeach; ?>
	</div></div>
	<?php endif; ?>
	<?php if ( ! empty( $az_child_home['intro_enabled'] ) ) : ?>
	<section id="az-home-services" class="az-child-home__intro az-child-home__container" aria-labelledby="az-child-intro-title">
		<h2 id="az-child-intro-title"><?php echo esc_html( $az_child_home['intro_title'] ); ?></h2>
		<div class="az-child-home__description"><?php echo wp_kses_post( wpautop( $az_child_home['intro_description'] ) ); ?></div>
		<div class="az-child-home__services">
		<?php foreach ( $az_child_home['services'] as $az_child_service ) : if ( empty( $az_child_service['image'] ) && empty( $az_child_service['title'] ) ) { continue; } $az_child_service_url = az_child_soviban_url( $az_child_service['url'] ?? '' ); ?>
			<article class="az-child-home__service">
				<?php if ( $az_child_service_url ) : ?><a href="<?php echo esc_url( $az_child_service_url ); ?>"><?php endif; ?>
				<?php if ( ! empty( $az_child_service['image'] ) ) : ?><img src="<?php echo esc_url( $az_child_service['image'] ); ?>" width="300" height="300" alt="" loading="lazy" decoding="async"><?php endif; ?>
				<h3><?php echo esc_html( $az_child_service['title'] ?? '' ); ?></h3>
				<?php if ( $az_child_service_url ) : ?></a><?php endif; ?>
			</article>
		<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>
	<?php if ( ! empty( $az_child_home['skyline_enabled'] ) ) {
		$az_child_skyline = array_values( array_filter( $az_child_home['skyline_items'], function( $item ) { return ! empty( $item['image'] ); } ) );
		get_template_part( 'template-parts/soviban-carousel', null, array( 'type' => 'skyline', 'label' => __( 'Thành phố và các tòa nhà', 'az-theme-child' ), 'slides' => $az_child_skyline, 'autoplay' => $az_child_home['skyline_autoplay'], 'features' => az_child_soviban_skyline_features( $az_child_home, $az_child_skyline ) ) );
	} ?>
	<section class="az-child-home__promos" aria-label="<?php esc_attr_e( 'Tư vấn và quản lý bất động sản', 'az-theme-child' ); ?>">
	<?php foreach ( az_child_soviban_promos( $az_child_page_id ) as $az_child_promo ) : if ( empty( $az_child_promo['image'] ) ) { continue; } $az_child_promo_url = az_child_soviban_url( $az_child_promo['url'] ?? '' ); ?>
		<?php if ( $az_child_promo_url ) : ?><a href="<?php echo esc_url( $az_child_promo_url ); ?>"><?php else : ?><div><?php endif; ?>
		<img src="<?php echo esc_url( $az_child_promo['image'] ); ?>" alt="<?php echo esc_attr( $az_child_promo['title'] ?? '' ); ?>" width="1170" height="398" loading="lazy" decoding="async">
		<?php echo $az_child_promo_url ? '</a>' : '</div>'; ?>
	<?php endforeach; ?>
	</section>
	<?php get_template_part( 'template-parts/soviban-home-extras', null, array( 'home' => az_home_page_get_data( $az_child_page_id ) ) ); ?>
</main>
