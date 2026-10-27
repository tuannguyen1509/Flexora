<?php
/** Standalone parent regressions: no child theme, WordPress bootstrap or database. */
define( 'ABSPATH', __DIR__ );
$az_test_parent = dirname( __DIR__ );
$az_test_template = 'page-neutral.php';
$az_test_styles = array();
$az_test_hooks = array();
$az_test_asset_override = false;
$az_test_banner = false;
$az_test_locked = false;
$az_test_featured = true;
$az_test_comments = false;
$az_test_pages = false;
$az_test_loop = 0;
$az_test_title = 'Thông tin dịch vụ';
$az_test_content = '<h2>Nội dung độc lập</h2><p>Thông tin <strong>chi tiết</strong> của trang.</p>';
function add_action( $hook, $callback, $priority = 10 ) { $GLOBALS['az_test_hooks'][ $hook ][ $priority ][] = $callback; }
function is_page_template( $template ) { return $GLOBALS['az_test_template'] === $template; }
function get_theme_file_path( $file ) { return $GLOBALS['az_test_parent'] . '/' . ( $GLOBALS['az_test_asset_override'] ? 'css/az-content.css' : $file ); }
function get_theme_file_uri( $file ) { return ( $GLOBALS['az_test_asset_override'] ? '/custom-child/' : '/az-theme/' ) . $file; }
function wp_enqueue_style( $handle, $url, $dependencies, $version ) { $GLOBALS['az_test_styles'][ $handle ] = array( $url, $dependencies, $version ); }
function esc_attr__( $value, $domain ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html__( $value, $domain ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_attr_e( $value, $domain ) { echo esc_attr__( $value, $domain ); }
function get_header() { echo '<!doctype html><html><body><div class="az-site"><header>Parent header</header>'; if ( $GLOBALS['az_test_banner'] ) { echo '<h1>Shared banner title</h1>'; } }
function get_footer() { echo '<footer>Parent footer</footer></div></body></html>'; }
function have_posts() { return 0 === $GLOBALS['az_test_loop']; }
function the_post() { $GLOBALS['az_test_loop']++; }
function az_shared_banner_active() { return $GLOBALS['az_test_banner']; }
function post_password_required() { return $GLOBALS['az_test_locked']; }
function the_title() { echo htmlspecialchars( $GLOBALS['az_test_title'], ENT_QUOTES, 'UTF-8' ); }
function the_ID() { echo 42; }
function post_class( $class ) { echo 'class="' . htmlspecialchars( $class, ENT_QUOTES, 'UTF-8' ) . '"'; }
function has_post_thumbnail() { return $GLOBALS['az_test_featured']; }
function the_post_thumbnail( $size, $attrs ) { echo '<img src="/uploads/featured.jpg" alt="Featured"'; foreach ( $attrs as $key => $value ) { echo ' ' . htmlspecialchars( $key, ENT_QUOTES, 'UTF-8' ) . '="' . htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ) . '"'; } echo '>'; }
function the_content() { echo $GLOBALS['az_test_locked'] ? '<form class="post-password-form">Password form</form>' : $GLOBALS['az_test_content']; }
function wp_link_pages( $args ) { if ( $GLOBALS['az_test_pages'] ) { echo $args['before'] . '<a href="/page/2/">2</a>' . $args['after']; } }
function comments_open() { return $GLOBALS['az_test_comments']; }
function get_comments_number() { return 0; }
function comments_template() { echo '<div class="test-comments">Comments</div>'; }
function az_test_neutral_check( $ok, $label ) { if ( ! $ok ) { throw new RuntimeException( $label ); } echo "PASS $label\n"; }
function get_template_part( $slug ) { require $GLOBALS['az_test_parent'] . '/' . $slug . '.php'; }
function az_test_neutral_render( $template = 'page-neutral.php' ) { $GLOBALS['az_test_loop'] = 0; ob_start(); require $GLOBALS['az_test_parent'] . '/' . $template; return ob_get_clean(); }

require $az_test_parent . '/inc/core/neutral-page.php';
az_test_neutral_check( array( 'az_neutral_page_assets' ) === $az_test_hooks['wp_enqueue_scripts'][60], 'parent registers its asset loader at enqueue time' );
az_neutral_page_assets();
az_test_neutral_check( '/az-theme/css/neutral-page.css' === $az_test_styles['az-neutral-page'][0]
    && array( 'az-content', 'az-page-style' ) === $az_test_styles['az-neutral-page'][1], 'standalone parent has no child asset dependency' );
az_test_neutral_check( (string) filemtime( $az_test_parent . '/css/neutral-page.css' ) === $az_test_styles['az-neutral-page'][2], 'asset version follows the resolved file' );
$az_test_asset_override = true;
az_neutral_page_assets();
az_test_neutral_check( '/custom-child/css/neutral-page.css' === $az_test_styles['az-neutral-page'][0]
    && (string) filemtime( $az_test_parent . '/css/az-content.css' ) === $az_test_styles['az-neutral-page'][2], 'asset loader respects files resolved by WordPress child override APIs' );
$az_test_asset_override = false;
foreach ( array( '', 'page-contact.php', 'front-page.php' ) as $az_test_template ) {
    $az_test_styles = array(); az_neutral_page_assets();
    az_test_neutral_check( array() === $az_test_styles, 'assets excluded for other template: ' . $az_test_template );
}
$html = az_test_neutral_render();
az_test_neutral_check( false === strpos( $html, '<h1>' ) && false === strpos( $html, 'az-neutral-page__heading' ) && false !== strpos( $html, '<h2>Nội dung độc lập</h2>' ) && false !== strpos( $html, '<strong>chi tiết</strong>' ), 'template omits automatic Page title and retains editor headings/content' );
az_test_neutral_check( false !== strpos( $html, 'az-neutral-page__image' ) && false !== strpos( $html, 'fetchpriority="high"' ), 'featured image is a full-width article image' );
az_test_neutral_check( false !== strpos( $html, 'id="main"' ) && false !== strpos( $html, 'id="az-main-content"' )
    && false !== strpos( $html, 'Parent header' ) && false !== strpos( $html, 'Parent footer' ), 'header/footer and accessibility targets are retained' );
$az_test_title = 'Chính sách'; $az_test_content = '<p>Nội dung trang khác.</p>'; $az_test_featured = false;
$html = az_test_neutral_render();
az_test_neutral_check( false !== strpos( $html, 'Nội dung trang khác.' ) && false === strpos( $html, 'az-neutral-page__featured' ), 'same parent template renders independent content without an image' );
$az_test_locked = true; $az_test_featured = true; $az_test_comments = true;
$html = az_test_neutral_render();
az_test_neutral_check( false !== strpos( $html, 'post-password-form' ) && false === strpos( $html, 'Nội dung trang khác.' )
    && false === strpos( $html, 'az-neutral-page__featured' ) && false === strpos( $html, 'test-comments' ), 'password form hides protected content, featured image and comments' );
$az_test_locked = false; $az_test_pages = true;
$html = az_test_neutral_render();
az_test_neutral_check( false !== strpos( $html, 'aria-label="Phân trang nội dung"' ) && false !== strpos( $html, 'test-comments' ), 'WordPress pagination and comments remain available' );
$az_test_banner = true;
$html = az_test_neutral_render();
az_test_neutral_check( 1 === substr_count( $html, '<h1>' ) && false === strpos( $html, 'az-neutral-page__heading' ), 'shared banner owns the single H1 when active' );
$source = file_get_contents( $az_test_parent . '/page-neutral.php' ) . file_get_contents( $az_test_parent . '/css/neutral-page.css' ) . file_get_contents( $az_test_parent . '/inc/core/neutral-page.php' );
az_test_neutral_check( false === strpos( $source, 'az_child_' ) && false === strpos( $source, '--az-child-' )
    && false === strpos( $source, 'az-theme-child' ) && false === stripos( $source, 'soviban' ), 'reusable parent implementation contains no site or child coupling' );
az_test_neutral_check( false !== strpos( file_get_contents( $az_test_parent . '/inc/bootstrap.php' ), "'/inc/core/neutral-page.php'" ), 'normal parent bootstrap includes the reusable module' );
$az_test_template = 'page-about.php'; $az_test_styles = array();
az_neutral_page_assets();
az_test_neutral_check( isset( $az_test_styles['az-neutral-page'] ), 'About template inherits the editorial stylesheet' );
$az_test_banner = false; $az_test_locked = false; $az_test_featured = false; $az_test_comments = false; $az_test_pages = false;
$az_test_content = '<h2>Giới thiệu từ editor</h2><p><strong>Nội dung định dạng</strong> và <a href="/lien-he/">liên kết</a>.</p>';
$html = az_test_neutral_render( 'page-about.php' );
az_test_neutral_check( $html === az_test_neutral_render(), 'About and neutral templates use exactly the same editorial rendering' );
az_test_neutral_check( false !== strpos( $html, $az_test_content ) && false === strpos( $html, 'Giới thiệu Công ty chúng tôi' ) && false === strpos( $html, 'wv-about-lead-form' ), 'About renders editor formatting without legacy defaults or a built-in contact form' );
$az_test_content = ''; $html = az_test_neutral_render( 'page-about.php' );
az_test_neutral_check( false !== strpos( $html, 'az-neutral-page__content"></div>' ) && false === strpos( $html, 'Công ty chúng tôi' ), 'empty About editor content stays empty rather than restoring canned text' );
$az_test_locked = true; $az_test_featured = true; $az_test_comments = true;
$html = az_test_neutral_render( 'page-about.php' );
az_test_neutral_check( false !== strpos( $html, 'post-password-form' ) && false === strpos( $html, 'az-neutral-page__featured' ) && false === strpos( $html, 'test-comments' ), 'About reuses password protection without leaking media or comments' );
echo "Standalone parent editorial Page regressions passed.\n";
