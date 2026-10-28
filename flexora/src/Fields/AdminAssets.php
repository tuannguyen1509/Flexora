<?php
namespace FLX\Fields;

final class AdminAssets {
	public static function register( $registrar ) {
		if ( ! is_admin() ) { return; }
		$base = get_template_directory_uri() . '/assets/admin/';
		$own = array( 'advanced' => array( 'style', array( 'flx.admin.controls.style' ) ), 'controls.style' => array( 'style', array() ), 'fields' => array( 'script', array() ), 'editor' => array( 'script', array( 'flx.admin.fields', 'flx.wp.editor' ) ), 'media' => array( 'script', array( 'flx.admin.fields', 'flx.wp.media' ) ), 'color' => array( 'script', array( 'flx.admin.fields', 'flx.wp.color.script' ) ), 'repeater' => array( 'script', array( 'flx.admin.fields' ) ) );
		foreach ( $own as $name => $definition ) {
			$registrar->asset( new \FLX\Contracts\V1\SpecV1( array( 'id' => 'flx.admin.' . $name, 'owner' => 'parent', 'version' => \FLX\Core\Kernel::VERSION, 'type' => $definition[0], 'handle' => 'flx-' . $name, 'source' => $base . ( 'controls.style' === $name ? 'controls' : $name ) . ( 'style' === $definition[0] ? '.css' : '.js' ), 'dependencies' => $definition[1], 'context' => array( 'admin' ) ) ) );
		}
		foreach ( array( 'editor' => array( 'script', 'editor' ), 'media' => array( 'script', 'media-views' ), 'color.script' => array( 'script', 'wp-color-picker' ), 'color.style' => array( 'style', 'wp-color-picker' ) ) as $id => $definition ) {
			$registrar->asset( new \FLX\Contracts\V1\SpecV1( array( 'id' => 'flx.wp.' . $id, 'owner' => 'wordpress', 'version' => \FLX\Core\Kernel::VERSION, 'type' => $definition[0], 'handle' => $definition[1], 'source' => 'wordpress:' . $definition[1], 'override_policy' => 'reference', 'dependencies' => array(), 'context' => array( 'admin' ) ) ) );
		}
	}
	public static function enqueue( array $fields ) {
		$types = array(); $collect = function ( $fields ) use ( &$collect, &$types ) {
			foreach ( $fields as $field ) { $types[] = $field['type']; if ( 'repeater' === $field['type'] ) { $collect( $field['fields'] ); } }
		}; $collect( $fields );
		flx_asset( 'flx.admin.controls.style' ); flx_asset( 'flx.admin.fields' );
		if ( in_array( 'editor', $types, true ) ) { wp_enqueue_editor(); flx_asset( 'flx.admin.editor' ); }
		if ( array_intersect( array( 'media', 'gallery' ), $types ) && current_user_can( 'upload_files' ) ) { wp_enqueue_media(); flx_asset( 'flx.admin.media' ); }
		if ( in_array( 'color', $types, true ) ) { flx_asset( 'flx.wp.color.style' ); flx_asset( 'flx.admin.color' ); }
		if ( in_array( 'repeater', $types, true ) ) { flx_asset( 'flx.admin.repeater' ); }
	}
}
