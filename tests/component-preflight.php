<?php
require __DIR__ . '/wp-fixture.php';
use FLX\Contracts\V1\SpecV1;

$flx_profile = isset( $argv[1] ) ? $argv[1] : 'native';
require __DIR__ . '/../flexora/bootstrap.php';
if ( 'reentry' === $flx_profile ) { require __DIR__ . '/../flexora/bootstrap.php'; }
$GLOBALS['preflight_renders'] = 0;
function preflight_renderer( $props ) {
	$GLOBALS['preflight_renders']++;
	return '<article>Fixture</article>';
}
add_action( 'flx/register', function ( $r ) {
	foreach ( array( 'card-css' => 'style', 'slider-script' => 'script', 'late-css' => 'style', 'late-script' => 'script', 'unused-css' => 'style', 'window-css' => 'style' ) as $id => $type ) {
		$r->asset( new SpecV1( array( 'id' => $id, 'owner' => 'parent', 'version' => '1.0.0', 'type' => $type, 'handle' => $id, 'source' => $id . ( 'style' === $type ? '.css' : '.js' ), 'strategy' => 'footer', 'dependencies' => array(), 'context' => array( 'frontend' ) ) ) );
	}
	$r->provider( new SpecV1( array( 'id' => 'slider-provider', 'owner' => 'parent', 'version' => '1.0.0', 'dependencies' => array(), 'assets' => array( 'slider-script' ) ) ) );
	$r->behavior( new SpecV1( array( 'id' => 'slider', 'owner' => 'parent', 'version' => '1.0.0', 'dependencies' => array(), 'provider' => 'slider-provider', 'assets' => array() ) ) );
	foreach ( array( 'card' => array( 'card-css' ), 'hero-slider' => array(), 'product-carousel' => array(), 'late-card' => array( 'late-css' ), 'late-script-card' => array( 'late-script' ), 'unused-card' => array( 'unused-css' ), 'window-card' => array( 'window-css' ), 'composite' => array(), 'static' => array() ) as $id => $assets ) {
		$r->component( new SpecV1( array( 'id' => $id, 'owner' => 'parent', 'version' => '1.0.0', 'dependencies' => 'composite' === $id ? array( 'card', 'hero-slider' ) : array(), 'renderer' => 'preflight_renderer', 'assets' => $assets, 'behaviors' => in_array( $id, array( 'hero-slider', 'product-carousel' ), true ) ? array( 'slider' ) : array() ) ) );
	}
} );
check( '0.1.0' === flx()->api_version(), 'runtime exposes implementation preview version' );
check( ! flx_supports( 'api', '^1.0' ) && flx_supports( 'api', '^0.1' ), 'target v1 cannot be negotiated prematurely' );
foreach ( array( 'flx_option', 'flx_data', 'flx_map', 'flx_template', 'flx_icon' ) as $function ) {
	check( ! function_exists( $function ), 'no fake ' . $function );
}
do_action( 'after_setup_theme' );
$card = flx_use_component( 'card' );
check( $card->accepted && array( 'card-css' ) === $card->assets, 'component-only call resolves its internal stylesheet' );
check( 0 === $GLOBALS['preflight_renders'], 'preflight never invokes renderer' );
check( array() === $GLOBALS['fx_queue']['style'] && array() === $GLOBALS['fx_assets']['style'], 'preflight neither registers WP handles nor enqueues' );
check( flx_use_component( 'card' )->accepted && flx_use_component( 'card' )->accepted, 'repeated preflight idempotently accepted' );
check( flx_use_component( 'hero-slider' )->accepted && flx_use_component( 'product-carousel' )->accepted, 'two components resolve shared behavior/provider without caller knowledge' );
$composite = flx_use_component( 'composite' );
check( $composite->accepted && array( 'card-css', 'slider-script' ) === $composite->assets, 'component dependencies recursively preflight without rendering' );
check( ! flx_use_component( 'missing' )->accepted, 'unknown component preflight safe failure' );
check( ! flx_use_component( 'card', 'forged-context' )->accepted, 'preflight rejects unsupported context' );
do_action( 'wp_enqueue_scripts' );
check( array( 'card-css' => true ) === $GLOBALS['fx_queue']['style'], 'only used component stylesheet enqueued' );
check( array( 'slider-script' => true ) === $GLOBALS['fx_queue']['script'], 'shared provider script enqueued once' );
check( array( 'style:card-css' => 1, 'script:slider-script' => 1 ) === $GLOBALS['fx_enqueue_calls'], 'shared dependencies and repeated preflight do not duplicate enqueue' );
check( true === $GLOBALS['fx_assets']['script']['slider-script']['footer'], 'footer script is queued through WordPress footer flag' );
check( 0 === $GLOBALS['preflight_renders'], 'enqueue does not render or query component content' );
check( 'component_enqueue_window_closed' === flx_use_component( 'window-card' )->errors[0]['code'], 'first discovery between enqueue pass and head is explicit failure' );
do_action( 'wp_head' );
check( flx_use_component( 'card' )->accepted, 'preflight already queued dependency remains safe after head' );
ob_start();
$status = flx_render( 'card', array() );
$html = ob_get_clean();
check( '<article>Fixture</article>' === $html && array() === $status->errors && 1 === $GLOBALS['preflight_renders'], 'render after head succeeds once with only component-level preflight' );
check( array() === flx_component( 'hero-slider' )->errors && array() === flx_component( 'product-carousel' )->errors, 'both components render using shared footer dependency' );
$before = $GLOBALS['preflight_renders'];
$late = flx_component( 'late-card' );
check( '' === $late->html && 'late_component_style_usage' === $late->errors[0]['code'], 'late unpreflighted CSS does not silently render unstyled HTML' );
check( $before === $GLOBALS['preflight_renders'] && ! isset( $GLOBALS['fx_queue']['style']['late-css'] ), 'late rejected component neither renders nor global-loads CSS' );
check( 'late_component_script_usage' === flx_use_component( 'late-script-card' )->errors[0]['code'], 'new script-only component follows deterministic preflight cutoff' );
check( array() === flx_component( 'static' )->errors, 'asset-free static component can render late' );
check( in_array( 'late_component_style_usage', array_column( flx()->diagnostics(), 'code' ), true ), 'development diagnostics expose late CSS component identity' );
do_action( 'wp_enqueue_scripts' );
check( 1 === $GLOBALS['fx_enqueue_calls']['script:slider-script'] && 1 === $GLOBALS['fx_enqueue_calls']['style:card-css'], 'repeated flush remains idempotent after render' );
do_action( 'wp_print_footer_scripts' );
check( 'component_script_phase_closed' === flx_component( 'hero-slider' )->errors[0]['code'], 'behavior component rejects rendering after footer script phase' );
check( 0 === $GLOBALS['fx_writes'] && 0 === $GLOBALS['fx_queries'], 'usage flow does not write DB or query content' );
// The simulated Child workflow above intentionally never calls flx_asset().
echo 'PASS component preflight ' . $flx_profile . ': ' . $GLOBALS['fx_assertions'] . " assertions\n";
