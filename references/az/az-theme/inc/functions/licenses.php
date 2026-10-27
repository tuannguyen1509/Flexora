<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register License Custom Post Type
 */
function az_register_license_post_type() {
    $labels = array(
        'name'               => __( 'Licenses', 'az-ai-theme' ),
        'singular_name'      => __( 'License', 'az-ai-theme' ),
        'menu_name'          => __( 'Licenses', 'az-ai-theme' ),
        'add_new'            => __( 'Add New', 'az-ai-theme' ),
        'add_new_item'       => __( 'Add New License', 'az-ai-theme' ),
        'edit_item'          => __( 'Edit License', 'az-ai-theme' ),
        'new_item'           => __( 'New License', 'az-ai-theme' ),
        'view_item'          => __( 'View License', 'az-ai-theme' ),
        'search_items'       => __( 'Search Licenses', 'az-ai-theme' ),
        'not_found'          => __( 'No licenses found', 'az-ai-theme' ),
        'not_found_in_trash' => __( 'No licenses found in trash', 'az-ai-theme' ),
    );
    
    $args = array(
        'labels'              => $labels,
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'query_var'           => true,
        'rewrite'             => false,
        'capability_type'     => 'post',
        'has_archive'         => false,
        'hierarchical'        => false,
        'menu_position'       => 25,
        'menu_icon'           => 'dashicons-admin-network',
        'supports'            => array( 'title', 'author' ),
    );
    
    register_post_type( 'license', $args );
}
add_action( 'init', 'az_register_license_post_type' );

/**
 * Add License Meta Boxes
 */
function az_add_license_meta_boxes() {
    add_meta_box(
        'az_license_details',
        __( 'License Details', 'az-ai-theme' ),
        'az_license_details_callback',
        'license',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'az_add_license_meta_boxes' );

/**
 * Add custom columns to Licenses list
 */
add_filter( 'manage_license_posts_columns', 'az_license_columns' );
function az_license_columns( $columns ) {
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = __( 'License', 'az-ai-theme' );
    $new_columns['license_key'] = __( 'License Key', 'az-ai-theme' );
    $new_columns['owner'] = __( 'Owner', 'az-ai-theme' );
    $new_columns['domain'] = __( 'Domain', 'az-ai-theme' );
    $new_columns['plan'] = __( 'Plan', 'az-ai-theme' );
    $new_columns['status'] = __( 'Status', 'az-ai-theme' );
    $new_columns['usage'] = __( 'Token Usage', 'az-ai-theme' );
    $new_columns['date'] = __( 'Date', 'az-ai-theme' );
    return $new_columns;
}

add_action( 'manage_license_posts_custom_column', 'az_license_column_content', 10, 2 );
function az_license_column_content( $column, $post_id ) {
    switch ( $column ) {
        case 'license_key':
            $license_key = get_post_meta( $post_id, '_license_key', true );
            if ( $license_key ) {
                echo '<code style="background: #f0f0f0; padding: 3px 8px; border-radius: 3px; font-size: 11px;">' . esc_html( $license_key ) . '</code>';
            }
            break;
        case 'owner':
            $author_id = get_post_field( 'post_author', $post_id );
            $author = get_userdata( $author_id );
            if ( $author ) {
                echo '<strong>' . esc_html( $author->display_name ) . '</strong><br>';
                echo '<small style="color: #666;">' . esc_html( $author->user_email ) . '</small>';
            }
            break;
        case 'domain':
            $domain = get_post_meta( $post_id, '_license_domain', true );
            if ( $domain ) {
                echo '<code style="background: #e3f2fd; color: #1976d2; padding: 3px 8px; border-radius: 3px; font-size: 11px;">' . esc_html( $domain ) . '</code>';
            } else {
                echo '<em style="color: #999;">Not activated</em>';
            }
            break;
        case 'plan':
            $plan = get_post_meta( $post_id, '_plan_slug', true );
            if ( ! $plan ) {
                $plan = get_post_meta( $post_id, '_plan_type', true );
            }
            if ( $plan ) {
                $plan_colors = array(
                    'free' => '#9e9e9e',
                    'starter' => '#9e9e9e',
                    'pro' => '#2196f3',
                    'professional' => '#2196f3',
                    'business' => '#627D47',
                    'enterprise' => '#627D47',
                );
                $color = isset( $plan_colors[ $plan ] ) ? $plan_colors[ $plan ] : '#9e9e9e';
                echo '<span style="background: ' . $color . '; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight:600;">' . esc_html( strtoupper( $plan ) ) . '</span>';
            }
            break;
        case 'status':
            $status = get_post_meta( $post_id, '_license_status', true );
            if ( ! $status ) {
                $status = get_post_meta( $post_id, '_status', true );
            }
            if ( empty( $status ) ) {
                $status = 'active';
            }
            $status_colors = array(
                'active' => 'green',
                'expired' => 'red',
                'suspended' => 'gray',
            );
            $color = isset( $status_colors[ $status ] ) ? $status_colors[ $status ] : 'gray';
            echo '<span style="background: ' . $color . '; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight:600;">' . esc_html( strtoupper( $status ) ) . '</span>';
            break;
        case 'usage':
            $tokens_used = (int) get_post_meta( $post_id, '_tokens_used', true );
            $tokens_limit = (int) get_post_meta( $post_id, '_tokens_limit', true );
            
            if ( $tokens_limit > 0 ) {
                $percentage = round( ( $tokens_used / $tokens_limit ) * 100 );
                $bar_color = $percentage > 80 ? '#f44336' : ( $percentage > 50 ? '#ff9800' : '#627D47' );
                
                echo '<div style="width: 100px;">';
                echo '<div style="background: #e0e0e0; height: 8px; border-radius: 4px; overflow: hidden;">';
                echo '<div style="background: ' . $bar_color . '; width: ' . min( $percentage, 100 ) . '%; height: 100%;"></div>';
                echo '</div>';
                echo '<small style="color: #666; font-size: 10px;">' . number_format( $tokens_used ) . ' / ' . number_format( $tokens_limit ) . '</small>';
                echo '</div>';
            } else {
                echo '<em style="color: #999;">No limit</em>';
            }
            break;
    }
}

/**
 * License Details Meta Box Callback
 */
function az_license_details_callback( $post ) {
    wp_nonce_field( 'az_save_license_details', 'az_license_nonce' );
    
    $license_key = get_post_meta( $post->ID, '_license_key', true );
    $domain = get_post_meta( $post->ID, '_license_domain', true );
    $status = get_post_meta( $post->ID, '_license_status', true );
    $plan_type = get_post_meta( $post->ID, '_plan_type', true );
    $plan_slug = get_post_meta( $post->ID, '_plan_slug', true );
    $tokens_used = get_post_meta( $post->ID, '_tokens_used', true );
    $tokens_limit = get_post_meta( $post->ID, '_tokens_limit', true );
    
    // Get owner info
    $author_id = get_post_field( 'post_author', $post->ID );
    $author = get_userdata( $author_id );
    
    // Generate license key if empty
    if ( empty( $license_key ) ) {
        $license_key = 'AZ-' . strtoupper( substr( md5( uniqid() ), 0, 3 ) ) . '-' . rand( 1000, 9999 ) . '-' . strtoupper( substr( md5( uniqid() ), 0, 3 ) ) . '-' . date( 'Y' );
    }
    
    // Use plan_slug if available, otherwise plan_type
    $current_plan = ! empty( $plan_slug ) ? $plan_slug : $plan_type;
    ?>
    <table class="form-table">
        <tr>
            <th colspan="2" style="background: #f5f5f5; padding: 12px;"><h3 style="margin: 0;">👤 Owner Information</h3></th>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Owner Name', 'az-ai-theme' ); ?></label></th>
            <td>
                <strong style="font-size: 14px;"><?php echo esc_html( $author->display_name ); ?></strong>
                <p class="description"><?php esc_html_e( 'License owner (cannot be changed)', 'az-ai-theme' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Owner Email', 'az-ai-theme' ); ?></label></th>
            <td>
                <code style="background: #f0f0f0; padding: 6px 10px; border-radius: 4px; font-size: 13px;"><?php echo esc_html( $author->user_email ); ?></code>
            </td>
        </tr>
        <tr>
            <th colspan="2" style="background: #f5f5f5; padding: 12px;"><h3 style="margin: 0;">🔑 License Information</h3></th>
        </tr>
        <tr>
            <th><label for="license_key"><?php esc_html_e( 'License Key', 'az-ai-theme' ); ?></label></th>
            <td>
                <input type="text" id="license_key" name="license_key" value="<?php echo esc_attr( $license_key ); ?>" class="regular-text" readonly style="font-family: monospace; background: #f9f9f9;" />
                <p class="description"><?php esc_html_e( 'Auto-generated unique license key', 'az-ai-theme' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="license_domain"><?php esc_html_e( 'Activated Domain', 'az-ai-theme' ); ?></label></th>
            <td>
                <input type="text" id="license_domain" name="license_domain" value="<?php echo esc_attr( $domain ); ?>" class="regular-text" placeholder="example.com" />
                <p class="description"><?php esc_html_e( 'Domain where this license is activated (leave empty if not activated yet)', 'az-ai-theme' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="license_status"><?php esc_html_e( 'Status', 'az-ai-theme' ); ?></label></th>
            <td>
                <select id="license_status" name="license_status" style="min-width: 200px;">
                    <option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( '✅ Active', 'az-ai-theme' ); ?></option>
                    <option value="expired" <?php selected( $status, 'expired' ); ?>><?php esc_html_e( '❌ Expired', 'az-ai-theme' ); ?></option>
                    <option value="suspended" <?php selected( $status, 'suspended' ); ?>><?php esc_html_e( '⏸️ Suspended', 'az-ai-theme' ); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="plan_slug"><?php esc_html_e( 'Plan Type', 'az-ai-theme' ); ?></label></th>
            <td>
                <select id="plan_slug" name="plan_slug" style="min-width: 200px;">
                    <option value="free" <?php selected( $current_plan, 'free' ); ?>><?php esc_html_e( 'Free', 'az-ai-theme' ); ?></option>
                    <option value="starter" <?php selected( $current_plan, 'starter' ); ?>><?php esc_html_e( 'Starter', 'az-ai-theme' ); ?></option>
                    <option value="pro" <?php selected( $current_plan, 'pro' ); ?>><?php esc_html_e( 'Pro', 'az-ai-theme' ); ?></option>
                    <option value="professional" <?php selected( $current_plan, 'professional' ); ?>><?php esc_html_e( 'Professional', 'az-ai-theme' ); ?></option>
                    <option value="business" <?php selected( $current_plan, 'business' ); ?>><?php esc_html_e( 'Business', 'az-ai-theme' ); ?></option>
                    <option value="enterprise" <?php selected( $current_plan, 'enterprise' ); ?>><?php esc_html_e( 'Enterprise', 'az-ai-theme' ); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th colspan="2" style="background: #f5f5f5; padding: 12px;"><h3 style="margin: 0;">📊 Usage Statistics</h3></th>
        </tr>
        <tr>
            <th><label for="tokens_used"><?php esc_html_e( 'Tokens Used', 'az-ai-theme' ); ?></label></th>
            <td>
                <input type="number" id="tokens_used" name="tokens_used" value="<?php echo esc_attr( $tokens_used ?: 0 ); ?>" class="regular-text" />
                <p class="description"><?php esc_html_e( 'Number of tokens used', 'az-ai-theme' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="tokens_limit"><?php esc_html_e( 'Tokens Limit', 'az-ai-theme' ); ?></label></th>
            <td>
                <input type="number" id="tokens_limit" name="tokens_limit" value="<?php echo esc_attr( $tokens_limit ?: 1000000 ); ?>" class="regular-text" />
                <p class="description"><?php esc_html_e( 'Maximum tokens allowed', 'az-ai-theme' ); ?></p>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Save License Meta Data
 */
function az_save_license_details( $post_id ) {
    if ( ! isset( $_POST['az_license_nonce'] ) || ! wp_verify_nonce( $_POST['az_license_nonce'], 'az_save_license_details' ) ) {
        return;
    }
    
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    
    $fields = array(
        '_license_key'    => 'sanitize_text_field',
        '_license_domain' => 'sanitize_text_field',
        '_license_status' => 'sanitize_text_field',
        '_plan_type'      => 'sanitize_text_field',
        '_plan_slug'      => 'sanitize_text_field',
        '_tokens_used'    => 'absint',
        '_tokens_limit'   => 'absint',
    );
    
    foreach ( $fields as $meta_key => $sanitize_callback ) {
        $field_name = ltrim( $meta_key, '_' );
        if ( isset( $_POST[ $field_name ] ) ) {
            $value = call_user_func( $sanitize_callback, $_POST[ $field_name ] );
            update_post_meta( $post_id, $meta_key, $value );
            
            // Sync _license_status to _status for My Licenses page compatibility
            if ( $meta_key === '_license_status' ) {
                update_post_meta( $post_id, '_status', $value );
            }
        }
    }
}
add_action( 'save_post_license', 'az_save_license_details' );

