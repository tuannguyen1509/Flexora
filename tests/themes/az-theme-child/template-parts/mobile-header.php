<?php
/** Branded mobile menu; shares search/category/account/cart/CTA/phone configuration. */
defined( 'ABSPATH' ) || exit;
?>
<div class="az-mobile-header<?php echo $sticky ? ' is-sticky' : ''; ?><?php echo 'brand_dropdown' === $style ? ' az-mobile-header--brand_dropdown' : ''; ?>">
  <?php az_render_header_logo( array( 'logo_id' => $logo, 'class' => 'az-mobile-header__logo' ) ); ?>
  <details class="az-mobile-menu az-mobile-menu--<?php echo esc_attr( $style ); ?>">
    <summary class="az-mobile-menu__toggle" aria-controls="az-mobile-menu-panel" aria-label="<?php echo esc_attr__( 'Mở menu', 'az-ai-theme' ); ?>"><svg viewBox="0 0 32 32" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 8h22M8 16h19M11 24h16"/></svg></summary>
    <div class="az-mobile-menu__content">
      <button class="az-mobile-menu__shade" type="button" tabindex="-1" aria-label="<?php echo esc_attr__( 'Đóng menu', 'az-ai-theme' ); ?>"></button>
      <div id="az-mobile-menu-panel" class="az-mobile-menu__panel" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__( 'Menu Mobile', 'az-ai-theme' ); ?>" data-submenu-open-label="<?php echo esc_attr__( 'Mở menu con', 'az-ai-theme' ); ?>" data-submenu-close-label="<?php echo esc_attr__( 'Đóng menu con', 'az-ai-theme' ); ?>">
        <div class="az-mobile-menu__heading"><?php az_render_header_logo( array( 'logo_id' => $logo, 'class' => 'az-mobile-menu__brand' ) ); ?><button class="az-mobile-menu__close" type="button" aria-label="<?php echo esc_attr__( 'Đóng menu', 'az-ai-theme' ); ?>"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button></div>
        <div class="az-child-mobile-controls">
          <?php if ( ! empty( $options['show_search'] ) ) { az_child_render_header_search( $options, 'az-steel-search az-child-mobile-search', 'az-mobile-search' ); } ?>
          <?php az_child_render_header_utilities( $options ); ?>
          <?php if ( ! empty( $options['show_category'] ) ) : ?><details class="az-child-mobile-category"><summary><?php echo esc_html( $options['category_button_text'] ); ?></summary><nav aria-label="Danh mục sản phẩm trên Mobile"><?php az_child_steel_category_links( true, $options ); ?></nav></details><?php endif; ?>
        </div>
        <nav aria-label="<?php echo esc_attr__( 'Menu Mobile', 'az-ai-theme' ); ?>"><?php echo $menu; // Escaped by WordPress menu renderer. ?></nav>
      </div>
    </div>
  </details>
</div>
