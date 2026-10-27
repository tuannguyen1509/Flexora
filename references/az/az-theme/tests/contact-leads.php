<?php
/** Contact AJAX to AZ9S persistence regression; database/mail APIs are simulated. */
define( 'ABSPATH', __DIR__ );
$hooks = array(); $store = array(); $transients = array(); $mail_calls = array(); $mail_result = true;
function add_action( $hook, $callback, ...$args ) { $GLOBALS['hooks'][ $hook ][] = $callback; }
function add_filter( ...$args ) {}
function remove_action( ...$args ) {}
function get_option( $key, $default = null ) { return $GLOBALS['store'][ $key ] ?? $default; }
function update_option( $key, $value, ...$args ) { $GLOBALS['store'][ $key ] = $value; }
function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function sanitize_textarea_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function sanitize_email( $value ) { return filter_var( $value, FILTER_VALIDATE_EMAIL ) ? $value : ''; }
function is_email( $value ) { return sanitize_email( $value ); }
function wp_unslash( $value ) { return stripslashes( $value ); }
function wp_verify_nonce( $value, $action ) { return 'valid' === $value && 'az_contact_form' === $action; }
function get_transient( $key ) { return $GLOBALS['transients'][ $key ] ?? false; }
function delete_transient( $key ) { unset( $GLOBALS['transients'][ $key ] ); }
function current_time( $format ) { return '2026-10-06 10:00:00'; }
function wp_timezone() { return new DateTimeZone( 'Asia/Ho_Chi_Minh' ); }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, $args ); }
function esc_html( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function az9s_get_notification_email() { return array( 'leads@example.test' ); }
function wp_mail( ...$args ) { $GLOBALS['mail_calls'][] = $args; return $GLOBALS['mail_result']; }
class ContactJsonResponse extends RuntimeException {
    public $success; public $data;
    public function __construct( $success, $data ) { $this->success = $success; $this->data = $data; }
}
function wp_send_json_success( $data ) { throw new ContactJsonResponse( true, $data ); }
function wp_send_json_error( $data ) { throw new ContactJsonResponse( false, $data ); }
class ContactLeadDatabase {
    public $prefix = 'custom_'; public $insert_id = 777;
    public $attempts = array(); public $rows = array(); public $mode = 'ok';
    public function prepare( $query, ...$args ) { return $query; }
    public function get_var( $query ) { return true; }
    public function insert( $table, $data, $formats ) {
        $this->attempts[] = array( $table, $data );
        if ( 'failed' === $this->mode || ( 'old-schema' === $this->mode && isset( $data['form_id'] ) ) ) { return false; }
        if ( $table !== $this->prefix . 'az9s_leads' ) { throw new RuntimeException( 'Wrong destination table' ); }
        if ( count( $formats ) !== count( $data ) ) { throw new RuntimeException( 'Wrong column formats' ); }
        $this->insert_id = count( $this->rows ) + 1;
        $this->rows[] = $data;
        return 1;
    }
}
$wpdb = new ContactLeadDatabase();
require dirname( __DIR__ ) . '/inc/functions/admin-settings.php';
require dirname( __DIR__ ) . '/inc/functions/contact-form.php';
require dirname( __DIR__ ) . '/inc/modules/leads/inc/leads-manager.php';
function check( $ok, $label ) { if ( ! $ok ) { fwrite( STDERR, "FAIL $label\n" ); exit( 1 ); } echo "PASS $label\n"; }
function contact_request( $overrides = array(), $new_captcha = true ) {
    $_POST = array_merge( array(
        'az_contact_nonce' => 'valid', 'name' => '<b>Khách hàng</b>', 'phone' => '0981368995',
        'email' => 'customer@example.test', 'topic' => 'Lắp đặt', 'message' => "Tư vấn thiết bị\nVà vật tư",
        'source' => 'Trang liên hệ', 'captcha_token' => 'token', 'captcha_answer' => 'abc23',
    ), $overrides );
    if ( $new_captcha ) { $GLOBALS['transients']['az_contact_captcha_token'] = 'ABC23'; }
    try { az_handle_contact_form(); } catch ( ContactJsonResponse $response ) { return $response; }
    throw new RuntimeException( 'Handler did not respond' );
}
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
check( in_array( 'az_handle_contact_form', $hooks['wp_ajax_az_contact_form_submit'], true ) && in_array( 'az_handle_contact_form', $hooks['wp_ajax_nopriv_az_contact_form_submit'], true ), 'Logged-in and visitor AJAX endpoints' );
$response = contact_request();
check( $response->success && 1 === count( $wpdb->rows ), 'Valid request saves exactly one AZ9S lead' );
$row = $wpdb->rows[0];
check( 'Khách hàng' === $row['name'] && '0981368995' === $row['phone'] && 'customer@example.test' === $row['email'], 'Contact identity mapped and sanitized' );
check( 'az-contact-page' === $row['form_id'] && 'Trang liên hệ' === $row['form_title'] && 'new' === $row['status'], 'Source and status visible to Leads manager' );
check( "Chủ đề: Lắp đặt\nTư vấn thiết bị\nVà vật tư" === $row['message'], 'Topic and multiline message preserved' );
check( '127.0.0.1' === $row['ip'] && current_time( 'mysql' ) === $row['created_at'], 'IP and server timestamp stored' );
check( $response->data['lead_id'] === 1 && $response->data['mail_sent'], 'AJAX returns stored ID and mail result' );
check( count( $mail_calls ) === 1 && $mail_calls[0][0] === array( 'leads@example.test' ) && strpos( $mail_calls[0][2], 'Lắp đặt' ) !== false, 'One AZ9S notification with topic and configured recipients' );
$mail_result = false;
$response = contact_request();
check( $response->success && ! $response->data['mail_sent'] && count( $wpdb->rows ) === 2, 'SMTP failure still retains the lead' );
$rows_before = count( $wpdb->rows ); $mail_before = count( $mail_calls );
$wpdb->mode = 'failed'; $wpdb->insert_id = 777;
$response = contact_request();
check( ! $response->success && count( $wpdb->rows ) === $rows_before && count( $mail_calls ) === $mail_before, 'DB failure does not claim success or mail a nonexistent lead' );
$wpdb->mode = 'old-schema';
$response = contact_request();
$row = end( $wpdb->rows );
check( $response->success && ! isset( $row['form_id'] ) && strpos( $row['message'], 'Tên form: Trang liên hệ' ) === 0, 'Older AZ9S schema keeps source in message' );
$wpdb->mode = 'ok';
$attempts_before = count( $wpdb->attempts );
foreach ( array( array( 'az_contact_nonce' => 'invalid' ), array( 'name' => '' ), array( 'message' => '' ), array( 'captcha_answer' => 'wrong' ) ) as $invalid ) {
    $response = contact_request( $invalid );
    check( ! $response->success && count( $wpdb->attempts ) === $attempts_before, 'Invalid nonce/required field/CAPTCHA rejected before insert' );
}
$response = contact_request( array(), false );
check( ! $response->success && count( $wpdb->attempts ) === $attempts_before, 'Consumed CAPTCHA cannot be reused' );
$response = contact_request( array( 'topic' => '', 'source' => '', 'email' => '' ) );
$row = end( $wpdb->rows );
check( $response->success && 'Form liên hệ' === $row['form_title'] && strpos( $row['message'], 'Chủ đề:') === false && '' === $row['email'], 'Optional fields and default source supported' );
