<?php
/** Shared floating contact controls inherited from the parent theme. */
defined( 'ABSPATH' ) || exit;

function az_contact_icon( $name ) {
    if ( 'messenger' === $name ) {
        return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="m3 16 7-9 5 5 6-5-7 10-5-5z"/></svg>';
    }
    if ( 'zalo' === $name ) {
        return '<svg viewBox="0 0 32 32" aria-hidden="true"><path fill="currentColor" d="M8 3h16a5 5 0 0 1 5 5v16a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5V8a5 5 0 0 1 5-5Z"/><path fill="#2189ed" d="M17 5C10 5 5 9 5 15c0 3 1.5 5.5 4 7L6 26l6-1.8c1.5.5 3 .8 5 .8 7 0 12-4 12-10S24 5 17 5Z"/><text x="17" y="18.5" text-anchor="middle" fill="currentColor" font-family="Arial,sans-serif" font-size="8" font-weight="700">Zalo</text></svg>';
    }
    if ( 'phone-solid' === $name ) {
        return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.61 21 3 13.39 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.24.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2Z"/></svg>';
    }
    $path = 'phone' === $name
        ? '<path d="M5 3h4l2 5-3 2c1 3 3 5 6 6l2-3 5 2v4c0 2-3 3-5 2C9 19 5 15 3 8 2 5 3 3 5 3Z"/>'
        : '<path d="M3 3h15v12h-8l-5 4v-4H3zM18 8h4v12h-4v3l-4-3h-3"/>';
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">' . $path . '</svg>';
}

function az_contact_data() {
    if ( ! az_get_option( 'social.contact_enabled', 0 ) ) { return array(); }
    $phone = trim( (string) az_get_option( 'social.contact_phone', '' ) );
    $dial = preg_replace( '/[^0-9+]/', '', $phone );
    $links = az_get_social_links();
    // The shared helper returns display-escaped URLs; escape once at render time.
    $zalo = esc_url_raw( html_entity_decode( $links['zalo'] ?? '', ENT_QUOTES, 'UTF-8' ), array( 'https', 'http' ) );
    $messenger = esc_url_raw( html_entity_decode( $links['messenger'] ?? '', ENT_QUOTES, 'UTF-8' ), array( 'https', 'http' ) );
    if ( ! preg_match( '/^\+?[0-9]{6,15}$/', $dial ) ) { $phone = ''; $dial = ''; }
    if ( ! $dial && ! $zalo && ! $messenger ) { return array(); }
    // The parent renders style 1; the child dispatcher handles style 2 separately.
    // Retired or missing selections retain their contact details in style 1.
    return array( 'phone' => $phone, 'dial' => $dial, 'zalo' => $zalo, 'messenger' => $messenger, 'style' => 'icons' );
}

function az_contact_assets() {
    if ( ! az_contact_data() ) { return; }
    $base = get_template_directory();
    wp_enqueue_style( 'az-floating-contact', get_template_directory_uri() . '/css/floating-contact.css', array(), filemtime( $base . '/css/floating-contact.css' ) );
}
add_action( 'wp_enqueue_scripts', 'az_contact_assets', 35 );

function az_render_contact() {
    $data = az_contact_data();
    if ( ! $data ) { return; }
    ?>
    <aside class="az-contact az-contact--<?php echo esc_attr( $data['style'] ); ?>" aria-label="Liên hệ tư vấn">
      <nav id="az-contact-links" class="az-contact__links" aria-label="Kênh liên hệ">
        <?php if ( $data['messenger'] ) : ?><a class="az-contact__link az-contact__messenger" href="<?php echo esc_url( $data['messenger'] ); ?>" target="_blank" rel="noopener noreferrer" title="Nhắn tin Facebook Messenger" aria-label="Nhắn tin Facebook Messenger (mở tab mới)"><span class="az-contact__icon" aria-hidden="true"><?php echo az_contact_icon( 'messenger' ); ?></span><span>Nhắn tin Messenger</span></a><?php endif; ?>
        <?php if ( $data['zalo'] ) : ?><a class="az-contact__link az-contact__zalo" href="<?php echo esc_url( $data['zalo'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Chat với chúng tôi qua Zalo (mở tab mới)"><span class="az-contact__icon" aria-hidden="true"><?php echo az_contact_icon( 'zalo' ); ?></span><span>Chat qua Zalo</span></a><?php endif; ?>
        <?php if ( $data['dial'] ) : ?><a class="az-contact__link az-contact__phone" href="tel:<?php echo esc_attr( $data['dial'] ); ?>" aria-label="<?php echo esc_attr( 'Gọi hotline ' . $data['phone'] ); ?>"><span class="az-contact__icon" aria-hidden="true"><?php echo az_contact_icon( 'phone-solid' ); ?></span><span><small>Hotline tư vấn</small><strong><?php echo esc_html( $data['phone'] ); ?></strong></span></a><?php endif; ?>
      </nav>
    </aside>
    <?php
}
add_action( 'wp_footer', 'az_render_contact', 5 );
