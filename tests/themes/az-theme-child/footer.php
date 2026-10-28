<?php
/** Shared homepage footer for every page in the child theme. */
defined( 'ABSPATH' ) || exit;
$az_footer_data = az_child_footer_data();
$az_footer_options = az_get_options()['footer'] ?? array();
require get_stylesheet_directory() . '/template-parts/footer-reference.php';
