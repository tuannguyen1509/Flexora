<?php
/** Declarations only, also usable by an early compatibility shim. */
spl_autoload_register( function ( $class ) {
	$prefix = 'Flexora\\';
	if ( 0 !== strpos( $class, $prefix ) ) {
		return;
	}
	$file = __DIR__ . '/src/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
	if ( is_file( $file ) ) {
		require_once $file;
	}
} );
