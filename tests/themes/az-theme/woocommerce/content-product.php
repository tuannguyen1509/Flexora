<?php
/**
 * Shared WooCommerce loop product card.
 *
 * @package AZ_AI_Theme
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product || ! $product->is_visible() || ! function_exists( 'az_render_product_card' ) ) {
    return;
}

az_render_product_card(
    $product,
    array(
        'context' => 'loop',
        'tag'     => 'li',
    )
);
