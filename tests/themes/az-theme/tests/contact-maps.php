<?php
/** Shared address-list Maps rendering, independent of WordPress/database access. */
define( 'ABSPATH', __DIR__ );
$settings = array(); $legacy = array(); $shortcodes = array(); $styles = array();
function get_option( $key, $default = null ) { return 'az_theme_options' === $key ? $GLOBALS['settings'] : ( $GLOBALS['legacy'][$key] ?? $default ); }
function add_action( ...$args ) {}
function add_shortcode( $name, $callback ) { $GLOBALS['shortcodes'][$name] = $callback; }
function shortcode_atts( $defaults, $atts, $tag = '' ) { return array_merge( $defaults, array_intersect_key( (array) $atts, $defaults ) ); }
function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function sanitize_textarea_field( $value ) { return sanitize_text_field( $value ); }
function sanitize_key( $value ) { return $value; }
function esc_html( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $value ) { return esc_html( $value ); }
function esc_url_raw( $value, $protocols = array() ) { return preg_match( '#^https?://#', (string) $value ) ? $value : ''; }
function esc_url( $value ) { return esc_attr( esc_url_raw( $value ) ); }
function wp_parse_url( $value, $component ) { return parse_url( $value, $component ); }
function get_template_directory() { return dirname( __DIR__ ); }
function get_template_directory_uri() { return '/az-theme'; }
function wp_enqueue_style( ...$args ) { $GLOBALS['styles'][] = $args; }
function get_bloginfo( $key ) { return 'Test company'; }
function get_queried_object_id() { return 0; }
function az_shared_banner_active() { return false; }
function get_header() {}
function get_footer() {}
function wp_nonce_field( ...$args ) {}
function admin_url( $path ) { return '/wp-admin/' . $path; }
function check( $ok, $label ) { if ( ! $ok ) { throw new RuntimeException( $label ); } echo "PASS $label\n"; }
function render_contact() { ob_start(); require dirname( __DIR__ ) . '/page-contact.php'; return ob_get_clean(); }
require dirname( __DIR__ ) . '/inc/options/class-sanitizer.php';
require dirname( __DIR__ ) . '/inc/helpers/options.php';
$fields = require dirname( __DIR__ ) . '/inc/admin/sections/general.php';
check( ! in_array( 'fallback', array_column( $fields, 'group' ), true ), 'parent removes the fallback address group' );
check( isset( $shortcodes['az_google_map'] ), 'map shortcode is registered in the parent' );
$legacy = array( 'az_contact_address' => 'Old address', 'az_contact_map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=old"></iframe>', 'az_contact_maps_url' => 'https://maps.app.goo.gl/old' );
$settings['general'] = array( 'address' => 'Stale address', 'google_maps_embed' => $legacy['az_contact_map_embed'], 'google_maps_url' => $legacy['az_contact_maps_url'] );
check( '' === az_google_map_shortcode() && '' === az_get_address() && array() === az_get_contact_items( 'address' ), 'retired fallback values never render' );
check( '' === az_general_shortcode( array( 'field' => 'google_maps_url' ) ), 'directions shortcode never reads retired values' );
az_google_map_assets();
check( ! $styles, 'no map data means no map asset' );
$rows = array(
    array( 'label' => '<b>Trụ sở</b>', 'value' => 'Địa chỉ & A', 'maps_url' => 'https://maps.app.goo.gl/head-office', 'maps_embed' => '<iframe src="https://www.google.com/maps/embed?pb=office&amp;lang=vi" onload="bad()"></iframe><script>bad()</script>' ),
    array( 'value' => 'Address without map' ),
    array( 'maps_embed' => '<iframe src="https://www.google.com/maps/embed?pb=map-only"></iframe>' ),
    array( 'maps_embed' => '<iframe src="https://www.google.com.evil.test/maps/embed"></iframe>' ),
    array( 'maps_embed' => '<iframe src="javascript:alert(1)"></iframe>' ),
    array( 'maps_embed' => array( 'invalid' ) ),
    'invalid row',
);
$settings['general']['addresses'] = $rows;
$maps = az_get_google_maps();
check( array( 1, 3 ) === array_column( $maps, 'index' ), 'valid maps retain address-list order and support iframe-only rows' );
$html = az_google_map_shortcode();
check( 2 === substr_count( $html, '<iframe ' ) && strpos( $html, 'pb=office') < strpos( $html, 'pb=map-only' ), 'all maps render in saved order' );
check( false === strpos( $html, 'onload=' ) && false === strpos( $html, '<script' ) && false === strpos( $html, 'evil.test' ), 'untrusted markup and iframe destinations are excluded' );
check( false !== strpos( $html, 'Địa chỉ &amp; A' ) && false !== strpos( $html, 'pb=office&amp;lang=vi' ) && false === strpos( $html, '&amp;amp;' ), 'labels, addresses and iframe URLs are escaped correctly' );
check( az_contact_shortcode( array( 'type' => 'map' ) ) === $html && az_general_shortcode( array( 'field' => 'google_maps_embed' ) ) === $html, 'all map shortcode entry points share the same source' );
$one = az_google_map_shortcode( array( 'index' => '3', 'title' => '' ) );
check( 1 === substr_count( $one, '<iframe ' ) && false !== strpos( $one, 'az-contact-branches__grid--single' ) && false === strpos( $one, '__info' ) && false === strpos( $one, '<h2>' ), 'a selected iframe-only location renders a full-width map without empty text blocks' );
check( '' === az_google_map_shortcode( array( 'index' => 2 ) ) && '' === az_google_map_shortcode( array( 'index' => -1 ) ), 'missing or invalid map selections render no empty section' );
check( 'Địa chỉ & A' === az_get_address() && 'Địa chỉ &amp; A' === az_general_shortcode( array( 'field' => 'address' ) ), 'address helpers and shortcodes use the address list' );
check( 'https://maps.app.goo.gl/head-office' === az_general_shortcode( array( 'field' => 'google_maps_url' ) ), 'directions shortcode reads a current location URL' );
check( 2 === substr_count( render_contact(), '<iframe ' ), 'Contact template renders both valid maps including the iframe-only row' );
az_google_map_assets();
check( '/az-theme/css/contact-branches.css' === $styles[0][1], 'shared Maps CSS is loaded for custom sections as well as Contact' );
$settings['general']['addresses'] = array();
check( '' === az_google_map_shortcode() && false === strpos( render_contact(), '<iframe ' ), 'cleared address list does not resurrect the old map' );
