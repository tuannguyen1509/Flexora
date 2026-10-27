<?php
/**
 * Trình tạo biểu mẫu Lead, cú pháp tương tự Contact Form 7.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function az9s_get_lead_forms() {
    $forms = get_option( 'az9s_lead_forms', [] );
    return is_array( $forms ) ? $forms : [];
}

/**
 * Tìm đúng ID biểu mẫu, kể cả dữ liệu cũ từng bị đổi chữ hoa/thường.
 */
function az9s_resolve_lead_form_id( $requested_id, $forms = null ) {
    $requested_id = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $requested_id );
    $forms = is_array( $forms ) ? $forms : az9s_get_lead_forms();
    if ( isset( $forms[ $requested_id ] ) ) {
        return $requested_id;
    }
    foreach ( array_keys( $forms ) as $stored_id ) {
        if ( 0 === strcasecmp( $stored_id, $requested_id ) ) {
            return $stored_id;
        }
    }
    return '';
}

function az9s_default_lead_form_template() {
    return '<div class="ct-field-row">
<div class="ct-field">
<label>Họ và tên</label>
[text* name placeholder "Nhập họ và tên"]
</div>
<div class="ct-field">
<label>Số điện thoại</label>
[tel* phone placeholder "Nhập số điện thoại"]
</div>
</div>
<div class="ct-field">
<label>Email</label>
[email email placeholder "Email (không bắt buộc)"]
</div>
<div class="ct-field">
<label>Nhu cầu tư vấn</label>
[textarea message placeholder "Nội dung cần tư vấn"]
</div>
[submit "GỬI YÊU CẦU"]';
}


function az9s_normalize_lead_form_lang( $lang ) {
    $lang = strtolower( preg_replace( '/[^a-zA-Z_-]/', '', (string) $lang ) );
    if ( '' === $lang ) {
        $lang = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
    }
    return substr( str_replace( '_', '-', $lang ), 0, 2 );
}

function az9s_get_lead_form_variant( $form, $lang = '' ) {
    $form = is_array( $form ) ? $form : [];
    $secondary_lang = az9s_normalize_lead_form_lang( $form['secondary_lang'] ?? 'en' );
    $requested_lang = az9s_normalize_lead_form_lang( $lang );
    $use_secondary = $requested_lang && $secondary_lang && $requested_lang === $secondary_lang;

    return [
        'lang'    => $use_secondary ? $secondary_lang : az9s_normalize_lead_form_lang( get_locale() ),
        'heading' => $use_secondary ? ( $form['heading_alt'] ?? '' ) : ( $form['heading'] ?? '' ),
        'intro'   => $use_secondary ? ( $form['intro_alt'] ?? '' ) : ( $form['intro'] ?? '' ),
        'content' => $use_secondary && ! empty( $form['content_alt'] ) ? $form['content_alt'] : ( $form['content'] ?? az9s_default_lead_form_template() ),
        'success' => $use_secondary && ! empty( $form['success_alt'] ) ? $form['success_alt'] : ( $form['success'] ?? '' ),
    ];
}

add_action( 'admin_post_az9s_save_lead_form', function() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Bạn không có quyền thực hiện thao tác này.' );
    }
    check_admin_referer( 'az9s_save_lead_form' );

    $forms = az9s_get_lead_forms();
    $requested_id = preg_replace( '/[^a-zA-Z0-9_-]/', '', wp_unslash( $_POST['form_id'] ?? '' ) );
    $id = az9s_resolve_lead_form_id( $requested_id, $forms );
    if ( ! $id ) {
        $id = 'form_' . strtolower( wp_generate_password( 8, false, false ) );
    }
    $existing = isset( $forms[ $id ] ) && is_array( $forms[ $id ] ) ? $forms[ $id ] : [];
    $secondary_lang = az9s_normalize_lead_form_lang( wp_unslash( $_POST['secondary_lang'] ?? ( $existing['secondary_lang'] ?? 'en' ) ) );
    $editor_lang = az9s_normalize_lead_form_lang( wp_unslash( $_POST['editor_lang'] ?? '' ) );
    $is_secondary_editor = $secondary_lang && $editor_lang === $secondary_lang;
    $form = wp_parse_args( $existing, [
        'title'          => 'Biểu mẫu liên hệ',
        'heading'        => '',
        'intro'          => '',
        'content'        => az9s_default_lead_form_template(),
        'success'        => 'Cảm ơn bạn! Chúng tôi sẽ liên hệ trong thời gian sớm nhất.',
        'secondary_lang' => 'en',
        'heading_alt'    => '',
        'intro_alt'      => '',
        'content_alt'    => '',
        'success_alt'    => '',
    ] );

    $form['title'] = sanitize_text_field( wp_unslash( $_POST['title'] ?? $form['title'] ) );
    $form['secondary_lang'] = $secondary_lang;

    if ( $is_secondary_editor ) {
        $form['heading_alt'] = sanitize_text_field( wp_unslash( $_POST['heading'] ?? '' ) );
        $form['intro_alt']   = sanitize_textarea_field( wp_unslash( $_POST['intro'] ?? '' ) );
        $form['content_alt'] = wp_kses_post( wp_unslash( $_POST['content'] ?? '' ) );
        $form['success_alt'] = sanitize_text_field( wp_unslash( $_POST['success'] ?? '' ) );
    } else {
        $form['heading'] = sanitize_text_field( wp_unslash( $_POST['heading'] ?? '' ) );
        $form['intro']   = sanitize_textarea_field( wp_unslash( $_POST['intro'] ?? '' ) );
        $form['content'] = wp_kses_post( wp_unslash( $_POST['content'] ?? '' ) );
        $form['success'] = sanitize_text_field( wp_unslash( $_POST['success'] ?? 'Cảm ơn bạn! Chúng tôi sẽ liên hệ trong thời gian sớm nhất.' ) );
    }

    $forms[ $id ] = $form;
    update_option( 'az9s_lead_forms', $forms, false );

    $redirect_url = admin_url( 'admin.php?page=az9s-leads&tab=forms&edit=' . rawurlencode( $id ) . '&saved=1' );
    if ( $is_secondary_editor ) {
        $redirect_url = add_query_arg( 'lang', $secondary_lang, $redirect_url );
    }
    wp_safe_redirect( $redirect_url );
    exit;
} );

add_action( 'admin_post_az9s_delete_lead_form', function() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Bạn không có quyền thực hiện thao tác này.' );
    }
    $forms = az9s_get_lead_forms();
    $id = az9s_resolve_lead_form_id( wp_unslash( $_GET['form_id'] ?? '' ), $forms );
    if ( ! $id ) {
        wp_safe_redirect( admin_url( 'admin.php?page=az9s-leads&tab=forms&error=not-found' ) );
        exit;
    }
    check_admin_referer( 'az9s_delete_lead_form_' . $id );
    unset( $forms[ $id ] );
    update_option( 'az9s_lead_forms', $forms, false );
    wp_safe_redirect( admin_url( 'admin.php?page=az9s-leads&tab=forms&deleted=1' ) );
    exit;
} );

function az9s_lead_forms_admin_page() {
    $forms = az9s_get_lead_forms();
    $requested_edit_id = preg_replace( '/[^a-zA-Z0-9_-]/', '', wp_unslash( $_GET['edit'] ?? '' ) );
    $edit_id = az9s_resolve_lead_form_id( $requested_edit_id, $forms );
    $editing = $edit_id && isset( $forms[ $edit_id ] ) ? $forms[ $edit_id ] : null;
    ?>
    <div class="wrap az9s-form-admin">
        <h1>Quản lý Leads</h1>
        <nav class="nav-tab-wrapper">
            <a class="nav-tab" href="<?php echo esc_url( admin_url( 'admin.php?page=az9s-leads' ) ); ?>">Danh sách Leads</a>
            <a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( 'admin.php?page=az9s-leads&tab=forms' ) ); ?>">Biểu mẫu</a>
            <a class="nav-tab" href="<?php echo esc_url( admin_url( 'admin.php?page=az9s-leads&tab=smtp' ) ); ?>">SMTP</a>
        </nav>

        <?php if ( isset( $_GET['saved'] ) ) : ?><div class="notice notice-success is-dismissible"><p>Đã lưu biểu mẫu.</p></div><?php endif; ?>
        <?php if ( isset( $_GET['deleted'] ) ) : ?><div class="notice notice-success is-dismissible"><p>Đã xóa biểu mẫu.</p></div><?php endif; ?>
        <?php if ( $requested_edit_id && ! $editing ) : ?><div class="notice notice-error"><p>Không tìm thấy biểu mẫu cần sửa. Vui lòng chọn lại trong danh sách bên dưới.</p></div><?php endif; ?>

        <?php if ( $editing || isset( $_GET['new'] ) ) :
            $form = $editing ?: [ 'title' => 'Biểu mẫu liên hệ', 'heading' => 'ĐĂNG KÝ TƯ VẤN', 'intro' => 'Hãy điền đầy đủ các thông tin được yêu cầu bên dưới, chúng tôi sẽ liên hệ trong thời gian sớm nhất.', 'content' => az9s_default_lead_form_template(), 'success' => 'Cảm ơn bạn! Chúng tôi sẽ liên hệ trong thời gian sớm nhất.', 'secondary_lang' => 'en' ];
            $admin_lang = az9s_normalize_lead_form_lang( wp_unslash( $_GET['lang'] ?? get_locale() ) );
            $secondary_lang = az9s_normalize_lead_form_lang( $form['secondary_lang'] ?? 'en' );
            $editing_secondary = $edit_id && $admin_lang === $secondary_lang;
            $editor_variant = $editing_secondary ? az9s_get_lead_form_variant( $form, $secondary_lang ) : az9s_get_lead_form_variant( $form, 'vi' );
            $primary_edit_url = $edit_id ? remove_query_arg( 'lang', admin_url( 'admin.php?page=az9s-leads&tab=forms&edit=' . rawurlencode( $edit_id ) ) ) : '';
            $secondary_edit_url = $edit_id ? add_query_arg( 'lang', $secondary_lang, admin_url( 'admin.php?page=az9s-leads&tab=forms&edit=' . rawurlencode( $edit_id ) ) ) : '';
            ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="az9s-form-editor">
                <input type="hidden" name="action" value="az9s_save_lead_form">
                <input type="hidden" name="form_id" value="<?php echo esc_attr( $edit_id ); ?>">
                <input type="hidden" name="editor_lang" value="<?php echo esc_attr( $editing_secondary ? $secondary_lang : 'vi' ); ?>">
                <?php wp_nonce_field( 'az9s_save_lead_form' ); ?>
                <div class="az9s-editor-main">
                    <h2><?php echo $editing ? 'Chỉnh sửa biểu mẫu' : 'Thêm biểu mẫu'; ?><?php echo $editing_secondary ? ' - Ngôn ngữ 2' : ' - Ngôn ngữ chính'; ?></h2>
                    <?php if ( $edit_id ) : ?>
                        <p class="az9s-language-switch">
                            <a class="button <?php echo $editing_secondary ? '' : 'button-primary'; ?>" href="<?php echo esc_url( $primary_edit_url ); ?>">Ngôn ngữ chính</a>
                            <a class="button <?php echo $editing_secondary ? 'button-primary' : ''; ?>" href="<?php echo esc_url( $secondary_edit_url ); ?>">Ngôn ngữ 2 (<?php echo esc_html( strtoupper( $secondary_lang ) ); ?>)</a>
                        </p>
                    <?php endif; ?>
                    <label class="az9s-field-label">Tên biểu mẫu
                        <input type="text" name="title" value="<?php echo esc_attr( $form['title'] ); ?>" required>
                    </label>
                    <div class="az9s-admin-columns">
                        <label class="az9s-field-label">Tiêu đề hiển thị
                            <input type="text" name="heading" value="<?php echo esc_attr( $editor_variant['heading'] ?? '' ); ?>" placeholder="ĐĂNG KÝ LÁI THỬ">
                        </label>
                        <label class="az9s-field-label">Mô tả đầu form
                            <textarea name="intro" rows="3" placeholder="Hướng dẫn khách hàng điền biểu mẫu"><?php echo esc_textarea( $editor_variant['intro'] ?? '' ); ?></textarea>
                        </label>
                    </div>
                    <div class="az9s-tag-buttons">
                        <strong>Chèn trường:</strong>
                        <button type="button" data-tag='[text* name placeholder "Họ và tên"]'>Text</button>
                        <button type="button" data-tag='[email email placeholder "Email"]'>Email</button>
                        <button type="button" data-tag='[url website_url placeholder "https://..."]'>URL</button>
                        <button type="button" data-tag='[tel* phone placeholder "Số điện thoại"]'>Điện thoại</button>
                        <button type="button" data-tag='[number quantity min:1 max:100 placeholder "Số lượng"]'>Số</button>
                        <button type="button" data-tag='[range budget min:0 max:100 step:1]'>Thanh trượt</button>
                        <button type="button" data-tag='[date appointment_date]'>Ngày</button>
                        <button type="button" data-tag='[textarea message placeholder "Nội dung"]'>Nội dung</button>
                        <button type="button" data-tag='[select service first_as_label "Chọn nhu cầu" "Tư vấn sản phẩm" "Báo giá" "Dịch vụ"]'>Danh sách</button>
                        <button type="button" data-tag='[select_posts* mau-xe post_type:cac-dong-xe first_as_label "Mẫu xe*"]'>Bài viết động</button>
                        <button type="button" data-tag='[select_terms* danh-muc taxonomy:category first_as_label "Chọn danh mục*"]'>Danh mục động</button>
                        <button type="button" data-tag='[checkbox interests "Sản phẩm" "Dịch vụ" "Khuyến mãi"]'>Checkbox</button>
                        <button type="button" data-tag='[radio contact_method "Điện thoại" "Zalo" "Email"]'>Radio</button>
                        <button type="button" data-tag='[acceptance* consent] Tôi đồng ý với các điều khoản và chính sách riêng tư. [/acceptance]'>Điều khoản</button>
                        <button type="button" data-tag='[quiz security_question "Thủ đô Việt Nam là gì?|Hà Nội"]'>Câu hỏi</button>
                        <button type="button" data-tag='[file attachment limit:5mb filetypes:jpg|jpeg|png|pdf|doc|docx]'>File</button>
                        <button type="button" data-tag='[hidden source "website"]'>Trường ẩn</button>
                        <button type="button" data-tag='[submit "Gửi yêu cầu"]'>Nút gửi</button>
                    </div>
                    <textarea id="az9s-form-template" name="content" rows="17"><?php echo esc_textarea( $editor_variant['content'] ); ?></textarea>
        <p class="description">Thêm dấu <code>*</code> sau loại trường để bắt buộc. Tên chuẩn nên dùng: <code>name</code>, <code>phone</code>, <code>email</code>, <code>message</code>. File hỗ trợ giới hạn dạng <code>limit:5mb</code> và <code>filetypes:jpg|png|pdf</code>.</p>
                    <label class="az9s-field-label">Thông báo gửi thành công
                        <input type="text" name="success" value="<?php echo esc_attr( $editor_variant['success'] ); ?>">
                    </label>
                    <hr>
                    <h2>Cấu hình ngôn ngữ</h2>
                    <p class="description">Mỗi form chỉ dùng một shortcode <code>[az9s_lead_form id=&quot;...&quot;]</code>. Chọn ngôn ngữ trên admin bar hoặc thêm <code>&amp;lang=<?php echo esc_html( $secondary_lang ); ?></code> vào URL quản trị để sửa riêng bản ngôn ngữ 2.</p>
                    <label class="az9s-field-label">Mã ngôn ngữ thứ 2
                        <input type="text" name="secondary_lang" value="<?php echo esc_attr( $secondary_lang ); ?>" placeholder="en">
                    </label>
                </div>
                <div class="az9s-editor-side">
                    <div class="az9s-publish-box">
                        <h3>Đăng biểu mẫu</h3>
                        <?php if ( $edit_id ) : ?>
                            <p>Shortcode</p>
                            <code class="az9s-shortcode">[az9s_lead_form id="<?php echo esc_html( $edit_id ); ?>"]</code>
                        <?php endif; ?>
                        <?php submit_button( 'Lưu biểu mẫu', 'primary large', 'submit', false ); ?>
                    </div>
                </div>
            </form>
        <?php else : ?>
            <div class="az9s-list-heading">
                <h2>Danh sách biểu mẫu</h2>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=az9s-leads&tab=forms&new=1' ) ); ?>" class="button button-primary">Thêm biểu mẫu</a>
            </div>
            <table class="wp-list-table widefat fixed striped">
                <thead><tr><th>Tên biểu mẫu</th><th>Shortcode</th><th style="width:180px">Thao tác</th></tr></thead>
                <tbody>
                <?php if ( ! $forms ) : ?>
                    <tr><td colspan="3">Chưa có biểu mẫu. Hãy tạo biểu mẫu đầu tiên.</td></tr>
                <?php else : foreach ( $forms as $id => $form ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( $form['title'] ); ?></strong></td>
                        <td><code>[az9s_lead_form id="<?php echo esc_html( $id ); ?>"]</code></td>
                        <td>
                            <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=az9s-leads&tab=forms&edit=' . $id ) ); ?>">Sửa</a>
                            <a class="button button-link-delete" onclick="return confirm('Xóa biểu mẫu này?')" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=az9s_delete_lead_form&form_id=' . $id ), 'az9s_delete_lead_form_' . $id ) ); ?>">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <style>
        .az9s-form-admin .nav-tab-wrapper{margin-bottom:20px}.az9s-form-editor{display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:20px;max-width:1200px}.az9s-editor-main,.az9s-publish-box{padding:20px;background:#fff;border:1px solid #ccd0d4}.az9s-editor-main h2,.az9s-publish-box h3{margin-top:0}.az9s-field-label{display:block;margin-bottom:18px;font-weight:600}.az9s-field-label input,.az9s-form-editor textarea{box-sizing:border-box;width:100%;margin-top:7px}.az9s-form-editor textarea{font-family:Consolas,monospace;font-size:14px;line-height:1.6}.az9s-admin-columns{display:grid;grid-template-columns:1fr 1fr;gap:15px}.az9s-tag-buttons{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:9px}.az9s-tag-buttons button{padding:5px 9px;background:#f6f7f7;border:1px solid #8c8f94;border-radius:3px;cursor:pointer}.az9s-publish-box{position:sticky;top:45px}.az9s-shortcode{display:block;margin:8px 0 20px;padding:10px;word-break:break-all}.az9s-list-heading{display:flex;justify-content:space-between;align-items:center}.button-link-delete{color:#b32d2e!important}@media(max-width:800px){.az9s-form-editor,.az9s-admin-columns{grid-template-columns:1fr}.az9s-publish-box{position:static}}
    </style>
    <script>
    document.querySelectorAll('.az9s-tag-buttons button').forEach(function(button) {
        button.addEventListener('click', function() {
            var editor = document.activeElement && document.activeElement.tagName === 'TEXTAREA' ? document.activeElement : document.getElementById('az9s-form-template');
            var start = editor.selectionStart;
            editor.value = editor.value.slice(0, start) + button.dataset.tag + editor.value.slice(editor.selectionEnd);
            editor.focus();
            editor.selectionStart = editor.selectionEnd = start + button.dataset.tag.length;
        });
    });
    </script>
    <?php
}

add_shortcode( 'az9s_lead_form', function( $atts ) {
    $atts = shortcode_atts( [ 'id' => '' ], $atts, 'az9s_lead_form' );
    $forms = az9s_get_lead_forms();
    $id = az9s_resolve_lead_form_id( $atts['id'], $forms );
    if ( ! $id || empty( $forms[ $id ] ) ) {
        return current_user_can( 'manage_options' ) ? '<p>Không tìm thấy biểu mẫu Lead.</p>' : '';
    }
    $form = $forms[ $id ];
    $variant = az9s_get_lead_form_variant( $form );
    $html = az9s_parse_lead_form_tags( $variant['content'] );
    ob_start();
    ?>
    <section class="az9s-lead-form-block">
        <?php if ( ! empty( $variant['heading'] ) ) : ?><h2 class="az9s-lead-form-title"><?php echo esc_html( $variant['heading'] ); ?></h2><?php endif; ?>
        <?php if ( ! empty( $variant['intro'] ) ) : ?><p class="az9s-lead-form-intro"><?php echo esc_html( $variant['intro'] ); ?></p><?php endif; ?>
        <form class="az9s-dynamic-lead-form" data-form-id="<?php echo esc_attr( $id ); ?>" data-form-lang="<?php echo esc_attr( $variant['lang'] ); ?>">
            <?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <input type="hidden" name="form_lang" value="<?php echo esc_attr( $variant['lang'] ); ?>">
            <input type="text" name="_az9s_hp" value="" tabindex="-1" autocomplete="off" class="az9s-honeypot">
            <div class="az9s-form-response" aria-live="polite"></div>
        </form>
    </section>
    <?php
    az9s_lead_form_assets_once();
    return ob_get_clean();
} );

function az9s_parse_lead_form_tags( $template ) {
    // Làm sạch HTML do quản trị viên nhập trước khi sinh các thẻ input.
    // Nếu lọc sau bước này, wp_kses_post sẽ xóa checkbox của acceptance.
    $template = wp_kses_post( $template );

    $template = preg_replace_callback( '/\[acceptance(\*)?\s+([a-zA-Z0-9_-]+)([^\]]*)\](.*?)\[\/acceptance\]/is', function( $match ) {
        $options = preg_split( '/\s+/', trim( $match[3] ) );
        $invert = in_array( 'invert', $options, true );
        return sprintf(
            '<label class="az9s-choice az9s-acceptance"><input type="checkbox" name="%s" value="%s"%s> <span>%s</span></label>',
            esc_attr( sanitize_key( $match[2] ) ),
            $invert ? 'Không đồng ý' : 'Đồng ý',
            ' required',
            wp_kses_post( trim( $match[4] ) )
        );
    }, $template );

    return preg_replace_callback( '/\[(text|tel|email|url|number|range|date|textarea|select|select_posts|select_terms|checkbox|radio|quiz|file|hidden|submit)(\*)?\s+([^\]]+)\]/', function( $match ) {
        $type = $match[1];
        $required = ! empty( $match[2] );
        preg_match_all( '/"([^"]*)"|(\S+)/', trim( $match[3] ), $parts );
        $tokens = [];
        foreach ( $parts[0] as $i => $raw ) {
            $tokens[] = '' !== $parts[1][ $i ] ? $parts[1][ $i ] : $parts[2][ $i ];
        }
        if ( 'submit' === $type ) {
            return '<button type="submit" class="az9s-form-submit">' . esc_html( $tokens[0] ?? 'Gửi yêu cầu' ) . '</button>';
        }
        $name = sanitize_key( array_shift( $tokens ) );
        $placeholder = '';
        $placeholder_index = array_search( 'placeholder', $tokens, true );
        if ( false !== $placeholder_index ) {
            $placeholder = $tokens[ $placeholder_index + 1 ] ?? '';
            array_splice( $tokens, $placeholder_index, 2 );
        }
        $required_attr = $required ? ' required' : '';
        $attributes = '';
        $class_names = [];
        $field_id = '';
        foreach ( $tokens as $index => $token ) {
            if ( preg_match( '/^(min|max|step):(.+)$/', $token, $attribute ) ) {
                $attributes .= ' ' . esc_attr( $attribute[1] ) . '="' . esc_attr( $attribute[2] ) . '"';
                unset( $tokens[ $index ] );
            } elseif ( 0 === strpos( $token, 'class:' ) ) {
                $class_names[] = sanitize_html_class( substr( $token, 6 ) );
                unset( $tokens[ $index ] );
            } elseif ( 0 === strpos( $token, 'id:' ) ) {
                $field_id = sanitize_html_class( substr( $token, 3 ) );
                unset( $tokens[ $index ] );
            } elseif ( 0 === strpos( $token, 'autocomplete:' ) ) {
                $attributes .= ' autocomplete="' . esc_attr( substr( $token, 13 ) ) . '"';
                unset( $tokens[ $index ] );
            }
        }
        $tokens = array_values( $tokens );
        $class_attr = $class_names ? ' class="' . esc_attr( implode( ' ', $class_names ) ) . '"' : '';
        $id_attr = $field_id ? ' id="' . esc_attr( $field_id ) . '"' : '';
        if ( 'textarea' === $type ) {
            return sprintf( '<textarea name="%s" placeholder="%s"%s%s%s%s></textarea>', esc_attr( $name ), esc_attr( $placeholder ), $required_attr, $class_attr, $id_attr, $attributes );
        }
        if ( in_array( $type, [ 'select', 'select_posts', 'select_terms' ], true ) ) {
            $first_as_label = in_array( 'first_as_label', $tokens, true );
            $multiple = in_array( 'multiple', $tokens, true );
            $tokens = array_values( array_diff( $tokens, [ 'first_as_label', 'multiple' ] ) );
            $dynamic_options = [];

            if ( 'select_posts' === $type ) {
                $post_type = az9s_form_token_value( $tokens, 'post_type', 'post' );
                $orderby = az9s_form_token_value( $tokens, 'orderby', 'title' );
                $order = strtoupper( az9s_form_token_value( $tokens, 'order', 'ASC' ) );
                $post_type_object = post_type_exists( $post_type ) ? get_post_type_object( $post_type ) : null;
                if ( $post_type_object && $post_type_object->public ) {
                    $posts = get_posts( [
                        'post_type'      => $post_type,
                        'post_status'    => 'publish',
                        'posts_per_page' => -1,
                        'orderby'        => in_array( $orderby, [ 'title', 'date', 'menu_order' ], true ) ? $orderby : 'title',
                        'order'          => 'DESC' === $order ? 'DESC' : 'ASC',
                        'no_found_rows'  => true,
                    ] );
                    foreach ( $posts as $option_post ) {
                        $dynamic_options[] = get_the_title( $option_post );
                    }
                }
                $tokens = az9s_remove_form_config_tokens( $tokens, [ 'post_type', 'orderby', 'order', 'value' ] );
            } elseif ( 'select_terms' === $type ) {
                $taxonomy = az9s_form_token_value( $tokens, 'taxonomy', 'category' );
                if ( taxonomy_exists( $taxonomy ) ) {
                    $terms = get_terms( [
                        'taxonomy'   => $taxonomy,
                        'hide_empty' => false,
                        'orderby'    => 'name',
                        'order'      => 'ASC',
                    ] );
                    if ( ! is_wp_error( $terms ) ) {
                        foreach ( $terms as $term ) {
                            $dynamic_options[] = $term->name;
                        }
                    }
                }
                $tokens = az9s_remove_form_config_tokens( $tokens, [ 'taxonomy', 'value' ] );
            }

            // Chuỗi đặt trong dấu ngoặc kép đầu tiên là nhãn lựa chọn.
            $label_option = $tokens[0] ?? '';
            if ( $dynamic_options ) {
                $tokens = $first_as_label && $label_option
                    ? array_merge( [ $label_option ], $dynamic_options )
                    : $dynamic_options;
            }
            $options = '';
            foreach ( $tokens as $index => $option ) {
                $is_placeholder = 0 === $index && ( $first_as_label || $required );
                $options .= '<option value="' . ( $is_placeholder ? '' : esc_attr( $option ) ) . '"' . ( $is_placeholder ? ' disabled selected' : '' ) . '>' . esc_html( $option ) . '</option>';
            }
            return sprintf( '<select name="%s%s"%s%s%s%s>%s</select>', esc_attr( $name ), $multiple ? '[]' : '', $required_attr, $multiple ? ' multiple' : '', $class_attr, $id_attr, $options );
        }
        if ( in_array( $type, [ 'checkbox', 'radio' ], true ) ) {
            $tokens = array_values( array_diff( $tokens, [ 'use_label_element', 'exclusive' ] ) );
            $choices = '<span class="az9s-choice-group">';
            foreach ( $tokens as $option ) {
                $choices .= sprintf( '<label class="az9s-choice"><input type="%s" name="%s%s" value="%s"%s> <span>%s</span></label>', esc_attr( $type ), esc_attr( $name ), 'checkbox' === $type ? '[]' : '', esc_attr( $option ), $required_attr, esc_html( $option ) );
            }
            return $choices . '</span>';
        }
        if ( 'quiz' === $type ) {
            $quiz = explode( '|', $tokens[0] ?? '', 2 );
            return sprintf( '<label class="az9s-quiz"><span>%s</span><input type="text" name="%s" autocomplete="off"%s></label>', esc_html( $quiz[0] ?? 'Câu hỏi xác nhận' ), esc_attr( $name ), $required_attr );
        }
        if ( 'file' === $type ) {
            $accept = '';
            foreach ( $tokens as $token ) {
                if ( 0 === strpos( $token, 'filetypes:' ) ) {
                    $extensions = explode( '|', substr( $token, 10 ) );
                    $accept = implode( ',', array_map( static function( $ext ) { return '.' . sanitize_key( $ext ); }, $extensions ) );
                }
            }
            return sprintf( '<input type="file" name="%s" accept="%s"%s%s%s>', esc_attr( $name ), esc_attr( $accept ), $required_attr, $class_attr, $id_attr );
        }
        if ( 'hidden' === $type ) {
            return sprintf( '<input type="hidden" name="%s" value="%s">', esc_attr( $name ), esc_attr( $tokens[0] ?? '' ) );
        }
        if ( 'tel' === $type ) {
            $attributes .= ' inputmode="numeric" pattern="[0-9]{9,15}" minlength="9" maxlength="15"';
        }
        return sprintf( '<input type="%s" name="%s" placeholder="%s"%s%s%s%s>', esc_attr( $type ), esc_attr( $name ), esc_attr( $placeholder ), $required_attr, $class_attr, $id_attr, $attributes );
    }, $template );
}

function az9s_form_token_value( $tokens, $key, $default = '' ) {
    foreach ( $tokens as $token ) {
        if ( 0 === strpos( $token, $key . ':' ) ) {
            return sanitize_key( substr( $token, strlen( $key ) + 1 ) );
        }
    }
    return $default;
}

function az9s_remove_form_config_tokens( $tokens, $keys ) {
    return array_values( array_filter( $tokens, static function( $token ) use ( $keys ) {
        foreach ( $keys as $key ) {
            if ( 0 === strpos( $token, $key . ':' ) ) {
                return false;
            }
        }
        return true;
    } ) );
}

function az9s_lead_form_assets_once() {
    static $printed = false;
    if ( $printed ) return;
    $printed = true;
    ?>
    <?php az9s_print_shared_form_styles(); ?>
    <script>
    document.addEventListener('submit', function(event) {
        var form = event.target.closest('.az9s-dynamic-lead-form');
        if (!form) return;
        event.preventDefault();
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        var button = form.querySelector('[type="submit"]');
        var response = form.querySelector('.az9s-form-response');
        var data = new FormData(form);
        data.append('action', 'az9s_dynamic_lead_submit');
        data.append('form_id', form.dataset.formId);
        data.append('nonce', '<?php echo esc_js( wp_create_nonce( 'az9s_dynamic_lead' ) ); ?>');
        var now = new Date();
        var pad = function(value) { return String(value).padStart(2, '0'); };
        data.append('client_time', now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds()));
        button.disabled = true;
        response.className = 'az9s-form-response';
        response.textContent = 'Đang gửi...';
        fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {method:'POST', body:data, credentials:'same-origin'})
            .then(function(r){return r.json();})
            .then(function(result){
                response.classList.add(result.success ? 'is-success' : 'is-error');
                response.textContent = result.data || (result.success ? 'Đã gửi thành công.' : 'Không thể gửi biểu mẫu.');
                if (result.success) form.reset();
            })
            .catch(function(){response.classList.add('is-error');response.textContent='Có lỗi kết nối, vui lòng thử lại.';})
            .finally(function(){button.disabled=false;});
    });
    </script>
    <?php
}

/**
 * Style thống nhất cho form Leads mới, Contact Form 7 và form liên hệ cũ.
 */
function az9s_print_shared_form_styles() {
    static $printed = false;
    if ( $printed ) {
        return;
    }
    $printed = true;
    ?>
    <style id="az9s-shared-form-styles">
        .az9s-lead-form-block{
            box-sizing:border-box;
            width:100%;
            margin:0 auto;
            padding:48px 32px 100px;
            background:#f4f4f5;
            color:#111827;
            font-family:"CityFordSansText",inherit;
        }
        .az9s-lead-form-title{
            margin:0 auto 32px!important;
            color:#001b70!important;
            font-family:"CityFordSansText",inherit!important;
            font-size:clamp(32px,4vw,48px)!important;
            font-weight:400!important;
            line-height:1.15!important;
            letter-spacing:-.02em!important;
            text-align:center!important;
            text-transform:uppercase;
        }
        .az9s-lead-form-intro{
            max-width:1100px;
            margin:0 auto 44px!important;
            color:#252525;
            font-size:18px;
            font-weight:400;
            line-height:1.65;
            text-align:center;
            text-transform:uppercase;
        }
        :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form){
            --az9s-form-primary:#001b70;
            --az9s-form-border:#202020;
            --az9s-form-focus:#1456a0;
            --az9s-form-bg:#fff;
            display:grid;
            gap:40px;
            box-sizing:border-box;
            width:100%;
            max-width:1270px;
            margin-right:auto;
            margin-left:auto;
            padding:0;
            border:0;
            border-radius:0;
            background:transparent;
            color:#111827;
            font-family:"CityFordSansText",inherit;
            font-size:16px;
            box-shadow:none;
        }
        :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form) :is(p,label){
            margin:0;
        }
        :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form) label{
            display:grid;
            gap:7px;
            margin-bottom:20px!important;
            color:#111;
            font-size:14px;
            font-weight:400;
            line-height:1.4;
        }
        :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form) .wpcf7-form-control-wrap{
            display:block;
            margin-top:7px;
        }
        :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form)
        :is(input[type=text],input[type=email],input[type=tel],input[type=url],input[type=number],input[type=date],input[type=file],textarea,select){
            box-sizing:border-box;
            width:100%;
            height:51px;
            margin:0;
            padding:12px 13px;
            border:1px solid var(--az9s-form-border);
            border-radius:4px;
            outline:none;
            background:#f7f7f8;
            color:#111827;
            font-family:"CityFordSansText",inherit;
            font-size:15px;
            font-weight:400;
            line-height:1.4;
            box-shadow:none;
            transition:border-color .2s,box-shadow .2s,background-color .2s,transform .2s;
        }
        :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form)
        :is(input,textarea,select):focus{
            border-color:var(--az9s-form-focus);
            background:#fff;
            box-shadow:0 0 0 3px rgba(20,86,160,.12);
        }
        :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form)
        :is(input,textarea)::placeholder{
            color:#737984;
            opacity:1;
        }
        :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form) select{
            -webkit-appearance:none;
            -moz-appearance:none;
            appearance:none;
            padding-right:46px!important;
            background-color:#f7f7f8!important;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23111827' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E")!important;
            background-repeat:no-repeat!important;
            background-position:right 15px center!important;
            background-size:14px 14px!important;
            cursor:pointer;
        }
        :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form) select:focus{
            background-color:#fff!important;
        }
        :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form) textarea{
            height:auto;
            min-height:125px;
            resize:vertical;
        }
        :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form)
        :is(.az9s-form-row,.ct-field-row,.form-row-2,.form-columns-2,.cf7-row){
            display:grid;
            grid-template-columns:minmax(0,1fr) minmax(0,1fr);
            gap:34px 28px;
            width:100%;
        }
        :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form) .az9s-choice-group{
            display:flex;
            gap:14px 24px;
            flex-wrap:wrap;
        }
        :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form) .az9s-acceptance{
            position:static!important;
            display:flex!important;
            align-items:flex-start;
            justify-self:start;
            max-width:100%;
            margin:0!important;
            padding:0!important;
            font-size:14px;
            line-height:1.55;
        }
        label.az9s-choice.az9s-acceptance{
            margin-bottom:20px!important;
        }
        :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form)
        :is(.az9s-choice,.wpcf7-list-item label){
            display:inline-flex;
            grid-template:none;
            align-items:center;
            gap:7px;
            font-size:14px;
            font-weight:400;
        }
        :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form)
        :is(input[type=checkbox],input[type=radio]){
            position:static!important;
            inset:auto!important;
            display:inline-block!important;
            flex:0 0 auto;
            -webkit-appearance:auto!important;
            appearance:auto!important;
            width:16px;
            height:16px;
            margin:0;
            padding:0!important;
            opacity:1!important;
            visibility:visible!important;
            transform:none!important;
            accent-color:var(--az9s-form-primary);
        }
        :is(.ct-form-body,.tp-project-form) .az9s-lead-form-block,
        .az9s-lead-form-block.az9s-contact-style{
            margin:0;
            padding:0;
            max-width:none;
            background:transparent;
        }
        :is(.ct-form-body,.tp-project-form) .az9s-lead-form-title,
        :is(.ct-form-body,.tp-project-form) .az9s-lead-form-intro,
        .az9s-lead-form-block.az9s-contact-style .az9s-lead-form-title,
        .az9s-lead-form-block.az9s-contact-style .az9s-lead-form-intro{
            display:none;
        }
        :is(.ct-form-body,.tp-project-form) .az9s-dynamic-lead-form,
        .az9s-contact-style .az9s-dynamic-lead-form{
            display:block;
            padding:0;
            background:transparent;
            border:0;
            border-radius:0;
            box-shadow:none;
        }
        :is(.ct-form-body,.tp-project-form) .az9s-dynamic-lead-form .ct-field-row,
        .az9s-contact-style .az9s-dynamic-lead-form .ct-field-row{
            display:grid;
            grid-template-columns:minmax(0,1fr) minmax(0,1fr);
            gap:28px;
            margin:0 0 28px;
        }
        :is(.ct-form-body,.tp-project-form) .az9s-dynamic-lead-form .ct-field,
        .az9s-contact-style .az9s-dynamic-lead-form .ct-field{
            margin:0 0 28px;
        }
        :is(.ct-form-body,.tp-project-form) .az9s-dynamic-lead-form .ct-field label,
        .az9s-contact-style .az9s-dynamic-lead-form .ct-field label{
            display:block;
            margin:0 0 8px!important;
            color:#111827;
            font-size:14px;
            font-weight:400;
            line-height:1.4;
        }
        :is(.ct-form-body,.tp-project-form) .az9s-dynamic-lead-form .ct-field :is(input[type=text],input[type=email],input[type=tel],input[type=url],input[type=number],textarea,select),
        .az9s-contact-style .az9s-dynamic-lead-form .ct-field :is(input[type=text],input[type=email],input[type=tel],input[type=url],input[type=number],textarea,select){
            width:100%;
            height:51px;
            margin:0;
            padding:13px 14px;
            border:1px solid #111;
            border-radius:3px;
            background:#f7f7f8;
            color:#111827;
            font-size:15px;
            font-weight:400;
            box-shadow:none;
        }
        :is(.ct-form-body,.tp-project-form) .az9s-dynamic-lead-form .ct-field textarea,
        .az9s-contact-style .az9s-dynamic-lead-form .ct-field textarea{
            min-height:125px;
            height:125px;
            resize:vertical;
        }
        :is(.ct-form-body,.tp-project-form) .az9s-dynamic-lead-form .ct-field ::placeholder,
        .az9s-contact-style .az9s-dynamic-lead-form .ct-field ::placeholder{
            color:#7d8796;
            opacity:1;
        }
        :is(.ct-form-body,.tp-project-form) .az9s-dynamic-lead-form .az9s-form-submit,
        .az9s-contact-style .az9s-dynamic-lead-form .az9s-form-submit{
            min-height:0;
            margin:4px 0 0;
            padding:12px 20px;
            border:0;
            border-radius:999px;
            background:#147fc9;
            color:#fff;
            font-size:14px;
            font-weight:700;
            line-height:1.1;
            text-transform:uppercase;
            box-shadow:0 11px 17px rgba(0,0,0,.28);
        }
        :is(.ct-form-body,.tp-project-form) .az9s-dynamic-lead-form .az9s-form-submit:hover,
        .az9s-contact-style .az9s-dynamic-lead-form .az9s-form-submit:hover{
            background:#0c72bd;
            transform:translateY(-1px);
        }
        .az9s-lead-form-block :is(p:empty,span:empty,label:empty){display:none!important}
        :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form)
        :is(.az9s-form-submit,input[type=submit],button[type=submit]){
            justify-self:start;
            min-height:0;
            margin:0;
            padding:11px 20px;
            border:0;
            border-radius:999px;
            background:#001b70;
            color:#fff;
            font-size:13px;
            font-weight:700;
            line-height:1.2;
            letter-spacing:.02em;
            text-transform:uppercase;
            box-shadow:0 12px 18px rgba(0,0,0,.3);
            cursor:pointer;
            transition:transform .2s,box-shadow .2s,background-color .2s;
        }
        :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form)
        :is(.az9s-form-submit,input[type=submit],button[type=submit]):hover{
            background:#063b93;
            color:#fff;
            transform:translateY(-1px);
            box-shadow:0 13px 21px rgba(0,0,0,.32);
        }
        .az9s-form-submit[disabled]{opacity:.6;cursor:wait}
        .az9s-honeypot{position:absolute!important;left:-9999px!important}
        .az9s-form-response{font-weight:600}
        .az9s-form-response.is-success,.wpcf7 form.sent .wpcf7-response-output{color:#16803c}
        .az9s-form-response.is-error,.wpcf7 form.invalid .wpcf7-response-output{color:#c62828}
        .wpcf7-not-valid-tip{margin-top:4px;color:#c62828;font-size:11px}
        .wpcf7 form .wpcf7-response-output{margin:0;padding:9px 12px;border-width:1px;border-radius:4px}
        @media(max-width:600px){
            .az9s-lead-form-block{padding:28px 15px 48px}
            .az9s-lead-form-title{margin-bottom:15px!important;font-size:32px!important}
            .az9s-lead-form-intro{margin-bottom:23px!important;font-size:13px}
            :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form){padding:0;border-radius:0}
            :is(.az9s-dynamic-lead-form,.wpcf7-form,.tp-lead-form,.ct-contact-form,.az9s-contact-form)
            :is(.az9s-form-row,.ct-field-row,.form-row-2,.form-columns-2,.cf7-row){
                grid-template-columns:1fr;
                gap:15px;
            }
        }
    </style>
    <?php
}

add_action( 'wp_head', 'az9s_print_shared_form_styles', 30 );

/**
 * Validation giao diện dùng chung cho form Lead mới, CF7 và form cũ.
 */
add_action( 'wp_footer', function() {
    if ( is_admin() ) {
        return;
    }
    ?>
    <script id="az9s-shared-form-validation">
    (function() {
        var formSelector = '.az9s-dynamic-lead-form, .wpcf7-form, .tp-lead-form, .ct-contact-form, .az9s-contact-form';

        function prepareForm(form) {
            form.querySelectorAll('input[type="email"]').forEach(function(field) {
                field.setAttribute('inputmode', 'email');
            });
            form.querySelectorAll('input[type="tel"]').forEach(function(field) {
                field.setAttribute('inputmode', 'numeric');
                field.setAttribute('pattern', '[0-9]{9,15}');
                field.setAttribute('minlength', '9');
                field.setAttribute('maxlength', '15');
            });
            form.querySelectorAll('.az9s-acceptance input[type="checkbox"], .wpcf7-acceptance input[type="checkbox"]').forEach(function(field) {
                field.required = true;
            });
        }

        function validateForm(form) {
            prepareForm(form);
            var firstInvalid = null;
            form.querySelectorAll('input, select, textarea').forEach(function(field) {
                if (firstInvalid || field.disabled || 'hidden' === field.type) return;
                if (!field.checkValidity()) firstInvalid = field;
            });
            if (firstInvalid) {
                firstInvalid.reportValidity();
                firstInvalid.focus({preventScroll:true});
                firstInvalid.scrollIntoView({behavior:'smooth', block:'center'});
                return false;
            }
            return true;
        }

        document.querySelectorAll(formSelector).forEach(prepareForm);

        document.addEventListener('submit', function(event) {
            var form = event.target.closest(formSelector);
            if (form && !validateForm(form)) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);
    }());
    </script>
    <?php
}, 40 );

add_action( 'wp_ajax_az9s_dynamic_lead_submit', 'az9s_dynamic_lead_submit' );
add_action( 'wp_ajax_nopriv_az9s_dynamic_lead_submit', 'az9s_dynamic_lead_submit' );

function az9s_dynamic_lead_submit() {
    check_ajax_referer( 'az9s_dynamic_lead', 'nonce' );
    if ( ! empty( $_POST['_az9s_hp'] ) ) {
        wp_send_json_error( 'Không thể gửi biểu mẫu.' );
    }
    $forms = az9s_get_lead_forms();
    $form_id = az9s_resolve_lead_form_id( wp_unslash( $_POST['form_id'] ?? '' ), $forms );
    if ( ! $form_id || empty( $forms[ $form_id ] ) ) {
        wp_send_json_error( 'Biểu mẫu không tồn tại.' );
    }
    $variant = az9s_get_lead_form_variant( $forms[ $form_id ], wp_unslash( $_POST['form_lang'] ?? '' ) );
    $template = $variant['content'];
    $field_names = az9s_get_form_field_names_by_type( $template );
    $name_key = isset( $_POST['name'] ) ? 'name' : ( $field_names['text'][0] ?? '' );
    $phone_key = isset( $_POST['phone'] ) ? 'phone' : ( $field_names['tel'][0] ?? '' );
    $email_key = isset( $_POST['email'] ) ? 'email' : ( $field_names['email'][0] ?? '' );

    $raw_name = trim( (string) wp_unslash( $_POST[ $name_key ] ?? '' ) );
    $raw_phone = trim( (string) wp_unslash( $_POST[ $phone_key ] ?? '' ) );
    $raw_email = trim( (string) wp_unslash( $_POST[ $email_key ] ?? '' ) );
    $name = sanitize_text_field( $raw_name );
    $phone = sanitize_text_field( $raw_phone );
    $email = sanitize_email( $raw_email );
    if ( '' !== $phone && ! preg_match( '/^[0-9]{9,15}$/', $phone ) ) {
        wp_send_json_error( 'Số điện thoại chỉ được gồm từ 9 đến 15 chữ số.' );
    }
    if ( '' !== $raw_email && ! is_email( $raw_email ) ) {
        wp_send_json_error( 'Địa chỉ email không đúng định dạng.' );
    }

    // Kiểm tra tất cả trường có dấu * ở phía máy chủ.
    $required_fields = az9s_get_required_form_fields( $template );
    foreach ( $required_fields as $required_field ) {
        $required_name = $required_field['name'];
        $has_upload = 'file' === $required_field['type']
            && ! empty( $_FILES[ $required_name ]['name'] )
            && UPLOAD_ERR_OK === (int) ( $_FILES[ $required_name ]['error'] ?? UPLOAD_ERR_NO_FILE );
        $posted_value = $_POST[ $required_name ] ?? null;
        if ( is_array( $posted_value ) ) {
            $posted_value = implode( '', array_map( 'trim', wp_unslash( $posted_value ) ) );
        } elseif ( null !== $posted_value ) {
            $posted_value = trim( (string) wp_unslash( $posted_value ) );
        }
        if ( ! $has_upload && ( null === $posted_value || '' === $posted_value ) ) {
            $label = ucwords( str_replace( [ '-', '_' ], ' ', $required_name ) );
            wp_send_json_error( 'Vui lòng nhập trường bắt buộc: ' . $label . '.' );
        }
        if (
            in_array( $required_field['type'], [ 'select', 'select_posts', 'select_terms' ], true )
            && is_string( $posted_value )
            && (
                preg_match( '/^(chọn|select|--)/iu', trim( $posted_value ) )
                || ( ! empty( $required_field['placeholder'] ) && trim( $posted_value ) === $required_field['placeholder'] )
            )
        ) {
            $label = ucwords( str_replace( [ '-', '_' ], ' ', $required_name ) );
            wp_send_json_error( 'Vui lòng chọn một giá trị cho trường: ' . $label . '.' );
        }
    }

    // Điều khoản acceptance luôn bắt buộc phải được đồng ý.
    $acceptance_names = [];
    if ( preg_match_all( '/\[acceptance(\*)?\s+([a-zA-Z0-9_-]+)/', $template, $acceptances, PREG_SET_ORDER ) ) {
        foreach ( $acceptances as $acceptance ) {
            $acceptance_name = $acceptance[2];
            $acceptance_names[] = $acceptance_name;
            if ( empty( $_POST[ $acceptance_name ] ) ) {
                wp_send_json_error( 'Vui lòng đồng ý với các điều khoản và chính sách riêng tư trước khi gửi.' );
            }
        }
    }

    // Kiểm tra đáp án của các trường quiz trên máy chủ.
    if ( preg_match_all( '/\[quiz\*?\s+([a-zA-Z0-9_-]+)\s+"([^"]+)\|([^"]+)"\]/', $template, $quizzes, PREG_SET_ORDER ) ) {
        foreach ( $quizzes as $quiz ) {
            $answer = sanitize_text_field( wp_unslash( $_POST[ $quiz[1] ] ?? '' ) );
            if ( 0 !== strcasecmp( trim( $answer ), trim( $quiz[3] ) ) ) {
                wp_send_json_error( 'Câu trả lời xác nhận chưa chính xác.' );
            }
        }
    }

    $ignored = array_merge(
        array_filter( [ 'action', 'nonce', 'form_id', 'form_lang', 'client_time', $name_key, $phone_key, $email_key, '_az9s_hp' ] ),
        $acceptance_names
    );
    $details = [];
    foreach ( $_POST as $key => $value ) {
        if ( in_array( $key, $ignored, true ) ) continue;
        if ( is_array( $value ) ) {
            $value = implode( ', ', array_map( 'sanitize_text_field', wp_unslash( $value ) ) );
        }
        $details[] = sanitize_text_field( $key ) . ': ' . sanitize_textarea_field( wp_unslash( $value ) );
    }

    // Lưu file đính kèm vào Media Library và ghi URL vào Lead.
    if ( ! empty( $_FILES ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        foreach ( $_FILES as $field_name => $file ) {
            if ( empty( $file['name'] ) || UPLOAD_ERR_OK !== (int) $file['error'] ) continue;
            if ( (int) $file['size'] > 5 * MB_IN_BYTES ) {
                wp_send_json_error( 'File tải lên không được lớn hơn 5MB.' );
            }
            $attachment_id = media_handle_upload( $field_name, 0 );
            if ( is_wp_error( $attachment_id ) ) {
                wp_send_json_error( 'Không thể tải file lên: ' . $attachment_id->get_error_message() );
            }
            $details[] = sanitize_text_field( $field_name ) . ': ' . wp_get_attachment_url( $attachment_id );
        }
    }
    global $wpdb;
    $client_time = sanitize_text_field( wp_unslash( $_POST['client_time'] ?? '' ) );
    if ( ! preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $client_time ) ) {
        $client_time = current_time( 'mysql' );
    }
    if ( function_exists( 'az9s_upgrade_leads_table' ) ) {
        az9s_upgrade_leads_table();
    }
    $inserted = $wpdb->insert( $wpdb->prefix . 'az9s_leads', [
        'name' => $name, 'phone' => $phone, 'email' => $email,
        'message' => implode( "\n", $details ),
        'form_id' => $form_id,
        'form_title' => sanitize_text_field( $forms[ $form_id ]['title'] ?? '' ),
        'status' => 'new',
        'ip' => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
        'created_at' => $client_time,
    ], [ '%s','%s','%s','%s','%s','%s','%s','%s','%s' ] );
    if ( ! $inserted ) {
        // Tương thích an toàn nếu hosting không cho phép ALTER TABLE.
        $inserted = $wpdb->insert( $wpdb->prefix . 'az9s_leads', [
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'message' => 'Tên form: ' . sanitize_text_field( $forms[ $form_id ]['title'] ?? '' ) . "\n" . implode( "\n", $details ),
            'status' => 'new',
            'ip' => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
            'created_at' => $client_time,
        ], [ '%s','%s','%s','%s','%s','%s','%s' ] );
    }
    if ( ! $inserted ) {
        wp_send_json_error( 'Không thể lưu thông tin vào database. Vui lòng kiểm tra quyền ghi của bảng Leads.' );
    }

    if ( function_exists( 'az9s_send_lead_notification_email' ) ) {
        $mail_sent = az9s_send_lead_notification_email( [
            'name'       => $name,
            'phone'      => $phone,
            'email'      => $email,
            'message'    => implode( "\n", $details ),
            'form_title' => sanitize_text_field( $forms[ $form_id ]['title'] ?? '' ),
            'created_at' => $client_time,
        ] );
        $mail_status = get_option( 'az9s_last_lead_mail_status', [] );
        $mail_note = $mail_sent ? 'Email thông báo: đã gửi' : 'Email thông báo: gửi thất bại';
        if ( is_array( $mail_status ) && ! empty( $mail_status['to'] ) ) {
            $mail_note .= ' tới ' . sanitize_email( $mail_status['to'] );
        }
        if ( is_array( $mail_status ) && ! empty( $mail_status['error'] ) ) {
            $mail_note .= ' - lỗi: ' . sanitize_text_field( $mail_status['error'] );
        }
        $wpdb->update(
            $wpdb->prefix . 'az9s_leads',
            [ 'message' => implode( "\n", array_merge( $details, [ $mail_note ] ) ) ],
            [ 'id' => $wpdb->insert_id ],
            [ '%s' ],
            [ '%d' ]
        );
    }

    wp_send_json_success( $variant['success'] ?: ( $forms[ $form_id ]['success'] ?? 'Cảm ơn bạn! Chúng tôi sẽ liên hệ trong thời gian sớm nhất.' ) );
}

function az9s_get_form_field_names_by_type( $template ) {
    $fields = [];
    if ( preg_match_all( '/\[(text|tel|email|url|number|date|textarea|select|select_posts|select_terms|checkbox|radio|quiz|file)\*?\s+([a-zA-Z0-9_-]+)/', $template, $matches, PREG_SET_ORDER ) ) {
        foreach ( $matches as $match ) {
            $fields[ $match[1] ][] = $match[2];
        }
    }
    return $fields;
}

function az9s_get_required_form_fields( $template ) {
    $required = [];
    $types = 'text|tel|email|url|number|range|date|textarea|select|select_posts|select_terms|checkbox|radio|quiz|file';
    if ( preg_match_all( '/\[\s*(' . $types . ')\s*\*\s+([a-zA-Z0-9_-]+)([^\]]*)\]/i', $template, $matches, PREG_SET_ORDER ) ) {
        foreach ( $matches as $match ) {
            $placeholder = '';
            if ( in_array( strtolower( $match[1] ), [ 'select', 'select_posts', 'select_terms' ], true ) && preg_match( '/"([^"]+)"/', $match[3], $quoted ) ) {
                $placeholder = $quoted[1];
            }
            $required[] = [
                'type' => strtolower( $match[1] ),
                'name' => $match[2],
                'placeholder' => $placeholder,
            ];
        }
    }
    return $required;
}
