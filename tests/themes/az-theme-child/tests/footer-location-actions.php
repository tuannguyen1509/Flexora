<?php
/** Footer location actions and child-owned General fields, without a database. */
define( 'ABSPATH', __DIR__ );
$theme = dirname( __DIR__ ); $options = array();
function add_action( ...$args ) {}
function is_admin() { return true; }
function absint( $value ) { return abs( (int) $value ); }
function sanitize_key( $value ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', $value ) ); }
function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function sanitize_textarea_field( $value ) { return sanitize_text_field( $value ); }
function get_option( $key, $fallback = '' ) { return $GLOBALS['options'][$key] ?? $fallback; }
function esc_attr( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $value ) { return esc_attr( $value ); }
function esc_url_raw( $value, $protocols = array() ) { return preg_match( '#^https?://#i', (string) $value ) ? $value : ''; }
function esc_url( $value ) { return esc_attr( esc_url_raw( $value ) ); }
function wp_parse_url( $value, $component ) { return parse_url( $value, $component ); }
function get_stylesheet_directory() { return $GLOBALS['theme']; }
function get_stylesheet_directory_uri() { return 'https://example.test/wp-content/themes/az-theme-child'; }
require dirname($theme) . '/az-theme/inc/options/class-registry.php';
require dirname($theme) . '/az-theme/inc/options/class-sanitizer.php';
require $theme . '/inc/footer-storefront.php';
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } echo "PASS $message\n"; }
function capture( $callback ) { ob_start(); $callback(); return ob_get_clean(); }
AZ_Options_Registry::add_section( 'general', 'General', require dirname($theme) . '/az-theme/inc/admin/sections/general.php' );
az_child_footer_register_general_fields(); az_child_footer_register_general_fields();
$fields = AZ_Options_Registry::get('general')['fields'];
check( !in_array('fallback', array_column($fields,'group'),true), 'parent and child no longer register the fallback address group' );
$addresses = array_values(array_filter($fields, function($f){return 'addresses' === $f['id'];}))[0];
check( !in_array('parking_url', array_column($fields,'id'),true) && !in_array('parking_url',array_column($addresses['fields'],'id'),true), 'parking link fields are removed from both General and the address repeater' );
check( in_array('maps_embed',array_column($addresses['fields'],'id'),true) && in_array('maps_url',array_column($addresses['fields'],'id'),true), 'existing Google Maps fields remain available' );
$row = array('label'=>'Showroom','value'=>'660 Cách Mạng Tháng 8, Tây Ninh','maps_url'=>'https://maps.app.goo.gl/directions','parking_url'=>'https://example.test/old-parking','maps_embed'=>'');
$clean = AZ_Options_Sanitizer::sanitize(array($row),'repeater',$addresses);
check( $clean[0]['maps_url'] === $row['maps_url'] && !isset($clean[0]['parking_url']), 'address save keeps Maps URL and drops the obsolete parking field' );
$locations = az_child_footer_locations(array('addresses'=>array(array('value'=>'Address without link'),$row,array('label'=>'Chi nhánh','maps_url'=>'https://maps.app.goo.gl/branch'))));
check( count($locations) === 2 && $locations[0]['value'] === $row['value'] && $locations[1]['label'] === 'Chi nhánh', 'only locations with configured Maps links are retained in order' );
check( az_child_footer_location_url($locations[0]) === $row['maps_url'], 'configured Google Maps destination takes priority' );
$html = capture(function() use($locations){az_child_footer_location_actions($locations[0]);});
check( 2 === substr_count($html,'class="az-footer-location-action"') && 2 === substr_count($html,'<img '), 'each location shows the two supplied icon images' );
check( strpos($html,'/icon/location-button-1.png') !== false && strpos($html,'/icon/location-button-2.png') !== false, 'icon loader uses the requested child-theme PNGs' );
check( 1 === substr_count($html,'<a ') && strpos($html,'href="https://maps.app.goo.gl/directions"') !== false && strpos($html,'rel="noopener noreferrer"') !== false, 'only directions is a labelled link opening Maps in a new tab' );
check( preg_match('#<span class="az-footer-location-action"><img[^>]+location-button-2\.png[^>]*></span>#',$html) && strpos($html,'old-parking') === false && strpos($html,'role="button"') === false && strpos($html,'tabindex') === false, 'parking is a static image without a link, button role or keyboard focus' );
check( az_child_footer_location_url(array('value'=>'A & B, Hà Nội','maps_url'=>'javascript:alert(1)')) === '', 'invalid Maps links cannot generate a link from the address' );
check( '' === capture(function(){az_child_footer_location_actions(array('value'=>'660 Cách Mạng Tháng 8, Tây Ninh','maps_url'=>''));}), 'address without a Maps link hides both icons' );
check( '' === capture(function(){az_child_footer_location_actions(array('value'=>'Showroom','maps_url'=>'   ','maps_embed'=>'<iframe src="https://www.google.com/maps/embed"></iframe>'));}), 'whitespace link and iframe do not create a footer group' );
$html = capture(function() use($locations){foreach($locations as $location){az_child_footer_location_actions($location);}});
check( 2 === substr_count($html,'class="az-footer-location-actions"') && 4 === substr_count($html,'<img ') && strpos($html,'directions') < strpos($html,'https://maps.app.goo.gl/branch'), 'adding a Maps location appends a second two-icon group beneath the first' );
check( '' === capture(function(){az_child_footer_location_actions(array('value'=>''));}), 'empty data produces no empty icon group' );
check( az_child_footer_locations(array('addresses'=>array(array('label'=>'Only label'),array('maps_url'=>'javascript:alert(1)'),array('parking_url'=>'https://example.test/parking')),'address'=>'','google_maps_url'=>'')) === array(), 'labels, invalid URLs and obsolete parking-only rows cannot create footer groups' );
$options = array('az_contact_address'=>'Legacy address','az_contact_maps_url'=>'https://maps.app.goo.gl/legacy');
$legacy = az_child_footer_locations(array());
check( $legacy === array(), 'removed legacy address and Maps URL cannot create footer groups' );
check( az_child_footer_locations(array('address'=>'','google_maps_url'=>'')) === array(), 'explicitly cleared fallback values stay empty' );
check( az_child_footer_locations(array('addresses'=>array(array('value'=>'Main branch','maps_url'=>'')),'google_maps_url'=>'https://maps.app.goo.gl/old')) === array(), 'cleared branch links cannot resurrect a fallback or legacy Maps group' );
check( az_child_footer_locations(array('address'=>'Legacy address','google_maps_url'=>'')) === array(), 'fallback address alone cannot create icons' );
$link_only = az_child_footer_locations(array('address'=>'','google_maps_url'=>'https://maps.app.goo.gl/fallback'));
check( $link_only === array(), 'retired fallback Maps URL cannot create a group' );
$embed = '<iframe src="https://www.google.com/maps/embed?pb=test"></iframe>';
$row['maps_embed'] = $embed;
$clean = AZ_Options_Sanitizer::sanitize(array($row),'repeater',$addresses);
$html = capture(function() use($clean){az_child_footer_location_actions(az_child_footer_locations(array('addresses'=>$clean))[0]);});
check( strpos($html,'<iframe') === false && strpos($clean[0]['maps_embed'],'google.com/maps/embed') !== false, 'iframe configuration remains available but never renders in the footer' );
check( az_child_footer_locations(array('addresses'=>array(array('maps_embed'=>$embed)),'address'=>'','google_maps_url'=>'')) === array(), 'iframe-only rows do not create empty icon groups' );
$home = file_get_contents($theme.'/template-parts/footer-reference.php'); $inner=file_get_contents($theme.'/footer.php');
check( strpos($home, "foreach ( $" . "az_footer_data['locations']") !== false && strpos($inner,"'/template-parts/footer-reference.php'") !== false, 'all footer entry points render the complete location list' );
