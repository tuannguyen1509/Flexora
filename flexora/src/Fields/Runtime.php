<?php
namespace FLX\Fields;

/** One shared field engine for settings and page maps. */
final class Runtime {
	private static $engine;
	public static function attach() { add_action( 'flx/register', array( AdminAssets::class, 'register' ), 10 ); }
	public static function engine() {
		if ( ! self::$engine ) {
			$types = new TypeRegistry(); BuiltinTypes::register( $types );
			do_action( 'flx/fields/register', $types ); $types->seal();
			self::$engine = new Engine( $types );
		}
		return self::$engine;
	}
}
