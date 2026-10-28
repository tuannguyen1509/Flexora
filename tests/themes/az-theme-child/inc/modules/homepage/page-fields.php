<?php
/** Per-page content controls for the homepage template. */
defined( 'ABSPATH' ) || exit;

function az_home_term_id( $slug, $fallback = 0 ) {
    $term = taxonomy_exists( 'product_cat' ) ? get_term_by( 'slug', $slug, 'product_cat' ) : false;
    return $term && ! is_wp_error( $term ) ? (int) $term->term_id : absint( $fallback );
}

function az_home_page_default_data() {
    $blog = get_category_by_slug( 'tin-tuc' );
    $project = get_category_by_slug( 'du-an' );
    $defaults = array(
        'deal' => array(
            'enabled' => 1,
            'title' => 'SẢN PHẨM NỔI BẬT',
            'messages' => array(),
            'category_id' => 0,
            'end_at' => 0,
        ),
        'products' => array(
            'enabled' => 1,
            'sections' => array(
                array( 'title' => 'SẢN PHẨM BÁN CHẠY', 'category_ids' => array_filter( array( az_home_term_id( 'nang-luong-mat-troi' ), az_home_term_id( 'may-loc-nuoc' ), az_home_term_id( 'may-bom' ) ) ), 'image' => '' ),
                array( 'title' => 'SẢN PHẨM KHÁC', 'category_ids' => array_filter( array( az_home_term_id( 'hwata' ), az_home_term_id( 'thiet-bi-bep' ), az_home_term_id( 'phu-kien' ) ) ), 'image' => '' ),
            ),
        ),
        'blog' => array(
            'enabled' => 1,
            'title' => 'Tin mới nhất',
            'category_id' => $blog ? (int) $blog->term_id : 0,
            'limit' => 4,
            'require_category' => 0,
        ),
        'projects' => array(
            'enabled' => 1,
            'title' => 'DỰ ÁN ĐÃ THỰC HIỆN',
            'category_id' => $project ? (int) $project->term_id : 0,
            'require_category' => 1,
        ),
        'video' => array(
            'enabled' => 0,
            'title' => 'Video',
            'items' => array(),
        ),
        'steel' => array(
            'featured_image' => '',
            'promos' => array(
                array( 'title' => 'Dịch vụ và hậu mãi', 'image' => '', 'url' => '/san-pham/' ),
                array( 'title' => 'Giải pháp cho ngôi nhà của bạn', 'image' => '', 'url' => '/gioi-thieu/' ),
            ),
        ),
    );
    if ( function_exists( 'az_child_soviban_promos' ) ) {
        $defaults['steel']['promos'] = az_child_soviban_promos( 0 );
        foreach ( array( 'products', 'blog', 'projects' ) as $section ) { $defaults[ $section ]['enabled'] = 0; }
    }
    return $defaults;
}

function az_home_page_get_data( $post_id = 0 ) {
    $post_id = $post_id ? absint( $post_id ) : get_queried_object_id();
    if ( ! $post_id ) { $post_id = absint( get_option( 'page_on_front' ) ); }
    $saved = $post_id ? get_post_meta( $post_id, '_az_home_page_data', true ) : array();
    $saved = is_array( $saved ) ? $saved : array();
    $data = array_replace_recursive( az_home_page_default_data(), $saved );
    foreach ( array( array( 'products', 'sections' ), array( 'video', 'items' ), array( 'deal', 'messages' ), array( 'steel', 'promos' ) ) as $path ) {
        list( $section, $key ) = $path;
        if ( isset( $saved[ $section ] ) && is_array( $saved[ $section ] ) && array_key_exists( $key, $saved[ $section ] ) && is_array( $saved[ $section ][ $key ] ) ) {
            $data[ $section ][ $key ] = $saved[ $section ][ $key ];
        }
    }
    return $data;
}

function az_home_page_is_template( $post ) {
    if ( ! $post || 'page' !== $post->post_type ) { return false; }
    return (int) get_option( 'page_on_front' ) === (int) $post->ID
        || 'front-page.php' === get_page_template_slug( $post )
        || in_array( $post->post_name, array( 'trang-chu', 'home' ), true );
}

function az_home_page_clean_url( $value ) {
    $value = trim( (string) $value );
    return 0 === strpos( $value, '/' ) ? sanitize_text_field( $value ) : esc_url_raw( $value );
}

function az_home_page_asset_url( $value ) {
    $value = trim( (string) $value );
    // Keep root-relative media on the domain currently serving wp-admin/frontend.
    // This avoids broken previews when WordPress Home URL still points at an old domain.
    return $value;
}

function az_home_youtube_embed_url( $url ) {
    $url = trim( (string) $url );
    $parts = wp_parse_url( $url );
    if ( empty( $parts['host'] ) ) { return ''; }
    $host = strtolower( preg_replace( '/^www\./', '', $parts['host'] ) );
    $path = trim( $parts['path'] ?? '', '/' );
    $id = '';
    if ( 'youtu.be' === $host ) { $id = strtok( $path, '/' ); }
    elseif ( in_array( $host, array( 'youtube.com', 'm.youtube.com', 'youtube-nocookie.com' ), true ) ) {
        if ( ! empty( $parts['query'] ) ) { parse_str( $parts['query'], $query ); $id = $query['v'] ?? ''; }
        if ( ! $id && preg_match( '#^(embed|shorts|live)/([^/?]+)#', $path, $match ) ) { $id = $match[2]; }
    }
    $id = is_string( $id ) ? $id : '';
    return preg_match( '/^[A-Za-z0-9_-]{11}$/', $id ) ? 'https://www.youtube-nocookie.com/embed/' . $id : '';
}

function az_home_page_category_options( $taxonomy, $selected, $multiple = false ) {
    $selected_ids = array_values( array_filter( array_map( 'absint', is_array( $selected ) ? $selected : array( $selected ) ) ) );
    $terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
    if ( is_wp_error( $terms ) ) { return; }
    echo '<option value="' . ( $multiple ? '' : '0' ) . '">' . ( $multiple ? '' : '— Chọn danh mục —' ) . '</option>';
    foreach ( $terms as $term ) {
        echo '<option value="' . esc_attr( $term->term_id ) . '" ' . selected( in_array( (int) $term->term_id, $selected_ids, true ), true, false ) . '>' . esc_html( $term->name ) . '</option>';
    }
}

function az_home_page_media_field( $label, $name, $value ) {
    echo '<label class="az-home-field az-home-field--wide"><span>' . esc_html( $label ) . '</span><div class="az-home-media">';
    echo '<span class="az-home-preview">' . ( $value ? '<img src="' . esc_url( az_home_page_asset_url( $value ) ) . '" alt="">' : '' ) . '</span>';
    echo '<input type="text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"><button type="button" class="button az-home-pick">Chọn ảnh</button><button type="button" class="button-link-delete az-home-clear">Xóa</button></div></label>';
}

function az_home_page_product_admin_row( $section, $index ) {
    $selected_categories = $section['category_ids'] ?? array( $section['category_id'] ?? 0 );
    $selected_categories = array_values( array_filter( array_map( 'absint', (array) $selected_categories ) ) );
    ?>
    <div class="az-home-card az-home-product-row" data-home-product-row>
      <div class="az-home-card-head"><h4>Khối sản phẩm <?php echo esc_html( $index + 1 ); ?></h4><button type="button" class="button-link-delete az-home-product-remove">Xóa khối</button></div>
      <div class="az-home-grid">
        <label class="az-home-field"><span>Tiêu đề</span><input name="az_home_product_title[]" value="<?php echo esc_attr( $section['title'] ?? '' ); ?>"></label>
        <label class="az-home-field"><span>Danh mục WooCommerce</span><select class="az-home-category-select wc-enhanced-select" name="az_home_product_category[<?php echo esc_attr( $index ); ?>][]" multiple data-placeholder="Chọn một hoặc nhiều danh mục"><?php az_home_page_category_options( 'product_cat', $selected_categories, true ); ?></select><small>Có thể tìm kiếm và chọn nhiều danh mục sản phẩm.</small></label>
        <input type="hidden" name="az_home_product_image[]" value="<?php echo esc_attr( $section['image'] ?? '' ); ?>">
      </div>
    </div>
    <?php
}

function az_home_page_video_admin_row( $url, $index ) {
    ?>
    <div class="az-home-card az-home-video-row" data-home-video-row>
      <div class="az-home-card-head"><h4>Video <?php echo esc_html( $index + 1 ); ?></h4><button type="button" class="button-link-delete az-home-video-remove">Xóa video</button></div>
      <label class="az-home-field"><span>URL YouTube</span><input type="url" name="az_home_video_url[]" value="<?php echo esc_attr( $url ); ?>" placeholder="https://www.youtube.com/watch?v=..."></label>
    </div>
    <?php
}

function az_home_page_meta_box( $post ) {
    $data = az_home_page_get_data( $post->ID );
    $admin_product_sections = ! empty( $data['products']['sections'] ) ? array_slice( $data['products']['sections'], 0, 12 ) : array( array( 'title' => '', 'category_id' => 0, 'image' => '' ) );
    wp_nonce_field( 'az_home_page_save', 'az_home_page_nonce' );
    ?>
    <style>
      .az-home-admin{margin:-6px -12px -12px;padding:18px;background:#f6f7f9}.az-home-note{margin:0 0 16px;padding:13px 16px;border-left:4px solid #f47b20;border-radius:7px;background:#fff}.az-home-panel{margin:0 0 12px;border:1px solid #d8dee8;border-radius:9px;background:#fff;overflow:hidden}.az-home-panel>summary{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;color:#24344d;font-size:15px;font-weight: 600;cursor:pointer;list-style:none}.az-home-panel>summary::-webkit-details-marker{display:none}.az-home-panel>summary:after{display:grid;width:28px;height:28px;place-items:center;border-radius:50%;background:#f2f4f7;content:"+"}.az-home-panel[open]>summary{border-bottom:1px solid #f1c39f;color:#b94d00}.az-home-panel[open]>summary:after{background:#f47b20;color:#fff;content:"−"}.az-home-body{padding:18px}.az-home-card{margin-bottom:14px;padding:15px;border:1px solid #e1e6ee;border-radius:9px;background:#fbfcfd}.az-home-card h4{margin:0;color:#344054}.az-home-card-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0 0 13px;padding-bottom:11px;border-bottom:1px solid #e8edf3}.az-home-product-remove{font-weight:600;text-decoration:none}.az-home-product-add{display:inline-flex!important;align-items:center;gap:7px;margin-top:3px!important;border-color:#f47b20!important;background:#f47b20!important;color:#fff!important;font-weight: 600!important}.az-home-product-add:hover{border-color:#d85e08!important;background:#d85e08!important;color:#fff!important}.az-home-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.az-home-field{display:block}.az-home-field--wide{grid-column:1/-1}.az-home-field>span{display:block;margin-bottom:6px;font-weight:600}.az-home-field input,.az-home-field textarea,.az-home-field select{width:100%;max-width:none}.az-home-field input:not([type=checkbox]),.az-home-field select{height:50px;min-height:50px;padding:0 14px;border-color:#aeb7c3;border-radius:4px;line-height:48px}.az-home-field select[multiple]{height:112px;padding:7px 10px;line-height:1.4}.az-home-field .select2-container,.az-home-field .selectWoo-container{width:100%!important}.az-home-field .select2-selection--multiple{min-height:50px!important;border-color:#aeb7c3!important;border-radius:4px!important}.az-home-field textarea{min-height:110px;padding:12px 14px;border-color:#aeb7c3;border-radius:4px}.az-home-field small{display:block;margin-top:6px;color:#667085}.az-home-check{display:flex;align-items:center;gap:8px;margin-bottom:15px;font-weight: 600}.az-home-media{display:grid;grid-template-columns:72px minmax(0,1fr) 110px 110px;gap:9px;align-items:center}.az-home-media .button,.az-home-media .button-link-delete{box-sizing:border-box;width:110px;height:50px;min-height:50px;display:inline-flex;align-items:center;justify-content:center;margin:0;padding:0 14px;border:1px solid #2271b1;border-radius:4px;background:#fff;font-size:14px;font-weight:500;line-height:1;text-decoration:none;cursor:pointer}.az-home-media .button-link-delete{border-color:#d63638;color:#d63638}.az-home-media .button-link-delete:hover,.az-home-media .button-link-delete:focus{border-color:#b32d2e;color:#b32d2e}.az-home-preview{width:72px;height:58px;display:grid;place-items:center;border:1px solid #d8dee8;border-radius:7px;background:#fff;overflow:hidden}.az-home-preview img{width:100%;height:100%;object-fit:cover}@media(max-width:800px){.az-home-grid{grid-template-columns:1fr}.az-home-media{grid-template-columns:58px minmax(0,1fr)}.az-home-media .button,.az-home-media .button-link-delete{grid-row:2}}
      @media(max-width:800px){.az-home-media{grid-template-columns:110px minmax(110px,1fr)}}
    </style>
    <div class="az-home-admin" id="az-home-admin">
      <p class="az-home-note"><strong>Nội dung Trang chủ:</strong> chỉ thay đổi nội dung và nguồn dữ liệu; bố cục frontend được giữ cố định theo template.</p>

      <p class="az-home-note">Banner chính chỉnh ở hộp <strong>Banner trang</strong> (ảnh khoảng 1920 × 700 px). Trang chủ dùng tất cả ảnh đã chọn, kể cả khi chọn “Không dùng banner” hoặc tắt tự chuyển. Chưa chọn ảnh sẽ dùng ảnh SOVIBAN đóng gói trong theme con. Nội dung chữ có sẵn trong ảnh cần thay bằng ảnh mới; các trường text bên dưới hiện thêm trên ảnh.</p>
      <?php if ( function_exists( 'az_child_soviban_admin' ) ) { az_child_soviban_admin( $post->ID ); } ?>
      <details class="az-home-panel"><summary>Banner giới thiệu phía dưới</summary><div class="az-home-body">
        <p>Banner tư vấn bất động sản hiển thị dưới slider thành phố. Đổi ảnh, mô tả và đường dẫn tại đây; ảnh trống được ẩn. Video hiển thị trong khối riêng khi bật.</p>
        <?php foreach ( $data['steel']['promos'] as $i => $promo ) : ?>
        <div class="az-home-card"><h4>Banner ngang <?php echo absint( $i + 1 ); ?></h4><div class="az-home-grid">
          <label class="az-home-field"><span>Tiêu đề / mô tả ảnh</span><input name="az_home_steel_title[]" value="<?php echo esc_attr( $promo['title'] ); ?>"></label>
          <label class="az-home-field"><span>Đường dẫn</span><input name="az_home_steel_url[]" value="<?php echo esc_attr( $promo['url'] ); ?>"></label>
          <?php az_home_page_media_field( 'Ảnh banner', 'az_home_steel_image[]', $promo['image'] ); ?>
        </div></div>
        <?php endforeach; ?>
      </div></details>

      <?php az_child_home_partners_admin( $post->ID ); ?>
      <details class="az-home-panel"><summary>Nội dung bổ sung (sản phẩm, tin tức, dự án, video)</summary><div class="az-home-body">
        <p>Các nhóm tùy chọn hiển thị khi bật và có dữ liệu phù hợp. Sản phẩm cần WooCommerce; tin tức và dự án dùng bài viết; video dùng URL YouTube.</p>
      <details class="az-home-panel"><summary>Sản phẩm theo danh mục</summary><div class="az-home-body"><input type="hidden" name="az_home_products_present" value="1">
        <label class="az-home-check"><input type="checkbox" name="az_home_products_enabled" value="1" <?php checked( $data['products']['enabled'] ); ?>> Hiển thị các khối sản phẩm</label>
        <div class="az-home-product-list" data-home-product-list><?php foreach ( $admin_product_sections as $i => $section ) { az_home_page_product_admin_row( $section, $i ); } ?></div>
        <button type="button" class="button az-home-product-add">+ Thêm khối sản phẩm</button>
      </div></details>

      <details class="az-home-panel"><summary>Tin tức & bài viết nổi bật</summary><div class="az-home-body"><div class="az-home-grid">
        <label class="az-home-check az-home-field--wide"><input type="checkbox" name="az_home_blog_enabled" value="1" <?php checked( $data['blog']['enabled'] ); ?>> Hiển thị tin tức</label>
        <label class="az-home-field"><span>Tiêu đề</span><input name="az_home_blog_title" value="<?php echo esc_attr( $data['blog']['title'] ); ?>"></label>
        <label class="az-home-field"><span>Danh mục bài viết</span><select name="az_home_blog_category"><?php az_home_page_category_options( 'category', $data['blog']['category_id'] ); ?></select></label>
        <p class="az-home-field--wide">Hiển thị 4 bài mới nhất trong danh mục đã chọn.</p><input type="hidden" name="az_home_blog_limit" value="<?php echo esc_attr( $data['blog']['limit'] ); ?>">
      </div></div></details>

      <details class="az-home-panel"><summary>Dự án đã thực hiện</summary><div class="az-home-body"><div class="az-home-grid">
        <label class="az-home-check az-home-field--wide"><input type="checkbox" name="az_home_projects_enabled" value="1" <?php checked( $data['projects']['enabled'] ); ?>> Hiển thị dự án</label>
        <label class="az-home-field"><span>Tiêu đề</span><input name="az_home_projects_title" value="<?php echo esc_attr( $data['projects']['title'] ); ?>"></label>
        <label class="az-home-field"><span>Danh mục bài viết dự án</span><select name="az_home_projects_category"><?php az_home_page_category_options( 'category', $data['projects']['category_id'] ); ?></select><small>Hiển thị 3 bài mới nhất dưới dạng thẻ ảnh và nội dung. Chọn danh mục dự án để lấy đúng nội dung.</small></label>
      </div></div></details>

      <details class="az-home-panel"><summary>Video giới thiệu</summary><div class="az-home-body"><div class="az-home-grid">
        <label class="az-home-check az-home-field--wide"><input type="checkbox" name="az_home_video_enabled" value="1" <?php checked( $data['video']['enabled'] ); ?>> Hiển thị khối video</label>
        <label class="az-home-field az-home-field--wide"><span>Tiêu đề</span><input name="az_home_video_title" value="<?php echo esc_attr( $data['video']['title'] ); ?>"></label>
      </div><div class="az-home-video-list" data-home-video-list><?php $admin_videos = $data['video']['items'] ?: array( '' ); foreach ( array_slice( $admin_videos, 0, 12 ) as $i => $url ) { az_home_page_video_admin_row( $url, $i ); } ?></div><button type="button" class="button az-home-product-add az-home-video-add">+ Thêm video</button></div></details>
      </div></details>
      <script type="text/html" id="tmpl-az-home-product"><?php az_home_page_product_admin_row( array( 'title' => '', 'category_id' => 0, 'image' => '' ), 0 ); ?></script>
      <script type="text/html" id="tmpl-az-home-video"><?php az_home_page_video_admin_row( '', 0 ); ?></script>
    </div>
    <script>(function(){var root=document.getElementById('az-home-admin');if(!root)return;function enhance(scope){if(!window.jQuery||!jQuery.fn.selectWoo)return;jQuery(scope||root).find('.az-home-category-select').not('.select2-hidden-accessible').selectWoo({width:'100%',placeholder:'Chọn một hoặc nhiều danh mục',allowClear:true})}function renumber(){root.querySelectorAll('[data-home-product-row]').forEach(function(row,index){var title=row.querySelector('.az-home-card-head h4');var select=row.querySelector('.az-home-category-select');if(title)title.textContent='Khối sản phẩm '+(index+1);if(select)select.name='az_home_product_category['+index+'][]'});root.querySelectorAll('[data-home-video-row]').forEach(function(row,index){var title=row.querySelector('.az-home-card-head h4');if(title)title.textContent='Video '+(index+1)})}root.addEventListener('click',function(e){var addVideo=e.target.closest('.az-home-video-add');var removeVideo=e.target.closest('.az-home-video-remove');var add=e.target.closest('.az-home-product-add');var remove=e.target.closest('.az-home-product-remove');var pick=e.target.closest('.az-home-pick');var clear=e.target.closest('.az-home-clear');if(addVideo){var videoList=root.querySelector('[data-home-video-list]');var videoTmpl=document.getElementById('tmpl-az-home-video');if(videoList&&videoTmpl&&videoList.children.length<12){videoList.insertAdjacentHTML('beforeend',videoTmpl.innerHTML);renumber()}return}if(removeVideo){var videoRow=removeVideo.closest('[data-home-video-row]');if(videoRow){videoRow.remove();renumber()}return}if(add){var list=root.querySelector('[data-home-product-list]');var tmpl=document.getElementById('tmpl-az-home-product');if(list&&tmpl&&list.children.length<12){list.insertAdjacentHTML('beforeend',tmpl.innerHTML);renumber();enhance(list.lastElementChild)}return}if(remove){var row=remove.closest('[data-home-product-row]');if(row){row.remove();renumber()}return}if(clear){var media=clear.closest('.az-home-media');media.querySelector('input').value='';media.querySelector('.az-home-preview').innerHTML='';return}if(!pick||!window.wp||!wp.media)return;var media=pick.closest('.az-home-media');var input=media.querySelector('input');var preview=media.querySelector('.az-home-preview');var frame=wp.media({title:'Chọn ảnh',button:{text:'Dùng ảnh này'},multiple:false,library:{type:'image'}});frame.on('select',function(){var image=frame.state().get('selection').first().toJSON();input.value=image.url||'';preview.innerHTML=image.url?'<img src="'+image.url+'" alt="">':''});frame.open()});renumber();if(window.jQuery){jQuery(function(){enhance(root)})}else{window.addEventListener('load',function(){enhance(root)})}})();</script>
    <?php
}

add_action( 'add_meta_boxes_page', function( $post ) {
    if ( az_home_page_is_template( $post ) ) {
        add_meta_box( 'az_home_page_content', 'Nội dung Trang chủ', 'az_home_page_meta_box', 'page', 'normal', 'high' );
    }
} );

add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
        wp_enqueue_media();
        if ( class_exists( 'WooCommerce' ) ) {
            wp_enqueue_script( 'selectWoo' );
            wp_enqueue_style( 'woocommerce_admin_styles' );
        }
    }
} );

add_action( 'save_post_page', function( $post_id, $post ) {
    if ( ! az_home_page_is_template( $post ) || ! isset( $_POST['az_home_page_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['az_home_page_nonce'] ) ), 'az_home_page_save' ) ) { return; }
    if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }

    $titles = (array) wp_unslash( $_POST['az_home_product_title'] ?? array() );
    $categories = (array) wp_unslash( $_POST['az_home_product_category'] ?? array() );
    $images = (array) wp_unslash( $_POST['az_home_product_image'] ?? array() );
    $product_sections = array();
    $product_count = min( 12, max( count( $titles ), count( $categories ), count( $images ) ) );
    for ( $i = 0; $i < $product_count; $i++ ) {
        $category_ids = array_values( array_unique( array_filter( array_map( 'absint', (array) ( $categories[ $i ] ?? array() ) ) ) ) );
        $title = sanitize_text_field( $titles[ $i ] ?? '' );
        if ( $category_ids || $title ) { $product_sections[] = array( 'title' => $title, 'category_id' => $category_ids[0] ?? 0, 'category_ids' => $category_ids, 'image' => az_home_page_clean_url( $images[ $i ] ?? '' ) ); }
    }

    $steel_promos = array();
    $steel_titles = (array) wp_unslash( $_POST['az_home_steel_title'] ?? array() );
    $steel_images = (array) wp_unslash( $_POST['az_home_steel_image'] ?? array() );
    $steel_urls = (array) wp_unslash( $_POST['az_home_steel_url'] ?? array() );
    for ( $i = 0; $i < 2; $i++ ) {
        $steel_promos[] = array( 'title' => sanitize_text_field( $steel_titles[ $i ] ?? '' ), 'image' => az_home_page_clean_url( $steel_images[ $i ] ?? '' ), 'url' => az_home_page_clean_url( $steel_urls[ $i ] ?? '' ) );
    }
    $data = array(
        'steel' => array( 'promos' => $steel_promos ),
        'products' => array( 'enabled' => empty( $_POST['az_home_products_enabled'] ) ? 0 : 1, 'sections' => $product_sections ),
        'blog' => array(
            'enabled' => empty( $_POST['az_home_blog_enabled'] ) ? 0 : 1,
            'title' => sanitize_text_field( wp_unslash( $_POST['az_home_blog_title'] ?? '' ) ),
            'category_id' => absint( $_POST['az_home_blog_category'] ?? 0 ),
            'limit' => max( 4, min( 24, absint( $_POST['az_home_blog_limit'] ?? 12 ) ) ),
        ),
        'projects' => array(
            'enabled' => empty( $_POST['az_home_projects_enabled'] ) ? 0 : 1,
            'title' => sanitize_text_field( wp_unslash( $_POST['az_home_projects_title'] ?? '' ) ),
            'category_id' => absint( $_POST['az_home_projects_category'] ?? 0 ),
            'require_category' => 1,
        ),
        'video' => array(
            'enabled' => empty( $_POST['az_home_video_enabled'] ) ? 0 : 1,
            'title' => sanitize_text_field( wp_unslash( $_POST['az_home_video_title'] ?? '' ) ),
            'items' => array_values( array_filter( array_map( 'esc_url_raw', array_slice( (array) wp_unslash( $_POST['az_home_video_url'] ?? array() ), 0, 12 ) ) ) ),
        ),
    );
    // A submission for one panel must not reset groups that weren't submitted.
    foreach ( array( 'steel' => 'az_home_steel_title', 'products' => 'az_home_products_present', 'blog' => 'az_home_blog_title', 'projects' => 'az_home_projects_title', 'video' => 'az_home_video_title' ) as $branch => $marker ) {
        $present = isset( $_POST[ $marker ] );
        if ( 'products' === $branch ) { $present = $present || isset( $_POST['az_home_product_title'], $_POST['az_home_product_category'] ) || isset( $_POST['az_home_products_enabled'] ); }
        if ( 'steel' === $branch ) { $present = $present || isset( $_POST['az_home_steel_title'] ) || isset( $_POST['az_home_steel_image'] ); }
        if ( ! $present ) { unset( $data[ $branch ] ); }
    }
    if ( ! $data ) { return; }
    // Keep additional/legacy data; replace collection lists rather than merging indexes.
    $previous = get_post_meta( $post_id, '_az_home_page_data', true );
    $merged = array_replace_recursive( is_array( $previous ) ? $previous : array(), $data );
    foreach ( array( 'products' => 'sections', 'steel' => 'promos', 'video' => 'items' ) as $branch => $key ) {
        if ( isset( $data[ $branch ] ) ) { $merged[ $branch ][ $key ] = $data[ $branch ][ $key ]; }
    }
    update_post_meta( $post_id, '_az_home_page_data', $merged );
}, 10, 2 );
