<?php
namespace Flexora\Contracts\V1;

/** Module extension hook writer, not the internal ledger. */
final class HookRegistrarV1 {
	private $register;
	public function __construct( $register ) {
		$this->register = $register;
	}
	public function action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		return call_user_func( $this->register, 'action', $hook, $callback, $priority, $accepted_args );
	}
	public function filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		return call_user_func( $this->register, 'filter', $hook, $callback, $priority, $accepted_args );
	}
}
