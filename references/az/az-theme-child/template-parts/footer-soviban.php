<?php
/** Same configured SOVIBAN footer for every child entrypoint. */
defined( 'ABSPATH' ) || exit;
$az_child_footer = az_child_soviban_data();
$az_child_footer_options = az_footer_options();
?>
<footer class="az-child-footer" <?php if ( $az_child_footer['footer_background'] ) : ?>style="<?php echo esc_attr( 'background-image:url(' . wp_json_encode( az_child_soviban_url( $az_child_footer['footer_background'] ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . ')' ); ?>"<?php endif; ?>>
	<div class="az-child-footer__container">
		<div class="az-child-footer__grid">
			<section><h2><?php echo esc_html( $az_child_footer['footer_title'] ); ?></h2><p><?php echo nl2br( esc_html( $az_child_footer['footer_description'] ) ); ?></p></section>
			<section><h2><?php echo esc_html( $az_footer_data['company'] ); ?></h2>
				<ul class="az-child-footer__contacts">
					<?php foreach ( $az_footer_data['addresses'] as $az_child_item ) : ?><li><?php echo esc_html( ( $az_child_item['label'] ?? __( 'Địa chỉ', 'az-theme-child' ) ) . ': ' . $az_child_item['value'] ); ?></li><?php endforeach; ?>
					<?php if ( $az_footer_data['tax_code'] ) : ?><li><?php echo esc_html( __( 'Mã số doanh nghiệp: ', 'az-theme-child' ) . $az_footer_data['tax_code'] ); ?></li><?php endif; ?>
					<?php foreach ( array( 'fax' => 'Fax', 'business_registration' => 'Đăng ký kinh doanh', 'registration_date' => 'Ngày cấp', 'registration_authority' => 'Nơi cấp' ) as $az_child_key => $az_child_label ) : if ( ! $az_footer_data[ $az_child_key ] ) { continue; } ?><li><?php echo esc_html( $az_child_label . ': ' . $az_footer_data[ $az_child_key ] ); ?></li><?php endforeach; ?>
					<?php foreach ( $az_footer_data['emails'] as $az_child_item ) : ?><li><?php esc_html_e( 'Email: ', 'az-theme-child' ); ?><a href="<?php echo esc_url( 'mailto:' . $az_child_item['value'] ); ?>"><?php echo esc_html( $az_child_item['value'] ); ?></a></li><?php endforeach; ?>
					<?php foreach ( $az_footer_data['phones'] as $az_child_item ) : ?><li><?php echo esc_html( ( $az_child_item['label'] ?? __( 'Hotline', 'az-theme-child' ) ) . ': ' ); ?><a class="az-child-footer__hotline" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $az_child_item['value'] ) ); ?>"><?php echo esc_html( $az_child_item['value'] ); ?></a></li><?php endforeach; ?>
				</ul>
			</section>
			<?php if ( ! empty( $az_child_footer_options['show_social'] ) || $az_footer_data['locations'] ) : ?><section><?php if ( ! empty( $az_child_footer_options['show_social'] ) ) : ?><h2><?php esc_html_e( 'Kết nối với chúng tôi', 'az-theme-child' ); ?></h2><?php az_child_steel_social_links(); ?><?php endif; ?><?php foreach ( $az_footer_data['locations'] as $az_child_location ) { az_child_footer_location_actions( $az_child_location ); } ?></section><?php endif; ?>
		</div>
		<?php if ( $az_footer_data['profile']['image'] ) : ?><div class="az-child-footer__profile"><?php if ( $az_footer_data['profile']['url'] ) : ?><a href="<?php echo esc_url( $az_footer_data['profile']['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php endif; ?><?php echo $az_footer_data['profile']['image']; ?><?php if ( $az_footer_data['profile']['url'] ) : ?></a><?php endif; ?></div><?php endif; ?>
		<div class="az-child-footer__signature">
			<?php if ( $az_child_footer['footer_logo'] ) : ?><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( $az_child_footer['footer_logo'] ); ?>" width="120" height="120" alt="<?php echo esc_attr( $az_footer_data['company'] ); ?>" loading="lazy" decoding="async"></a><?php endif; ?>
			<p><?php echo esc_html( $az_child_footer['footer_tagline'] ); ?></p><p><?php echo esc_html( $az_child_footer['footer_note'] ); ?></p>
			<?php $az_child_general = az_get_option( 'general', array() ); if ( ! empty( $az_child_footer_options['show_top_links'] ) && is_array( $az_child_general ) && array_key_exists( 'footer_policies', $az_child_general ) && $az_footer_data['policies'] ) : ?>
			<nav aria-label="<?php esc_attr_e( 'Chính sách', 'az-theme-child' ); ?>"><ul class="az-child-footer__policies"><?php foreach ( $az_footer_data['policies'] as $az_child_policy ) : ?><li><a href="<?php echo esc_url( $az_child_policy['url'] ); ?>"><?php echo esc_html( $az_child_policy['label'] ); ?></a></li><?php endforeach; ?></ul></nav>
			<?php endif; ?>
			<?php if ( ! empty( $az_child_footer_options['show_copyright'] ) && $az_footer_data['copyright'] ) : ?><p class="az-child-footer__copyright"><?php echo esc_html( $az_footer_data['copyright'] ); ?></p><?php endif; ?>
		</div>
	</div>
</footer>
