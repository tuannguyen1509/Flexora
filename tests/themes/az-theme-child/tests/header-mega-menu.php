<?php
/** Real WordPress walker/hook regressions, with in-memory categories and media. */
error_reporting( E_ALL );
set_error_handler( static function( $severity, $message, $file, $line ) { throw new ErrorException( $message, 0, $severity, $file, $line ); } );
$root = dirname( __DIR__, 4 );
define( 'ABSPATH', $root . '/' );
require $root . '/wp-includes/plugin.php';
require $root . '/wp-includes/class-wp-walker.php';
require $root . '/wp-includes/class-walker-nav-menu.php';
function get_privacy_policy_url() { return ''; }
function absint( $value ) { return abs( (int) $value ); }
function esc_attr( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $value ) { return esc_attr( $value ); }
function __( $value, $domain = '' ) { return $value; }
function wp_strip_all_tags( $value ) { return strip_tags( $value ); }
function wp_unique_id( $prefix = '' ) { static $id = 0; return $prefix . ++$id; }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function untrailingslashit( $value ) { return rtrim( $value, '/\\' ); }
function home_url( $path = '/' ) { return 'https://example.test' . $path; }
function is_wp_error( $value ) { return $value instanceof Exception; }
$taxonomies = array( 'product_cat', 'category' );
function taxonomy_exists( $taxonomy ) { return in_array( $taxonomy, $GLOBALS['taxonomies'], true ); }
$terms = array(
    1 => (object) array( 'term_id' => 1, 'name' => 'Điện Năng Lượng Mặt Trời', 'taxonomy' => 'product_cat', 'slug' => 'dien-mat-troi' ),
    2 => (object) array( 'term_id' => 2, 'name' => 'Máy Lọc Nước', 'taxonomy' => 'product_cat', 'slug' => 'may-loc-nuoc' ),
    3 => (object) array( 'term_id' => 3, 'name' => 'Tin tức', 'taxonomy' => 'category', 'slug' => 'tin-tuc' ),
    4 => (object) array( 'term_id' => 4, 'name' => 'Chưa có ảnh', 'taxonomy' => 'product_cat', 'slug' => 'chua-co-anh' ),
);
$meta = array( 1 => array( 'thumbnail_id' => 101 ), 2 => array( 'thumbnail_id' => 102 ), 3 => array( 'cp_cat_image_id' => 103 ) );
function get_term( $id, $taxonomy ) { $term = $GLOBALS['terms'][$id] ?? null; return $term && $term->taxonomy === $taxonomy ? $term : null; }
function get_term_by( $field, $slug, $taxonomy ) { foreach ( $GLOBALS['terms'] as $term ) { if ( $term->slug === $slug && $term->taxonomy === $taxonomy ) { return $term; } } return false; }
function get_term_link( $term ) { return home_url( ( $term->taxonomy === 'product_cat' ? '/san-pham/' : '/category/' ) . $term->slug . '/' ); }
function get_term_meta( $id, $key, $single ) { return $GLOBALS['meta'][$id][$key] ?? ''; }
function wp_get_attachment_image( $id, $size, $icon, $attrs ) {
    if ( ! in_array( $id, array( 101, 102, 103 ), true ) ) { return ''; }
    $src = $id === 102 ? '/fixtures/water.svg' : '/fixtures/solar.svg';
    return '<img src="' . $src . '" width="300" height="300" data-image-id="' . $id . '" class="' . esc_attr( $attrs['class'] ) . '" alt="' . esc_attr( $attrs['alt'] ) . '" loading="lazy">';
}
require dirname( __DIR__ ) . '/inc/header-mega-menu.php';
function item( $id, $parent, $title, $term_id = 0, $taxonomy = 'product_cat' ) {
    return (object) array( 'ID' => $id, 'db_id' => $id, 'menu_item_parent' => $parent, 'title' => $title, 'type' => $term_id ? 'taxonomy' : 'custom', 'object' => $term_id ? $taxonomy : 'custom', 'object_id' => $term_id, 'classes' => array(), 'target' => '', 'xfn' => '', 'current' => false, 'attr_title' => '', 'url' => $term_id ? get_term_link( get_term( $term_id, $taxonomy ) ) : '/link-' . $id . '/' );
}
$items = array( item( 10, 0, 'Điện Năng Lượng Mặt Trời', 1 ) );
foreach ( array( 'Điện Mặt Trời Hòa Lưới Bám Tải', 'Điện Mặt Trời Có Lưu Trữ (Hybrid)', 'Điện Mặt Trời Bơm Tưới - Solar Pump', 'Điện Mặt Trời Độc Lập', 'Biến Tần (Inverter) Điện Mặt Trời', 'Tấm Pin Năng Lượng Mặt Trời', 'Pin Lưu Trữ Điện Mặt Trời (Battery)', 'Tủ Điện Năng Lượng Mặt Trời', 'Phụ Kiện Điện Mặt Trời' ) as $i => $title ) { $items[] = item( 11 + $i, 10, $title ); }
$items[] = item( 30, 0, 'Máy Nước Nóng Năng Lượng', 4 );
$items[] = item( 31, 30, 'Bình nước nóng' );
$items[] = item( 40, 0, 'Máy Lọc Nước', 2 );
$items[] = item( 41, 40, 'Máy lọc nước gia đình' );
$items[] = item( 50, 0, 'Bơm Tăng Áp' );
$items[] = item( 60, 0, 'Vòi Nước Aseka' );
$items[] = item( 70, 0, 'TBVS HWATA' );
$items[] = item( 80, 0, 'Thiết Bị Nhà Bếp', 1 );
$items[] = item( 81, 80, 'Thiết bị nhà bếp' );
function render_menu( $items, $class = 'az-steel-menu', $depth = 0 ) {
    $args = apply_filters( 'wp_nav_menu_args', array( 'menu_class' => $class ) );
    $walker = $args['walker'] ?? new Walker_Nav_Menu();
    $settings = (object) array( 'before' => '', 'after' => '', 'link_before' => '', 'link_after' => '', 'item_spacing' => 'preserve', 'depth' => $depth );
    return '<ul class="' . esc_attr( $class ) . '">' . $walker->walk( $items, $depth, $settings ) . '</ul>';
}
if ( ( $argv[1] ?? '' ) === '--preview' ) {
    echo '<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    foreach ( array( '/parent/style.css', '/theme/css/steel-storefront.css', '/theme/css/header-storefront.css', '/theme/css/home-reference-layout.css', '/theme/css/header-mega-menu.css' ) as $css ) { echo '<link rel="stylesheet" href="' . $css . '">'; }
    echo '<style>:root{--az-container:1200px;--az-body-size:16px;--az-color-text:#555;--az-color-primary:#009dd5}body{margin:0}header{--az-header-main-height:110px}.az-steel-primary{grid-column:1/-1}.az-steel-logo{color:#ef242b}.az-home-solar-copy h2{font-family:Arial,sans-serif}</style></head><body><div id="page" class="az-site az-steel-site az-home-storefront"><header class="az-steel-header az-steel-site az-shared-header"><div class="az-steel-container az-shared-brandbar"><a class="az-steel-logo" href="/">TƯỜNG VINH</a><p>Bản xem trước menu · ảnh danh mục minh họa</p></div><div class="az-steel-navband"><div class="az-steel-container az-shared-navrow az-shared-navrow--no-category"><nav class="az-steel-primary" aria-label="Điều hướng chính">' . render_menu( $items ) . '</nav></div></div></header><main style="min-height:1200px;background:#eef6fa"><img src="/theme/assets/images/solar-home.svg" alt="Banner minh họa" style="width:100%;max-height:460px;object-fit:cover"></main></div><script src="/theme/js/header-mega-menu.js"></script></body></html>';
    exit;
}
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } echo "PASS $message\n"; }
$html = render_menu( $items );
$doc = new DOMDocument();
@$doc->loadHTML( '<!doctype html><html><body>' . $html . '</body></html>', LIBXML_NOERROR | LIBXML_NOWARNING );
$xpath = new DOMXPath( $doc );
check( $xpath->query( '//div[contains(@class,"az-mega-menu__panel")]/ul/li' )->length === 12, 'all manual child links stay in their left columns' );
check( $xpath->query( '//div[contains(@class,"az-mega-menu__panel")]/div/img' )->length === 3, 'category images are separate right columns' );
check( strpos( $html, 'data-image-id="101"' ) !== false && strpos( $html, 'data-image-id="102"' ) !== false, 'each product category uses its own thumbnail' );
check( strpos( $html, 'alt="Điện Năng Lượng Mặt Trời"' ) !== false, 'category image has accessible text' );
check( $xpath->query( '//div[contains(@class,"az-mega-menu__panel") and not(contains(@class,"az-mega-menu__panel--image"))]' )->length === 1, 'missing image leaves a links-only panel' );
check( $xpath->query( '//li[@id="menu-item-50"]/button' )->length === 0, 'leaf items have no unused toggles' );
check( strpos( render_menu( $items, 'az-steel-menu', 1 ), 'data-az-mega-toggle' ) === false, 'flat menus do not render buttons for omitted submenus' );
check( strpos( $html, 'Hybrid)' ) < strpos( $html, 'Solar Pump' ), 'manual menu order preserved' );
$grandchild = item( 100, 11, 'Menu cấp ba' );
$nested = render_menu( array_merge( $items, array( $grandchild ) ) );
check( strpos( $nested, 'Menu cấp ba' ) !== false, 'third-level links remain reachable' );
$blog = render_menu( array( item( 200, 0, 'Tin tức', 3, 'category' ), item( 201, 200, 'Tin mới' ) ) );
check( strpos( $blog, 'data-image-id="103"' ) !== false, 'blog category uses existing category image field' );
$custom = item( 300, 0, 'Liên kết tùy chỉnh' ); $custom->url = get_term_link( $terms[1] );
check( az_child_mega_menu_term( $custom )->term_id === 1, 'exact local custom category links resolve artwork' );
$custom->url = 'https://outside.test/san-pham/dien-mat-troi/';
check( az_child_mega_menu_term( $custom ) === null, 'external links never borrow local artwork' );
$custom->url = '/wrong-path/dien-mat-troi/';
check( az_child_mega_menu_term( $custom ) === null, 'matching slug alone does not misidentify a category' );
$custom->url = '/san-pham/dien-mat-troi/?sort=price';
check( az_child_mega_menu_term( $custom )->term_id === 1, 'relative category links with query resolve' );
add_filter( 'nav_menu_item_title', static function( $title ) { return '<span class="test-icon"></span>' . $title; } );
$items[0]->current = true;
$filtered = render_menu( $items );
check( strpos( $filtered, 'test-icon' ) !== false && strpos( $filtered, 'aria-current="page"' ) !== false, 'WordPress title/icon filters and active-link attributes preserved' );
check( $items[0]->classes === array(), 'shared menu objects are not mutated for mobile' );
preg_match_all( '/aria-controls="([^"]+)"/', $filtered . render_menu( $items, 'az-sticky-menu' ), $ids );
check( count( $ids[1] ) === count( array_unique( $ids[1] ) ), 'primary and sticky toggles have unique panel IDs' );
foreach ( array( 'az-mobile-menu__list', 'az-steel-category-list', 'az-steel-top-menu' ) as $class ) {
    check( ! isset( apply_filters( 'wp_nav_menu_args', array( 'menu_class' => $class ) )['walker'] ), $class . ' keeps its existing renderer' );
}
$existing = new Walker_Nav_Menu();
check( apply_filters( 'wp_nav_menu_args', array( 'menu_class' => 'az-steel-menu', 'walker' => $existing ) )['walker'] === $existing, 'explicit plugin walkers are respected' );
$taxonomies = array( 'category' );
check( strpos( render_menu( $items ), 'az-mega-menu__image' ) === false, 'WooCommerce unavailable keeps menu links without product images' );
