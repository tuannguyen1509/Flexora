<?php
namespace FLX\Templates;

use FLX\Data\PostMetaStorage;

final class Resolver {
	private $registry;
	private $engine;
	private $storage;
	private $cache = array();
	private $diagnostics = array();
	public function __construct( MapRegistry $registry, $engine, PostMetaStorage $storage ) { $this->registry = $registry; $this->engine = $engine; $this->storage = $storage; }
	public function post_id( array $context = array() ) {
		$id = isset( $context['post_id'] ) ? absint( $context['post_id'] ) : ( is_singular( 'page' ) ? get_queried_object_id() : get_the_ID() );
		return 'page' === get_post_type( $id ) ? (int) $id : 0;
	}
	public function map( $post_id ) { return $post_id ? $this->registry->by_file( (string) get_page_template_slug( $post_id ) ) : null; }
	public function diagnostic( $code, $post_id, $map = '' ) { $this->diagnostics[ $code . ':' . $post_id . ':' . $map ] = array( 'code' => $code, 'post_id' => $post_id, 'map' => $map ); }
	public function diagnostics() { return array_values( $this->diagnostics ); }
	public function payload( $post_id ) {
		if ( ! array_key_exists( $post_id, $this->cache ) ) { $this->cache[ $post_id ] = $this->storage->read( $post_id ); }
		$root = $this->cache[ $post_id ];
		foreach ( isset( $root['maps'] ) && is_array( $root['maps'] ) ? $root['maps'] : array() as $id => $entry ) {
			$map = $this->registry->get( $id );
			if ( ! $map ) { $this->diagnostic( 'template_unknown_map', $post_id, $id ); }
			elseif ( ! isset( $entry['version'] ) || $entry['version'] !== $map['version'] ) { $this->diagnostic( 'template_schema_version_mismatch', $post_id, $id ); }
		}
		return $root;
	}
	public function clear( $post_id ) { unset( $this->cache[ $post_id ] ); }
	public function data( $path, $fallback = null, array $context = array() ) {
		$id = $this->post_id( $context ); $map = $this->map( $id ); $root = $id ? $this->payload( $id ) : array();
		if ( ! $map ) { return $fallback; }
		$values = isset( $root['maps'][ $map['id'] ]['data'] ) && is_array( $root['maps'][ $map['id'] ]['data'] ) ? $root['maps'][ $map['id'] ]['data'] : array();
		$parts = explode( '.', $path );
		if ( 1 === count( $parts ) && isset( $map['groups'][ $path ] ) ) {
			$out = array(); foreach ( $map['groups'][ $path ]['fields'] as $key => $field ) { $out[ $key ] = $this->data( $path . '.' . $key, null, $context ); } return $out;
		}
		if ( array_key_exists( $path, $values ) ) { return $values[ $path ]; }
		if ( isset( $map['fields'][ $path ] ) && array_key_exists( 'default', $map['fields'][ $path ] ) ) { return $map['fields'][ $path ]['default']; }
		return $fallback;
	}
	public function preflight() {
		$id = $this->post_id(); $map = $this->map( $id );
		if ( $id ) { $this->payload( $id ); }
		if ( $map ) { foreach ( $map['components'] as $component ) { flx_use_component( $component ); } }
	}
}
