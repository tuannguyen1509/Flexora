<?php
/** Real WordPress hooks, permalink generation and request parsing; no database. */
error_reporting( E_ALL );
set_error_handler( static function( $severity, $message, $file, $line ) { throw new ErrorException( $message, 0, $severity, $file, $line ); } );
$root = dirname( __DIR__, 4 );
define( 'ABSPATH', $root . '/' );
require $root . '/wp-includes/plugin.php';
require $root . '/wp-includes/class-wp-rewrite.php';
require $root . '/wp-includes/class-wp.php';
require $root . '/wp-includes/class-wp-post.php';
require $root . '/wp-includes/class-wp-matchesmapregex.php';
require $root . '/wp-includes/link-template.php';
require $root . '/wp-includes/rewrite.php';
$options = array( 'home' => 'https://example.test/site', 'permalink_structure' => '/%postname%/', 'default_product_cat' => 5 );
$terms = array(); $posts = array(); $pages = array(); $assigned = array(); $context = array(); $tax_enabled = true;
$flushes = array(); $updates = array();
function get_option( $key, $fallback = false ) { return $GLOBALS['options'][$key] ?? $fallback; }
function update_option( $key, $value, $autoload = null ) { $GLOBALS['options'][$key] = $value; $GLOBALS['updates'][] = $key; }
function delete_option( $key ) { unset( $GLOBALS['options'][$key] ); }
function taxonomy_exists( $taxonomy ) { return $GLOBALS['tax_enabled'] && 'product_cat' === $taxonomy; }
function post_type_exists( $type ) { return 'product' === $type; }
function is_wp_error( $value ) { return $value instanceof Exception; }
function get_term( $id, $taxonomy ) { if ( is_object( $id ) ) { return $id; } return $GLOBALS['terms'][$id] ?? null; }
function get_terms( $args ) { return array_values( $GLOBALS['terms'] ); }
function get_ancestors( $id, $taxonomy, $type = '' ) {
    $ids = array(); $term = get_term( $id, $taxonomy );
    while ( $term && $term->parent && ! in_array( $term->parent, $ids, true ) ) { $ids[] = $term->parent; $term = get_term( $term->parent, $taxonomy ); }
    return $ids;
}
function get_the_terms( $id, $taxonomy ) { return array_map( static function( $id ) { return get_term( $id, 'product_cat' ); }, $GLOBALS['assigned'][$id] ?? array() ); }
function rest_get_url_prefix() { return 'wp-json'; }
function wc_get_page_id( $name ) { return array( 'shop' => 100, 'cart' => 101, 'checkout' => 102, 'myaccount' => 103 )[$name] ?? -1; }
function get_page_uri( $id ) { return $GLOBALS['posts'][$id]->post_name ?? ''; }
function get_page_by_path( $path ) { return $GLOBALS['pages'][$path] ?? null; }
function get_post( $post = null ) { return is_object( $post ) ? $post : ( $GLOBALS['posts'][$post] ?? null ); }
function get_post_status( $post ) { return get_post( $post )->post_status; }
function get_post_type( $post ) { return get_post( $post )->post_type; }
function get_post_status_object( $status ) { return (object) array( 'public' => 'publish' === $status, 'private' => 'private' === $status, 'protected' => in_array( $status, array( 'draft', 'pending', 'future' ), true ), 'exclude_from_search' => false ); }
function is_post_status_viewable( $status ) { return $status->public; }
function current_user_can( ...$args ) { return false; }
function get_post_type_object( $type ) { return (object) array( 'name' => $type, 'hierarchical' => false, 'query_var' => $type, 'publicly_queryable' => true ); }
function get_post_types( $args = array(), $output = 'names' ) { return 'objects' === $output ? array( 'product' => get_post_type_object( 'product' ), 'post' => get_post_type_object( 'post' ), 'page' => get_post_type_object( 'page' ) ) : array( 'product', 'post', 'page' ); }
function is_post_type_viewable( $type ) { return true; }
function get_taxonomies( $args = array(), $output = 'names' ) { return isset( $args['publicly_queryable'] ) && ! $args['publicly_queryable'] ? array() : array( 'product_cat' => (object) array( 'query_var' => 'product_cat' ) ); }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function wp_parse_str( $query, &$args ) { parse_str( $query, $args ); }
function trailingslashit( $text ) { return rtrim( $text, '/\\' ) . '/'; }
function untrailingslashit( $text ) { return rtrim( $text, '/\\' ); }
function wp_unslash( $text ) { return stripslashes( $text ); }
function is_multisite() { return false; }
function is_ssl() { return true; }
function absint( $value ) { return abs( (int) $value ); }
function add_query_arg( $args, $url ) { return $url . ( strpos( $url, '?' ) === false ? '?' : '&' ) . http_build_query( $args ); }
function is_admin() { return ! empty( $GLOBALS['context']['admin'] ); }
function wp_doing_ajax() { return ! empty( $GLOBALS['context']['ajax'] ); }
function is_preview() { return ! empty( $GLOBALS['context']['preview'] ); }
function is_404() { return ! empty( $GLOBALS['context']['404'] ); }
function is_singular( $type ) { return ( $GLOBALS['context']['type'] ?? '' ) === $type; }
function is_tax( $taxonomy ) { return ( $GLOBALS['context']['taxonomy'] ?? '' ) === $taxonomy; }
function is_feed() { return ! empty( $GLOBALS['context']['feed'] ); }
function is_embed() { return ! empty( $GLOBALS['context']['embed'] ); }
function is_trackback() { return ! empty( $GLOBALS['context']['trackback'] ); }
function get_queried_object_id() { return $GLOBALS['context']['id'] ?? 0; }
function get_queried_object() { return get_term( get_queried_object_id(), 'product_cat' ); }
function get_query_var( $key, $fallback = '' ) { return $GLOBALS['context']['vars'][$key] ?? $fallback; }
function get_term_link( $term, $taxonomy = '' ) { $term = get_term( $term, $taxonomy ); return apply_filters( 'term_link', home_url( '/danh-muc-san-pham/' . az_child_product_category_path( $term ) . '/' ), $term, $term->taxonomy ); }
function get_default_feed() { return 'rss2'; }
function get_post_embed_url( $id ) { return trailingslashit( get_permalink( $id ) ) . 'embed/'; }
function apply_flush() { $GLOBALS['flushes'][] = false; $GLOBALS['options']['rewrite_rules'] = apply_filters( 'rewrite_rules_array', legacy_rules() ); }
// Stub persistence only; production refresh calls the real core flush function.
class Test_Product_Rewrite extends WP_Rewrite { function flush_rules( $hard = true ) { if ( $hard ) { throw new RuntimeException('Must never hard flush'); } apply_flush(); } }
function legacy_rules() { return array( '^danh-muc-san-pham/(.+?)/?$' => 'index.php?product_cat=$matches[1]', '^san-pham/([^/]+)/?$' => 'index.php?product=$matches[1]', '(.?.+?)/?$' => 'index.php?pagename=$matches[1]' ); }
function check( $ok, $message ) { if ( ! $ok ) { throw new RuntimeException( $message ); } echo "PASS $message\n"; }
function term_fixture( $id, $slug, $parent = 0 ) { return (object) array( 'term_id' => $id, 'taxonomy' => 'product_cat', 'slug' => $slug, 'parent' => $parent ); }
function post_fixture( $id, $slug, $type = 'product', $status = 'publish' ) { return new WP_Post( (object) array( 'ID' => $id, 'post_name' => $slug, 'post_type' => $type, 'post_status' => $status, 'post_date' => '2026-01-01 12:00:00' ) ); }
$terms = array( 1 => term_fixture( 1, 'giai-phap-nlmt' ), 2 => term_fixture( 2, 'pin-luu-tru', 1 ), 3 => term_fixture( 3, 'lithium', 2 ), 4 => term_fixture( 4, 'may-loc-nuoc' ), 5 => term_fixture( 5, 'chua-phan-loai' ), 6 => term_fixture( 6, 'gio-hang' ), 7 => term_fixture( 7, 'gioi-thieu' ), 8 => term_fixture( 8, 'wp-json' ) );
$posts = array( 10 => post_fixture( 10, 'pin-flex16' ), 11 => post_fixture( 11, 'bai-viet', 'post' ), 12 => post_fixture( 12, 'ban-nhap', 'product', 'draft' ), 13 => post_fixture( 13, 'san-pham-moi' ), 14 => post_fixture( 14, 'san-pham-trung-page' ), 15 => post_fixture( 15, 'sp-danh-muc-trung' ) );
foreach ( array( 'cua-hang', 'gio-hang', 'thanh-toan', 'tai-khoan' ) as $i => $slug ) { $posts[100+$i] = post_fixture( 100+$i, $slug, 'page' ); $pages[$slug] = $posts[100+$i]; }
$pages['gioi-thieu'] = post_fixture( 104, 'gioi-thieu', 'page' );
$assigned = array( 10 => array( 1, 4, 2 ), 12 => array( 2 ), 14 => array( 2 ), 15 => array( 7 ) );
$wp_rewrite = new Test_Product_Rewrite();
$wp_rewrite->extra_permastructs['product'] = array( 'struct' => '/san-pham/%product%' );
require dirname( __DIR__ ) . '/inc/product-permalinks.php';
check( get_term_link( $terms[1] ) === 'https://example.test/site/giai-phap-nlmt/', 'root category drops the old base' );
check( get_term_link( $terms[2] ) === 'https://example.test/site/giai-phap-nlmt/pin-luu-tru/', 'child category keeps its parent path' );
check( get_term_link( $terms[3] ) === 'https://example.test/site/giai-phap-nlmt/pin-luu-tru/lithium/', 'deeper categories keep the full tree' );
check( get_permalink( 10 ) === 'https://example.test/site/giai-phap-nlmt/pin-luu-tru/pin-flex16/', 'real WordPress permalink API uses the deepest assigned category' );
check( get_post_permalink( 10, true ) === 'https://example.test/site/giai-phap-nlmt/pin-luu-tru/%product%/', 'editor permalink keeps the product placeholder' );
check( get_permalink( 13 ) === 'https://example.test/site/chua-phan-loai/san-pham-moi/', 'uncategorized products use the configured default category' );
check( az_child_product_permalink( 'original', $posts[11], false, false ) === 'original', 'blog post URLs remain unchanged' );
check( az_child_product_category_link( 'news URL', (object) array('taxonomy'=>'category'), 'category' ) === 'news URL', 'news categories remain unchanged' );
check( az_child_product_permalink( 'draft URL', $posts[12], false, false ) === 'draft URL', 'draft URLs and preview permissions remain unchanged' );
$sample = clone $posts[12]; $sample->filter = 'sample';
check( az_child_product_permalink( 'sample URL', $sample, true, false ) === 'https://example.test/site/giai-phap-nlmt/pin-luu-tru/%product%/', 'draft editor sample shows the future pretty URL' );
$primary = static function( $term ) use ( $terms ) { return $terms[4]; };
add_filter( 'wc_product_post_type_link_product_cat', $primary );
check( get_permalink( 10 ) === 'https://example.test/site/may-loc-nuoc/pin-flex16/', 'WooCommerce primary-category integration controls the canonical product path' );
remove_filter( 'wc_product_post_type_link_product_cat', $primary );
$stale = static function( $term ) use ( $terms ) { return $terms[3]; };
add_filter( 'wc_product_post_type_link_product_cat', $stale );
check( get_permalink( 10 ) === 'https://example.test/site/giai-phap-nlmt/pin-luu-tru/pin-flex16/', 'stale unassigned primary category is rejected' );
remove_filter( 'wc_product_post_type_link_product_cat', $stale );
az_child_product_permalink_refresh(); az_child_product_permalink_refresh();
check( count( $flushes ) === 1 && $flushes[0] === false, 'rules refresh once without writing server configuration' );
check( $options['permalink_structure'] === '/%postname%/' && ! in_array('woocommerce_permalinks',$updates,true), 'global and WooCommerce permalink settings are not overwritten' );
function route( $path ) {
    global $wp;
    $_GET = array(); $_POST = array(); $_SERVER['REQUEST_URI'] = '/site/' . $path; $_SERVER['PHP_SELF'] = '/site/index.php';
    $wp = new WP(); $wp->public_query_vars[] = 'product'; $wp->public_query_vars[] = 'product_cat';
    $wp->parse_request(); return $wp->query_vars;
}
check( route('giai-phap-nlmt/')['product_cat'] === 'giai-phap-nlmt', 'WordPress parser resolves root category' );
check( route('giai-phap-nlmt/pin-luu-tru/')['product_cat'] === 'giai-phap-nlmt/pin-luu-tru', 'subcategory wins over the parent product wildcard' );
check( route('giai-phap-nlmt/pin-luu-tru/lithium/')['product_cat'] === 'giai-phap-nlmt/pin-luu-tru/lithium', 'parser resolves a third-level category' );
$vars = route('giai-phap-nlmt/pin-luu-tru/pin-flex16/');
check( $vars['post_type'] === 'product' && $vars['name'] === 'pin-flex16' && !isset($vars['product_cat']), 'product request resolves through the native product query, without a conflicting taxonomy constraint' );
$vars = route('giai-phap-nlmt/pin-luu-tru/page/2/');
check( $vars['product_cat'] === 'giai-phap-nlmt/pin-luu-tru' && $vars['paged'] === '2', 'category pagination reaches the correct archive' );
$vars = route('giai-phap-nlmt/pin-luu-tru/feed/rss2/');
check( $vars['product_cat'] === 'giai-phap-nlmt/pin-luu-tru' && $vars['feed'] === 'rss2', 'category feed still works' );
foreach ( array( 'embed/' => array('embed','true'), '2/' => array('page','2'), 'comment-page-3/' => array('cpage','3'), 'feed/atom/' => array('feed','atom'), 'trackback/' => array('tb','1') ) as $endpoint => $expect ) {
    $vars = route('giai-phap-nlmt/pin-luu-tru/pin-flex16/'.$endpoint);
    check( $vars['name'] === 'pin-flex16' && $vars[$expect[0]] === $expect[1], 'product endpoint '.$endpoint.' is preserved' );
}
check( route('danh-muc-san-pham/giai-phap-nlmt/pin-luu-tru/')['product_cat'] === 'giai-phap-nlmt/pin-luu-tru', 'old category rules remain routable for redirects' );
check( route('san-pham/pin-flex16/')['name'] === 'pin-flex16', 'old product rules remain routable for redirects' );
foreach ( array('cua-hang','gio-hang','thanh-toan','tai-khoan','gioi-thieu') as $slug ) { check( route($slug.'/')['pagename'] === $slug, 'existing Page '.$slug.' keeps its route' ); }
check( get_term_link($terms[6]) === 'https://example.test/site/danh-muc-san-pham/gio-hang/', 'category colliding with cart keeps its old URL' );
check( get_term_link($terms[7]) === 'https://example.test/site/danh-muc-san-pham/gioi-thieu/', 'category colliding with a published Page keeps its old URL' );
check( get_term_link($terms[8]) === 'https://example.test/site/danh-muc-san-pham/wp-json/', 'REST prefix is protected' );
$pages['giai-phap-nlmt/pin-luu-tru/san-pham-trung-page'] = post_fixture(105,'san-pham-trung-page','page');
check( get_permalink(14) === 'https://example.test/site/san-pham/san-pham-trung-page/', 'product colliding with a Page keeps its existing permalink' );
check( route('giai-phap-nlmt/pin-luu-tru/san-pham-trung-page/')['page_id'] === 105, 'Page created after rule generation takes precedence over a product rule' );
check( get_permalink(15) === 'https://example.test/site/san-pham/sp-danh-muc-trung/', 'products under a reserved category keep routable existing links' );
$posts[16] = post_fixture(16,'pin-luu-tru'); $assigned[16] = array(1);
check( get_permalink(16) === 'https://example.test/site/san-pham/pin-luu-tru/', 'product sharing a child-category slug keeps its unambiguous existing URL' );
$feed_term = term_fixture(17,'feed');
check( az_child_product_category_link('old feed category URL',$feed_term,'product_cat') === 'old feed category URL', 'global feed prefix remains reserved' );
check( (route('khong-ton-tai/foo/')['error'] ?? '') === '404', 'unknown category prefix remains a 404 instead of becoming a product catch-all' );
$terms[2]->slug = 'pin-dien'; do_action('edited_product_cat',2); az_child_product_permalink_refresh();
check( count($flushes)===2 && route('giai-phap-nlmt/pin-dien/')['product_cat']==='giai-phap-nlmt/pin-dien', 'editing a category regenerates rules once with the new slug' );
$terms[2]->slug = 'pin-luu-tru'; do_action('edited_product_cat',2); az_child_product_permalink_refresh();
$terms[2]->parent = 4; do_action('edited_product_cat',2); az_child_product_permalink_refresh();
check( get_permalink(10) === 'https://example.test/site/may-loc-nuoc/pin-luu-tru/pin-flex16/' && route('may-loc-nuoc/pin-luu-tru/')['product_cat'] === 'may-loc-nuoc/pin-luu-tru', 'moving a category updates both links and routes to the new parent' );
$terms[2]->parent = 1; do_action('edited_product_cat',2); az_child_product_permalink_refresh();
$terms[9] = term_fixture(9,'bien-tan',1); do_action('created_product_cat',9); az_child_product_permalink_refresh();
check( route('giai-phap-nlmt/bien-tan/')['product_cat']==='giai-phap-nlmt/bien-tan', 'new category becomes routable after one refresh' );
unset($terms[9]); do_action('delete_product_cat',9); az_child_product_permalink_refresh();
check( !array_key_exists('^giai\\-phap\\-nlmt/bien\\-tan/?$',$options['rewrite_rules']), 'deleted category rules are removed' );
$_SERVER['REQUEST_METHOD']='GET'; $_GET=array();
$context = array('taxonomy'=>'product_cat','id'=>2,'vars'=>array('paged'=>2));
check( az_child_product_permalink_redirect_url('/site/danh-muc-san-pham/giai-phap-nlmt/pin-luu-tru/page/2/?orderby=price&utm_source=test') === 'https://example.test/site/giai-phap-nlmt/pin-luu-tru/page/2/?orderby=price&utm_source=test', 'legacy category redirect retains pagination, ordering and tracking' );
$context = array('type'=>'product','id'=>10);
check( az_child_product_permalink_redirect_url('/site/san-pham/pin-flex16/?utm_source=test') === 'https://example.test/site/giai-phap-nlmt/pin-luu-tru/pin-flex16/?utm_source=test', 'legacy product redirects to its canonical hierarchy URL' );
check( az_child_product_permalink_redirect_url('/site/?p=10&post_type=product&utm_campaign=ads') === 'https://example.test/site/giai-phap-nlmt/pin-luu-tru/pin-flex16/?utm_campaign=ads', 'plain product URL loses routing parameters but keeps campaign tracking' );
check( !az_child_product_permalink_redirect_url('/site/giai-phap-nlmt/pin-luu-tru/pin-flex16/?utm_source=test'), 'canonical product URL does not redirect in a loop' );
$context['vars']=array('page'=>2);
check( az_child_product_permalink_redirect_url('/site/san-pham/pin-flex16/2/') === 'https://example.test/site/giai-phap-nlmt/pin-luu-tru/pin-flex16/2/', 'multi-page product content keeps its page during redirect' );
$context=array('type'=>'product','id'=>10,'feed'=>true,'vars'=>array('feed'=>'atom'));
check( az_child_product_permalink_redirect_url('/site/san-pham/pin-flex16/feed/atom/') === 'https://example.test/site/giai-phap-nlmt/pin-luu-tru/pin-flex16/feed/atom/', 'product feed redirect keeps its native endpoint' );
$context=array('taxonomy'=>'product_cat','id'=>2,'feed'=>true,'vars'=>array('feed'=>'rss2'));
check( az_child_product_permalink_redirect_url('/site/danh-muc-san-pham/giai-phap-nlmt/pin-luu-tru/feed/') === 'https://example.test/site/giai-phap-nlmt/pin-luu-tru/feed/', 'category feed redirect keeps its native endpoint' );
foreach ( array('admin','ajax','preview','404') as $skip ) { $context=array('type'=>'product','id'=>10,$skip=>true); check(!az_child_product_permalink_redirect_url('/site/san-pham/pin-flex16/'), $skip.' request is not redirected'); }
$context=array('type'=>'product','id'=>10); $_SERVER['REQUEST_METHOD']='POST';
check(!az_child_product_permalink_redirect_url('/site/san-pham/pin-flex16/'), 'POST review/cart operations are not redirected');
$_SERVER['REQUEST_METHOD']='GET'; $_GET['add-to-cart']=10;
check(!az_child_product_permalink_redirect_url('/site/san-pham/pin-flex16/?add-to-cart=10'), 'GET add-to-cart is not repeated by a canonical redirect');
$_GET=array(); $context=array('type'=>'post','id'=>11);
check(!az_child_product_permalink_redirect_url('/site/bai-viet/'), 'news articles are not redirected');
$wp_rewrite->use_trailing_slashes=false;
check( get_permalink(10) === 'https://example.test/site/giai-phap-nlmt/pin-luu-tru/pin-flex16', 'URLs respect installations without trailing slashes' );
$wp_rewrite->use_trailing_slashes=true;
$options['permalink_structure']='';
check( az_child_product_permalink_rules(legacy_rules())===legacy_rules() && az_child_product_category_link('plain',$terms[1],'product_cat')==='plain', 'plain permalink installations retain their existing routes and links' );
$options['permalink_structure']='/%postname%/'; $tax_enabled=false;
check( az_child_product_permalink_rules(legacy_rules())===legacy_rules(), 'disabling WooCommerce leaves existing rules alone' );
check( has_action('template_redirect','az_child_product_permalink_redirect')===1, '301 redirect handler runs before WooCommerce canonical redirect' );
