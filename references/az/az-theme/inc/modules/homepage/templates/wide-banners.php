<?php defined( 'ABSPATH' ) || exit; ?>
  <?php if ( '' !== $az_homepage_section['title'] ) : ?><h2 class="az-wrap"><?php echo esc_html( $az_homepage_section['title'] ); ?></h2><?php endif; ?>
  <section class="az-wrap az-wide-banners">
    <a href="<?php echo esc_url( $az_home_banners['wide_left']['link'] ?: $az_shop_url ); ?>">
      <img src="<?php echo esc_url( $az_home_banners['wide_left']['img'] ?: $az_theme_img ); ?>" alt="<?php echo esc_attr( $az_home_banners['wide_left']['alt'] ?: 'Khóa vân tay hiện đại' ); ?>" loading="lazy">
    </a>
    <a href="<?php echo esc_url( $az_home_banners['wide_right']['link'] ?: $az_shop_url ); ?>">
      <img src="<?php echo esc_url( $az_home_banners['wide_right']['img'] ?: $az_theme_img ); ?>" alt="<?php echo esc_attr( $az_home_banners['wide_right']['alt'] ?: 'Tiêu chuẩn mới nâng tầm cuộc sống' ); ?>" loading="lazy">
    </a>
  </section>
