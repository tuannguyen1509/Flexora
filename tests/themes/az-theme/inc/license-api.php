<?php
/**
 * Local License API for Content AI Plugin Integration
 * 
 * This file provides API endpoints for the Content AI plugin to verify and manage licenses
 * from the local WordPress system instead of an external server.
 * 
 * @package AZ_AI_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Helper Functions - Define these first before API functions
 */

/**
 * Helper: Get license by key
 */
function az_get_license_by_key( $license_key ) {
    $args = array(
        'post_type' => 'license',
        'post_status' => array( 'publish', 'private' ),
        'meta_query' => array(
            array(
                'key' => '_license_key',
                'value' => $license_key,
                'compare' => '=',
            ),
        ),
        'posts_per_page' => 1,
    );
    
    $licenses = get_posts( $args );
    
    return ! empty( $licenses ) ? $licenses[0] : false;
}

/**
 * Helper: Get plan features
 */
function az_get_plan_features( $plan_slug ) {
    $features_map = array(
        'free' => array( 'basic_templates' ),
        'pro' => array( 'basic_templates', 'premium_templates', 'priority_support' ),
        'business' => array( 'basic_templates', 'premium_templates', 'priority_support', 'custom_templates', 'api_access' ),
    );
    
    return isset( $features_map[ $plan_slug ] ) ? $features_map[ $plan_slug ] : array( 'basic_templates' );
}

/**
 * Helper: Get post limit by plan
 */
function az_get_post_limit( $plan_slug ) {
    // Use the helper function from vietqr-payment.php if available
    if ( function_exists( 'az_get_plan_post_limit' ) ) {
        return az_get_plan_post_limit( $plan_slug );
    }
    
    // Fallback to default limits
    $post_limits = array(
        'free' => 10,         // 10 posts
        'pro' => 100,         // 100 posts
        'business' => 1000,   // 1000 posts
    );
    
    return isset( $post_limits[ $plan_slug ] ) ? $post_limits[ $plan_slug ] : 10;
}

/**
 * Register REST API endpoints for license management
 */
add_action( 'rest_api_init', 'az_register_license_api_endpoints' );
function az_register_license_api_endpoints() {
    // Verify license endpoint
    register_rest_route( 'az-license/v1', '/verify', array(
        'methods' => 'POST',
        'callback' => 'az_api_verify_license',
        'permission_callback' => '__return_true', // Public endpoint
    ) );
    
    // Activate license endpoint
    register_rest_route( 'az-license/v1', '/activate', array(
        'methods' => 'POST',
        'callback' => 'az_api_activate_license',
        'permission_callback' => '__return_true',
    ) );
    
    // Update usage endpoint
    register_rest_route( 'az-license/v1', '/usage', array(
        'methods' => 'POST',
        'callback' => 'az_api_update_usage',
        'permission_callback' => '__return_true',
    ) );
    
    // Update tokens endpoint
    register_rest_route( 'az-license/v1', '/update-tokens', array(
        'methods' => 'POST',
        'callback' => 'az_api_update_tokens',
        'permission_callback' => '__return_true',
    ) );
}

/**
 * API: Verify License
 * 
 * Endpoint: POST /wp-json/az-license/v1/verify
 * Body: { license_key, domain }
 */
function az_api_verify_license( WP_REST_Request $request ) {
    $license_key = sanitize_text_field( $request->get_param( 'license_key' ) );
    $domain = sanitize_text_field( $request->get_param( 'domain' ) );
    
    if ( empty( $license_key ) ) {
        return new WP_REST_Response( array(
            'success' => false,
            'message' => 'License key is required',
        ), 400 );
    }
    
    // Find license by key
    $license = az_get_license_by_key( $license_key );
    
    if ( ! $license ) {
        return new WP_REST_Response( array(
            'success' => false,
            'message' => 'License key not found',
        ), 404 );
    }
    
    // Get license details
    $status = get_post_meta( $license->ID, '_status', true );
    $plan_slug = get_post_meta( $license->ID, '_plan_slug', true );
    $api_calls_used = (int) get_post_meta( $license->ID, '_api_calls_used', true );
    $api_calls_limit = (int) get_post_meta( $license->ID, '_api_calls_limit', true );
    $created_at = get_post_meta( $license->ID, '_created_at', true );
    $expires_at = get_post_meta( $license->ID, '_expires_at', true );
    $license_domain = get_post_meta( $license->ID, '_license_domain', true );
    
    // Check if license is active
    if ( empty( $status ) ) {
        $status = 'active'; // Default to active if not set
    }
    
    // Check expiry - support both _expires_at and _expiry_date
    $is_expired = false;
    $expiry_date = $expires_at;
    
    // Try _expiry_date if _expires_at is empty
    if ( empty( $expiry_date ) ) {
        $expiry_date = get_post_meta( $license->ID, '_expiry_date', true );
    }
    
    if ( ! empty( $expiry_date ) ) {
        $expiry_timestamp = strtotime( $expiry_date );
        $current_timestamp = current_time( 'timestamp' );
        
        if ( $expiry_timestamp < $current_timestamp ) {
            $is_expired = true;
            $status = 'expired';
            
            // Auto-update status to expired
            update_post_meta( $license->ID, '_status', 'expired' );
            update_post_meta( $license->ID, '_license_status', 'expired' );
            
            error_log( "License {$license_key} auto-expired (expiry: {$expiry_date})" );
        }
    }
    
    // Use the found expiry date for response
    if ( empty( $expires_at ) && ! empty( $expiry_date ) ) {
        $expires_at = $expiry_date;
    }
    
    // Check domain binding (if domain is provided and license has domain set)
    if ( ! empty( $domain ) && ! empty( $license_domain ) && $license_domain !== $domain ) {
        return new WP_REST_Response( array(
            'success' => false,
            'message' => 'License is bound to another domain: ' . $license_domain,
        ), 403 );
    }
    
    // If domain is provided but license has no domain, bind it
    if ( ! empty( $domain ) && empty( $license_domain ) ) {
        update_post_meta( $license->ID, '_license_domain', $domain );
        $license_domain = $domain;
    }
    
    // Get user info
    $user = get_userdata( $license->post_author );
    
    // Get token usage from plugin table if available
    global $wpdb;
    $plugin_usage_table = $wpdb->prefix . 'iaca_license_usage';
    $tokens_used = 0;
    $tokens_limit = az_get_plan_limit( $plan_slug );
    
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$plugin_usage_table'" ) == $plugin_usage_table ) {
        $usage_data = $wpdb->get_row( $wpdb->prepare(
            "SELECT tokens_used, tokens_limit FROM $plugin_usage_table 
             WHERE license_key = %s AND domain = %s",
            $license_key,
            $domain
        ) );
        
        if ( $usage_data ) {
            $tokens_used = (int) $usage_data->tokens_used;
            if ( $usage_data->tokens_limit > 0 ) {
                $tokens_limit = (int) $usage_data->tokens_limit;
            }
        }
    }
    
    // Determine license features based on plan
    $features = az_get_plan_features( $plan_slug );
    
    // Get post limit based on plan
    $post_limit = az_get_post_limit( $plan_slug );
    
    // Build response
    $response_data = array(
        'success' => true,
        'message' => 'License is valid',
        'data' => array(
            'license_key' => $license_key,
            'license_type' => ucfirst( $plan_slug ),
            'status' => $status,
            'is_active' => ( $status === 'active' ),
            'is_expired' => $is_expired,
            'domain' => $license_domain,
            'post_count' => $api_calls_used,
            'post_limit' => $post_limit,
            'tokens_used' => $tokens_used,
            'tokens_limit' => $tokens_limit,
            'created_date' => $created_at,
            'expiry_date' => $expires_at,
            'customer_name' => $user->display_name,
            'customer_email' => $user->user_email,
            'features' => $features,
        ),
    );
    
    return new WP_REST_Response( $response_data, 200 );
}

/**
 * API: Activate License
 * 
 * Endpoint: POST /wp-json/az-license/v1/activate
 * Body: { license_key, domain }
 */
function az_api_activate_license( WP_REST_Request $request ) {
    $license_key = sanitize_text_field( $request->get_param( 'license_key' ) );
    $domain = sanitize_text_field( $request->get_param( 'domain' ) );
    
    if ( empty( $license_key ) || empty( $domain ) ) {
        return new WP_REST_Response( array(
            'success' => false,
            'message' => 'License key and domain are required',
        ), 400 );
    }
    
    // Find license
    $license = az_get_license_by_key( $license_key );
    
    if ( ! $license ) {
        return new WP_REST_Response( array(
            'success' => false,
            'message' => 'License key not found',
        ), 404 );
    }
    
    // Check if license is already bound to another domain
    $current_domain = get_post_meta( $license->ID, '_license_domain', true );
    
    if ( ! empty( $current_domain ) && $current_domain !== $domain ) {
        return new WP_REST_Response( array(
            'success' => false,
            'message' => 'License is already activated on another domain: ' . $current_domain,
        ), 403 );
    }
    
    // Bind license to domain
    update_post_meta( $license->ID, '_license_domain', $domain );
    update_post_meta( $license->ID, '_status', 'active' );
    update_post_meta( $license->ID, '_activated_at', current_time( 'mysql' ) );
    
    // Get license details
    $plan_slug = get_post_meta( $license->ID, '_plan_slug', true );
    $user = get_userdata( $license->post_author );
    
    return new WP_REST_Response( array(
        'success' => true,
        'message' => 'License activated successfully',
        'data' => array(
            'license_key' => $license_key,
            'license_type' => ucfirst( $plan_slug ),
            'domain' => $domain,
            'customer_name' => $user->display_name,
            'customer_email' => $user->user_email,
        ),
    ), 200 );
}

/**
 * API: Update Usage (Post Count)
 * 
 * Endpoint: POST /wp-json/az-license/v1/usage
 * Body: { license_key, domain, post_count }
 */
function az_api_update_usage( WP_REST_Request $request ) {
    $license_key = sanitize_text_field( $request->get_param( 'license_key' ) );
    $domain = sanitize_text_field( $request->get_param( 'domain' ) );
    $post_count = (int) $request->get_param( 'post_count' );
    
    if ( empty( $license_key ) ) {
        return new WP_REST_Response( array(
            'success' => false,
            'message' => 'License key is required',
        ), 400 );
    }
    
    // Find license
    $license = az_get_license_by_key( $license_key );
    
    if ( ! $license ) {
        return new WP_REST_Response( array(
            'success' => false,
            'message' => 'License key not found',
        ), 404 );
    }
    
    // Update post count
    update_post_meta( $license->ID, '_api_calls_used', $post_count );
    update_post_meta( $license->ID, '_last_used_at', current_time( 'mysql' ) );
    
    return new WP_REST_Response( array(
        'success' => true,
        'message' => 'Usage updated successfully',
        'data' => array(
            'post_count' => $post_count,
        ),
    ), 200 );
}

/**
 * API: Update Tokens
 * 
 * Endpoint: POST /wp-json/az-license/v1/update-tokens
 * Body: { license_key, domain, tokens_used }
 */
function az_api_update_tokens( WP_REST_Request $request ) {
    $license_key = sanitize_text_field( $request->get_param( 'license_key' ) );
    $domain = sanitize_text_field( $request->get_param( 'domain' ) );
    $tokens_used = (int) $request->get_param( 'tokens_used' );
    
    if ( empty( $license_key ) ) {
        return new WP_REST_Response( array(
            'success' => false,
            'message' => 'License key is required',
        ), 400 );
    }
    
    // Find license
    $license = az_get_license_by_key( $license_key );
    
    if ( ! $license ) {
        return new WP_REST_Response( array(
            'success' => false,
            'message' => 'License key not found',
        ), 404 );
    }
    
    // Update in license meta (for dashboard display)
    update_post_meta( $license->ID, '_tokens_used', $tokens_used );
    update_post_meta( $license->ID, '_last_used_at', current_time( 'mysql' ) );
    
    return new WP_REST_Response( array(
        'success' => true,
        'message' => 'Tokens updated successfully',
        'data' => array(
            'tokens_used' => $tokens_used,
        ),
    ), 200 );
}

/**
 * Auto-activate license for Content AI plugin when user logs in
 * This ensures seamless integration between the two systems
 */
add_action( 'wp_login', 'az_auto_sync_license_to_plugin', 10, 2 );
function az_auto_sync_license_to_plugin( $user_login, $user ) {
    // Check if Content AI plugin is active
    if ( ! class_exists( 'IACA_License_Manager' ) ) {
        return;
    }
    
    // Get user's active licenses
    $licenses = get_posts( array(
        'post_type' => 'license',
        'post_status' => array( 'publish', 'private' ),
        'author' => $user->ID,
        'meta_query' => array(
            array(
                'key' => '_status',
                'value' => 'active',
                'compare' => '=',
            ),
        ),
        'posts_per_page' => 1,
        'orderby' => 'date',
        'order' => 'DESC',
    ) );
    
    if ( empty( $licenses ) ) {
        return;
    }
    
    // Get the most recent active license
    $license = $licenses[0];
    $license_key = get_post_meta( $license->ID, '_license_key', true );
    
    // Save to plugin options
    $iaca_set = get_option( 'iaca_set', array() );
    $iaca_set['license_key'] = $license_key;
    update_option( 'iaca_set', $iaca_set );
}
