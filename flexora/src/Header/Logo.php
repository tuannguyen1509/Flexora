<?php
namespace FLX\Header;

final class Logo {
	public static function render( $props = array() ) {
		$name = get_bloginfo( 'name' );
		if ( '' === trim( $name ) ) { $name = __( 'Home', 'flexora' ); }
		$id = (int) get_theme_mod( 'custom_logo' );
		$image = $id && wp_attachment_is_image( $id ) ? wp_get_attachment_image( $id, 'full', false, array( 'class' => 'flx-header__logo', 'alt' => $name, 'loading' => 'eager' ) ) : '';
		$description = get_bloginfo( 'description' );
		return '<a class="flx-header__home" href="' . esc_url( home_url( '/' ) ) . '" aria-label="' . esc_attr( $name ) . '">' . ( $image ? $image : '<span class="flx-header__title">' . esc_html( $name ) . '</span>' ) . '</a>' . ( '' !== $description ? '<p class="flx-header__description">' . esc_html( $description ) . '</p>' : '' );
	}
}
