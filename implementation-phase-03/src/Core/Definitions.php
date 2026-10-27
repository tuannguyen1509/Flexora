<?php
namespace Flexora\Core;

use Flexora\Contracts\V1\SpecV1;

/** Five small registries share validation, ownership and a dependency DAG. */
final class Definitions {
	private $items = array();
	private $valid = array();
	private $state = 'closed';
	private $diagnostics;
	public function __construct( Diagnostics $diagnostics ) {
		$this->diagnostics = $diagnostics;
	}
	public function open() {
		if ( 'closed' === $this->state ) {
			$this->state = 'open';
		}
	}
	public function register( $kind, SpecV1 $spec ) {
		$id = $spec->id;
		if ( 'open' !== $this->state ) {
			$this->diagnostics->add( 'late_registration', $kind, $id );
			return false;
		}
		if ( ! is_string( $id ) || ! preg_match( '/^[a-z][a-z0-9_.-]*$/D', $id ) || ! is_string( $spec->owner ) || '' === $spec->owner || ! is_string( $spec->version ) || ! preg_match( '/^\d+\.\d+\.\d+$/D', $spec->version ) ) {
			$this->diagnostics->add( 'invalid_definition', $kind );
			return false;
		}
		$key = $kind . ':' . $id;
		if ( isset( $this->items[ $key ] ) ) {
			$this->diagnostics->add( 'duplicate_' . $kind . '_id', $kind, $id );
			return false;
		}
		$fields = array(
			'asset' => array( 'type', 'handle', 'source', 'context', 'strategy', 'override_policy' ),
			'component' => array( 'renderer', 'validate_props', 'assets', 'behaviors', 'variant', 'view_contract_version' ),
			'module' => array( 'enabled', 'available', 'scope', 'capabilities', 'boot' ),
			'provider' => array( 'assets' ),
			'behavior' => array( 'provider', 'assets' ),
		);
		$common = array( 'id', 'owner', 'version', 'dependencies' );
		if ( 'module' !== $kind ) {
			$common[] = 'module';
		}
		if ( ! isset( $fields[ $kind ] ) || array_diff( array_keys( $spec->to_array() ), array_merge( $common, $fields[ $kind ] ) ) ) {
			$this->diagnostics->add( 'unsupported_definition_field', $kind, $id );
			return false;
		}
		if ( null !== $spec->module && ! is_string( $spec->module ) ) {
			$this->diagnostics->add( 'invalid_definition', $kind, $id );
			return false;
		}
		if ( ! $this->shape_valid( $kind, $spec ) ) {
			$this->diagnostics->add( 'invalid_definition', $kind, $id );
			return false;
		}
		$this->items[ $key ] = $spec;
		return true;
	}
	private function string_list( $value ) {
		if ( ! is_array( $value ) ) {
			return false;
		}
		foreach ( $value as $item ) {
			if ( ! is_string( $item ) || '' === $item ) {
				return false;
			}
		}
		return true;
	}
	private function shape_valid( $kind, SpecV1 $spec ) {
		if ( ! $this->string_list( $spec->dependencies ) ) {
			return false;
		}
		if ( 'asset' === $kind ) {
			return in_array( $spec->type, array( 'style', 'script' ), true ) && is_string( $spec->handle ) && preg_match( '/^[a-zA-Z0-9_.-]+$/D', $spec->handle ) && is_string( $spec->source ) && '' !== $spec->source && $this->string_list( $spec->context );
		}
		if ( 'module' === $kind ) {
			return is_bool( $spec->enabled ) && is_bool( $spec->available ) && $this->string_list( $spec->scope ) && $this->string_list( $spec->capabilities ) && is_callable( $spec->boot );
		}
		if ( 'component' === $kind ) {
			return is_callable( $spec->renderer ) && $this->string_list( $spec->assets ) && $this->string_list( $spec->behaviors );
		}
		if ( 'provider' === $kind || 'behavior' === $kind ) {
			return $this->string_list( $spec->assets ) && ( 'provider' === $kind || is_string( $spec->provider ) );
		}
		return false;
	}
	public function get( $kind, $id ) {
		if ( ! is_string( $id ) ) {
			return null;
		}
		$key = $kind . ':' . $id;
		return isset( $this->items[ $key ] ) ? $this->items[ $key ] : null;
	}
	public function all( $kind ) {
		$result = array();
		foreach ( $this->items as $key => $spec ) {
			if ( 0 === strpos( $key, $kind . ':' ) ) {
				$result[ $spec->id ] = $spec;
			}
		}
		return $result;
	}
	/** Dependencies are local IDs. Cross-kind edges are explicit descriptor fields. */
	public function edges( $kind, SpecV1 $spec ) {
		$edges = array();
		foreach ( $spec->dependencies as $id ) {
			$edges[] = array( $kind, $id );
		}
		if ( $spec->module ) {
			$edges[] = array( 'module', $spec->module );
		}
		if ( 'asset' !== $kind ) {
			foreach ( (array) $spec->assets as $id ) {
				$edges[] = array( 'asset', $id );
			}
		}
		if ( 'component' === $kind ) {
			foreach ( $spec->behaviors as $id ) {
				$edges[] = array( 'behavior', $id );
			}
		}
		if ( 'behavior' === $kind ) {
			$edges[] = array( 'provider', $spec->provider );
		}
		return $edges;
	}
	public function seal() {
		if ( 'sealed' === $this->state ) {
			return;
		}
		$this->state = 'sealed';
		// Duplicate physical handles are blocked, not silently reassigned.
		$handles = array();
		foreach ( $this->all( 'asset' ) as $id => $spec ) {
			if ( isset( $handles[ $spec->handle ] ) ) {
				$this->valid[ 'asset:' . $id ] = false;
				$this->valid[ 'asset:' . $handles[ $spec->handle ] ] = false;
				$this->diagnostics->add( 'asset_handle_conflict', 'asset', $id );
			}
			$handles[ $spec->handle ] = $id;
		}
		foreach ( $this->items as $key => $spec ) {
			list( $kind, $id ) = explode( ':', $key, 2 );
			$this->validate( $kind, $id, array() );
		}
	}
	private function validate( $kind, $id, $trail ) {
		$key = $kind . ':' . $id;
		if ( array_key_exists( $key, $this->valid ) ) {
			return $this->valid[ $key ];
		}
		$spec = $this->get( $kind, $id );
		if ( null === $spec || isset( $trail[ $key ] ) ) {
			$this->diagnostics->add( isset( $trail[ $key ] ) ? 'dependency_cycle' : 'unsupported_dependency', $kind, $id );
			return false;
		}
		$trail[ $key ] = true;
		$valid = true;
		foreach ( $this->edges( $kind, $spec ) as $edge ) {
			if ( ! $this->validate( $edge[0], $edge[1], $trail ) ) {
				$valid = false;
			}
			if ( 'asset' === $kind && 'asset' === $edge[0] ) {
				$dependency = $this->get( 'asset', $edge[1] );
				if ( $dependency && $dependency->type !== $spec->type ) {
					$valid = false;
					$this->diagnostics->add( 'unsupported_dependency', $kind, $id );
				}
			}
		}
		$this->valid[ $key ] = $valid;
		return $valid;
	}
	public function valid( $kind, $id ) {
		return ! empty( $this->valid[ $kind . ':' . $id ] );
	}
	public function sealed() {
		return 'sealed' === $this->state;
	}
}
