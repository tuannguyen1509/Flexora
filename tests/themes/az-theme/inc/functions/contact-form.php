<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─── Contact Form AJAX handler ────────────────────────────────────────────────
function az_contact_generate_captcha_code() {
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code     = '';

    for ( $i = 0; $i < 5; $i++ ) {
        $code .= $alphabet[ wp_rand( 0, strlen( $alphabet ) - 1 ) ];
    }

    return $code;
}

function az_contact_create_captcha() {
    $token = wp_generate_uuid4();
    $code  = az_contact_generate_captcha_code();

    set_transient( 'az_contact_captcha_' . $token, $code, 20 * MINUTE_IN_SECONDS );

    return array(
        'token' => $token,
        'code'  => $code,
    );
}

function az_contact_verify_captcha( $token, $answer ) {
    $token  = sanitize_text_field( $token );
    $answer = strtoupper( preg_replace( '/\s+/', '', sanitize_text_field( $answer ) ) );

    if ( ! $token || ! $answer ) {
        return false;
    }

    $transient_key = 'az_contact_captcha_' . $token;
    $expected      = get_transient( $transient_key );

    delete_transient( $transient_key );

    return $expected && hash_equals( (string) $expected, $answer );
}

add_action( 'wp_ajax_az_contact_refresh_captcha',        'az_contact_refresh_captcha' );
add_action( 'wp_ajax_nopriv_az_contact_refresh_captcha', 'az_contact_refresh_captcha' );
function az_contact_refresh_captcha() {
    if ( ! isset( $_POST['az_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['az_contact_nonce'] ) ), 'az_contact_form' ) ) {
        wp_send_json_error( 'Phiên làm việc hết hạn. Vui lòng tải lại trang.' );
    }

    wp_send_json_success( az_contact_create_captcha() );
}

add_action( 'wp_ajax_az_contact_form_submit',        'az_handle_contact_form' );
add_action( 'wp_ajax_nopriv_az_contact_form_submit', 'az_handle_contact_form' );
function az_handle_contact_form() {
    if ( ! isset( $_POST['az_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['az_contact_nonce'] ) ), 'az_contact_form' ) ) {
        wp_send_json_error('Phiên làm việc hết hạn. Vui lòng tải lại trang.');
    }
    $name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
    $phone   = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
    $email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
    $topic   = sanitize_text_field( wp_unslash( $_POST['topic'] ?? '' ) );
    $message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
    $source  = sanitize_text_field( wp_unslash( $_POST['source'] ?? 'Form liên hệ' ) );
    $captcha_token  = sanitize_text_field( wp_unslash( $_POST['captcha_token'] ?? '' ) );
    $captcha_answer = sanitize_text_field( wp_unslash( $_POST['captcha_answer'] ?? '' ) );

    if ( ! $name || ! $phone || ! $message ) {
        wp_send_json_error('Vui lòng điền đầy đủ thông tin bắt buộc.');
    }

    if ( ! az_contact_verify_captcha( $captcha_token, $captcha_answer ) ) {
        wp_send_json_error( 'Mã bảo vệ không đúng hoặc đã hết hạn. Vui lòng nhập lại mã mới.' );
    }

    $lead = array(
        'customer_name' => $name,
        'phone'         => $phone,
        'email'         => $email,
        'topic'         => $topic,
        'message'       => $message,
        'source'        => $source ?: 'Form liên hệ',
    );

    $lead_id = az_insert_contact_lead( $lead );
    if ( ! $lead_id ) {
        wp_send_json_error( 'Không thể lưu thông tin. Vui lòng thử lại.' );
    }

    $sent = function_exists( 'az9s_send_lead_notification_email' )
        ? az9s_send_lead_notification_email( az_contact_lead_manager_data( $lead ) )
        : az_send_lead_notification_email( $lead );
    if ( $sent ) {
        wp_send_json_success(
            array(
                'message'   => 'Gửi thành công!',
                'lead_id'   => $lead_id,
                'mail_sent' => true,
            )
        );
    }

    wp_send_json_success(
        array(
            'message'   => 'Đã nhận thông tin. Chúng tôi sẽ liên hệ lại trong thời gian sớm nhất.',
            'lead_id'   => $lead_id,
            'mail_sent' => false,
        )
    );
}
