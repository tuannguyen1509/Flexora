<?php
/** Parent style 1 and child style 2 settings and rendering without a database. */
define( 'ABSPATH', __DIR__ );
$theme = dirname( __DIR__ ); $options = array(); $hooks = array(); $styles = array(); $scripts = array();
function add_action( $hook, $callback, $priority = 10, ...$args ) { $GLOBALS['hooks'][$hook][$priority][] = $callback; }
function remove_action( $hook, $callback, $priority = 10 ) { $GLOBALS['hooks'][$hook][$priority] = array_values(array_filter($GLOBALS['hooks'][$hook][$priority] ?? array(), function($v) use($callback){return $v !== $callback;})); }
function add_filter( ...$args ) {}
function add_shortcode( ...$args ) {}
function is_admin() { return true; }
function get_option( $key, $fallback = '' ) { return $GLOBALS['options'][$key] ?? $fallback; }
function sanitize_key( $value ) { return strtolower(preg_replace('/[^a-z0-9_\-]/i', '', $value)); }
function sanitize_html_class( $value ) { return $value; }
function selected( $value, $expected, $echo = false ) { return (string) $value === (string) $expected ? 'selected="selected"' : ''; }
function sanitize_text_field( $value ) { return trim(strip_tags((string)$value)); }
function esc_attr( $value ) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function esc_html( $value ) { return esc_attr($value); }
function esc_url_raw( $value, $protocols = array() ) { return preg_match('#^https?://#i',(string)$value) || ( ! $protocols && strpos((string)$value,'tel:') === 0 ) ? $value : ''; }
function esc_url( $value, $protocols = array() ) { return esc_attr(esc_url_raw($value,$protocols)); }
function get_stylesheet_directory() { return $GLOBALS['theme']; }
function get_stylesheet_directory_uri() { return 'https://example.test/child'; }
function get_template_directory() { return dirname($GLOBALS['theme']).'/az-theme'; }
function get_template_directory_uri() { return 'https://example.test/parent'; }
function wp_enqueue_style( $handle, $src, $dependencies, $version ) { $GLOBALS['styles'][$handle] = array($src,$dependencies); }
function wp_enqueue_script( $handle, $src, $dependencies, $version, $footer ) { $GLOBALS['scripts'][$handle] = $src; }
require get_template_directory().'/inc/options/class-registry.php';
require get_template_directory().'/inc/options/class-sanitizer.php';
require get_template_directory().'/inc/options/class-renderer.php';
require get_template_directory().'/inc/helpers/options.php';
require $theme.'/inc/steel-storefront.php';
require $theme.'/inc/floating-contact.php';
function check( $value, $message ) { if(!$value){throw new RuntimeException($message);} echo "PASS $message\n"; }
function capture( $callback ) { ob_start(); $callback(); return ob_get_clean(); }
AZ_Options_Registry::add_section('social','Social',require get_template_directory().'/inc/admin/sections/social.php');
az_child_register_social_contact_style(); az_child_register_social_contact_style();
$fields=AZ_Options_Registry::get('social')['fields']; $field=array_values(array_filter($fields,function($v){return $v['id']==='contact_style';}))[0];
check(array_keys($field['options'])===array('icons','social_expand') && count(array_filter($fields,function($v){return $v['id']==='contact_style';}))===1,'only styles 1 and 2 register without duplicating the select');
check(AZ_Options_Sanitizer::sanitize('social_expand','select',$field)==='social_expand','parent sanitizer accepts and preserves style 2');
check($field['default']==='icons' && AZ_Options_Sanitizer::sanitize('invalid','select',$field)==='icons','default/reset and invalid styles use style 1');
check($hooks['wp_footer'][5]===array('az_child_render_contact') && $hooks['wp_enqueue_scripts'][35]===array('az_child_contact_assets'),'only one renderer and asset callback are registered');
$social=array('contact_enabled'=>1,'contact_style'=>'social_expand','contact_phone'=>'0933 003 911');
foreach(array_keys(az_child_steel_social_icons()) as $network){$social[$network]='https://example.test/'.$network.'?a=1&b=2';}
$options=array('az_theme_options'=>array('social'=>$social));
$items=az_child_social_contact_items();
check(count($items)===11,'all ten networks and the optional hotline are available');
$html=capture('az_child_render_contact');
check(substr_count($html,'class="az-contact__link ')===11 && substr_count($html,'<aside ')===1,'all configured channels render in one widget');
check(strpos($html,'aria-expanded="false"')!==false && strpos($html,'aria-label="Các kênh liên hệ" hidden')!==false,'panel starts closed above its labelled toggle');
check(substr_count($html,'rel="noopener noreferrer"')===10 && strpos($html,'href="tel:0933003911"')!==false,'network destinations open safely and hotline uses a telephone link');
check(strpos($html,'&amp;amp;')===false && strpos($html,'?a=1&amp;b=2')!==false,'query parameters are escaped once');
az_child_contact_assets();
check(isset($styles['az-child-social-contact']) && $styles['az-child-social-contact'][1]===array('az-floating-contact'),'child stylesheet follows the shared base');
check(isset($scripts['az-child-social-contact']) && !isset($scripts['az-floating-contact']),'only the new interaction script is loaded for style 2');
foreach(array('buttons','expand','icons','cards','dock','invalid') as $style){
 $options['az_theme_options']['social']['contact_style']=$style;
 $html=capture('az_child_render_contact');
 check(strpos($html,'az-contact--icons')!==false && capture('az_render_contact')===$html,"style $style uses the style 1 renderer");
 check(strpos($html,'href="tel:0933003911"')!==false && strpos($html,'https://example.test/zalo')!==false && strpos($html,'https://example.test/messenger')!==false && substr_count($html,'class="az-contact__link ')===3 && strpos($html,'az-contact__toggle')===false,"style $style retains Messenger/Zalo/phone without retired layout controls");
 check(strpos($html,'&amp;amp;')===false && substr_count($html,'?a=1&amp;b=2')===2,"style $style escapes Messenger and Zalo query parameters once");
 check(AZ_Options_Sanitizer::sanitize($style,'select',$field)==='icons',"save normalizes $style to style 1");
 $admin=capture(function() use($field,$style){AZ_Options_Renderer::field('social',$field,array('social'=>array('contact_style'=>$style)));});
 check(strpos($admin,'value="icons" selected="selected"')!==false,"admin reflects style 1 for $style");
 $styles=$scripts=array(); az_child_contact_assets();
 check(isset($styles['az-floating-contact']) && !$scripts && !isset($styles['az-child-social-contact']),"style $style loads icon CSS without retired scripts");
}
unset($options['az_theme_options']['social']['contact_style']);
check(strpos(capture('az_child_render_contact'),'az-contact--icons')!==false,'missing setting also uses style 1');
$options=array('az_theme_options'=>array('social'=>array('contact_enabled'=>1,'contact_style'=>'icons','messenger'=>'https://m.me/example?ref=site&source=contact')));
$html=capture('az_render_contact');
check(substr_count($html,'class="az-contact__link ')===1 && strpos($html,'az-contact__messenger')!==false && capture('az_child_render_contact')===$html,'Messenger alone renders through both parent and child style 1');
check(strpos($html,'href="https://m.me/example?ref=site&amp;source=contact"')!==false && strpos($html,'target="_blank" rel="noopener noreferrer"')!==false && strpos($html,'aria-label="Nhắn tin Facebook Messenger (mở tab mới)"')!==false,'Messenger uses its configured chat URL with a safe new tab and accessible label');
check(strpos($html,az_contact_icon('messenger'))!==false && strpos(az_contact_icon('messenger'),'fill="currentColor"')!==false,'Messenger renders the shared lightning icon');
$styles=$scripts=array(); az_child_contact_assets();
check(isset($styles['az-floating-contact']) && !$scripts && !isset($styles['az-child-social-contact']),'Messenger-only style 1 loads the parent CSS without interaction scripts');
$options['az_theme_options']['social']['contact_phone']='0933 003 911';
$options['az_theme_options']['social']['zalo']='https://zalo.me/0933003911';
$html=capture('az_render_contact');
check(strpos($html,'az-contact__messenger')<strpos($html,'az-contact__zalo') && strpos($html,'az-contact__zalo')<strpos($html,'az-contact__phone'),'style 1 orders Messenger above Zalo and phone');
foreach(array('','javascript:alert(1)') as $url){
 $options['az_theme_options']['social']['messenger']=$url;
 $html=capture('az_render_contact');
 check(strpos($html,'az-contact__messenger')===false && substr_count($html,'class="az-contact__link ')===2,'empty or unsafe Messenger URL preserves the existing Zalo and phone buttons');
}
$options['az_theme_options']['social']=array('contact_enabled'=>1,'contact_style'=>'icons','facebook'=>'https://www.facebook.com/example');
check(capture('az_render_contact')==='','style 1 does not confuse a Facebook page URL with its Messenger chat URL');
$options['az_contact_messenger_url']='https://m.me/legacy';
check(strpos(capture('az_render_contact'),'href="https://m.me/legacy"')!==false,'style 1 supports a saved legacy Messenger URL');
$options['az_theme_options']['social']['messenger']='';
check(capture('az_render_contact')==='','explicitly cleared Messenger never restores the legacy chat URL');
$options['az_theme_options']['social']['messenger']='https://m.me/example';
$options['az_theme_options']['social']['contact_enabled']=0;
$styles=$scripts=array(); az_child_contact_assets();
check(capture('az_render_contact')==='' && !$styles && !$scripts,'disabled master switch hides Messenger and its assets in style 1');
unset($options['az_contact_messenger_url']);
$options['az_theme_options']['social']=array('contact_enabled'=>1,'contact_style'=>'social_expand','facebook'=>'https://www.facebook.com/example');
check(count(az_child_social_contact_items())===1 && strpos(capture('az_child_render_contact'),'az-contact-social__facebook')!==false,'Facebook alone renders without requiring phone or Zalo');
$options['az_theme_options']['social']['contact_phone']='not-a-phone';
check(count(az_child_social_contact_items())===1,'invalid telephone numbers are omitted');
$options['az_theme_options']['social']['facebook']='javascript:alert(1)';
check(capture('az_child_render_contact')==='', 'empty and unsafe destinations produce no launcher');
$styles=$scripts=array(); az_child_contact_assets();
check(!$styles && !$scripts,'empty widget does not load its assets');
$options['az_theme_options']['social']=array('contact_enabled'=>0,'contact_style'=>'social_expand','facebook'=>'https://www.facebook.com/example');
check(capture('az_child_render_contact')==='', 'disabled master switch hides style 2');
$options['az_theme_options']['social']=array('contact_enabled'=>1,'contact_style'=>'social_expand');
$options['az_footer_social_facebook']='https://www.facebook.com/legacy';
check(count(az_child_social_contact_items())===1,'legacy Social URL remains available through parent helper');
$options['az_theme_options']['social']['facebook']='';
check(az_child_social_contact_items()===array(),'explicitly cleared Social URL never resurrects its legacy value');
