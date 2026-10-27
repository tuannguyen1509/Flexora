<?php
/**
 * Comments Template
 * @package AZ_AI_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( post_password_required() ) {
    echo '<p class="cmt-protected">Bài viết này được bảo vệ bằng mật khẩu. Vui lòng nhập mật khẩu để xem bình luận.</p>';
    return;
}
$commenter = wp_get_current_commenter();
?>
<style>
/* ═══════════════════════════════════════════════════
   COMMENTS — Chi tiết bài viết blog
═══════════════════════════════════════════════════ */
.cmt-wrap { font-family:'Inter',sans-serif; }

/* Tiêu đề */
.cmt-title {
    font-family:'Manrope',sans-serif;
    font-size:1.25rem;font-weight:600;color:#111111;
    margin:0 0 28px;
    display:flex;align-items:center;gap:10px;
}
.cmt-title::after {
    content:'';flex:1;height:2px;
    background:linear-gradient(90deg,rgba(0,152,218,.3),transparent);
    border-radius:2px;
}
.cmt-count {
    display:inline-flex;align-items:center;justify-content:center;
    min-width:26px;height:26px;padding:0 8px;
    background:var(--primary-color);color:#fff;
    font-size:12px;font-weight:600;border-radius:100px;
}

/* Danh sách */
.cmt-list { list-style:none;margin:0 0 40px;padding:0;display:flex;flex-direction:column;gap:20px; }

/* Item */
.cmt-item {
    background:#f8fbff;border:1px solid rgba(0,152,218,.12);
    border-radius:14px;padding:24px;position:relative;
}
.cmt-item.bypostauthor { border-color:rgba(0,152,218,.35);background:#e5f7ff; }
.cmt-item.bypostauthor::before {
    content:'Tác giả';position:absolute;top:16px;right:16px;
    background:var(--primary-color);color:#fff;font-size:10px;font-weight:600;
    letter-spacing:1px;text-transform:uppercase;
    padding:3px 10px;border-radius:100px;
}

/* Header */
.cmt-header { display:flex;align-items:flex-start;gap:14px;margin-bottom:14px; }
.cmt-avatar { width:44px;height:44px;border-radius:50%;flex-shrink:0;overflow:hidden;border:2px solid rgba(0,152,218,.20); }
.cmt-avatar img { width:100%;height:100%;object-fit:cover;display:block; }
.cmt-author-name { font-family:'Manrope',sans-serif;font-size:15px;font-weight:600;color:#111111;margin:0 0 3px; }
.cmt-date { font-size:12px;color:#888888;display:flex;align-items:center;gap:5px; }
.cmt-date a { color:inherit;text-decoration:none; }

/* Body */
.cmt-body { font-size:15px;line-height:1.8;color:#333333;margin:0 0 14px; }
.cmt-body p { margin:0 0 10px; }
.cmt-body p:last-child { margin:0; }

/* Reply */
.comment-reply-link {
    display:inline-flex;align-items:center;gap:5px;
    font-size:12px;font-weight:600;color:var(--primary-color);text-decoration:none;
    padding:5px 12px;border-radius:100px;
    background:rgba(0,152,218,.08);border:1px solid rgba(0,152,218,.20);
    transition:all .15s;
}
.comment-reply-link:hover { background:var(--primary-color);color:#fff;border-color:var(--primary-color); }

/* Chờ duyệt */
.cmt-moderation { font-size:12px;color:var(--primary-color);font-weight:600;margin-bottom:10px; }

/* Nested */
.children {
    list-style:none;margin:16px 0 0 32px;padding:0 0 0 20px;
    display:flex;flex-direction:column;gap:14px;
    border-left:2px solid rgba(0,152,218,.20);
}

/* ── Form ── */
#respond {
    background:#fff;border:1px solid rgba(17,17,17,.10);
    border-radius:16px;padding:32px;
    box-shadow:0 2px 16px rgba(17,17,17,.06);
}
#reply-title {
    font-family:'Manrope',sans-serif;font-size:1.2rem;font-weight:600;color:#111111;
    margin:0 0 24px;display:flex;align-items:center;gap:10px;
}
#reply-title::before {
    content:'';width:4px;height:22px;
    background:linear-gradient(180deg, var(--primary-color) 0%, var(--primary-color) 100%);
    border-radius:2px;flex-shrink:0;
}
#reply-title small a { font-size:13px;font-weight:600;color:#888;text-decoration:none;margin-left:8px; }
#reply-title small a:hover { color:var(--primary-color); }

.comment-notes { font-size:13px;color:#666666;margin:0 0 20px;line-height:1.6; }
.required { color:#ef4444; }

#commentform { display:flex;flex-direction:column;gap:16px; }

.comment-form-author,
.comment-form-email,
.comment-form-url,
.comment-form-comment { display:flex;flex-direction:column;gap:6px;margin:0; }

.cmt-row-2 { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
@media(max-width:600px){ .cmt-row-2{grid-template-columns:1fr} #respond{padding:20px} }

#commentform label { font-size:12px;font-weight:600;color:#333333;letter-spacing:.3px;text-transform:uppercase; }

#commentform input[type="text"],
#commentform input[type="email"],
#commentform input[type="url"],
#commentform textarea {
    width:100%;padding:11px 14px;
    border:1.5px solid rgba(17,17,17,.15);border-radius:10px;
    font-size:14px;color:#111111;background:#fafafa;
    outline:none;transition:border-color .15s,box-shadow .15s,background .15s;
    font-family:'Inter',sans-serif;box-sizing:border-box;
}
#commentform input:focus,
#commentform textarea:focus {
    border-color:var(--primary-color);
    box-shadow:0 0 0 3px rgba(0,152,218,.12);
    background:#fff;
}
#commentform textarea { resize:vertical;min-height:130px;line-height:1.7; }

.form-submit { margin:0; }
#commentform .form-submit input[type="submit"],
#commentform .form-submit button[type="submit"] {
    display:inline-flex;align-items:center;justify-content:center;gap:8px;
    box-sizing:border-box;min-height:44px;max-width:100%;padding:12px 28px;
    background:#154c85;background-image:none;color:#fff;
    font-family:var(--az-font-main,Arial,sans-serif);
    font-size:15px;font-weight:600;line-height:1.4;
    border:1px solid #154c85;border-radius:10px;cursor:pointer;
    text-align:center;white-space:normal;opacity:1;
    box-shadow:0 4px 12px rgba(21,76,133,.18);
    transition:background-color .15s,border-color .15s,box-shadow .15s;
}
#commentform .form-submit input[type="submit"]:hover,
#commentform .form-submit button[type="submit"]:hover {
    background:#103d6a;color:#fff;border-color:#103d6a;
}
#commentform .form-submit input[type="submit"]:focus-visible,
#commentform .form-submit button[type="submit"]:focus-visible {
    outline:2px solid #154c85;outline-offset:3px;
    box-shadow:0 0 0 3px #fff;
}
#commentform .form-submit input[type="submit"]:disabled,
#commentform .form-submit button[type="submit"]:disabled {
    background:#64748b;color:#fff;border-color:#64748b;
    cursor:not-allowed;box-shadow:none;
}

/* Ẩn URL và antibot */
.comment-form-url,
.comment-form-aios-antibot-keys { display:none !important; }

/* Phân trang */
.cmt-pagination { display:flex;justify-content:center;gap:8px;margin:24px 0;flex-wrap:wrap; }
.cmt-pagination a,.cmt-pagination span {
    display:inline-flex;align-items:center;justify-content:center;
    min-width:40px;height:40px;padding:0 14px;border-radius:10px;
    font-size:14px;font-weight:600;text-decoration:none;
    border:1.5px solid rgba(17,17,17,.15);color:#333333;background:#fff;transition:all .15s;
}
.cmt-pagination a:hover,.cmt-pagination .current { background:var(--primary-color);border-color:var(--primary-color);color:#fff; }

.cmt-protected { background:#fff3e3;border:1px solid var(--primary-color);border-radius:10px;padding:16px 20px;font-size:14px;color:#92400e; }
</style>

<div class="cmt-wrap" id="comments">

    <?php if ( have_comments() ) : ?>

        <h2 class="cmt-title">
            Bình luận
            <span class="cmt-count"><?php echo get_comments_number(); ?></span>
        </h2>

        <?php if ( get_comment_pages_count() > 1 && get_option('page_comments') ) : ?>
            <div class="cmt-pagination"><?php paginate_comments_links(['prev_text'=>'&laquo;','next_text'=>'&raquo;']); ?></div>
        <?php endif; ?>

        <ol class="cmt-list">
            <?php wp_list_comments(['style'=>'ol','short_ping'=>true,'avatar_size'=>44,'callback'=>'az_comment_cb']); ?>
        </ol>

        <?php if ( get_comment_pages_count() > 1 && get_option('page_comments') ) : ?>
            <div class="cmt-pagination"><?php paginate_comments_links(['prev_text'=>'&laquo;','next_text'=>'&raquo;']); ?></div>
        <?php endif; ?>

    <?php endif; ?>

    <?php if ( ! comments_open() && get_comments_number() && post_type_supports(get_post_type(),'comments') ) : ?>
        <p style="text-align:center;color:#888;font-size:14px;padding:20px 0;">Bình luận đã bị đóng.</p>
    <?php endif; ?>

    <?php
    comment_form([
        'title_reply'          => 'Để lại bình luận',
        'title_reply_to'       => 'Trả lời %s',
        'cancel_reply_link'    => 'Hủy',
        'label_submit'         => 'Gửi bình luận',
        'comment_notes_before' => '<p class="comment-notes">Email của bạn sẽ không được hiển thị công khai. <span class="required-field-message">Các trường bắt buộc được đánh dấu <span class="required">*</span></span></p>',
        'fields' => [
            'author' => '<div class="cmt-row-2"><p class="comment-form-author"><label for="author">Tên <span class="required">*</span></label><input id="author" name="author" type="text" placeholder="Họ và tên" value="' . esc_attr($commenter['comment_author'] ?? '') . '" size="30" maxlength="245" autocomplete="name" required /></p>',
            'email'  => '<p class="comment-form-email"><label for="email">Email <span class="required">*</span></label><input id="email" name="email" type="email" placeholder="email@example.com" value="' . esc_attr($commenter['comment_author_email'] ?? '') . '" size="30" maxlength="100" autocomplete="email" required /></p></div>',
            'url'    => '',
        ],
        'comment_field' => '<p class="comment-form-comment"><label for="comment">Bình luận <span class="required">*</span></label><textarea id="comment" name="comment" placeholder="Chia sẻ suy nghĩ của bạn..." cols="45" rows="6" maxlength="65525" required></textarea></p>',
    ]);
    ?>

</div><!-- #comments -->

<?php
if ( ! function_exists('az_comment_cb') ) :
function az_comment_cb( $comment, $args, $depth ) {
    $is_author = get_the_author_meta('ID') == $comment->user_id;
    ?>
    <li id="comment-<?php comment_ID(); ?>" <?php comment_class(['cmt-item', $is_author ? 'bypostauthor' : ''], $comment); ?>>
        <div class="cmt-header">
            <div class="cmt-avatar"><?php echo get_avatar($comment, 44); ?></div>
            <div>
                <div class="cmt-author-name"><?php comment_author($comment); ?></div>
                <div class="cmt-date">
                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <a href="<?php echo esc_url(get_comment_link($comment)); ?>"><?php echo get_comment_date('d/m/Y',$comment); ?> lúc <?php echo get_comment_time('H:i',false,false,$comment); ?></a>
                </div>
            </div>
        </div>
        <?php if ( '0' == $comment->comment_approved ) : ?>
            <p class="cmt-moderation">⏳ Bình luận đang chờ kiểm duyệt.</p>
        <?php endif; ?>
        <div class="cmt-body"><?php comment_text($comment); ?></div>
        <?php comment_reply_link(array_merge($args,['add_below'=>'comment','depth'=>$depth,'max_depth'=>$args['max_depth'],'before'=>'','after'=>'']), $comment); ?>
    <?php
    // Không đóng </li> — wp_list_comments tự đóng
}
endif;
