<?php
/** Native FLX entrypoint. */
if ( ! defined( 'ABSPATH' ) ) {
	return;
}
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/public-api.php';
FLX\Core\Runtime::initialize( 'flx-native', get_template_directory_uri() );
