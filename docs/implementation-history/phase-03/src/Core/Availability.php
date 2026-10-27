<?php
namespace FLX\Core;

/** Pure capability check. It never boots a dependency or requests assets. */
final class Availability {
	private $definitions;
	private $modules;
	public function __construct( $definitions, $modules ) {
		$this->definitions = $definitions;
		$this->modules = $modules;
	}
	public function usable( $kind, $id, $context, $trail = array() ) {
		$key = $kind . ':' . $id;
		if ( isset( $trail[ $key ] ) || ! $this->definitions->valid( $kind, $id ) ) {
			return false;
		}
		$spec = $this->definitions->get( $kind, $id );
		if ( 'module' === $kind ) {
			if ( ! $this->modules->status( $id )->available || ! in_array( $context->scope, $spec->scope, true ) ) {
				return false;
			}
		}
		if ( 'asset' === $kind && ! in_array( $context->scope, $spec->context, true ) ) {
			return false;
		}
		$trail[ $key ] = true;
		foreach ( $this->definitions->edges( $kind, $spec ) as $edge ) {
			if ( ! $this->usable( $edge[0], $edge[1], $context, $trail ) ) {
				return false;
			}
		}
		return true;
	}
}
