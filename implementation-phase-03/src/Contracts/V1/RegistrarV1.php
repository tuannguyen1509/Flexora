<?php
namespace Flexora\Contracts\V1;

/** Only the implemented foundation extension surfaces are exposed. */
final class RegistrarV1 {
	private $register;
	public function __construct( $register ) {
		$this->register = $register;
	}
	public function asset( SpecV1 $spec ) {
		return call_user_func( $this->register, 'asset', $spec );
	}
	public function component( SpecV1 $spec ) {
		return call_user_func( $this->register, 'component', $spec );
	}
	public function module( SpecV1 $spec ) {
		return call_user_func( $this->register, 'module', $spec );
	}
	public function provider( SpecV1 $spec ) {
		return call_user_func( $this->register, 'provider', $spec );
	}
	public function behavior( SpecV1 $spec ) {
		return call_user_func( $this->register, 'behavior', $spec );
	}
}
