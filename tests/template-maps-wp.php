<?php
if ( 'http://localhost:8090' !== get_option( 'siteurl' ) || 'flexora-child-starter' !== get_stylesheet() ) { WP_CLI::error( 'Primary Child development site required.' ); }
$checks = 0; $ok = function ( $condition, $message ) use ( &$checks ) { if ( ! $condition ) { throw new RuntimeException( $message ); } $checks++; };
$admin = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) ); wp_set_current_user( $admin[0] );
$engine = FLX\Fields\Runtime::engine(); $registry = FLX\Templates\Runtime::registry(); $storage = new FLX\Data\PostMetaStorage();
$resolver = new FLX\Templates\Resolver( $registry, $engine, $storage ); $handler = new FLX\Templates\PageAdmin( $registry, $resolver, $engine, $storage );
$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'FLX API integration fixture', 'page_template' => 'page-templates/flx-example.php' ) );
$other = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'draft', 'post_title' => 'FLX security fixture' ) );
try {
	$templates = wp_get_theme()->get_page_templates(); $ok( isset( $templates['page-templates/flx-example.php'] ), 'WP Child template discovery' );
	$ok( 'example' === flx_template( array( 'post_id' => $id ) )->id, 'public descriptor' );
	$ok( array() === $storage->read( $id ), 'initial missing storage' );
	$ok( 'fallback' === flx_data( 'hero.title', 'fallback', array( 'post_id' => $id ) ), 'missing fallback' );
	$ok( ! metadata_exists( 'post', $id, FLX\Data\PostMetaStorage::KEY ), 'read no default write' );
	$map = $registry->get( 'example' );
	$values = array( 'hero.title' => 'A title', 'hero.description' => '<strong>Rich</strong>', 'future.branch' => array( 'keep' => true ) );
	$ok( $storage->save( $id, $map, $values, 0 ), 'write map A' );
	$ok( ! $storage->save( $id, $map, array(), 0 ), 'stale revision rejected' );
	$ok( 'A title' === flx_data( 'hero.title', '', array( 'post_id' => $id ) ), 'API field' );
	$ok( 'A title' === flx_data( 'hero', array(), array( 'post_id' => $id ) )['title'], 'API group' );
	$root = $storage->read( $id ); $ok( 1 === $root['maps']['example']['version'], 'persist schema version' );
	foreach ( array( '', false, 0, array(), null ) as $value ) {
		$values['hero.title'] = $value; $root = $storage->read( $id ); $ok( $storage->save( $id, $map, $values, $root['revision'] ), 'presence write' ); FLX\Templates\Runtime::resolver()->clear( $id );
		$ok( $value === flx_data( 'hero.title', 'WRONG', array( 'post_id' => $id ) ), 'falsey does not trigger fallback' );
	}
	$root = $storage->read( $id ); $values['hero.title'] = 'A retained'; $storage->save( $id, $map, $values, $root['revision'] );
	update_post_meta( $id, '_wp_page_template', 'page-templates/flx-example-alt.php' ); FLX\Templates\Runtime::resolver()->clear( $id );
	$ok( 'missing B' === flx_data( 'hero.title', 'missing B', array( 'post_id' => $id ) ), 'A not merged into B' );
	$root = $storage->read( $id ); $ok( $storage->save( $id, $registry->get( 'example-alt' ), array( 'hero.title' => 'B independent' ), $root['revision'] ), 'write B' );
	update_post_meta( $id, '_wp_page_template', 'page-templates/flx-example.php' ); FLX\Templates\Runtime::resolver()->clear( $id );
	$ok( 'A retained' === flx_data( 'hero.title', '', array( 'post_id' => $id ) ), 'A to B to A retained' );
	$root = $storage->read( $id ); $ok( 'B independent' === $root['maps']['example-alt']['data']['hero.title'], 'B retained too' );
	$request = array( 'flx_map_nonce' => wp_create_nonce( 'flx-map-' . $id ), 'flx_map_id' => 'example', 'flx_map_version' => '1', 'flx_map_revision' => (string) $root['revision'], 'flx_map_complete' => '1', 'flx_map_values' => array( 'hero.button_text' => 'Button' ) );
	$ok( isset( $handler->save( $id, $request )['saved'] ), 'validated partial save' );
	$root = $storage->read( $id ); $ok( 'A retained' === $root['maps']['example']['data']['hero.title'], 'missing group/field preserved' );
	$ok( true === $root['maps']['example']['data']['future.branch']['keep'], 'unknown stored branch preserved' );
	$before = $root;
	foreach ( array( array( 'flx_map_nonce' => 'bad' ), array( 'flx_map_values' => array( 'intruder' => 'x' ) ), array( 'flx_map_values' => array( 'features.items' => array( array( '_id' => 'bad' ) ) ) ), array( 'flx_map_version' => '99' ), array( 'flx_map_complete' => '0' ) ) as $patch ) {
		$bad = array_replace( $request, $patch ); $bad['flx_map_revision'] = (string) $root['revision'];
		$ok( ! empty( $handler->save( $id, $bad )['errors'] ), 'invalid request rejected' ); $ok( $before === $storage->read( $id ), 'rejection did not write' );
	}
	$bad = $request; $bad['flx_map_id'] = 'forged'; $ok( isset( $handler->save( $id, $bad )['skipped'] ), 'untrusted map ID not selected' );
	$ok( isset( $handler->save( $id, $request, true )['skipped'] ) && $before === $storage->read( $id ), 'autosave no corruption' );
	require_once ABSPATH . 'wp-admin/includes/post.php';
	$autosave = wp_create_post_autosave( array( 'post_ID' => $id, 'post_type' => 'page', 'post_title' => 'FLX autosaved title', 'post_content' => 'Autosave content' ) );
	$ok( is_int( $autosave ) && $id === wp_is_post_autosave( $autosave ), 'actual WP autosave revision created' );
	$ok( isset( $handler->save( $autosave, $request )['skipped'] ) && $before === $storage->read( $id ), 'actual autosave leaves map payload intact' );
	$ok( isset( $handler->save( $other, $request )['errors'] ), 'wrong post type rejected' );
	wp_set_current_user( 0 ); $ok( isset( $handler->save( $id, $request )['errors'] ), 'wrong capability rejected' ); wp_set_current_user( $admin[0] );
	$revision = wp_save_post_revision( $id );
	$ok( is_int( $revision ) && $revision > 0, 'native page revision created' );
	$ok( isset( $handler->save( $revision, $request )['skipped'] ), 'revision destination skipped' );
	wp_restore_post_revision( $revision ); $ok( $before === $storage->read( $id ), 'native revision restore leaves unregistered map meta intact' );
	$root = $storage->read( $id ); $root['maps']['example']['version'] = 0; $root['maps']['retired-map'] = array( 'version' => 1, 'data' => array( 'old' => 'safe' ) ); update_post_meta( $id, FLX\Data\PostMetaStorage::KEY, $root ); $resolver->clear( $id ); $resolver->payload( $id );
	$codes = array_column( $resolver->diagnostics(), 'code' ); $ok( in_array( 'template_schema_version_mismatch', $codes, true ), 'version diagnostic' ); $ok( in_array( 'template_unknown_map', $codes, true ), 'unknown map diagnostic' ); $ok( $root === $storage->read( $id ), 'diagnostic read did not rewrite storage' );
	update_post_meta( $id, '_wp_page_template', 'default' ); $ok( null === $resolver->map( $id ), 'default template no mapped panel' ); $ok( $root === $storage->read( $id ), 'default selection retains maps' );
	$synthetic = new FLX\Templates\MapRegistry( $engine ); $descriptor = $map; $descriptor['id'] = 'defaults'; $descriptor['groups'] = array( array( 'id' => 'hero', 'label' => 'Hero', 'fields' => array( 'title' => array( 'type' => 'text', 'default' => 'Schema default' ) ) ) ); $synthetic->register( $descriptor ); update_post_meta( $id, '_wp_page_template', $map['template'] );
	$other_resolver = new FLX\Templates\Resolver( $synthetic, $engine, $storage ); $ok( 'Schema default' === $other_resolver->data( 'hero.title', 'fallback', array( 'post_id' => $id ) ), 'schema default without persistence' );
	$root['maps']['example']['version'] = 1; update_post_meta( $id, FLX\Data\PostMetaStorage::KEY, $root );
	$v2_registry = new FLX\Templates\MapRegistry( $engine ); $v2 = $map; $v2['version'] = 2; $v2_registry->register( $v2 );
	$v2_resolver = new FLX\Templates\Resolver( $v2_registry, $engine, $storage ); $v2_resolver->payload( $id );
	$ok( in_array( 'template_schema_version_mismatch', array_column( $v2_resolver->diagnostics(), 'code' ), true ), 'descriptor v2 detects stored v1' );
	$ok( 1 === $storage->read( $id )['maps']['example']['version'], 'v2 read does not upgrade v1 data' );
	$root['format_version'] = 99; update_post_meta( $id, FLX\Data\PostMetaStorage::KEY, $root ); $ok( ! $storage->save( $id, $map, array(), $root['revision'] ), 'future envelope not overwritten' );
	update_post_meta( $id, FLX\Data\PostMetaStorage::KEY, 'malformed' ); $ok( ! $storage->save( $id, $map, array(), 0 ), 'malformed envelope not overwritten' );
} finally { wp_delete_post( $id, true ); wp_delete_post( $other, true ); }
echo "PASS real WP Template Map: $checks assertions\n";
