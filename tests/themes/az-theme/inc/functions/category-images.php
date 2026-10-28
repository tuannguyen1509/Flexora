<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ═══════════════════════════════════════════════════════════════
   CATEGORY IMAGE — Thêm field ảnh cho danh mục
   Hiển thị trong popup icon Danh mục ở header
═══════════════════════════════════════════════════════════════ */

/**
 * Thêm field ảnh vào form tạo category mới
 */
add_action( 'category_add_form_fields', 'cp_cat_image_add_field' );
function cp_cat_image_add_field() {
    ?>
    <div class="form-field term-image-wrap">
        <label for="cp_cat_image">Ảnh danh mục</label>
        <div id="cp_cat_image_preview" style="margin-bottom:8px;">
            <img id="cp_cat_image_thumb" src="" style="max-width:200px;display:none;border-radius:6px;">
        </div>
        <input type="hidden" id="cp_cat_image" name="cp_cat_image" value="">
        <button type="button" class="button" id="cp_cat_image_btn">Chọn ảnh</button>
        <button type="button" class="button" id="cp_cat_image_remove" style="display:none;">Xóa ảnh</button>
        <p class="description">Ảnh hiển thị trong popup danh mục ở header. Khuyến nghị: ảnh vuông 1:1, ví dụ 400x400px.</p>
    </div>
    <?php
    cp_cat_image_admin_script();
}

/**
 * Thêm field ảnh vào form chỉnh sửa category
 */
add_action( 'category_edit_form_fields', 'cp_cat_image_edit_field' );
function cp_cat_image_edit_field( $term ) {
    $image_id  = get_term_meta( $term->term_id, 'cp_cat_image_id', true );
    $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
    ?>
    <tr class="form-field term-image-wrap">
        <th scope="row"><label for="cp_cat_image">Ảnh danh mục</label></th>
        <td>
            <div id="cp_cat_image_preview" style="margin-bottom:8px;">
                <img id="cp_cat_image_thumb" src="<?php echo esc_url( $image_url ); ?>"
                     style="max-width:200px;border-radius:6px;<?php echo $image_url ? '' : 'display:none;'; ?>">
            </div>
            <input type="hidden" id="cp_cat_image" name="cp_cat_image"
                   value="<?php echo esc_attr( $image_id ); ?>">
            <button type="button" class="button" id="cp_cat_image_btn">Chọn ảnh</button>
            <button type="button" class="button" id="cp_cat_image_remove"
                    style="<?php echo $image_id ? '' : 'display:none;'; ?>">Xóa ảnh</button>
            <p class="description">Ảnh hiển thị trong popup danh mục ở header. Khuyến nghị: ảnh vuông 1:1, ví dụ 400x400px.</p>
        </td>
    </tr>
    <?php
    cp_cat_image_admin_script();
}

/**
 * Script media uploader dùng chung
 */
function cp_cat_image_admin_script() {
    ?>
    <script>
    jQuery(function($){
        var frame;
        $('#cp_cat_image_btn').on('click', function(e){
            e.preventDefault();
            if(frame){ frame.open(); return; }
            frame = wp.media({ title: 'Chọn ảnh danh mục', button: { text: 'Dùng ảnh này' }, multiple: false });
            frame.on('select', function(){
                var att = frame.state().get('selection').first().toJSON();
                $('#cp_cat_image').val(att.id);
                $('#cp_cat_image_thumb').attr('src', att.url).show();
                $('#cp_cat_image_remove').show();
            });
            frame.open();
        });
        $('#cp_cat_image_remove').on('click', function(e){
            e.preventDefault();
            $('#cp_cat_image').val('');
            $('#cp_cat_image_thumb').hide().attr('src','');
            $(this).hide();
        });
    });
    </script>
    <?php
}

/**
 * Enqueue media uploader trên trang category
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( in_array( $hook, [ 'edit-tags.php', 'term.php' ] ) &&
         isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === 'category' ) {
        wp_enqueue_media();
    }
} );

/**
 * Lưu ảnh khi tạo category mới
 */
add_action( 'created_category', 'cp_cat_image_save' );
add_action( 'edited_category',  'cp_cat_image_save' );
function cp_cat_image_save( $term_id ) {
    if ( isset( $_POST['cp_cat_image'] ) ) {
        $image_id = absint( $_POST['cp_cat_image'] );
        if ( $image_id ) {
            update_term_meta( $term_id, 'cp_cat_image_id', $image_id );
        } else {
            delete_term_meta( $term_id, 'cp_cat_image_id' );
        }
    }
}

/**
 * Helper: lấy URL ảnh của category
 */
function cp_get_cat_image_url( $term_id, $size = 'medium' ) {
    $image_id = get_term_meta( $term_id, 'cp_cat_image_id', true );
    if ( $image_id ) {
        $url = wp_get_attachment_image_url( $image_id, $size );
        if ( $url ) return $url;
    }
    // Fallback: lấy ảnh từ bài viết mới nhất trong category
    $posts = get_posts([
        'numberposts'    => 1,
        'category'       => $term_id,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'suppress_filters' => false,
    ]);
    if ( $posts ) {
        $thumb = get_the_post_thumbnail_url( $posts[0]->ID, $size );
        if ( $thumb ) return $thumb;
    }
    return '';
}



