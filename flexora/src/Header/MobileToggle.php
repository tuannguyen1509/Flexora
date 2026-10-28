<?php
namespace FLX\Header;

final class MobileToggle {
	public static function render( $props = array() ) {
		if ( ! isset( $props['navigation_id'] ) || ! is_string( $props['navigation_id'] ) ) { return ''; }
		return '<button type="button" class="flx-header__toggle" data-flx-menu-toggle aria-expanded="false" aria-controls="' . esc_attr( $props['navigation_id'] ) . '" hidden><svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 6h18M3 12h18M3 18h18" fill="none" stroke="currentColor" stroke-width="2"/></svg><span>' . esc_html__( 'Menu', 'flexora' ) . '</span></button>';
	}
}
