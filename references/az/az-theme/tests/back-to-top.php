<?php
/** Isolated Footer controls, legacy settings and frontend hook checks. */
define( 'ABSPATH', dirname( __DIR__, 4 ) . '/' );
$options = array();
$hooks = array();
$scripts = array();
$styles = array();
require ABSPATH . 'wp-includes/class-wp-dependency.php';
require ABSPATH . 'wp-includes/class-wp-dependencies.php';
$style_registry = new WP_Dependencies();
function get_option( $key, $default = null ) { return 'az_theme_options' === $key ? $GLOBALS['options'] : $default; }
function add_action( $hook, $callback, $priority = 10 ) { $GLOBALS['hooks'][ $hook ][ $priority ][] = $callback; }
function add_shortcode( $tag, $callback ) {}
function get_template_directory() { return dirname( __DIR__ ); }
function get_template_directory_uri() { return '/theme'; }
function wp_enqueue_script( ...$args ) { $GLOBALS['scripts'][] = $args; }
function wp_enqueue_style( $handle, $url, $deps, $version ) {
    $GLOBALS['styles'][] = array( $handle, $url, $deps, $version );
    $GLOBALS['style_registry']->add( $handle, $url, $deps, $version );
    $GLOBALS['style_registry']->enqueue( array( $handle ) );
}
function wp_styles() { return $GLOBALS['style_registry']; }
function add_filter( ...$args ) {}
function is_user_logged_in() { return true; }
function sanitize_key( $value ) { return $value; }
function sanitize_html_class( $value ) { return $value; }
function esc_attr( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $value ) { return esc_attr( $value ); }
function checked( $value, $expected, $echo = false ) { return (string) $value === (string) $expected ? 'checked="checked"' : ''; }
function selected( $value, $expected, $echo = false ) { return (string) $value === (string) $expected ? 'selected="selected"' : ''; }
$checks = 0;
function check( $ok, $message ) {
    if ( ! $ok ) { throw new RuntimeException( $message ); }
    $GLOBALS['checks']++;
    echo "PASS $message\n";
}
function output( $callback ) { ob_start(); $callback(); return ob_get_clean(); }
require dirname( __DIR__ ) . '/inc/helpers/options.php';
require dirname( __DIR__ ) . '/inc/options/class-renderer.php';
require dirname( __DIR__ ) . '/inc/options/class-sanitizer.php';
require dirname( __DIR__ ) . '/inc/core/class-site-effects.php';
$effects = new AZ_Site_Effects();
$fields = array_column( require dirname( __DIR__ ) . '/inc/admin/sections/footer.php', null, 'id' );
$toggle = $fields['back_to_top'];
$position = $fields['back_to_top_position'];
$render = static function () use ( $effects ) { $effects->render_back_to_top(); };
$admin = static function () use ( $toggle ) { AZ_Options_Renderer::field( 'footer', $toggle, $GLOBALS['options'] ); };

check( '' === output( $render ), 'new installs do not render a disabled button' );
$effects->enqueue_assets();
check( ! $scripts && ! $styles, 'disabled effects do not load CSS or a script' );
$options = array( 'general' => array( 'back_to_top' => 1 ) );
check( false !== strpos( output( $render ), 'az-back-to-top--right' ), 'old General setting keeps its button on the right' );
check( false !== strpos( output( $admin ), 'checked="checked"' ), 'Footer switch reflects the old saved General setting' );
$options['footer'] = array( 'back_to_top' => 0 );
check( '' === output( $render ), 'explicit Footer off overrides old General on' );
check( false === strpos( output( $admin ), 'checked="checked"' ), 'admin switch also honors explicit Footer off' );
$effects->enqueue_assets();
check( ! $scripts, 'Footer off prevents back-to-top script loading' );
$options['general']['preloader'] = 1;
$effects->enqueue_assets();
check( 1 === count( $scripts ) && 'az-site-effects' === $scripts[0][0], 'preloader still loads shared script with back-to-top off' );
check( ! $styles, 'preloader alone does not load back-to-top CSS' );
$scripts = array();
$options = array( 'general' => array( 'back_to_top' => 0 ), 'footer' => array( 'back_to_top' => 1, 'back_to_top_position' => 'left' ) );
$html = output( $render );
check( false !== strpos( $html, 'az-back-to-top--left' ), 'Footer on overrides General off and renders the chosen left position' );
check( 1 === substr_count( $html, 'data-az-back-to-top' ) && false !== strpos( $html, 'aria-label="Lên đầu trang"' ), 'renders one accessible button' );
check( false !== strpos( $html, 'href="#page"' ) && false !== strpos( $html, 'stroke="currentColor"' ), 'native top link and intrinsic SVG work before JavaScript initializes' );
$effects->enqueue_assets();
check( 1 === count( $scripts ) && substr( hash_file( 'sha256', dirname( __DIR__ ) . '/js/az-site-effects.js' ), 0, 12 ) === $scripts[0][3] && true === $scripts[0][4], 'enabled button enqueues script with content hash in footer' );
check( 1 === count( $styles ) && '/theme/css/back-to-top.css' === $styles[0][1] && array() === $styles[0][2], 'button loads independent component stylesheet' );
check( substr( hash_file( 'sha256', dirname( __DIR__ ) . '/css/back-to-top.css' ), 0, 12 ) === $styles[0][3], 'component CSS version changes with content, not deployment timestamps' );
require dirname( __DIR__ ) . '/inc/core/class-performance.php';
$style_registry->add( 'az-ai-theme-style', '/theme/style.css' );
$style_registry->enqueue( array( 'az-ai-theme-style' ) );
$options['performance']['disable_theme_style'] = 1;
( new AZ_Performance() )->assets();
$style_registry->all_deps( $style_registry->queue );
check( false === $style_registry->registered['az-ai-theme-style']->src && in_array( 'az-back-to-top', $style_registry->to_do, true ), 'Performance can disable parent CSS without disabling button styling' );
$options['footer']['back_to_top_position'] = 'right';
check( false !== strpos( output( $render ), 'az-back-to-top--right' ), 'right position renders correctly' );
$options['footer']['back_to_top_position'] = '"><script>bad</script>';
check( false !== strpos( output( $render ), 'az-back-to-top--right' ) && false === strpos( output( $render ), '<script>' ), 'invalid saved position safely falls back to right' );
check( 'right' === AZ_Options_Sanitizer::sanitize( 'invalid', 'select', $position ), 'save rejects unknown positions' );
check( 'left' === AZ_Options_Sanitizer::sanitize( 'left', 'select', $position ), 'save accepts left position' );
$options['footer']['back_to_top'] = AZ_Options_Sanitizer::sanitize( $toggle['default'], 'switch', $toggle );
check( '' === output( $render ), 'Reset Footer disables button even when legacy data exists' );
$options['footer']['back_to_top_position'] = 'left';
$html = output( static function () use ( $position ) { AZ_Options_Renderer::field( 'footer', $position, $GLOBALS['options'] ); } );
check( false !== strpos( $html, '<option value="left" selected="selected"' ), 'Footer dropdown reflects saved left position' );
$general = array_column( require dirname( __DIR__ ) . '/inc/admin/sections/general.php', null, 'id' );
check( ! isset( $general['back_to_top'] ), 'Footer is the single control owner' );
$child_footer = file_get_contents( dirname( __DIR__, 2 ) . '/az-theme-child/footer.php' );
check( false === strpos( $child_footer, 'az-steel-backtop' ) && false !== strpos( $child_footer, 'wp_footer();' ), 'child footer uses shared hook without its duplicate arrow' );
check( isset( $hooks['wp_footer'][5] ), 'button rendering is hooked into parent and child footers' );
echo "$checks checks passed.\n";
