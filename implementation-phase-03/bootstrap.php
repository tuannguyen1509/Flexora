<?php
/** Isolated foundation entrypoint. Never includes AZ production code. */
if ( ! defined( 'ABSPATH' ) ) {
	return;
}
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/public-api.php';
Flexora\Core\Runtime::initialize( isset( $flexora_profile ) ? $flexora_profile : 'flexora-native' );
