<?php
/** Include this site's optional homepage Page template in the parent SEO context. */
defined( 'ABSPATH' ) || exit;

add_filter( 'az_home_meta_description_context', static function( $is_home ) {
    return $is_home || is_page_template( 'front-page.php' );
} );
