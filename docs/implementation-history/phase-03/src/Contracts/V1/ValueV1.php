<?php
namespace FLX\Contracts\V1;

/** Immutable value surface using PHP 7-compatible syntax. Arrays return by value. */
abstract class ValueV1 {
	private $values;
	public function __construct( array $values ) {
		$this->values = $values;
	}
	public function __get( $name ) {
		return array_key_exists( $name, $this->values ) ? $this->values[ $name ] : null;
	}
	public function __isset( $name ) {
		return array_key_exists( $name, $this->values );
	}
	public function __set( $name, $value ) {
		throw new \LogicException( 'Public values are immutable.' );
	}
	public function __unset( $name ) {
		throw new \LogicException( 'Public values are immutable.' );
	}
	public function to_array() {
		return $this->values;
	}
}
