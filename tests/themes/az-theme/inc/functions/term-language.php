<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ─────────────────────────────────────────────────────────────
 * CATEGORY / TAG BILINGUAL — add [EN] Name field in admin
 * ─────────────────────────────────────────────────────────────
 */

// Add field to Edit Category form
function az_category_en_field( $term ) {
    $name_en = get_term_meta( $term->term_id, '_category_name_en', true );
    ?>
    <tr class="form-field">
        <th scope="row">
            <label for="category_name_en">🌐 [EN] Tên tiếng Anh</label>
        </th>
        <td>
            <input type="text" id="category_name_en" name="category_name_en"
                   value="<?php echo esc_attr( $name_en ); ?>"
                   placeholder="e.g. News, Guides, Tips..." style="width:100%;" />
            <p class="description">Tên danh mục hiển thị khi chuyển sang tiếng Anh.</p>
        </td>
    </tr>
    <?php
}
add_action( 'category_edit_form_fields', 'az_category_en_field' );
add_action( 'post_tag_edit_form_fields', 'az_category_en_field' );

// Add field to Add New Category form
function az_category_en_add_field() {
    ?>
    <div class="form-field">
        <label for="category_name_en">🌐 [EN] Tên tiếng Anh</label>
        <input type="text" id="category_name_en" name="category_name_en"
               placeholder="e.g. News, Guides, Tips..." />
        <p>Tên danh mục hiển thị khi chuyển sang tiếng Anh.</p>
    </div>
    <?php
}
add_action( 'category_add_form_fields', 'az_category_en_add_field' );
add_action( 'post_tag_add_form_fields', 'az_category_en_add_field' );

// Save term meta
function az_save_category_en_field( $term_id ) {
    if ( isset( $_POST['category_name_en'] ) ) {
        $value = sanitize_text_field( $_POST['category_name_en'] );
        if ( ! empty( $value ) ) {
            update_term_meta( $term_id, '_category_name_en', $value );
        } else {
            delete_term_meta( $term_id, '_category_name_en' );
        }
    }
}
add_action( 'edited_category', 'az_save_category_en_field' );
add_action( 'create_category', 'az_save_category_en_field' );
add_action( 'edited_post_tag', 'az_save_category_en_field' );
add_action( 'create_post_tag', 'az_save_category_en_field' );

/* ── Flush rewrite on theme switch ── */
add_action( 'after_switch_theme', function() { flush_rewrite_rules(); } );
