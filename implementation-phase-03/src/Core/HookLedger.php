<?php
namespace Flexora\Core;

/** Records the first attachment, including callbacks removed outside the ledger. */
final class HookLedger {
	private $entries = array();
	private function identity( $callback ) {
		if ( is_string( $callback ) ) {
			return $callback;
		}
		if ( is_array( $callback ) ) {
			return ( is_object( $callback[0] ) ? spl_object_hash( $callback[0] ) : $callback[0] ) . '::' . $callback[1];
		}
		return spl_object_hash( $callback );
	}
	public function attach( $type, $hook, $callback, $priority, $accepted_args, $owner, $profile ) {
		$key = $hook . '|' . $this->identity( $callback ) . '|' . $priority;
		if ( isset( $this->entries[ $key ] ) ) {
			return false;
		}
		$this->entries[ $key ] = array( 'hook' => $hook, 'callback' => $callback, 'priority' => $priority, 'accepted_args' => $accepted_args, 'owner' => $owner, 'profile' => $profile );
		$function = 'filter' === $type ? 'add_filter' : 'add_action';
		$function( $hook, $callback, $priority, $accepted_args );
		return true;
	}
	public function entries() {
		return array_values( $this->entries );
	}
	public function checkpoint() {
		return array_keys( $this->entries );
	}
	public function rollback( $checkpoint ) {
		foreach ( $this->entries as $key => $entry ) {
			if ( ! in_array( $key, $checkpoint, true ) ) {
				remove_filter( $entry['hook'], $entry['callback'], $entry['priority'] );
				// Retain the record so failure cannot trigger accidental reattachment.
			}
		}
	}
}
