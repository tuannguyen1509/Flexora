<?php
namespace Flexora\Contracts\V1;

/** Deliberately read-only. No registry, container, boot or service getter. */
final class FrameworkV1 {
	private $kernel;
	public function __construct( $kernel ) {
		$this->kernel = $kernel;
	}
	public function api_version() {
		return $this->kernel->api_version();
	}
	public function parent_version() {
		return $this->kernel->parent_version();
	}
	public function supports( $feature, $range = '*' ) {
		return $this->kernel->supports( $feature, $range );
	}
	public function diagnostics() {
		return $this->kernel->diagnostics();
	}
}
