<?php
/** FLX declarations only. */
spl_autoload_register( function ( $class ) {
	$prefix = 'FLX\\';
	if ( 0 !== strpos( $class, $prefix ) ) {
		return;
	}
	$file = __DIR__ . '/src/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
	if ( is_file( $file ) ) {
		require_once $file;
	}
} );
