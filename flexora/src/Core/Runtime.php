<?php
namespace FLX\Core;

/** The sole composition root. Internal; never returned by the public facade. */
final class Runtime {
	private static $kernel;
	public static function initialize( $profile, $asset_base_uri = '' ) {
		if ( null === self::$kernel ) {
			self::$kernel = new Kernel( $profile, $asset_base_uri );
			self::$kernel->attach();
		} elseif ( self::$kernel->profile() !== $profile ) {
			throw new \LogicException( 'Only one Flexora delivery profile may run in a request.' );
		}
	}
	public static function kernel() {
		if ( null === self::$kernel ) {
			throw new \LogicException( 'Flexora has not been initialized.' );
		}
		return self::$kernel;
	}
}
