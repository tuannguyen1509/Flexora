<?php
/** Child declares callbacks before Parent; no copied engine or assets. */
defined( 'ABSPATH' ) || exit;
add_action( 'after_setup_theme', function () {
	load_child_theme_textdomain( 'flexora-child-starter', get_stylesheet_directory() . '/languages' );
}, 20 );
