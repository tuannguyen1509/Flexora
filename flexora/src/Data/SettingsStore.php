<?php
namespace FLX\Data;

/** Only this adapter knows the option name; the field engine does not. */
final class SettingsStore {
	public function read() {
		$data = get_option( 'flx_settings', array() );
		return is_array( $data ) ? $data : array();
	}
	public function save( $section, array $values, $revision ) {
		$old = $this->read();
		$actual = isset( $old['_revision'] ) ? (int) $old['_revision'] : 0;
		if ( $actual !== $revision ) { return false; }
		$new = $old; $new[ $section ] = $values; $new['_revision'] = $actual + 1;
		// Compare-and-swap against the exact original serialized value prevents lost updates.
		global $wpdb;
		if ( array() === $old && false === get_option( 'flx_settings', false ) ) { return add_option( 'flx_settings', $new, '', false ); }
		$updated = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND BINARY option_value = BINARY %s", maybe_serialize( $new ), 'flx_settings', maybe_serialize( $old ) ) );
		if ( 1 === $updated ) { wp_cache_delete( 'flx_settings', 'options' ); wp_cache_delete( 'alloptions', 'options' ); return true; }
		return false;
	}
}
