<?php
/** Homepage editor cleanup regressions; uses in-memory metadata only. */
define( 'AZ_CHILD_FIXTURE_ONLY', true );
require __DIR__ . '/soviban-home.php';

$post = (object) array( 'ID' => 1, 'post_type' => 'page', 'post_name' => 'trang-chu' );
$admin = capture( function() use ( $post ) { az_home_page_meta_box( $post ); } );
foreach ( array( 'az_ref_', 'az_home_deal_', 'az_home_steel_featured_image', 'data-home-reference', 'Ảnh đại diện cũ' ) as $obsolete ) {
    check( false === strpos( $admin, $obsolete ), 'editor removes unused controls: ' . $obsolete );
}
$dom = new DOMDocument();
libxml_use_internal_errors( true );
$dom->loadHTML( '<?xml encoding="UTF-8">' . $admin );
$xpath = new DOMXPath( $dom );
$panels = $xpath->query( '//*[@id="az-home-admin"]/details/summary' );
$headings = array();
foreach ( $panels as $panel ) { $headings[] = trim( $panel->textContent ); }
check( array(
    'Giới thiệu SOVIBAN & dịch vụ', 'Slider thành phố & chân trang',
    'Banner giới thiệu phía dưới', 'Đối tác của chúng tôi',
    'Nội dung bổ sung (sản phẩm, tin tức, dự án, video)',
) === $headings, 'editor has five useful top-level groups in frontend order' );
check( 4 === $xpath->query( '//*[@id="az-home-admin"]/details[last()]//details' )->length
    && 0 === $xpath->query( '//*[@id="az-home-admin"]/details[last()]//@open' )->length,
    'four working optional sections are grouped and initially collapsed' );
check( false !== strpos( $admin, 'az_child_soviban' ) && false !== strpos( $admin, 'az_home_partners' ), 'active child components retain their fields' );
check( ! function_exists( 'az_child_reference_admin' ) && ! isset( $hooks['save_post_page'][20] ), 'obsolete module and save handler are removed' );

$metadata[1] = array(
    'reference' => array( 'category_title' => 'Keep legacy', 'categories' => array() ),
    'deal' => array( 'enabled' => false, 'title' => '0', 'messages' => array(), 'end_at' => 0 ),
    'steel' => array( 'featured_image' => '/keep-legacy.jpg', 'other' => '' ),
    'soviban' => array( 'footer_note' => 'Keep footer' ),
    'partners' => array( 'enabled' => 0, 'items' => array() ),
    'unknown' => array( 'false' => false, 'zero' => 0, 'empty' => '', 'list' => array() ),
);
$before = $metadata[1];
$_POST = array(
    'az_home_page_nonce' => 'valid',
    'az_home_steel_title' => array( 'New banner' ), 'az_home_steel_image' => array( '/new.jpg' ), 'az_home_steel_url' => array( '/contact/' ),
    'az_home_products_present' => '1', 'az_home_products_enabled' => '1',
    'az_home_product_title' => array( '0' ), 'az_home_product_category' => array( array( 3, 3, 4 ) ), 'az_home_product_image' => array( '/keep-row.jpg' ),
    'az_home_blog_title' => 'News', 'az_home_blog_enabled' => '1', 'az_home_blog_category' => '5',
    'az_home_projects_title' => 'Projects', 'az_home_projects_category' => '6',
    'az_home_video_title' => 'Videos', 'az_home_video_enabled' => '1', 'az_home_video_url' => array( 'https://youtu.be/dQw4w9WgXcQ' ),
    // Old cached forms must also be unable to rewrite retired branches.
    'az_home_deal_title' => 'Overwrite legacy', 'az_home_steel_featured_image' => '/overwrite.jpg',
);
foreach ( $hooks['save_post_page'][10] as $callback ) { $callback( 1, $post ); }
foreach ( array( 'reference', 'deal', 'soviban', 'partners', 'unknown' ) as $branch ) {
    check( $before[ $branch ] === $metadata[1][ $branch ], 'save preserves unedited or retired metadata: ' . $branch );
}
check( '/keep-legacy.jpg' === $metadata[1]['steel']['featured_image'] && '' === $metadata[1]['steel']['other']
    && '/new.jpg' === $metadata[1]['steel']['promos'][0]['image'], 'editing banners preserves legacy steel keys' );
check( '0' === $metadata[1]['products']['sections'][0]['title'] && array( 3, 4 ) === $metadata[1]['products']['sections'][0]['category_ids']
    && '/keep-row.jpg' === $metadata[1]['products']['sections'][0]['image'], 'product settings and hidden compatibility values still save' );
check( 1 === $metadata[1]['blog']['enabled'] && 5 === $metadata[1]['blog']['category_id']
    && 0 === $metadata[1]['projects']['enabled'] && 6 === $metadata[1]['projects']['category_id'], 'optional article settings save independently of collapsed state' );
check( 1 === $metadata[1]['video']['enabled'] && array( 'https://youtu.be/dQw4w9WgXcQ' ) === $metadata[1]['video']['items'], 'video settings remain functional' );
$saved = $metadata[1];
$_POST = array( 'az_home_page_nonce' => 'valid', 'az_home_products_present' => '1', 'az_home_video_title' => '' );
foreach ( $hooks['save_post_page'][10] as $callback ) { $callback( 1, $post ); }
check( array() === az_home_page_get_data( 1 )['products']['sections'] && array() === az_home_page_get_data( 1 )['video']['items']
    && 0 === $metadata[1]['products']['enabled'] && '' === $metadata[1]['video']['title'], 'deleting rows and clearing text remain empty after reload' );
check( $saved['steel'] === $metadata[1]['steel'] && $saved['blog'] === $metadata[1]['blog'] && $saved['projects'] === $metadata[1]['projects'], 'partial forms preserve other active panels' );
$saved = $metadata;
foreach ( array( 'nonce_valid', 'can_edit' ) as $flag ) {
    $GLOBALS[ $flag ] = false;
    foreach ( $hooks['save_post_page'][10] as $callback ) { $callback( 1, $post ); }
    check( $saved === $metadata, 'save rejects ' . $flag );
    $GLOBALS[ $flag ] = true;
}
$revision = true;
foreach ( $hooks['save_post_page'][10] as $callback ) { $callback( 1, $post ); }
check( $saved === $metadata, 'save rejects revisions' );
echo "All homepage editor regressions passed.\n";
