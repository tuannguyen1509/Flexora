<?php
defined( 'ABSPATH' ) || exit;

return array(
    array( 'id' => 'site_layout', 'type' => 'select', 'title' => 'Site Layout', 'default' => 'full', 'options' => array( 'full' => 'Full Width', 'boxed' => 'Boxed' ) ),
    array( 'id' => 'container', 'type' => 'number', 'title' => 'Container Width', 'default' => 1110 ),
    array( 'id' => 'container_tablet', 'type' => 'number', 'title' => 'Tablet Container Width', 'default' => 960 ),
    array( 'id' => 'mobile_padding', 'type' => 'number', 'title' => 'Mobile Padding', 'default' => 24 ),
    array( 'id' => 'grid_gutter', 'type' => 'number', 'title' => 'Grid Gutter', 'default' => 24 ),
    array( 'id' => 'section_spacing_desktop', 'type' => 'number', 'title' => 'Section Spacing Desktop', 'default' => 42 ),
    array( 'id' => 'section_spacing_tablet', 'type' => 'number', 'title' => 'Section Spacing Tablet', 'default' => 32 ),
    array( 'id' => 'section_spacing_mobile', 'type' => 'number', 'title' => 'Section Spacing Mobile', 'default' => 28 ),
    array( 'id' => 'radius_small', 'type' => 'number', 'title' => 'Radius Small', 'default' => 4 ),
    array( 'id' => 'radius_medium', 'type' => 'number', 'title' => 'Radius Medium', 'default' => 8 ),
    array( 'id' => 'radius_large', 'type' => 'number', 'title' => 'Radius Large', 'default' => 14 ),
);
