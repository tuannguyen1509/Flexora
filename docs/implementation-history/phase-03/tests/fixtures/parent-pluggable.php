<?php
if ( ! function_exists( 'fx_legacy_pluggable' ) ) {
	function fx_legacy_pluggable() { $GLOBALS['fx_pluggable'][] = 'parent'; }
}
add_action( 'fixture/pluggable', 'fx_legacy_pluggable', 10, 0 );
