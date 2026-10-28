<?php
/** Generic starter examples. Editable values belong to Page data, not this file. */
defined( 'ABSPATH' ) || exit;
add_action( 'flx/template-maps/register', function () {
	flx_register_template_map( array( 'id' => 'example', 'label' => 'Flexora Example', 'template' => 'page-templates/flx-example.php', 'version' => 1, 'components' => defined( 'WP_DEBUG' ) && WP_DEBUG ? array( 'flx.fixture.example' ) : array(), 'groups' => array(
		array( 'id' => 'hero', 'label' => 'Hero', 'fields' => array(
			'title' => array( 'type' => 'text', 'label' => 'Tiêu đề' ),
			'description' => array( 'type' => 'editor', 'label' => 'Mô tả' ),
			'image' => array( 'type' => 'media', 'label' => 'Hình ảnh' ),
			'button_text' => array( 'type' => 'text', 'label' => 'Nội dung nút' ),
			'button_url' => array( 'type' => 'url', 'label' => 'URL nút' ),
		) ),
		array( 'id' => 'features', 'label' => 'Features', 'fields' => array( 'items' => array( 'type' => 'repeater', 'label' => 'Danh sách', 'item_label' => 'mục', 'fields' => array(
			'title' => array( 'type' => 'text', 'label' => 'Tiêu đề' ), 'description' => array( 'type' => 'editor', 'label' => 'Mô tả' ),
			'image' => array( 'type' => 'media', 'label' => 'Hình ảnh' ), 'enabled' => array( 'type' => 'toggle', 'label' => 'Hiển thị', 'default' => true ),
		) ) ) ),
	) ) );
	flx_register_template_map( array( 'id' => 'example-alt', 'label' => 'Flexora Example Alternate', 'template' => 'page-templates/flx-example-alt.php', 'version' => 1, 'groups' => array(
		array( 'id' => 'hero', 'label' => 'Alternate content', 'fields' => array( 'title' => array( 'type' => 'text', 'label' => 'Tiêu đề' ) ) ),
	) ) );
} );
// Development-only synthetic component proves map preflight without rendering to discover CSS.
add_action( 'flx/register', function ( $register ) {
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) { return; }
	$register->asset( new \FLX\Contracts\V1\SpecV1( array( 'id' => 'flx.fixture.example.style', 'owner' => 'child:starter', 'version' => '0.3.0', 'type' => 'style', 'handle' => 'flx-example-style', 'source' => get_stylesheet_directory_uri() . '/assets/css/pages/example.css', 'dependencies' => array(), 'context' => array( 'frontend' ) ) ) );
	$register->component( new \FLX\Contracts\V1\SpecV1( array( 'id' => 'flx.fixture.example', 'owner' => 'child:starter', 'version' => '0.3.0', 'dependencies' => array(), 'assets' => array( 'flx.fixture.example.style' ), 'behaviors' => array(), 'renderer' => function () { return ''; } ) ) );
} );
