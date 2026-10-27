<?php
defined( 'ABSPATH' ) || exit;
function az_google_ready() {
    return az_get_option( 'google.enabled', 0 ) && az_get_option( 'google.login_enabled', 0 ) && az_get_option( 'google.client_id', '' ) && az_get_option( 'google.client_secret', '' );
}
function az_google_button() {
    if ( ! az_google_ready() || is_user_logged_in() ) { return ''; }
    return '<p><a class="button az-google-login" href="' . esc_url( admin_url( 'admin-post.php?action=az_google_start' ) ) . '">Đăng nhập bằng Google</a></p>';
}
add_shortcode( 'google-login', 'az_google_button' );
add_shortcode( 'az_google_login', 'az_google_button' );
add_action( 'login_form', function () { if ( az_get_option( 'google.show_login', 1 ) ) { echo az_google_button(); } } );
function az_google_cookie( $value, $expiry ) {
    setcookie( 'az_google_state', $value, array( 'expires' => $expiry, 'path' => '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax' ) );
}
function az_google_start() {
    if ( ! az_google_ready() || is_user_logged_in() ) { wp_die( 'Đăng nhập Google chưa sẵn sàng.' ); }
    nocache_headers();
    if ( ! az_auth_rate_allowed( 'google-start', az_auth_client_identity(), 10, 600 ) ) { wp_die( 'Quá nhiều yêu cầu. Vui lòng thử lại sau 10 phút.', '', array( 'response' => 429 ) ); }
    $state = bin2hex( random_bytes( 32 ) ); $verifier = bin2hex( random_bytes( 32 ) );
    set_transient( 'az_google_' . hash( 'sha256', $state ), $verifier, 600 );
    az_google_cookie( $state, time() + 600 );
    $url = add_query_arg( array( 'client_id' => az_get_option( 'google.client_id', '' ), 'redirect_uri' => admin_url( 'admin-post.php?action=az_google_callback' ), 'response_type' => 'code', 'scope' => 'openid email profile', 'state' => $state, 'code_challenge' => rtrim( strtr( base64_encode( hash( 'sha256', $verifier, true ) ), '+/', '-_' ), '=' ), 'code_challenge_method' => 'S256', 'prompt' => 'select_account' ), 'https://accounts.google.com/o/oauth2/v2/auth' );
    wp_redirect( $url ); exit;
}
function az_google_valid_identity( $data ) {
    return is_array( $data ) && ! empty( $data['sub'] ) && is_string( $data['sub'] ) && ! empty( $data['email'] ) && is_email( $data['email'] ) && true === ( $data['email_verified'] ?? false );
}
function az_google_callback() {
    if ( ! az_google_ready() || is_user_logged_in() ) { wp_die( 'Đăng nhập Google không khả dụng.' ); }
    nocache_headers();
    if ( ! az_auth_rate_allowed( 'google-callback', az_auth_client_identity(), 15, 600 ) ) { wp_die( 'Quá nhiều yêu cầu xác thực.', '', array( 'response' => 429 ) ); }
    $state = sanitize_text_field( wp_unslash( $_GET['state'] ?? '' ) );
    $cookie = sanitize_text_field( wp_unslash( $_COOKIE['az_google_state'] ?? '' ) );
    if ( ! preg_match( '/^[a-f0-9]{64}$/', $state ) || ! hash_equals( $state, $cookie ) ) { wp_die( 'Phiên Google không hợp lệ. Vui lòng thử lại.' ); }
    $key = 'az_google_' . hash( 'sha256', $state ); $verifier = get_transient( $key );
    delete_transient( $key ); az_google_cookie( '', time() - 3600 );
    if ( ! $verifier || ! empty( $_GET['error'] ) || empty( $_GET['code'] ) ) { wp_die( 'Phiên Google đã hết hạn hoặc bị hủy.' ); }
    $response = wp_remote_post( 'https://oauth2.googleapis.com/token', array( 'timeout' => 15, 'body' => array( 'code' => sanitize_text_field( wp_unslash( $_GET['code'] ) ), 'client_id' => az_get_option( 'google.client_id', '' ), 'client_secret' => az_get_option( 'google.client_secret', '' ), 'redirect_uri' => admin_url( 'admin-post.php?action=az_google_callback' ), 'grant_type' => 'authorization_code', 'code_verifier' => $verifier ) ) );
    if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) { wp_die( 'Không xác thực được với Google. Kiểm tra cấu hình OAuth.' ); }
    $token = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( empty( $token['access_token'] ) || ! is_string( $token['access_token'] ) ) { wp_die( 'Google không trả về token hợp lệ.' ); }
    $response = wp_remote_get( 'https://openidconnect.googleapis.com/v1/userinfo', array( 'timeout' => 15, 'headers' => array( 'Authorization' => 'Bearer ' . $token['access_token'] ) ) );
    if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) { wp_die( 'Không đọc được hồ sơ Google.' ); }
    $identity = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( ! az_google_valid_identity( $identity ) ) { wp_die( 'Email Google chưa được xác minh.' ); }
    $users = get_users( array( 'meta_key' => 'az_google_sub', 'meta_value' => $identity['sub'], 'number' => 2 ) );
    if ( count( $users ) > 1 ) { wp_die( 'Liên kết tài khoản bị trùng. Liên hệ quản trị viên.' ); }
    if ( $users ) { $user = $users[0]; }
    else {
        if ( email_exists( $identity['email'] ) ) { wp_die( 'Email đã có tài khoản. Hãy đăng nhập bằng mật khẩu; không tự liên kết qua Google.' ); }
        if ( ! az_get_option( 'google.allow_registration', 0 ) ) { wp_die( 'Website chưa cho phép đăng ký qua Google.' ); }
        if ( ! az_auth_rate_allowed( 'google-register', az_auth_client_identity(), 3, 3600 ) ) { wp_die( 'Đã đạt giới hạn đăng ký. Thử lại sau một giờ.', '', array( 'response' => 429 ) ); }
        $id = wp_insert_user( array( 'user_login' => 'google_' . substr( hash( 'sha256', $identity['sub'] ), 0, 32 ), 'user_pass' => wp_generate_password( 32, true, true ), 'user_email' => $identity['email'], 'display_name' => sanitize_text_field( $identity['name'] ?? $identity['email'] ), 'role' => 'subscriber' ) );
        if ( is_wp_error( $id ) ) { wp_die( 'Không tạo được tài khoản. Vui lòng thử lại.' ); }
        update_user_meta( $id, 'az_google_sub', $identity['sub'] ); $user = get_user_by( 'id', $id );
    }
    $allowed = apply_filters( 'az_google_allow_login', true, $user );
    if ( is_wp_error( $allowed ) || ! $allowed || ( is_multisite() && ( ! empty( $user->spam ) || ! empty( $user->deleted ) || ! is_user_member_of_blog( $user->ID ) ) ) ) { wp_die( 'Tài khoản không được phép đăng nhập.' ); }
    wp_set_current_user( $user->ID ); wp_set_auth_cookie( $user->ID, false, is_ssl() ); do_action( 'wp_login', $user->user_login, $user );
    wp_safe_redirect( home_url( '/' ) ); exit;
}
foreach ( array( 'start', 'callback' ) as $action ) {
    add_action( 'admin_post_nopriv_az_google_' . $action, 'az_google_' . $action );
    add_action( 'admin_post_az_google_' . $action, 'az_google_' . $action );
}
function az_google_captcha_ready() {
    return az_get_option( 'google.enabled', 0 ) && 'v2' === az_get_option( 'google.captcha_type', 'none' ) && az_get_option( 'google.site_key', '' ) && az_get_option( 'google.captcha_secret', '' );
}
add_action( 'login_enqueue_scripts', function () {
    if ( az_google_captcha_ready() ) { wp_enqueue_script( 'az-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true ); }
} );
add_action( 'login_form', function () {
    if ( az_google_captcha_ready() ) { echo '<div class="g-recaptcha" data-sitekey="' . esc_attr( az_get_option( 'google.site_key', '' ) ) . '"></div>'; }
} );
function az_google_verify_captcha( $user, $password ) {
    global $pagenow;
    if ( ! az_google_captcha_ready() || 'wp-login.php' !== $pagenow || 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || is_wp_error( $user ) ) { return $user; }
    $token = sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ?? '' ) );
    if ( ! $token ) { return new WP_Error( 'az_captcha_required', 'Vui lòng xác minh reCAPTCHA.' ); }
    $response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array( 'timeout' => 10, 'body' => array( 'secret' => az_get_option( 'google.captcha_secret', '' ), 'response' => $token ) ) );
    if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) { return new WP_Error( 'az_captcha_unavailable', 'Không kết nối được reCAPTCHA. Vui lòng thử lại.' ); }
    $data = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( empty( $data['success'] ) || strtolower( $data['hostname'] ?? '' ) !== strtolower( (string) wp_parse_url( site_url(), PHP_URL_HOST ) ) ) { return new WP_Error( 'az_captcha_invalid', 'reCAPTCHA không hợp lệ hoặc đã hết hạn.' ); }
    return $user;
}
add_filter( 'wp_authenticate_user', 'az_google_verify_captcha', 99, 2 );
