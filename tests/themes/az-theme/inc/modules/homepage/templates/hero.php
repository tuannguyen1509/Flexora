<?php defined( 'ABSPATH' ) || exit; ?>
  <section class="az-hero-area">
    <?php if ( '' !== $az_homepage_section['title'] ) : ?><h2 class="az-wrap"><?php echo esc_html( $az_homepage_section['title'] ); ?></h2><?php endif; ?>
    <div class="az-wrap az-hero-grid">
      <aside class="az-home-sidebar" aria-label="Danh mục sản phẩm">
        <p class="az-home-sidebar__title">Danh mục sản phẩm</p>
        <?php
        if ( $az_category_menu ) {
            wp_nav_menu(
                array(
                    'menu'        => $az_category_menu->term_id,
                    'container'   => false,
                    'menu_class'  => 'az-category-sidebar-menu',
                    'depth'       => 2,
                    'fallback_cb' => false,
                )
            );
        } else {
            ?>
            <ul class="az-category-sidebar-menu">
              <li class="is-hot"><a href="<?php echo esc_url( $az_shop_url ); ?>">Khuyến mãi sốc</a></li>
              <?php foreach ( array_slice( $az_categories, 0, 6 ) as $category ) : ?>
                <li><a href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?><span>›</span></a></li>
              <?php endforeach; ?>
            </ul>
            <?php
        }
        ?>
      </aside>

      <div class="az-hero-center">
        <a class="az-main-banner <?php echo count( $az_home_banners['main']['slides'] ) > 1 ? 'is-slider' : ''; ?>" href="<?php echo esc_url( $az_home_banners['main']['link'] ?: $az_shop_url ); ?>" data-az-main-banner>
          <?php foreach ( $az_home_banners['main']['slides'] as $az_slide_index => $az_slide_url ) : ?>
            <img class="<?php echo 0 === $az_slide_index ? 'is-active' : ''; ?>" src="<?php echo esc_url( $az_slide_url ?: $az_theme_img ); ?>" alt="<?php echo esc_attr( $az_home_banners['main']['alt'] ?: get_bloginfo( 'name' ) ); ?>" loading="<?php echo 0 === $az_slide_index ? 'eager' : 'lazy'; ?>">
          <?php endforeach; ?>
        </a>
      </div>
    </div>
  </section>
