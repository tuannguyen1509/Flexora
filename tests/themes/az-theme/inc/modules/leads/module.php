<?php
/**
 * Module: AZ9S Leads integrated into AZ Theme
 * Description: Quan ly Lead, bieu mau Lead va SMTP thong bao doc lap cho website WordPress.
 * Version: 1.2.3
 * Author: AZ9S
 * Text Domain: az9s-leads
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'AZ9S_LEADS_VERSION', '1.2.3' );
define( 'AZ9S_LEADS_DB_VERSION', '1.0.0' );
define( 'AZ9S_LEADS_PLUGIN_FILE', __FILE__ );
define( 'AZ9S_LEADS_PLUGIN_DIR', __DIR__ . '/' );
define( 'AZ9S_LEADS_PLUGIN_URL', get_template_directory_uri() . '/inc/modules/leads/' );

function az9s_leads_plugin_load() {
    if ( ! function_exists( 'az9s_create_leads_table' ) ) {
        require_once AZ9S_LEADS_PLUGIN_DIR . 'inc/leads-manager.php';
    }

    if ( ! function_exists( 'az9s_get_lead_forms' ) ) {
        require_once AZ9S_LEADS_PLUGIN_DIR . 'inc/lead-forms.php';
    }
}
az9s_leads_plugin_load();

function az9s_leads_activate() {
    az9s_leads_plugin_load();

    if ( function_exists( 'az9s_create_leads_table' ) ) {
        az9s_create_leads_table();
    }
    if ( function_exists( 'az9s_upgrade_leads_table' ) ) {
        az9s_upgrade_leads_table();
    }

    update_option( 'az9s_leads_db_version', AZ9S_LEADS_DB_VERSION, false );
    if ( false === get_option( 'az9s_lead_notification_emails', false ) ) {
        update_option( 'az9s_lead_notification_emails', get_option( 'admin_email' ), false );
    }
}

function az9s_leads_deactivate() {
    delete_option( 'az9s_last_lead_mail_status' );
    delete_option( 'az9s_last_smtp_test_status' );
}

function az9s_leads_maybe_upgrade() {
    az9s_leads_plugin_load();

    if ( get_option( 'az9s_leads_db_version' ) !== AZ9S_LEADS_DB_VERSION ) {
        if ( function_exists( 'az9s_upgrade_leads_table' ) ) {
            az9s_upgrade_leads_table();
        }
        update_option( 'az9s_leads_db_version', AZ9S_LEADS_DB_VERSION, false );
    }
}
add_action( 'init', 'az9s_leads_maybe_upgrade', 5 );

if ( ! function_exists( 'az9s_get_notification_email' ) ) {
    function az9s_get_notification_email() {
        $emails = get_option( 'az9s_lead_notification_emails', '' );
        if ( is_string( $emails ) && '' !== trim( $emails ) ) {
            return array_filter( array_map( 'trim', preg_split( '/[\r\n,;]+/', $emails ) ), 'is_email' );
        }

        return [ get_option( 'admin_email' ) ];
    }
}

if ( ! function_exists( 'az9s_get_smtp_settings' ) ) {
    function az9s_get_smtp_settings() {
        $defaults = [
            'enabled'    => 0,
            'host'       => '',
            'port'       => 587,
            'encryption' => 'tls',
            'auth'       => 1,
            'username'   => '',
            'password'   => '',
            'from_email' => get_option( 'admin_email' ),
            'from_name'  => get_bloginfo( 'name' ),
        ];

        $settings = get_option( 'az_smtp_settings', [] );
        return wp_parse_args( is_array( $settings ) ? $settings : [], $defaults );
    }
}

add_action( 'phpmailer_init', function( $phpmailer ) {
    $settings = az9s_get_smtp_settings();

    if ( empty( $settings['enabled'] ) || empty( $settings['host'] ) ) {
        return;
    }

    $phpmailer->isSMTP();
    $phpmailer->Host       = $settings['host'];
    $phpmailer->Port       = ! empty( $settings['port'] ) ? (int) $settings['port'] : 587;
    $phpmailer->SMTPAuth   = ! empty( $settings['auth'] );
    $phpmailer->Username   = (string) $settings['username'];
    $phpmailer->Password   = (string) $settings['password'];
    $phpmailer->SMTPSecure = in_array( $settings['encryption'], [ 'ssl', 'tls' ], true ) ? $settings['encryption'] : '';

    if ( ! empty( $settings['from_email'] ) && is_email( $settings['from_email'] ) ) {
        $from_name = ! empty( $settings['from_name'] ) ? $settings['from_name'] : get_bloginfo( 'name' );
        $phpmailer->setFrom( $settings['from_email'], $from_name, false );
    }
} );
