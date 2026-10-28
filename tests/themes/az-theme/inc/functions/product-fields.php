<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'az_product_extra_info_meta_box' ) ) {
    function az_product_extra_info_meta_box() {
        add_meta_box(
            'az_product_extra_info',
            'Thông tin chi tiết Công ty chúng tôi',
            'az_product_extra_info_meta_box_html',
            'product',
            'normal',
            'default'
        );
    }
}
add_action( 'add_meta_boxes_product', 'az_product_extra_info_meta_box' );

if ( ! function_exists( 'az_product_extra_info_meta_box_html' ) ) {
    function az_product_extra_info_meta_box_html( $post ) {
        wp_nonce_field( 'az_product_extra_info_save', 'az_product_extra_info_nonce' );

        $text_fields = array(
            'az_manufacturer' => 'Nhà sản xuất',
            'az_origin'       => 'Xuất xứ',
            'az_warranty'     => 'Bảo hành',
            'az_shopee_url'   => 'Link Shopee',
            'az_sendo_url'    => 'Link Sendo',
            'az_lazada_url'   => 'Link Lazada',
        );
        ?>
        <div class="az-product-extra-admin">
            <?php foreach ( $text_fields as $key => $label ) : ?>
                <p class="form-field">
                    <label for="<?php echo esc_attr( $key ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label>
                    <input type="text" class="widefat" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( get_post_meta( $post->ID, $key, true ) ); ?>">
                </p>
            <?php endforeach; ?>

            <p><strong>Thông số kỹ thuật</strong></p>
            <?php
            wp_editor(
                get_post_meta( $post->ID, 'az_technical_specs', true ),
                'az_technical_specs',
                array(
                    'textarea_name' => 'az_technical_specs',
                    'textarea_rows' => 12,
                    'media_buttons' => true,
                    'teeny'         => false,
                )
            );
            ?>

            <p style="margin-top:22px;"><strong>Ứng dụng thực tiễn</strong></p>
            <?php
            wp_editor(
                get_post_meta( $post->ID, 'az_practical_applications', true ),
                'az_practical_applications',
                array(
                    'textarea_name' => 'az_practical_applications',
                    'textarea_rows' => 12,
                    'media_buttons' => true,
                    'teeny'         => false,
                )
            );
            ?>
        </div>
        <?php
    }
}

if ( ! function_exists( 'az_save_product_extra_info_meta' ) ) {
    function az_save_product_extra_info_meta( $post_id ) {
        if ( ! isset( $_POST['az_product_extra_info_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['az_product_extra_info_nonce'] ) ), 'az_product_extra_info_save' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $text_fields = array( 'az_manufacturer', 'az_origin', 'az_warranty' );
        foreach ( $text_fields as $key ) {
            $value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
            '' !== $value ? update_post_meta( $post_id, $key, $value ) : delete_post_meta( $post_id, $key );
        }

        $url_fields = array( 'az_shopee_url', 'az_sendo_url', 'az_lazada_url' );
        foreach ( $url_fields as $key ) {
            $value = isset( $_POST[ $key ] ) ? esc_url_raw( wp_unslash( $_POST[ $key ] ) ) : '';
            '' !== $value ? update_post_meta( $post_id, $key, $value ) : delete_post_meta( $post_id, $key );
        }

        $html_fields = array( 'az_technical_specs', 'az_practical_applications' );
        foreach ( $html_fields as $key ) {
            $value = isset( $_POST[ $key ] ) ? wp_kses_post( wp_unslash( $_POST[ $key ] ) ) : '';
            if ( function_exists( 'az_strip_tinymce_selection_markers' ) ) {
                $value = az_strip_tinymce_selection_markers( $value );
            }
            '' !== trim( $value ) ? update_post_meta( $post_id, $key, $value ) : delete_post_meta( $post_id, $key );
        }
    }
}
add_action( 'save_post_product', 'az_save_product_extra_info_meta' );
