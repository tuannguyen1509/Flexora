<?php
/** CLI regression checks; uses real WordPress filesystem classes, no DB bootstrap. */
define( 'ABSPATH', dirname( __DIR__, 4 ) . '/' );
define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
define( 'WP_LANG_DIR', WP_CONTENT_DIR . '/languages' );
define( 'FS_METHOD', 'direct' );
define( 'WEEK_IN_SECONDS', 604800 );
require ABSPATH . 'wp-includes/class-wp-error.php';
require ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
require ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

$state = array( 'stylesheet' => 'az-theme', 'template' => 'az-theme', 'admin' => false, 'file_mods' => true, 'multisite' => false, 'super_admin' => false, 'capabilities' => true, 'fail_write' => '', 'fail_connect' => false, 'fail_mkdir' => false, 'options' => array(), 'css' => array(), 'transients' => array(), 'hooks' => array(), 'writes' => array(), 'css_error' => false );
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function do_action( ...$args ) {}
function apply_filters( $hook, $value, ...$args ) { return 'filesystem_method' === $hook ? 'az_test' : $value; }
function trailingslashit( $path ) { return rtrim( $path, '/\\' ) . '/'; }
function untrailingslashit( $path ) { return rtrim( $path, '/\\' ); }
function get_template() { return $GLOBALS['state']['template']; }
function get_stylesheet() { return $GLOBALS['state']['stylesheet']; }
function get_theme_root( $slug = '' ) { return $GLOBALS['fixture']; }
function get_template_directory() { return get_theme_root() . '/az-theme'; }
function get_stylesheet_directory() { return get_theme_root() . '/' . get_stylesheet(); }
function get_stylesheet_uri() { return '/themes/' . get_stylesheet() . '/style.css'; }
function is_admin() { return $GLOBALS['state']['admin']; }
function is_multisite() { return $GLOBALS['state']['multisite']; }
function is_super_admin() { return $GLOBALS['state']['super_admin']; }
function wp_is_file_mod_allowed( $context ) { return $GLOBALS['state']['file_mods']; }
function current_user_can( $capability ) { return $GLOBALS['state']['capabilities']; }
function add_action( $hook, $callback, $priority = 10 ) { $GLOBALS['state']['hooks'][] = array( $hook, $callback, $priority ); }
function set_transient( $key, $value, $ttl ) { $GLOBALS['state']['transients'][ $key ] = $value; }
function get_transient( $key ) { return $GLOBALS['state']['transients'][ $key ] ?? false; }
function delete_transient( $key ) { unset( $GLOBALS['state']['transients'][ $key ] ); }
function wp_clean_themes_cache() {}
function get_option( $key, $default = false ) { return $GLOBALS['state']['options'][ $key ] ?? $default; }
function update_option( $key, $value, $autoload = null ) { $GLOBALS['state']['options'][ $key ] = $value; }
function get_theme_mods() { return get_option( 'theme_mods_' . get_stylesheet(), array() ); }
function set_theme_mod( $key, $value ) { $mods = get_theme_mods(); $mods[ $key ] = $value; update_option( 'theme_mods_' . get_stylesheet(), $mods ); }
function wp_get_custom_css( $slug ) { return $GLOBALS['state']['css'][ $slug ] ?? ''; }
function wp_get_custom_css_post( $slug ) { return array_key_exists( $slug, $GLOBALS['state']['css'] ) ? (object) array( 'ID' => 99 ) : null; }
function wp_update_custom_css_post( $css, $args ) {
    if ( $GLOBALS['state']['css_error'] ) { return new WP_Error( 'css_failed', 'Fixture CSS error' ); }
    $GLOBALS['state']['css'][ $args['stylesheet'] ] = $css;
    set_theme_mod( 'custom_css_post_id', 99 );
    return (object) array( 'ID' => 99 );
}
function wp_enqueue_style( ...$args ) { $GLOBALS['enqueued'] = $args; }
function esc_html__( $text, $domain ) { return $text; }
function __( $text, $domain = '' ) { return $text; }
function mbstring_binary_safe_encoding( $reset = false ) {}
function reset_mbstring_encoding() {}
function wp_die( $message ) { throw new RuntimeException( $message ); }
function check_admin_referer( $action ) { throw new RuntimeException( 'Nonce checked: ' . $action ); }

/** Lightweight theme-header double; filesystem operations below are core code. */
class AZ_Test_Theme {
    private $path;
    public function __construct( $path ) { $this->path = $path; }
    public function exists() { return is_file( $this->path . '/style.css' ); }
    public function errors() { return ! $this->exists() || ! $this->get( 'Template' ); }
    public function get( $header ) {
        if ( ! $this->exists() ) { return ''; }
        preg_match( '/^' . preg_quote( $header, '/' ) . ':\s*(.+)$/m', file_get_contents( $this->path . '/style.css' ), $match );
        return trim( $match[1] ?? '' );
    }
}
function wp_get_theme( $slug, $root = '' ) { return new AZ_Test_Theme( ( $root ?: get_theme_root() ) . '/' . $slug ); }
class WP_Filesystem_az_test extends WP_Filesystem_Direct {
    public function connect() { return ! $GLOBALS['state']['fail_connect']; }
    public function mkdir( $path, $mode = false, $owner = false, $group = false ) {
        return ! $GLOBALS['state']['fail_mkdir'] && parent::mkdir( $path, $mode, $owner, $group );
    }
    public function put_contents( $path, $contents, $mode = false ) {
        $GLOBALS['state']['writes'][] = basename( $path );
        if ( basename( $path ) === $GLOBALS['state']['fail_write'] ) {
            parent::put_contents( $path, 'partial write', $mode );
            return false;
        }
        return parent::put_contents( $path, $contents, $mode );
    }
}
function check( $condition, $message ) {
    if ( ! $condition ) { throw new RuntimeException( $message ); }
    echo "PASS $message\n";
}

$fixture = sys_get_temp_dir() . '/az-child-tests-' . bin2hex( random_bytes( 6 ) );
$source = $fixture . '/az-theme/inc/admin/templates/child-theme';
mkdir( $source, 0755, true );
foreach ( array( 'functions.php', 'README.md', 'style.css' ) as $file ) {
    copy( dirname( __DIR__ ) . '/inc/admin/templates/child-theme/' . $file, $source . '/' . $file );
}
require dirname( __DIR__ ) . '/inc/admin/class-child-theme-installer.php';
$installer = new AZ_Child_Theme_Installer();
$child = AZ_Child_Theme_Installer::child_theme_path();
try {
    check( count( $state['hooks'] ) === 1 && 'after_switch_theme' === $state['hooks'][0][0], 'activation hook registered on public requests without admin UI hooks' );
    $state['capabilities'] = false;
    $installer->install_after_parent_activation();
    check( get_transient( AZ_Child_Theme_Installer::NOTICE_KEY ) === 'created', 'parent activation automatically creates child even when next request is anonymous' );
    check( AZ_Child_Theme_Installer::child_theme_exists(), 'generated child declares Template: az-theme' );
    check( $state['writes'] === array( 'functions.php', 'README.md', 'style.css' ), 'all starter files written, stylesheet published last' );
    check( get_stylesheet() === 'az-theme', 'creating child leaves parent active until child activation' );
    file_put_contents( $child . '/functions.php', '<?php /* website-specific code */' );
    file_put_contents( $child . '/custom.php', 'keep this layout' );
    $hash = hash_file( 'sha256', $child . '/functions.php' );
    check( $installer->install_child_theme() === 'exists' && hash_file( 'sha256', $child . '/functions.php' ) === $hash && file_get_contents( $child . '/custom.php' ) === 'keep this layout', 'repeat activation preserves every existing child file' );
    $state['stylesheet'] = 'az-theme-child';
    $installer->install_after_parent_activation();
    check( $installer->install_child_theme()->get_error_code() === 'parent_inactive', 'no installer writes when child is active' );
    $state['stylesheet'] = 'az-theme';
    $wp_filesystem->delete( $child, true );
    mkdir( $child );
    file_put_contents( $child . '/functions.php', 'existing incomplete child' );
    check( $installer->install_child_theme()->get_error_code() === 'child_theme_conflict' && file_get_contents( $child . '/functions.php' ) === 'existing incomplete child', 'incomplete existing directory is never repaired or overwritten' );
    file_put_contents( $child . '/style.css', "Theme Name: Other\nTemplate: another-parent\n" );
    check( $installer->install_child_theme()->get_error_code() === 'child_theme_conflict', 'unrelated theme using the child slug is not treated as a valid child' );
    $wp_filesystem->delete( $child, true );
    $state['file_mods'] = false;
    check( $installer->install_child_theme()->get_error_code() === 'file_mods_disabled' && ! file_exists( $child ), 'DISALLOW_FILE_MODS respected' );
    $state['file_mods'] = true;
    $state['multisite'] = true;
    check( $installer->install_child_theme()->get_error_code() === 'file_mods_disabled', 'multisite tenant cannot create shared theme files' );
    $state['multisite'] = false;
    $state['fail_connect'] = true;
    check( $installer->install_child_theme()->get_error_code() === 'filesystem_error' && ! file_exists( $child ), 'filesystem connection failure leaves no child directory' );
    $state['fail_connect'] = false;
    $state['fail_mkdir'] = true;
    check( $installer->install_child_theme()->get_error_code() === 'filesystem_error' && ! file_exists( $child ), 'directory creation failure handled' );
    $state['fail_mkdir'] = false;
    unlink( $source . '/README.md' );
    check( $installer->install_child_theme()->get_error_code() === 'source_error' && ! file_exists( $child ), 'missing starter file detected before any child writes' );
    copy( dirname( __DIR__ ) . '/inc/admin/templates/child-theme/README.md', $source . '/README.md' );
    foreach ( array( 'functions.php', 'README.md', 'style.css' ) as $file ) {
        $state['fail_write'] = $file;
        check( $installer->install_child_theme()->get_error_code() === 'filesystem_error' && ! file_exists( $child ), 'failed/partial ' . $file . ' write rolled back for safe retry' );
    }
    $state['fail_write'] = '';
    check( $installer->install_child_theme() === 'created', 'retry succeeds after filesystem failure' );
    try { $installer->create_child_theme(); throw new LogicException( 'Permission check skipped' ); }
    catch ( RuntimeException $error ) { check( strpos( $error->getMessage(), 'quyền' ) !== false, 'manual installation rejects insufficient capabilities' ); }
    $state['capabilities'] = true;
    try { $installer->create_child_theme(); throw new LogicException( 'Nonce check skipped' ); }
    catch ( RuntimeException $error ) { check( $error->getMessage() === 'Nonce checked: az_create_child_theme', 'manual installation checks nonce before mutation' ); }

    $state['stylesheet'] = 'az-theme-child';
    require $child . '/functions.php';
    $state['options']['theme_mods_az-theme'] = array( 'custom_logo' => 12, 'nav_menu_locations' => array( 'primary' => 3 ), 'color' => 'blue', 'custom_css_post_id' => 55 );
    $state['options']['theme_mods_az-theme-child'] = array( 'custom_logo' => 88, 'color' => '', 'enabled' => false );
    $state['css']['az-theme'] = '.site { color: red; }';
    az_child_migrate_parent_theme_mods();
    $mods = get_theme_mods();
    check( $mods['custom_logo'] === 88 && $mods['color'] === '' && $mods['enabled'] === false, 'migration preserves child logo and explicit empty/false values' );
    check( $mods['nav_menu_locations'] === array( 'primary' => 3 ), 'missing menu assignments copied from parent' );
    check( $mods['custom_css_post_id'] === 99 && $state['css']['az-theme-child'] === $state['css']['az-theme'], 'Additional CSS copied into a separate child post' );
    $state['options']['theme_mods_az-theme']['later'] = 'do not copy';
    az_child_migrate_parent_theme_mods();
    check( ! isset( get_theme_mods()['later'] ), 'migration runs only once, parent changes do not overwrite child settings' );
    unset( $state['options']['az_child_parent_mods_migrated'] );
    $state['css']['az-theme-child'] = '';
    az_child_migrate_parent_theme_mods();
    check( $state['css']['az-theme-child'] === '', 'existing child CSS post preserved even when empty' );
    unset( $state['options']['az_child_parent_mods_migrated'], $state['css']['az-theme-child'] );
    $state['css_error'] = true;
    az_child_migrate_parent_theme_mods();
    check( ! get_option( 'az_child_parent_mods_migrated' ), 'CSS save failure does not mark migration complete' );
    $state['css_error'] = false;
    az_child_enqueue_assets();
    check( $enqueued[0] === 'az-theme-child' && $enqueued[2] === array( 'az-container-sync' ) && $enqueued[3] === filemtime( $child . '/style.css' ), 'child CSS uses parent dependency and cache-busting version' );
    check( in_array( array( 'wp_enqueue_scripts', 'az_child_enqueue_assets', 100 ), $state['hooks'], true ), 'child CSS enqueued after parent homepage/module assets' );
} finally {
    // Only the randomized test-owned temporary fixture is removed.
    $cleanup = new WP_Filesystem_Direct( false );
    $cleanup->delete( $fixture, true );
}
