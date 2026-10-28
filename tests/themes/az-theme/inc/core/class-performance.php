<?php
defined( 'ABSPATH' ) || exit;
class AZ_Performance {
    public function __construct() { add_action( 'init', array( $this, 'configure' ), 20 ); add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 100 ); add_filter( 'wp_lazy_loading_enabled', array( $this, 'lazy_loading' ), 10, 3 ); add_action( 'wp_enqueue_scripts', array( $this, 'preload_script' ), 20 ); }
    public function configure() {
        if ( az_get_option( 'performance.disable_emoji', 0 ) ) { remove_action( 'wp_head', 'print_emoji_detection_script', 7 ); remove_action( 'wp_print_styles', 'print_emoji_styles' ); remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' ); remove_filter( 'the_content_feed', 'wp_staticize_emoji' ); remove_filter( 'comment_text_rss', 'wp_staticize_emoji' ); remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' ); }
        if ( az_get_option( 'performance.disable_embeds', 0 ) ) { remove_action( 'wp_head', 'wp_oembed_add_discovery_links' ); remove_action( 'wp_head', 'wp_oembed_add_host_js' ); add_filter( 'embed_oembed_discover', '__return_false' ); }
        if ( az_get_option( 'performance.disable_generator', 0 ) ) { remove_action( 'wp_head', 'wp_generator' ); add_filter( 'the_generator', '__return_empty_string' ); }
    }
    private function disable_asset( $registry, $handle ) {
        if ( isset( $registry->registered[$handle] ) ) { $registry->registered[$handle]->src = false; }
        $registry->dequeue( $handle );
    }
    public function assets() { if ( az_get_option( 'performance.disable_theme_style', 0 ) ) { $this->disable_asset( wp_styles(), 'az-ai-theme-style' ); } if ( az_get_option( 'performance.disable_block_css', 0 ) ) { $this->disable_asset( wp_styles(), 'wp-block-library' ); $this->disable_asset( wp_styles(), 'wp-block-library-theme' ); $this->disable_asset( wp_styles(), 'global-styles' ); } if ( az_get_option( 'performance.disable_mediaelement', 0 ) ) { $this->disable_asset( wp_scripts(), 'mediaelement' ); $this->disable_asset( wp_scripts(), 'wp-mediaelement' ); $this->disable_asset( wp_styles(), 'mediaelement' ); $this->disable_asset( wp_styles(), 'wp-mediaelement' ); } if ( az_get_option( 'performance.disable_jquery_migrate', 0 ) ) { foreach ( wp_scripts()->registered as $script ) { $script->deps = array_values( array_diff( $script->deps, array( 'jquery-migrate' ) ) ); } $this->disable_asset( wp_scripts(), 'jquery-migrate' ); } if ( ! is_user_logged_in() && ! ( is_singular( 'post' ) || is_home() || is_category() || is_tag() || is_author() || is_date() ) && az_get_option( 'performance.disable_dashicons_guests', 0 ) ) { $this->disable_asset( wp_styles(), 'dashicons' );  } }
    public function lazy_loading( $default, $tag_name, $context ) { return 'img' === $tag_name && ! az_get_option( 'performance.lazy_load_images', 1 ) ? false : $default; }
    public function preload_script() {
        if ( az_get_option( 'performance.preload_pages', 0 ) ) {
            wp_enqueue_script( 'az-prefetch', get_template_directory_uri() . '/js/prefetch.js', array(), filemtime( get_template_directory() . '/js/prefetch.js' ), true );
        }
    }
}
new AZ_Performance();
