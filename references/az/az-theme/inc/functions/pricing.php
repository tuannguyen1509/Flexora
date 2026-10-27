<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Custom Post Type: Pricing Plans
 */
function az_ai_register_pricing_cpt() {
    $labels = array(
        'name'               => esc_html__( 'Pricing Plans', 'az-ai-theme' ),
        'singular_name'      => esc_html__( 'Pricing Plan', 'az-ai-theme' ),
        'add_new'            => esc_html__( 'Add New', 'az-ai-theme' ),
        'add_new_item'       => esc_html__( 'Add New Pricing Plan', 'az-ai-theme' ),
        'edit_item'          => esc_html__( 'Edit Pricing Plan', 'az-ai-theme' ),
        'new_item'           => esc_html__( 'New Pricing Plan', 'az-ai-theme' ),
        'view_item'          => esc_html__( 'View Pricing Plan', 'az-ai-theme' ),
        'search_items'       => esc_html__( 'Search Pricing Plans', 'az-ai-theme' ),
        'not_found'          => esc_html__( 'No pricing plans found', 'az-ai-theme' ),
        'not_found_in_trash' => esc_html__( 'No pricing plans found in Trash', 'az-ai-theme' ),
    );
    
    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => false,
        'publicly_queryable'  => false,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'query_var'           => true,
        'rewrite'             => array( 'slug' => 'pricing' ),
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'menu_position'       => 21,
        'menu_icon'           => 'dashicons-tag',
        'supports'            => array( 'title', 'editor' ),
    );
    
    register_post_type( 'pricing', $args );
}
add_action( 'init', 'az_ai_register_pricing_cpt' );

// ── Bilingual Site Name ───────────────────────────────────────────────────────
add_action( 'customize_register', function( $wp_customize ) {
    // Reuse the built-in "Site Identity" section
    $wp_customize->add_setting( 'az_site_name_en', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'az_site_name_en', array(
        'label'       => '[EN] Site Name (tiêu đề tiếng Anh)',
        'description' => 'Tên website hiển thị khi chuyển sang ngôn ngữ tiếng Anh.',
        'section'     => 'title_tagline',
        'type'        => 'text',
        'priority'    => 11,
    ) );

    // Logo Size Setting
    $wp_customize->add_setting( 'az_logo_size', array(
        'default'           => '52',
        'sanitize_callback' => 'absint',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'az_logo_size', array(
        'label'       => 'Kích thước logo (px)',
        'description' => 'Điều chỉnh kích thước icon logo vuông. Mặc định: 52px',
        'section'     => 'title_tagline',
        'type'        => 'range',
        'priority'    => 12,
        'input_attrs' => array(
            'min'  => 36,
            'max'  => 72,
            'step' => 2,
        ),
    ) );

    // Logo Text Size Setting
    $wp_customize->add_setting( 'az_logo_text_size', array(
        'default'           => '16',
        'sanitize_callback' => 'absint',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'az_logo_text_size', array(
        'label'       => 'Kích thước text logo (px)',
        'description' => 'Điều chỉnh kích thước text "Khoan Cắt Bê Tông Phương Nam". Mặc định: 16px',
        'section'     => 'title_tagline',
        'type'        => 'range',
        'priority'    => 13,
        'input_attrs' => array(
            'min'  => 12,
            'max'  => 24,
            'step' => 1,
        ),
    ) );
} );

/**
 * Add meta boxes for pricing plans
 */
function az_ai_add_pricing_meta_boxes() {
    add_meta_box(
        'az_ai_pricing_details',
        esc_html__( 'Pricing Details', 'az-ai-theme' ),
        'az_ai_pricing_meta_box_callback',
        'pricing',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'az_ai_add_pricing_meta_boxes' );

function az_ai_pricing_meta_box_callback( $post ) {
    wp_nonce_field( 'az_ai_save_pricing_meta', 'az_ai_pricing_nonce' );
    
    $price = get_post_meta( $post->ID, '_az_ai_price', true );
    $period = get_post_meta( $post->ID, '_az_ai_period', true );
    $featured = get_post_meta( $post->ID, '_az_ai_featured', true );
    $description = get_post_meta( $post->ID, '_az_ai_description', true );
    $button_text = get_post_meta( $post->ID, '_az_ai_button_text', true );
    $button_url = get_post_meta( $post->ID, '_az_ai_button_url', true );
    $token_limit = get_post_meta( $post->ID, '_az_ai_token_limit', true );
    $post_limit = get_post_meta( $post->ID, '_az_ai_post_limit', true );
    ?>
    <p style="background:#f0f8ff; padding:10px; border-left:3px solid #0073aa;">
        <label for="az_ai_plan_name_en"><strong>[EN] Plan Name (tên gói tiếng Anh):</strong></label>
        <input type="text" id="az_ai_plan_name_en" name="az_ai_plan_name_en" value="<?php echo esc_attr( get_post_meta( $post->ID, '_az_ai_plan_name_en', true ) ); ?>" placeholder="e.g. Free, Pro, Enterprise" style="width: 100%;" />
        <small style="color:#666;">Leave empty to use the Vietnamese plan name for both languages.</small>
    </p>
    <p>
        <label for="az_ai_price"><?php esc_html_e( 'Price (USD):', 'az-ai-theme' ); ?></label>
        <input type="text" id="az_ai_price" name="az_ai_price" value="<?php echo esc_attr( $price ); ?>" style="width: 100%;" placeholder="$20" />
        <small style="color:#666;">Giá hiển thị khi ngôn ngữ tiếng Anh (USD). Ví dụ: $20</small>
    </p>
    <p style="background:#fff3e3; padding:10px; border-left:3px solid #f7941d;">
        <label for="az_ai_price_vnd"><strong>💰 Giá VNĐ (hiển thị khi ngôn ngữ tiếng Việt):</strong></label>
        <input type="text" id="az_ai_price_vnd" name="az_ai_price_vnd" value="<?php echo esc_attr( get_post_meta( $post->ID, '_az_ai_price_vnd', true ) ); ?>" style="width: 100%; margin-top:4px;" placeholder="490.000₫" />
        <small style="color:#666;">Ví dụ: 490.000₫ hoặc Miễn phí. Để trống sẽ dùng giá USD cho cả hai ngôn ngữ.</small>
    </p>
    <p>
        <label for="az_ai_period"><?php esc_html_e( 'Period:', 'az-ai-theme' ); ?></label>
        <input type="text" id="az_ai_period" name="az_ai_period" value="<?php echo esc_attr( $period ); ?>" placeholder="/mo" style="width: 100%;" />
    </p>
    <p>
        <label for="az_ai_description"><?php esc_html_e( 'Short Description:', 'az-ai-theme' ); ?></label>
        <input type="text" id="az_ai_description" name="az_ai_description" value="<?php echo esc_attr( $description ); ?>" placeholder="For individuals & starters." style="width: 100%;" />
    </p>
    <?php
    $description_en = get_post_meta( $post->ID, '_az_ai_description_en', true );
    ?>
    <p style="background:#f0f8ff; padding:10px; border-left:3px solid #0073aa;">
        <label for="az_ai_description_en"><strong>[EN] Short Description:</strong></label>
        <input type="text" id="az_ai_description_en" name="az_ai_description_en" value="<?php echo esc_attr( $description_en ); ?>" placeholder="For individuals & starters." style="width: 100%;" />
    </p>
    <p>
        <label for="az_ai_token_limit"><?php esc_html_e( 'Token Limit:', 'az-ai-theme' ); ?></label>
        <input type="number" id="az_ai_token_limit" name="az_ai_token_limit" value="<?php echo esc_attr( $token_limit ); ?>" placeholder="10000" style="width: 100%;" min="0" step="1000" />
        <small style="color: #666;">Số lượng token tối đa (ví dụ: 10000 = 10K, 100000 = 100K, 300000 = 300K)</small>
    </p>
    <p>
        <label for="az_ai_post_limit"><?php esc_html_e( 'Post Limit:', 'az-ai-theme' ); ?></label>
        <input type="number" id="az_ai_post_limit" name="az_ai_post_limit" value="<?php echo esc_attr( $post_limit ); ?>" placeholder="10" style="width: 100%;" min="0" step="1" />
        <small style="color: #666;">Số lượng bài viết tối đa có thể tạo (ví dụ: 10, 100, 1000)</small>
    </p>
    <p>
        <label for="az_ai_featured">
            <input type="checkbox" id="az_ai_featured" name="az_ai_featured" value="1" <?php checked( $featured, '1' ); ?> />
            <?php esc_html_e( 'Featured Plan (Recommended)', 'az-ai-theme' ); ?>
        </label>
    </p>
    <p>
        <label for="az_ai_button_text"><?php esc_html_e( 'Button Text:', 'az-ai-theme' ); ?></label>
        <input type="text" id="az_ai_button_text" name="az_ai_button_text" value="<?php echo esc_attr( $button_text ); ?>" placeholder="Choose Plan" style="width: 100%;" />
    </p>
    <p style="background:#f0f8ff; padding:10px; border-left:3px solid #0073aa;">
        <label for="az_ai_button_text_en"><strong>[EN] Button Text:</strong></label>
        <input type="text" id="az_ai_button_text_en" name="az_ai_button_text_en" value="<?php echo esc_attr( get_post_meta( $post->ID, '_az_ai_button_text_en', true ) ); ?>" placeholder="Sign Up" style="width: 100%;" />
        <small style="color:#666;">Leave empty to use the same button text for both languages.</small>
    </p>
    <p>
        <label for="az_ai_button_url"><?php esc_html_e( 'Button URL:', 'az-ai-theme' ); ?></label>
        <input type="url" id="az_ai_button_url" name="az_ai_button_url" value="<?php echo esc_url( $button_url ); ?>" placeholder="#" style="width: 100%;" />
    </p>
    <div style="background:#f0f8ff; padding:12px; border-left:3px solid #0073aa; margin-top:10px;">
        <label for="az_ai_content_en"><strong>[EN] Plan Features (English content):</strong></label>
        <p style="color:#555; font-size:12px; margin:4px 0 8px;">Nhập danh sách tính năng bằng tiếng Anh. Mỗi dòng = 1 tính năng. Ví dụ:<br><code>Unlimited posts<br>GPT, Gemini, Claude AI<br>Premium quality<br>24/7 Support</code></p>
        <textarea id="az_ai_content_en" name="az_ai_content_en" rows="6" style="width:100%; font-family:monospace;" placeholder="Unlimited posts&#10;GPT, Gemini, Claude AI&#10;Premium quality&#10;24/7 Support"><?php echo esc_textarea( get_post_meta( $post->ID, '_az_ai_content_en', true ) ); ?></textarea>
        <small style="color:#666;">Leave empty to show Vietnamese content for both languages.</small>
    </div>
    <?php
}

function az_ai_save_pricing_meta( $post_id ) {
    if ( ! isset( $_POST['az_ai_pricing_nonce'] ) || ! wp_verify_nonce( $_POST['az_ai_pricing_nonce'], 'az_ai_save_pricing_meta' ) ) {
        return;
    }
    
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    
    if ( isset( $_POST['az_ai_price'] ) ) {
        update_post_meta( $post_id, '_az_ai_price', sanitize_text_field( $_POST['az_ai_price'] ) );
    }

    if ( isset( $_POST['az_ai_price_vnd'] ) ) {
        update_post_meta( $post_id, '_az_ai_price_vnd', sanitize_text_field( $_POST['az_ai_price_vnd'] ) );
    }
    
    if ( isset( $_POST['az_ai_plan_name_en'] ) ) {
        update_post_meta( $post_id, '_az_ai_plan_name_en', sanitize_text_field( $_POST['az_ai_plan_name_en'] ) );
    }
    
    if ( isset( $_POST['az_ai_period'] ) ) {
        update_post_meta( $post_id, '_az_ai_period', sanitize_text_field( $_POST['az_ai_period'] ) );
    }
    
    if ( isset( $_POST['az_ai_description'] ) ) {
        update_post_meta( $post_id, '_az_ai_description', sanitize_text_field( $_POST['az_ai_description'] ) );
    }
    
    if ( isset( $_POST['az_ai_token_limit'] ) ) {
        update_post_meta( $post_id, '_az_ai_token_limit', absint( $_POST['az_ai_token_limit'] ) );
    }
    
    if ( isset( $_POST['az_ai_post_limit'] ) ) {
        update_post_meta( $post_id, '_az_ai_post_limit', absint( $_POST['az_ai_post_limit'] ) );
    }
    
    $featured = isset( $_POST['az_ai_featured'] ) ? '1' : '0';
    update_post_meta( $post_id, '_az_ai_featured', $featured );
    
    if ( isset( $_POST['az_ai_button_text'] ) ) {
        update_post_meta( $post_id, '_az_ai_button_text', sanitize_text_field( $_POST['az_ai_button_text'] ) );
    }
    
    if ( isset( $_POST['az_ai_button_text_en'] ) ) {
        update_post_meta( $post_id, '_az_ai_button_text_en', sanitize_text_field( $_POST['az_ai_button_text_en'] ) );
    }
    
    if ( isset( $_POST['az_ai_description_en'] ) ) {
        update_post_meta( $post_id, '_az_ai_description_en', sanitize_text_field( $_POST['az_ai_description_en'] ) );
    }
    
    if ( isset( $_POST['az_ai_content_en'] ) ) {
        update_post_meta( $post_id, '_az_ai_content_en', sanitize_textarea_field( $_POST['az_ai_content_en'] ) );
    }
    
    if ( isset( $_POST['az_ai_button_url'] ) ) {
        update_post_meta( $post_id, '_az_ai_button_url', esc_url_raw( $_POST['az_ai_button_url'] ) );
    }
}
add_action( 'save_post_pricing', 'az_ai_save_pricing_meta' );

/**
 * Add meta boxes for testimonials
 */
function az_ai_add_testimonial_meta_boxes() {
    add_meta_box(
        'az_ai_testimonial_details',
        esc_html__( 'Testimonial Details', 'az-ai-theme' ),
        'az_ai_testimonial_meta_box_callback',
        'testimonial',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'az_ai_add_testimonial_meta_boxes' );

function az_ai_testimonial_meta_box_callback( $post ) {
    wp_nonce_field( 'az_ai_save_testimonial_meta', 'az_ai_testimonial_nonce' );
    
    $author_name     = get_post_meta( $post->ID, '_az_ai_author_name', true );
    $author_position = get_post_meta( $post->ID, '_az_ai_author_position', true );
    $author_position_en = get_post_meta( $post->ID, '_az_ai_author_position_en', true );
    $content_en      = get_post_meta( $post->ID, '_az_ai_content_en', true );
    ?>
    <p>
        <label for="az_ai_author_name"><?php esc_html_e( 'Author Name:', 'az-ai-theme' ); ?></label>
        <input type="text" id="az_ai_author_name" name="az_ai_author_name" value="<?php echo esc_attr( $author_name ); ?>" style="width: 100%;" />
    </p>
    <p>
        <label for="az_ai_author_position"><?php esc_html_e( 'Author Position (VI):', 'az-ai-theme' ); ?></label>
        <input type="text" id="az_ai_author_position" name="az_ai_author_position" value="<?php echo esc_attr( $author_position ); ?>" style="width: 100%;" />
    </p>
    <p style="background:#f0f8ff; padding:10px; border-left:3px solid #0073aa;">
        <label for="az_ai_author_position_en"><strong>[EN] Author Position:</strong></label>
        <input type="text" id="az_ai_author_position_en" name="az_ai_author_position_en" value="<?php echo esc_attr( $author_position_en ); ?>" style="width: 100%;" placeholder="e.g. Marketing Director" />
    </p>
    <p style="background:#f0f8ff; padding:10px; border-left:3px solid #0073aa;">
        <label for="az_ai_content_en"><strong>[EN] Testimonial Content (English):</strong></label>
        <textarea id="az_ai_content_en" name="az_ai_content_en" rows="4" style="width: 100%;" placeholder="English version of the testimonial..."><?php echo esc_textarea( $content_en ); ?></textarea>
        <small style="color:#666;">Leave empty to show Vietnamese content for both languages.</small>
    </p>
    <?php
}

function az_ai_save_testimonial_meta( $post_id ) {
    if ( ! isset( $_POST['az_ai_testimonial_nonce'] ) || ! wp_verify_nonce( $_POST['az_ai_testimonial_nonce'], 'az_ai_save_testimonial_meta' ) ) {
        return;
    }
    
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    
    if ( isset( $_POST['az_ai_author_name'] ) ) {
        update_post_meta( $post_id, '_az_ai_author_name', sanitize_text_field( $_POST['az_ai_author_name'] ) );
    }
    
    if ( isset( $_POST['az_ai_author_position'] ) ) {
        update_post_meta( $post_id, '_az_ai_author_position', sanitize_text_field( $_POST['az_ai_author_position'] ) );
    }
    
    if ( isset( $_POST['az_ai_author_position_en'] ) ) {
        update_post_meta( $post_id, '_az_ai_author_position_en', sanitize_text_field( $_POST['az_ai_author_position_en'] ) );
    }
    
    if ( isset( $_POST['az_ai_content_en'] ) ) {
        update_post_meta( $post_id, '_az_ai_content_en', sanitize_textarea_field( $_POST['az_ai_content_en'] ) );
    }
}
add_action( 'save_post_testimonial', 'az_ai_save_testimonial_meta' );


