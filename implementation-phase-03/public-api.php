<?php
/** Preview subset of target Public API v1; runtime access stays inside this boundary. */
function flexora() {
	return Flexora\Core\Runtime::kernel()->facade();
}
function flexora_supports( $feature, $version_range = '*' ) {
	return flexora()->supports( $feature, $version_range );
}
function flexora_module( $id ) {
	return Flexora\Core\Runtime::kernel()->module_status( $id );
}
function flexora_asset( $id, $context = null ) {
	return Flexora\Core\Runtime::kernel()->use_asset( $id, $context );
}
function flexora_component( $id, $props = array(), $context = null ) {
	return Flexora\Core\Runtime::kernel()->render( $id, $props, $context );
}
/** Declare component usage before enqueue/head; never invokes its renderer. */
function flexora_use_component( $id, $context = null ) {
	return Flexora\Core\Runtime::kernel()->use_component( $id, $context );
}
function flexora_render( $id, $props = array(), $context = null ) {
	$result = flexora_component( $id, $props, $context );
	// Trusted registered renderer owns context-appropriate escaping.
	echo $result->html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return new Flexora\Contracts\V1\RenderStatusV1( array( 'errors' => $result->errors, 'usage' => $result->usage ) );
}
