<?php
namespace FLX\Data;

/** Page-scoped, multi-map envelope; never seeds defaults during reads. */
final class PostMetaStorage {
	const KEY = '_flx_template_data';
	public function read( $post_id ) {
		$data = get_post_meta( $post_id, self::KEY, true );
		return is_array( $data ) ? $data : array();
	}
	public function save( $post_id, $map, $values, $revision ) {
		$raw = get_post_meta( $post_id, self::KEY, true );
		if ( metadata_exists( 'post', $post_id, self::KEY ) && ( ! is_array( $raw ) || isset( $raw['format_version'] ) && 1 !== $raw['format_version'] || isset( $raw['maps'] ) && ! is_array( $raw['maps'] ) ) ) { return false; }
		$old = $this->read( $post_id );
		if ( ( isset( $old['revision'] ) ? $old['revision'] : 0 ) !== $revision ) { return false; }
		$new = $old; $new['format_version'] = 1; $new['revision'] = $revision + 1;
		if ( ! isset( $new['maps'] ) ) { $new['maps'] = array(); }
		$entry = isset( $new['maps'][ $map['id'] ] ) ? $new['maps'][ $map['id'] ] : array();
		if ( ! is_array( $entry ) ) { return false; }
		$entry['map'] = $map['id']; $entry['version'] = $map['version']; $entry['data'] = $values;
		$new['maps'][ $map['id'] ] = $entry;
		if ( ! metadata_exists( 'post', $post_id, self::KEY ) ) { return (bool) add_post_meta( $post_id, self::KEY, wp_slash( $new ), true ); }
		return (bool) update_post_meta( $post_id, self::KEY, wp_slash( $new ), $old );
	}
}
