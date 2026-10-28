<?php
namespace FLX\Navigation;

final class Primary {
	public static function item_id( $id, $item, $args ) {
		return ! empty( $args->flx_header_navigation ) && isset( $args->menu_id ) ? $args->menu_id . '-item-' . (int) $item->ID : $id;
	}
	public static function render( $props = array() ) {
		if ( ! has_nav_menu( 'primary' ) || ! isset( $props['navigation_id'] ) || ! is_string( $props['navigation_id'] ) ) { return ''; }
		$menu = wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'fallback_cb' => false, 'echo' => false, 'menu_class' => 'flx-nav__list', 'menu_id' => $props['navigation_id'] . '-list', 'flx_header_navigation' => true ) );
		return $menu ? '<nav class="flx-nav" data-flx-navigation data-flx-submenu-open-label="' . esc_attr__( 'Open submenu', 'flexora' ) . '" data-flx-submenu-close-label="' . esc_attr__( 'Close submenu', 'flexora' ) . '" id="' . esc_attr( $props['navigation_id'] ) . '" aria-label="' . esc_attr__( 'Primary navigation', 'flexora' ) . '">' . $menu . '</nav>' : '';
	}
}
