<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Classic Editor
// Respect the site's editor preference; do not disable Gutenberg globally.

// Thay doi duong dan logo admin
    function wpc_url_login(){
     return home_url( '/' );
}
     add_filter('login_headerurl', 'wpc_url_login');

// Footer admin: "Bởi TN Digi Solution"
add_filter( 'admin_footer_text', function() {
    return 'Bởi <a href="https://tndigi.com" target="_blank" rel="noopener" style="color:#25aff4;text-decoration:none;font-weight:600;">TN Digi Solution</a>';
} );

add_action( 'admin_menu', function() {
    add_options_page(
        'Lead liên hệ',
        'Lead liên hệ',
        'manage_options',
        'az-contact-leads',
        'az_contact_leads_page'
    );

    // Keep the legacy Leads URL reachable without a duplicate Settings entry.
    remove_submenu_page( 'options-general.php', 'az-contact-leads' );
} );

add_action( 'admin_init', function() {
    // Retired Header & Footer bookmarks now lead to the parent's only editor.
    if ( 'az-hfc-settings' === sanitize_key( wp_unslash( $_GET['page'] ?? '' ) ) && current_user_can( 'manage_options' ) ) {
        wp_safe_redirect( admin_url( 'admin.php?page=az-theme-advanced&section=custom_code' ) );
        exit;
    }

    register_setting( 'az_lead_smtp_group', 'az_lead_smtp_enabled',      [ 'sanitize_callback' => 'absint' ] );
    register_setting( 'az_lead_smtp_group', 'az_lead_notify_emails',     [ 'sanitize_callback' => 'az_sanitize_email_list' ] );
    register_setting( 'az_lead_smtp_group', 'az_lead_smtp_host',         [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'az_lead_smtp_group', 'az_lead_smtp_port',         [ 'sanitize_callback' => 'absint' ] );
    register_setting( 'az_lead_smtp_group', 'az_lead_smtp_encryption',   [ 'sanitize_callback' => 'az_sanitize_smtp_encryption' ] );
    register_setting( 'az_lead_smtp_group', 'az_lead_smtp_username',     [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'az_lead_smtp_group', 'az_lead_smtp_password',     [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'az_lead_smtp_group', 'az_lead_smtp_from_email',   [ 'sanitize_callback' => 'sanitize_email' ] );
    register_setting( 'az_lead_smtp_group', 'az_lead_smtp_from_name',    [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'az_lead_smtp_group', 'az_lead_smtp_debug',        [ 'sanitize_callback' => 'absint' ] );
} );

// Custom sanitize function for iframe embed code
function az_sanitize_iframe( $input ) {
    // Allow iframe with specific attributes
    $allowed_html = [
        'iframe' => [
            'src'                   => true,
            'width'                 => true,
            'height'                => true,
            'style'                 => true,
            'frameborder'           => true,
            'allowfullscreen'       => true,
            'allow'                 => true,
            'loading'               => true,
            'referrerpolicy'        => true,
            'title'                 => true,
            'aria-hidden'           => true,
            'tabindex'              => true,
        ]
    ];
    return wp_kses( $input, $allowed_html );
}

function az_sanitize_email_list( $input ) {
    $emails = preg_split( '/[\s,;]+/', (string) wp_unslash( $input ) );
    $emails = array_filter( array_map( 'sanitize_email', $emails ) );
    $emails = array_filter( $emails, 'is_email' );

    return implode( ', ', array_unique( $emails ) );
}

function az_sanitize_smtp_encryption( $input ) {
    $input = strtolower( sanitize_text_field( (string) $input ) );

    return in_array( $input, array( 'none', 'ssl', 'tls' ), true ) ? $input : 'tls';
}

function az_lead_table_name() {
    global $wpdb;

    return $wpdb->prefix . 'az_contact_leads';
}

function az_create_lead_table() {
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $table_name      = az_lead_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    dbDelta(
        "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            customer_name VARCHAR(190) NOT NULL DEFAULT '',
            phone VARCHAR(80) NOT NULL DEFAULT '',
            email VARCHAR(190) NOT NULL DEFAULT '',
            message TEXT NULL,
            source VARCHAR(190) NOT NULL DEFAULT '',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY created_at (created_at),
            KEY email (email),
            KEY phone (phone)
        ) {$charset_collate};"
    );
}
add_action( 'after_switch_theme', 'az_create_lead_table' );
add_action( 'admin_init', 'az_create_lead_table' );

/** Map the contact page fields to the shared AZ9S lead schema. */
function az_contact_lead_manager_data( $lead ) {
    $topic = sanitize_text_field( $lead['topic'] ?? '' );
    $message = sanitize_textarea_field( $lead['message'] ?? '' );
    $source = sanitize_text_field( $lead['source'] ?? '' ) ?: 'Form liên hệ';
    return array(
        'name' => sanitize_text_field( $lead['customer_name'] ?? '' ),
        'phone' => sanitize_text_field( $lead['phone'] ?? '' ),
        'email' => sanitize_email( $lead['email'] ?? '' ),
        'message' => ( $topic ? 'Chủ đề: ' . $topic . "\n" : '' ) . $message,
        'form_id' => 'az-contact-page',
        'form_title' => $source,
        'status' => 'new',
        'ip' => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
        'created_at' => current_time( 'mysql' ),
    );
}

function az_insert_contact_lead( $lead ) {
    global $wpdb;
    if ( function_exists( 'az9s_upgrade_leads_table' ) ) {
        az9s_upgrade_leads_table();
    }
    $data = az_contact_lead_manager_data( $lead );
    $inserted = $wpdb->insert( $wpdb->prefix . 'az9s_leads', $data, array_fill( 0, count( $data ), '%s' ) );
    if ( ! $inserted ) {
        // Older standalone AZ9S tables may not allow adding the form columns.
        $data['message'] = 'Tên form: ' . $data['form_title'] . "\n" . $data['message'];
        unset( $data['form_id'], $data['form_title'] );
        $inserted = $wpdb->insert( $wpdb->prefix . 'az9s_leads', $data, array_fill( 0, count( $data ), '%s' ) );
    }
    return $inserted ? (int) $wpdb->insert_id : 0;
}

function az_get_lead_notify_emails() {
    $emails = az_sanitize_email_list( get_option( 'az_lead_notify_emails', '' ) );

    if ( ! $emails ) {
        $emails = sanitize_email( get_option( 'az_contact_email', get_option( 'admin_email' ) ) );
    }

    if ( ! $emails ) {
        $emails = sanitize_email( get_option( 'admin_email' ) );
    }

    return $emails;
}

function az_send_lead_notification_email( $lead ) {
    $to = az_get_lead_notify_emails();
    if ( ! $to ) {
        return false;
    }

    $name    = sanitize_text_field( $lead['customer_name'] ?? '' );
    $phone   = sanitize_text_field( $lead['phone'] ?? '' );
    $email   = sanitize_email( $lead['email'] ?? '' );
    $topic   = sanitize_text_field( $lead['topic'] ?? '' );
    $message = sanitize_textarea_field( $lead['message'] ?? '' );
    $source  = sanitize_text_field( $lead['source'] ?? 'Form liên hệ' );
    $site    = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

    $subject = sprintf( '[%s] Lead mới - %s', $site ?: 'Website', $source ?: 'Form liên hệ' );
    $body    = "Bạn có lead liên hệ mới từ website.\n\n";
    $body   .= "Nguồn: {$source}\n";
    $body   .= "Họ tên: {$name}\n";
    $body   .= "Điện thoại: {$phone}\n";
    if ( $email ) {
        $body .= "Email: {$email}\n";
    }
    if ( $topic ) {
        $body .= "Chủ đề: {$topic}\n";
    }
    $body .= "\nNội dung:\n{$message}\n\n";
    $body .= 'Thời gian: ' . current_time( 'd/m/Y H:i' ) . "\n";
    $body .= 'Website: ' . home_url( '/' );

    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
    if ( $email ) {
        $headers[] = sprintf( 'Reply-To: %s <%s>', $name ?: $email, $email );
    }

    return wp_mail( $to, $subject, $body, $headers );
}

add_action( 'phpmailer_init', function( $phpmailer ) {
    // The integrated AZ9S SMTP configuration takes precedence when enabled.
    if ( function_exists( 'az9s_get_smtp_settings' ) ) {
        $az9s_smtp = az9s_get_smtp_settings();
        if ( ! empty( $az9s_smtp['enabled'] ) && ! empty( $az9s_smtp['host'] ) ) { return; }
    }
    if ( ! get_option( 'az_lead_smtp_enabled', 0 ) ) {
        return;
    }

    $host = trim( (string) get_option( 'az_lead_smtp_host', '' ) );
    $port = (int) get_option( 'az_lead_smtp_port', 587 );
    $user = trim( (string) get_option( 'az_lead_smtp_username', '' ) );
    $pass = (string) get_option( 'az_lead_smtp_password', '' );

    if ( ! $host || ! $port ) {
        return;
    }

    $phpmailer->isSMTP();
    $phpmailer->Host       = $host;
    $phpmailer->Port       = $port;
    $phpmailer->SMTPAuth   = ( '' !== $user || '' !== $pass );
    $phpmailer->Username   = $user;
    $phpmailer->Password   = $pass;
    $phpmailer->SMTPDebug  = get_option( 'az_lead_smtp_debug', 0 ) ? 2 : 0;
    $phpmailer->Debugoutput = 'error_log';

    $encryption = az_sanitize_smtp_encryption( get_option( 'az_lead_smtp_encryption', 'tls' ) );
    if ( 'none' !== $encryption ) {
        $phpmailer->SMTPSecure = $encryption;
    }

    $from_email = sanitize_email( get_option( 'az_lead_smtp_from_email', '' ) );
    if ( ! $from_email && is_email( $user ) ) {
        $from_email = $user;
    }
    $from_name  = sanitize_text_field( get_option( 'az_lead_smtp_from_name', get_bloginfo( 'name' ) ) );
    if ( $from_email ) {
        $phpmailer->setFrom( $from_email, $from_name ?: get_bloginfo( 'name' ), false );
    }
} );

add_action( 'wp_mail_failed', function( $error ) {
    if ( is_wp_error( $error ) ) {
        error_log( '[AZ Lead Mail] ' . $error->get_error_message() );
    }
} );

function az_contact_leads_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    global $wpdb;

    az_create_lead_table();

    $active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'list';
    $active_tab = in_array( $active_tab, array( 'list', 'smtp' ), true ) ? $active_tab : 'list';
    $lead_notice = '';

    if ( 'list' === $active_tab && isset( $_GET['az_lead_action'], $_GET['lead_id'], $_GET['_wpnonce'] ) && 'delete' === sanitize_key( wp_unslash( $_GET['az_lead_action'] ) ) ) {
        $lead_id = absint( wp_unslash( $_GET['lead_id'] ) );

        if ( $lead_id && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'az_delete_lead_' . $lead_id ) ) {
            $deleted = $wpdb->delete( az_lead_table_name(), array( 'id' => $lead_id ), array( '%d' ) );
            if ( false !== $deleted && $deleted > 0 ) {
                $lead_notice = '<div class="notice notice-success is-dismissible"><p>Đã xoá lead.</p></div>';
            } else {
                $lead_notice = '<div class="notice notice-warning is-dismissible"><p>Không tìm thấy lead cần xoá hoặc lead đã được xoá trước đó.</p></div>';
            }
        } else {
            $lead_notice = '<div class="notice notice-error is-dismissible"><p>Không thể xoá lead vì liên kết không hợp lệ. Vui lòng tải lại trang và thử lại.</p></div>';
        }
    }

    if ( isset( $_POST['az_lead_test_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['az_lead_test_nonce'] ) ), 'az_lead_test_email' ) ) {
        $test_to = sanitize_email( wp_unslash( $_POST['az_lead_test_email'] ?? '' ) );
        if ( $test_to && wp_mail( $test_to, 'Email test Lead', "Đây là email test từ cấu hình SMTP Lead.\nWebsite: " . home_url( '/' ), array( 'Content-Type: text/plain; charset=UTF-8' ) ) ) {
            echo '<div class="notice notice-success is-dismissible"><p>Đã gửi email test Lead.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Không gửi được email test. Vui lòng kiểm tra cấu hình SMTP.</p></div>';
        }
    }

    $per_page = 50;
    $paged    = max( 1, absint( $_GET['paged'] ?? 1 ) );
    $offset   = ( $paged - 1 ) * $per_page;
    $total    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . az_lead_table_name() );
    $leads    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . az_lead_table_name() . " ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d", $per_page, $offset ) );
    $base_url = admin_url( 'admin.php?page=az-contact-leads' );
    ?>
    <div class="wrap az-lead-wrap">
        <h1>Lead liên hệ</h1>
        <h2 class="nav-tab-wrapper">
            <a href="<?php echo esc_url( $base_url ); ?>" class="nav-tab <?php echo 'list' === $active_tab ? 'nav-tab-active' : ''; ?>">Danh sách lead</a>
            <a href="<?php echo esc_url( add_query_arg( 'tab', 'smtp', $base_url ) ); ?>" class="nav-tab <?php echo 'smtp' === $active_tab ? 'nav-tab-active' : ''; ?>">SMTP</a>
        </h2>

        <?php if ( 'list' === $active_tab ) : ?>
            <?php echo wp_kses_post( $lead_notice ); ?>
            <p class="subsubsub"><strong>Tất cả (<?php echo esc_html( $total ); ?>)</strong></p>
            <table class="widefat striped az-lead-table">
                <thead>
                    <tr>
                        <th>Khách hàng</th>
                        <th>Số điện thoại</th>
                        <th>Email</th>
                        <th>Nội dung</th>
                        <th>Nguồn</th>
                        <th>Ngày gửi</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( $leads ) : ?>
                        <?php foreach ( $leads as $lead ) : ?>
                            <?php
                            $delete_url = wp_nonce_url(
                                add_query_arg(
                                    array(
                                        'page'           => 'az-contact-leads',
                                        'az_lead_action' => 'delete',
                                        'lead_id'        => (int) $lead->id,
                                        'paged'          => $paged,
                                    ),
                                    admin_url( 'admin.php' )
                                ),
                                'az_delete_lead_' . (int) $lead->id
                            );
                            ?>
                            <tr>
                                <td><?php echo esc_html( $lead->customer_name ); ?></td>
                                <td><?php echo esc_html( $lead->phone ); ?></td>
                                <td><?php echo esc_html( $lead->email ); ?></td>
                                <td><?php echo esc_html( wp_trim_words( $lead->message, 24, '...' ) ); ?></td>
                                <td><?php echo esc_html( $lead->source ); ?></td>
                                <td><?php echo esc_html( mysql2date( 'd/m/Y H:i', $lead->created_at ) ); ?></td>
                                <td><a class="az-lead-delete" href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('Bạn chắc chắn muốn xoá lead này?');">Xoá</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="7">Chưa có lead liên hệ.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php
            $total_pages = (int) ceil( $total / $per_page );
            if ( $total_pages > 1 ) {
                echo '<div class="tablenav"><div class="tablenav-pages">';
                echo wp_kses_post( paginate_links( array(
                    'base'      => add_query_arg( 'paged', '%#%', $base_url ),
                    'format'    => '',
                    'current'   => $paged,
                    'total'     => $total_pages,
                    'prev_text' => '&lsaquo;',
                    'next_text' => '&rsaquo;',
                ) ) );
                echo '</div></div>';
            }
            ?>
        <?php else : ?>
            <h2>Common SMTP Providers</h2>
            <div class="az-smtp-providers">
                <div><h3>Gmail</h3><p><strong>Host:</strong> <code>smtp.gmail.com</code><br><strong>Port:</strong> 587<br><strong>Encryption:</strong> TLS<br><strong>Note:</strong> Use App Password</p></div>
                <div><h3>Outlook/Hotmail</h3><p><strong>Host:</strong> <code>smtp-mail.outlook.com</code><br><strong>Port:</strong> 587<br><strong>Encryption:</strong> TLS<br><strong>Note:</strong> TLS</p></div>
                <div><h3>Yahoo</h3><p><strong>Host:</strong> <code>smtp.mail.yahoo.com</code><br><strong>Port:</strong> 587<br><strong>Encryption:</strong> TLS<br><strong>Note:</strong> Use App Password</p></div>
                <div><h3>SendGrid</h3><p><strong>Host:</strong> <code>smtp.sendgrid.net</code><br><strong>Port:</strong> 587<br><strong>Encryption:</strong> TLS<br><strong>Note:</strong> Username: apikey</p></div>
            </div>

            <form method="post" action="options.php" class="az-lead-smtp-form">
                <?php settings_fields( 'az_lead_smtp_group' ); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">Bật SMTP</th>
                        <td><input type="hidden" name="az_lead_smtp_enabled" value="0"><label><input type="checkbox" name="az_lead_smtp_enabled" value="1" <?php checked( get_option( 'az_lead_smtp_enabled', 0 ), 1 ); ?>> Dùng SMTP này để gửi email thông báo Lead và các email qua <code>wp_mail()</code>.</label></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="az_lead_notify_emails">Email nhận thông báo Lead</label></th>
                        <td><textarea id="az_lead_notify_emails" name="az_lead_notify_emails" rows="3" class="large-text"><?php echo esc_textarea( get_option( 'az_lead_notify_emails', get_option( 'az_contact_email', get_option( 'admin_email' ) ) ) ); ?></textarea><p class="description">Có thể nhập một hoặc nhiều email, phân tách bằng dấu phẩy.</p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="az_lead_smtp_host">SMTP Host <span class="required">*</span></label></th>
                        <td><input id="az_lead_smtp_host" type="text" name="az_lead_smtp_host" value="<?php echo esc_attr( get_option( 'az_lead_smtp_host', '' ) ); ?>" class="regular-text" placeholder="smtp.gmail.com"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="az_lead_smtp_port">SMTP Port <span class="required">*</span></label></th>
                        <td><input id="az_lead_smtp_port" type="number" name="az_lead_smtp_port" value="<?php echo esc_attr( get_option( 'az_lead_smtp_port', 587 ) ); ?>" class="small-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="az_lead_smtp_encryption">Encryption</label></th>
                        <td><select id="az_lead_smtp_encryption" name="az_lead_smtp_encryption"><option value="tls" <?php selected( get_option( 'az_lead_smtp_encryption', 'tls' ), 'tls' ); ?>>TLS</option><option value="ssl" <?php selected( get_option( 'az_lead_smtp_encryption', 'tls' ), 'ssl' ); ?>>SSL</option><option value="none" <?php selected( get_option( 'az_lead_smtp_encryption', 'tls' ), 'none' ); ?>>None</option></select></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="az_lead_smtp_username">SMTP Username <span class="required">*</span></label></th>
                        <td><input id="az_lead_smtp_username" type="text" name="az_lead_smtp_username" value="<?php echo esc_attr( get_option( 'az_lead_smtp_username', '' ) ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="az_lead_smtp_password">SMTP Password <span class="required">*</span></label></th>
                        <td><input id="az_lead_smtp_password" type="password" name="az_lead_smtp_password" value="<?php echo esc_attr( get_option( 'az_lead_smtp_password', '' ) ); ?>" class="regular-text" autocomplete="new-password"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="az_lead_smtp_from_email">From Email <span class="required">*</span></label></th>
                        <td><input id="az_lead_smtp_from_email" type="email" name="az_lead_smtp_from_email" value="<?php echo esc_attr( get_option( 'az_lead_smtp_from_email', get_option( 'az_contact_email', get_option( 'admin_email' ) ) ) ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="az_lead_smtp_from_name">From Name</label></th>
                        <td><input id="az_lead_smtp_from_name" type="text" name="az_lead_smtp_from_name" value="<?php echo esc_attr( get_option( 'az_lead_smtp_from_name', get_bloginfo( 'name' ) ) ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row">Debug</th>
                        <td><input type="hidden" name="az_lead_smtp_debug" value="0"><label><input type="checkbox" name="az_lead_smtp_debug" value="1" <?php checked( get_option( 'az_lead_smtp_debug', 0 ), 1 ); ?>> Ghi log debug SMTP vào error log.</label></td>
                    </tr>
                </table>
                <?php submit_button( 'Lưu cấu hình SMTP' ); ?>
            </form>

            <div class="postbox az-lead-test-box">
                <h2 class="hndle">Gửi email test Lead</h2>
                <div class="inside">
                    <form method="post">
                        <?php wp_nonce_field( 'az_lead_test_email', 'az_lead_test_nonce' ); ?>
                        <input type="email" name="az_lead_test_email" value="<?php echo esc_attr( get_option( 'az_contact_email', get_option( 'admin_email' ) ) ); ?>" class="regular-text">
                        <?php submit_button( 'Gửi email test Lead', 'secondary', '', false ); ?>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <style>
        .az-lead-wrap .nav-tab-wrapper{margin-bottom:18px}.az-lead-table th,.az-lead-table td{font-size:14px;padding:12px 14px}.az-lead-delete{color:#b32d2e;font-weight:600;text-decoration:none}.az-lead-delete:hover{color:#8a2424;text-decoration:underline}.az-smtp-providers{display:grid;grid-template-columns:repeat(4,minmax(180px,1fr));gap:16px;max-width:1120px;margin:12px 0 24px}.az-smtp-providers>div{padding:18px;background:#fff;border:1px solid #dcdcde;border-radius:4px}.az-smtp-providers h3{margin:0 0 14px}.az-smtp-providers p{margin:0;line-height:1.65}.az-lead-smtp-form{max-width:1120px;padding:28px 22px;background:#fff;border:1px solid #ccd0d4}.az-lead-smtp-form .required{color:#d63638}.az-lead-test-box{max-width:1120px;margin-top:18px}.az-lead-test-box .inside{padding:14px 22px 20px}@media(max-width:1100px){.az-smtp-providers{grid-template-columns:repeat(2,minmax(180px,1fr))}}@media(max-width:640px){.az-smtp-providers{grid-template-columns:1fr}.az-lead-table{display:block;overflow-x:auto}}
    </style>
    <?php
}

function wv_about_default_content() {
    $image_url = esc_url( get_template_directory_uri() . '/assets/images/banner-tbi-motion.jpg' );

    return '<!-- wp:group {"className":"wv-about-content","layout":{"type":"default"}} -->
<div class="wp-block-group wv-about-content">
<!-- wp:group {"className":"wv-about-section wv-about-intro","layout":{"type":"constrained"}} -->
<div class="wp-block-group wv-about-section wv-about-intro">
<!-- wp:columns {"className":"wv-about-grid wv-about-grid-top"} -->
<div class="wp-block-columns wv-about-grid wv-about-grid-top">
<!-- wp:column --><div class="wp-block-column"><!-- wp:image {"sizeSlug":"large","className":"wv-about-rounded-image"} --><figure class="wp-block-image size-large wv-about-rounded-image"><img src="' . $image_url . '" alt="Hình ảnh công ty"/></figure><!-- /wp:image --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"textAlign":"center","className":"wv-about-title"} --><h2 class="wp-block-heading has-text-align-center wv-about-title">GIỚI THIỆU</h2><!-- /wp:heading --><!-- wp:paragraph {"className":"wv-about-copy"} --><p class="wv-about-copy">Công Ty TNHH WORLD VISION (VN) là Doanh nghiệp 100% vốn đầu tư của Đài Loan. Công ty thành lập năm 2010, nhà máy đi vào hoạt động năm 2012, diện tích xưởng 20.000m2, với quy mô 1.800 tấn/năm. Chuyên sản xuất, gia công, đóng gói các loại thuốc bảo vệ thực vật, phân bón và sản phẩm hóa chất đạt chuẩn ISO 9001:2015, ISO 14001:2015.</p><!-- /wp:paragraph --></div><!-- /wp:column -->
</div><!-- /wp:columns -->
<!-- wp:columns {"className":"wv-about-grid wv-about-benefits-row"} -->
<div class="wp-block-columns wv-about-grid wv-about-benefits-row">
<!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"textAlign":"center","className":"wv-about-title"} --><h2 class="wp-block-heading has-text-align-center wv-about-title">LỢI THẾ</h2><!-- /wp:heading --><!-- wp:list {"className":"wv-about-list"} --><ul class="wv-about-list"><li>Nguyên phụ liệu nhập khẩu trực tiếp từ các nhà cung cấp nên giá cả cạnh tranh hơn, chất lượng hơn.</li><li>Công thức phối trộn chuyên biệt từ các chuyên gia và không ngừng cải tiến để mang lại sản phẩm hiệu quả cao.</li></ul><!-- /wp:list --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:image {"sizeSlug":"large","className":"wv-about-rounded-image"} --><figure class="wp-block-image size-large wv-about-rounded-image"><img src="' . $image_url . '" alt="Nhà máy sản xuất"/></figure><!-- /wp:image --></div><!-- /wp:column -->
</div><!-- /wp:columns -->
</div><!-- /wp:group -->
<!-- wp:group {"className":"wv-about-section wv-about-vision","layout":{"type":"constrained"}} -->
<div class="wp-block-group wv-about-section wv-about-vision"><!-- wp:heading {"textAlign":"center","className":"wv-about-title"} --><h2 class="wp-block-heading has-text-align-center wv-about-title">TẦM NHÌN</h2><!-- /wp:heading --><!-- wp:image {"sizeSlug":"large","className":"wv-about-vision-image"} --><figure class="wp-block-image size-large wv-about-vision-image"><img src="' . $image_url . '" alt="Tầm nhìn phát triển"/></figure><!-- /wp:image --><!-- wp:paragraph {"align":"center","className":"wv-about-caption"} --><p class="has-text-align-center wv-about-caption">World Vision (VN) hướng đến sự phát triển vươn tầm khu vực Châu Á Thái Bình Dương.</p><!-- /wp:paragraph --></div><!-- /wp:group -->
<!-- wp:group {"className":"wv-about-section wv-about-mission","layout":{"type":"constrained"}} -->
<div class="wp-block-group wv-about-section wv-about-mission"><!-- wp:heading {"textAlign":"center","className":"wv-about-title"} --><h2 class="wp-block-heading has-text-align-center wv-about-title">SỨ MỆNH</h2><!-- /wp:heading --><!-- wp:paragraph {"align":"center","className":"wv-about-lead"} --><p class="has-text-align-center wv-about-lead">Vì mục tiêu tồn tại và phát triển lâu dài, Công ty luôn phấn đấu tạo giá trị bền vững cho khách hàng, cộng đồng và nhân viên.</p><!-- /wp:paragraph --><!-- wp:columns {"className":"wv-about-cards"} --><div class="wp-block-columns wv-about-cards"><!-- wp:column --><div class="wp-block-column"><!-- wp:group {"className":"wv-about-card","layout":{"type":"constrained"}} --><div class="wp-block-group wv-about-card"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Khách hàng</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Đối với khách hàng thì luôn nâng cao chất lượng sản phẩm đáp ứng các yêu cầu.</p><!-- /wp:paragraph --></div><!-- /wp:group --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:group {"className":"wv-about-card","layout":{"type":"constrained"}} --><div class="wp-block-group wv-about-card"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Cộng đồng</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Luôn cam kết bảo vệ môi trường và ngăn ngừa ô nhiễm, thực hiện đúng các thủ tục môi trường.</p><!-- /wp:paragraph --></div><!-- /wp:group --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:group {"className":"wv-about-card","layout":{"type":"constrained"}} --><div class="wp-block-group wv-about-card"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Công nhân viên</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Đối với cán bộ công nhân viên thì nâng cao chất lượng cuộc sống của mọi người trong công ty.</p><!-- /wp:paragraph --></div><!-- /wp:group --></div><!-- /wp:column --></div><!-- /wp:columns --></div><!-- /wp:group -->
<!-- wp:group {"className":"wv-about-section wv-about-values-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group wv-about-section wv-about-values-section"><!-- wp:heading {"textAlign":"center","className":"wv-about-title"} --><h2 class="wp-block-heading has-text-align-center wv-about-title">GIÁ TRỊ CỐT LÕI</h2><!-- /wp:heading --><!-- wp:columns {"className":"wv-about-grid"} --><div class="wp-block-columns wv-about-grid"><!-- wp:column --><div class="wp-block-column"><!-- wp:image {"sizeSlug":"large","className":"wv-about-rounded-image"} --><figure class="wp-block-image size-large wv-about-rounded-image"><img src="' . $image_url . '" alt="Giá trị cốt lõi"/></figure><!-- /wp:image --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:list {"className":"wv-about-list"} --><ul class="wv-about-list"><li>Sản phẩm chất lượng</li><li>Thân thiện môi trường</li><li>Giá thành hợp lý</li><li>Khách hàng vừa ý</li></ul><!-- /wp:list --></div><!-- /wp:column --></div><!-- /wp:columns --></div><!-- /wp:group -->
<!-- wp:group {"className":"wv-about-section wv-about-contact-band","layout":{"type":"constrained"}} -->
<div class="wp-block-group wv-about-section wv-about-contact-band"><!-- wp:heading {"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center">HỢP TÁC</h2><!-- /wp:heading --><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">Hợp tác không chỉ là chiến lược, mà còn là tâm huyết để tạo ra giá trị chung.</p><!-- /wp:paragraph --><!-- wp:shortcode -->[contact-form-7 id="1" title="Form liên hệ"]<!-- /wp:shortcode --></div><!-- /wp:group -->
</div><!-- /wp:group -->';
}

function wv_seed_about_template_content() {
    return;

    if ( ! is_admin() || wp_doing_ajax() ) {
        return;
    }

    $pages = get_posts(
        array(
            'post_type'      => 'page',
            'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
            'posts_per_page' => -1,
            'meta_key'       => '_wp_page_template',
            'meta_value'     => 'page-about.php',
            'fields'         => 'ids',
        )
    );

    foreach ( $pages as $page_id ) {
        $page = get_post( $page_id );
        if ( $page && false === strpos( (string) $page->post_content, 'wv-about-content' ) && ! get_post_meta( $page_id, '_wv_about_seeded', true ) ) {
            if ( '' !== trim( (string) $page->post_content ) ) {
                update_post_meta( $page_id, '_wv_about_original_content', $page->post_content );
            }

            wp_update_post(
                array(
                    'ID'           => $page_id,
                    'post_content' => wv_about_default_content(),
                )
            );
            update_post_meta( $page_id, '_wv_about_seeded', current_time( 'mysql' ) );
        }
    }
}
add_action( 'admin_init', 'wv_seed_about_template_content' );

function az_catalogue_default_lines() {
    return "ĐMT Zero Export | #\nĐMT Hybrid | #\nCatalogue Công ty chúng tôi | #";
}

function az_catalogue_items() {
    $lines = get_option( 'az_catalogue_lines', az_catalogue_default_lines() );
    $items = array();

    foreach ( preg_split( '/\r\n|\r|\n/', (string) $lines ) as $line ) {
        $line = trim( $line );
        if ( '' === $line ) {
            continue;
        }

        $parts = array_map( 'trim', explode( '|', $line, 2 ) );
        $title = sanitize_text_field( $parts[0] ?? '' );
        $url   = esc_url_raw( $parts[1] ?? '' );

        if ( '' === $title ) {
            continue;
        }

        $items[] = array(
            'title' => $title,
            'url'   => $url ? $url : '#',
        );
    }

    return $items;
}
