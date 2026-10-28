<?php
namespace FLX\Fields;

/** Small callback-based extension contract; storage is deliberately absent. */
final class TypeRegistry {
	private $types = array();
	private $sealed = false;
	public function register( $id, array $callbacks ) {
		if ( $this->sealed || isset( $this->types[ $id ] ) || ! preg_match( '/^[a-z][a-z0-9_-]*$/D', $id ) ) { throw new \InvalidArgumentException( 'Duplicate, late or invalid field type.' ); }
		foreach ( array( 'render', 'normalize', 'sanitize', 'validate', 'default' ) as $method ) {
			if ( ! isset( $callbacks[ $method ] ) || ! is_callable( $callbacks[ $method ] ) ) { throw new \InvalidArgumentException( 'Incomplete field type contract.' ); }
		}
		$this->types[ $id ] = $callbacks;
	}
	public function get( $id ) {
		if ( ! isset( $this->types[ $id ] ) ) { throw new \InvalidArgumentException( 'Unknown field type: ' . $id ); }
		return $this->types[ $id ];
	}
	public function seal() { $this->sealed = true; }
	public function ids() { return array_keys( $this->types ); }
}
