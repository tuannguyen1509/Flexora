<?php
/** Steel homepage: category rail, hero/news, promotions and product collections. */
defined( 'ABSPATH' ) || exit;
if ( post_password_required() ) { echo '<main id="main" class="az-steel-container">' . get_the_password_form() . '</main>'; return; }
$az_steel_data = az_home_page_get_data();
$az_steel_news = az_child_steel_news();
$az_steel_banner = az_shared_banner_get( get_queried_object_id() );
$az_steel_images = array_values( array_filter( wp_parse_id_list( $az_steel_banner['images'] ?? '' ), 'wp_attachment_is_image' ) );
// The homepage has its own hero layout: autoplay must retain every selected image.
if ( 'slider' !== ( $az_steel_banner['layout'] ?? '' ) && empty( $az_steel_banner['autoplay'] ) ) {
    $az_steel_images = array_slice( $az_steel_images, 0, 1 );
}
$az_steel_fallback = get_stylesheet_directory_uri() . '/assets/images/steel-banner.svg';
?>
<main id="main" class="az-steel-home" tabindex="-1">
  <section id="az-steel-intro" class="az-steel-container az-steel-opening" aria-label="Danh mục và thông tin nổi bật">
    <nav id="az-steel-categories" class="az-steel-categories" aria-label="Danh mục sản phẩm">
      <?php az_child_steel_category_links(); ?>
    </nav>
    <div class="az-steel-hero" data-az-steel-slider data-autoplay="<?php echo ! empty( $az_steel_banner['autoplay'] ) ? '1' : '0'; ?>" role="region" aria-roledescription="carousel" aria-label="Banner trang chủ">
      <?php if ( $az_steel_images ) : foreach ( $az_steel_images as $index => $image ) : ?>
        <div class="az-steel-hero__slide" data-az-steel-slide <?php echo $index ? 'hidden' : ''; ?>><?php if ( ! empty( $az_steel_banner['button_url'] ) ) : ?><a href="<?php echo esc_url( $az_steel_banner['button_url'] ); ?>"><?php endif; ?><?php echo wp_get_attachment_image( $image, 'full', false, array( 'alt' => $az_steel_banner['text'] ?: 'Tôn thép xây dựng', 'loading' => $index ? 'lazy' : 'eager', 'fetchpriority' => $index ? 'auto' : 'high' ) ); ?><?php if ( ! empty( $az_steel_banner['button_url'] ) ) : ?></a><?php endif; ?></div>
      <?php endforeach; else : ?><div class="az-steel-hero__slide" data-az-steel-slide><img src="<?php echo esc_url( $az_steel_fallback ); ?>" width="1200" height="680" alt="Chuyên cung cấp các loại sắt thép xây dựng" fetchpriority="high"></div><?php endif; ?>
      <?php if ( count( $az_steel_images ) > 1 ) : ?><div class="az-steel-hero__controls"><button class="az-button az-steel-slider-control" type="button" data-az-steel-prev aria-label="Banner trước">‹</button><span data-az-steel-status aria-live="polite">1 / <?php echo count( $az_steel_images ); ?></span><button class="az-button az-steel-slider-control" type="button" data-az-steel-next aria-label="Banner tiếp theo">›</button><button class="az-button az-steel-slider-control" type="button" data-az-steel-pause aria-label="Tạm dừng tự chuyển banner" aria-pressed="false">Ⅱ</button></div><?php endif; ?>
    </div>
    <aside class="az-steel-headlines" aria-label="Tin mới">
      <?php foreach ( array_slice( $az_steel_news, 0, 4 ) as $article ) : ?><a class="az-steel-headline" href="<?php echo esc_url( get_permalink( $article ) ); ?>"><div class="az-steel-headline__image"><?php echo get_the_post_thumbnail( $article, 'thumbnail', array( 'loading' => 'lazy' ) ); ?></div><div><h2><?php echo esc_html( get_the_title( $article ) ); ?></h2><time datetime="<?php echo esc_attr( get_the_date( 'c', $article ) ); ?>"><?php echo esc_html( get_the_date( 'd/m/Y', $article ) ); ?></time></div></a><?php endforeach; ?>
      <?php if ( ! $az_steel_news ) : ?><div class="az-steel-news-empty"><strong>TIN TỨC NGÀNH THÉP</strong><p>Cập nhật sản phẩm, công trình và thông tin thị trường.</p><a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>">Xem tin tức →</a></div><?php endif; ?>
    </aside>
  </section>
  <section class="az-steel-container az-steel-promos" aria-label="Giới thiệu sản phẩm">
    <?php foreach ( $az_steel_data['steel']['promos'] as $promo ) : ?><a class="az-steel-promo" href="<?php echo esc_url( $promo['url'] ? ( 0 === strpos( $promo['url'], '/' ) ? home_url( $promo['url'] ) : $promo['url'] ) : home_url( '/san-pham/' ) ); ?>">
      <?php if ( ! empty( $promo['image'] ) ) : ?><img src="<?php echo esc_url( az_home_page_asset_url( $promo['image'] ) ); ?>" alt="<?php echo esc_attr( $promo['title'] ); ?>" loading="lazy"><?php else : ?><img class="az-steel-promo__illustration" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/steel-promo.svg' ); ?>" alt="" loading="lazy"><strong><?php echo esc_html( $promo['title'] ); ?></strong><?php endif; ?>
    </a><?php endforeach; ?>
  </section>
  <?php if ( ! empty( $az_steel_data['deal']['enabled'] ) ) : ?>
  <section id="az-steel-featured" class="az-steel-section az-steel-section--tinted"><div class="az-steel-container">
    <h2 class="az-steel-section__title"><?php echo esc_html( $az_steel_data['deal']['title'] ?: 'SẢN PHẨM NỔI BẬT' ); ?></h2>
    <div class="az-steel-featured"><div class="az-steel-product-grid az-steel-product-grid--featured">
      <?php $az_featured = az_child_steel_products( array_filter( array( absint( $az_steel_data['deal']['category_id'] ) ) ), true ); foreach ( $az_featured as $product ) { az_child_steel_card( $product ); } if ( ! $az_featured ) : ?><p class="az-steel-empty">Sản phẩm đang được cập nhật.</p><?php endif; ?>
    </div><a class="az-steel-featured__banner" href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>"><img src="<?php echo esc_url( $az_steel_data['steel']['featured_image'] ?: $az_steel_fallback ); ?>" alt="Tư vấn và báo giá tôn thép" loading="lazy"></a></div>
  </div></section>
  <?php endif; ?>
  <?php if ( ! empty( $az_steel_data['products']['enabled'] ) ) : foreach ( $az_steel_data['products']['sections'] as $index => $section ) : $ids = array_filter( (array) ( $section['category_ids'] ?? array( $section['category_id'] ?? 0 ) ) ); ?>
  <section id="az-steel-products-<?php echo absint( $index + 1 ); ?>" class="az-steel-section"><div class="az-steel-container">
    <h2 class="az-steel-section__title"><?php echo esc_html( $section['title'] ); ?></h2>
    <div class="az-steel-product-grid"><?php $collection = $ids ? az_child_steel_products( $ids ) : array(); foreach ( $collection as $product ) { az_child_steel_card( $product ); } if ( ! $collection ) : ?><p class="az-steel-empty">Sản phẩm đang được cập nhật.</p><?php endif; ?></div>
  </div></section>
  <?php endforeach; endif; ?>
  <?php if ( ! empty( $az_steel_data['blog']['enabled'] ) ) : ?>
  <section id="az-steel-news" class="az-steel-section az-steel-section--tinted"><div class="az-steel-container"><h2 class="az-steel-section__title"><?php echo esc_html( $az_steel_data['blog']['title'] ); ?></h2><div class="az-steel-news-grid">
    <?php foreach ( $az_steel_news as $article ) : ?><article class="az-steel-news-card"><a href="<?php echo esc_url( get_permalink( $article ) ); ?>"><div class="az-steel-news-card__image"><?php echo get_the_post_thumbnail( $article, 'medium_large', array( 'loading' => 'lazy' ) ); ?></div><h3><?php echo esc_html( get_the_title( $article ) ); ?></h3></a><p><?php echo esc_html( wp_trim_words( get_the_excerpt( $article ), 23, '…' ) ); ?></p></article><?php endforeach; ?>
    <?php if ( ! $az_steel_news ) : ?><p class="az-steel-empty">Bài viết đang được cập nhật.</p><?php endif; ?>
  </div></div></section>
  <?php endif; ?>
  <nav class="az-steel-section-nav" aria-label="Chuyển đến khu vực trang chủ">
    <a href="#az-steel-intro" aria-label="Banner và danh mục"></a>
    <?php if ( ! empty( $az_steel_data['deal']['enabled'] ) ) : ?><a href="#az-steel-featured" aria-label="Sản phẩm nổi bật"></a><?php endif; ?>
    <?php if ( ! empty( $az_steel_data['products']['enabled'] ) ) : foreach ( $az_steel_data['products']['sections'] as $index => $section ) : ?><a href="#az-steel-products-<?php echo absint( $index + 1 ); ?>" aria-label="<?php echo esc_attr( $section['title'] ); ?>"></a><?php endforeach; endif; ?>
    <?php if ( ! empty( $az_steel_data['blog']['enabled'] ) ) : ?><a href="#az-steel-news" aria-label="Bài viết và tin tức"></a><?php endif; ?>
  </nav>
</main>
