<?php
namespace FLX\Components\Accessibility;

/** Markup only; no hooks, assets, storage or content queries. */
final class SkipLink {
	public static function validate( $props ) {
		return ! array_key_exists( 'label', $props ) || ( is_string( $props['label'] ) && '' !== trim( $props['label'] ) );
	}
	public static function render( $props ) {
		$label = array_key_exists( 'label', $props ) ? $props['label'] : __( 'Bỏ qua tới nội dung', 'flexora' );
		return '<a class="flx-skip-link" href="#flx-main-content">' . esc_html( $label ) . '</a>';
	}
}
