<?php
namespace FLX\Templates;

final class Runtime {
	private static $registry;
	private static $resolver;
	public static function attach() { add_action( 'after_setup_theme', array( self::class, 'initialize' ), 36 ); }
	public static function initialize() {
		if ( self::$registry ) { return; }
		$engine = \FLX\Fields\Runtime::engine();
		self::$registry = new MapRegistry( $engine );
		self::$resolver = new Resolver( self::$registry, $engine, new \FLX\Data\PostMetaStorage() );
		do_action( 'flx/template-maps/register' ); self::$registry->seal();
		add_action( 'wp_enqueue_scripts', array( self::$resolver, 'preflight' ), 15 );
		foreach ( array( 'added_post_meta', 'updated_post_meta', 'deleted_post_meta' ) as $hook ) { add_action( $hook, array( self::class, 'invalidate' ), 10, 3 ); }
		( new PageAdmin( self::$registry, self::$resolver, $engine, new \FLX\Data\PostMetaStorage() ) )->attach();
	}
	public static function registry() { if ( ! self::$registry ) { throw new \LogicException( 'Template map registration is not open.' ); } return self::$registry; }
	public static function invalidate( $meta_id, $post_id, $key ) { if ( \FLX\Data\PostMetaStorage::KEY === $key ) { self::$resolver->clear( $post_id ); } }
	public static function resolver() { if ( ! self::$resolver ) { throw new \LogicException( 'Template maps not initialized.' ); } return self::$resolver; }
	public static function diagnostics() { return self::$resolver ? self::$resolver->diagnostics() : array(); }
}
