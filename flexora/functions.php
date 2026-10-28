<?php
/** Canonical Parent entrypoint. */
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/bootstrap.php';
FLX\Core\ThemeSetup::attach();
FLX\Admin\Advanced::attach();
