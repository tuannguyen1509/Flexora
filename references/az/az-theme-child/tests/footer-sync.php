<?php
/** Render all footer entry points against shared settings without a WordPress database. */
define( 'ABSPATH', __DIR__ );
$theme = dirname( __DIR__ );
$options = array( 'page_on_front' => 10 );
$home = true; $footer_calls = 0; $partner_calls = 0;
$settings = array( 'general' => array(
    'company_name' => 'CTY TNHH MTV NĂNG LƯỢNG TƯỜNG VINH',
    'addresses' => array( array( 'label' => 'Showroom', 'value' => '660 Cách Mạng Tháng 8, Tây Ninh', 'maps_url' => 'https://maps.app.goo.gl/showroom', 'maps_embed' => '<iframe src="https://www.google.com/maps/embed?pb=test"></iframe>' ), array( 'label' => 'Chi nhánh', 'value' => '39 Dương Thị Giang, Củ Chi, TP HCM', 'maps_url' => 'https://maps.app.goo.gl/branch' ), array( 'label' => 'Chi nhánh không gắn link', 'value' => 'QL22 B, Hòa Thành, Tây Ninh', 'maps_url' => '' ) ),
    'phones' => array( array( 'label' => 'Hotline', 'value' => '0933 003 911' ) ),
    'emails' => array( array( 'label' => 'Email', 'value' => 'tuongvinh9@gmail.com' ) ),
    'copyright' => '© Bản quyền thuộc về Tường Vinh',
    'footer_profile_image' => 123,
    'footer_profile_url' => 'https://example.test/profile.pdf?view=1&lang=vi',
    'footer_policies' => array( array( 'label' => 'Chính sách bảo hành', 'url' => '/bao-hanh/' ), array( 'label' => 'Chính sách thanh toán', 'url' => '/thanh-toan/' ) ),
), 'footer' => array( 'show_copyright' => true ) );
function add_action( ...$args ) {}
function get_option( $key, $fallback = '' ) { return $GLOBALS['options'][$key] ?? $fallback; }
function absint( $v ) { return abs( (int) $v ); }
function is_admin() { return true; }
function sanitize_key( $v ) { return strtolower(preg_replace('/[^a-z0-9_\-]/i', '', $v)); }
function wp_attachment_is_image( $id ) { return 123 === $id; }
function wp_get_attachment_image( $id, $size, $icon, $attr ) { return '<img class="' . esc_attr($attr['class']) . '" src="https://example.test/cover.png" width="600" height="800" alt="' . esc_attr($attr['alt']) . '" loading="lazy">'; }
function az_get_options() { return $GLOBALS['settings']; }
function az_get_social_links() { return array( 'facebook' => 'https://www.facebook.com/example' ); }
function az_get_contact_items( $type ) { return $GLOBALS['settings']['general'][array( 'address' => 'addresses', 'phone' => 'phones', 'email' => 'emails' )[$type]] ?? array(); }
function get_bloginfo( $key ) { return 'description' === $key ? 'Tường Vinh cung cấp giải pháp năng lượng mặt trời, máy lọc nước và thiết bị nhà bếp.' : 'TƯỜNG VINH TÂY NINH'; }
function sanitize_text_field( $v ) { return trim( strip_tags( (string) $v ) ); }
function sanitize_textarea_field( $v ) { return sanitize_text_field( $v ); }
function esc_attr( $v ) { return htmlspecialchars( (string) $v, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $v ) { return esc_attr( $v ); }
function esc_url_raw( $v, $protocols = array() ) { return preg_match( '#^https?://#i', (string) $v ) ? $v : ''; }
function esc_url( $v ) { return esc_attr( esc_url_raw( $v ) ); }
function wp_parse_url( $v, $component ) { return parse_url( $v, $component ); }
function home_url( $v = '' ) { return 'https://example.test' . $v; }
function get_stylesheet_directory() { return $GLOBALS['theme']; }
function get_stylesheet_directory_uri() { return 'https://example.test/wp-content/themes/az-theme-child'; }
function az_child_is_steel_home() { return $GLOBALS['home']; }
function az_child_home_partners_render() { $GLOBALS['partner_calls']++; echo '<section class="test-partners">ĐỐI TÁC CỦA CHÚNG TÔI</section>'; }
function az_child_steel_social_links() { echo '<div class="az-steel-social">Kênh mạng xã hội</div>'; }
function wp_footer() { $GLOBALS['footer_calls']++; }
require dirname($theme) . '/az-theme/inc/options/class-registry.php';
require dirname($theme) . '/az-theme/inc/options/class-sanitizer.php';
require $theme . '/inc/footer-storefront.php';
function render_footer( $is_home, $entry = '/footer.php' ) {
    $GLOBALS['home'] = $is_home;
    $GLOBALS['footer_calls'] = 0; $GLOBALS['partner_calls'] = 0;
    ob_start(); require get_stylesheet_directory() . $entry; return ob_get_clean();
}
function footer_markup( $html ) { preg_match( '#<footer\b.*?</footer>#s', $html, $m ); return $m[0] ?? ''; }
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } echo "PASS $message\n"; }
$homepage = render_footer( true );
check( 1 === $footer_calls && 1 === $partner_calls, 'homepage runs wp_footer once and keeps its partner section' );
check( 4 === substr_count(footer_markup($homepage), '<section ') && strpos($homepage, 'Hồ sơ năng lực') !== false, 'homepage retains its four content columns' );
check( strpos($homepage,'/icon/location-button-2.png') !== false && strpos($homepage,'az-footer-location-map') === false && strpos($homepage,'google.com/maps/embed') === false, 'homepage shows directions and static parking icons without a Maps iframe' );
check( 2 === substr_count($homepage,'class="az-footer-location-actions"') && 2 === substr_count($homepage,'<span class="az-footer-location-action">') && strpos($homepage,'QL22 B, Hòa Thành, Tây Ninh') !== false, 'only linked locations create icon groups while unlinked addresses remain in company contacts' );
foreach ( array( 'shop', 'category', 'product', 'news', 'page', 'search', '404' ) as $type ) {
    $html = render_footer( false, 'shop' === $type ? '/footer-shop.php' : '/footer.php' );
    check( footer_markup($html) === footer_markup($homepage), "$type renders the identical homepage footer" );
    check( 1 === $footer_calls && 0 === $partner_calls && 1 === substr_count($html, '</html>'), "$type uses one closing document and no homepage partners" );
}
check( footer_markup(render_footer(false, '/template-parts/footer-home.php')) === footer_markup($homepage), 'legacy footer entry also delegates to the shared footer' );
$saved_addresses = $settings['general']['addresses'];
foreach ( $settings['general']['addresses'] as &$address ) { $address['maps_url'] = ''; }
unset( $address );
$settings['general']['google_maps_url'] = 'https://maps.app.goo.gl/stale-fallback';
$cleared = render_footer(false);
check( strpos($cleared,'az-footer-location-actions') === false && strpos($cleared,'660 Cách Mạng Tháng 8, Tây Ninh') !== false, 'clearing every branch link hides all icon groups without removing contact addresses' );
$settings['general']['addresses'] = $saved_addresses;
unset( $settings['general']['google_maps_url'] );
$settings['footer']['show_copyright'] = false;
check( strpos(render_footer(false), 'az-reference-footer__copyright') === false, 'copyright visibility follows Advanced settings' );
check( strpos($homepage, 'href="https://example.test/profile.pdf?view=1&amp;lang=vi"') !== false && strpos($homepage, 'aria-label="Xem Hồ sơ năng lực (mở tab mới)"') !== false, 'cover opens the configured document URL with a labelled safe link' );
check( strpos($homepage, 'Danh mục sản phẩm chân trang') === false && strpos($homepage, ' download') === false, 'profile replaces the old category menu without forcing download' );
$settings['general']['footer_profile_url'] = '';
$html = render_footer(false);
check( strpos($html, '<div class="az-footer-profile-preview">') !== false, 'image without a document URL remains visible without an empty link' );
$settings['general']['footer_profile_url'] = 'javascript:alert(1)';
check( az_child_footer_data()['profile']['url'] === '', 'unsafe document URL is rejected' );
$settings['general']['footer_profile_image'] = 0;
$html = render_footer(false);
check( strpos($html,'az-footer-profile-title') === false && strpos($html,'az-reference-footer__grid--without-profile') !== false, 'cleared image hides the column and expands the other three columns' );
$settings['general']['footer_profile_image'] = 999;
check( az_child_footer_data()['profile']['image'] === '', 'non-image or deleted attachment cannot render as a cover' );
$settings['general']['footer_profile_title'] = '<b>Hồ sơ công ty</b>';
check( az_child_footer_data()['profile']['title'] === 'Hồ sơ công ty', 'profile title is plain text' );
AZ_Options_Registry::add_section('general','General',require dirname($theme).'/az-theme/inc/admin/sections/general.php');
az_child_footer_register_general_fields(); az_child_footer_register_general_fields();
$fields = AZ_Options_Registry::get('general')['fields'];
foreach(array('footer_profile_title'=>'text','footer_profile_image'=>'image','footer_profile_url'=>'url') as $key=>$type){
    $match=array_values(array_filter($fields,function($f) use($key){return $f['id']===$key;}));
    check( count($match) === 1 && $match[0]['type'] === $type, "$key registers once in General" );
    $value='image'===$type?'123':('url'===$type?'https://example.test/profile.pdf':'Hồ sơ năng lực');
    check( AZ_Options_Sanitizer::sanitize($value,$type,$match[0]) === ('image'===$type?123:$value), "$key saves through the parent sanitizer" );
}
check( strpos(file_get_contents($theme.'/css/home-reference-layout.css'), 'az-reference-footer') === false, 'homepage has no separate footer CSS overrides' );
check( strpos(file_get_contents($theme.'/functions.php'), "wp_enqueue_style( 'az-footer-storefront'") !== false, 'shared footer stylesheet remains registered for every page' );
