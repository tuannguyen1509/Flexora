<?php
/** Partner save/render checks, without changing a WordPress database. */
define( 'ABSPATH', __DIR__ );
$theme = dirname( __DIR__ );
$metadata = array( 'reference' => array( 'category_title' => 'Keep categories' ), 'video' => array( 'items' => array( 'Keep video' ) ) );
$nonce_valid = true; $can_edit = true; $revision = false;
$attachments = array( 11 => 'https://example.test/logo-11.png', 12 => 'https://example.test/logo-12.png' );
function add_action( ...$args ) {}
function absint( $value ) { return abs( (int) $value ); }
function get_queried_object_id() { return 1; }
function get_option( $key, $fallback = false ) { return 'page_on_front' === $key ? 1 : $fallback; }
function get_post_meta( ...$args ) { return $GLOBALS['metadata']; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['metadata'] = $value; }
function wp_parse_args( $saved, $defaults ) { return array_merge( $defaults, $saved ); }
function wp_unslash( $value ) { return $value; }
function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function esc_attr( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $value ) { return esc_attr( $value ); }
function esc_url_raw( $value ) { return preg_match( '#^https?://#', $value ) ? $value : ''; }
function esc_url( $value ) { return esc_attr( $value ); }
function wp_verify_nonce( ...$args ) { return $GLOBALS['nonce_valid']; }
function current_user_can( ...$args ) { return $GLOBALS['can_edit']; }
function wp_is_post_revision( ...$args ) { return $GLOBALS['revision']; }
function get_stylesheet_directory() { return $GLOBALS['theme']; }
function get_term_by( ...$args ) { return false; }
function wp_attachment_is_image( $id ) { return isset( $GLOBALS['attachments'][ $id ] ); }
function wp_get_attachment_url( $id ) { return $GLOBALS['attachments'][ $id ] ?? false; }
function wp_get_attachment_image_url( $id, $size ) { return wp_get_attachment_url( $id ); }
function attachment_url_to_postid( $url ) { return array_search( $url, $GLOBALS['attachments'], true ) ?: 0; }
function wp_get_attachment_image( $id, $size, $icon, $attrs ) { return '<img src="' . esc_url( wp_get_attachment_url( $id ) ) . '" alt="Media alt" loading="lazy" decoding="async">'; }
function checked( $value, $expected ) { if ( $value == $expected ) { echo 'checked'; } }
require $theme . '/inc/modules/homepage/page-fields.php';
require $theme . '/inc/home-partners.php';
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } echo "PASS $message\n"; }
function capture( $callback ) { ob_start(); $callback(); return ob_get_clean(); }
$post = (object) array( 'ID' => 1, 'post_type' => 'page', 'post_name' => 'trang-chu' );
check( 'ĐỐI TÁC CỦA CHÚNG TÔI' === az_child_home_partners_data()['title'], 'existing homepage receives the partner heading by default' );
$metadata['partners'] = array( 'enabled' => 1, 'items' => array( array( 'name' => 'Legacy name', 'image' => '/old-logo.svg', 'url' => 'https://old-link.test' ) ) );
check( az_child_home_partners_data()['items'] === array( array( 'id' => 0, 'image' => '/old-logo.svg' ) ), 'legacy images remain available without name or link' );
$html = capture( function() { az_child_home_partners_render(); } );
check( strpos( $html, '/old-logo.svg' ) !== false && strpos( $html, '<a ' ) === false && strpos( $html, 'Legacy name' ) === false, 'legacy render includes only logos' );
$admin = capture( function() { az_child_home_partners_admin( 1 ); } );
check( strpos( $admin, 'az_home_partner_name' ) === false && strpos( $admin, 'az_home_partner_url' ) === false && strpos( $admin, 'data-home-partner-add' ) !== false, 'gallery replaces name and link fields' );
check( strpos( $admin, 'role="switch"' ) !== false && strpos( $admin, 'az-home-partners-toggle__track' ) !== false, 'visibility uses an accessible compact switch' );
$_POST = array( 'az_home_partners_present' => '1', 'az_home_page_nonce' => 'valid', 'az_home_partners_enabled' => '1', 'az_home_partners_title' => '<b>Đối tác của chúng tôi</b>', 'az_home_partner_id' => array( 12, 11, 12, 99, 0, 0, 0 ), 'az_home_partner_image' => array( '/untrusted.png', '/untrusted.png', '/duplicate.png', '/not-image.png', '/old-logo.svg', '', 'javascript:alert(1)' ) );
az_child_home_partners_save( 1, $post );
check( $metadata['reference']['category_title'] === 'Keep categories' && $metadata['video']['items'][0] === 'Keep video', 'saving gallery preserves other homepage settings' );
check( 'Đối tác của chúng tôi' === $metadata['partners']['title'] && 3 === count( $metadata['partners']['items'] ), 'save strips heading markup, skips duplicate and invalid images' );
check( array_column( $metadata['partners']['items'], 'id' ) === array( 12, 11, 0 ), 'gallery order and legacy images are preserved' );
check( $metadata['partners']['items'][0]['image'] === $attachments[12], 'attachment ID supplies the trusted Media URL' );
$html = capture( function() { az_child_home_partners_render(); } );
check( 3 === substr_count( $html, '<img ' ) && strpos( $html, '<a ' ) === false, 'gallery renders images without links' );
check( strpos( $html, 'alt="Media alt"' ) !== false && strpos( $html, 'alt="Logo đối tác"' ) !== false, 'Media alt text and legacy fallback are retained' );
check( strpos( $html, 'aria-labelledby="az-home-partners-title"' ) !== false && strpos( $html, 'loading="lazy"' ) !== false, 'section is labelled and offscreen logos load lazily' );
$_POST['az_home_partner_id'] = array(0);
$_POST['az_home_partner_image'] = array($attachments[11]);
az_child_home_partners_save( 1, $post );
check( 11 === $metadata['partners']['items'][0]['id'], 'legacy Media URL resolves to an attachment on save' );
$_POST['az_home_partner_id'] = array();
$_POST['az_home_partner_image'] = array();
az_child_home_partners_save( 1, $post );
check( array() === az_child_home_partners_data()['items'], 'removing all partners does not resurrect old rows' );
$_POST['az_home_partners_enabled'] = '0';
az_child_home_partners_save( 1, $post );
check( '' === capture( function() { az_child_home_partners_render(); } ), 'disabled partner section renders nothing' );
$before = $metadata;
$_POST['az_home_partners_title'] = 'Must not save';
foreach ( array( 'nonce_valid', 'can_edit' ) as $flag ) {
    $GLOBALS[$flag] = false; az_child_home_partners_save( 1, $post );
    check( $before === $metadata, 'save rejects ' . $flag ); $GLOBALS[$flag] = true;
}
$revision = true; az_child_home_partners_save( 1, $post ); $revision = false;
check( $before === $metadata, 'revision save preserves partner settings' );
unset( $_POST['az_home_partners_present'] ); az_child_home_partners_save( 1, $post );
check( $before === $metadata, 'missing editor fields never reset partner settings' );
$footer = file_get_contents( $theme . '/template-parts/footer-reference.php' );
check( strpos( $footer, 'az_child_home_partners_render()' ) !== false && strpos( $footer, 'az-home-social-strip' ) === false, 'homepage replaces social strip with partner component' );
