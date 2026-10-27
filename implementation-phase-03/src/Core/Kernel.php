<?php
namespace Flexora\Core;

use Flexora\Contracts\V1\ContextV1;
use Flexora\Contracts\V1\FrameworkV1;
use Flexora\Contracts\V1\RegistrarV1;
use Flexora\Assets\AssetRegistry;
use Flexora\Components\ComponentRegistry;
use Flexora\Modules\ModuleRegistry;
use Flexora\Support\VersionRange;

/** Lifecycle coordinator. Feature implementations do not live in this class. */
final class Kernel {
	const VERSION = '0.1.0';
	// Runtime subset version; Contracts/V1 describes the future target, not completeness.
	const API_VERSION = '0.1.0';
	private $profile;
	private $diagnostics;
	private $ledger;
	private $definitions;
	private $modules;
	private $assets;
	private $components;
	private $availability;
	private $facade;
	private $registration_dispatched = false;
	public function __construct( $profile ) {
		if ( ! in_array( $profile, array( 'az-bridge', 'flexora-native' ), true ) ) {
			throw new \InvalidArgumentException( 'Unknown delivery profile.' );
		}
		$this->profile = $profile;
		$this->diagnostics = new Diagnostics();
		$this->ledger = new HookLedger();
		$this->definitions = new Definitions( $this->diagnostics );
		$this->modules = new ModuleRegistry( $this->definitions, $this->diagnostics, $this->ledger, $profile );
		$availability = new Availability( $this->definitions, $this->modules );
		$this->availability = $availability;
		$this->assets = new AssetRegistry( $this->definitions, $availability, $this->diagnostics );
		$this->components = new ComponentRegistry( $this->definitions, $availability, $this->assets, $this->diagnostics );
		$this->facade = new FrameworkV1( $this );
	}
	public function attach() {
		$this->ledger->attach( 'action', 'after_setup_theme', array( $this, 'register' ), 30, 0, 'parent', $this->profile );
		$this->ledger->attach( 'action', 'after_setup_theme', array( $this, 'seal' ), 40, 0, 'parent', $this->profile );
		foreach ( array( 'wp', 'admin_init', 'rest_api_init', 'init' ) as $hook ) {
			$this->ledger->attach( 'action', $hook, array( $this, 'boot_scope' ), 10, 0, 'parent', $this->profile );
		}
		foreach ( array( 'wp_enqueue_scripts', 'admin_enqueue_scripts' ) as $hook ) {
			$this->ledger->attach( 'action', $hook, array( $this->assets, 'flush' ), 100, 0, 'parent', $this->profile );
		}
	}
	public function register() {
		if ( $this->registration_dispatched || $this->definitions->sealed() ) {
			return;
		}
		$this->registration_dispatched = true;
		$this->definitions->open();
		$definitions = $this->definitions;
		$registrar = new RegistrarV1( function ( $kind, $spec ) use ( $definitions ) {
			return $definitions->register( $kind, $spec );
		} );
		do_action( 'flexora/register', $registrar );
	}
	public function seal() {
		$this->definitions->seal();
	}
	public function context() {
		$scope = 'frontend';
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$scope = 'cli';
		} elseif ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			$scope = 'cron';
		} elseif ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			$scope = 'rest';
		} elseif ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$scope = 'ajax';
		} elseif ( is_admin() ) {
			$scope = 'admin';
		}
		return new ContextV1( array( 'scope' => $scope, 'site_id' => isset( $GLOBALS['blog_id'] ) ? $GLOBALS['blog_id'] : null, 'stylesheet' => isset( $GLOBALS['stylesheet'] ) ? $GLOBALS['stylesheet'] : null, 'locale' => isset( $GLOBALS['locale'] ) ? $GLOBALS['locale'] : null, 'object_type' => null, 'object_id' => null, 'capabilities' => array(), 'instance_id' => null ) );
	}
	public function boot_scope() {
		$context = $this->context();
		$phases = array( 'frontend' => 'wp', 'admin' => 'admin_init', 'ajax' => 'admin_init', 'rest' => 'rest_api_init', 'cron' => 'init', 'cli' => 'init' );
		if ( $this->definitions->sealed() && current_filter() === $phases[ $context->scope ] ) {
			$this->modules->boot( $context );
		}
	}
	private function checked_context( $context ) {
		$current = $this->context();
		// Phase 03 only supports the current request identity, never caller authority.
		if ( null !== $context && ( ! $context instanceof ContextV1 || $context->to_array() !== $current->to_array() ) ) {
			$this->diagnostics->add( 'unsupported_context' );
			return null;
		}
		return $current;
	}
	public function use_asset( $id, $context = null ) {
		$context = $this->checked_context( $context );
		if ( null === $context ) {
			return new \Flexora\Contracts\V1\UsageResultV1( array( 'accepted' => false, 'assets' => array(), 'errors' => array( array( 'code' => 'unsupported_context' ) ) ) );
		}
		return $this->assets->request( $id, $context );
	}
	public function render( $id, $props = array(), $context = null ) {
		$context = $this->checked_context( $context );
		if ( null === $context ) {
			return new \Flexora\Contracts\V1\RenderResultV1( array( 'html' => '', 'usage' => array(), 'errors' => array( array( 'code' => 'unsupported_context' ) ), 'variant' => null, 'view_contract_version' => '1.0.0' ) );
		}
		return $this->components->render( $id, $props, $context );
	}
	public function use_component( $id, $context = null ) {
		$context = $this->checked_context( $context );
		if ( null === $context ) {
			return new \Flexora\Contracts\V1\UsageResultV1( array( 'accepted' => false, 'assets' => array(), 'usage' => array(), 'errors' => array( array( 'code' => 'unsupported_context' ) ) ) );
		}
		return $this->components->preflight( $id, $context );
	}
	public function supports( $feature, $range = '*' ) {
		if ( 'api' === $feature || 'foundation' === $feature ) {
			return VersionRange::matches( 'api' === $feature ? self::API_VERSION : self::VERSION, $range );
		}
		foreach ( $this->definitions->all( 'module' ) as $id => $spec ) {
			if ( in_array( $feature, $spec->capabilities, true ) && $this->availability->usable( 'module', $id, $this->context() ) && VersionRange::matches( $spec->version, $range ) ) {
				return true;
			}
		}
		return false;
	}
	public function module_status( $id ) {
		$status = $this->modules->status( $id );
		if ( ! $status->available ) {
			$this->diagnostics->add( 'unavailable_module', 'module', $id );
		}
		return $status;
	}
	public function facade() { return $this->facade; }
	public function diagnostics() { return $this->diagnostics->all(); }
	public function profile() { return $this->profile; }
	public function api_version() { return self::API_VERSION; }
	public function parent_version() { return self::VERSION; }
}
