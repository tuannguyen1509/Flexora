<?php
/**
 * AZ9S — Quản lý Lead (khách hàng đăng ký tư vấn)
 */
if ( ! defined('ABSPATH') ) exit;

// Tao bang DB khi kich hoat plugin.
function az9s_create_leads_table() {
    global $wpdb;
    $table   = $wpdb->prefix . 'az9s_leads';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name        VARCHAR(150) NOT NULL,
        phone       VARCHAR(30)  NOT NULL,
        email       VARCHAR(150) DEFAULT '',
        message     TEXT         DEFAULT '',
        form_id     VARCHAR(100) DEFAULT '',
        form_title  VARCHAR(255) DEFAULT '',
        status      VARCHAR(20)  DEFAULT 'new',
        ip          VARCHAR(45)  DEFAULT '',
        created_at  DATETIME     NOT NULL,
        PRIMARY KEY (id)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

function az9s_upgrade_leads_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'az9s_leads';
    if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) ) {
        az9s_create_leads_table();
        return;
    }
    if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$table}` LIKE 'form_id'" ) ) {
        $wpdb->query( "ALTER TABLE `{$table}` ADD `form_id` VARCHAR(100) NOT NULL DEFAULT '' AFTER `message`" );
    }
    if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$table}` LIKE 'form_title'" ) ) {
        $wpdb->query( "ALTER TABLE `{$table}` ADD `form_title` VARCHAR(255) NOT NULL DEFAULT '' AFTER `form_id`" );
    }
}

function az9s_format_lead_time( $mysql_time ) {
    $date = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', (string) $mysql_time, wp_timezone() );
    return $date ? $date->format( 'd/m/Y H:i' ) : (string) $mysql_time;
}

function az9s_send_lead_notification_email( $lead ) {
    $lead = wp_parse_args( $lead, [
        'name'       => '',
        'phone'      => '',
        'email'      => '',
        'message'    => '',
        'form_title' => '',
        'created_at' => current_time( 'mysql' ),
    ] );

    $to = function_exists( 'az9s_get_notification_email' ) ? az9s_get_notification_email() : [ get_option( 'admin_email' ) ];
    $to = is_array( $to ) ? array_values( array_filter( $to, 'is_email' ) ) : array_values( array_filter( [ $to ], 'is_email' ) );
    if ( ! $to ) {
        update_option( 'az9s_last_lead_mail_status', [
            'sent'    => false,
            'to'      => '',
            'subject' => '',
            'error'   => 'Danh sách Email nhận thông báo không có email hợp lệ.',
            'time'    => current_time( 'mysql' ),
        ], false );
        return false;
    }

    $subject_name = $lead['name'] ? $lead['name'] : 'Khách hàng mới';
    $subject = sprintf( 'Lead mới từ %s', $subject_name );
    if ( ! empty( $lead['form_title'] ) ) {
        $subject .= ' - ' . $lead['form_title'];
    }

    $rows = [
        'Họ tên'    => $lead['name'],
        'Điện thoại' => $lead['phone'],
        'Email'     => $lead['email'],
        'Tên form'  => $lead['form_title'],
        'Nội dung'  => $lead['message'],
        'Thời gian' => az9s_format_lead_time( $lead['created_at'] ),
    ];

    $body = '<h2 style="margin:0 0 14px">Website có khách hàng đăng ký mới</h2>';
    $body .= '<table cellpadding="8" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:680px">';
    foreach ( $rows as $label => $value ) {
        if ( '' === trim( (string) $value ) ) {
            continue;
        }
        $body .= '<tr>';
        $body .= '<th align="left" style="border:1px solid #ddd;background:#f7f7f7;width:140px">' . esc_html( $label ) . '</th>';
        $body .= '<td style="border:1px solid #ddd">' . nl2br( esc_html( $value ) ) . '</td>';
        $body .= '</tr>';
    }
    $body .= '</table>';

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
    ];
    $smtp_settings = get_option( 'az_smtp_settings', [] );
    if ( is_array( $smtp_settings ) && ! empty( $smtp_settings['enabled'] ) && ! empty( $smtp_settings['from_email'] ) && is_email( $smtp_settings['from_email'] ) ) {
        $from_name = ! empty( $smtp_settings['from_name'] ) ? sanitize_text_field( $smtp_settings['from_name'] ) : get_bloginfo( 'name' );
        $headers[] = 'From: ' . $from_name . ' <' . sanitize_email( $smtp_settings['from_email'] ) . '>';
    }
    if ( is_email( $lead['email'] ) ) {
        $headers[] = 'Reply-To: ' . $lead['name'] . ' <' . $lead['email'] . '>';
    }

    $mail_error = null;
    $capture_error = static function( $error ) use ( &$mail_error ) {
        $mail_error = $error instanceof WP_Error ? $error->get_error_message() : 'Không rõ lỗi gửi mail.';
    };
    add_action( 'wp_mail_failed', $capture_error );
    $sent = wp_mail( $to, $subject, $body, $headers );
    remove_action( 'wp_mail_failed', $capture_error );
    if ( ! $sent ) {
        global $phpmailer;
        if ( empty( $mail_error ) && is_object( $phpmailer ) && ! empty( $phpmailer->ErrorInfo ) ) {
            $mail_error = $phpmailer->ErrorInfo;
        }
    }

    update_option( 'az9s_last_lead_mail_status', [
        'sent'    => (bool) $sent,
        'to'      => implode( ', ', $to ),
        'subject' => $subject,
        'error'   => $sent ? '' : ( $mail_error ?: 'wp_mail trả về false nhưng không có thông báo lỗi.' ),
        'time'    => current_time( 'mysql' ),
    ], false );

    return $sent;
}


// ── AJAX: Nhận form submit ────────────────────────────────────────────────────
add_action( 'wp_ajax_az9s_lead',        'az9s_handle_lead_submit' );
add_action( 'wp_ajax_nopriv_az9s_lead', 'az9s_handle_lead_submit' );

function az9s_handle_lead_submit() {
    check_ajax_referer( 'az9s_lead', 'nonce' );

    $name    = sanitize_text_field( wp_unslash( $_POST['name']    ?? '' ) );
    $phone   = sanitize_text_field( wp_unslash( $_POST['phone']   ?? '' ) );
    $email   = sanitize_email(      wp_unslash( $_POST['email']   ?? '' ) );
    $message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

    if ( ! $name || ! $phone ) {
        wp_send_json_error( 'Vui lòng điền đầy đủ họ tên và số điện thoại.' );
    }

    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'az9s_leads',
        [
            'name'       => $name,
            'phone'      => $phone,
            'email'      => $email,
            'message'    => $message,
            'status'     => 'new',
            'ip'         => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
            'created_at' => current_time('mysql'),
        ],
        [ '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
    );

    az9s_send_lead_notification_email( [
        'name'       => $name,
        'phone'      => $phone,
        'email'      => $email,
        'message'    => $message,
        'form_title' => 'Form cũ / Liên hệ',
        'created_at' => current_time( 'mysql' ),
    ] );

    wp_send_json_success( 'Cảm ơn! Chúng tôi sẽ liên hệ lại trong thời gian sớm nhất.' );
}

// ── AJAX: Cập nhật trạng thái ─────────────────────────────────────────────────
add_action( 'wp_ajax_az9s_lead_status', function() {
    check_ajax_referer( 'az9s_admin_nonce', 'nonce' );
    if ( ! current_user_can('manage_options') ) wp_send_json_error('Unauthorized');

    $id     = absint( $_POST['id']     ?? 0 );
    $status = sanitize_key( $_POST['status'] ?? 'new' );
    $allowed = ['new', 'contacted', 'done', 'spam'];
    if ( ! in_array($status, $allowed, true) ) wp_send_json_error('Invalid status');

    global $wpdb;
    $wpdb->update( $wpdb->prefix . 'az9s_leads', ['status' => $status], ['id' => $id], ['%s'], ['%d'] );
    wp_send_json_success();
});

// ── AJAX: Xóa lead ────────────────────────────────────────────────────────────
add_action( 'wp_ajax_az9s_lead_delete', function() {
    check_ajax_referer( 'az9s_admin_nonce', 'nonce' );
    if ( ! current_user_can('manage_options') ) wp_send_json_error('Unauthorized');

    $id = absint( $_POST['id'] ?? 0 );
    global $wpdb;
    $wpdb->delete( $wpdb->prefix . 'az9s_leads', ['id' => $id], ['%d'] );
    wp_send_json_success();
});

add_action( 'admin_post_az9s_delete_all_leads', function() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Bạn không có quyền thực hiện thao tác này.' );
    }
    check_admin_referer( 'az9s_delete_all_leads' );

    global $wpdb;
    $table = $wpdb->prefix . 'az9s_leads';
    $wpdb->query( "TRUNCATE TABLE `{$table}`" );

    wp_safe_redirect( admin_url( 'admin.php?page=az9s-leads&all-deleted=1' ) );
    exit;
} );

add_action( 'admin_post_az9s_test_lead_email', function() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Bạn không có quyền thực hiện thao tác này.' );
    }
    check_admin_referer( 'az9s_test_lead_email' );

    az9s_send_lead_notification_email( [
        'name'       => 'Khách test email',
        'phone'      => '0900000000',
        'email'      => 'test@example.com',
        'message'    => 'Đây là email test từ chức năng Quản lý Lead.',
        'form_title' => 'Test Email Lead',
        'created_at' => current_time( 'mysql' ),
    ] );

    wp_safe_redirect( admin_url( 'admin.php?page=az9s-leads&email-tested=1' ) );
    exit;
} );

// ── Admin menu ────────────────────────────────────────────────────────────────
add_action( 'admin_menu', function() {
    add_menu_page(
        'Quản lý Lead',
        'Quản lý Lead',
        'manage_options',
        'az9s-leads',
        'az9s_leads_admin_page',
        'dashicons-list-view',
        26
    );
});

// ── Admin page ────────────────────────────────────────────────────────────────
function az9s_leads_admin_page() {
    if ( ! current_user_can('manage_options') ) return;

    $admin_tab = sanitize_key( $_GET['tab'] ?? 'leads' );
    if ( 'smtp' === $admin_tab ) {
        az9s_leads_smtp_admin_page();
        return;
    }
    if ( 'forms' === $admin_tab && function_exists( 'az9s_lead_forms_admin_page' ) ) {
        az9s_lead_forms_admin_page();
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'az9s_leads';

    // Filter
    $status_filter = sanitize_key( $_GET['status'] ?? '' );
    $search        = sanitize_text_field( $_GET['s'] ?? '' );
    $per_page      = 20;
    $current_page  = max(1, absint($_GET['paged'] ?? 1));
    $offset        = ($current_page - 1) * $per_page;

    $where = 'WHERE 1=1';
    $args  = [];
    if ( $status_filter ) {
        $where .= ' AND status = %s';
        $args[] = $status_filter;
    }
    if ( $search ) {
        $where .= ' AND (name LIKE %s OR phone LIKE %s OR email LIKE %s)';
        $like   = '%' . $wpdb->esc_like($search) . '%';
        $args   = array_merge($args, [$like, $like, $like]);
    }

    $total = $args
        ? (int) $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM $table $where", ...$args) )
        : (int) $wpdb->get_var("SELECT COUNT(*) FROM $table $where");

    $query = "SELECT * FROM $table $where ORDER BY created_at DESC LIMIT %d OFFSET %d";
    $leads = $args
        ? $wpdb->get_results( $wpdb->prepare($query, ...array_merge($args, [$per_page, $offset])) )
        : $wpdb->get_results( $wpdb->prepare($query, $per_page, $offset) );

    // Stats
    $stats = $wpdb->get_results("SELECT status, COUNT(*) as cnt FROM $table GROUP BY status");
    $stat_map = ['new'=>0,'contacted'=>0,'done'=>0,'spam'=>0];
    foreach ($stats as $s) $stat_map[$s->status] = (int)$s->cnt;

    $admin_nonce = wp_create_nonce('az9s_admin_nonce');
    $base_url    = admin_url('admin.php?page=az9s-leads');
    ?>
    <div class="wrap" id="az9s-leads-wrap">
        <h1 style="display:flex;align-items:center;gap:10px">
            📋 Quản lý Lead
            <span style="font-size:13px;background:#e74c3c;color:#fff;padding:3px 10px;border-radius:50px;font-weight:700">
                <?php echo $stat_map['new']; ?> mới
            </span>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0 0 0 12px">
                <input type="hidden" name="action" value="az9s_test_lead_email">
                <?php wp_nonce_field( 'az9s_test_lead_email' ); ?>
                <button type="submit" class="button button-primary">Gửi email test Lead</button>
            </form>
        </h1>
        <nav class="nav-tab-wrapper" style="margin:14px 0 18px">
            <a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( 'admin.php?page=az9s-leads' ) ); ?>">Danh sách Leads</a>
            <a class="nav-tab" href="<?php echo esc_url( admin_url( 'admin.php?page=az9s-leads&tab=forms' ) ); ?>">Biểu mẫu</a>
            <a class="nav-tab" href="<?php echo esc_url( admin_url( 'admin.php?page=az9s-leads&tab=smtp' ) ); ?>">SMTP</a>
        </nav>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0 0 12px;display:flex;align-items:center;gap:10px">
            <input type="hidden" name="action" value="az9s_test_lead_email">
            <?php wp_nonce_field( 'az9s_test_lead_email' ); ?>
            <button type="submit" class="button">Gửi email test Lead</button>
            <span style="color:#646970">Gửi thử tới email trong mục <strong>Email nhận thông báo</strong>.</span>
        </form>

        <?php
        $mail_status = get_option( 'az9s_last_lead_mail_status', [] );
        if ( is_array( $mail_status ) && ! empty( $mail_status['time'] ) ) :
            $mail_sent = ! empty( $mail_status['sent'] );
            ?>
            <div class="notice <?php echo $mail_sent ? 'notice-success' : 'notice-error'; ?>">
                <p>
                    <strong>Email thông báo Lead gần nhất:</strong>
                    <?php echo $mail_sent ? 'Đã gửi' : 'Gửi thất bại'; ?>
                    <?php if ( ! empty( $mail_status['to'] ) ) : ?>
                        tới <code><?php echo esc_html( $mail_status['to'] ); ?></code>
                    <?php endif; ?>
                    lúc <?php echo esc_html( az9s_format_lead_time( $mail_status['time'] ) ); ?>.
                    <?php if ( ! $mail_sent && ! empty( $mail_status['error'] ) ) : ?>
                        Lỗi: <code><?php echo esc_html( $mail_status['error'] ); ?></code>
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div style="display:flex;gap:12px;margin:16px 0;flex-wrap:wrap">
            <?php
            $stat_labels = ['new'=>['🆕','Mới','#3498db'],'contacted'=>['📞','Đã liên hệ','#f39c12'],'done'=>['✅','Hoàn thành','#27ae60'],'spam'=>['🚫','Spam','#e74c3c']];
            foreach ($stat_labels as $key => [$icon,$label,$color]) :
            ?>
            <a href="<?php echo esc_url(add_query_arg('status',$key,$base_url)); ?>"
               style="background:#fff;border:2px solid <?php echo $status_filter===$key?$color:'#e0e0e0'; ?>;border-radius:8px;padding:12px 20px;text-decoration:none;display:flex;align-items:center;gap:8px;min-width:120px">
                <span style="font-size:1.4rem"><?php echo $icon; ?></span>
                <div>
                    <div style="font-size:1.3rem;font-weight:900;color:<?php echo $color; ?>"><?php echo $stat_map[$key]; ?></div>
                    <div style="font-size:11px;color:#666"><?php echo $label; ?></div>
                </div>
            </a>
            <?php endforeach; ?>
            <a href="<?php echo esc_url($base_url); ?>"
               style="background:#fff;border:2px solid <?php echo !$status_filter?'#333':'#e0e0e0'; ?>;border-radius:8px;padding:12px 20px;text-decoration:none;display:flex;align-items:center;gap:8px">
                <span style="font-size:1.4rem">📊</span>
                <div>
                    <div style="font-size:1.3rem;font-weight:900;color:#333"><?php echo $total; ?></div>
                    <div style="font-size:11px;color:#666">Tất cả</div>
                </div>
            </a>
        </div>

        <!-- Search -->
        <form method="get" style="margin-bottom:16px;display:flex;gap:8px">
            <input type="hidden" name="page" value="az9s-leads">
            <?php if ($status_filter) : ?><input type="hidden" name="status" value="<?php echo esc_attr($status_filter); ?>"><?php endif; ?>
            <input type="search" name="s" value="<?php echo esc_attr($search); ?>"
                   placeholder="Tìm theo tên, SĐT, email..."
                   style="padding:8px 12px;border:1px solid #ccc;border-radius:6px;width:280px;font-size:13px">
            <button type="submit" class="button">🔍 Tìm kiếm</button>
            <?php if ($search) : ?><a href="<?php echo esc_url($base_url); ?>" class="button">✕ Xóa lọc</a><?php endif; ?>
        </form>

        <?php if ( isset( $_GET['all-deleted'] ) ) : ?>
            <div class="notice notice-success is-dismissible"><p>Đã xóa toàn bộ danh sách Leads.</p></div>
        <?php endif; ?>

        <?php if ( $total > 0 ) : ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:16px" onsubmit="return confirm('Bạn có chắc muốn xóa TOÀN BỘ danh sách Leads? Dữ liệu đã xóa không thể khôi phục.');">
            <input type="hidden" name="action" value="az9s_delete_all_leads">
            <?php wp_nonce_field( 'az9s_delete_all_leads' ); ?>
            <button type="submit" class="button" style="color:#b32d2e;border-color:#b32d2e">Xóa toàn bộ Leads</button>
        </form>
        <?php endif; ?>

        <!-- Table -->
        <table class="wp-list-table widefat fixed striped" style="border-radius:8px;overflow:hidden">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Họ tên</th>
                    <th>Điện thoại</th>
                    <th>Email</th>
                    <th>Tên form</th>
                    <th style="width:110px">Trạng thái</th>
                    <th style="width:130px">Thời gian</th>
                    <th style="width:140px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if ( empty($leads) ) : ?>
                <tr><td colspan="8" style="text-align:center;padding:32px;color:#999">Chưa có lead nào.</td></tr>
            <?php else : ?>
                <?php foreach ($leads as $lead) : ?>
                <tr id="lead-row-<?php echo $lead->id; ?>">
                    <td style="color:#999;font-size:12px"><?php echo $lead->id; ?></td>
                    <td><strong><?php echo esc_html($lead->name); ?></strong></td>
                    <td><a href="tel:<?php echo esc_attr($lead->phone); ?>" style="color:#2980b9;font-weight:600"><?php echo esc_html($lead->phone); ?></a></td>
                    <td style="font-size:12px"><?php echo $lead->email ? esc_html($lead->email) : '<span style="color:#ccc">—</span>'; ?></td>
                    <td><strong><?php echo ! empty( $lead->form_title ) ? esc_html( $lead->form_title ) : 'Form cũ / Liên hệ'; ?></strong></td>
                    <td>
                        <select class="az9s-status-select" data-id="<?php echo $lead->id; ?>"
                                data-status="<?php echo esc_attr( $lead->status ); ?>" aria-label="<?php echo esc_attr( 'Trạng thái của ' . $lead->name ); ?>">
                            <option value="new"       <?php selected($lead->status,'new'); ?>>Mới</option>
                            <option value="contacted" <?php selected($lead->status,'contacted'); ?>>Đã liên hệ</option>
                            <option value="done"      <?php selected($lead->status,'done'); ?>>Hoàn thành</option>
                            <option value="spam"      <?php selected($lead->status,'spam'); ?>>Spam</option>
                        </select>
                    </td>
                    <td style="font-size:11px;color:#666"><?php echo esc_html( az9s_format_lead_time( $lead->created_at ) ); ?></td>
                    <td>
                        <button type="button" class="az9s-view-lead button button-small"
                                data-name="<?php echo esc_attr( $lead->name ); ?>"
                                data-phone="<?php echo esc_attr( $lead->phone ); ?>"
                                data-email="<?php echo esc_attr( $lead->email ); ?>"
                                data-form="<?php echo esc_attr( ! empty( $lead->form_title ) ? $lead->form_title : 'Form cũ / Liên hệ' ); ?>"
                                data-details="<?php echo esc_attr( $lead->message ); ?>"
                                data-time="<?php echo esc_attr( az9s_format_lead_time( $lead->created_at ) ); ?>">Chi tiết</button>
                        <button class="az9s-delete-lead button button-small"
                                data-id="<?php echo $lead->id; ?>"
                                style="color:#e74c3c;border-color:#e74c3c"
                                title="Xóa lead này">🗑</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total > $per_page) :
            $total_pages = ceil($total / $per_page);
        ?>
        <div style="margin-top:16px;display:flex;gap:6px;align-items:center">
            <span style="color:#666;font-size:13px">Trang <?php echo $current_page; ?>/<?php echo $total_pages; ?> (<?php echo $total; ?> leads)</span>
            <?php for ($p=1;$p<=$total_pages;$p++) : ?>
            <a href="<?php echo esc_url(add_query_arg(['paged'=>$p,'status'=>$status_filter,'s'=>$search],$base_url)); ?>"
               style="padding:5px 10px;border-radius:5px;border:1px solid <?php echo $p===$current_page?'#2980b9':'#ddd'; ?>;background:<?php echo $p===$current_page?'#2980b9':'#fff'; ?>;color:<?php echo $p===$current_page?'#fff':'#333'; ?>;text-decoration:none;font-size:13px">
                <?php echo $p; ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <p style="margin-top:12px;font-size:12px;color:#999">
            IP khách hàng được lưu để phòng spam. Dữ liệu lưu trong database WordPress.
        </p>
    </div>

    <div id="az9s-lead-modal" class="az9s-lead-modal" hidden>
        <div class="az9s-lead-modal__backdrop"></div>
        <div class="az9s-lead-modal__dialog" role="dialog" aria-modal="true">
            <div class="az9s-lead-modal__head">
                <h2>Chi tiết Lead</h2>
                <button type="button" class="az9s-lead-modal__close" aria-label="Đóng">×</button>
            </div>
            <div class="az9s-lead-modal__body"></div>
        </div>
    </div>
    <style>
    .wp-core-ui #az9s-leads-wrap select.az9s-status-select {
        --az9s-status-color: #475569;
        --az9s-status-bg: #f8fafc;
        --az9s-status-border: #dbe2ea;
        box-sizing: border-box;
        width: 112px;
        max-width: 100%;
        height: 28px;
        min-height: 28px;
        margin: 0;
        padding: 0 24px 0 10px;
        border: 1px solid var(--az9s-status-border);
        border-radius: 6px;
        color: var(--az9s-status-color);
        background-color: var(--az9s-status-bg);
        background-image: linear-gradient(45deg, transparent 50%, currentColor 50%), linear-gradient(135deg, currentColor 50%, transparent 50%);
        background-position: calc(100% - 13px) 50%, calc(100% - 9px) 50%;
        background-size: 4px 4px;
        background-repeat: no-repeat;
        font-size: 12px;
        font-weight: 600;
        line-height: 26px;
        vertical-align: middle;
        appearance: none;
        -webkit-appearance: none;
        box-shadow: none;
        cursor: pointer;
    }
    .wp-core-ui #az9s-leads-wrap select.az9s-status-select[data-status="new"] { --az9s-status-color: #1d4ed8; --az9s-status-bg: #eff6ff; --az9s-status-border: #bfdbfe; }
    .wp-core-ui #az9s-leads-wrap select.az9s-status-select[data-status="contacted"] { --az9s-status-color: #92400e; --az9s-status-bg: #fffbeb; --az9s-status-border: #fde68a; }
    .wp-core-ui #az9s-leads-wrap select.az9s-status-select[data-status="done"] { --az9s-status-color: #166534; --az9s-status-bg: #f0fdf4; --az9s-status-border: #bbf7d0; }
    .wp-core-ui #az9s-leads-wrap select.az9s-status-select[data-status="spam"] { --az9s-status-color: #9f1239; --az9s-status-bg: #fff1f2; --az9s-status-border: #fecdd3; }
    .wp-core-ui #az9s-leads-wrap select.az9s-status-select:hover { border-color: currentColor; }
    .wp-core-ui #az9s-leads-wrap select.az9s-status-select:focus-visible { outline: 2px solid var(--wp-admin-theme-color, #2271b1); outline-offset: 2px; }
    .wp-core-ui #az9s-leads-wrap select.az9s-status-select option { color: #1d2327; background: #fff; font-weight: 400; }
    @media (max-width: 782px) { .wp-core-ui #az9s-leads-wrap select.az9s-status-select { width: 124px; height: 34px; min-height: 34px; line-height: 32px; } }
    .az9s-lead-modal{position:fixed;z-index:100000;inset:0;display:flex;align-items:center;justify-content:center;padding:20px}.az9s-lead-modal[hidden]{display:none}.az9s-lead-modal__backdrop{position:absolute;inset:0;background:rgba(15,23,42,.58)}.az9s-lead-modal__dialog{position:relative;width:min(620px,100%);max-height:85vh;overflow:auto;background:#fff;border-radius:10px;box-shadow:0 24px 70px rgba(0,0,0,.3)}.az9s-lead-modal__head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e5e7eb}.az9s-lead-modal__head h2{margin:0;color:#123b67}.az9s-lead-modal__close{padding:0;border:0;background:none;font-size:28px;cursor:pointer}.az9s-lead-modal__body{padding:20px}.az9s-detail-row{display:grid;grid-template-columns:140px 1fr;gap:15px;padding:10px 0;border-bottom:1px solid #edf0f3}.az9s-detail-row:last-child{border-bottom:0}.az9s-detail-content{white-space:pre-wrap;word-break:break-word}@media(max-width:600px){.az9s-detail-row{grid-template-columns:1fr;gap:4px}}
    </style>

    <script>
    var az9sAdminNonce = '<?php echo esc_js($admin_nonce); ?>';

    var leadModal = document.getElementById('az9s-lead-modal');
    document.querySelectorAll('.az9s-view-lead').forEach(function(button) {
        button.addEventListener('click', function() {
            var rows = [
                ['Tên form', button.dataset.form],
                ['Thời gian', button.dataset.time],
                ['Họ và tên', button.dataset.name],
                ['Điện thoại', button.dataset.phone],
                ['Email', button.dataset.email || '—']
            ];
            var fieldLabels = {
                'dai-ly': 'Đại lý',
                'dai_ly': 'Đại lý',
                'mau-xe': 'Mẫu xe',
                'mau_xe': 'Mẫu xe',
                'hieu-xe': 'Hiệu xe',
                'hieu_xe': 'Hiệu xe',
                'so-km': 'Số km',
                'so_km': 'Số km',
                'bien-so': 'Biển số',
                'bien_so': 'Biển số',
                'dia-chi': 'Địa chỉ',
                'dia_chi': 'Địa chỉ',
                'ngay-hen': 'Ngày hẹn',
                'ngay_hen': 'Ngày hẹn',
                'gio-hen': 'Giờ hẹn',
                'gio_hen': 'Giờ hẹn',
                'loai-xe': 'Loại xe',
                'loai_xe': 'Loại xe',
                'nam-san-xuat': 'Năm sản xuất',
                'nam_san_xuat': 'Năm sản xuất',
                'noi-dung': 'Nội dung',
                'noi_dung': 'Nội dung',
                'ghi-chu': 'Ghi chú',
                'ghi_chu': 'Ghi chú',
                'ngay-sinh': 'Ngày sinh',
                'ngay_sinh': 'Ngày sinh',
                'tinh-thanh': 'Tỉnh/thành phố',
                'tinh_thanh': 'Tỉnh/thành phố',
                'message': 'Nội dung',
                'service': 'Dịch vụ',
                'attachment': 'File đính kèm',
                'consent': 'Đồng ý điều khoản'
            };
            (button.dataset.details || '').split(/\r?\n/).forEach(function(line) {
                if (!line.trim()) return;
                var separator = line.indexOf(':');
                var rawKey = separator >= 0 ? line.slice(0, separator).trim() : 'Thông tin';
                var value = separator >= 0 ? line.slice(separator + 1).trim() : line.trim();
                var label = fieldLabels[rawKey] || rawKey.replace(/[-_]+/g, ' ').replace(/(^|\s)\S/g, function(letter) {
                    return letter.toUpperCase();
                });
                rows.push([label, value || '—']);
            });
            var body = leadModal.querySelector('.az9s-lead-modal__body');
            body.innerHTML = '';
            rows.forEach(function(row) {
                var item = document.createElement('div');
                item.className = 'az9s-detail-row';
                var label = document.createElement('strong');
                label.textContent = row[0];
                var value = document.createElement('div');
                value.className = 'az9s-detail-content';
                value.textContent = row[1];
                item.appendChild(label);
                item.appendChild(value);
                body.appendChild(item);
            });
            leadModal.hidden = false;
        });
    });
    function closeLeadModal() { leadModal.hidden = true; }
    leadModal.querySelector('.az9s-lead-modal__close').addEventListener('click', closeLeadModal);
    leadModal.querySelector('.az9s-lead-modal__backdrop').addEventListener('click', closeLeadModal);
    document.addEventListener('keydown', function(event) {
        if ('Escape' === event.key && !leadModal.hidden) closeLeadModal();
    });

    // Đổi trạng thái
    document.querySelectorAll('.az9s-status-select').forEach(function(sel) {
        sel.addEventListener('change', function() {
            var id = this.dataset.id;
            var status = this.value;
            this.dataset.status = status;
            var data = new FormData();
            data.append('action', 'az9s_lead_status');
            data.append('nonce', az9sAdminNonce);
            data.append('id', id);
            data.append('status', status);
            fetch(ajaxurl, {method:'POST',body:data,credentials:'same-origin'})
                .then(function(r){return r.json();})
                .then(function(d){ if(!d.success) alert('Lỗi cập nhật!'); });
        });
    });

    // Xóa lead
    document.querySelectorAll('.az9s-delete-lead').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('Xóa lead này?')) return;
            var id = this.dataset.id;
            var data = new FormData();
            data.append('action', 'az9s_lead_delete');
            data.append('nonce', az9sAdminNonce);
            data.append('id', id);
            fetch(ajaxurl, {method:'POST',body:data,credentials:'same-origin'})
                .then(function(r){return r.json();})
                .then(function(d){
                    if (d.success) {
                        var row = document.getElementById('lead-row-' + id);
                        if (row) row.remove();
                    }
                });
        });
    });
    </script>
    <?php
}



add_action( 'admin_post_az9s_save_smtp_settings', function() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Bạn không có quyền thực hiện thao tác này.' );
    }
    check_admin_referer( 'az9s_save_smtp_settings' );

    $current = function_exists( 'az9s_get_smtp_settings' ) ? az9s_get_smtp_settings() : [];
    $password = isset( $current['password'] ) ? (string) $current['password'] : '';

    if ( ! empty( $_POST['smtp_password'] ) ) {
        $password = (string) wp_unslash( $_POST['smtp_password'] );
    } elseif ( ! empty( $_POST['smtp_clear_password'] ) ) {
        $password = '';
    }

    $settings = [
        'enabled'    => isset( $_POST['smtp_enabled'] ) ? 1 : 0,
        'host'       => sanitize_text_field( wp_unslash( $_POST['smtp_host'] ?? '' ) ),
        'port'       => absint( $_POST['smtp_port'] ?? 587 ),
        'encryption' => sanitize_key( $_POST['smtp_encryption'] ?? 'tls' ),
        'auth'       => isset( $_POST['smtp_auth'] ) ? 1 : 0,
        'username'   => sanitize_text_field( wp_unslash( $_POST['smtp_username'] ?? '' ) ),
        'password'   => $password,
        'from_email' => sanitize_email( wp_unslash( $_POST['smtp_from_email'] ?? '' ) ),
        'from_name'  => sanitize_text_field( wp_unslash( $_POST['smtp_from_name'] ?? '' ) ),
    ];

    if ( ! in_array( $settings['encryption'], [ '', 'ssl', 'tls' ], true ) ) {
        $settings['encryption'] = 'tls';
    }
    if ( empty( $settings['port'] ) ) {
        $settings['port'] = 587;
    }

    if ( isset( $_POST['notification_emails'] ) ) {
        $raw_notification_emails = preg_split( '/[\s,;]+/', wp_unslash( $_POST['notification_emails'] ) );
        $notification_emails = [];
        foreach ( $raw_notification_emails as $email ) {
            $email = sanitize_email( $email );
            if ( is_email( $email ) ) {
                $notification_emails[] = $email;
            }
        }
        update_option( 'az9s_lead_notification_emails', implode( ', ', array_unique( $notification_emails ) ), false );
    }

    update_option( 'az_smtp_settings', $settings, false );
    wp_safe_redirect( admin_url( 'admin.php?page=az9s-leads&tab=smtp&saved=1' ) );
    exit;
} );

add_action( 'admin_post_az9s_test_smtp_settings', function() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Bạn không có quyền thực hiện thao tác này.' );
    }
    check_admin_referer( 'az9s_test_smtp_settings' );

    $to = sanitize_email( wp_unslash( $_POST['smtp_test_email'] ?? get_option( 'admin_email' ) ) );
    if ( ! is_email( $to ) ) {
        wp_safe_redirect( admin_url( 'admin.php?page=az9s-leads&tab=smtp&test=invalid' ) );
        exit;
    }

    $mail_error = '';
    $capture_error = static function( $error ) use ( &$mail_error ) {
        $mail_error = $error instanceof WP_Error ? $error->get_error_message() : 'Không rõ lỗi gửi mail.';
    };
    add_action( 'wp_mail_failed', $capture_error );
    $sent = wp_mail(
        $to,
        'AZ9S Leads SMTP test - ' . get_bloginfo( 'name' ),
        'Email test SMTP từ plugin AZ9S Leads đã được gửi lúc ' . current_time( 'd/m/Y H:i:s' ) . '.',
        [ 'Content-Type: text/html; charset=UTF-8' ]
    );
    remove_action( 'wp_mail_failed', $capture_error );

    if ( ! $sent ) {
        global $phpmailer;
        if ( empty( $mail_error ) && is_object( $phpmailer ) && ! empty( $phpmailer->ErrorInfo ) ) {
            $mail_error = $phpmailer->ErrorInfo;
        }
    }

    update_option( 'az9s_last_smtp_test_status', [
        'sent'  => (bool) $sent,
        'to'    => $to,
        'error' => $sent ? '' : ( $mail_error ?: 'wp_mail trả về false nhưng không có thông báo lỗi.' ),
        'time'  => current_time( 'mysql' ),
    ], false );

    wp_safe_redirect( admin_url( 'admin.php?page=az9s-leads&tab=smtp&test=' . ( $sent ? 'sent' : 'failed' ) ) );
    exit;
} );

function az9s_leads_smtp_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $settings = function_exists( 'az9s_get_smtp_settings' ) ? az9s_get_smtp_settings() : [];
    $notification_emails = (string) get_option( 'az9s_lead_notification_emails', get_option( 'admin_email' ) );
    $test_status = get_option( 'az9s_last_smtp_test_status', [] );
    ?>
    <div class="wrap" id="az9s-leads-smtp-wrap">
        <h1>📧 Cấu hình SMTP</h1>
        <nav class="nav-tab-wrapper" style="margin:14px 0 18px">
            <a class="nav-tab" href="<?php echo esc_url( admin_url( 'admin.php?page=az9s-leads' ) ); ?>">Danh sách Leads</a>
            <a class="nav-tab" href="<?php echo esc_url( admin_url( 'admin.php?page=az9s-leads&tab=forms' ) ); ?>">Biểu mẫu</a>
            <a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( 'admin.php?page=az9s-leads&tab=smtp' ) ); ?>">SMTP</a>
        </nav>

        <?php if ( isset( $_GET['saved'] ) ) : ?>
            <div class="notice notice-success is-dismissible"><p>Đã lưu cấu hình SMTP.</p></div>
        <?php endif; ?>
        <?php if ( isset( $_GET['test'] ) && 'invalid' === $_GET['test'] ) : ?>
            <div class="notice notice-error is-dismissible"><p>Email test không hợp lệ.</p></div>
        <?php endif; ?>
        <?php if ( is_array( $test_status ) && ! empty( $test_status['time'] ) ) : ?>
            <div class="notice <?php echo ! empty( $test_status['sent'] ) ? 'notice-success' : 'notice-error'; ?>">
                <p><strong>Email test SMTP gần nhất:</strong> <?php echo ! empty( $test_status['sent'] ) ? 'Đã gửi' : 'Gửi thất bại'; ?> tới <code><?php echo esc_html( $test_status['to'] ?? '' ); ?></code> lúc <?php echo esc_html( az9s_format_lead_time( $test_status['time'] ) ); ?>. <?php if ( empty( $test_status['sent'] ) && ! empty( $test_status['error'] ) ) : ?>Lỗi: <code><?php echo esc_html( $test_status['error'] ); ?></code><?php endif; ?></p>
            </div>
        <?php endif; ?>


        <style>
            .az9s-smtp-provider-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:14px;max-width:860px;margin:0 0 16px}
            .az9s-smtp-provider-card{background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:16px;box-shadow:0 1px 1px rgba(0,0,0,.04)}
            .az9s-smtp-provider-card h3{margin:0 0 12px;font-size:15px;line-height:1.3}
            .az9s-smtp-provider-card p{margin:0;color:#1d2327;line-height:1.55}
            .az9s-smtp-provider-card code{font-size:12px}
            .az9s-smtp-help{margin-top:8px;color:#646970;line-height:1.6}
            .az9s-smtp-help ul{margin:.2em 0 0 1em}
        </style>
        <h2 style="margin-top:8px">📖 Common SMTP Providers</h2>
        <div class="az9s-smtp-provider-grid">
            <div class="az9s-smtp-provider-card">
                <h3>Gmail</h3>
                <p><strong>Host:</strong> <code>smtp.gmail.com</code><br><strong>Port:</strong> 587<br><strong>Encryption:</strong> TLS<br><strong>Note:</strong> Use App Password</p>
            </div>
            <div class="az9s-smtp-provider-card">
                <h3>Outlook/Hotmail</h3>
                <p><strong>Host:</strong> <code>smtp-mail.outlook.com</code><br><strong>Port:</strong> 587<br><strong>Encryption:</strong> TLS</p>
            </div>
            <div class="az9s-smtp-provider-card">
                <h3>Yahoo</h3>
                <p><strong>Host:</strong> <code>smtp.mail.yahoo.com</code><br><strong>Port:</strong> 587<br><strong>Encryption:</strong> TLS<br><strong>Note:</strong> Use App Password</p>
            </div>
            <div class="az9s-smtp-provider-card">
                <h3>SendGrid</h3>
                <p><strong>Host:</strong> <code>smtp.sendgrid.net</code><br><strong>Port:</strong> 587<br><strong>Encryption:</strong> TLS<br><strong>Username:</strong> <code>apikey</code></p>
            </div>
        </div>
        <div class="postbox" style="max-width:860px">
            <div class="postbox-header"><h2 style="padding:12px 16px;margin:0">Thông tin máy chủ SMTP</h2></div>
            <div class="inside">
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="action" value="az9s_save_smtp_settings">
                    <?php wp_nonce_field( 'az9s_save_smtp_settings' ); ?>
                    <table class="form-table" role="presentation">
                        <tr><th scope="row">Bật SMTP</th><td><label><input type="checkbox" name="smtp_enabled" value="1" <?php checked( ! empty( $settings['enabled'] ) ); ?>> Dùng SMTP này để gửi email thông báo Lead và các email qua <code>wp_mail()</code>.</label></td></tr>
                        <tr><th scope="row"><label for="smtp_host">SMTP Host <span style="color:#d63638">*</span></label></th><td><input type="text" id="smtp_host" name="smtp_host" value="<?php echo esc_attr( $settings['host'] ?? '' ); ?>" class="regular-text" placeholder="smtp.gmail.com"><div class="az9s-smtp-help">Examples:<ul><li>Gmail: <code>smtp.gmail.com</code></li><li>Outlook: <code>smtp-mail.outlook.com</code></li><li>Yahoo: <code>smtp.mail.yahoo.com</code></li><li>SendGrid: <code>smtp.sendgrid.net</code></li></ul></div></td></tr>
                        <tr><th scope="row"><label for="smtp_port">SMTP Port <span style="color:#d63638">*</span></label></th><td><input type="number" id="smtp_port" name="smtp_port" value="<?php echo esc_attr( $settings['port'] ?? 587 ); ?>" class="small-text" min="1" max="65535"><div class="az9s-smtp-help">Common ports:<ul><li>587 (TLS - recommended)</li><li>465 (SSL)</li><li>25 (No encryption)</li></ul></div></td></tr>
                        <tr><th scope="row"><label for="smtp_encryption">Mã hóa</label></th><td><select id="smtp_encryption" name="smtp_encryption"><option value="" <?php selected( $settings['encryption'] ?? '', '' ); ?>>Không</option><option value="tls" <?php selected( $settings['encryption'] ?? 'tls', 'tls' ); ?>>TLS</option><option value="ssl" <?php selected( $settings['encryption'] ?? '', 'ssl' ); ?>>SSL</option></select></td></tr>
                        <tr><th scope="row">Xác thực</th><td><label><input type="checkbox" name="smtp_auth" value="1" <?php checked( ! empty( $settings['auth'] ) ); ?>> SMTP yêu cầu username/password</label></td></tr>
                        <tr><th scope="row"><label for="smtp_username">Username</label></th><td><input type="text" id="smtp_username" name="smtp_username" value="<?php echo esc_attr( $settings['username'] ?? '' ); ?>" class="regular-text" autocomplete="off"></td></tr>
                        <tr><th scope="row"><label for="smtp_password">Password</label></th><td><input type="password" id="smtp_password" name="smtp_password" value="" class="regular-text" autocomplete="new-password" placeholder="<?php echo empty( $settings['password'] ) ? '' : 'Đang lưu, nhập mới để đổi'; ?>"><label style="display:block;margin-top:6px"><input type="checkbox" name="smtp_clear_password" value="1"> Xóa password SMTP đang lưu</label></td></tr>
                        <tr><th scope="row"><label for="smtp_from_email">From Email</label></th><td><input type="email" id="smtp_from_email" name="smtp_from_email" value="<?php echo esc_attr( $settings['from_email'] ?? get_option( 'admin_email' ) ); ?>" class="regular-text"></td></tr>
                        <tr><th scope="row"><label for="notification_emails">Email nhận thông báo</label></th><td><input type="text" id="notification_emails" name="notification_emails" value="<?php echo esc_attr( $notification_emails ); ?>" class="regular-text" placeholder="admin@example.com"><p class="description">Email nhận lead từ tất cả form AZ9S. Có thể nhập nhiều email, cách nhau bằng dấu phẩy.</p></td></tr>
                        <tr><th scope="row"><label for="smtp_from_name">From Name</label></th><td><input type="text" id="smtp_from_name" name="smtp_from_name" value="<?php echo esc_attr( $settings['from_name'] ?? get_bloginfo( 'name' ) ); ?>" class="regular-text"></td></tr>
                    </table>
                    <p class="submit"><button type="submit" class="button button-primary">Lưu SMTP</button></p>
                </form>
            </div>
        </div>

        <div class="postbox" style="max-width:860px;margin-top:16px">
            <div class="postbox-header"><h2 style="padding:12px 16px;margin:0">Gửi email test</h2></div>
            <div class="inside">
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                    <input type="hidden" name="action" value="az9s_test_smtp_settings">
                    <?php wp_nonce_field( 'az9s_test_smtp_settings' ); ?>
                    <input type="email" name="smtp_test_email" value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" class="regular-text">
                    <button type="submit" class="button">Gửi test SMTP</button>
                </form>
            </div>
        </div>
    </div>
    <?php
}
