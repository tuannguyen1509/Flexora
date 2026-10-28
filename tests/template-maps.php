<?php
require __DIR__ . '/fields.php';
$count = $checks;
$registry = new FLX\Templates\MapRegistry( $engine );
$map = array( 'id' => 'sample', 'label' => 'Sample', 'template' => 'page-templates/sample.php', 'version' => 1, 'groups' => array(
	array( 'id' => 'hero', 'label' => 'Hero', 'fields' => array( 'title' => array( 'type' => 'text', 'default' => 'Default' ), 'enabled' => array( 'type' => 'toggle' ), 'image' => array( 'type' => 'media' ) ) ),
) );
$registry->register( $map );
field_check( 'sample' === $registry->get( 'sample' )['id'], 'ID lookup' );
field_check( 'sample' === $registry->by_file( 'page-templates/sample.php' )['id'], 'file lookup' );
field_check( null === $registry->by_file( 'default' ), 'default has no map' );
field_check( 1 === count( $registry->all() ), 'list registry' );
field_check( 1 === $registry->get( 'sample' )['version'], 'version present' );
field_check( 'text' === $registry->get( 'sample' )['fields']['hero.title']['type'], 'same field schema scoped by group' );
field_check( isset( $registry->get( 'sample' )['groups']['hero'] ), 'groups indexed' );
field_throws( function () use ( $registry, $map ) { $registry->register( $map ); }, 'duplicate ID' );
$copy = $map; $copy['id'] = 'other'; field_throws( function () use ( $registry, $copy ) { $registry->register( $copy ); }, 'duplicate file' );
foreach ( array( '../escape.php', '/absolute.php', 'C:/file.php', 'templates\\file.php' ) as $file ) {
	$copy = $map; $copy['template'] = $file; $copy['id'] = 'invalid';
	field_throws( function () use ( $registry, $copy ) { $registry->register( $copy ); }, 'unsafe file rejected' );
}
foreach ( array( 0, '1', null ) as $version ) { $copy = $map; $copy['id'] = 'version'; $copy['template'] = 'version.php'; $copy['version'] = $version; field_throws( function () use ( $registry, $copy ) { $registry->register( $copy ); }, 'invalid version' ); }
$copy = $map; $copy['id'] = 'unknown'; $copy['template'] = 'unknown.php'; $copy['groups'][0]['fields']['title']['type'] = 'not-real'; field_throws( function () use ( $registry, $copy ) { $registry->register( $copy ); }, 'unknown field uses shared registry reject' );
$copy['groups'][0]['fields']['title']['type'] = 'text'; $copy['groups'][] = $copy['groups'][0]; field_throws( function () use ( $registry, $copy ) { $registry->register( $copy ); }, 'duplicate group' );
$copy = $map; $copy['id'] = 'second'; $copy['template'] = 'second.php'; $copy['components'] = array( 'flx.test' ); $registry->register( $copy );
field_check( array( 'flx.test' ) === $registry->get( 'second' )['components'], 'component declarations retained' );
$registry->seal(); field_throws( function () use ( $registry, $copy ) { $registry->register( $copy ); }, 'late reject' );
echo 'PASS template registry portable: ' . ( $checks - $count ) . " new assertions\n";
