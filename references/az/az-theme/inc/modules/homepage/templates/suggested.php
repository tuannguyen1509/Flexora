<?php defined( 'ABSPATH' ) || exit; ?>
  <?php if ( $az_suggested_enabled && $az_suggested instanceof WP_Query ) : ?>
    <?php if ( $az_suggested->have_posts() ) : ?>
      <section class="az-wrap az-liked" data-az-home-columns="<?php echo esc_attr( $az_homepage_section['columns'] ); ?>">
        <?php if ( '' !== $az_suggested_title ) : ?><h2><?php echo esc_html( $az_suggested_title ); ?></h2><?php endif; ?>
        <div class="az-liked-row">
          <?php while ( $az_suggested->have_posts() ) : $az_suggested->the_post(); ?>
            <?php az_home_product_card( get_the_ID() ); ?>
          <?php endwhile; ?>
        </div>
      </section>
      <?php wp_reset_postdata(); ?>
    <?php endif; ?>
  <?php endif; ?>
