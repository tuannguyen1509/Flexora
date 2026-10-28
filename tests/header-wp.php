<?php
if ( 'http://localhost:8090' !== get_option( 'siteurl' ) || 'flexora-child-starter' !== get_stylesheet() ) { WP_CLI::error( 'Primary development Child required.' ); }
$checks = 0; $check = function ( $value, $message ) use ( &$checks ) { if ( ! $value ) { throw new RuntimeException( $message ); } $checks++; };
$check( isset( get_registered_nav_menus()['primary'] ), 'primary location registered' ); $check( current_theme_supports( 'custom-logo' ), 'native custom logo support' );
$menu = wp_get_nav_menu_object( 'FLX Header Development' ); $menu_id = $menu ? $menu->term_id : wp_create_nav_menu( 'FLX Header Development' );
$check( ! is_wp_error( $menu_id ), 'fixture menu available' );
foreach ( wp_get_nav_menu_items( $menu_id ) ?: array() as $item ) { wp_delete_post( $item->ID, true ); }
$normal = get_page_by_path( 'flx-header-normal' ); $normal_id = $normal ? $normal->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Header Development Page', 'post_name' => 'flx-header-normal' ) );
$alt = get_page_by_path( 'flx-header-alt' ); $alt_id = $alt ? $alt->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Header Alternate Page', 'post_name' => 'flx-header-alt', 'page_template' => 'page-templates/flx-example-alt.php' ) );
$item = function ( $title, $url, $parent = 0 ) use ( $menu_id ) { return wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $title, 'menu-item-url' => $url, 'menu-item-type' => 'custom', 'menu-item-status' => 'publish', 'menu-item-parent-id' => $parent ) ); };
$item( 'Home', home_url( '/' ) ); $parent = $item( 'Explore reusable framework foundations', get_permalink( $normal_id ) );
$child = $item( 'Nested page with a long descriptive navigation label', get_permalink( $normal_id ), $parent ); $item( 'Third level navigation', get_permalink( $alt_id ), $child );
for ( $i = 1; $i <= 6; $i++ ) { $item( 'Foundation navigation item ' . $i, get_permalink( $normal_id ) ); }
$locations = get_theme_mod( 'nav_menu_locations', array() ); $locations['primary'] = (int) $menu_id; set_theme_mod( 'nav_menu_locations', $locations );
$check( has_nav_menu( 'primary' ), 'menu assigned native API' );
$old_logo = get_theme_mod( 'custom_logo', 0 ); remove_theme_mod( 'custom_logo' );
$fallback = FLX\Header\Logo::render(); $check( false !== strpos( $fallback, 'flx-header__title' ) && false === strpos( $fallback, '<img' ), 'no logo site title fallback' );
$images = get_posts( array( 'post_type' => 'attachment', 'name' => 'flx-header-logo', 'posts_per_page' => 1, 'fields' => 'ids' ) );
if ( ! $images ) {
	// Plain-color PNG test data, built with core zlib; no image library/vendor or external fetch.
	$chunk = function ( $name, $data ) { return pack( 'N', strlen( $data ) ) . $name . $data . pack( 'N', crc32( $name . $data ) ); };
	$png = "\x89PNG\r\n\x1a\n" . $chunk( 'IHDR', pack( 'NNCCCCC', 1200, 300, 8, 2, 0, 0, 0 ) ) . $chunk( 'IDAT', gzcompress( str_repeat( "\0" . str_repeat( "\x31\x57\xd5", 1200 ), 300 ), 9 ) ) . $chunk( 'IEND', '' );
	$upload = wp_upload_bits( 'flx-header-logo.png', null, $png ); if ( $upload['error'] ) { throw new RuntimeException( 'Logo fixture upload failed.' ); }
	$images = array( wp_insert_attachment( array( 'post_title' => 'FLX Header Logo', 'post_name' => 'flx-header-logo', 'post_mime_type' => 'image/png', 'post_status' => 'inherit' ), $upload['file'] ) );
}
$logo = (int) $images[0];
require_once ABSPATH . 'wp-admin/includes/image.php';
wp_update_attachment_metadata( $logo, wp_generate_attachment_metadata( $logo, get_attached_file( $logo ) ) );
set_theme_mod( 'custom_logo', $logo );
$logo_html = FLX\Header\Logo::render(); $check( false !== strpos( $logo_html, '<img' ) && false !== strpos( $logo_html, 'width=' ) && false !== strpos( $logo_html, 'height=' ), 'native image dimensions' );
$check( false !== strpos( $logo_html, 'aria-label=' ) && false !== strpos( $logo_html, home_url( '/' ) ), 'accessible home identity' );
set_theme_mod( 'custom_logo', 999999999 ); $check( false === strpos( FLX\Header\Logo::render(), '<img' ), 'missing attachment fallback' );
remove_theme_mod( 'custom_logo' ); // Readable title is the default development review state.
$without = $locations; unset( $without['primary'] ); set_theme_mod( 'nav_menu_locations', $without );
$check( '' === FLX\Navigation\Primary::render( array( 'navigation_id' => 'flx-no-menu' ) ), 'unassigned menu safe empty fallback' );
set_theme_mod( 'nav_menu_locations', $locations );
$writes = 0; $track = function () use ( &$writes ) { $writes++; };
foreach ( array( 'added_option', 'updated_option', 'deleted_option', 'added_post_meta', 'updated_post_meta', 'deleted_post_meta' ) as $hook ) { add_action( $hook, $track ); }
$navigation = FLX\Navigation\Primary::render( array( 'navigation_id' => 'flx-wp-header-test' ) ); FLX\Header\Logo::render();
$check( false !== strpos( $navigation, '<nav' ), 'nav landmark' ); $check( false !== strpos( $navigation, 'sub-menu' ), 'native hierarchy' );
$check( false !== strpos( $navigation, 'Third level navigation' ), 'nested third level retained' );
$second = FLX\Navigation\Primary::render( array( 'navigation_id' => 'flx-second-header-test' ) );
preg_match_all( '/\sid="([^"]+)"/', $navigation . $second, $ids );
$check( count( $ids[1] ) === count( array_unique( $ids[1] ) ), 'native menu IDs scoped across header instances' );
$check( 0 === $writes, 'render zero persistent writes' );
foreach ( array( 'added_option', 'updated_option', 'deleted_option', 'added_post_meta', 'updated_post_meta', 'deleted_post_meta' ) as $hook ) { remove_action( $hook, $track ); }
$http = wp_remote_get( 'http://wordpress/?flx_probe=1', array( 'headers' => array( 'Host' => 'localhost:8090' ), 'timeout' => 30 ) );
$check( ! is_wp_error( $http ) && 200 === wp_remote_retrieve_response_code( $http ), 'real primary frontend response' );
$html = wp_remote_retrieve_body( $http ); preg_match( '/FLX_LOCAL_PROBE ([A-Za-z0-9+\/=]+)/', $html, $match );
$check( isset( $match[1] ), 'real WP probe available' ); $probe = json_decode( base64_decode( $match[1] ), true );
$check( 'flexora-child-starter' === $probe['stylesheet'], 'actual Child inheritance' );
$check( 'WP_Styles' === $probe['styles_class'] && 'WP_Scripts' === $probe['scripts_class'], 'native WP queues' );
$check( in_array( 'flx-header', $probe['styles_done'], true ) && in_array( 'flx-header', $probe['scripts_done'], true ), 'both header assets printed through WP' );
$check( 1 === $probe['hooks']['wp_body_open'], 'body open once' );
$check( 1 === substr_count( $html, '<header class="flx-header"' ), 'real header once' );
echo wp_json_encode( array( 'assertions' => $checks, 'menu_id' => $menu_id, 'logo_id' => $logo, 'normal_url' => get_permalink( $normal_id ), 'alt_url' => get_permalink( $alt_id ), 'original_logo' => $old_logo ) );
