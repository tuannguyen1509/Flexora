<?php
defined( 'ABSPATH' ) || exit;

function az_theme_legacy_includes() {
    static $loaded = false;
    if ( $loaded ) { return; }
    $loaded = true;
    $base = get_template_directory() . '/inc/functions/';
    foreach ( array( 'security.php', 'woocommerce.php', 'theme-setup.php', 'pricing.php', 'licenses.php', 'admin-settings.php', 'contact-form.php', 'integrations-access.php', 'translation.php', 'term-language.php', 'home-products.php', 'customizer.php', 'category-images.php', 'menu-icons.php', 'product-attributes-pricing.php', 'product-fields.php', 'content-media.php', 'product-videos.php' ) as $file ) {
        require_once $base . $file;
    }
    require_once get_template_directory() . '/inc/modules/homepage/module.php';
}

az_theme_legacy_includes();
require_once get_template_directory() . '/inc/modules/flatsome-compat/migrations/class-settings-map.php';
require_once get_template_directory() . '/inc/modules/flatsome-compat/migrations/class-settings-importer.php';
require_once get_template_directory() . '/inc/helpers/options.php';
require_once get_template_directory() . '/inc/core/branding-seo.php';
require_once get_template_directory() . '/inc/helpers/header-components.php';
require_once get_template_directory() . '/inc/floating-contact.php';
require_once get_template_directory() . '/inc/helpers/footer-components.php';
require_once get_template_directory() . '/inc/modules/flatsome-compat/widgets.php';
require_once get_template_directory() . '/inc/modules/flatsome-compat/shortcodes.php';
require_once get_template_directory() . '/inc/modules/content-duplicate.php';
require_once get_template_directory() . '/inc/modules/analytics/class-analytics.php';
require_once get_template_directory() . '/inc/helpers/utilities.php';
require_once get_template_directory() . '/inc/core/class-performance.php';
require_once get_template_directory() . '/inc/core/upload-limits.php';
require_once get_template_directory() . '/inc/core/html-minify.php';
require_once get_template_directory() . '/inc/core/performance-security.php';
require_once get_template_directory() . '/inc/core/performance-tools.php';
require_once get_template_directory() . '/inc/core/media-tools.php';
require_once get_template_directory() . '/inc/core/editor.php';
require_once get_template_directory() . '/inc/core/user-options.php';
require_once get_template_directory() . '/inc/core/auth-rate-limit.php';
require_once get_template_directory() . '/inc/core/google-login.php';
require_once get_template_directory() . '/inc/core/mobile-menu.php';
require_once get_template_directory() . '/inc/core/accessibility.php';
require_once get_template_directory() . '/inc/core/class-blog.php';
require_once get_template_directory() . '/inc/core/blog-image-description.php';
require_once get_template_directory() . '/inc/core/shared-banner.php';
require_once get_template_directory() . '/inc/core/neutral-page.php';
if ( ! is_admin() ) {
    require_once get_template_directory() . '/inc/core/class-site-effects.php';
    require_once get_template_directory() . '/inc/core/class-dynamic-css.php';
}
require_once get_template_directory() . '/inc/options/class-sanitizer.php';
require_once get_template_directory() . '/inc/core/class-custom-code.php';
require_once get_template_directory() . '/inc/options/class-registry.php';
require_once get_template_directory() . '/inc/options/class-options.php';
require_once get_template_directory() . '/inc/options/class-renderer.php';
// after_switch_theme can run on the first public request after activation.
require_once get_template_directory() . '/inc/admin/class-child-theme-installer.php';
$GLOBALS['az_child_theme_installer'] = new AZ_Child_Theme_Installer();
if ( is_admin() ) {
    require_once get_template_directory() . '/inc/admin/class-admin-assets.php';
    require_once get_template_directory() . '/inc/admin/class-dashboard.php';
    require_once get_template_directory() . '/inc/admin/class-advanced.php';
    require_once get_template_directory() . '/inc/admin/class-admin.php';
    $GLOBALS['az_advanced_admin'] = new AZ_Advanced();
    $GLOBALS['az_admin'] = new AZ_Admin();
}
