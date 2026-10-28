<?php
/** Neutral-page fixture and regressions without bootstrapping WordPress/database. */
define( 'AZ_CHILD_FIXTURE_ONLY', true );
require __DIR__ . '/soviban-home.php';
require $parent . '/inc/core/neutral-page.php';
$az_child_fixture_template = isset( $_GET['about'] ) ? 'page-about.php' : 'page-neutral.php';
function get_theme_file_path( $file ) { return $GLOBALS['parent'] . '/' . $file; }
function get_theme_file_uri( $file ) { return get_template_directory_uri() . '/' . $file; }
$is_home = false; $queried_id = 99; $loop_index = 0;
$az_child_fixture_locked = false; $az_child_fixture_thumbnail = true;
$az_child_fixture_paginated = false; $az_child_fixture_comments = false;
$content_calls = 0;
$linked_posts[99] = (object) array( 'ID'=>99, 'post_type'=>'page', 'post_title'=>'Hệ thống quản lý tòa nhà thông minh', 'post_content'=>'<h2>Các đối tượng quản lý của hệ thống</h2><p>Hệ thống <strong>quản lý tòa nhà</strong> thông minh BMS sẽ quản lý các đối tượng sau: Hệ thống phân phối điện năng, máy phát và ắc quy điện dự phòng, hệ thống ánh sáng, điều hòa nhiệt độ, thông gió, hệ thống cấp, thoát nước và xử lý nước, rác thải, phòng cháy chữa cháy, thang máy và thang thoát hiểm, hệ thống an ninh, âm thanh, hình ảnh, thông tin liên lạc.</p><p>Hệ thống BMS sẽ giám sát và điều khiển tối ưu các hệ thống trong tòa nhà trên cơ sở phân tích đặc trưng từng hệ thống và mối liên hệ giữa các hệ thống đó. Thông qua hệ thống quản lý thông minh này sẽ giúp cho ban quản lý dễ dàng hơn trong việc điều khiển và vận hành cả một tòa nhà lớn.</p><h3>Nội dung linh hoạt cho từng trang</h3><p>Trang này dùng bố cục một cột, có thể chỉnh sửa bằng trình soạn thảo WordPress. <a href="#more">Xem thêm thông tin</a>.</p><ul><li>Ảnh đại diện được chọn riêng cho mỗi trang.</li><li>Định dạng đoạn văn, tiêu đề, danh sách và liên kết được giữ.</li></ul>', 'post_status'=>'publish', 'post_password'=>'' );
$linked_posts[100] = (object) array( 'ID'=>100, 'post_type'=>'page', 'post_title'=>'Chính sách dịch vụ', 'post_content'=>'<p>Nội dung riêng của trang chính sách.</p>', 'post_status'=>'publish', 'post_password'=>'' );
function get_header() { require $GLOBALS['theme'].'/header.php'; }
function get_footer() { require $GLOBALS['theme'].'/footer.php'; }
function have_posts() { return $GLOBALS['loop_index'] < 1; }
function the_post() { $GLOBALS['loop_index']++; }
function get_the_ID() { return $GLOBALS['queried_id']; }
function the_ID() { echo get_the_ID(); }
function the_title() { echo esc_html(get_the_title(get_the_ID())); }
function post_class( $class ) { echo 'class="'.esc_attr($class).'"'; }
function has_post_thumbnail() { return $GLOBALS['az_child_fixture_thumbnail']; }
function the_post_thumbnail( $size, $attrs ) { echo '<img src="/wp-content/themes/az-theme-child/assets/images/soviban/skyline-river.jpg" width="1920" height="1080" alt="Thành phố bên sông"';foreach($attrs as $key=>$value){echo ' '.esc_attr($key).'="'.esc_attr($value).'"';}echo '>'; }
function the_content() { $GLOBALS['content_calls']++;echo $GLOBALS['az_child_fixture_locked']?'<form class="post-password-form"><label>Mật khẩu <input type="password"></label></form>':get_post(get_the_ID())->post_content; }
function wp_link_pages( $args ) { if($GLOBALS['az_child_fixture_paginated']){echo $args['before'].'<span class="post-page-numbers current">1</span><a class="post-page-numbers" href="#page-2">2</a>'.$args['after'];} }
function comments_open() { return $GLOBALS['az_child_fixture_comments']; }
function get_comments_number() { return 0; }
function comments_template() { echo '<div class="fixture-comments">Bình luận</div>'; }
function get_queried_object() { return get_post(get_the_ID()); }
function get_post_ancestors( $id ) { return array(); }
function get_query_var( $key ) { return 0; }
function render_neutral( $id = 99 ) { $GLOBALS['queried_id']=$id;$GLOBALS['loop_index']=0;$GLOBALS['content_calls']=0;return capture(function(){require $GLOBALS['parent'].'/'.( 'page-about.php'===$GLOBALS['az_child_fixture_template'] ? 'page-about.php' : 'page-neutral.php' );}); }
function legacy_chat_content( $content ) {
	return '<div class="group w-full text-gray-800 dark:text-gray-100 border-b border-black/10 dark:border-gray-900/50 bg-gray-50 dark:bg-[#444654]"><div class="flex p-4 gap-4 text-base md:gap-6 md:max-w-2xl lg:max-w-xl xl:max-w-3xl md:py-6 lg:px-0 m-auto"><div class="relative flex flex-col w-[calc(100%-50px)] gap-1 md:gap-3 lg:w-[calc(100%-115px)]"><div class="flex flex-grow flex-col gap-3"><div class="min-h-[20px] flex flex-col items-start gap-4 whitespace-pre-wrap break-words"><div class="markdown prose w-full break-words dark:prose-invert light">' . $content . '</div></div></div></div></div></div>';
}
if ( isset($_GET['preview']) || in_array('--preview',$argv ?? array(),true) ) {
	if ( isset($_GET['about']) ) { $linked_posts[99]->post_title = 'Giới thiệu'; }
	$az_child_fixture_thumbnail = !isset($_GET['noimage']);
	$az_child_fixture_locked = isset($_GET['locked']);
	$az_child_fixture_paginated = isset($_GET['pages']);
	if ( isset($_GET['legacy']) ) {
		$az_child_fixture_thumbnail = false;
		$linked_posts[99]->post_title = 'Giới thiệu';
		$linked_posts[99]->post_content = legacy_chat_content('<h2>SOVIBAN — quản lý bất động sản và cho thuê văn phòng</h2><p>Đây là nội dung được lưu cùng các lớp trình bày từ bố cục cũ. Mẫu trang trung tính cần hiển thị đoạn văn trên nền trắng, chữ dễ đọc và chiều rộng đồng nhất với khung bài viết.</p><p>Các <a href="#services">dịch vụ quản lý tòa nhà</a> và liên kết vẫn được giữ nguyên.</p>') . legacy_chat_content('<p>Ảnh trong nội dung được giữ nguyên vị trí do người biên tập lựa chọn.</p><p><img class="aligncenter" src="/wp-content/uploads/2023/05/IMG_7414-scaled.jpg" alt="Văn phòng" width="2560" height="1707"></p><p>Nội dung tiếp theo hiển thị với cùng kiểu chữ và khoảng cách đoạn văn.</p>') . '<div class="wp-block-group has-background" style="padding:20px;background:#fff3df;color:#553b21"><p>Khối WordPress có màu nền do người biên tập lựa chọn vẫn được giữ.</p></div>';
		// Emulate the observed Tailwind utilities without loading a new dependency.
		$legacy_preview_css = '<style>.group[class*="dark:bg-"]{background:#444654;color:#f3f4f6;border-bottom:1px solid #0002}.group div[class*="max-w-"]{display:flex;max-width:768px;margin:auto;padding:24px 0}.group div[class*="w-[calc("]{display:flex;width:calc(100% - 115px)}.group .whitespace-pre-wrap{white-space:pre-wrap}.group .markdown.prose{max-width:65ch;line-height:1.5}</style>';
	}
	echo str_replace( '</head>', ( $legacy_preview_css ?? '' ) . '</head>', render_neutral() ); exit;
}
$html=render_neutral();
check(false===strpos($html,'<h1>') && false===strpos($html,'az-neutral-page__heading') && false!==strpos($html,'Hệ thống quản lý tòa nhà thông minh'),'neutral template omits duplicate title while retaining the breadcrumb Page name');
check(false!==strpos($html,'class="az-neutral-page__image"') && false!==strpos($html,'<strong>quản lý tòa nhà</strong>') && 1===$content_calls,'featured media and rich content use normal WordPress render paths');
check(false!==strpos($html,'id="main"') && false!==strpos($html,'id="az-main-content"') && 1===substr_count($html,'<!DOCTYPE html>') && 1===substr_count($html,'</html>'),'shared header/footer and both skip-link targets are retained');
check(false!==strpos($html,'class="az-content az-neutral-page__content"') && false===strpos($html,'az-neutral-hero'),'neutral page reuses parent article typography without the old overlay hero');
check(array('az-content','az-page-style')===$styles['az-neutral-page'][1] && false!==strpos($html,'/az-theme/css/neutral-page.css') && false===strpos($html,'/az-theme-child/css/neutral-page.css'),'inherited stylesheet comes from parent and follows its content/page styles');
$other=render_neutral(100);
check(false!==strpos($other,'Nội dung riêng của trang chính sách.') && false===strpos($other,'Các đối tượng quản lý'),'same template uses each Page own content');
$az_child_fixture_thumbnail=false;$html=render_neutral();
check(false===strpos($html,'az-neutral-page__featured') && false!==strpos($html,'Các đối tượng quản lý'),'page without a featured image retains readable content');
$az_child_fixture_thumbnail=true;$az_child_fixture_locked=true;$az_child_fixture_comments=true;$html=render_neutral();
check(false!==strpos($html,'post-password-form') && false===strpos($html,'Các đối tượng quản lý') && false===strpos($html,'az-neutral-page__featured') && false===strpos($html,'fixture-comments'),'password page does not expose article body, featured image or comments');
$az_child_fixture_locked=false;$az_child_fixture_paginated=true;$html=render_neutral();
check(false!==strpos($html,'aria-label="Phân trang nội dung"') && false!==strpos($html,'fixture-comments'),'WordPress content pagination and enabled comments remain available');
$banner_meta[99]=array('layout'=>'center','text'=>'Banner trang');$html=render_neutral();
check(1===substr_count($html,'<h1>') && false!==strpos($html,'Banner trang') && false===strpos($html,'class="az-neutral-page__heading"'),'active parent banner owns the single H1');
$banner_meta[99]=array(); $az_child_fixture_paginated=false; $az_child_fixture_comments=false; $az_child_fixture_template='page-about.php';
$html=render_neutral();
check(false!==strpos($html,'<strong>quản lý tòa nhà</strong>') && false!==strpos($html,'/az-theme/css/neutral-page.css') && false===strpos($html,'wv-about-lead-form'),'About template inherits child header/footer and renders WordPress editor formatting with parent styles');
foreach(array('','front-page.php','page-contact.php') as $template){$az_child_fixture_template=$template;unset($styles['az-neutral-page']);az_neutral_page_assets();check(!isset($styles['az-neutral-page']),'neutral stylesheet does not load on template '.$template);}
echo "Neutral Page isolated regressions passed.\n";
