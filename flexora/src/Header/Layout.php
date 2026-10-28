<?php
namespace FLX\Header;

/** Returns markup once; subcomponent graph has already been preflighted. */
final class Layout {
	private static function slot( $hook ) { ob_start(); do_action( $hook ); return ob_get_clean(); }
	public static function render( $props = array() ) {
		$id = wp_unique_id( 'flx-primary-navigation-' );
		$navigation = flx_component( 'flx.header.navigation', array( 'navigation_id' => $id ) )->html;
		$toggle = $navigation ? flx_component( 'flx.header.mobile-toggle', array( 'navigation_id' => $id ) )->html : '';
		return self::slot( 'flx/header/before' ) . '<header class="flx-header" data-flx-header><div class="flx-container flx-header__inner"><div class="flx-header__brand">' . flx_component( 'flx.header.logo' )->html . self::slot( 'flx/header/brand/after' ) . '</div>' . $toggle . $navigation . self::slot( 'flx/header/navigation/after' ) . '</div></header>' . self::slot( 'flx/header/after' );
	}
}
