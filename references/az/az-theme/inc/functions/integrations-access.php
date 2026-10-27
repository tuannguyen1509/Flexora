<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Load the parent theme login stylesheet on WordPress login screens.
 *
 * get_template_directory_uri() deliberately keeps this shared styling active
 * even when a child theme is enabled.
 */
function az_enqueue_login_assets() {
    $file = get_template_directory() . '/login.css';

    if ( ! file_exists( $file ) ) {
        return;
    }

    wp_enqueue_style(
        'az-login',
        get_template_directory_uri() . '/login.css',
        array(),
        (string) filemtime( $file )
    );
}
add_action( 'login_enqueue_scripts', 'az_enqueue_login_assets' );

// Include Local License API for Content AI Plugin Integration
require_once get_template_directory() . '/inc/license-api.php';

/**
 * Auto-expire licenses based on expiry date
 * Runs daily via WP Cron
 */
add_action('az_check_expired_licenses', 'az_auto_expire_licenses');
function az_auto_expire_licenses() {
    global $wpdb;
    
    // Get current date
    $current_date = current_time('mysql');
    
    // Find licenses that have expired
    $expired_licenses = $wpdb->get_results($wpdb->prepare(
        "SELECT p.ID, pm1.meta_value as expiry_date, pm2.meta_value as status
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_expiry_date'
        LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_status'
        WHERE p.post_type = 'license'
        AND p.post_status = 'publish'
        AND pm1.meta_value != ''
        AND pm1.meta_value < %s
        AND (pm2.meta_value IS NULL OR pm2.meta_value = 'active')
        ",
        $current_date
    ));
    
    $count = 0;
    foreach ($expired_licenses as $license) {
        // Update status to expired
        update_post_meta($license->ID, '_status', 'expired');
        update_post_meta($license->ID, '_license_status', 'expired');
        
        // Log
        error_log("Auto-expired license ID {$license->ID} (expiry: {$license->expiry_date})");
        $count++;
    }
    
    if ($count > 0) {
        error_log("AZ Content AI: Auto-expired {$count} licenses");
    }
    
    return $count;
}

// Schedule cron job if not already scheduled
if (!wp_next_scheduled('az_check_expired_licenses')) {
    wp_schedule_event(time(), 'daily', 'az_check_expired_licenses');
}

// Enqueue Pricing Redirect JS
function az_enqueue_pricing_redirect_js() {
    if ( is_page_template( 'page-pricing-order.php' ) ) {
        wp_enqueue_script( 'az-pricing-redirect', get_template_directory_uri() . '/js/pricing-redirect.js', array(), '1.0.0', true );
    }
}
add_action( 'wp_enqueue_scripts', 'az_enqueue_pricing_redirect_js' );

/**
 * Hide Admin Bar for Non-Admin Users
 * Ẩn thanh admin bar cho thành viên thường (không phải admin)
 */
add_action( 'after_setup_theme', 'az_hide_admin_bar_for_non_admins' );
function az_hide_admin_bar_for_non_admins() {
    // Chỉ ẩn cho user đã login nhưng không phải admin
    if ( is_user_logged_in() && ! current_user_can( 'manage_options' ) ) {
        show_admin_bar( false );
    }
}

/**
 * Remove Admin Bar CSS for Non-Admin Users
 * Xóa CSS của admin bar để không có khoảng trống ở trên
 */
add_action( 'get_header', 'az_remove_admin_bar_css' );
function az_remove_admin_bar_css() {
    if ( is_user_logged_in() && ! current_user_can( 'manage_options' ) ) {
        remove_action( 'wp_head', '_admin_bar_bump_cb' );
    }
}

/**
 * Disable Admin Bar in User Profile for Non-Admins
 * Tắt tùy chọn hiển thị admin bar trong profile
 */
add_action( 'admin_init', 'az_disable_admin_bar_profile_option' );
function az_disable_admin_bar_profile_option() {
    if ( ! current_user_can( 'manage_options' ) ) {
        update_user_meta( get_current_user_id(), 'show_admin_bar_front', 'false' );
    }
}

/**
 * Redirect Non-Admin Users Away from Dashboard
 * Chặn thành viên thường không được vào /wp-admin/
 */
add_action( 'admin_init', 'az_redirect_non_admin_from_dashboard' );
function az_redirect_non_admin_from_dashboard() {
    // Chỉ redirect nếu:
    // 1. User không phải admin
    // 2. Không phải AJAX request (để không break các chức năng AJAX)
    // 3. Không phải admin-ajax.php (WordPress AJAX endpoint)
    if ( ! current_user_can( 'manage_options' ) && ! wp_doing_ajax() ) {
        // Redirect về trang My Licenses
        wp_redirect( home_url( '/thanh-vien' ) );
        exit;
    }
}


/**
 * Handle Plugin Download
 * Cho phép members download plugin sau khi đăng ký
 */
add_action( 'template_redirect', 'az_handle_plugin_download' );
function az_handle_plugin_download() {
    if ( ! is_page( 'download-plugin' ) ) {
        return;
    }
    
    // Check if user is logged in
    if ( ! is_user_logged_in() ) {
        wp_redirect( wp_login_url( get_permalink() ) );
        exit;
    }
    
    // Check if user has active license
    $current_user = wp_get_current_user();
    $licenses = get_posts( array(
        'post_type'      => 'license',
        'posts_per_page' => 1,
        'author'         => $current_user->ID,
        'post_status'    => array( 'publish', 'private' ),
        'meta_query'     => array(
            array(
                'key'     => '_status',
                'value'   => 'active',
                'compare' => '=',
            ),
        ),
    ) );
    
    if ( empty( $licenses ) ) {
        wp_die( 'Bạn cần có license active để download plugin. <a href="' . home_url( '/pricing' ) . '">Mua license ngay</a>' );
    }
    
    // Path to plugin ZIP file
    $plugin_path = WP_PLUGIN_DIR . '/plugin-az-content-ai.zip';
    
    // If ZIP doesn't exist, create it
    if ( ! file_exists( $plugin_path ) ) {
        $plugin_dir = WP_PLUGIN_DIR . '/plugin-az-content-ai';
        
        if ( ! file_exists( $plugin_dir ) ) {
            wp_die( 'Plugin không tồn tại trên server.' );
        }
        
        // Create ZIP file
        $zip = new ZipArchive();
        if ( $zip->open( $plugin_path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) !== true ) {
            wp_die( 'Không thể tạo file ZIP.' );
        }
        
        // Add files to ZIP
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $plugin_dir ),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ( $files as $file ) {
            if ( ! $file->isDir() ) {
                $file_path = $file->getRealPath();
                $relative_path = 'plugin-az-content-ai/' . substr( $file_path, strlen( $plugin_dir ) + 1 );
                $zip->addFile( $file_path, $relative_path );
            }
        }
        
        $zip->close();
    }
    
    // Download file
    if ( file_exists( $plugin_path ) ) {
        header( 'Content-Type: application/zip' );
        header( 'Content-Disposition: attachment; filename="plugin-az-content-ai.zip"' );
        header( 'Content-Length: ' . filesize( $plugin_path ) );
        header( 'Cache-Control: no-cache, must-revalidate' );
        header( 'Expires: 0' );
        
        readfile( $plugin_path );
        exit;
    } else {
        wp_die( 'File plugin không tồn tại.' );
    }
}
