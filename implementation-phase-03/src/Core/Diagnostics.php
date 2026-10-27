<?php
namespace Flexora\Core;

final class Diagnostics {
	private $entries = array();
	public function add( $code, $kind = '', $id = '' ) {
		// Only bounded code-owned identifiers; never props, source URLs or exceptions.
		$id = is_scalar( $id ) ? (string) $id : '';
		$entry = array( 'code' => $code, 'kind' => $kind, 'id' => preg_replace( '/[^a-zA-Z0-9_.:-]/', '', substr( $id, 0, 100 ) ) );
		$this->entries[ implode( '|', $entry ) ] = $entry;
		return $entry;
	}
	public function all() {
		return array_values( $this->entries );
	}
}
