<?php
/** Local-only read-only probe. Never part of either theme ZIP. */
if ( '1' !== getenv( 'FLX_INTEGRATION_PROBE' ) || ! isset( $_GET['flx_probe'] ) || '1' !== $_GET['flx_probe'] ) {
	return;
}
add_action( 'wp_footer', function () {
	$hooks = array();
	foreach ( array( 'after_setup_theme', 'wp', 'wp_enqueue_scripts', 'wp_head', 'wp_body_open', 'wp_footer' ) as $hook ) {
		$hooks[ $hook ] = did_action( $hook );
	}
	$styles = wp_styles();
	$scripts = wp_scripts();
	$theme = wp_get_theme();
	$data = array(
		'stylesheet' => get_stylesheet(), 'template' => get_template(),
		'theme_name' => $theme->get( 'Name' ), 'theme_errors' => false !== $theme->errors(),
		'hooks' => $hooks, 'hook_class' => get_class( $GLOBALS['wp_filter']['wp_body_open'] ),
		'styles_class' => get_class( $styles ), 'scripts_class' => get_class( $scripts ),
		'styles_done' => $styles->done, 'scripts_done' => $scripts->done,
		'style_source' => isset( $styles->registered['flx-accessibility-skip-link'] ) ? $styles->registered['flx-accessibility-skip-link']->src : null,
		'script_source' => isset( $scripts->registered['flx-accessibility-skip-link-js'] ) ? $scripts->registered['flx-accessibility-skip-link-js']->src : null,
		'api_version' => function_exists( 'flx' ) ? flx()->api_version() : null,
		'module' => function_exists( 'flx_module' ) ? flx_module( 'flx.accessibility' )->to_array() : null,
		'body_callback_priority' => has_action( 'wp_body_open', array( 'FLX\\Modules\\Accessibility\\Module', 'render' ) ),
		'canonical_api' => function_exists( 'flx' ) && realpath( ( new ReflectionFunction( 'flx' ) )->getFileName() ) === realpath( get_template_directory() . '/public-api.php' ),
		'diagnostics' => function_exists( 'flx' ) ? flx()->diagnostics() : array(),
		'nested_preflight' => isset( $GLOBALS['flx_local_preflight'] ) ? $GLOBALS['flx_local_preflight'] : null,
	);
	echo "\n<!-- FLX_LOCAL_PROBE " . base64_encode( wp_json_encode( $data ) ) . " -->\n";
}, 99 );
add_action( 'wp_enqueue_scripts', function () {
	$GLOBALS['flx_local_preflight'] = flx_use_component( 'flx.accessibility.skip-link' )->accepted;
}, 20 );
