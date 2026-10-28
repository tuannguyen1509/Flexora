<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ═══════════════════════════════════════════════════════════════
   MENU ITEM CUSTOM FIELDS
   Thêm field: Icon (chọn từ thư viện), Width, Height vào nav-menus.php
═══════════════════════════════════════════════════════════════ */

add_filter( 'wp_nav_menu_item_custom_fields', 'cp_menu_item_custom_fields', 10, 4 );
function cp_menu_item_custom_fields( $item_id, $item, $depth, $args ) {
    $icon_url = get_post_meta( $item_id, '_menu_item_icon_url', true );
    $icon_id  = get_post_meta( $item_id, '_menu_item_icon_id',  true );
    $icon_w   = get_post_meta( $item_id, '_menu_item_icon_width',  true ) ?: '20';
    $icon_h   = get_post_meta( $item_id, '_menu_item_icon_height', true ) ?: '20';
    ?>
    <div class="cp-menu-fields" id="cp-menu-field-<?php echo esc_attr($item_id); ?>">
        <p class="description description-wide">
            <label style="font-weight:600;font-size:12px;color:var(--primary-color);display:block;margin-bottom:6px;">🖼 Icon menu</label>
            <span class="cp-icon-preview" id="cp-preview-<?php echo esc_attr($item_id); ?>"
                  style="display:<?php echo $icon_url ? 'inline-flex' : 'none'; ?>;align-items:center;gap:8px;margin-bottom:8px;padding:6px 8px;background:#fff;border:1px solid #d8e6f7;border-radius:6px;">
                <img id="cp-preview-img-<?php echo esc_attr($item_id); ?>"
                     src="<?php echo esc_url($icon_url); ?>"
                     style="width:<?php echo esc_attr($icon_w); ?>px;height:<?php echo esc_attr($icon_h); ?>px;object-fit:contain;">
                <span style="font-size:11px;color:#888;" id="cp-preview-name-<?php echo esc_attr($item_id); ?>"><?php echo $icon_url ? basename($icon_url) : ''; ?></span>
                <button type="button" class="cp-icon-remove" data-id="<?php echo esc_attr($item_id); ?>"
                        style="color:#a00;font-size:11px;border:none;background:none;cursor:pointer;padding:0 4px;" title="Xóa">✕</button>
            </span>
            <input type="hidden" id="cp-icon-url-<?php echo esc_attr($item_id); ?>" name="menu-item-icon-url[<?php echo esc_attr($item_id); ?>]" value="<?php echo esc_attr($icon_url); ?>">
            <input type="hidden" id="cp-icon-id-<?php echo esc_attr($item_id); ?>"  name="menu-item-icon-id[<?php echo esc_attr($item_id); ?>]"  value="<?php echo esc_attr($icon_id); ?>">
            <button type="button" class="button cp-icon-select" data-id="<?php echo esc_attr($item_id); ?>"
                    style="background:var(--primary-color);color:#fff;border-color:var(--primary-color);border-radius:5px;padding:4px 12px;font-size:12px;cursor:pointer;">
                📁 Chọn từ thư viện
            </button>
        </p>
        <p class="description description-wide" style="margin-top:8px;">
            <label style="font-weight:600;font-size:12px;color:var(--primary-color);display:block;margin-bottom:4px;">📐 Kích thước icon (px)</label>
            <span style="display:flex;gap:10px;align-items:center;">
                <label style="font-size:12px;color:#555;display:flex;align-items:center;gap:4px;">W:
                    <input type="number" id="cp-icon-w-<?php echo esc_attr($item_id); ?>" name="menu-item-icon-width[<?php echo esc_attr($item_id); ?>]"
                           value="<?php echo esc_attr($icon_w); ?>" min="8" max="128" step="1"
                           style="width:58px;border-radius:4px;border:1px solid #ddd;padding:3px 6px;font-size:12px;"
                           onchange="cpUpdatePreviewSize(<?php echo esc_attr($item_id); ?>)"> px
                </label>
                <label style="font-size:12px;color:#555;display:flex;align-items:center;gap:4px;">H:
                    <input type="number" id="cp-icon-h-<?php echo esc_attr($item_id); ?>" name="menu-item-icon-height[<?php echo esc_attr($item_id); ?>]"
                           value="<?php echo esc_attr($icon_h); ?>" min="8" max="128" step="1"
                           style="width:58px;border-radius:4px;border:1px solid #ddd;padding:3px 6px;font-size:12px;"
                           onchange="cpUpdatePreviewSize(<?php echo esc_attr($item_id); ?>)"> px
                </label>
                <small style="color:#aaa;font-size:11px;">Mặc định: 20×20</small>
            </span>
        </p>
    </div>
    <?php
}

add_action( 'wp_update_nav_menu_item', 'cp_save_menu_item_custom_fields', 10, 3 );
function cp_save_menu_item_custom_fields( $menu_id, $menu_item_db_id, $args ) {
    if ( isset( $_POST['menu-item-icon-url'][$menu_item_db_id] ) ) {
        $val = esc_url_raw( $_POST['menu-item-icon-url'][$menu_item_db_id] );
        if ( $val ) update_post_meta( $menu_item_db_id, '_menu_item_icon_url', $val );
        else        delete_post_meta( $menu_item_db_id, '_menu_item_icon_url' );
    }
    if ( isset( $_POST['menu-item-icon-id'][$menu_item_db_id] ) ) {
        $id = absint( $_POST['menu-item-icon-id'][$menu_item_db_id] );
        if ( $id ) update_post_meta( $menu_item_db_id, '_menu_item_icon_id', $id );
        else       delete_post_meta( $menu_item_db_id, '_menu_item_icon_id' );
    }
    if ( isset( $_POST['menu-item-icon-width'][$menu_item_db_id] ) )
        update_post_meta( $menu_item_db_id, '_menu_item_icon_width',  absint( $_POST['menu-item-icon-width'][$menu_item_db_id] )  ?: 20 );
    if ( isset( $_POST['menu-item-icon-height'][$menu_item_db_id] ) )
        update_post_meta( $menu_item_db_id, '_menu_item_icon_height', absint( $_POST['menu-item-icon-height'][$menu_item_db_id] ) ?: 20 );
}

add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( $hook !== 'nav-menus.php' ) return;
    wp_enqueue_media();
    // Inline JS thay vì file riêng
    add_action( 'admin_footer', 'cp_menu_icon_picker_js' );
} );

function cp_menu_icon_picker_js() { ?>
<script>
(function($){
    // Mở media uploader khi click "Chọn từ thư viện"
    $(document).on('click', '.cp-icon-select', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        var frame = wp.media({
            title: 'Chọn icon menu',
            button: { text: 'Dùng ảnh này' },
            multiple: false,
            library: { type: ['image'] }
        });
        frame.on('select', function(){
            var att = frame.state().get('selection').first().toJSON();
            var w = parseInt($('#cp-icon-w-' + id).val()) || 20;
            var h = parseInt($('#cp-icon-h-' + id).val()) || 20;
            $('#cp-icon-url-' + id).val(att.url);
            $('#cp-icon-id-'  + id).val(att.id);
            $('#cp-preview-img-'  + id).attr('src', att.url).css({width: w+'px', height: h+'px'});
            $('#cp-preview-name-' + id).text(att.filename || att.url.split('/').pop());
            $('#cp-preview-' + id).show();
        });
        frame.open();
    });
    // Xóa icon
    $(document).on('click', '.cp-icon-remove', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        $('#cp-icon-url-' + id).val('');
        $('#cp-icon-id-'  + id).val('');
        $('#cp-preview-'  + id).hide();
    });
    // Cập nhật preview khi đổi W/H
    window.cpUpdatePreviewSize = function(id){
        var w = parseInt($('#cp-icon-w-' + id).val()) || 20;
        var h = parseInt($('#cp-icon-h-' + id).val()) || 20;
        $('#cp-preview-img-' + id).css({width: w+'px', height: h+'px'});
    };
})(jQuery);
</script>
<?php }

add_filter( 'nav_menu_item_title', 'cp_menu_item_add_icon', 10, 4 );
function cp_menu_item_add_icon( $title, $item, $args, $depth ) {
    $icon_url = get_post_meta( $item->ID, '_menu_item_icon_url', true );
    if ( ! $icon_url ) return $title;
    $w = absint( get_post_meta( $item->ID, '_menu_item_icon_width',  true ) ) ?: 20;
    $h = absint( get_post_meta( $item->ID, '_menu_item_icon_height', true ) ) ?: 20;
    return '<img src="' . esc_url($icon_url) . '" width="' . $w . '" height="' . $h . '" alt="" class="cp-menu-icon" style="display:inline-block;vertical-align:middle;margin-right:5px;object-fit:contain;" loading="lazy">' . $title;
}

add_action( 'admin_head-nav-menus.php', function() { ?>
<style>
.cp-menu-fields { background:#fdf8f2; border:1px solid #d8e6f7; border-radius:6px; padding:10px 12px !important; margin:8px 0 4px !important; }
.cp-icon-select:hover { background:var(--primary-color) !important; }
</style>
<?php } );


