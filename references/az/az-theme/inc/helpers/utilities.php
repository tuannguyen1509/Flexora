<?php
defined( 'ABSPATH' ) || exit;
function az_is_woocommerce_active() { return class_exists( 'WooCommerce' ); }
function az_get_theme_version() { $theme = wp_get_theme(); return (string) $theme->get( 'Version' ); }
