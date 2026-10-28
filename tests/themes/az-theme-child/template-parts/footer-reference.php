<?php
/** Shared pale footer; contacts and Fanpage use General/Social settings. */
defined( 'ABSPATH' ) || exit;
$az_footer_profile = $az_footer_data['profile'];
?>
<?php if ( az_child_is_steel_home() ) { az_child_home_partners_render(); } ?>
<?php if ( function_exists( 'az_child_soviban_data' ) ) : ?>
<?php require get_stylesheet_directory() . '/template-parts/footer-soviban.php'; ?>
<?php else : ?>
<footer class="az-reference-footer"><div class="az-steel-container az-reference-footer__grid<?php echo $az_footer_profile['image'] ? '' : ' az-reference-footer__grid--without-profile'; ?>">
  <section aria-labelledby="az-footer-contact-title"><h2 id="az-footer-contact-title"><?php echo esc_html( $az_footer_data['company'] ?: get_bloginfo( 'name' ) ); ?></h2>
    <?php if ( get_bloginfo( 'description' ) ) : ?><p class="az-home-footer-description"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p><?php endif; ?>
    <ul class="az-footer-contact-list">
      <?php foreach ( $az_footer_data['addresses'] as $item ) { az_child_footer_contact_row( 'pin', $item['label'] ?? '', $item['value'] ?? '' ); } ?>
      <?php foreach ( $az_footer_data['phones'] as $item ) { $number = preg_replace( '/[^0-9+]/', '', $item['value'] ?? '' ); az_child_footer_contact_row( 'phone', $item['label'] ?? 'Hotline', $item['value'] ?? '', $number ? 'tel:' . $number : '' ); } ?>
      <?php foreach ( $az_footer_data['emails'] as $item ) { az_child_footer_contact_row( 'email', $item['label'] ?? '', $item['value'] ?? '', 'mailto:' . ( $item['value'] ?? '' ) ); } ?>
      <?php foreach ( array( 'tax_code' => 'MST', 'business_registration' => 'Đăng ký kinh doanh', 'registration_date' => 'Ngày cấp', 'registration_authority' => 'Nơi cấp' ) as $field => $label ) { if ( $az_footer_data[ $field ] ) { ?><li><?php echo esc_html( $label . ': ' . $az_footer_data[ $field ] ); ?></li><?php } } ?>
    </ul>
  </section>
  <section aria-labelledby="az-footer-policy-title"><h2 id="az-footer-policy-title">Hỗ trợ khách hàng</h2><nav aria-label="Chính sách của công ty"><ul class="az-home-footer-links"><?php foreach ( $az_footer_data['policies'] as $policy ) : ?><li><a href="<?php echo esc_url( $policy['url'] ); ?>"><?php echo esc_html( $policy['label'] ); ?></a></li><?php endforeach; ?></ul></nav></section>
  <?php if ( $az_footer_profile['image'] ) : ?>
  <section aria-labelledby="az-footer-profile-title"><h2 id="az-footer-profile-title"><?php echo esc_html( $az_footer_profile['title'] ); ?></h2>
    <?php if ( $az_footer_profile['url'] ) : ?>
    <a class="az-footer-profile-preview" href="<?php echo esc_url( $az_footer_profile['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( 'Xem ' . $az_footer_profile['title'] . ' (mở tab mới)' ); ?>"><?php echo $az_footer_profile['image']; ?></a>
    <?php else : ?><div class="az-footer-profile-preview"><?php echo $az_footer_profile['image']; ?></div><?php endif; ?>
  </section>
  <?php endif; ?>
  <section aria-labelledby="az-footer-fanpage-title"><h2 id="az-footer-fanpage-title">Fanpage</h2>
    <?php if ( $az_footer_data['facebook'] ) : $az_embed = 'https://www.facebook.com/plugins/page.php?' . http_build_query( array( 'href' => $az_footer_data['facebook'], 'tabs' => '', 'width' => 340, 'height' => 150, 'small_header' => false, 'adapt_container_width' => true, 'hide_cover' => false, 'show_facepile' => false ), '', '&', PHP_QUERY_RFC3986 ); ?>
    <div class="az-footer-fanpage-frame"><iframe src="<?php echo esc_url( $az_embed ); ?>" width="340" height="150" title="Fanpage Facebook của <?php echo esc_attr( $az_footer_data['company'] ?: get_bloginfo( 'name' ) ); ?>" loading="lazy" allow="encrypted-media; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin"></iframe></div><a class="az-footer-fanpage-link" href="<?php echo esc_url( $az_footer_data['facebook'] ); ?>" target="_blank" rel="noopener noreferrer">Xem Fanpage <span aria-hidden="true">↗</span></a>
    <?php else : ?><p>Theo dõi các kênh của chúng tôi để cập nhật thông tin mới nhất.</p><?php az_child_steel_social_links(); endif; ?>
    <?php foreach ( $az_footer_data['locations'] as $az_footer_location ) { az_child_footer_location_actions( $az_footer_location ); } ?>
  </section>
</div>
<?php if ( ! empty( $az_footer_data['copyright'] ) && ( ! array_key_exists( 'show_copyright', $az_footer_options ) || ! empty( $az_footer_options['show_copyright'] ) ) ) : ?><div class="az-reference-footer__copyright"><div class="az-steel-container"><?php echo esc_html( $az_footer_data['copyright'] ); ?></div></div><?php endif; ?>
</footer>
<?php endif; ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
