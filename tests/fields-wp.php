<?php
/** Real WordPress primitives, option adapter and test-only fixtures on primary development site. */
if ( 'http://localhost:8090' !== get_option( 'siteurl' ) || 'flexora-child-starter' !== get_stylesheet() ) { WP_CLI::error( 'Primary development Child site required.' ); }
$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) ); wp_set_current_user( $admins[0] );
$checks = 0;
$check = function ( $ok, $message ) use ( &$checks ) { if ( ! $ok ) { throw new RuntimeException( $message ); } $checks++; };
$types = new FLX\Fields\TypeRegistry(); FLX\Fields\BuiltinTypes::register( $types ); $engine = new FLX\Fields\Engine( $types );
$fields = array( 'rich' => array( 'type' => 'editor' ), 'image' => array( 'type' => 'media' ), 'gallery' => array( 'type' => 'gallery' ), 'toggle' => array( 'type' => 'toggle' ) );
$url = $engine->patch( array( 'url' => array( 'type' => 'url' ) ), array(), array( 'url' => 'https://example.org/details' ) );
$check( 'https://example.org/details' === $url['values']['url'], 'native URL sanitizer does not receive schema as protocols' );
$r = $engine->patch( $fields, array(), array( 'rich' => '<p><strong>Rich</strong> <a href="https://example.org">link</a></p><ul><li>List</li></ul><script>alert(1)</script><img src=x onerror="alert(2)">' ) );
$check( array() === $r['errors'], 'editor validates' );
foreach ( array( '<strong>Rich</strong>', '<ul>', '<a href="https://example.org">' ) as $html ) { $check( false !== strpos( $r['values']['rich'], $html ), 'real KSES retains formatting' ); }
$check( false === strpos( $r['values']['rich'], '<script' ) && false === strpos( $r['values']['rich'], 'onerror' ), 'real KSES strips executable HTML' );
$images = array();
foreach ( array( 'one', 'two' ) as $name ) {
	$found = get_posts( array( 'post_type' => 'attachment', 'name' => 'flx-field-test-' . $name, 'posts_per_page' => 1, 'fields' => 'ids' ) );
	if ( $found ) { $images[] = $found[0]; continue; }
	$upload = wp_upload_bits( 'flx-field-test-' . $name . '.png', null, base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jI0YAAAAASUVORK5CYII=' ) );
	if ( $upload['error'] ) { throw new RuntimeException( 'Fixture upload failed.' ); }
	$id = wp_insert_attachment( array( 'post_title' => 'FLX Field Test ' . $name, 'post_name' => 'flx-field-test-' . $name, 'post_mime_type' => 'image/png', 'post_status' => 'inherit' ), $upload['file'] );
	$images[] = $id;
}
$check( array() === $engine->patch( $fields, array(), array( 'image' => $images[0], 'gallery' => $images ) )['errors'], 'real attachment validation' );
$check( isset( $engine->patch( $fields, array(), array( 'image' => 999999999 ) )['errors']['image'] ), 'invalid attachment rejected' );
$check( isset( $engine->patch( $fields, array(), array( 'gallery' => array( $images[0], $images[0] ) ) )['errors']['gallery'] ), 'duplicate gallery rejected' );
$before = get_option( 'flx_settings', null ); $store = new FLX\Data\SettingsStore();
try {
	delete_option( 'flx_settings' );
	$check( array() === $store->read() && null === get_option( 'flx_settings', null ), 'read does not seed defaults' );
	$check( $store->save( 'test', array( 'value' => false ), 0 ), 'first save' );
	$check( ! $store->save( 'test', array( 'value' => true ), 0 ), 'stale revision rejected' );
	$check( $store->save( 'other', array( 'value' => 0 ), 1 ), 'partial other section save' );
	$check( false === $store->read()['test']['value'] && 0 === $store->read()['other']['value'], 'false and zero preserved across sections' );
} finally { delete_option( 'flx_settings' ); if ( null !== $before ) { add_option( 'flx_settings', $before, '', false ); } }
$user = get_user_by( 'login', 'flx_field_subscriber' );
$password = wp_generate_password( 28, true, false );
if ( ! $user ) { $user_id = wp_insert_user( array( 'user_login' => 'flx_field_subscriber', 'user_pass' => $password, 'user_email' => 'flx-fields@localhost.invalid', 'role' => 'subscriber' ) ); } else { $user_id = $user->ID; wp_set_password( $password, $user_id ); }
if ( is_wp_error( $user_id ) ) { throw new RuntimeException( 'Cannot create security fixture.' ); }
wp_set_current_user( $user_id ); $check( ! current_user_can( 'manage_options' ), 'subscriber lacks settings capability' );
$check( false === wp_verify_nonce( 'invalid', 'flx-settings-general' ), 'invalid nonce rejected by real WordPress' );
echo wp_json_encode( array( 'assertions' => $checks, 'images' => $images, 'subscriber' => array( 'username' => 'flx_field_subscriber', 'password' => $password ) ) );
