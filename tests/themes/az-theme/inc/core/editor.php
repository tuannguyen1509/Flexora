<?php
/** Optional Classic Editor preference; no plugin installation is required. */
defined( 'ABSPATH' ) || exit;

function az_theme_use_block_editor( $use_block_editor, $post_type ) {
    if ( ! az_get_option( 'general.classic_editor', 0 ) ) {
        return $use_block_editor;
    }
    $types = apply_filters( 'az_classic_editor_post_types', array( 'post', 'page' ) );
    return in_array( $post_type, (array) $types, true ) ? false : $use_block_editor;
}
add_filter( 'use_block_editor_for_post_type', 'az_theme_use_block_editor', 100, 2 );

function az_theme_use_block_editor_for_post( $use_block_editor, $post ) {
    return az_theme_use_block_editor( $use_block_editor, $post->post_type );
}
add_filter( 'use_block_editor_for_post', 'az_theme_use_block_editor_for_post', 100, 2 );
