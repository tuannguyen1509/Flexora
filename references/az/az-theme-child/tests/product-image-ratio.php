<?php
/** Isolated settings/save/gallery regressions without a WordPress database. */
define( 'ABSPATH', __DIR__ );
$theme = dirname( __DIR__ );
$options = array(); $product_page = true; $shop_page = false; $taxonomy_page = false; $styles = array(); $inline_styles = array(); $image_calls = array();
function add_action( ...$args ) {}
function add_filter( ...$args ) {}
function is_admin() { return true; }
function is_product() { return $GLOBALS['product_page']; }
function is_shop() { return $GLOBALS['shop_page']; }
function is_product_taxonomy() { return $GLOBALS['taxonomy_page']; }
function az_get_option( $key, $fallback = null ) { return $GLOBALS['options'][substr($key, strlen('woocommerce.'))] ?? $fallback; }
function sanitize_key( $value ) { return $value; }
function sanitize_text_field( $value ) { return trim(strip_tags((string)$value)); }
function sanitize_html_class( $value ) { return $value; }
function esc_attr( $value ) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function esc_html( $value ) { return esc_attr($value); }
function esc_url( $value ) { return esc_attr($value); }
function selected( $a, $b, $echo = false ) { return $a === $b ? 'selected' : ''; }
function wp_style_is( ...$args ) { return true; }
function get_stylesheet_directory() { return $GLOBALS['theme']; }
function get_stylesheet_directory_uri() { return '/theme'; }
function wp_enqueue_style( $handle, $src, $deps, $version ) { $GLOBALS['styles'][$handle] = compact('src','deps','version'); }
function wp_add_inline_style( $handle, $css ) { $GLOBALS['inline_styles'][$handle] = $css; }
function get_the_title() { return 'Pin Lưu Trữ C&I Flex16–HBox–7 Lithium Valley'; }
function get_post_meta( ...$args ) { return ''; }
function wp_get_attachment_image_url( $id, $size ) { return 1 === $id ? '/product-source.png' : '/theme/assets/images/solar-home.svg'; }
function wp_get_attachment_image_srcset( ...$args ) { return ''; }
function wp_get_attachment_image_sizes( ...$args ) { return ''; }
function wp_get_attachment_image( $id, $size, $icon = false, $attrs = array() ) {
    $GLOBALS['image_calls'][] = compact('id','size','attrs');
    $html = '<img src="'.esc_url(wp_get_attachment_image_url($id,$size)).'" width="1510" height="1512" alt="'.esc_attr(get_the_title()).'"';
    foreach ($attrs as $key=>$value) { $html .= ' '.esc_attr($key).'="'.esc_attr($value).'"'; }
    return $html.'>';
}
function wc_placeholder_img_src( ...$args ) { return '/theme/assets/images/solar-home.svg'; }
require dirname($theme).'/az-theme/inc/options/class-registry.php';
require dirname($theme).'/az-theme/inc/options/class-sanitizer.php';
require dirname($theme).'/az-theme/inc/options/class-renderer.php';
require $theme.'/inc/product-display.php';
AZ_Options_Registry::add_section('woocommerce','WooCommerce', require dirname($theme).'/az-theme/inc/admin/sections/woocommerce.php');
az_child_register_product_image_fields(); az_child_register_product_image_fields();
$fields = AZ_Options_Registry::get('woocommerce')['fields'];
$image_fields = array_values(array_filter($fields, function($field){ return 'product_images' === ($field['group'] ?? ''); }));
function check( $ok, $message ) { if (!$ok) { throw new RuntimeException($message); } echo "PASS $message\n"; }
function gallery_html() {
    $az_woo_show_gallery = true;
    $product = new class { function get_image_id(){return 1;} function get_gallery_image_ids(){return array(2);} };
    $source = file_get_contents($GLOBALS['theme'].'/single-product.php');
    $start = strpos($source,'<?php if ( $az_woo_show_gallery ) : ?>');
    $end = strpos($source,'<div class="az-product-info">',$start);
    ob_start(); eval('?>'.substr($source,$start,$end-$start)); return ob_get_clean();
}
if ( '--preview' === ($argv[1] ?? '') ) {
    $options = array('gallery_image_ratio'=>$argv[2] ?? 'square','gallery_ratio_width'=>5,'gallery_ratio_height'=>3);
    echo '<!doctype html><html lang="vi"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Tỷ lệ ảnh sản phẩm</title><link rel="stylesheet" href="/parent/css/woocommerce.css"><link rel="stylesheet" href="/theme/css/product-gallery.css"><link rel="stylesheet" href="/parent/assets/admin/css/az-admin.css"><link rel="stylesheet" href="/parent/assets/admin/css/az-advanced-fields.css"><style>body{margin:0;font:15px/1.6 Arial,sans-serif;background:#f4f6f8;color:#263445}*{box-sizing:border-box}.preview-wrap{max-width:1180px;margin:auto;padding:28px 24px}.preview-note{margin:0 0 24px;color:#607184}.az-product-info h1{font-size:30px;line-height:1.25}.az-product-info p{color:#607184}.az-theme-admin{margin:36px 0 0}.az-settings-card{margin:0}h2{font-size:18px}</style><body class="single-product"><div id="page" class="az-site"><main class="preview-wrap"><p class="preview-note">Bản xem trước · Ảnh chính hiển thị toàn bộ · Tỷ lệ '.esc_html(az_child_product_image_ratio()).'</p><section class="az-product-summary">';
    echo gallery_html();
    echo '<div class="az-product-info"><h1>'.esc_html(get_the_title()).'</h1><p>Chọn hình vuông, chữ nhật ngang hoặc tỷ lệ ảnh gốc trong AZ Theme Advanced → WooCommerce.</p><p>Ảnh được giữ nguyên tỷ lệ và hiển thị đầy đủ trong khung.</p></div></section><div class="az-theme-admin"><section class="az-settings-card"><header class="az-settings-card__header"><h2>Kích thước ảnh chi tiết sản phẩm</h2></header><div class="az-settings-grid">';
    foreach ($image_fields as $field) { AZ_Options_Renderer::field('woocommerce',$field,array('woocommerce'=>$options)); }
    echo '</div></section></div></main></div>';
    preg_match('/<script>[\s\S]*?<\/script>/',$source = file_get_contents($theme.'/single-product.php'),$script);
    echo $script[0].'</body></html>'; exit;
}
check(3 === count($image_fields), 'image fields register once and preserve existing WooCommerce fields');
foreach ($image_fields as $field) {
    ob_start(); AZ_Options_Renderer::field('woocommerce',$field,array()); $html=ob_get_clean();
    check(strpos($html,'name="az_theme_options[woocommerce]['.$field['id'].']"') !== false,'admin renderer exposes '.$field['id']);
}
foreach (array('square'=>'1 / 1','landscape'=>'4 / 3','wide'=>'3 / 2','widescreen'=>'16 / 9','original'=>'auto') as $mode=>$ratio) {
    $options['gallery_image_ratio']=AZ_Options_Sanitizer::sanitize($mode,'select',$image_fields[0]);
    check($ratio === az_child_product_image_ratio(),'saved mode '.$mode.' reaches gallery ratio');
    check(strpos(gallery_html(),'--az-product-image-ratio: '.$ratio) !== false,'template applies '.$mode);
}
$options=array(); check('1 / 1' === az_child_product_image_ratio(),'unsaved settings default to square');
$options=array('gallery_image_ratio'=>'custom','gallery_ratio_width'=>5,'gallery_ratio_height'=>3);
check('5 / 3' === az_child_product_image_ratio(),'custom width and height reach rendering');
$options['gallery_ratio_width']=AZ_Options_Sanitizer::sanitize(-4,'number',$image_fields[1]);
$options['gallery_ratio_height']=AZ_Options_Sanitizer::sanitize(999,'number',$image_fields[2]);
check('1 / 100' === az_child_product_image_ratio(),'custom dimensions are bounded and never divide by zero');
$options=array('gallery_image_ratio'=>'custom','gallery_ratio_width'=>'bad','gallery_ratio_height'=>array());
check('4 / 3' === az_child_product_image_ratio(),'malformed custom dimensions use safe defaults');
foreach (array('bad',array(),'square; background:red') as $mode) {
    $options=array('gallery_image_ratio'=>$mode); check('1 / 1' === az_child_product_image_ratio(),'invalid mode cannot inject CSS');
}
$options=array('gallery_image_ratio'=>'original');
check(strpos(gallery_html(),'az-product-main-frame--original') !== false,'original images use intrinsic height');
az_child_product_image_assets();
check(in_array('az-woocommerce',$styles['az-child-product-gallery']['deps'],true),'gallery override loads after parent WooCommerce CSS');
check(in_array('az-product-card',$styles['az-child-product-gallery']['deps'],true),'catalogue override loads after shared card CSS');
$styles=array(); $product_page=false; az_child_product_image_assets();
check(!$styles,'image stylesheet does not load on unrelated pages');
$image_product = new class { function get_image_id(){ return 1; } };
check('existing thumbnail' === az_child_catalogue_product_image('existing thumbnail',$image_product,'woocommerce_thumbnail',array()),'unrelated pages keep existing image markup');
foreach (array('shop','category','product (related and viewed)') as $page) {
    $shop_page='shop' === $page; $taxonomy_page='category' === $page; $product_page='product (related and viewed)' === $page;
    foreach (array('square'=>'1 / 1','landscape'=>'4 / 3','wide'=>'3 / 2','widescreen'=>'16 / 9','custom'=>'5 / 3','original'=>'auto') as $mode=>$ratio) {
        $options=array('gallery_image_ratio'=>$mode,'gallery_ratio_width'=>5,'gallery_ratio_height'=>3);
        $styles=array(); $inline_styles=array(); az_child_product_image_assets();
        check(isset($styles['az-child-product-gallery']) && strpos($inline_styles['az-child-product-gallery'],'--az-product-image-ratio:'.$ratio.';') !== false,$page.' receives saved ratio '.$mode);
        if ('original' === $mode) { check(strpos($inline_styles['az-child-product-gallery'],'--az-product-card-image-height:auto;') !== false,$page.' original images use natural height'); }
    }
    $image_calls=array(); $attrs=array('alt'=>'Tên ảnh','loading'=>'lazy','decoding'=>'async');
    az_child_catalogue_product_image('cropped thumbnail',$image_product,'woocommerce_thumbnail',$attrs);
    check('full' === $image_calls[0]['size'] && $attrs === $image_calls[0]['attrs'],$page.' uses the complete artwork and preserves image attributes');
    check('small image' === az_child_catalogue_product_image('small image',$image_product,'thumbnail',$attrs),$page.' keeps non-card image sizes');
    $placeholder_product=new class { function get_image_id(){return 0;} };
    check('placeholder' === az_child_catalogue_product_image('placeholder',$placeholder_product,'woocommerce_thumbnail',$attrs),$page.' keeps products without an image');
}
