<?php
/** One configured header for homepage, pages, posts and WooCommerce. */
defined( 'ABSPATH' ) || exit;
$az_header = az_child_header_options();
$az_header_home = az_child_is_steel_home();
$az_header_logo = az_get_logo_id();
$az_header_primary = ! empty( $az_header['show_primary_menu'] ) && ( ! empty( $az_header['primary_menu'] ) || has_nav_menu( 'primary' ) );
$az_header_transparent = ! empty( $az_header['transparent'] );
if ( $az_header_home && function_exists( 'az_child_soviban_banner' ) ) {
    $az_header_transparent = az_child_soviban_banner( get_queried_object_id() )['overlay_header'] && ! post_password_required();
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="az-site az-child-soviban-site<?php echo $az_header_home ? ' az-steel-site az-home-storefront' : ''; ?>">
<a class="az-steel-skip" href="#main">Đến nội dung chính</a>
<header class="az-steel-header az-steel-site az-shared-header<?php echo $az_header_transparent ? ' is-transparent' : ''; ?><?php echo $az_header_home && $az_header_transparent ? ' az-child-home-overlay' : ''; ?> az-site-header" style="--az-header-main-height:<?php echo esc_attr( $az_header['height'] ); ?>px">
  <?php if ( ! empty( $az_header['top_bar'] ) ) : ?>
  <div class="az-steel-topbar"><div class="az-steel-container az-shared-topbar">
    <span><?php echo esc_html( $az_header['top_bar_text'] ); ?></span>
    <?php if ( ! empty( $az_header['top_menu'] ) || has_nav_menu( 'top' ) ) : ?><nav aria-label="Liên kết trên cùng"><?php az_child_steel_top_menu( $az_header ); ?></nav><?php endif; ?>
    <?php if ( ! empty( $az_header['child_top_social'] ) ) { az_child_steel_social_links(); } ?>
  </div></div>
  <?php endif; ?>
  <div class="az-steel-container az-shared-brandbar">
    <?php if ( function_exists( 'az_child_soviban_logo' ) ) : ?><div class="az-steel-logo"><?php az_child_soviban_logo(); ?></div><?php else : ?>
    <a class="az-steel-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
      <?php if ( $az_header_logo ) { echo wp_get_attachment_image( $az_header_logo, 'full', false, array( 'loading' => 'eager' ) ); } else { ?><span class="az-steel-logo__mark" aria-hidden="true">AZ</span><span><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span><?php } ?>
    </a>
    <?php endif; ?>
    <?php if ( ! empty( $az_header['show_search'] ) ) { az_child_render_header_search( $az_header ); } ?>
    <?php az_child_render_header_utilities( $az_header ); ?>
  </div>
  <?php if ( ! empty( $az_header['show_category'] ) || $az_header_primary ) : ?>
  <div class="az-steel-navband"><div class="az-steel-container az-shared-navrow<?php echo empty( $az_header['show_category'] ) ? ' az-shared-navrow--no-category' : ''; ?>">
    <?php if ( ! empty( $az_header['show_category'] ) ) : ?>
    <div class="az-steel-category-control az-steel-category-dropdown">
      <button class="az-button az-steel-category-toggle" type="button" aria-expanded="false" aria-controls="az-steel-categories"><?php echo az_child_steel_icon( 'menu' ); ?><span><?php echo esc_html( $az_header['category_button_text'] ); ?></span></button>
      <nav id="az-steel-categories" class="az-steel-categories" aria-label="Danh mục sản phẩm"><?php az_child_steel_category_links( true, $az_header ); ?></nav>
    </div>
    <?php endif; ?>
    <?php if ( $az_header_primary ) : ?><nav class="az-steel-primary" aria-label="Điều hướng chính"><?php az_child_steel_main_menu( 'az-steel-menu', $az_header ); ?></nav><?php endif; ?>
  </div></div>
  <?php endif; ?>
</header>
<?php az_child_render_mobile_menu( $az_header ); ?>
<?php az_child_render_sticky_header( $az_header ); ?>
<?php if ( ! $az_header_home ) { do_action( 'az_after_site_header' ); } ?>
