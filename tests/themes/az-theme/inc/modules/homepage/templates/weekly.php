<?php defined( 'ABSPATH' ) || exit; ?>
  <?php if ( $az_weekly_enabled && $az_featured_products && $az_featured_products->have_posts() ) : ?>
    <section class="az-wrap az-weekly" data-az-home-columns="<?php echo esc_attr( $az_homepage_section['columns'] ); ?>">
      <?php if ( '' !== $az_weekly_title ) : ?><h2><?php echo esc_html( $az_weekly_title ); ?></h2><?php endif; ?>
      <div class="az-weekly-grid">
        <?php while ( $az_featured_products->have_posts() ) : $az_featured_products->the_post(); ?>
          <?php az_home_product_card( get_the_ID(), true ); ?>
        <?php endwhile; ?>
      </div>
    </section>
    <?php wp_reset_postdata(); ?>
  <?php endif; ?>
