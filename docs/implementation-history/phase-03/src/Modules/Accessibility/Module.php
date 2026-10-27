<?php
namespace FLX\Modules\Accessibility;

use FLX\Contracts\V1\RegistrarV1;
use FLX\Contracts\V1\SpecV1;
use FLX\Components\Accessibility\SkipLink;

/** Native reusable frontend module. Declaration has no feature side effects. */
final class Module {
	private static $rendered = false;
	public static function register( RegistrarV1 $registrar, $base_uri ) {
		$id = 'flx.accessibility';
		$enabled = (bool) apply_filters( 'flx/modules/enabled', true, $id );
		foreach ( array( 'style' => 'css', 'script' => 'js' ) as $type => $extension ) {
			$registrar->asset( new SpecV1( array(
				'id' => $id . '.skip-link.' . $type,
				'owner' => 'parent', 'module' => $id, 'version' => '0.1.0',
				'type' => $type,
				'handle' => 'flx-accessibility-skip-link' . ( 'script' === $type ? '-js' : '' ),
				'source' => rtrim( $base_uri, '/' ) . '/assets/accessibility/skip-link.' . $extension,
				'dependencies' => array(), 'context' => array( 'frontend' ), 'strategy' => 'footer',
			) ) );
		}
		$registrar->provider( new SpecV1( array(
			'id' => $id . '.native', 'owner' => 'parent', 'version' => '0.1.0',
			'dependencies' => array(), 'assets' => array(), 'module' => $id,
		) ) );
		$registrar->behavior( new SpecV1( array(
			'id' => $id . '.skip-link', 'owner' => 'parent', 'version' => '0.1.0',
			'dependencies' => array(), 'provider' => $id . '.native',
			'assets' => array( $id . '.skip-link.script' ), 'module' => $id,
		) ) );
		$registrar->component( new SpecV1( array(
			'id' => $id . '.skip-link', 'owner' => 'parent', 'version' => '0.1.0',
			'dependencies' => array(), 'assets' => array( $id . '.skip-link.style' ),
			'behaviors' => array( $id . '.skip-link' ), 'module' => $id,
			'renderer' => array( SkipLink::class, 'render' ),
			'validate_props' => array( SkipLink::class, 'validate' ),
		) ) );
		$registrar->module( new SpecV1( array(
			'id' => $id, 'owner' => 'parent', 'version' => '0.1.0',
			'enabled' => $enabled, 'available' => true, 'dependencies' => array(),
			'scope' => array( 'frontend' ), 'capabilities' => array( $id . '.skip-link' ),
			'boot' => array( self::class, 'boot' ),
		) ) );
	}
	public static function boot( $hooks, $context ) {
		$usage = flx_use_component( 'flx.accessibility.skip-link' );
		if ( $usage->accepted ) {
			$hooks->action( 'wp_body_open', array( self::class, 'render' ), 1, 0 );
		}
	}
	public static function render() {
		if ( ! self::$rendered ) {
			self::$rendered = true;
			flx_render( 'flx.accessibility.skip-link' );
		}
	}
}
