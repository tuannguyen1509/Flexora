<?php
/** Parent-owned Custom Code storage, save and output; no database or requests. */
define( 'ABSPATH', __DIR__ );
$options = array(); $legacy = array(); $hooks = array(); $secret = 'site-a'; $raw_allowed = true;
$registered_pages = array(); $registered_settings = array(); $redirect_url = '';
function az_get_options() { return $GLOBALS['options']; }
function get_option( $key, $default = '' ) { return $GLOBALS['legacy'][$key] ?? $default; }
function update_option( $key, $value, $autoload = null ) { if ( 'az_theme_options' === $key ) { $GLOBALS['options'] = $value; } }
function wp_salt( $scheme ) { return $GLOBALS['secret']; }
function current_user_can( $cap ) { return 'unfiltered_html' !== $cap || $GLOBALS['raw_allowed']; }
function wp_strip_all_tags( $value ) { return strip_tags( preg_replace( '#<(script|style)\b[^>]*>.*?</\1>#is', '', $value ) ); }
function wp_kses_post( $value ) { return strip_tags( $value, '<p><strong>' ); }
function add_action( $name, $callback, $priority = 10, ...$args ) { $GLOBALS['hooks'][$name][$priority][] = $callback; }
function add_filter( ...$args ) {}
function sanitize_key( $value ) { return $value; }
function sanitize_text_field( $value ) { return strip_tags( $value ); }
function sanitize_html_class( $value ) { return $value; }
function esc_html( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $value ) { return esc_html( $value ); }
function esc_textarea( $value ) { return esc_html( $value ); }
function esc_html__( $value, $domain ) { return $value; }
function wp_unslash( $value ) { return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( $value ); }
function check_admin_referer( ...$args ) {}
function admin_url( $path ) { return '/wp-admin/' . $path; }
function wp_safe_redirect( $url ) { $GLOBALS['redirect_url'] = $url; throw new RuntimeException( 'redirect' ); }
function add_options_page( $title, $label, $capability, $slug, $callback ) { $GLOBALS['registered_pages'][$slug] = $callback; }
function remove_submenu_page( ...$args ) {}
function register_setting( $group, $name, $args = array() ) { $GLOBALS['registered_settings'][$group][] = $name; }
function wp_die( $message ) { throw new RuntimeException( $message ); }
function output( $callback ) { ob_start(); $callback(); return ob_get_clean(); }
function fire( $hook ) { $priorities = $GLOBALS['hooks'][$hook] ?? array(); ksort( $priorities ); foreach ( $priorities as $callbacks ) { foreach ( $callbacks as $callback ) { $callback(); } } }
function check( $ok, $label ) { if ( ! $ok ) { throw new RuntimeException( $label ); } echo "PASS $label\n"; }
function signed_code( $code, $key ) { return AZ_Options_Sanitizer::sanitize( $code, 'code', array( 'id' => $key ) ); }
require dirname( __DIR__ ) . '/inc/options/class-sanitizer.php';
require dirname( __DIR__ ) . '/inc/options/class-registry.php';
require dirname( __DIR__ ) . '/inc/options/class-renderer.php';
require dirname( __DIR__ ) . '/inc/core/class-custom-code.php';
require dirname( __DIR__ ) . '/inc/functions/admin-settings.php';
require dirname( __DIR__ ) . '/inc/admin/class-advanced.php';
$fields = require dirname( __DIR__ ) . '/inc/admin/sections/custom-code.php';
fire( 'admin_menu' );
// Exercise settings registration without running unrelated database seed callbacks.
$init_settings = array_values( array_filter( $hooks['admin_init'][10], static function( $callback ) { return $callback instanceof Closure; } ) )[0];
$init_settings();
check( ! isset( $registered_pages['az-hfc-settings'] ) && ! isset( $registered_settings['az_hfc_group'] ), 'retired Header & Footer screen and save group are no longer registered' );
check( ! function_exists( 'az_hfc_settings_page' ) && ! function_exists( 'az_hfc_sanitize_code' ) && ! function_exists( 'az_hfc_print_code' ), 'retired screen, sanitizer and output helper are removed' );
check( isset( $registered_pages['az-contact-leads'] ) && isset( $registered_settings['az_lead_smtp_group'] ), 'unrelated Leads and SMTP configuration stay available' );
$_GET['page'] = 'az-hfc-settings';
try { $init_settings(); } catch ( RuntimeException $error ) { if ( 'redirect' !== $error->getMessage() ) { throw $error; } }
check( '/wp-admin/admin.php?page=az-theme-advanced&section=custom_code' === $redirect_url, 'old Header & Footer URL redirects to the parent Custom Code editor' );
unset( $_GET['page'] );
check( 1 === count( $hooks['wp_head'][30] ) && ! isset( $hooks['wp_head'][99] ) && ! isset( $hooks['wp_footer'][99] ), 'parent is the sole output owner; legacy callbacks cannot duplicate snippets' );
AZ_Options_Registry::add_section( 'custom_code', 'Custom Code', $fields );
$header = '<script async src="https://example.test/tag.js?id=test"></script><script>window.header=1;</script>';
$body = '<noscript><iframe src="https://example.test/ns.html" height="0" width="0" style="display:none"></iframe></noscript>';
$footer = '<script>window.footer="\\d+";</script>';
$css = 'body { color: #123456; }';
$options = array( 'custom_code' => array( 'custom_css' => $css, 'header_code' => signed_code( $header, 'header_code' ), 'body_open_code' => signed_code( $body, 'body_open_code' ), 'footer_code' => signed_code( $footer, 'footer_code' ) ) );
$renderer = $hooks['wp_head'][30][0][0];
$head = output( function() { fire( 'wp_head' ); fire( 'wp_head' ); } );
$opening = output( function() { fire( 'wp_body_open' ); fire( 'wp_body_open' ); } );
$ending = output( function() { fire( 'wp_footer' ); fire( 'wp_footer' ); } );
check( 1 === substr_count( $head, $header ) && 1 === substr_count( $head, 'id="az-custom-css"' ), 'authorized Header Code and CSS render once through parent hooks' );
check( trim( $opening ) === $body && isset( $hooks['wp_body_open'][0] ), 'Body Open preserves the complete noscript iframe and runs first' );
check( trim( $ending ) === $footer, 'Footer Code retains JavaScript backslashes and renders once' );
check( ! AZ_Custom_Code::needs_review( $options ), 'authorized code does not require another review' );
check( AZ_Custom_Code::editor_values( $options )['header_code'] === $header, 'admin editor resolves signed source without metadata' );
$legacy = array( 'az_hfc_header_scripts' => '<script>window.legacy=1;</script>', 'az_hfc_footer_scripts' => '<script>window.oldFooter=1;</script>' );
check( trim( output( array( new AZ_Custom_Code(), 'head' ) ) ) === trim( $head ), 'explicit Advanced values override legacy values' );
$options['custom_code'] = array( 'header_code' => '', 'body_open_code' => '', 'footer_code' => '', 'custom_css' => '' );
$empty = new AZ_Custom_Code();
check( '' === output( function() use ( $empty ) { $empty->head(); $empty->body(); $empty->footer(); } ), 'explicit clears never restore legacy tracking' );
$options = array();
$legacy['az_hfc_body_top_scripts'] = signed_code( $body, 'body_open_code' );
$legacy['az_hfc_body_bottom_scripts'] = signed_code( '<script>window.bottom=1;</script>', 'footer_code' );
$legacy['az_hfc_footer_scripts'] = signed_code( $footer, 'footer_code' );
$editor = AZ_Custom_Code::editor_values( $options );
check( $editor['header_code'] === $legacy['az_hfc_header_scripts'] && $editor['body_open_code'] === $body, 'Advanced recognizes legacy header and body values' );
check( $editor['footer_code'] === '<script>window.bottom=1;</script>' . "\n" . $footer, 'Advanced includes both legacy Body bottom and Footer code' );
check( AZ_Custom_Code::needs_review( $options ) && '' === output( array( new AZ_Custom_Code(), 'head' ) ), 'unsigned imported scripts require a save and do not leak visible JavaScript' );
$legacy['az_hfc_header_scripts'] = '<!--az-trusted:' . hash_hmac( 'sha256', $header, wp_salt( 'auth' ) ) . '-->' . $header;
check( trim( output( array( new AZ_Custom_Code(), 'head' ) ) ) === $header, 'legacy signed comments are recognized without duplicate output' );
$old_footer = output( array( new AZ_Custom_Code(), 'footer' ) );
check( 1 === substr_count( $old_footer, 'window.bottom' ) && 1 === substr_count( $old_footer, 'window.footer' ), 'both signed legacy footer positions render exactly once' );
$legacy['az_hfc_body_bottom_scripts'] = $legacy['az_hfc_footer_scripts'];
check( 1 === substr_count( output( array( new AZ_Custom_Code(), 'footer' ) ), 'window.footer' ), 'identical legacy footer snippets are deduplicated' );
$advanced = ( new ReflectionClass( 'AZ_Advanced' ) )->newInstanceWithoutConstructor();
function save_codes( $advanced, $codes ) {
    $_POST = array( 'az_section' => 'custom_code', 'az_complete' => '1', 'az_theme_options' => array( 'custom_code' => array_map( 'addslashes', $codes ) ) );
    try { $advanced->save(); } catch ( RuntimeException $error ) { if ( 'redirect' !== $error->getMessage() ) { throw $error; } }
}
save_codes( $advanced, AZ_Custom_Code::editor_values( $options ) );
check( ! AZ_Custom_Code::needs_review( $options ) && $header === AZ_Custom_Code::trusted_markup( $options['custom_code']['header_code'] ), 'actual Advanced save authorizes resolved legacy code' );
$secret = 'site-b';
check( AZ_Custom_Code::needs_review( $options ) && '' === output( array( new AZ_Custom_Code(), 'head' ) ), 'a new installation flags imported tracking instead of running stale signatures' );
save_codes( $advanced, AZ_Custom_Code::editor_values( $options ) );
check( ! AZ_Custom_Code::needs_review( $options ) && trim( output( array( new AZ_Custom_Code(), 'head' ) ) ) === $header, 'same parent module authorizes and renders code on another website after save' );
$original_header = $options['custom_code']['header_code'];
$raw_allowed = false;
save_codes( $advanced, array( 'header_code' => '<script>changed()</script>', 'custom_css' => $css ) );
check( $original_header === $options['custom_code']['header_code'] && $css === $options['custom_code']['custom_css'], 'restricted save preserves scripts while allowing CSS edits' );
check( false !== strpos( output( array( new AZ_Custom_Code(), 'head' ) ), $header ), 'saved trusted scripts render independently of visitor permissions' );
$options['custom_code']['custom_css'] = array( 'code' => $css );
check( false !== strpos( output( array( new AZ_Custom_Code(), 'head' ) ), $css ), 'CSS stored in a code array also renders without a type error' );
check( '' === AZ_Options_Sanitizer::code_source( array( 'code' => array() ) ) && '' === AZ_Custom_Code::trusted_markup( null ), 'malformed and null values are safely ignored' );
$field = array_values( array_filter( $fields, static function( $field ) { return 'header_code' === $field['id']; } ) )[0];
$admin = output( static function() use ( $field, $options ) { AZ_Options_Renderer::field( 'custom_code', $field, array( 'custom_code' => AZ_Custom_Code::editor_values( $options ) ) ); } );
check( false !== strpos( $admin, '&lt;script' ) && false === strpos( $admin, '<script' ), 'admin textarea displays source without executing it' );
