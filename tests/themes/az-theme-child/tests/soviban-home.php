<?php
/** Isolated fixture and regressions. No WordPress bootstrap or database operations. */
define( 'ABSPATH', __DIR__ );
$theme = dirname( __DIR__ );
$parent = dirname( $theme ) . '/az-theme';
$metadata = array( 1 => array(), 99 => array( 'soviban' => array( 'footer_title' => 'WRONG INNER PAGE' ) ) );
$banner_meta = array(); $hooks = array(); $inline_styles = array(); $styles = array();
$nonce_valid = true; $can_edit = true; $revision = false; $is_home = true; $queried_id = 1;
$linked_posts = array();
foreach ( array(
	1 => array( 'quan-ly-khai-thac', 'Quản lý khai thác', '/wp-content/themes/az-theme-child/assets/images/soviban/skyline-management.jpg', 'Quản lý khai thác tòa nhà văn phòng, quản lý tòa nhà hiệu quả. Việc quản lý khai thác đòi hỏi sự quản lý chuyên nghiệp và đồng hành lâu dài.' ),
	2 => array( 'uy-thac-tron-goi', 'Ủy thác trọn gói', '/wp-content/uploads/2023/04/hop-tac-kinh-doanh-tra-sua.jpg', 'Vì sao chủ đầu tư cần ủy thác trọn gói việc quản lý và cho thuê văn phòng cho đơn vị cung cấp dịch vụ? Giải pháp quản lý đồng bộ giúp tối ưu hoạt động và kiểm soát chi phí.' ),
	3 => array( 'quan-ly-ky-thuat', 'Quản lý kỹ thuật', '/wp-content/uploads/2023/04/quan-ly-ky-thuat-3.jpg', 'Vận hành, bảo trì và kiểm tra hệ thống kỹ thuật của tòa nhà nhằm duy trì môi trường làm việc an toàn, ổn định.' ),
	4 => array( 'quan-ly-tong-the', 'Quản lý tổng thể', '/wp-content/themes/az-theme-child/assets/images/soviban/management.png', 'Đồng bộ khai thác, kỹ thuật và các dịch vụ vận hành để nâng cao chất lượng tòa nhà.' ),
) as $id => $item ) { $linked_posts[$id] = (object) array( 'ID'=>$id,'post_name'=>$item[0],'post_title'=>$item[1],'thumbnail'=>$item[2],'post_excerpt'=>$item[3],'post_status'=>'publish','post_type'=>'post','post_password'=>'' ); }
foreach ( array( 10=>'private',11=>'draft',12=>'publish' ) as $id=>$status ) { $linked_posts[$id] = (object) array('ID'=>$id,'post_name'=>'protected-'.$id,'post_title'=>'Do not expose','thumbnail'=>'/private.jpg','post_excerpt'=>'Private content','post_status'=>$status,'post_type'=>'post','post_password'=>12===$id?'secret':''); }
$settings = array( 'general' => array( 'company_name' => 'CÔNG TY TNHH QUẢN LÝ BĐS SOVIBAN', 'phones' => array( array( 'label' => 'Hotline', 'value' => '0902-904-797' ) ), 'emails' => array( array( 'label' => 'Email', 'value' => 'pm@soviban.com' ) ), 'addresses' => array( array( 'label' => 'Địa chỉ', 'value' => '331, Nguyễn Trọng Tuyển, P.10, Q.Phú Nhuận, Tp.HCM' ) ), 'tax_code' => '0314 213 915', 'copyright' => '© Quản Lý Tòa Nhà Văn Phòng - SOVIBAN' ), 'social' => array( 'facebook' => 'https://facebook.com/example', 'instagram' => 'https://instagram.com/example', 'tiktok' => 'https://tiktok.com/@example', 'youtube' => 'https://youtube.com/@example' ) );
function add_action( $hook, $cb, $priority = 10, $args = 1 ) { $GLOBALS['hooks'][ $hook ][ $priority ][] = $cb; }
function add_filter( ...$args ) {}
function add_shortcode( ...$args ) {}
function absint( $v ) { return abs( (int) $v ); }
function get_option( $key, $default = false ) { return 'az_theme_options' === $key ? $GLOBALS['settings'] : ( 'page_on_front' === $key ? 1 : $default ); }
function get_post_meta( $id, $key, $single = true ) { if ( '_az_shared_banner' === $key ) { return $GLOBALS['banner_meta'][ $id ] ?? ''; } return '_az_home_page_data' === $key ? ( $GLOBALS['metadata'][ $id ] ?? array() ) : ''; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['metadata'][ $id ] = $value; }
function get_queried_object_id() { return $GLOBALS['queried_id']; }
function is_front_page() { return $GLOBALS['is_home']; }
function is_page_template( $template ) { return ( $GLOBALS['az_child_fixture_template'] ?? '' ) === $template; }
function is_page() { return true; }
function is_admin() { return false; }
function get_page_template_slug( ...$args ) { return $GLOBALS['az_child_fixture_template'] ?? ( is_front_page() ? 'front-page.php' : '' ); }
function get_stylesheet_directory() { return $GLOBALS['theme']; }
function get_template_directory() { return $GLOBALS['parent']; }
function get_stylesheet_directory_uri() { return '/wp-content/themes/az-theme-child'; }
function get_template_directory_uri() { return '/wp-content/themes/az-theme'; }
function home_url( $path = '/' ) { return 'http://127.0.0.1:8765' . $path; }
function admin_url( $path = '' ) { return home_url('/wp-admin/' . $path); }
function get_theme_mod( $key, $default = false ) { return $default; }
function get_bloginfo( $key, ...$args ) { return 'description' === $key ? 'Trust drives us forward!' : 'SOVIBAN'; }
function wp_parse_args( $v, $d ) { return array_merge( $d, $v ); }
function wp_unslash( $v ) { return $v; }
function wp_json_encode( $v, $flags = 0 ) { return json_encode( $v, $flags ); }
function sanitize_text_field( $v ) { return trim( strip_tags( (string) $v ) ); }
function sanitize_textarea_field( $v ) { return sanitize_text_field( $v ); }
function sanitize_email( $v ) { return filter_var( $v, FILTER_SANITIZE_EMAIL ); }
function sanitize_key( $v ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $v ) ); }
function sanitize_html_class( $v ) { return sanitize_key( $v ); }
function sanitize_hex_color( $v ) { return preg_match( '/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', (string) $v ) ? $v : ''; }
function esc_attr( $v ) { return htmlspecialchars( (string) $v, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $v ) { return esc_attr( $v ); }
function esc_textarea( $v ) { return esc_attr( $v ); }
function esc_url_raw( $v, ...$args ) { return preg_match( '#^https?://#', (string) $v ) ? $v : ''; }
function esc_url( $v, ...$args ) { return esc_attr( $v ); }
function __( $v, ...$args ) { return $v; }
function esc_html__( $v, ...$args ) { return esc_html( $v ); }
function esc_attr__( $v, ...$args ) { return esc_attr( $v ); }
function esc_html_e( $v, ...$args ) { echo esc_html( $v ); }
function esc_attr_e( $v, ...$args ) { echo esc_attr( $v ); }
function wp_kses_post( $v ) { return preg_replace( '#<script.*?</script>#s', '', strip_tags( $v, '<p><strong><em><a><br>' ) ); }
function wp_strip_all_tags( $v ) { return strip_tags( $v ); }
function wpautop( $v ) { return $v ? '<p>' . $v . '</p>' : ''; }
function wp_verify_nonce( ...$args ) { return $GLOBALS['nonce_valid']; }
function current_user_can( ...$args ) { return $GLOBALS['can_edit']; }
function wp_is_post_revision( ...$args ) { return $GLOBALS['revision']; }
function wp_parse_id_list( $v ) { return array_values( array_unique( array_filter( array_map( 'absint', is_array( $v ) ? $v : explode( ',', $v ) ) ) ) ); }
function wp_attachment_is_image( $id ) { return in_array( $id, array( 11, 12 ), true ); }
function wp_get_attachment_url( $id ) { return wp_attachment_is_image( $id ) ? get_stylesheet_directory_uri() . '/assets/images/soviban/' . ( 11 === $id ? 'hero.png' : 'skyline.jpg' ) : ''; }
function wp_get_attachment_image_url( $id, ...$args ) { return wp_get_attachment_url( $id ); }
function attachment_url_to_postid( ...$args ) { return 0; }
function wp_get_attachment_image( $id, $size, $icon = false, $attrs = array() ) { $html = '<img src="' . esc_url( wp_get_attachment_url( $id ) ) . '" alt="Media alt"'; foreach ( $attrs as $k => $v ) { $html .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"'; } return $html . '>'; }
function wp_parse_url( $v, $part = -1 ) { return parse_url( $v, $part ); }
function taxonomy_exists( ...$args ) { return false; }
function get_category_by_slug( ...$args ) { return false; }
function get_terms( ...$args ) { return array(); }
function get_post( $id ) { return is_object($id) ? $id : ($GLOBALS['linked_posts'][$id] ?? null); }
function url_to_postid( $url ) { $path=trim(parse_url($url,PHP_URL_PATH),'/'); foreach($GLOBALS['linked_posts'] as $post){if($post->post_name===$path){return $post->ID;}} return 0; }
function is_post_publicly_viewable( $post ) { return 'publish'===$post->post_status; }
function get_the_title( $post ) { return get_post($post)->post_title; }
function get_the_excerpt( $post ) { return get_post($post)->post_excerpt; }
function get_the_post_thumbnail_url( $post, $size ) { return get_post($post)->thumbnail; }
function wp_trim_words( $text, $limit, $more ) { $words=preg_split('/\s+/',trim($text));return count($words)>$limit?implode(' ',array_slice($words,0,$limit)).$more:$text; }
function get_posts( ...$args ) { return array(); }
function is_wp_error( ...$args ) { return false; }
function post_password_required( ...$args ) { return $GLOBALS['az_child_fixture_locked'] ?? false; }
function get_search_query() { return ''; }
function az_is_woocommerce_active() { return false; }
function has_nav_menu( $slot ) { return 'primary' === $slot; }
function get_search_form( ...$args ) {}
function wp_nav_menu( $args ) { $html = '<ul class="' . esc_attr( $args['menu_class'] ) . '">'; foreach ( array( 'TRANG CHỦ', 'GIỚI THIỆU', 'TƯ VẤN QUẢN LÝ', 'QUẢN LÝ TÒA NHÀ', 'DỊCH VỤ TÒA NHÀ', 'VĂN PHÒNG CHO THUÊ', 'TIN TỨC', 'LIÊN HỆ' ) as $name ) { $html .= '<li><a href="#az-home-services">' . $name . '</a></li>'; } $html .= '</ul>'; if ( isset( $args['echo'] ) && ! $args['echo'] ) { return $html; } echo $html; }
function language_attributes() { echo 'lang="vi"'; }
function bloginfo( ...$args ) { echo 'UTF-8'; }
function body_class() { $classes=array('fixture'); foreach($GLOBALS['hooks']['body_class'] ?? array() as $callbacks){foreach($callbacks as $callback){$classes=$callback($classes);}} echo 'class="'.esc_attr(implode(' ',$classes)).'"'; }
function do_action( $name ) { $handlers = $GLOBALS['hooks'][ $name ] ?? array(); ksort( $handlers ); foreach ( $handlers as $cbs ) { foreach ( $cbs as $cb ) { call_user_func( $cb ); } } }
function wp_enqueue_style( $handle, $url, $deps = array(), ...$args ) { $GLOBALS['styles'][ $handle ] = array( $url, $deps ); }
function wp_add_inline_style( $handle, $css ) { $GLOBALS['inline_styles'][ $handle ] = $css; }
function wp_head() {
	foreach ( array( '/style.css', '/css/container-sync.css', '/css/mobile-menu.css' ) as $file ) { echo '<link rel="stylesheet" href="' . get_template_directory_uri() . $file . '">'; }
	foreach ( array( 'steel-storefront', 'header-storefront', 'home-storefront', 'footer-storefront', 'soviban' ) as $name ) { echo '<link rel="stylesheet" href="' . get_stylesheet_directory_uri() . '/css/' . $name . '.css">'; }
	if ( 'brand_dropdown' === ( $GLOBALS['settings']['header']['mobile_menu_style'] ?? '' ) ) { echo '<link rel="stylesheet" href="' . get_template_directory_uri() . '/css/mobile-menu-brand-dropdown.css">'; }
	if ( in_array( $GLOBALS['az_child_fixture_template'] ?? '', array( 'page-neutral.php', 'page-about.php' ), true ) ) {
		echo '<link rel="stylesheet" href="' . get_template_directory_uri() . '/css/page.css"><link rel="stylesheet" href="' . get_template_directory_uri() . '/css/az-content.css"><link rel="stylesheet" href="' . get_template_directory_uri() . '/css/neutral-page.css">';
		az_neutral_page_assets();
	}
	echo '<style>' . az_child_menu_colors_css() . '</style>';
	az_child_soviban_assets(); foreach ( $GLOBALS['inline_styles'] as $css ) { echo '<style>' . $css . '</style>'; }
	do_action( 'wp_head' );
}
function wp_body_open() { az_render_home_seo_h1(); }
function wp_footer() { echo '<script src="' . get_template_directory_uri() . '/js/mobile-menu.js"></script><script src="' . get_stylesheet_directory_uri() . '/js/steel-storefront.js"></script>'; }
function get_template_part( $slug, $name = null, $args = array() ) { $file = get_stylesheet_directory() . '/' . $slug . '.php'; if ( ! is_file( $file ) ) { $file = get_template_directory() . '/' . $slug . '.php'; } require $file; }
function checked( $v, $match = true, $echo = true ) { $out = $v == $match ? 'checked' : ''; if ( $echo ) { echo $out; } return $out; }
function selected( $v, $match, $echo = true ) { return checked( $v, $match, $echo ); }
function wp_nonce_field( ...$args ) { echo '<input type="hidden" name="az_home_page_nonce" value="fixture">'; }
require $parent . '/inc/helpers/options.php';
require $parent . '/inc/helpers/header-components.php';
require $parent . '/inc/helpers/footer-components.php';
require $parent . '/inc/core/shared-banner.php';
require $parent . '/inc/core/mobile-menu.php';
require $parent . '/inc/core/class-dynamic-css.php';
require $theme . '/inc/steel-storefront.php';
require $theme . '/inc/home-storefront.php';
require $theme . '/inc/header-storefront.php';
require $theme . '/inc/header-colors.php';
require $theme . '/inc/footer-storefront.php';
require $theme . '/inc/modules/homepage/page-fields.php';
require $theme . '/inc/home-partners.php';
require $theme . '/inc/soviban-home.php';
function capture( $cb ) { ob_start(); $cb(); return ob_get_clean(); }
function check( $ok, $message ) { if ( ! $ok ) { throw new RuntimeException( $message ); } echo "PASS $message\n"; }
if ( defined( 'AZ_CHILD_FIXTURE_ONLY' ) && AZ_CHILD_FIXTURE_ONLY ) { return; }
if ( isset( $_GET['preview'] ) || in_array( '--preview', $argv ?? array(), true ) ) {
	foreach ( array( 'container', 'container_tablet', 'mobile_padding', 'grid_gutter' ) as $key ) {
		if ( isset( $_GET[ $key ] ) && is_scalar( $_GET[ $key ] ) ) { $settings['layout'][ $key ] = absint( $_GET[ $key ] ); }
	}
	if ( isset( $_GET['sticky'] ) ) { $settings['header'] = array( 'sticky' => 1, 'sticky_top_bar' => 1, 'sticky_header_main' => 1, 'sticky_header_bottom' => 1 ); }
	if ( isset( $_GET['hotline'] ) ) { $settings['header']['show_hotline'] = 1; }
	if ( in_array( $_GET['mobile_style'] ?? '', array( 'brand_right', 'brand_dropdown' ), true ) ) { $settings['header']['mobile_menu_style'] = $_GET['mobile_style']; $settings['header']['mobile_menu_enabled'] = 1; }
	$banner_meta[1] = array( 'layout' => 'none', 'images' => $_GET['images'] ?? '', 'autoplay' => (int) ( $_GET['autoplay'] ?? 0 ), 'transparent' => (int) ( $_GET['transparent'] ?? 0 ) );
	if ( isset( $_GET['skyline'] ) ) { $metadata[1]['soviban']['skyline_items'] = array( array( 'image' => az_child_soviban_asset('skyline.jpg') ), array( 'image' => az_child_soviban_asset('skyline.jpg') ) ); }
	if ( isset( $_GET['skyline_autoplay'] ) ) { $metadata[1]['soviban']['skyline_autoplay'] = 1; }
	if ( isset( $_GET['inner'] ) ) { $is_home = false; $queried_id = 99; }
	if ( isset( $_GET['admin'] ) ) { echo '<!doctype html><html lang="vi"><head><meta charset="utf-8"><title>Nội dung Trang chủ — fixture</title><style>body{font-family:Arial;margin:32px;max-width:1100px}button{cursor:pointer}input,textarea,select{box-sizing:border-box}</style></head><body>'; az_home_page_meta_box( (object) array( 'ID'=>1 ) ); echo '<script src="' . get_stylesheet_directory_uri() . '/js/soviban-admin.js"></script><script src="' . get_stylesheet_directory_uri() . '/js/home-partners-admin.js"></script></body></html>'; exit; }
	require $theme . '/header.php';
	if ( $is_home ) { require $theme . '/template-parts/home-storefront.php'; } else { echo '<main id="main" style="min-height:500px">Nội dung trang trong</main>'; }
	require $theme . '/footer.php';
	exit;
}
$post = (object) array( 'ID' => 1, 'post_type' => 'page', 'post_name' => 'trang-chu' );
check( 3 === count( az_child_soviban_data()['services'] ), 'fresh homepage has three reference services' );
check( 5 === count( az_child_home_partners_data()['items'] ), 'fresh homepage reuses five bundled partner images' );
$feature = az_child_soviban_skyline_feature( az_child_soviban_data() );
check( 'Quản lý khai thác' === $feature['title'] && false !== strpos( $feature['image'], 'skyline-management.jpg' ), 'skyline foreground defaults use the reference image and text' );
foreach ( array( false, 0, '' ) as $disabled ) {
	$metadata[1]['soviban']['skyline_feature_enabled'] = $disabled;
	check( array() === az_child_soviban_skyline_feature( az_child_soviban_data() ), 'explicit disabled foreground is not restored by defaults' );
}
$metadata[1]['soviban'] = array( 'skyline_feature_image' => '', 'skyline_feature_title' => '', 'skyline_feature_description' => '' );
check( array() === az_child_soviban_skyline_feature( az_child_soviban_data() ), 'clearing all foreground content hides the block' );
$metadata[1]['soviban']['skyline_feature_title'] = '0';
check( '0' === az_child_soviban_skyline_feature( az_child_soviban_data() )['title'], 'foreground literal zero remains valid text' );
foreach ( array( false, 0, '', array() ) as $empty ) {
	$metadata[1]['soviban'] = array( 'intro_title' => $empty, 'services' => array(), 'skyline_items' => array(), 'footer_logo' => '' );
	$data = az_child_soviban_data();
	check( array() === $data['services'] && array() === $data['skyline_items'] && '' === $data['footer_logo'] && ( is_array( $empty ) ? '' : $empty ) === $data['intro_title'], 'explicit empty values do not resurrect website defaults' );
}
$metadata[1] = array( 'steel' => array( 'promos' => array() ), 'partners' => array( 'items' => array(), 'title' => '' ), 'video' => array( 'items' => array('keep video') ), 'unknown' => array('keep') );
check( array() === az_child_soviban_promos(1) && array() === az_home_page_get_data()['steel']['promos'], 'cleared promo collection stays empty in frontend and editor' );
check( array() === az_child_home_partners_data()['items'], 'cleared partner collection stays empty' );
$_POST = array( 'az_child_soviban_present' => '1', 'az_home_page_nonce' => 'valid', 'az_child_soviban' => array( 'intro_title' => '<b>Edited</b>', 'intro_description' => '<strong>Safe</strong><script>alert(1)</script>', 'services' => array( 'image' => array( '/photo.jpg' ), 'title' => array( 'New service' ), 'url' => array( 'javascript:alert(1)' ) ), 'footer_logo' => '' ) );
foreach ( $hooks['save_post_page'][10] as $callback ) { $callback( 1, $post ); }
az_child_soviban_save( 1, $post );
check( 'Edited' === $metadata[1]['soviban']['intro_title'] && false === strpos($metadata[1]['soviban']['intro_description'],'<script'), 'save sanitizes text and rich description' );
check( '' === $metadata[1]['soviban']['services'][0]['url'] && 0 === $metadata[1]['soviban']['intro_enabled'], 'save rejects unsafe link and respects unchecked switches' );
check( array('keep') === $metadata[1]['unknown'] && array('keep video') === $metadata[1]['video']['items'] && array() === $metadata[1]['steel']['promos'], 'save preserves all other homepage branches' );
$_POST['az_child_soviban']['services'] = array(); az_child_soviban_save(1,$post);
check( array() === az_child_soviban_data()['services'], 'removing all service rows stays empty after save' );
$before = $metadata;
foreach ( array('nonce_valid','can_edit') as $flag ) { $GLOBALS[$flag] = false; az_child_soviban_save(1,$post); check($before===$metadata,'rejects '.$flag); $GLOBALS[$flag]=true; }
$revision = true; az_child_soviban_save(1,$post); $revision = false; check($before===$metadata,'rejects revisions');
unset($_POST['az_child_soviban_present']); az_child_soviban_save(1,$post); check($before===$metadata,'missing panel preserves data');
$metadata[1]['soviban'] = array( 'skyline_items' => array( array( 'image' => '/keep-background.jpg', 'title' => 'Keep background', 'url' => '' ) ), 'footer_note' => 'Keep footer' );
$_POST = array( 'az_child_soviban_present' => '1', 'az_home_page_nonce' => 'valid', 'az_child_soviban' => array( 'skyline_feature_present' => '1', 'skyline_feature_enabled' => '1', 'skyline_feature_image' => '/chosen.jpg', 'skyline_feature_title' => '<b>Managed building</b>', 'skyline_feature_description' => '<strong>Content</strong><script>bad()</script>', 'skyline_feature_url' => 'javascript:bad()' ) );
foreach ( $hooks['save_post_page'][10] as $callback ) { $callback( 1, $post ); }
az_child_soviban_save(1,$post);
check( 'Managed building' === $metadata[1]['soviban']['skyline_feature_title'] && false === strpos($metadata[1]['soviban']['skyline_feature_description'],'<script'), 'foreground saves plain title and sanitized rich description' );
check( home_url('/chosen.jpg') === $metadata[1]['soviban']['skyline_feature_image'] && '' === $metadata[1]['soviban']['skyline_feature_url'], 'foreground resolves image URL and rejects unsafe detail links' );
check( 'Keep background' === $metadata[1]['soviban']['skyline_items'][0]['title'] && 'Keep footer' === $metadata[1]['soviban']['footer_note'] && array('keep video') === $metadata[1]['video']['items'], 'foreground save preserves background collection, footer and other meta branches' );
$_POST['az_child_soviban'] = array( 'skyline_feature_present' => '1', 'skyline_feature_image' => '', 'skyline_feature_title' => '', 'skyline_feature_description' => '', 'skyline_feature_url' => '' );
az_child_soviban_save(1,$post);
check( 0 === $metadata[1]['soviban']['skyline_feature_enabled'] && '' === $metadata[1]['soviban']['skyline_feature_image'], 'unchecked foreground and cleared image stay off and empty after saving' );
$_POST['az_child_soviban'] = array( 'footer_note' => 'Updated footer' ); az_child_soviban_save(1,$post);
check( 0 === $metadata[1]['soviban']['skyline_feature_enabled'], 'older or partial forms do not reset the foreground switch' );
foreach ( array('/protected-10/','/protected-11/','/protected-12/','https://other.example/quan-ly-khai-thac/','/missing-page/') as $link ) {
	check( array()===az_child_soviban_linked_feature($link), 'automatic slide rejects non-public, protected, missing or external content: '.$link );
}
$_POST = array( 'az_child_soviban_present'=>'1','az_home_page_nonce'=>'valid','az_child_soviban'=>array('skyline_feature_present'=>'1','skyline_feature_enabled'=>'1','skyline_enabled'=>'1','skyline_items_present'=>'1','skyline_items'=>array(
	'image'=>array('/background-1.jpg','/background-2.jpg'), 'title'=>array('Background 1','Background 2'), 'url'=>array('/quan-ly-khai-thac/','/uy-thac-tron-goi/'),
	'feature_source'=>array('auto','custom'), 'feature_image'=>array('','/custom-slide.jpg'), 'feature_title'=>array('','<b>Custom second slide</b>'), 'feature_description'=>array('','<strong>Own content</strong><script>bad()</script>'),
) ) );
az_child_soviban_save(1,$post);
$dynamic_data=az_child_soviban_data(); $dynamic_features=az_child_soviban_skyline_features($dynamic_data,$dynamic_data['skyline_items']);
check( 'Quản lý khai thác'===$dynamic_features[0]['title'] && 'Custom second slide'===$dynamic_features[1]['title'] && home_url('/custom-slide.jpg')===$dynamic_features[1]['image'], 'each saved slide resolves its own automatic or custom content' );
check( false===strpos($dynamic_features[1]['description'],'<script') && false!==strpos($dynamic_features[1]['description'],'<strong>Own content</strong>'), 'custom slide description is sanitized while retaining rich text' );
check( home_url('/uy-thac-tron-goi/')===$dynamic_features[1]['url'] && 'Updated footer'===$metadata[1]['soviban']['footer_note'] && array('keep video')===$metadata[1]['video']['items'], 'per-slide detail URLs and unrelated saved settings are preserved' );
$raw_rows=$metadata[1]['soviban']['skyline_items'];
foreach (array('feature_source','feature_image','feature_title','feature_description') as $field) { unset($_POST['az_child_soviban']['skyline_items'][$field]); }
az_child_soviban_save(1,$post);
check( $raw_rows===$metadata[1]['soviban']['skyline_items'], 'legacy form submissions preserve existing per-slide fields' );
$_POST['az_child_soviban']['skyline_items']['feature_source']=array('none','custom');
$_POST['az_child_soviban']['skyline_items']['feature_image']=array('','');
$_POST['az_child_soviban']['skyline_items']['feature_title']=array('','0');
$_POST['az_child_soviban']['skyline_items']['feature_description']=array('','');
az_child_soviban_save(1,$post); $dynamic_data=az_child_soviban_data(); $dynamic_features=az_child_soviban_skyline_features($dynamic_data,$dynamic_data['skyline_items']);
check( !isset($dynamic_features[0]) && '0'===$dynamic_features[1]['title'] && ''===$dynamic_features[1]['image'], 'background-only mode and explicit cleared image/literal zero remain per slide' );
$legacy_data=az_child_soviban_defaults(); $legacy_slides=array(array('image'=>'/bg1.jpg','url'=>''),array('image'=>'/bg2.jpg','url'=>''));
check( array(0)===array_keys(az_child_soviban_skyline_features($legacy_data,$legacy_slides)), 'legacy global block is used only for the first unlinked slide' );
$_POST['az_child_soviban']['skyline_items']=array(); az_child_soviban_save(1,$post);
check( array()===az_child_soviban_data()['skyline_items'], 'removing every dynamic slide does not restore defaults' );
$metadata[1] = array();
foreach ( array(0,1) as $transparent ) { foreach ( array(0,1) as $autoplay ) { foreach ( array('', '11', '11,999,12') as $images ) {
	$banner_meta[1] = array('layout'=>'none','images'=>$images,'transparent'=>$transparent,'autoplay'=>$autoplay);
	$data = az_child_soviban_banner(1);
	check( (bool)$transparent === $data['overlay_header'] && (int)$autoplay === $data['autoplay'], 'layout none keeps independent overlay/playback switches: '.$transparent.'/'.$autoplay.'/'.$images );
	if ( '11,999,12' === $images ) { check(array(11,12)===$data['ids'],'manual slider retains all valid image IDs in order'); }
} } }
$settings['header'] = array('show_category'=>0,'show_account'=>false,'show_hotline'=>0,'top_bar_text'=>'','height'=>100,'child_top_social'=>0,'child_search_scope'=>'products');
$data = az_child_header_options(); check( ''===$data['top_bar_text'] && false===$data['show_account'] && 100===$data['height'] && 0===$data['child_top_social'], 'saved Header values outrank child defaults' );
$settings['header'] = array(); $banner_meta[1] = array();
$html = capture(function() use($theme) { require $theme.'/header.php'; require $theme.'/template-parts/home-storefront.php'; require $theme.'/footer.php'; });
check( 3 === substr_count($html,'class="az-child-home__service"') && 5 === substr_count($html,'class="az-home-partners__item"') && 1 === substr_count($html,'class="az-child-footer"'), 'full template renders reference services, partners and one footer' );
check( 4 === substr_count($html,'data-az-steel-feature="') && false !== strpos($html,'Xem chi tiết: Quản lý khai thác') && false !== strpos($html,'Xem chi tiết: Ủy thác trọn gói'), 'skyline renders distinct linked foregrounds for its four slides' );
$metadata[1]['soviban'] = array( 'skyline_feature_image' => '', 'skyline_feature_url' => '', 'skyline_feature_description' => '<strong>Edited content</strong>' );
$metadata[1]['soviban']['skyline_items'] = array( array( 'image'=>'/background.jpg','url'=>'' ) );
$foreground_html = capture(function() use($theme){ require $theme.'/template-parts/home-storefront.php'; });
check( false !== strpos($foreground_html,'az-child-skyline-feature--text-only') && false === strpos($foreground_html,'class="az-child-skyline-feature__link"') && false !== strpos($foreground_html,'<strong>Edited content</strong>'), 'image and detail link can be cleared without losing rich description' );
$metadata[1]['soviban']['skyline_feature_enabled'] = 0;
check( false === strpos(capture(function() use($theme){ require $theme.'/template-parts/home-storefront.php'; }),'class="az-child-skyline-feature'), 'disabled foreground preserves the background carousel without the block' );
$metadata[1] = array();
preg_match('#<section class="az-child-carousel az-child-carousel--hero".*?</section>#s',$html,$hero_html);
check( false===strpos($hero_html[0],'data-az-steel-prev') && false===strpos($hero_html[0],'data-az-steel-pause'), 'single-image hero has no controls or playback button' );
$footer_home = capture(function() use($theme){ require $theme.'/footer.php'; });
$is_home=false; $queried_id=99;
$footer_inner = capture(function() use($theme){ require $theme.'/footer.php'; });
preg_match('#<footer\b.*?</footer>#s',$footer_home,$a); preg_match('#<footer\b.*?</footer>#s',$footer_inner,$b);
check($a[0]===$b[0] && false===strpos($footer_inner,'WRONG INNER PAGE'),'inner page renders same footer from homepage settings');
az_child_soviban_assets(); check( !in_array('az-home-storefront',$styles['az-child-soviban'][1],true),'inner page asset graph has no missing homepage dependency');
echo "All SOVIBAN isolated regressions passed.\n";
