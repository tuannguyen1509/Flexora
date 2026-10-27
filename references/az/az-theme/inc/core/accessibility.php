<?php
/** Shared keyboard navigation, including headers supplied by child themes. */
defined( 'ABSPATH' ) || exit;
add_action( 'wp_body_open', function() {
    echo '<a class="az-skip-link" href="#az-main-content">' . esc_html__( 'Bỏ qua tới nội dung', 'az-ai-theme' ) . '</a>';
}, 1 );
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'az-accessibility', get_template_directory_uri() . '/css/accessibility.css', array(), filemtime( get_template_directory() . '/css/accessibility.css' ) );
    wp_enqueue_script( 'az-accessibility', get_template_directory_uri() . '/js/accessibility.js', array(), filemtime( get_template_directory() . '/js/accessibility.js' ), true );
} );
