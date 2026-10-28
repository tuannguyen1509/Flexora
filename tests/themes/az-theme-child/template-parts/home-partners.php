<?php
/** Partner strip; renders only the logos selected in the homepage gallery. */
defined( 'ABSPATH' ) || exit;
?>
<section class="az-home-partners" aria-labelledby="az-home-partners-title">
  <div class="az-steel-container">
    <h2 id="az-home-partners-title"><?php echo esc_html( $config['title'] ); ?></h2>
    <?php if ( $config['items'] ) : ?><ul class="az-home-partners__list">
      <?php foreach ( $config['items'] as $item ) : ?>
      <li class="az-home-partners__item">
        <?php if ( ! empty( $item['id'] ) ) : ?>
          <?php echo wp_get_attachment_image( $item['id'], 'medium', false, array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
        <?php else : ?>
          <img src="<?php echo esc_url( $item['image'] ); ?>" alt="Logo đối tác" loading="lazy" decoding="async" width="180" height="80">
        <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul><?php endif; ?>
  </div>
</section>
