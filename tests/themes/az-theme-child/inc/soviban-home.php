<?php
/** SOVIBAN website content. Parent APIs are called only by hooked callbacks/templates. */
defined( 'ABSPATH' ) || exit;

function az_child_soviban_asset( $name ) {
	return get_stylesheet_directory_uri() . '/assets/images/soviban/' . $name;
}

function az_child_soviban_defaults() {
	return array(
		'intro_enabled' => 1,
		'intro_title' => 'GIỚI THIỆU VỀ SOVIBAN',
		'intro_description' => 'SOVIBAN chuyên cung cấp các giải pháp tổng thể <strong>quản lý tòa nhà và cho thuê văn phòng</strong> nhằm đạt mục tiêu nâng cao giá trị, tăng doanh thu, kiểm soát chi phí và đảm bảo chất lượng lâu dài. Các giải pháp của SOVIBAN bao gồm:',
		'services' => array(
			array( 'title' => 'TƯ VẤN QUẢN LÝ', 'image' => az_child_soviban_asset( 'consulting.png' ), 'url' => '/tu-van-quan-ly/' ),
			array( 'title' => 'QUẢN LÝ TÒA NHÀ', 'image' => az_child_soviban_asset( 'management.png' ), 'url' => '/quan-ly-toa-nha/' ),
			array( 'title' => 'DỊCH VỤ TÒA NHÀ', 'image' => az_child_soviban_asset( 'services.png' ), 'url' => '/dich-vu-toa-nha/' ),
		),
		'skyline_enabled' => 1,
		'skyline_autoplay' => 0,
		'skyline_feature_enabled' => 1,
		'skyline_feature_image' => az_child_soviban_asset( 'skyline-management.jpg' ),
		'skyline_feature_title' => 'Quản lý khai thác',
		'skyline_feature_description' => 'Quản lý khai thác tòa nhà văn phòng, quản lý tòa nhà hiệu quả. Tòa nhà văn phòng là một trong những loại tòa nhà thương mại phổ biến nhất trên thế giới. Việc quản lý khai thác tòa nhà văn phòng đòi hỏi sự quản lý chuyên nghiệp và […]',
		'skyline_feature_url' => '/quan-ly-khai-thac/',
		'skyline_items' => array(
			array( 'image' => az_child_soviban_asset( 'skyline.jpg' ), 'title' => 'Không gian thành phố', 'url' => '/quan-ly-khai-thac/' ),
			array( 'image' => az_child_soviban_asset( 'skyline-evening.jpg' ), 'title' => 'Thành phố lúc hoàng hôn', 'url' => '/uy-thac-tron-goi/' ),
			array( 'image' => az_child_soviban_asset( 'skyline-river.jpg' ), 'title' => 'Các tòa nhà bên sông', 'url' => '/quan-ly-ky-thuat/' ),
			array( 'image' => az_child_soviban_asset( 'skyline-night.jpeg' ), 'title' => 'Thành phố về đêm', 'url' => '/quan-ly-tong-the/' ),
		),
		'contact_band' => 0,
		'footer_title' => 'SOVIBAN - QUẢN LÝ BẤT ĐỘNG SẢN',
		'footer_description' => 'SOVIBAN chuyên cung cấp các giải pháp tổng thể quản lý tòa nhà văn phòng nhằm đạt mục tiêu nâng cao giá trị, tăng doanh thu, kiểm soát chi phí và đảm bảo chất lượng lâu dài.',
		'footer_background' => az_child_soviban_asset( 'footer-city.png' ),
		'footer_logo' => az_child_soviban_asset( 'footer-logo.png' ),
		'footer_tagline' => 'SOVIBAN - Trust drives us forward!',
		'footer_note' => 'SOVIBAN là đơn vị chuyên nghiệp trong quản lý tòa nhà văn phòng. Dịch vụ bao gồm: Quản lý khai thác - Quản lý kỹ thuật - Quản lý tổng thể hoặc nhận ủy thác trọn gói.',
	);
}

/** Footer always reads the configured homepage, never an inner page's metadata. */
function az_child_soviban_data( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : absint( get_option( 'page_on_front' ) );
	if ( ! $post_id && az_child_is_steel_home() ) { $post_id = get_queried_object_id(); }
	$raw = get_post_meta( $post_id, '_az_home_page_data', true );
	$saved = is_array( $raw ) && is_array( $raw['soviban'] ?? null ) ? $raw['soviban'] : array();
	$data = array_replace( az_child_soviban_defaults(), $saved );
	foreach ( array( 'services', 'skyline_items' ) as $key ) {
		$data[ $key ] = array_values( array_filter( is_array( $data[ $key ] ) ? $data[ $key ] : array(), 'is_array' ) );
		foreach ( $data[ $key ] as &$item ) {
			foreach ( array( 'title', 'url', 'image' ) as $field ) {
				$item[ $field ] = is_string( $item[ $field ] ?? null ) ? $item[ $field ] : '';
			}
			if ( 'skyline_items' === $key ) {
				$item['feature_source'] = in_array( $item['feature_source'] ?? 'auto', array( 'auto', 'custom', 'none' ), true ) ? ( $item['feature_source'] ?? 'auto' ) : 'auto';
				foreach ( array( 'feature_image', 'feature_title', 'feature_description' ) as $field ) {
					$item[ $field ] = is_scalar( $item[ $field ] ?? null ) ? (string) $item[ $field ] : '';
				}
			}
		}
		unset( $item );
	}
	foreach ( $data as $key => $value ) {
		if ( ! in_array( $key, array( 'services', 'skyline_items' ), true ) && ! is_scalar( $value ) ) { $data[ $key ] = ''; }
	}
	return $data;
}

function az_child_soviban_url( $value ) {
	$value = is_string( $value ) ? trim( $value ) : '';
	if ( '/' === substr( $value, 0, 1 ) && '//' !== substr( $value, 0, 2 ) ) { $value = home_url( $value ); }
	return esc_url_raw( $value, array( 'http', 'https' ) );
}

/** Legacy foreground remains available as a fallback for the first slide only. */
function az_child_soviban_skyline_feature( $data ) {
	if ( empty( $data['skyline_feature_enabled'] ) ) { return array(); }
	$feature = array();
	foreach ( array( 'image', 'title', 'description', 'url' ) as $key ) {
		$value = $data[ 'skyline_feature_' . $key ] ?? '';
		$feature[ $key ] = is_scalar( $value ) ? (string) $value : '';
	}
	$feature['image'] = az_child_soviban_url( $feature['image'] );
	$feature['url'] = az_child_soviban_url( $feature['url'] );
	return '' !== $feature['image'] || '' !== trim( $feature['title'] ) || '' !== trim( wp_strip_all_tags( $feature['description'] ) ) ? $feature : array();
}

/** Read published, password-free content from a local WordPress URL; no remote fetch. */
function az_child_soviban_linked_feature( $url ) {
	static $cache = array();
	$url = az_child_soviban_url( $url );
	if ( ! $url || strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) ) !== strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) ) ) { return array(); }
	if ( array_key_exists( $url, $cache ) ) { return $cache[ $url ]; }
	$cache[ $url ] = array();
	$post_id = url_to_postid( $url );
	$post = $post_id ? get_post( $post_id ) : null;
	if ( ! $post || 'publish' !== $post->post_status || ! is_post_publicly_viewable( $post ) || ! empty( $post->post_password ) || ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) { return array(); }
	$cache[ $url ] = array(
		'image' => get_the_post_thumbnail_url( $post, 'large' ) ?: '',
		'title' => get_the_title( $post ),
		'description' => esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post ) ), 45, ' […]' ) ),
		'url' => $url,
	);
	return $cache[ $url ];
}

/** Keep feature indices aligned with the filtered, visible background slide list. */
function az_child_soviban_skyline_features( $data, $slides ) {
	$features = array();
	if ( empty( $data['skyline_feature_enabled'] ) ) { return $features; }
	foreach ( $slides as $index => $slide ) {
		$source = $slide['feature_source'] ?? 'auto';
		$feature = array();
		if ( 'custom' === $source ) {
			$feature = az_child_soviban_skyline_feature( array(
				'skyline_feature_enabled' => 1,
				'skyline_feature_image' => $slide['feature_image'] ?? '',
				'skyline_feature_title' => $slide['feature_title'] ?? '',
				'skyline_feature_description' => $slide['feature_description'] ?? '',
				'skyline_feature_url' => $slide['url'] ?? '',
			) );
		} elseif ( 'auto' === $source ) {
			$feature = az_child_soviban_linked_feature( $slide['url'] ?? '' );
			// Preserve a legacy manually configured block without repeating it on every slide.
			if ( ! $feature && 0 === $index && ! ( $slide['url'] ?? '' ) ) { $feature = az_child_soviban_skyline_feature( $data ); }
		}
		if ( $feature ) { $features[ $index ] = $feature; }
	}
	return $features;
}

/** Missing lists use website defaults; explicitly cleared lists stay cleared. */
function az_child_soviban_promos( $post_id ) {
	$raw = get_post_meta( $post_id, '_az_home_page_data', true );
	if ( is_array( $raw ) && is_array( $raw['steel'] ?? null ) && array_key_exists( 'promos', $raw['steel'] ) ) {
		$promos = is_array( $raw['steel']['promos'] ) ? array_filter( $raw['steel']['promos'], 'is_array' ) : array();
		foreach ( $promos as &$promo ) {
			foreach ( array( 'title', 'url', 'image' ) as $field ) { $promo[ $field ] = is_string( $promo[ $field ] ?? null ) ? $promo[ $field ] : ''; }
		}
		unset( $promo );
		return $promos;
	}
	return array( array( 'title' => 'Tư vấn & quản lý bất động sản', 'image' => az_child_soviban_asset( 'consulting-banner.png' ), 'url' => '/tu-van-quan-ly/' ) );
}

function az_child_soviban_partner_defaults() {
	$items = array();
	foreach ( array( 'sk', 'fc', 'tg', 'aerofoam', 'kps' ) as $name ) {
		$items[] = array( 'id' => 0, 'image' => az_child_soviban_asset( 'partner-' . $name . '.png' ) );
	}
	return array( 'enabled' => 1, 'title' => 'KHÁCH HÀNG, ĐỐI TÁC TIÊU BIỂU', 'items' => $items );
}

function az_child_soviban_header_defaults() {
	return array( 'height' => 86, 'show_category' => 0, 'show_account' => 0, 'show_cart' => 0, 'show_hotline' => 0,
		'top_bar_text' => az_child_footer_data()['company'], 'search_placeholder' => 'Bạn cần tìm gì?', 'child_search_scope' => 'all', 'child_top_social' => 1 );
}

function az_child_soviban_logo() {
	$id = az_get_logo_id();
	$branding = az_get_option( 'branding', array() );
	if ( $id || ( is_array( $branding ) && array_key_exists( 'logo', $branding ) ) ) {
		az_render_header_logo( array( 'logo_id' => $id, 'class' => 'az-logo' ) );
		return;
	}
	echo '<a class="az-logo" href="' . esc_url( home_url( '/' ) ) . '" aria-label="' . esc_attr( get_bloginfo( 'name' ) ) . '"><img src="' . esc_url( az_child_soviban_asset( 'logo.png' ) ) . '" width="230" height="55" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" loading="eager"></a>';
}

/** Do not infer homepage overlay from the global Header transparent switch. */
function az_child_soviban_banner( $post_id ) {
	$data = az_shared_banner_get( $post_id );
	foreach ( array( 'text', 'subtitle', 'button_text', 'button_url', 'height', 'mobile_height' ) as $key ) {
		$data[ $key ] = is_scalar( $data[ $key ] ) ? (string) $data[ $key ] : '';
	}
	foreach ( array( 'transparent', 'autoplay', 'overlay' ) as $key ) {
		$data[ $key ] = is_scalar( $data[ $key ] ) ? absint( $data[ $key ] ) : 0;
	}
	if ( ! is_string( $data['images'] ) && ! is_array( $data['images'] ) ) { $data['images'] = ''; }
	$data['ids'] = array_values( array_filter( wp_parse_id_list( $data['images'] ), 'wp_attachment_is_image' ) );
	$data['has_media'] = (bool) $data['ids'] || is_file( get_stylesheet_directory() . '/assets/images/soviban/hero.png' );
	$data['overlay_header'] = $data['has_media'] && ! empty( $data['transparent'] );
	return $data;
}

function az_child_soviban_assets() {
	$dir = get_stylesheet_directory();
	$dependencies = array( 'az-header-storefront', 'az-footer-storefront' );
	if ( az_child_is_steel_home() ) { $dependencies[] = 'az-home-storefront'; }
	wp_enqueue_style( 'az-child-soviban', get_stylesheet_directory_uri() . '/css/soviban.css', $dependencies, (string) filemtime( $dir . '/css/soviban.css' ) );
	$accent = az_get_option( 'colors.primary', '#ef1429' );
	$heading = az_get_option( 'colors.heading', '#4e6275' );
	$accent = is_string( $accent ) ? sanitize_hex_color( $accent ) : '';
	$heading = is_string( $heading ) ? sanitize_hex_color( $heading ) : '';
	$text = az_get_option( 'colors.muted', '#67798b' );
	$text = is_string( $text ) ? sanitize_hex_color( $text ) : '';
	$width = min( 600, max( 40, absint( az_get_option( 'branding.logo_width', 200 ) ) ) );
	$mobile_width = min( 400, max( 32, absint( az_get_option( 'branding.logo_width_mobile', 140 ) ) ) );
	$weight = az_get_option( 'typography.navigation_weight', 400 );
	$weight = in_array( (int) $weight, array( 300, 400, 500, 600, 700, 800, 900 ), true ) ? (int) $weight : 400;
	wp_add_inline_style( 'az-child-soviban', '.az-child-soviban-site{--az-child-accent:' . ( $accent ?: '#ef1429' ) . ';--az-child-heading:' . ( $heading ?: '#4e6275' ) . ';--az-child-text:' . ( $text ?: '#67798b' ) . ';--az-child-navigation-weight:' . $weight . ';--az-child-logo-width:' . $width . 'px;--az-child-logo-width-mobile:' . $mobile_width . 'px;}' );
}
add_action( 'wp_enqueue_scripts', 'az_child_soviban_assets', 50 );

/** Make website defaults visible in Advanced as well as on the frontend; never seed storage. */
function az_child_soviban_register_fields() {
	if ( ! is_admin() || ! class_exists( 'AZ_Theme_Options' ) || ! class_exists( 'AZ_Options_Registry' ) ) { return; }
	$section = AZ_Options_Registry::get( 'header' );
	if ( ! $section ) { return; }
	$defaults = az_child_soviban_header_defaults();
	foreach ( $section['fields'] as &$field ) {
		if ( array_key_exists( $field['id'], $defaults ) ) { $field['default'] = $defaults[ $field['id'] ]; }
	}
	unset( $field );
	AZ_Options_Registry::add_section( 'header', $section['title'], $section['fields'] );
	$ids = array_column( $section['fields'], 'id' );
	foreach ( array(
		array( 'id' => 'child_top_social', 'type' => 'switch', 'title' => 'Mạng xã hội trên thanh đầu trang', 'default' => 1, 'group' => 'top_bar' ),
		array( 'id' => 'child_search_scope', 'type' => 'select', 'title' => 'Phạm vi tìm kiếm', 'default' => 'all', 'options' => array( 'all' => 'Toàn website', 'products' => 'Sản phẩm WooCommerce' ), 'group' => 'header_main' ),
	) as $field ) {
		if ( ! in_array( $field['id'], $ids, true ) ) { AZ_Theme_Options::add_field( 'header', $field ); }
	}
	foreach ( array( 'colors' => array( 'primary' => '#ef1429', 'heading' => '#4e6275', 'muted' => '#67798b' ), 'branding' => array( 'logo_width' => 200 ), 'typography' => array( 'navigation_weight' => 400 ) ) as $id => $website_defaults ) {
		$section = AZ_Options_Registry::get( $id );
		if ( ! $section ) { continue; }
		foreach ( $section['fields'] as &$field ) {
			if ( array_key_exists( $field['id'], $website_defaults ) ) { $field['default'] = $website_defaults[ $field['id'] ]; }
		}
		unset( $field );
		AZ_Options_Registry::add_section( $id, $section['title'], $section['fields'] );
	}
}
add_action( 'after_setup_theme', 'az_child_soviban_register_fields', 25 );

function az_child_soviban_admin_row( $item, $type ) {
	$prefix = 'az_child_soviban[' . $type . ']';
	?>
	<div class="az-home-card" data-az-child-row>
		<div class="az-home-card-head"><h4><?php echo esc_html( 'services' === $type ? __( 'Dịch vụ', 'az-theme-child' ) : __( 'Slide thành phố', 'az-theme-child' ) ); ?></h4><button class="button" type="button" data-az-child-remove><?php esc_html_e( 'Xóa mục', 'az-theme-child' ); ?></button></div>
		<div class="az-home-grid">
			<label class="az-home-field"><span><?php esc_html_e( 'Tiêu đề / mô tả ảnh', 'az-theme-child' ); ?></span><input name="<?php echo esc_attr( $prefix . '[title][]' ); ?>" value="<?php echo esc_attr( $item['title'] ?? '' ); ?>"></label>
			<label class="az-home-field"><span><?php echo esc_html( 'skyline_items' === $type ? __( 'Liên kết bài viết / xem chi tiết', 'az-theme-child' ) : __( 'Liên kết (để trống nếu chỉ hiện ảnh)', 'az-theme-child' ) ); ?></span><input name="<?php echo esc_attr( $prefix . '[url][]' ); ?>" value="<?php echo esc_attr( $item['url'] ?? '' ); ?>"></label>
			<?php az_home_page_media_field( 'skyline_items' === $type ? __( 'Ảnh nền', 'az-theme-child' ) : __( 'Ảnh', 'az-theme-child' ), $prefix . '[image][]', $item['image'] ?? '' ); ?>
			<?php if ( 'skyline_items' === $type ) : ?>
			<label class="az-home-field az-home-field--wide"><span><?php esc_html_e( 'Nguồn nội dung của slide', 'az-theme-child' ); ?></span><select name="<?php echo esc_attr( $prefix . '[feature_source][]' ); ?>"><?php foreach ( array( 'auto' => 'Tự lấy từ bài viết theo liên kết', 'custom' => 'Nhập ảnh và nội dung riêng', 'none' => 'Chỉ hiện ảnh nền' ) as $value => $label ) : ?><option value="<?php echo esc_attr( $value ); ?>" <?php selected( $item['feature_source'] ?? 'auto', $value ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select><small><?php esc_html_e( 'Chế độ tự động lấy ảnh đại diện, tiêu đề và tóm tắt của bài viết/trang đã công khai, không có mật khẩu trên website này. Các trường riêng bên dưới chỉ dùng khi chọn Nhập ảnh và nội dung riêng.', 'az-theme-child' ); ?></small></label>
			<?php az_home_page_media_field( __( 'Ảnh bên trái của slide', 'az-theme-child' ), $prefix . '[feature_image][]', $item['feature_image'] ?? '' ); ?>
			<label class="az-home-field az-home-field--wide"><span><?php esc_html_e( 'Tiêu đề riêng của slide', 'az-theme-child' ); ?></span><input name="<?php echo esc_attr( $prefix . '[feature_title][]' ); ?>" value="<?php echo esc_attr( $item['feature_title'] ?? '' ); ?>"></label>
			<label class="az-home-field az-home-field--wide"><span><?php esc_html_e( 'Mô tả riêng của slide', 'az-theme-child' ); ?></span><textarea name="<?php echo esc_attr( $prefix . '[feature_description][]' ); ?>"><?php echo esc_textarea( $item['feature_description'] ?? '' ); ?></textarea></label>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

function az_child_soviban_admin( $post_id ) {
	$data = az_child_soviban_data( $post_id );
	?>
	<details class="az-home-panel" open><summary><?php esc_html_e( 'Giới thiệu SOVIBAN & dịch vụ', 'az-theme-child' ); ?></summary><div class="az-home-body">
		<input type="hidden" name="az_child_soviban_present" value="1">
		<label class="az-home-check"><input type="checkbox" name="az_child_soviban[intro_enabled]" value="1" <?php checked( $data['intro_enabled'], 1 ); ?>><?php esc_html_e( 'Hiển thị giới thiệu và dịch vụ', 'az-theme-child' ); ?></label>
		<div class="az-home-grid">
			<label class="az-home-field az-home-field--wide"><span><?php esc_html_e( 'Tiêu đề giới thiệu', 'az-theme-child' ); ?></span><input name="az_child_soviban[intro_title]" value="<?php echo esc_attr( $data['intro_title'] ); ?>"></label>
			<label class="az-home-field az-home-field--wide"><span><?php esc_html_e( 'Nội dung giới thiệu (cho phép chữ đậm, liên kết)', 'az-theme-child' ); ?></span><textarea name="az_child_soviban[intro_description]"><?php echo esc_textarea( $data['intro_description'] ); ?></textarea></label>
		</div>
		<?php az_child_soviban_admin_list( $data, 'services' ); ?>
		<label class="az-home-check"><input type="checkbox" name="az_child_soviban[contact_band]" value="1" <?php checked( $data['contact_band'], 1 ); ?>><?php esc_html_e( 'Dải hotline/email dưới banner chính (lấy từ General)', 'az-theme-child' ); ?></label>
	</div></details>
	<details class="az-home-panel"><summary><?php esc_html_e( 'Slider thành phố & chân trang', 'az-theme-child' ); ?></summary><div class="az-home-body">
		<label class="az-home-check"><input type="checkbox" name="az_child_soviban[skyline_enabled]" value="1" <?php checked( $data['skyline_enabled'], 1 ); ?>><?php esc_html_e( 'Hiển thị slider thành phố', 'az-theme-child' ); ?></label>
		<label class="az-home-check"><input type="checkbox" name="az_child_soviban[skyline_autoplay]" value="1" <?php checked( $data['skyline_autoplay'], 1 ); ?>><?php esc_html_e( 'Tự chuyển ảnh thành phố mỗi 3 giây', 'az-theme-child' ); ?></label>
		<input type="hidden" name="az_child_soviban[skyline_feature_present]" value="1">
		<label class="az-home-check"><input type="checkbox" name="az_child_soviban[skyline_feature_enabled]" value="1" <?php checked( $data['skyline_feature_enabled'], 1 ); ?>><?php esc_html_e( 'Hiển thị ảnh và nội dung riêng theo từng slide', 'az-theme-child' ); ?></label>
		<p><?php esc_html_e( 'Mỗi slide bên dưới có ảnh nền, liên kết và nguồn nội dung riêng. Khi chuyển slide, ảnh bên trái, tiêu đề, mô tả và liên kết chi tiết chuyển cùng ảnh nền.', 'az-theme-child' ); ?></p>
		<?php az_child_soviban_admin_list( $data, 'skyline_items' ); ?>
		<details class="az-home-card"><summary><?php esc_html_e( 'Nội dung cũ dự phòng cho slide đầu', 'az-theme-child' ); ?></summary>
			<p><?php esc_html_e( 'Giữ nội dung đã lưu trước đây. Chỉ dùng cho slide đầu ở chế độ tự động khi chưa nhập liên kết bài viết; không lặp lại nội dung này trên các slide khác.', 'az-theme-child' ); ?></p>
			<div class="az-home-grid">
				<?php az_home_page_media_field( __( 'Ảnh bên trái', 'az-theme-child' ), 'az_child_soviban[skyline_feature_image]', $data['skyline_feature_image'] ); ?>
				<label class="az-home-field"><span><?php esc_html_e( 'Tiêu đề khối nội dung', 'az-theme-child' ); ?></span><input name="az_child_soviban[skyline_feature_title]" value="<?php echo esc_attr( $data['skyline_feature_title'] ); ?>"></label>
				<label class="az-home-field"><span><?php esc_html_e( 'Liên kết xem chi tiết (trống để ẩn mũi tên)', 'az-theme-child' ); ?></span><input name="az_child_soviban[skyline_feature_url]" value="<?php echo esc_attr( $data['skyline_feature_url'] ); ?>"></label>
				<label class="az-home-field az-home-field--wide"><span><?php esc_html_e( 'Mô tả khối nội dung (cho phép chữ đậm, liên kết)', 'az-theme-child' ); ?></span><textarea name="az_child_soviban[skyline_feature_description]"><?php echo esc_textarea( $data['skyline_feature_description'] ); ?></textarea></label>
			</div>
		</details>
		<p><?php esc_html_e( 'Chân trang dùng chung cho mọi trang. Thông tin doanh nghiệp và mạng xã hội chỉnh tại AZ Theme → Advanced → General / Social; logo header và menu chỉnh tại Branding / Header và Giao diện → Menu.', 'az-theme-child' ); ?></p>
		<p><?php foreach ( array( 'general' => 'Thông tin doanh nghiệp', 'social' => 'Mạng xã hội', 'branding' => 'Logo & SEO', 'header' => 'Header & tìm kiếm' ) as $section => $label ) : ?><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=az-theme-advanced&section=' . $section ) ); ?>"><?php echo esc_html( $label ); ?></a> <?php endforeach; ?><a class="button" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php esc_html_e( 'Menu điều hướng', 'az-theme-child' ); ?></a></p>
		<div class="az-home-grid">
		<?php foreach ( array( 'footer_title' => 'Tiêu đề cột giới thiệu', 'footer_description' => 'Mô tả doanh nghiệp', 'footer_tagline' => 'Khẩu hiệu dưới logo', 'footer_note' => 'Nội dung dưới khẩu hiệu' ) as $key => $label ) : ?>
			<label class="az-home-field"><span><?php echo esc_html( $label ); ?></span><textarea name="<?php echo esc_attr( 'az_child_soviban[' . $key . ']' ); ?>"><?php echo esc_textarea( $data[ $key ] ); ?></textarea></label>
		<?php endforeach; ?>
		<?php foreach ( array( 'footer_background' => 'Ảnh nền chân trang', 'footer_logo' => 'Logo chân trang' ) as $key => $label ) { az_home_page_media_field( $label, 'az_child_soviban[' . $key . ']', $data[ $key ] ); } ?>
		</div>
	</div></details>
	<?php
}

function az_child_soviban_admin_list( $data, $type ) {
	?>
	<div data-az-child-list="<?php echo esc_attr( $type ); ?>">
		<input type="hidden" name="<?php echo esc_attr( 'az_child_soviban[' . $type . '_present]' ); ?>" value="1">
		<div data-az-child-rows><?php foreach ( $data[ $type ] as $item ) { az_child_soviban_admin_row( $item, $type ); } ?></div>
		<p><button type="button" class="button button-primary" data-az-child-add><?php esc_html_e( 'Thêm mục', 'az-theme-child' ); ?></button></p>
		<template><?php az_child_soviban_admin_row( array(), $type ); ?></template>
	</div>
	<?php
}

function az_child_soviban_save( $post_id, $post ) {
	if ( ! az_home_page_is_template( $post ) || ! isset( $_POST['az_child_soviban_present'], $_POST['az_home_page_nonce'], $_POST['az_child_soviban'] ) || ! is_array( $_POST['az_child_soviban'] ) ) { return; }
	if ( ! is_string( $_POST['az_home_page_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['az_home_page_nonce'] ) ), 'az_home_page_save' ) ) { return; }
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
	$input = wp_unslash( $_POST['az_child_soviban'] );
	$raw = get_post_meta( $post_id, '_az_home_page_data', true );
	$raw = is_array( $raw ) ? $raw : array();
	$data = is_array( $raw['soviban'] ?? null ) ? $raw['soviban'] : array();
	foreach ( az_child_soviban_defaults() as $key => $default ) {
		if ( 'skyline_feature_enabled' === $key ) {
			if ( isset( $input['skyline_feature_present'] ) || array_key_exists( $key, $input ) ) {
				$data[ $key ] = isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) && '1' === (string) $input[ $key ] ? 1 : 0;
			}
		} elseif ( in_array( $key, array( 'services', 'skyline_items' ), true ) ) {
			if ( ! array_key_exists( $key, $input ) && ! isset( $input[ $key . '_present' ] ) ) { continue; }
			$list = is_array( $input[ $key ] ?? null ) ? $input[ $key ] : array();
			$previous_items = is_array( $data[ $key ] ?? null ) ? $data[ $key ] : array();
			$data[ $key ] = array();
			foreach ( array_slice( is_array( $list['image'] ?? null ) ? $list['image'] : array(), 0, 12, true ) as $i => $image ) {
				if ( ! is_string( $image ) || ! is_string( $list['title'][ $i ] ?? '' ) || ! is_string( $list['url'][ $i ] ?? '' ) ) { continue; }
				$row = is_array( $previous_items[ $i ] ?? null ) ? $previous_items[ $i ] : array();
				$row = array_replace( $row, array( 'image' => az_child_soviban_url( $image ), 'title' => sanitize_text_field( $list['title'][ $i ] ?? '' ), 'url' => az_child_soviban_url( $list['url'][ $i ] ?? '' ) ) );
				if ( 'skyline_items' === $key ) {
					foreach ( array( 'feature_source', 'feature_image', 'feature_title', 'feature_description' ) as $field ) {
						if ( ! is_array( $list[ $field ] ?? null ) || ! array_key_exists( $i, $list[ $field ] ) || ! is_string( $list[ $field ][ $i ] ) ) { continue; }
						$value = $list[ $field ][ $i ];
						if ( 'feature_source' === $field ) { $row[ $field ] = in_array( $value, array( 'auto', 'custom', 'none' ), true ) ? $value : 'auto'; }
						elseif ( 'feature_image' === $field ) { $row[ $field ] = az_child_soviban_url( $value ); }
						elseif ( 'feature_description' === $field ) { $row[ $field ] = wp_kses_post( $value ); }
						else { $row[ $field ] = sanitize_text_field( $value ); }
					}
				}
				$data[ $key ][] = $row;
			}
		} elseif ( in_array( $key, array( 'intro_enabled', 'skyline_enabled', 'skyline_autoplay', 'contact_band' ), true ) ) {
			$data[ $key ] = isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) && '1' === (string) $input[ $key ] ? 1 : 0;
		} elseif ( array_key_exists( $key, $input ) && is_string( $input[ $key ] ) ) {
			$data[ $key ] = in_array( $key, array( 'intro_description', 'skyline_feature_description' ), true ) ? wp_kses_post( $input[ $key ] ) : ( in_array( $key, array( 'footer_logo', 'footer_background', 'skyline_feature_image', 'skyline_feature_url' ), true ) ? az_child_soviban_url( $input[ $key ] ) : sanitize_textarea_field( $input[ $key ] ) );
		}
	}
	$raw['soviban'] = $data;
	update_post_meta( $post_id, '_az_home_page_data', $raw );
}
add_action( 'save_post_page', 'az_child_soviban_save', 40, 2 );

function az_child_soviban_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type ) { return; }
	wp_enqueue_script( 'az-child-soviban-admin', get_stylesheet_directory_uri() . '/js/soviban-admin.js', array( 'media-views' ), (string) filemtime( get_stylesheet_directory() . '/js/soviban-admin.js' ), true );
}
add_action( 'admin_enqueue_scripts', 'az_child_soviban_admin_assets' );
