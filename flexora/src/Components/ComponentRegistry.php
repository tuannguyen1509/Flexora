<?php
namespace FLX\Components;

use FLX\Contracts\V1\RenderResultV1;
use FLX\Contracts\V1\UsageResultV1;

final class ComponentRegistry {
	private $definitions;
	private $availability;
	private $assets;
	private $diagnostics;
	private $rendering = array();
	public function __construct( $definitions, $availability, $assets, $diagnostics ) {
		$this->definitions = $definitions;
		$this->availability = $availability;
		$this->assets = $assets;
		$this->diagnostics = $diagnostics;
	}
	private function failure( $id, $code ) {
		return new RenderResultV1( array( 'html' => '', 'usage' => array(), 'errors' => array( $this->diagnostics->add( $code, 'component', $id ) ), 'variant' => null, 'view_contract_version' => '1.0.0' ) );
	}
	private function usage( $kind, $id, &$assets, &$behaviors ) {
		$spec = $this->definitions->get( $kind, $id );
		foreach ( (array) $spec->assets as $asset ) {
			$assets[ $asset ] = true;
		}
		if ( 'behavior' === $kind ) {
			$behaviors[ $id ] = true;
		}
		foreach ( $this->definitions->edges( $kind, $spec ) as $edge ) {
			if ( in_array( $edge[0], array( 'component', 'behavior', 'provider' ), true ) ) {
				$this->usage( $edge[0], $edge[1], $assets, $behaviors );
			}
		}
	}
	public function render( $id, $props, $context ) {
		$spec = $this->definitions->get( 'component', $id );
		if ( ! $spec ) {
			return $this->failure( $id, 'component_unknown' );
		}
		if ( ! $this->availability->usable( 'component', $id, $context ) ) {
			return $this->failure( $id, 'dependency_unavailable' );
		}
		if ( isset( $this->rendering[ $id ] ) ) {
			return $this->failure( $id, 'recursive_render' );
		}
		try {
			$props = apply_filters( 'flx/component/props', $props, $id, $context );
			if ( ! is_array( $props ) || ( $spec->validate_props && ( ! is_callable( $spec->validate_props ) || true !== call_user_func( $spec->validate_props, $props ) ) ) ) {
				return $this->failure( $id, 'invalid_props' );
			}
		} catch ( \Throwable $error ) {
			return $this->failure( $id, 'invalid_props' );
		}
		$preflight = $this->preflight( $id, $context );
		if ( ! $preflight->accepted ) {
			return $this->failure( $id, $preflight->errors[0]['code'] );
		}
		$usage = $preflight->usage;
		$this->rendering[ $id ] = true;
		$level = ob_get_level();
		ob_start();
		try {
			$html = call_user_func( $spec->renderer, $props, $context );
			$echoed = ob_get_contents();
			if ( ! is_string( $html ) || '' !== $echoed ) {
				throw new \UnexpectedValueException( 'Renderer must return a string without output.' );
			}
		} catch ( \Throwable $error ) {
			return $this->failure( $id, 'renderer_failed' );
		} finally {
			while ( ob_get_level() > $level ) {
				ob_end_clean();
			}
			unset( $this->rendering[ $id ] );
		}
		return new RenderResultV1( array( 'html' => $html, 'usage' => $usage, 'errors' => array(), 'variant' => $spec->variant ? $spec->variant : 'default', 'view_contract_version' => $spec->view_contract_version ? $spec->view_contract_version : '1.0.0' ) );
	}
	private function usage_failure( $id, $code ) {
		return new UsageResultV1( array( 'accepted' => false, 'assets' => array(), 'usage' => array(), 'errors' => array( $this->diagnostics->add( $code, 'component', $id ) ) ) );
	}
	/** Pure descriptor traversal plus usage recording; no props, view or content query. */
	public function preflight( $id, $context ) {
		if ( ! $this->definitions->get( 'component', $id ) ) {
			return $this->usage_failure( $id, 'component_unknown' );
		}
		if ( ! $this->availability->usable( 'component', $id, $context ) ) {
			return $this->usage_failure( $id, 'dependency_unavailable' );
		}
		$assets = array();
		$behaviors = array();
		$this->usage( 'component', $id, $assets, $behaviors );
		$error = $this->assets->component_error( array_keys( $assets ), $context );
		if ( null !== $error ) {
			return $this->usage_failure( $id, $error );
		}
		$usage = array();
		$resolved_assets = array();
		foreach ( array_keys( $assets ) as $asset ) {
			$result = $this->assets->request( $asset, $context );
			if ( ! $result->accepted ) {
				return $this->usage_failure( $id, 'asset_unavailable' );
			}
			foreach ( $result->assets as $used ) {
				$resolved_assets[ $used ] = true;
				$usage[ 'asset:' . $used ] = array( 'type' => 'asset', 'id' => $used );
			}
		}
		foreach ( array_keys( $behaviors ) as $behavior ) {
			$usage[ 'behavior:' . $behavior ] = array( 'type' => 'behavior', 'id' => $behavior );
		}
		return new UsageResultV1( array( 'accepted' => true, 'assets' => array_keys( $resolved_assets ), 'usage' => array_values( $usage ), 'errors' => array() ) );
	}
}
