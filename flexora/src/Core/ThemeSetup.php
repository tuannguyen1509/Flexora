<?php
namespace FLX\Core;

use FLX\Contracts\V1\SpecV1;

/** Minimal WordPress shell wiring, not a header/footer system. */
final class ThemeSetup {
	public static function attach() {
		add_action( 'after_setup_theme', array( self::class, 'setup' ), 10 );
		add_action( 'flx/register', array( self::class, 'register_assets' ), 5 );
		add_action( 'wp_enqueue_scripts', array( self::class, 'use_styles' ), 20 );
		add_action( 'shutdown', array( self::class, 'log_diagnostics' ) );
	}
	public static function setup() {
		load_theme_textdomain( 'flexora', get_template_directory() . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
		register_nav_menus( array( 'flx-primary' => __( 'Primary navigation', 'flexora' ) ) );
	}
	public static function register_assets( $registrar ) {
		$registrar->asset( new SpecV1( array(
			'id' => 'flx.shell.style', 'owner' => 'parent', 'version' => Kernel::VERSION,
			'type' => 'style', 'handle' => 'flx-shell',
			'source' => get_template_directory_uri() . '/assets/css/shell.css',
			'dependencies' => array(), 'context' => array( 'frontend' ),
		) ) );
	}
	public static function use_styles() {
		flx_asset( 'flx.shell.style' );
	}
	public static function log_diagnostics() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			foreach ( flx()->diagnostics() as $entry ) {
				error_log( 'FLX diagnostic ' . wp_json_encode( $entry ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}
	}
}
