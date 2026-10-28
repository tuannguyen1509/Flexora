<?php
define( 'ABSPATH', '/' );
function add_action( ...$args ) {}
function add_filter( ...$args ) {}
function az_get_option( $key, $default ) { return $default; }
function wp_attachment_is_image( $id ) { return 1 === $id; }
$relative = 'az-nonexistent-test/image.png'; $external = false;
function get_post_meta( ...$args ) { return $GLOBALS['relative']; }
function wp_get_upload_dir() { return array( 'basedir' => '/private/tmp', 'baseurl' => 'https://test.example/uploads' ); }
function get_attached_file( $id ) { return '/private/tmp/' . $GLOBALS['relative']; }
function wp_get_attachment_url( $id ) { return $GLOBALS['external'] ? 'https://cdn.example/image.png' : 'https://test.example/uploads/' . $GLOBALS['relative']; }
function wp_normalize_path( $path ) { return $path; }
function trailingslashit( $path ) { return rtrim( $path, '/' ) . '/'; }
function apply_filters( $name, $value, ...$args ) { return $value; }
function check( $ok, $label ) { if ( ! $ok ) { throw new RuntimeException( $label ); } echo "PASS $label\n"; }
require dirname( __DIR__ ) . '/inc/core/media-tools.php';
$GLOBALS['title'] = null;
az_missing_media_admin_title();
check( is_string( $GLOBALS['title'] ) && '' !== $GLOBALS['title'], 'hidden admin page has non-null title before admin header' );
check( ! az_missing_local_image( 2 ), 'non-images excluded' );
check( az_missing_local_image( 1 ), 'missing local primary image detected' );
$external = true;
check( ! az_missing_local_image( 1 ), 'external CDN excluded' );
$external = false; $relative = '../image.png';
check( ! az_missing_local_image( 1 ), 'path traversal excluded' );
$relative = 'https://example.com/image.png';
check( ! az_missing_local_image( 1 ), 'remote metadata excluded' );
