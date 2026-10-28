<?php
namespace FLX\Admin;

use FLX\Fields\Engine;

final class SectionRegistry {
	private $sections = array();
	private $engine;
	private $sealed = false;
	public function __construct( Engine $engine ) { $this->engine = $engine; }
	public function register( $id, array $section ) {
		if ( $this->sealed || isset( $this->sections[ $id ] ) || ! preg_match( '/^[a-z][a-z0-9_.-]*$/D', $id ) || ! isset( $section['title'], $section['groups'] ) || ! is_array( $section['groups'] ) ) { throw new \InvalidArgumentException( 'Duplicate, late or invalid settings section.' ); }
		$fields = array();
		foreach ( $section['groups'] as $group ) {
			if ( ! isset( $group['title'], $group['fields'] ) || ! is_array( $group['fields'] ) ) { throw new \InvalidArgumentException( 'Invalid settings group.' ); }
			foreach ( $group['fields'] as $key => $field ) {
				if ( isset( $fields[ $key ] ) ) { throw new \InvalidArgumentException( 'Duplicate field ID within section.' ); }
				$fields[ $key ] = $field;
			}
		}
		$this->engine->check_schema( $fields ); $section['fields'] = $fields;
		$this->sections[ $id ] = $section;
	}
	public function all() { return $this->sections; }
	public function get( $id ) { return isset( $this->sections[ $id ] ) ? $this->sections[ $id ] : null; }
	public function seal() { $this->sealed = true; }
}
