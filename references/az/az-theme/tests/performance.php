<?php
/** Run with php tests/performance.php; no database or WordPress bootstrap. */
define('ABSPATH', dirname(__DIR__, 4) . '/');
require ABSPATH . 'wp-includes/class-wp-dependency.php';
require ABSPATH . 'wp-includes/class-wp-dependencies.php';
$options=[]; $hooks=[]; $blog=false;
$styles=new WP_Dependencies(); $scripts=new WP_Dependencies();
function add_action(...$a){}
function add_filter(...$a){$GLOBALS['hooks'][]=$a;}
function remove_action(...$a){$GLOBALS['hooks'][]=$a;}
function remove_filter(...$a){}
function az_get_option($k,$d=null){return $GLOBALS['options'][$k]??$d;}
function wp_styles(){return $GLOBALS['styles'];}
function wp_scripts(){return $GLOBALS['scripts'];}
function is_user_logged_in(){return false;}
function is_singular($t){return $GLOBALS['blog'];}
function is_home(){return false;}
function is_category(){return false;}
function is_tag(){return false;}
function is_author(){return false;}
function is_date(){return false;}
function check($ok,$message){if(!$ok)throw new RuntimeException($message);echo "PASS $message\n";}
require dirname(__DIR__).'/inc/core/class-performance.php';
$p=new AZ_Performance();
$styles->add('az-ai-theme-style','/style.css');$styles->add('child','/child.css',['az-ai-theme-style']);$styles->enqueue(['child']);
$scripts->add('jquery-migrate','/migrate.js');$scripts->add('jquery','/jquery.js',['jquery-migrate']);$scripts->enqueue(['jquery']);
$options=['performance.disable_theme_style'=>1,'performance.disable_jquery_migrate'=>1,'performance.disable_emoji'=>1,'performance.disable_embeds'=>1];
$p->configure();$p->assets();$styles->all_deps($styles->queue);$scripts->all_deps($scripts->queue);
check($styles->registered['az-ai-theme-style']->src===false && in_array('child',$styles->to_do,true),'disabled parent CSS preserves child dependencies');
check(!in_array('jquery-migrate',$scripts->to_do,true),'jQuery Migrate absent from resolved dependency list');
check(in_array(['wp_enqueue_scripts','wp_enqueue_emoji_styles'],$hooks,true),'modern Emoji CSS hook removed');
check(in_array(['embed_oembed_discover','__return_false'],$hooks,true),'oEmbed discovery disabled');
$options=['performance.lazy_load_images'=>0];
check(!$p->lazy_loading(true,'img','content') && $p->lazy_loading(true,'iframe','content'),'image toggle does not disable iframe lazy loading');
$styles->add('dashicons','/icons.css');$styles->enqueue(['dashicons']);$options=['performance.disable_dashicons_guests'=>1];$blog=true;$p->assets();
check($styles->registered['dashicons']->src==='/icons.css','Blog icons retained for guests');
$blog=false;$p->assets();check($styles->registered['dashicons']->src===false,'Dashicons removed outside Blog');
