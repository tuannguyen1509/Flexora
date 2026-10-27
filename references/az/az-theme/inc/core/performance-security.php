<?php
/** Optional hardening. Every feature requires both the master and its own switch. */
defined( 'ABSPATH' ) || exit;

function az_performance_security_enabled( $feature ) {
    return (bool) az_get_option( 'performance.security_enabled', 0 ) && (bool) az_get_option( 'performance.' . $feature, 0 );
}

add_filter( 'rest_authentication_errors', function ( $result ) {
    if ( null !== $result || ! az_performance_security_enabled( 'restrict_rest_guests' ) || is_user_logged_in() ) {
        return $result;
    }
    // Integrations can explicitly allow their public routes here.
    if ( apply_filters( 'az_security_allow_guest_rest', false ) ) {
        return $result;
    }
    return new WP_Error( 'az_rest_login_required', __( 'Vui lòng đăng nhập để sử dụng REST API.', 'az-theme' ), array( 'status' => 401 ) );
}, 100 );

add_filter( 'xmlrpc_enabled', function ( $enabled ) {
    return az_performance_security_enabled( 'disable_xmlrpc' ) ? false : $enabled;
} );
add_filter( 'xmlrpc_methods', function ( $methods ) {
    if ( az_performance_security_enabled( 'disable_xmlrpc' ) ) {
        return array();
    }
    if ( az_performance_security_enabled( 'disable_pingback' ) ) {
        unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
    }
    return $methods;
}, 100 );
add_filter( 'wp_headers', function ( $headers ) {
    if ( az_performance_security_enabled( 'disable_pingback' ) || az_performance_security_enabled( 'disable_xmlrpc' ) ) {
        foreach ( array_keys( $headers ) as $key ) {
            if ( 0 === strcasecmp( $key, 'X-Pingback' ) ) {
                unset( $headers[$key] );
            }
        }
    }
    return $headers;
}, 100 );

add_action( 'init', function () {
    if ( az_performance_security_enabled( 'clean_head_info' ) ) {
        remove_action( 'wp_head', 'rsd_link' );
        remove_action( 'wp_head', 'wlwmanifest_link' );
        remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
        remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
    }
    if ( az_performance_security_enabled( 'disable_feeds' ) ) {
        remove_action( 'wp_head', 'feed_links', 2 );
        remove_action( 'wp_head', 'feed_links_extra', 3 );
    }
}, 30 );
add_action( 'template_redirect', function () {
    if ( is_feed() && az_performance_security_enabled( 'disable_feeds' ) ) {
        wp_die( esc_html__( 'Nguồn cấp dữ liệu đã được tắt.', 'az-theme' ), '', array( 'response' => 403 ) );
    }
}, 0 );
add_action( 'wp_enqueue_scripts', function () {
    if ( az_performance_security_enabled( 'disable_wp_embed' ) ) {
        wp_dequeue_script( 'wp-embed' );
        // Preserve the registered handle for plugins declaring it as a dependency.
        $scripts = wp_scripts();
        if ( isset( $scripts->registered['wp-embed'] ) ) {
            $scripts->registered['wp-embed']->src = false;
        }
    }
}, 110 );

function az_security_image_mimes() {
    return array( 'jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp', 'avif' => 'image/avif' );
}
add_filter( 'upload_mimes', function ( $mimes ) {
    return az_performance_security_enabled( 'images_only_uploads' ) ? array_intersect( $mimes, az_security_image_mimes() ) : $mimes;
} );
function az_security_validate_image_upload( $file ) {
    if ( ! az_performance_security_enabled( 'images_only_uploads' ) || ! empty( $file['error'] ) ) {
        return $file;
    }
    $allowed = az_security_image_mimes();
    $type = wp_check_filetype( $file['name'], $allowed );
    $actual = wp_get_image_mime( $file['tmp_name'] );
    if ( ! $type['type'] || ! $actual || $actual !== $type['type'] ) {
        $file['error'] = __( 'Chỉ được tải lên file hình ảnh hợp lệ (JPEG, PNG, GIF, WebP, AVIF).', 'az-theme' );
    }
    return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'az_security_validate_image_upload' );
add_filter( 'wp_handle_sideload_prefilter', 'az_security_validate_image_upload' );

function az_security_asset_version( $src ) {
    if ( is_admin() || ! az_performance_security_enabled( 'strip_asset_versions' ) ) {
        return $src;
    }
    $host = wp_parse_url( $src, PHP_URL_HOST );
    if ( $host && strcasecmp( $host, (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) !== 0 ) {
        return $src;
    }
    return remove_query_arg( 'ver', $src );
}
add_filter( 'script_loader_src', 'az_security_asset_version', 100 );
add_filter( 'style_loader_src', 'az_security_asset_version', 100 );
