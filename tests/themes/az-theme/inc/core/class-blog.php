<?php
defined( 'ABSPATH' ) || exit;
class AZ_Blog {
    public function __construct() { add_action( 'pre_get_posts', array( $this, 'query' ), 20 ); }
    public function query( $query ) { if ( is_admin() || ! $query->is_main_query() || ( ! $query->is_home() && ! $query->is_archive() ) || ( function_exists( 'is_shop' ) && is_shop() ) || ( function_exists( 'is_product_category' ) && is_product_category() ) ) { return; } $query->set( 'posts_per_page', max( 1, min( 100, absint( az_get_option( 'blog.posts_per_page', 10 ) ) ) ) ); }
}
new AZ_Blog();
function az_blog_show( $field, $default = 1 ) { $all = az_get_options(); $blog = isset( $all['blog'] ) && is_array( $all['blog'] ) ? $all['blog'] : array(); return array_key_exists( $field, $blog ) ? (bool) $blog[ $field ] : (bool) $default; }

