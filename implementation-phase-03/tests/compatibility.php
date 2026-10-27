<?php
require __DIR__ . '/wp-fixture.php';
$GLOBALS['fx_legacy_assets'] = 0;
$GLOBALS['fx_legacy_renders'] = 0;
$GLOBALS['fx_pluggable'] = array();
// WordPress Child functions.php phase, before any Parent bootstrap.
require_once __DIR__ . '/fixtures/early-contact.php';
check( ! function_exists( 'flexora' ), 'early shim does not load Parent API/kernel' );
check( 35 === has_action( 'wp_enqueue_scripts', 'fx_legacy_assets' ) && 5 === has_action( 'wp_footer', 'fx_legacy_render' ), 'early string callback priorities preserved' );
check( ! remove_action( 'wp_enqueue_scripts', 'fx_legacy_assets', 10 ), 'wrong removal priority has no effect' );
check( remove_action( 'wp_enqueue_scripts', 'fx_legacy_assets', 35 ) && remove_action( 'wp_footer', 'fx_legacy_render', 5 ), 'Child removes exact callback and priority' );
function fx_legacy_pluggable() { $GLOBALS['fx_pluggable'][] = 'child'; }
require __DIR__ . '/fixtures/child.php';
$flexora_profile = 'az-bridge';
require __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/fixtures/early-contact.php';
require_once __DIR__ . '/fixtures/parent-pluggable.php';
check( false === $GLOBALS['fx_early_ledger']->attach( 'action', 'wp_enqueue_scripts', 'fx_legacy_assets', 35, 0, 'legacy.fixture', 'az-v1' ), 'ledger does not resurrect externally removed callback' );
$entries = $GLOBALS['fx_early_ledger']->entries();
check( 'fx_legacy_assets' === $entries[0]['callback'] && 35 === $entries[0]['priority'] && 0 === $entries[0]['accepted_args'] && 'legacy.fixture' === $entries[0]['owner'] && 'az-v1' === $entries[0]['profile'], 'ledger captures callback identity/priority/args/owner/profile' );
do_action( 'after_setup_theme' );
check( array() === flexora_component( 'fixture.test-card', array( 'title' => 'Bridge' ) )->errors, 'bridge profile uses the same working engine' );
do_action( 'fixture/pluggable' );
do_action( 'wp_enqueue_scripts' );
do_action( 'wp_footer' );
check( array( 'child' ) === $GLOBALS['fx_pluggable'], 'Parent function_exists guard and string hook use Child implementation' );
check( 0 === $GLOBALS['fx_legacy_assets'] && 0 === $GLOBALS['fx_legacy_renders'], 'Parent bootstrap/require_once does not re-add legacy callbacks' );
$manifest = require __DIR__ . '/../src/Compatibility/AzV1/manifest.php';
check( 'az-theme' === $manifest['physical_parent_slug'] && false === $manifest['production_compatibility'], 'manifest protects slug and does not claim full compatibility' );
$native = require __DIR__ . '/../profiles/flexora-native.php';
$bridge = require __DIR__ . '/../profiles/az-bridge.php';
check( $native['engine_entry'] === $bridge['engine_entry'], 'profiles reference exactly one engine' );
$rejected = false;
try { Flexora\Core\Runtime::initialize( 'flexora-native' ); } catch ( LogicException $error ) { $rejected = true; }
check( $rejected, 'second active profile rejected' );
check( 0 === $GLOBALS['fx_writes'] && 0 === $GLOBALS['fx_queries'], 'compatibility harness performs no data mutation/query' );
echo 'PASS compatibility: ' . $GLOBALS['fx_assertions'] . " assertions\n";
