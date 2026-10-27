<?php
require __DIR__ . '/wp-fixture.php';
$root = dirname( __DIR__ );
$files = array( $root . '/autoload.php', $root . '/bootstrap.php', $root . '/public-api.php' );
foreach ( array( 'src', 'assets', 'profiles' ) as $directory ) {
	foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root . '/' . $directory, FilesystemIterator::SKIP_DOTS ) ) as $file ) {
		if ( in_array( $file->getExtension(), array( 'php', 'js', 'css' ), true ) ) { $files[] = $file->getPathname(); }
	}
}
$bad = array();
foreach ( $files as $file ) {
	if ( preg_match( '/az_|az-|AZ_|\.az-|--az-|data-az-|flexora_|Flexora\\\\/', file_get_contents( $file ) ) ) { $bad[] = $file; }
}
check( array() === $bad, 'zero forbidden identifiers in all executable native runtime; no exceptions' );
check( ! is_file( $root . '/profiles/az-bridge.php' ) && ! is_file( $root . '/src/Compatibility/AzV1/manifest.php' ), 'no executable compatibility profile or manifest' );
require $root . '/bootstrap.php';
foreach ( array( 'flexora', 'flexora_supports', 'flexora_module', 'flexora_use_component', 'flexora_component', 'flexora_render', 'flexora_asset' ) as $old ) {
	check( ! function_exists( $old ), 'old preview API absent: ' . $old );
}
check( function_exists( 'flx' ) && function_exists( 'flx_use_component' ) && '0.1.0' === flx()->api_version(), 'native API preview uses FLX naming' );
$css = file_get_contents( $root . '/assets/accessibility/skip-link.css' );
foreach ( array( '.flx-skip-link {', '.flx-skip-link:focus', 'outline: 3px', 'prefers-reduced-motion', 'scroll-behavior: auto', '[hidden]', 'position: fixed', 'transform: translateY(0)', 'z-index: 1000000' ) as $contract ) {
	check( false !== strpos( $css, $contract ), 'CSS source contract: ' . $contract );
}
$module = file_get_contents( $root . '/src/Modules/Accessibility/Module.php' );
check( false !== strpos( $module, "flx_use_component( 'flx.accessibility.skip-link' )" ) && false === strpos( $module, 'flx_asset(' ), 'module consumer knows only component ID' );
$renderer = file_get_contents( $root . '/src/Components/Accessibility/SkipLink.php' );
check( ! preg_match( '/add_action|enqueue|update_option|wp_query|wpdb|require/i', preg_replace( '/\/\*.*?\*\//s', '', $renderer ) ), 'renderer contains no lifecycle or data operations' );
echo 'PASS naming/CSS source contracts: ' . $GLOBALS['fx_assertions'] . " assertions\n";
