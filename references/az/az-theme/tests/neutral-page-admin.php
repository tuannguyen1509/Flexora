<?php
/** About editor removal: normal parent include path, no WordPress/database bootstrap. */
define( 'ABSPATH', __DIR__ );
$hooks = array(); $boxes = array(); $writes = array(); $assets = array();
$metadata = array( '_az_gioi_thieu_data' => '{"intro":{"title":"Keep old intro"},"unknown":false}', 'post_content' => '<p>Keep editor content.</p>' );
function get_template_directory() { return dirname( __DIR__ ); }
function add_action( $hook, $callback, $priority = 10, $args = 1 ) { $GLOBALS['hooks'][ $hook ][] = $callback; }
function add_meta_box( $id, ...$args ) { $GLOBALS['boxes'][] = $id; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['writes'][] = array( $id, $key, $value ); $GLOBALS['metadata'][ $key ] = $value; }
function wp_enqueue_media() { $GLOBALS['assets'][] = 'media'; }
function wp_enqueue_editor() { $GLOBALS['assets'][] = 'editor'; }
function az_test_editor_check( $ok, $label ) { if ( ! $ok ) { throw new RuntimeException( $label ); } echo "PASS $label\n"; }
require dirname( __DIR__ ) . '/inc/functions/customizer.php';
$before = $metadata;
$post = (object) array( 'ID' => 7, 'post_type' => 'page', 'post_name' => 'gioi-thieu' );
$_POST = array( 'az_gioi_thieu_nonce' => 'valid', 'az_gt_intro_title' => 'Stale form', 'az_gt_intro_paragraphs' => array( 'Stale text' ) );
foreach ( array( 'page-about.php', 'page-neutral.php', '' ) as $template ) {
    $post->page_template = $template;
    $boxes = array();
    foreach ( $hooks['add_meta_boxes_page'] ?? array() as $callback ) { $callback( $post ); }
    az_test_editor_check( array() === $boxes, 'no legacy About metabox for template: ' . $template );
    foreach ( $hooks['save_post_page'] ?? array() as $callback ) { $callback( $post->ID, $post ); }
    az_test_editor_check( array() === $writes && $before === $metadata, 'stale About fields cannot rewrite editor or legacy metadata: ' . $template );
}
ob_start();
foreach ( $hooks['admin_head-post.php'] ?? array() as $callback ) { $callback(); }
az_test_editor_check( '' === ob_get_clean(), 'About module adds no styles hiding the normal WordPress editor' );
foreach ( $hooks['admin_enqueue_scripts'] ?? array() as $callback ) { $callback( 'post.php' ); }
az_test_editor_check( array() === $assets, 'removed About module no longer enqueues its extra editor/media assets' );
az_test_editor_check( isset( $hooks['wp_head'], $hooks['customize_preview_init'] ), 'unrelated customizer logo and preview hooks remain registered' );
az_test_editor_check( ! function_exists( 'az_gioi_thieu_meta_box' ) && ! function_exists( 'az_gioi_thieu_get_data' ), 'retired About form and defaults are no longer loaded' );
echo "About editor cleanup regressions passed.\n";
