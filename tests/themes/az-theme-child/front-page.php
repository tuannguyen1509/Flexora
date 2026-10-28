<?php
/**
 * Trang chủ riêng của website sử dụng AZ Theme Child.
 *
 * Template Name: Trang chủ
 * Template Post Type: page
 *
 * @package AZ_Theme_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$az_child_home = locate_template( 'template-parts/home-storefront.php', false, false );
if ( $az_child_home ) {
    require $az_child_home;
} else {
    echo '<main class="az-main"><div class="az-wrap">';
    while ( have_posts() ) {
        the_post();
        the_content();
    }
    echo '</div></main>';
}

get_footer();
