<?php
/** Editable logo gallery above the homepage footer. */
defined( 'ABSPATH' ) || exit;

function az_child_home_partners_data( $post_id = 0 ) {
    $post_id = $post_id ?: ( get_queried_object_id() ?: absint( get_option( 'page_on_front' ) ) );
    $data = get_post_meta( $post_id, '_az_home_page_data', true );
    $saved = is_array( $data ) && is_array( $data['partners'] ?? null ) ? $data['partners'] : array();
    $defaults = function_exists( 'az_child_soviban_partner_defaults' ) ? az_child_soviban_partner_defaults() : array( 'enabled' => 1, 'title' => 'ĐỐI TÁC CỦA CHÚNG TÔI', 'items' => array() );
    $config = wp_parse_args( $saved, $defaults );
    $items = array();
    foreach ( is_array( $config['items'] ) ? $config['items'] : array() as $item ) {
        if ( ! is_array( $item ) ) { continue; }
        $id = is_scalar( $item['id'] ?? 0 ) ? absint( $item['id'] ?? 0 ) : 0;
        $image = is_string( $item['image'] ?? null ) ? az_home_page_clean_url( $item['image'] ) : '';
        if ( $id ) {
            if ( ! wp_attachment_is_image( $id ) ) { continue; }
            $image = wp_get_attachment_url( $id );
        }
        if ( $image ) { $items[] = array( 'id' => $id, 'image' => $image ); }
    }
    $config['items'] = $items;
    return $config;
}

function az_child_home_partners_render( $config = null ) {
    $config = $config ?? az_child_home_partners_data();
    if ( empty( $config['enabled'] ) ) { return; }
    require get_stylesheet_directory() . '/template-parts/home-partners.php';
}

function az_child_home_partner_admin_logo( $item = array() ) {
    $id = absint( $item['id'] ?? 0 );
    $preview = $id ? wp_get_attachment_image_url( $id, 'thumbnail' ) : ( $item['image'] ?? '' );
    ?>
    <div class="az-home-partners-logo" data-home-partner-logo>
      <img src="<?php echo esc_url( $preview ); ?>" alt="" data-home-partner-preview>
      <input type="hidden" name="az_home_partner_id[]" value="<?php echo esc_attr( $id ); ?>">
      <input type="hidden" name="az_home_partner_image[]" value="<?php echo esc_attr( $item['image'] ?? '' ); ?>">
      <button type="button" class="az-home-partners-remove" data-home-partner-remove aria-label="Xóa logo">×</button>
    </div>
    <?php
}

function az_child_home_partners_admin( $post_id ) {
    $config = az_child_home_partners_data( $post_id );
    ?>
    <details class="az-home-panel" open><summary>Đối tác của chúng tôi</summary><div class="az-home-body" data-home-partners-admin>
      <input type="hidden" name="az_home_partners_present" value="1">
      <div class="az-home-grid">
        <label class="az-home-field"><span>Tiêu đề</span><input name="az_home_partners_title" value="<?php echo esc_attr( $config['title'] ); ?>"></label>
        <div class="az-home-partners-visibility"><span>Hiển thị</span><label class="az-home-partners-toggle">
          <input type="checkbox" role="switch" name="az_home_partners_enabled" value="1" <?php checked( $config['enabled'], 1 ); ?>>
          <span class="az-home-partners-toggle__track" aria-hidden="true"></span><span>Hiện mục đối tác trước footer trang chủ</span>
        </label></div>
      </div>
      <div class="az-home-partners-toolbar"><div><strong>Gallery logo đối tác</strong><p>Chọn nhiều logo cùng lúc từ thư viện Media. Chỉ hiển thị hình ảnh trên trang chủ.</p></div><button type="button" class="button button-primary" data-home-partner-add>Thêm logo</button></div>
      <div class="az-home-partners-gallery" data-home-partner-list><?php foreach ( $config['items'] as $item ) { az_child_home_partner_admin_logo( $item ); } ?></div>
      <p class="az-home-partners-empty" data-home-partner-empty <?php if ( $config['items'] ) { echo 'hidden'; } ?>>Chưa có logo. Nhấn “Thêm logo” để chọn ảnh đối tác.</p>
      <div class="az-home-partners-gallery-footer"><span data-home-partner-count aria-live="polite"><?php echo count( $config['items'] ); ?> logo đã chọn</span><button type="button" class="button-link button-link-delete" data-home-partner-clear <?php if ( ! $config['items'] ) { echo 'hidden'; } ?>>Xóa tất cả logo</button></div>
      <template data-home-partner-template><?php az_child_home_partner_admin_logo(); ?></template>
    </div></details>
    <?php
}

function az_child_home_partners_save( $post_id, $post ) {
    if ( ! az_home_page_is_template( $post ) || ! isset( $_POST['az_home_partners_present'], $_POST['az_home_page_nonce'] )
        || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['az_home_page_nonce'] ) ), 'az_home_page_save' ) ) { return; }
    if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
    $ids = (array) wp_unslash( $_POST['az_home_partner_id'] ?? array() );
    $images = (array) wp_unslash( $_POST['az_home_partner_image'] ?? array() );
    $items = array(); $seen = array();
    foreach ( $images as $i => $image ) {
        if ( ! is_string( $image ) || ! is_scalar( $ids[ $i ] ?? 0 ) ) { continue; }
        $id = absint( $ids[ $i ] ?? 0 );
        if ( $id ) {
            if ( ! wp_attachment_is_image( $id ) ) { continue; }
            $image = wp_get_attachment_url( $id );
        } else {
            // Preserve logos saved by the previous URL-based editor.
            $image = az_home_page_clean_url( $image );
            $id = $image ? attachment_url_to_postid( $image ) : 0;
            if ( $id && ! wp_attachment_is_image( $id ) ) { continue; }
        }
        if ( ! $image || isset( $seen[ $image ] ) ) { continue; }
        $seen[ $image ] = true;
        $items[] = array( 'id' => $id, 'image' => $image );
    }
    $data = get_post_meta( $post_id, '_az_home_page_data', true );
    $data = is_array( $data ) ? $data : array();
    $data['partners'] = array(
        'enabled' => empty( $_POST['az_home_partners_enabled'] ) ? 0 : 1,
        'title' => sanitize_text_field( wp_unslash( $_POST['az_home_partners_title'] ?? '' ) ),
        'items' => $items,
    );
    update_post_meta( $post_id, '_az_home_page_data', $data );
}
add_action( 'save_post_page', 'az_child_home_partners_save', 30, 2 );

add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'page' !== get_current_screen()->post_type ) { return; }
    wp_enqueue_media();
    $dir = get_stylesheet_directory(); $uri = get_stylesheet_directory_uri();
    wp_enqueue_script( 'az-home-partners-admin', $uri . '/js/home-partners-admin.js', array( 'media-views' ), filemtime( $dir . '/js/home-partners-admin.js' ), true );
    wp_enqueue_style( 'az-home-partners-admin', $uri . '/css/home-partners-admin.css', array(), filemtime( $dir . '/css/home-partners-admin.css' ) );
} );
