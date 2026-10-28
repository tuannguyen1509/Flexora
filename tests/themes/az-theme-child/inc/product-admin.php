<?php
/** Product information metabox presentation; saving remains in the parent theme. */
defined( 'ABSPATH' ) || exit;

function az_child_product_admin_assets( $hook ) {
    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
    $screen = get_current_screen();
    if ( ! $screen || 'product' !== $screen->post_type ) { return; }
    $path = get_stylesheet_directory() . '/css/product-admin.css';
    wp_enqueue_style( 'az-product-admin', get_stylesheet_directory_uri() . '/css/product-admin.css', array(), filemtime( $path ) );
}
add_action( 'admin_enqueue_scripts', 'az_child_product_admin_assets', 30 );

/** Child functions load first, allowing the parent's pluggable renderer to be replaced. */
if ( ! function_exists( 'az_product_extra_info_meta_box_html' ) ) {
    function az_product_extra_info_meta_box_html( $post ) {
        wp_nonce_field( 'az_product_extra_info_save', 'az_product_extra_info_nonce' );
        $groups = array(
            array(
                'title' => 'Thông tin sản phẩm',
                'icon' => 'info-outline',
                'fields' => array(
                    'az_manufacturer' => array( 'Nhà sản xuất', 'Ví dụ: Panasonic', 'text' ),
                    'az_origin' => array( 'Xuất xứ', 'Ví dụ: Việt Nam', 'text' ),
                    'az_warranty' => array( 'Bảo hành', 'Ví dụ: 12 tháng', 'text' ),
                ),
            ),
            array(
                'title' => 'Liên kết mua hàng',
                'icon' => 'admin-links',
                'fields' => array(
                    'az_shopee_url' => array( 'Link Shopee', 'https://shopee.vn/…', 'url' ),
                    'az_sendo_url' => array( 'Link Sendo', 'https://www.sendo.vn/…', 'url' ),
                    'az_lazada_url' => array( 'Link Lazada', 'https://www.lazada.vn/…', 'url' ),
                ),
            ),
        );
        ?>
        <div class="az-product-extra-admin">
            <?php foreach ( $groups as $index => $group ) : ?>
                <section class="az-product-extra-section" aria-labelledby="az-product-extra-group-<?php echo (int) $index; ?>">
                    <h3 id="az-product-extra-group-<?php echo (int) $index; ?>" class="az-product-extra-heading"><span class="dashicons dashicons-<?php echo esc_attr( $group['icon'] ); ?>" aria-hidden="true"></span><?php echo esc_html( $group['title'] ); ?></h3>
                    <div class="az-product-extra-grid">
                        <?php foreach ( $group['fields'] as $key => $field ) : ?>
                            <div class="az-product-extra-field">
                                <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field[0] ); ?></label>
                                <input type="<?php echo esc_attr( $field[2] ); ?>" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( get_post_meta( $post->ID, $key, true ) ); ?>" placeholder="<?php echo esc_attr( $field[1] ); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
            <?php foreach ( array( 'az_technical_specs' => 'Thông số kỹ thuật', 'az_practical_applications' => 'Ứng dụng thực tiễn' ) as $key => $title ) : ?>
                <section class="az-product-extra-section az-product-extra-section--editor" aria-labelledby="<?php echo esc_attr( $key ); ?>-heading">
                    <h3 id="<?php echo esc_attr( $key ); ?>-heading" class="az-product-extra-heading"><span class="dashicons dashicons-editor-alignleft" aria-hidden="true"></span><?php echo esc_html( $title ); ?></h3>
                    <?php wp_editor( get_post_meta( $post->ID, $key, true ), $key, array(
                        'textarea_name' => $key,
                        'textarea_rows' => 12,
                        'media_buttons' => true,
                        'teeny' => false,
                    ) ); ?>
                </section>
            <?php endforeach; ?>
        </div>
        <?php
    }
}
