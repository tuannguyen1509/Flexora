<?php
/** Shared page/category banner. All presentation colours come from theme tokens. */
defined( 'ABSPATH' ) || exit;

function az_shared_banner_defaults() {
    return array( 'layout' => 'none', 'text' => '', 'subtitle' => '', 'images' => '', 'height' => '', 'mobile_height' => '', 'overlay' => 35, 'transparent' => 0, 'autoplay' => 0, 'button_text' => '', 'button_url' => '' );
}

function az_shared_banner_layouts() {
    return array( 'none' => 'Không dùng banner', 'left' => 'Tiêu đề căn trái', 'center' => 'Tiêu đề căn giữa', 'split' => 'Text lớn bên trái + tiêu đề bên phải', 'slider' => 'Slider ảnh + tiêu đề', 'image' => 'Ảnh toàn chiều rộng, tiêu đề bên dưới' );
}

function az_shared_banner_get( $id, $term = false ) {
    $saved = $term ? get_term_meta( $id, '_az_shared_banner', true ) : get_post_meta( $id, '_az_shared_banner', true );
    if ( ! is_array( $saved ) ) {
        $saved = array();
        // Read the supplied sample's legacy fields without overwriting existing data.
        if ( ! $term ) {
            $legacy = get_post_meta( $id, '_az_page_banner_layout', true );
            $layouts = array( 'title' => 'center', 'slider' => 'slider', 'full_width' => 'image', 'split_text_title' => 'split', 'none' => 'none' );
            if ( isset( $layouts[ $legacy ] ) ) {
                $saved = array( 'layout' => $layouts[ $legacy ], 'images' => get_post_meta( $id, '_az_page_banner_images', true ), 'text' => get_post_meta( $id, '_az_page_banner_custom_text', true ), 'height' => get_post_meta( $id, '_az_page_banner_height', true ) ?: 450 );
            }
        } else {
            $legacy = absint( get_term_meta( $id, '_az_category_banner_image', true ) );
            if ( $legacy ) { $saved = array( 'layout' => 'center', 'images' => (string) $legacy ); }
        }
    }
    return array_merge( az_shared_banner_defaults(), $saved );
}

function az_shared_banner_context() {
    if ( is_page() ) {
        $page_id = get_queried_object_id();

        // The default page template is intentionally a plain content page.
        // Custom page templates and the front page may still use shared banners.
        if ( ! is_front_page() && ! get_page_template_slug( $page_id ) ) {
            return false;
        }

        return array( $page_id, false );
    }
    if ( is_category() || is_tax( 'product_cat' ) ) { return array( get_queried_object_id(), true ); }
    return false;
}

function az_shared_banner_active() {
    $context = az_shared_banner_context();
    if ( ! $context ) { return false; }
    $data = az_shared_banner_get( $context[0], $context[1] );
    return 'none' !== $data['layout'];
}

add_filter( 'body_class', function( $classes ) {
    if ( az_shared_banner_context() ) { $classes[] = 'az-shared-banner-scope'; }
    return $classes;
} );

function az_shared_banner_fields( $data ) {
    wp_nonce_field( 'az_shared_banner_save', 'az_shared_banner_nonce' );
    echo '<div class="az-banner-editor"><p>Áp dụng riêng cho trang/danh mục này. Màu lấy từ <strong>Advanced → Colors</strong>, chiều rộng lấy từ <strong>Layout</strong>. Để trống tiêu đề để dùng tên trang/danh mục.</p>';
    echo '<p><label for="az-banner-layout"><strong>Bố cục</strong></label><br><select id="az-banner-layout" name="az_banner[layout]">';
    foreach ( az_shared_banner_layouts() as $value => $label ) { echo '<option value="' . esc_attr( $value ) . '" ' . selected( $data['layout'], $value, false ) . '>' . esc_html( $label ) . '</option>'; }
    echo '</select></p>';
    foreach ( array( 'text' => 'Tiêu đề / text lớn trên banner', 'subtitle' => 'Mô tả ngắn (không thay thế mô tả danh mục)', 'button_text' => 'Chữ nút liên kết (không bắt buộc)', 'button_url' => 'Đường dẫn nút liên kết' ) as $key => $label ) {
        echo '<p><label for="az-banner-' . esc_attr( $key ) . '"><strong>' . esc_html( $label ) . '</strong></label><br><input class="widefat" id="az-banner-' . esc_attr( $key ) . '" type="' . ( 'button_url' === $key ? 'url' : 'text' ) . '" name="az_banner[' . esc_attr( $key ) . ']" value="' . esc_attr( $data[ $key ] ) . '"></p>';
    }
    foreach ( array( 'height' => array( 'Chiều cao desktop (px)', 180, 1000 ), 'mobile_height' => array( 'Chiều cao mobile (px)', 140, 700 ) ) as $key => $field ) {
        echo '<p><label for="az-banner-' . esc_attr( $key ) . '">' . esc_html( $field[0] ) . '</label> <input id="az-banner-' . esc_attr( $key ) . '" type="number" min="' . esc_attr( $field[1] ) . '" max="' . esc_attr( $field[2] ) . '" placeholder="100%" name="az_banner[' . esc_attr( $key ) . ']" value="' . esc_attr( $data[ $key ] ) . '"></p>';
    }
    echo '<p><label for="az-banner-overlay">Lớp phủ ảnh (%)</label> <input id="az-banner-overlay" type="number" min="0" max="90" name="az_banner[overlay]" value="' . esc_attr( $data['overlay'] ) . '"></p>';
    echo '<div class="description" style="margin:-8px 0 18px">Để trống chiều cao để ảnh hiển thị đủ theo tỷ lệ 100%; khi nhập số, giá trị được tính theo px.</div>';
    foreach ( array( 'transparent' => 'Header đè lên banner (desktop, không áp dụng khi tắt banner)', 'autoplay' => 'Slider tự chuyển ảnh (3 giây; người xem có thể tạm dừng)' ) as $key => $label ) {
        echo '<p><label><input type="checkbox" name="az_banner[' . esc_attr( $key ) . ']" value="1" ' . checked( ! empty( $data[ $key ] ), true, false ) . '> ' . esc_html( $label ) . '</label></p>';
    }
    echo '<p><strong>Ảnh banner / slider</strong></p><input type="hidden" data-az-banner-images name="az_banner[images]" value="' . esc_attr( is_array( $data['images'] ) ? implode( ',', $data['images'] ) : $data['images'] ) . '"><div data-az-banner-preview>';
    foreach ( wp_parse_id_list( $data['images'] ) as $id ) {
        $url = wp_get_attachment_image_url( $id, 'thumbnail' );
        if ( $url ) { echo '<img data-id="' . esc_attr( $id ) . '" src="' . esc_url( $url ) . '" alt="" width="80" height="60" style="object-fit:cover;margin:4px">'; }
    }
    echo '</div><p><button class="button" type="button" data-az-banner-pick>Chọn / sắp xếp ảnh</button> <button class="button" type="button" data-az-banner-clear>Xóa ảnh</button></p><p class="description">Slider dùng tất cả ảnh; bố cục khác dùng ảnh đầu. Không có ảnh sẽ dùng nền màu theme. Breadcrumb theo công tắc General → Enable Breadcrumb.</p></div>';
}

add_action( 'add_meta_boxes_page', function() {
    add_meta_box( 'az-shared-banner', 'Banner trang', function( $post ) { az_shared_banner_fields( az_shared_banner_get( $post->ID ) ); }, 'page', 'normal', 'high' );
} );

function az_shared_banner_sanitize( $input ) {
    $data = az_shared_banner_defaults();
    $layouts = az_shared_banner_layouts();
    $data['layout'] = isset( $input['layout'] ) && is_string( $input['layout'] ) && isset( $layouts[ $input['layout'] ] ) ? $input['layout'] : 'none';
    foreach ( array( 'text', 'subtitle', 'button_text' ) as $key ) { $data[ $key ] = sanitize_text_field( $input[ $key ] ?? '' ); }
    $data['button_url'] = esc_url_raw( $input['button_url'] ?? '' );
    $ids = array_filter( wp_parse_id_list( $input['images'] ?? '' ), 'wp_attachment_is_image' );
    $data['images'] = implode( ',', array_slice( $ids, 0, 20 ) );
    foreach ( array( 'height' => array( 180, 1000 ), 'mobile_height' => array( 140, 700 ) ) as $key => $limits ) {
        $value = trim( (string) ( $input[ $key ] ?? '' ) );
        $data[ $key ] = '' === $value ? '' : max( $limits[0], min( $limits[1], absint( $value ) ) );
    }
    $data['overlay'] = max( 0, min( 90, absint( $input['overlay'] ?? $data['overlay'] ) ) );
    foreach ( array( 'transparent', 'autoplay' ) as $key ) { $data[ $key ] = empty( $input[ $key ] ) ? 0 : 1; }
    return $data;
}

function az_shared_banner_submission() {
    if ( ! isset( $_POST['az_shared_banner_nonce'], $_POST['az_banner'] ) || ! is_array( $_POST['az_banner'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['az_shared_banner_nonce'] ) ), 'az_shared_banner_save' ) ) { return false; }
    return az_shared_banner_sanitize( wp_unslash( $_POST['az_banner'] ) );
}

add_action( 'save_post_page', function( $id ) {
    if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $id ) || ! current_user_can( 'edit_post', $id ) ) { return; }
    $data = az_shared_banner_submission();
    if ( false !== $data ) { update_post_meta( $id, '_az_shared_banner', $data ); }
} );

foreach ( array( 'category', 'product_cat' ) as $az_banner_taxonomy ) {
    add_action( $az_banner_taxonomy . '_add_form_fields', function() { echo '<div class="form-field"><h2>Banner danh mục</h2>'; az_shared_banner_fields( az_shared_banner_defaults() ); echo '</div>'; } );
    add_action( $az_banner_taxonomy . '_edit_form_fields', function( $term ) { echo '<tr class="form-field"><th>Banner danh mục</th><td>'; az_shared_banner_fields( az_shared_banner_get( $term->term_id, true ) ); echo '</td></tr>'; } );
    foreach ( array( 'created_', 'edited_' ) as $az_banner_event ) {
        add_action( $az_banner_event . $az_banner_taxonomy, function( $id ) {
            if ( ! current_user_can( 'edit_term', $id ) ) { return; }
            $data = az_shared_banner_submission();
            if ( false !== $data ) { update_term_meta( $id, '_az_shared_banner', $data ); }
        } );
    }
}

add_action( 'admin_enqueue_scripts', function() {
    $screen = get_current_screen();
    if ( ! $screen || ! ( ( 'post' === $screen->base && 'page' === $screen->post_type ) || ( in_array( $screen->base, array( 'edit-tags', 'term' ), true ) && in_array( $screen->taxonomy, array( 'category', 'product_cat' ), true ) ) ) ) { return; }
    wp_enqueue_media();
    wp_enqueue_style( 'az-banner-editor', get_template_directory_uri() . '/css/banner-editor.css', array(), filemtime( get_template_directory() . '/css/banner-editor.css' ) );
    wp_enqueue_script( 'az-banner-editor', get_template_directory_uri() . '/assets/admin/js/az-banner-editor.js', array( 'jquery', 'media-views' ), filemtime( get_template_directory() . '/assets/admin/js/az-banner-editor.js' ), true );
} );

/** Escaped, hierarchical breadcrumb shared by all supported banner contexts. */
function az_shared_breadcrumb() {
    if ( ! az_get_option( 'general.breadcrumb', 1 ) || is_front_page() ) { return; }
    $object = get_queried_object();
    $items = array( array( home_url( '/' ), 'Trang chủ' ) );
    if ( $object instanceof WP_Term ) {
        if ( 'product_cat' === $object->taxonomy && function_exists( 'wc_get_page_id' ) ) {
            $shop = wc_get_page_id( 'shop' );
            if ( $shop > 0 ) { $items[] = array( get_permalink( $shop ), get_the_title( $shop ) ); }
        }
        foreach ( array_reverse( get_ancestors( $object->term_id, $object->taxonomy, 'taxonomy' ) ) as $id ) {
            $parent = get_term( $id, $object->taxonomy );
            $url = get_term_link( $id, $object->taxonomy );
            if ( $parent && ! is_wp_error( $parent ) && ! is_wp_error( $url ) ) { $items[] = array( $url, $parent->name ); }
        }
        $title = $object->name;
    } else {
        foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $id ) { $items[] = array( get_permalink( $id ), get_the_title( $id ) ); }
        $title = get_the_title( get_queried_object_id() );
    }
    $items[] = array( '', $title );
    if ( get_query_var( 'paged' ) > 1 ) { $items[] = array( '', sprintf( 'Trang %d', get_query_var( 'paged' ) ) ); }
    echo '<nav class="az-shared-breadcrumb" aria-label="Đường dẫn trang"><ol>';
    foreach ( $items as $index => $item ) {
        echo '<li>';
        if ( $item[0] ) { echo '<a href="' . esc_url( $item[0] ) . '">' . esc_html( $item[1] ) . '</a>'; }
        else { echo '<span' . ( $index === count( $items ) - 1 ? ' aria-current="page"' : '' ) . '>' . esc_html( $item[1] ) . '</span>'; }
        echo '</li>';
    }
    echo '</ol></nav>';
}

add_action( 'wp_enqueue_scripts', function() {
    if ( ! az_shared_banner_context() ) { return; }
    foreach ( array( 'css/shared-banner.css', 'js/shared-banner.js' ) as $file ) {
        $url = get_template_directory_uri() . '/' . $file;
        $version = filemtime( get_template_directory() . '/' . $file );
        if ( '.css' === substr( $file, -4 ) ) { wp_enqueue_style( 'az-shared-banner', $url, array(), $version ); }
        else { wp_enqueue_script( 'az-shared-banner', $url, array(), $version, true ); }
    }
}, 100 );

add_action( 'az_after_site_header', function() {
    $context = az_shared_banner_context();
    if ( ! $context ) { return; }
    // The homepage places these images beside its category menu instead of
    // rendering a second full-width banner above the page template.
    if ( is_front_page() && locate_template( 'template-parts/home-reference.php' ) ) { return; }
    $data = az_shared_banner_get( $context[0], $context[1] );
    if ( 'none' === $data['layout'] ) {
        if ( ! is_front_page() && az_get_option( 'general.breadcrumb', 1 ) ) { echo '<div class="az-shared-trail"><div class="az-shared-container">'; az_shared_breadcrumb(); echo '</div></div>'; }
        return;
    }
    $object = get_queried_object();
    $title = $context[1] ? $object->name : get_the_title( $context[0] );
    $ids = array_values( array_filter( wp_parse_id_list( $data['images'] ), 'wp_attachment_is_image' ) );
    if ( 'slider' !== $data['layout'] ) { $ids = array_slice( $ids, 0, 1 ); }
    $desktop_height = '' === trim( (string) $data['height'] ) ? '100%' : absint( $data['height'] ) . 'px';
    $mobile_height = '' === trim( (string) $data['mobile_height'] ) ? '100%' : absint( $data['mobile_height'] ) . 'px';
    echo '<section class="az-shared-banner az-shared-banner--' . esc_attr( $data['layout'] ) . '" aria-label="' . esc_attr( $title ) . '" data-autoplay="' . esc_attr( $data['autoplay'] ) . '" data-loop="1" data-overlay-header="' . esc_attr( $data['transparent'] ) . '" style="--az-banner-height:' . esc_attr( $desktop_height ) . ';--az-banner-mobile-height:' . esc_attr( $mobile_height ) . ';--az-banner-overlay:' . esc_attr( absint( $data['overlay'] ) / 100 ) . '">';
    echo '<div class="az-shared-banner__media" aria-hidden="true">';
    foreach ( $ids as $index => $id ) { echo wp_get_attachment_image( $id, 'full', false, array( 'class' => 'az-shared-banner__slide', 'data-slide' => $index, 'style' => $index ? 'display:none' : '', 'alt' => '', 'loading' => $index ? 'lazy' : 'eager' ) ); }
    echo '</div><div class="az-shared-container az-shared-banner__content">';
    az_shared_breadcrumb();
    echo '<div class="az-shared-banner__copy">';
    if ( 'split' === $data['layout'] && $data['text'] ) { echo '<p class="az-shared-banner__statement">' . esc_html( $data['text'] ) . '</p>'; }
    // The homepage already receives its Branding H1 through wp_body_open.
    $heading_tag = is_front_page() ? 'h2' : 'h1';
    echo '<div><' . $heading_tag . '>' . esc_html( 'split' !== $data['layout'] && $data['text'] ? $data['text'] : $title ) . '</' . $heading_tag . '>';
    if ( $data['subtitle'] ) { echo '<p class="az-shared-banner__subtitle">' . esc_html( $data['subtitle'] ) . '</p>'; }
    if ( $data['button_text'] && $data['button_url'] ) { echo '<a class="az-shared-banner__cta" href="' . esc_url( $data['button_url'] ) . '">' . esc_html( $data['button_text'] ) . '</a>'; }
    echo '</div></div>';
    if ( count( $ids ) > 1 ) {
        echo '<div class="az-shared-banner__controls" hidden><button type="button" data-direction="-1" aria-label="Ảnh trước">←</button><span data-slide-status aria-live="polite">1 / ' . count( $ids ) . '</span><button type="button" data-direction="1" aria-label="Ảnh tiếp theo">→</button>';
        if ( $data['autoplay'] ) { echo '<button type="button" data-pause aria-pressed="false">Tạm dừng</button>'; }
        echo '</div>';
    }
    echo '</div></section>';
} );
