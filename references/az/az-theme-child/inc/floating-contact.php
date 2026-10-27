<?php
/** Keep style 1 from the parent and the child-owned style 2 social launcher. */
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/inc/floating-contact.php';

function az_child_register_social_contact_style() {
    if ( ! is_admin() || ! class_exists( 'AZ_Options_Registry' ) ) { return; }
    $section = AZ_Options_Registry::get( 'social' );
    if ( ! $section ) { return; }
    foreach ( $section['fields'] as &$field ) {
        if ( 'contact_style' === ( $field['id'] ?? '' ) ) {
            $field['default'] = 'icons';
            $field['options'] = array( 'icons' => 'Kiểu 1 – Icon tròn tối giản', 'social_expand' => 'Kiểu 2 – Nút liên hệ mở rộng tất cả Social' );
            $field['description'] = 'Kiểu 1 hiển thị icon Messenger, Zalo và Hotline. Kiểu 2 bấm nút Liên hệ để mở các kênh Social Networks và Messaging đã nhập liên kết. Chỉ hiện nút đã nhập link hoặc số điện thoại; nút nhắn tin Facebook lấy link từ mục Messenger bên dưới. Các kiểu cũ còn lại dùng Kiểu 1.';
        }
    }
    unset( $field );
    AZ_Options_Registry::add_section( 'social', $section['title'], $section['fields'] );
}
add_action( 'after_setup_theme', 'az_child_register_social_contact_style', 20 );

function az_child_social_contact_items() {
    if ( ! az_get_option( 'social.contact_enabled', 0 ) ) { return array(); }
    $items = array();
    $links = az_get_social_links();
    foreach ( az_child_steel_social_icons() as $network => $icon ) {
        // The parent helper returns escaped URLs; decode before escaping once at render.
        $raw = $links[ $network ] ?? '';
        $url = is_string( $raw ) ? esc_url_raw( html_entity_decode( $raw, ENT_QUOTES, 'UTF-8' ), array( 'https', 'http' ) ) : '';
        if ( ! $url ) { continue; }
        $items[] = array( 'network' => $network, 'label' => $icon[0], 'url' => $url, 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">' . $icon[1] . '</svg>' );
    }
    $raw_phone = az_get_option( 'social.contact_phone', '' );
    $phone = is_scalar( $raw_phone ) ? trim( (string) $raw_phone ) : '';
    $dial = preg_replace( '/[^0-9+]/', '', $phone );
    if ( preg_match( '/^\+?[0-9]{6,15}$/', $dial ) ) {
        $items[] = array( 'network' => 'phone', 'label' => 'Hotline ' . $phone, 'url' => 'tel:' . $dial, 'svg' => az_contact_icon( 'phone-solid' ) );
    }
    return $items;
}

function az_child_contact_assets() {
    if ( 'social_expand' !== az_get_option( 'social.contact_style', 'icons' ) ) { az_contact_assets(); return; }
    if ( ! az_child_social_contact_items() ) { return; }
    $parent = get_template_directory();
    $child = get_stylesheet_directory();
    wp_enqueue_style( 'az-floating-contact', get_template_directory_uri() . '/css/floating-contact.css', array(), filemtime( $parent . '/css/floating-contact.css' ) );
    wp_enqueue_style( 'az-child-social-contact', get_stylesheet_directory_uri() . '/css/social-contact.css', array( 'az-floating-contact' ), filemtime( $child . '/css/social-contact.css' ) );
    wp_enqueue_script( 'az-child-social-contact', get_stylesheet_directory_uri() . '/js/social-contact.js', array(), filemtime( $child . '/js/social-contact.js' ), true );
}

function az_child_render_contact() {
    if ( 'social_expand' !== az_get_option( 'social.contact_style', 'icons' ) ) { az_render_contact(); return; }
    $items = az_child_social_contact_items();
    if ( ! $items ) { return; }
    ?>
    <aside class="az-contact az-contact--social-expand" aria-label="Liên hệ và mạng xã hội" data-az-social-contact>
      <nav id="az-contact-social-links" class="az-contact__links" aria-label="Các kênh liên hệ" hidden>
        <?php foreach ( $items as $item ) : ?>
        <a class="az-contact__link az-contact-social__<?php echo esc_attr( $item['network'] ); ?>" href="<?php echo esc_url( $item['url'] ); ?>" title="<?php echo esc_attr( $item['label'] ); ?>" aria-label="<?php echo esc_attr( $item['label'] . ( 'phone' === $item['network'] ? '' : ' (mở tab mới)' ) ); ?>"<?php if ( 'phone' !== $item['network'] ) : ?> target="_blank" rel="noopener noreferrer"<?php endif; ?>><?php echo $item['svg']; ?></a>
        <?php endforeach; ?>
      </nav>
      <button class="az-contact__toggle" type="button" aria-controls="az-contact-social-links" aria-expanded="false" aria-label="Mở các kênh liên hệ">
        <span class="az-social-contact-open" aria-hidden="true"><?php echo az_contact_icon( 'chat' ); ?></span>
        <svg class="az-social-contact-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="m6 6 12 12M18 6 6 18"/></svg>
      </button>
    </aside>
    <?php
}

// Dispatch between the parent icon buttons (3) and the social launcher (6).
remove_action( 'wp_enqueue_scripts', 'az_contact_assets', 35 );
remove_action( 'wp_footer', 'az_render_contact', 5 );
add_action( 'wp_enqueue_scripts', 'az_child_contact_assets', 35 );
add_action( 'wp_footer', 'az_child_render_contact', 5 );
