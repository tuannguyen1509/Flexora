<?php
namespace FLX\Templates;

use FLX\Fields\Engine;

/** Portable, code-owned maps. Field definitions remain the Phase06 schema. */
final class MapRegistry {
	private $maps = array();
	private $files = array();
	private $sealed = false;
	private $engine;
	public function __construct( Engine $engine ) { $this->engine = $engine; }
	public function register( array $map ) {
		if ( $this->sealed ) { throw new \InvalidArgumentException( 'Late template map registration.' ); }
		foreach ( array( 'id', 'label', 'template', 'version', 'groups' ) as $key ) { if ( ! isset( $map[ $key ] ) ) { throw new \InvalidArgumentException( 'Incomplete template map.' ); } }
		if ( ! is_string( $map['id'] ) || ! preg_match( '/^[a-z][a-z0-9_-]*$/D', $map['id'] ) || ! is_string( $map['label'] ) || ! is_int( $map['version'] ) || $map['version'] < 1 || ! is_array( $map['groups'] ) ) { throw new \InvalidArgumentException( 'Invalid template map metadata.' ); }
		$file = $map['template'];
		if ( ! is_string( $file ) || ! preg_match( '#^[a-zA-Z0-9_/-]+\.php$#D', $file ) || '/' === substr( $file, 0, 1 ) || false !== strpos( $file, '..' ) ) { throw new \InvalidArgumentException( 'Template path must be portable and relative.' ); }
		if ( isset( $this->maps[ $map['id'] ] ) || isset( $this->files[ $file ] ) ) { throw new \InvalidArgumentException( 'Duplicate template map ID or file.' ); }
		$fields = array(); $groups = array();
		foreach ( $map['groups'] as $group ) {
			if ( ! isset( $group['id'], $group['label'], $group['fields'] ) || ! is_string( $group['id'] ) || ! is_string( $group['label'] ) || ! preg_match( '/^[a-z][a-z0-9_-]*$/D', $group['id'] ) || isset( $groups[ $group['id'] ] ) || ! is_array( $group['fields'] ) ) { throw new \InvalidArgumentException( 'Invalid or duplicate template group.' ); }
			$this->engine->check_schema( $group['fields'] ); $groups[ $group['id'] ] = $group;
			foreach ( $group['fields'] as $key => $field ) { if ( false !== strpos( $key, '.' ) ) { throw new \InvalidArgumentException( 'Map field IDs are local segments; group scopes the path.' ); } $fields[ $group['id'] . '.' . $key ] = $field; }
		}
		$map['groups'] = $groups; $map['fields'] = $fields;
		$map['components'] = isset( $map['components'] ) ? $map['components'] : array();
		if ( ! is_array( $map['components'] ) ) { throw new \InvalidArgumentException( 'Components must be IDs.' ); }
		foreach ( $map['components'] as $id ) { if ( ! is_string( $id ) || ! preg_match( '/^[a-z][a-z0-9_.-]*$/D', $id ) ) { throw new \InvalidArgumentException( 'Invalid component ID.' ); } }
		$this->maps[ $map['id'] ] = $map; $this->files[ $file ] = $map['id'];
	}
	public function get( $id ) { return isset( $this->maps[ $id ] ) ? $this->maps[ $id ] : null; }
	public function by_file( $file ) { return isset( $this->files[ $file ] ) ? $this->get( $this->files[ $file ] ) : null; }
	public function all() { return $this->maps; }
	public function seal() { $this->sealed = true; }
}
