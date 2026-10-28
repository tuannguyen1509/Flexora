<?php defined( 'ABSPATH' ) || exit; ?>
  <section class="az-home-copy" data-az-home-columns="<?php echo esc_attr( $az_homepage_section['columns'] ); ?>">
    <div class="az-wrap">
      <?php if ( '' !== $az_homepage_section['title'] ) : ?><h2><?php echo esc_html( $az_homepage_section['title'] ); ?></h2><?php endif; ?>
      <?php if ( $az_news_query->have_posts() ) : ?>
        <div class="az-news-grid">
          <?php while ( $az_news_query->have_posts() ) : $az_news_query->the_post(); ?>
            <article class="az-news-card">
              <a class="az-news-thumb" href="<?php the_permalink(); ?>">
                <?php if ( has_post_thumbnail() ) : ?>
                  <?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
                <?php else : ?>
                  <img src="<?php echo esc_url( $az_theme_img ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                <?php endif; ?>
              </a>
              <div class="az-news-body">
                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></time>
                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
              </div>
            </article>
          <?php endwhile; ?>
        </div>
        <?php wp_reset_postdata(); ?>
      <?php endif; ?>
      <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/tin-tuc/' ) ); ?>">Xem thêm</a>
    </div>
  </section>

