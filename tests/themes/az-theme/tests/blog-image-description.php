<?php
define( 'ABSPATH', '/' );
$enabled = false; $alt = ''; $writes = array();
$parent = (object) array( 'post_type' => 'post', 'post_status' => 'draft', 'post_title' => 'Bài viết thử' );
function add_action( ...$args ) {}
function add_filter( ...$args ) {}
function az_get_option( ...$args ) { return $GLOBALS['enabled']; }
function wp_attachment_is_image( $id ) { return 1 === $id; }
function get_post( $id ) { return $GLOBALS['parent']; }
function get_attached_file( $id ) { return '/uploads/photo.jpg'; }
function sanitize_text_field( $value ) { return trim( strip_tags( $value ) ); }
function wp_slash( $value ) { return $value; }
function wp_unslash( $value ) { return $value; }
function has_blocks( $content ) { return false !== strpos( $content, '<!-- wp:' ); }
function absint( $value ) { return abs( (int) $value ); }
function current_user_can( ...$args ) { return true; }
function get_post_meta( ...$args ) { return $GLOBALS['alt']; }
function update_post_meta( ...$args ) { $GLOBALS['writes']['alt'] = $args[2]; }
function wp_update_post( $value ) { $GLOBALS['writes']['description'] = $value['post_content']; }
function check( $ok, $label ) { if ( ! $ok ) { throw new RuntimeException( $label ); } echo "PASS $label\n"; }
require dirname( __DIR__ ) . '/inc/core/blog-image-description.php';
$image = (object) array( 'post_type' => 'attachment', 'post_parent' => 2, 'post_content' => '', 'post_title' => 'Custom title' );
az_blog_image_description( 1, $image, false ); check( ! $writes, 'disabled by default' );
$enabled = true; az_blog_image_description( 1, $image, false );
check( $writes === array( 'alt' => 'Bài viết thử', 'description' => 'Bài viết thử' ), 'fills empty ALT and description' );
$writes = array(); $alt = 'Custom ALT'; $image->post_content = 'Custom description';
az_blog_image_description( 1, $image, false ); check( ! $writes, 'preserves custom descriptions' );
$alt = ''; $image->post_content = ''; az_blog_image_description( 1, $image, true ); check( isset( $writes['alt'], $writes['description'] ), 'empty descriptions filled when existing attachment is saved' );
$writes = array();
$image->post_parent = 0; az_blog_image_description( 1, $image, false ); check( ! $writes, 'unattached uploads excluded' );
$image->post_parent = 2; $parent->post_status = 'auto-draft'; az_blog_image_description( 1, $image, false ); check( ! $writes, 'auto-draft placeholder title excluded' );

function is_admin() { return false; }
function is_singular( $type ) { return 'post' === $type; }
function in_the_loop() { return true; }
function is_main_query() { return true; }
function get_the_title() { return 'Bài viết thử'; }
function esc_attr( $text ) { return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' ); }
function wp_kses_uri_attributes() { return array( 'src', 'href' ); }
$core = dirname( __DIR__, 4 ) . '/wp-includes/html-api/';
require_once dirname( __DIR__, 4 ) . '/wp-includes/utf8.php';
foreach ( array( 'class-wp-html-attribute-token.php', 'class-wp-html-span.php', 'class-wp-html-text-replacement.php', 'class-wp-html-decoder.php', 'class-wp-html-tag-processor.php' ) as $file ) { require_once $core . $file; }
$result = az_blog_content_image_descriptions( '<p><img src="old.jpg" alt=""><img src="a.jpg" alt="Custom"><img role="presentation" src="decorative.jpg"></p>' );
check( false !== strpos( $result, 'alt="Bài viết thử"' ) && false !== strpos( $result, 'title="Bài viết thử"' ), 'existing content images get ALT and tooltip' );
check( false !== strpos( $result, 'alt="Custom"' ), 'existing content ALT preserved' );
check( false !== strpos( $result, '<img role="presentation" src="decorative.jpg">' ), 'decorative images untouched' );
$enabled = false;
check( '<img src="old.jpg">' === az_blog_content_image_descriptions( '<img src="old.jpg">' ), 'disabled leaves frontend unchanged' );
$enabled = true; $parent->post_status = 'draft'; $_REQUEST['post_id'] = 2;
check( false !== strpos( az_blog_insert_image_title( '<img src="new.jpg">' ), 'title="Bài viết thử"' ), 'Classic insert HTML gets title immediately' );
$data = array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Bài viết thử', 'post_content' => '<img src="old.jpg"><img src="new.jpg" title="Manual">' );
$saved = az_blog_save_image_titles( $data, array() );
check( false !== strpos( $saved['post_content'], 'title="Bài viết thử"' ), 'saving persists missing image titles' );
check( false !== strpos( $saved['post_content'], 'title="Manual"' ), 'saving preserves manual titles' );
$data['post_content'] = '<!-- wp:image --><figure><img src="new.jpg"></figure><!-- /wp:image -->';
check( $data === az_blog_save_image_titles( $data, array() ), 'Gutenberg saved markup remains byte-identical' );
