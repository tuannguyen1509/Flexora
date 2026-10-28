<?php
/** Preview subset of target Public API v1; runtime access stays inside this boundary. */
/** Register during flx/settings/register. Field IDs are stable storage identities. */
function flx_register_settings_section( $id, array $section ) {
	FLX\Admin\Advanced::instance()->register_section( $id, $section );
}
function flx() {
	return FLX\Core\Runtime::kernel()->facade();
}
function flx_supports( $feature, $version_range = '*' ) {
	return flx()->supports( $feature, $version_range );
}
function flx_module( $id ) {
	return FLX\Core\Runtime::kernel()->module_status( $id );
}
function flx_asset( $id, $context = null ) {
	return FLX\Core\Runtime::kernel()->use_asset( $id, $context );
}
function flx_component( $id, $props = array(), $context = null ) {
	return FLX\Core\Runtime::kernel()->render( $id, $props, $context );
}
/** Declare component usage before enqueue/head; never invokes its renderer. */
function flx_use_component( $id, $context = null ) {
	return FLX\Core\Runtime::kernel()->use_component( $id, $context );
}
function flx_render( $id, $props = array(), $context = null ) {
	$result = flx_component( $id, $props, $context );
	// Trusted registered renderer owns context-appropriate escaping.
	echo $result->html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return new FLX\Contracts\V1\RenderStatusV1( array( 'errors' => $result->errors, 'usage' => $result->usage ) );
}
