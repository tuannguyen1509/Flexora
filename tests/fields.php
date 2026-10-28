<?php
/** Portable engine contract tests. WP integration exercises real primitives separately. */
require_once dirname( __DIR__ ) . '/flexora/autoload.php';
if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $v ) { return trim( strip_tags( $v ) ); }
	function sanitize_textarea_field( $v ) { return trim( strip_tags( $v ) ); }
	function wp_kses_post( $v ) { return preg_replace( '#<script.*?</script>#si', '', $v ); }
	function esc_url_raw( $v ) { return trim( $v ); }
	function sanitize_email( $v ) { return trim( $v ); }
	function sanitize_hex_color( $v ) { return preg_match( '/^#([a-f0-9]{3}|[a-f0-9]{6})$/iD', $v ) ? $v : null; }
	function is_email( $v ) { return filter_var( $v, FILTER_VALIDATE_EMAIL ); }
	function get_post_type( $id ) { return 17 === $id ? 'attachment' : 'post'; }
	function wp_attachment_is_image( $id ) { return 17 === $id; }
	function current_user_can( $cap, $id = null ) { return true; }
}
$checks = 0;
function field_check( $ok, $message ) { global $checks; if ( ! $ok ) { throw new RuntimeException( $message ); } $checks++; }
function field_throws( $call, $message ) { try { $call(); } catch ( InvalidArgumentException $e ) { field_check( true, $message ); return; } field_check( false, $message ); }
$types = new FLX\Fields\TypeRegistry(); FLX\Fields\BuiltinTypes::register( $types );
field_check( 13 === count( $types->ids() ), '13 required field types' );
foreach ( $types->ids() as $type ) { foreach ( array( 'render', 'normalize', 'sanitize', 'validate', 'default' ) as $method ) { field_check( is_callable( $types->get( $type )[ $method ] ), $type . ' contract ' . $method ); } }
field_throws( function () use ( $types ) { $types->register( 'text', $types->get( 'text' ) ); }, 'duplicate type rejected' );
field_throws( function () use ( $types ) { $types->get( 'unsupported' ); }, 'unknown type rejected' );
$engine = new FLX\Fields\Engine( $types );
$fields = array(
	'text' => array( 'type' => 'text', 'maxlength' => 20 ), 'nullable' => array( 'type' => 'text', 'nullable' => true ),
	'number' => array( 'type' => 'number', 'min' => 0, 'max' => 10, 'step' => 2 ), 'toggle' => array( 'type' => 'toggle' ),
	'url' => array( 'type' => 'url' ), 'email' => array( 'type' => 'email' ), 'color' => array( 'type' => 'color' ),
	'select' => array( 'type' => 'select', 'options' => array( '0' => 'Zero', 'one' => 'One' ), 'default' => 'one' ),
	'rich' => array( 'type' => 'editor' ), 'media' => array( 'type' => 'media' ), 'gallery' => array( 'type' => 'gallery' ),
	'rows' => array( 'type' => 'repeater', 'fields' => array( 'title' => array( 'type' => 'text' ), 'body' => array( 'type' => 'editor' ), 'image' => array( 'type' => 'media' ), 'visible' => array( 'type' => 'toggle' ) ) ),
);
$engine->check_schema( $fields );
field_check( false === $engine->defaults( $fields )['toggle'], 'boolean default' );
field_check( array() === $engine->defaults( $fields )['rows'], 'repeater default' );
field_throws( function () use ( $engine ) { $engine->check_schema( array( 'bad' => array( 'type' => 'unknown' ) ) ); }, 'no type fallback' );
field_throws( function () use ( $engine ) { $engine->check_schema( array( 'r' => array( 'type' => 'repeater', 'fields' => array( 'r' => array( 'type' => 'repeater', 'fields' => array() ) ) ) ) ); }, 'nested repeater explicitly rejected' );
$old = array( 'text' => 'keep', 'number' => 4, 'toggle' => true, 'unknown' => array( 'future' => 'safe' ) );
field_check( $old === $engine->patch( $fields, $old, array() )['values'], 'missing request preserves entire state' );
foreach ( array( array( 'text', '', '' ), array( 'number', '0', 0 ), array( 'toggle', '0', false ), array( 'toggle', false, false ), array( 'nullable', null, null ), array( 'rows', array(), array() ), array( 'gallery', array(), array() ) ) as $case ) {
	$result = $engine->patch( $fields, $old, array( $case[0] => $case[1] ) );
	field_check( array() === $result['errors'] && $case[2] === $result['values'][ $case[0] ], 'explicit value preserved: ' . $case[0] );
	field_check( $old['unknown'] === $result['values']['unknown'], 'unknown branch preserved' );
}
foreach ( array( array( 'text', null ), array( 'text', false ), array( 'text', array() ), array( 'number', 11 ), array( 'number', 3 ), array( 'url', 'javascript:alert(1)' ), array( 'email', 'bad' ), array( 'select', 'bad' ), array( 'toggle', '' ), array( 'media', -1 ), array( 'media', 99 ), array( 'color', 'notcolor' ), array( 'rows', 'bad' ), array( 'gallery', array( 99 ) ) ) as $case ) {
	field_check( isset( $engine->patch( $fields, $old, array( $case[0] => $case[1] ) )['errors'][ $case[0] ] ), 'invalid typed value rejected: ' . $case[0] );
}
field_check( isset( $engine->patch( $fields, $old, array( 'intruder' => 'x' ) )['errors']['intruder'] ), 'unexpected submitted key rejected' );
field_check( 'ok' === $engine->patch( $fields, array(), array( 'text' => '<b>ok</b>' ) )['values']['text'], 'text sanitizer' );
field_check( '' === $engine->patch( $fields, array(), array( 'url' => '' ) )['values']['url'], 'optional empty URL' );
field_check( '0' === $engine->patch( $fields, array(), array( 'select' => '0' ) )['values']['select'], 'zero choice is valid' );
$rows = array( array( '_id' => 'stable-row-one', 'title' => 'One', 'future' => 'keep' ), array( '_id' => 'stable-row-two', 'title' => 'Two' ) );
$result = $engine->patch( $fields, array( 'rows' => $rows ), array( 'rows' => array( array( '_id' => 'stable-row-two', 'body' => '<strong>Rich</strong>' ), array( '_id' => 'stable-row-one', 'title' => 'Edited' ) ) ) );
field_check( array() === $result['errors'], 'reorder and partial children valid' );
field_check( 'stable-row-two' === $result['values']['rows'][0]['_id'], 'reorder preserves identity' );
field_check( 'Two' === $result['values']['rows'][0]['title'], 'missing child preserves old value' );
field_check( 'keep' === $result['values']['rows'][1]['future'], 'unknown row branch preserved' );
field_check( '<strong>Rich</strong>' === $result['values']['rows'][0]['body'], 'editor child formatting retained' );
field_check( count( $engine->patch( $fields, array( 'rows' => $rows ), array( 'rows' => array( $rows[0] ) ) )['values']['rows'] ) === 1, 'remove row' );
field_check( array() === $engine->patch( $fields, array( 'rows' => $rows ), array( 'rows' => '' ) )['values']['rows'], 'HTML explicit clear sentinel becomes empty list' );
field_check( $rows === $engine->patch( $fields, array( 'rows' => $rows ), array() )['values']['rows'], 'missing repeater preserves rows' );
foreach ( array( array( $rows[0], $rows[0] ), array( array( '_id' => 'bad' ) ), array( array( '_id' => 'stable-row-one', 'image' => 99 ) ), array( array( '_id' => 'stable-row-one', 'evil' => true ) ) ) as $bad ) {
	field_check( count( $engine->patch( $fields, array(), array( 'rows' => $bad ) )['errors'] ) > 0, 'invalid repeater identity/child rejected' );
}
$registry = new FLX\Admin\SectionRegistry( $engine );
$section = array( 'title' => 'Extension', 'groups' => array( array( 'title' => 'Group', 'fields' => array( 'a' => array( 'type' => 'text' ) ) ) ) );
$registry->register( 'extension', $section ); field_check( null !== $registry->get( 'extension' ), 'no section allowlist' );
field_throws( function () use ( $registry, $section ) { $registry->register( 'extension', $section ); }, 'duplicate section rejected' );
$registry->seal(); field_throws( function () use ( $registry, $section ) { $registry->register( 'late', $section ); }, 'late section rejected' );
$types->seal(); field_throws( function () use ( $types ) { $types->register( 'late', $types->get( 'text' ) ); }, 'late field type rejected' );
$all_subfields = array();
foreach ( array( 'text', 'textarea', 'editor', 'number', 'url', 'email', 'select', 'radio', 'toggle', 'color', 'media', 'gallery' ) as $type ) {
	$all_subfields[ $type ] = array( 'type' => $type );
	if ( in_array( $type, array( 'select', 'radio' ), true ) ) { $all_subfields[ $type ]['options'] = array( 'a' => 'A' ); $all_subfields[ $type ]['default'] = 'a'; }
}
$composite = array( 'items' => array( 'type' => 'repeater', 'fields' => $all_subfields ) );
$engine->check_schema( $composite );
$all_values = array( '_id' => 'complete-row-01', 'text' => 'Short', 'textarea' => "Two\nlines", 'editor' => '<em>Rich</em>', 'number' => 0, 'url' => 'https://example.org', 'email' => 'test@example.org', 'select' => 'a', 'radio' => 'a', 'toggle' => false, 'color' => '#123456', 'media' => 17, 'gallery' => array( 17 ) );
$composite_result = $engine->patch( $composite, array(), array( 'items' => array( $all_values ) ) );
field_check( array() === $composite_result['errors'], 'all twelve typed repeater children supported' );
foreach ( $all_values as $key => $value ) { field_check( $value === $composite_result['values']['items'][0][ $key ], 'typed child preserved: ' . $key ); }
$all_values['url'] = 'bad-url';
$invalid = $engine->patch( $composite, $composite_result['values'], array( 'items' => array( $all_values ) ) );
field_check( isset( $invalid['errors']['items.complete-row-01.url'] ), 'child error path uses stable identity' );
field_check( 'bad-url' === $invalid['display']['items'][0]['url'], 'invalid child display input retained' );
field_check( 'https://example.org' === $invalid['values']['items'][0]['url'], 'invalid display cannot enter storage values' );
$production_sections = new FLX\Admin\SectionRegistry( $engine ); FLX\Admin\DefaultSections::register( $production_sections, false );
field_check( null === $production_sections->get( 'playground' ), 'playground absent with development mode off' );
$required = array( 'n' => array( 'type' => 'number', 'required' => true ), 'b' => array( 'type' => 'toggle', 'required' => true ), 't' => array( 'type' => 'text', 'required' => true, 'nullable' => true ) );
field_check( array() === $engine->patch( $required, array(), array( 'n' => 0, 'b' => false ) )['errors'], 'required does not reject valid zero or false' );
field_check( isset( $engine->patch( $required, array(), array( 't' => null ) )['errors']['t'] ), 'required rejects null even when nullable' );
echo "PASS field engine portable: $checks assertions (WP primitive stubs; real WP tested separately)\n";
