<?php
namespace FLX\Header;

use FLX\Contracts\V1\SpecV1;

/** Mandatory native theme shell, not an optional settings module. */
final class Integration {
	public static function attach() {
		add_action( 'flx/register', array( self::class, 'register' ), 12 );
		add_action( 'wp_enqueue_scripts', array( self::class, 'preflight' ), 10 );
		add_filter( 'nav_menu_item_id', array( \FLX\Navigation\Primary::class, 'item_id' ), 10, 3 );
	}
	public static function register( $register ) {
		foreach ( array( 'style' => 'css', 'script' => 'js' ) as $type => $extension ) {
			$register->asset( new SpecV1( array( 'id' => 'flx.header.' . $type, 'owner' => 'parent', 'version' => '0.4.0', 'type' => $type, 'handle' => 'flx-header', 'source' => get_template_directory_uri() . '/assets/header/header.' . $extension, 'dependencies' => array(), 'context' => array( 'frontend' ), 'strategy' => 'footer' ) ) );
		}
		$register->provider( new SpecV1( array( 'id' => 'flx.header.native', 'owner' => 'parent', 'version' => '0.4.0', 'dependencies' => array(), 'assets' => array() ) ) );
		$register->behavior( new SpecV1( array( 'id' => 'flx.header.mobile-navigation', 'owner' => 'parent', 'version' => '0.4.0', 'dependencies' => array(), 'provider' => 'flx.header.native', 'assets' => array( 'flx.header.script' ) ) ) );
		foreach ( array( 'logo' => array( Logo::class, 'render' ), 'navigation' => array( \FLX\Navigation\Primary::class, 'render' ), 'mobile-toggle' => array( MobileToggle::class, 'render' ), '' => array( Layout::class, 'render' ) ) as $name => $renderer ) {
			$register->component( new SpecV1( array( 'id' => 'flx.header' . ( $name ? '.' . $name : '' ), 'owner' => 'parent', 'version' => '0.4.0', 'dependencies' => $name ? array() : array( 'flx.header.logo', 'flx.header.navigation', 'flx.header.mobile-toggle' ), 'assets' => array( 'flx.header.style' ), 'behaviors' => array( 'flx.header.mobile-navigation' ), 'renderer' => $renderer, 'view_contract_version' => '1.0.0' ) ) );
		}
	}
	public static function preflight() { flx_use_component( 'flx.header' ); }
}
