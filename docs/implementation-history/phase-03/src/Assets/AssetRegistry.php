<?php
namespace FLX\Assets;

use FLX\Contracts\V1\UsageResultV1;

/** Usage collection and WP enqueue only; no independent loader. */
final class AssetRegistry {
	private $definitions;
	private $availability;
	private $diagnostics;
	private $used = array();
	private $requested = array();
	private $sent = array();
	private $registered = array();
	private $flushed = false;
	public function __construct( $definitions, $availability, $diagnostics ) {
		$this->definitions = $definitions;
		$this->availability = $availability;
		$this->diagnostics = $diagnostics;
	}
	public function request( $id, $context ) {
		$code = null;
		if ( ! $this->definitions->get( 'asset', $id ) ) {
			$code = 'missing_asset';
		} elseif ( ! $this->availability->usable( 'asset', $id, $context ) ) {
			$code = 'dependency_unavailable';
		} elseif ( function_exists( 'did_action' ) && did_action( 'wp_head' ) && 'style' === $this->definitions->get( 'asset', $id )->type && ! isset( $this->sent[ $id ] ) ) {
			$code = 'late_style_usage';
		}
		if ( null !== $code ) {
			$error = $this->diagnostics->add( $code, 'asset', $id );
			return new UsageResultV1( array( 'accepted' => false, 'assets' => array(), 'errors' => array( $error ) ) );
		}
		$ids = $this->order( $id );
		foreach ( $ids as $used ) {
			$this->used[ $used ] = $context;
		}
		$this->requested[ $id ] = $context;
		return new UsageResultV1( array( 'accepted' => true, 'assets' => $ids, 'errors' => array() ) );
	}
	private function order( $id, $seen = array() ) {
		if ( isset( $seen[ $id ] ) ) {
			return array();
		}
		$seen[ $id ] = true;
		$result = array();
		$spec = $this->definitions->get( 'asset', $id );
		foreach ( $spec->dependencies as $dependency ) {
			$result = array_merge( $result, $this->order( $dependency, $seen ) );
		}
		$result[] = $id;
		return array_values( array_unique( $result ) );
	}
	public function flush() {
		$this->flushed = true;
		foreach ( $this->requested as $id => $context ) {
			if ( isset( $this->sent[ $id ] ) || ! $this->availability->usable( 'asset', $id, $context ) ) {
				continue;
			}
			$order = $this->order( $id );
			$ready = true;
			// Validate the entire graph before enqueueing any member.
			foreach ( $order as $dependency ) {
				if ( ! $this->register_wp( $dependency ) ) {
					$ready = false;
				}
			}
			if ( ! $ready ) {
				continue;
			}
			foreach ( $order as $dependency ) {
				if ( isset( $this->sent[ $dependency ] ) ) {
					continue;
				}
				$spec = $this->definitions->get( 'asset', $dependency );
				$enqueue = 'style' === $spec->type ? 'wp_enqueue_style' : 'wp_enqueue_script';
				$enqueue( $spec->handle );
				$this->sent[ $dependency ] = true;
			}
		}
	}
	private function register_wp( $id ) {
		if ( isset( $this->registered[ $id ] ) ) {
			return true;
		}
		$spec = $this->definitions->get( 'asset', $id );
		$check = 'style' === $spec->type ? 'wp_style_is' : 'wp_script_is';
		if ( $check( $spec->handle, 'registered' ) ) {
			// Explicit reference policy permits core/plugin assets without replacement.
			if ( 'reference' !== $spec->override_policy ) {
				$this->diagnostics->add( 'asset_handle_conflict', 'asset', $id );
				return false;
			}
		} else {
			if ( 'reference' === $spec->override_policy ) {
				$this->diagnostics->add( 'missing_wp_handle', 'asset', $id );
				return false;
			}
			$handles = array();
			foreach ( $spec->dependencies as $dependency ) {
				$handles[] = $this->definitions->get( 'asset', $dependency )->handle;
			}
			if ( 'style' === $spec->type ) {
				wp_register_style( $spec->handle, $spec->source, $handles, $spec->version );
			} else {
				// Boolean footer arg works before WP's newer strategy-array API.
				wp_register_script( $spec->handle, $spec->source, $handles, $spec->version, 'head' !== $spec->strategy );
			}
		}
		$this->registered[ $id ] = true;
		return true;
	}
	public function usage() {
		return array_keys( $this->used );
	}
	/** Check a component's full graph atomically before recording any usage. */
	public function component_error( array $ids, $context ) {
		foreach ( $ids as $id ) {
			if ( ! $this->availability->usable( 'asset', $id, $context ) ) {
				return 'dependency_unavailable';
			}
			foreach ( $this->order( $id ) as $dependency ) {
				if ( 'script' === $this->definitions->get( 'asset', $dependency )->type && did_action( 'wp_print_footer_scripts' ) ) {
					return 'component_script_phase_closed';
				}
				if ( isset( $this->sent[ $dependency ] ) ) {
					continue;
				}
				if ( did_action( 'wp_head' ) ) {
					return 'style' === $this->definitions->get( 'asset', $dependency )->type ? 'late_component_style_usage' : 'late_component_script_usage';
				}
				if ( $this->flushed ) {
					return 'component_enqueue_window_closed';
				}
			}
		}
		return null;
	}
}
