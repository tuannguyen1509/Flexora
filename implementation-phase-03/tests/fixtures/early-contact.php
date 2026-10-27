<?php
/** Synthetic shim pattern; never defines/calls real AZ production functions. */
require_once __DIR__ . '/../../autoload.php';
function fx_legacy_assets() { $GLOBALS['fx_legacy_assets']++; }
function fx_legacy_render() { $GLOBALS['fx_legacy_renders']++; }
if ( ! isset( $GLOBALS['fx_early_ledger'] ) ) {
	$GLOBALS['fx_early_ledger'] = new Flexora\Core\HookLedger();
}
$GLOBALS['fx_early_ledger']->attach( 'action', 'wp_enqueue_scripts', 'fx_legacy_assets', 35, 0, 'legacy.fixture', 'az-v1' );
$GLOBALS['fx_early_ledger']->attach( 'action', 'wp_footer', 'fx_legacy_render', 5, 0, 'legacy.fixture', 'az-v1' );
