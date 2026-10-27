<?php
/** Shared assets for the neutral and About Page templates, also available to child themes. */
defined( 'ABSPATH' ) || exit;

function az_neutral_page_assets() {
	if ( ! is_page_template( 'page-neutral.php' ) && ! is_page_template( 'page-about.php' ) ) { return; }
	$path = get_theme_file_path( 'css/neutral-page.css' );
	if ( is_file( $path ) ) {
		wp_enqueue_style( 'az-neutral-page', get_theme_file_uri( 'css/neutral-page.css' ), array( 'az-content', 'az-page-style' ), (string) filemtime( $path ) );
	}
}
add_action( 'wp_enqueue_scripts', 'az_neutral_page_assets', 60 );
