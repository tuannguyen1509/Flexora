<?php
namespace Flexora\Modules;

use Flexora\Contracts\V1\ModuleStatusV1;
use Flexora\Contracts\V1\HookRegistrarV1;

/** Owns module states and scoped, exactly-once boot. No activation or DB writes. */
final class ModuleRegistry {
	private $definitions;
	private $diagnostics;
	private $ledger;
	private $profile;
	private $booted = array();
	private $failed = array();
	public function __construct( $definitions, $diagnostics, $ledger, $profile ) {
		$this->definitions = $definitions;
		$this->diagnostics = $diagnostics;
		$this->ledger = $ledger;
		$this->profile = $profile;
	}
	public function status( $id, $trail = array() ) {
		$spec = $this->definitions->get( 'module', $id );
		$reason = '';
		if ( ! $spec ) {
			$reason = 'undeclared';
		} elseif ( ! $spec->enabled ) {
			$reason = 'disabled';
		} elseif ( ! $this->definitions->sealed() ) {
			$reason = 'pending_registration';
		} elseif ( ! $spec->available || ! $this->definitions->valid( 'module', $id ) || isset( $this->failed[ $id ] ) ) {
			$reason = 'unavailable';
		} else {
			$trail[ $id ] = true;
			foreach ( $spec->dependencies as $dependency ) {
				if ( isset( $trail[ $dependency ] ) || ! $this->status( $dependency, $trail )->available ) {
					$reason = 'dependency_unavailable';
					break;
				}
			}
		}
		return new ModuleStatusV1( array( 'declared' => null !== $spec, 'enabled' => $spec ? $spec->enabled : false, 'available' => '' === $reason, 'booted' => isset( $this->booted[ $id ] ) ? array_keys( $this->booted[ $id ] ) : array(), 'reason' => $reason, 'version' => $spec ? $spec->version : null ) );
	}
	public function boot( $context ) {
		foreach ( $this->definitions->all( 'module' ) as $id => $spec ) {
			$this->boot_one( $id, $context );
		}
	}
	private function boot_one( $id, $context ) {
		$spec = $this->definitions->get( 'module', $id );
		if ( ! $spec || ! in_array( $context->scope, $spec->scope, true ) || isset( $this->booted[ $id ][ $context->scope ] ) ) {
			return;
		}
		if ( ! $this->status( $id )->available ) {
			if ( $spec->enabled ) {
				$this->diagnostics->add( 'unavailable_module', 'module', $id );
			}
			return;
		}
		foreach ( $spec->dependencies as $dependency ) {
			$this->boot_one( $dependency, $context );
			if ( ! isset( $this->booted[ $dependency ][ $context->scope ] ) ) {
				$this->failed[ $id ] = true;
				$this->diagnostics->add( 'unavailable_module', 'module', $id );
				return;
			}
		}
		$this->booted[ $id ][ $context->scope ] = true;
		$ledger = $this->ledger;
		$checkpoint = $ledger->checkpoint();
		$profile = $this->profile;
		$hooks = new HookRegistrarV1( function ( $type, $hook, $callback, $priority, $args ) use ( $ledger, $profile, $spec ) {
			return $ledger->attach( $type, $hook, $callback, $priority, $args, $spec->owner, $profile );
		} );
		try {
			call_user_func( $spec->boot, $hooks, $context );
		} catch ( \Throwable $error ) {
			$ledger->rollback( $checkpoint );
			$this->failed[ $id ] = true;
			unset( $this->booted[ $id ][ $context->scope ] );
			$this->diagnostics->add( 'module_boot_failed', 'module', $id );
		}
	}
}
