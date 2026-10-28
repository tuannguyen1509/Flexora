<?php
/** Isolated parent SEO regressions; optional current-child integration, no database. */
define( 'ABSPATH', __DIR__ );
$mode = $argv[1] ?? 'plain';
$with_child = 'child' === ( $argv[2] ?? '' );
if ( 'rankmath' === $mode ) { define( 'RANK_MATH_VERSION', 'fixture' ); }
if ( 'yoast' === $mode ) { define( 'WPSEO_VERSION', 'fixture' ); }
$options = array(); $hooks = array(); $admin = false; $front = true; $template = false;
$site_name = 'Demo Company'; $tagline = 'Products & services';
function check( $ok, $label ) { if ( ! $ok ) { fwrite( STDERR, "FAIL $label\n" ); exit( 1 ); } }
function add_action( $hook, $callback, ...$args ) { $GLOBALS['hooks'][ $hook ][] = $callback; }
function add_filter( $hook, $callback, ...$args ) { add_action( $hook, $callback ); }
function add_shortcode( ...$args ) {}
function do_action( $hook ) { foreach ( $GLOBALS['hooks'][ $hook ] ?? array() as $callback ) { $callback(); } }
function apply_filters( $hook, $value ) { foreach ( $GLOBALS['hooks'][ $hook ] ?? array() as $callback ) { $value = $callback( $value ); } return $value; }
function is_admin() { return $GLOBALS['admin']; }
function is_front_page() { return $GLOBALS['front']; }
function is_page_template( $name ) { return $GLOBALS['template']; }
function is_page() { return true; }
function is_category() { return false; }
function is_tax( $name ) { return false; }
function get_page_template_slug( $id ) { return $GLOBALS['template'] ? 'front-page.php' : ''; }
function get_option( $key, $fallback = null ) { return 'az_theme_options' === $key ? $GLOBALS['options'] : $fallback; }
function get_bloginfo( $key, ...$args ) { return 'name' === $key ? $GLOBALS['site_name'] : $GLOBALS['tagline']; }
function wp_strip_all_tags( $text ) { return strip_tags( $text ); }
function sanitize_textarea_field( $text ) { return trim( strip_tags( $text ) ); }
function esc_attr( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $text ) { return esc_attr( $text ); }
function esc_url( $url ) { return esc_attr( $url ); }
function absint( $value ) { return abs( (int) $value ); }
function get_queried_object_id() { return 1; }
function get_queried_object() { return (object) array( 'ID' => 1 ); }
function get_the_title( $id ) { return 'Demo page'; }
function get_post_meta( $id, $key, $single ) { return '_az_shared_banner' === $key ? array( 'layout' => 'center', 'text' => 'Banner title' ) : ''; }
function wp_parse_id_list( $value ) { return array(); }
function wp_attachment_is_image( $id ) { return true; }
function locate_template( $name ) { return $GLOBALS['with_child'] ? dirname( __DIR__, 2 ) . '/az-theme-child/' . $name : ''; }
function post_password_required() { return false; }
function az_home_page_get_data() { return array( 'blog' => array( 'enabled' => false ), 'projects' => array( 'enabled' => false ), 'products' => array( 'enabled' => false ) ); }
function az_child_home_articles( ...$args ) { return array(); }
function az_child_steel_icon( $name ) { return '<svg></svg>'; }
function get_stylesheet_directory_uri() { return '/child'; }
function home_url( $path ) { return 'https://example.test' . $path; }
if ( $with_child ) { require dirname( __DIR__, 2 ) . '/az-theme-child/inc/branding-seo.php'; }
require dirname( __DIR__ ) . '/inc/helpers/options.php';
require dirname( __DIR__ ) . '/inc/core/branding-seo.php';
require dirname( __DIR__ ) . '/inc/core/shared-banner.php';
require dirname( __DIR__ ) . '/inc/options/class-sanitizer.php';
$fields = require dirname( __DIR__ ) . '/inc/admin/sections/branding.php';
$ids = array_column( $fields, 'id' );
check( count( $ids ) === count( array_unique( $ids ) ), 'Unique Branding fields' );
$field = $fields[ array_search( 'home_meta_description', $ids, true ) ];
check( 'textarea' === $field['type'], 'Parent owns Meta description control' );
check( 'New description' === AZ_Options_Sanitizer::sanitize( '<b>New description</b>', 'textarea', $field ), 'Parent sanitizer' );
// Emulate plugin ownership of the tag while invoking the real registered filter.
if ( 'plain' !== $mode ) {
    add_action( 'wp_head', static function() {
        $hook = 'rankmath' === $GLOBALS['mode'] ? 'rank_math/frontend/description' : 'wpseo_metadesc';
        $description = apply_filters( $hook, 'Plugin description' );
        if ( '' !== $description ) { printf( '<meta name="description" content="%s">', esc_attr( $description ) ); }
    } );
}
function render_head() { ob_start(); do_action( 'wp_head' ); return ob_get_clean(); }
function render_home() {
    ob_start(); do_action( 'wp_body_open' );
    if ( $GLOBALS['with_child'] ) { require dirname( __DIR__, 2 ) . '/az-theme-child/template-parts/home-storefront.php'; }
    else { do_action( 'az_after_site_header' ); }
    return ob_get_clean();
}
foreach ( array( array( '', '', 'Demo Company', 'Products & services' ), array( 'Custom H1', 'Custom description & services', 'Custom H1', 'Custom description & services' ), array( '  ', " \n ", 'Demo Company', 'Products & services' ) ) as $case ) {
    $options = array( 'branding' => array( 'home_h1' => $case[0], 'home_meta_description' => $case[1] ) );
    $html = render_home();
    check( 1 === preg_match_all( '/<h1\b/i', $html ), 'Exactly one H1 with homepage banner/template' );
    check( false !== strpos( $html, '>' . esc_html( $case[2] ) . '</h1>' ), 'H1 custom/site title fallback' );
    $head = render_head();
    check( 1 === substr_count( $head, 'name="description"' ), 'One description tag from theme or plugin' );
    check( false !== strpos( $head, esc_attr( $case[3] ) ), 'Description custom/tagline fallback and escaping' );
    check( $case[3] === az_filter_home_meta_description( 'Plugin auto description' ), 'SEO plugin filter' );
}
$options['branding']['home_meta_description'] = "Line 1\nLine 2 &amp; more";
check( 'Line 1 Line 2 & more' === az_get_home_meta_description(), 'Normalize whitespace/entities' );
$options['branding']['home_meta_description'] = array( 'invalid' );
check( $tagline === az_get_home_meta_description(), 'Invalid value falls back' );
$options['branding']['home_meta_description'] = '';
$tagline = '';
check( '' === render_head(), 'Empty description and tagline omit empty meta' );
$tagline = 'Products & services';
$front = false;
check( 'Page SEO' === az_filter_home_meta_description( 'Page SEO' ), 'Unrelated page SEO preserved' );
check( 'plain' !== $mode || '' === render_head(), 'Theme does not emit homepage meta elsewhere' );
$template = true;
$options['general']['breadcrumb'] = 0;
check( 1 === preg_match_all( '/<h1\b/i', render_home() ), 'Inner banner/template keeps one H1' );
check( $with_child === az_home_meta_description_context(), 'Child extends context without parent dependency' );
$admin = true;
check( 'Admin SEO' === az_filter_home_meta_description( 'Admin SEO' ), 'Admin context untouched' );
echo 'PASS branding SEO (' . $mode . ', ' . ( $with_child ? 'current child' : 'parent standalone' ) . "): settings, fallbacks, single H1/meta, plugin ownership, inner pages and child extension.\n";
