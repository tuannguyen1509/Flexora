<?php
/**
 * WooCommerce single product template with configurable section limits.
 *
 * @package AZ_AI_Theme
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

while ( have_posts() ) :
    the_post();
    $product = wc_get_product( get_the_ID() );

    if ( ! $product ) {
        continue;
    }

    $az_woo_options = function_exists( 'az_get_options' ) && is_array( az_get_options()['woocommerce'] ?? null ) ? az_get_options()['woocommerce'] : array();
    $az_woo_enabled = static function( $key, $default = true ) use ( $az_woo_options ) {
        return array_key_exists( $key, $az_woo_options ) ? (bool) $az_woo_options[ $key ] : $default;
    };
    $az_woo_show_breadcrumb      = $az_woo_enabled( 'show_breadcrumb' );
    $az_woo_show_gallery         = $az_woo_enabled( 'show_gallery' );
    $az_woo_show_brand           = $az_woo_enabled( 'show_brand' );
    $az_woo_show_sku             = $az_woo_enabled( 'show_sku' );
    $az_woo_show_short           = $az_woo_enabled( 'show_short_description' );
    $az_woo_show_actions         = $az_woo_enabled( 'show_purchase_actions' );
    $az_woo_show_contact         = $az_woo_enabled( 'show_contact_box' );
    $az_woo_show_facts           = $az_woo_enabled( 'show_product_facts' );
    $az_woo_show_market_links    = $az_woo_enabled( 'show_market_links' );
    $az_woo_show_product_tabs    = $az_woo_enabled( 'show_product_tabs' );
    $az_woo_show_related         = $az_woo_enabled( 'show_related_products' );
    $az_woo_show_recent          = $az_woo_enabled( 'show_recently_viewed' );

    $brand_taxonomy = '';
    foreach ( array( 'product_brand', 'pa_brand', 'pa_thuong-hieu' ) as $taxonomy_name ) {
        if ( taxonomy_exists( $taxonomy_name ) ) {
            $brand_taxonomy = $taxonomy_name;
            break;
        }
    }

    $brands     = $brand_taxonomy ? wp_get_post_terms( get_the_ID(), $brand_taxonomy ) : array();
    $categories = wp_get_post_terms( get_the_ID(), 'product_cat' );
    $warehouses = taxonomy_exists( 'product_warehouse' ) ? wp_get_post_terms( get_the_ID(), 'product_warehouse' ) : array();

    $manufacturer = get_post_meta( get_the_ID(), 'az_manufacturer', true );
    $origin       = get_post_meta( get_the_ID(), 'az_origin', true );
    $warranty     = get_post_meta( get_the_ID(), 'az_warranty', true );
    $shopee_url   = get_post_meta( get_the_ID(), 'az_shopee_url', true );
    $sendo_url    = get_post_meta( get_the_ID(), 'az_sendo_url', true );
    $lazada_url   = get_post_meta( get_the_ID(), 'az_lazada_url', true );
    $tech_specs   = get_post_meta( get_the_ID(), 'az_technical_specs', true );
    $practical    = get_post_meta( get_the_ID(), 'az_practical_applications', true );
    ?>

      <?php if ( $az_woo_show_breadcrumb ) : ?>
        <div class="az-product-trail">
          <div class="az-container az-product-breadcrumb">
            <?php woocommerce_breadcrumb(); ?>
          </div>
        </div>
      <?php endif; ?>

    <main id="az-main-content" tabindex="-1" class="az-main az-product-page az-product-detail">
      <section class="az-container az-shop-notices">
        <?php woocommerce_output_all_notices(); ?>
      </section>

      <section class="az-container az-product-summary<?php echo $az_woo_show_gallery ? '' : ' az-product-summary--gallery-hidden'; ?>">
        <?php if ( $az_woo_show_gallery ) : ?>
        <div class="az-product-gallery">
          <?php
          $main_image_id = $product->get_image_id();
          $gallery_ids   = array_values( array_filter( array_unique( array_merge( $main_image_id ? array( $main_image_id ) : array(), $product->get_gallery_image_ids() ) ) ) );
          ?>

          <?php $az_image_ratio = az_child_product_image_ratio(); ?>
          <div class="az-product-main-frame<?php echo 'auto' === $az_image_ratio ? ' az-product-main-frame--original' : ''; ?>" style="--az-product-image-ratio: <?php echo esc_attr( $az_image_ratio ); ?>;">
          <?php
          if ( ! empty( $gallery_ids ) ) {
              $first_image_id = $gallery_ids[0];
              echo wp_get_attachment_image(
                  $first_image_id,
                  'full',
                  false,
                  array(
                      'class'        => 'az-product-main-img',
                      'data-az-main-image' => '1',
                      'loading'      => 'eager',
                      'decoding'     => 'async',
                  )
              );
          } else {
              echo '<img class="az-product-main-img" data-az-main-image="1" src="' . esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ) . '" alt="' . esc_attr( get_the_title() ) . '">';
          }
          ?>
            <?php if ( count( $gallery_ids ) > 1 ) : ?>
              <button type="button" class="az-product-gallery-nav az-product-gallery-prev" data-az-gallery-prev aria-label="Ảnh trước">‹</button>
              <button type="button" class="az-product-gallery-nav az-product-gallery-next" data-az-gallery-next aria-label="Ảnh tiếp theo">›</button>
            <?php endif; ?>
            <button type="button" class="az-product-gallery-zoom" data-az-gallery-zoom aria-label="Xem ảnh lớn">+</button>
          </div>

          <?php
          if ( count( $gallery_ids ) > 1 ) :
              ?>
              <div class="az-product-thumbs">
                <?php foreach ( $gallery_ids as $thumb_index => $gallery_id ) : ?>
                  <?php
                  $full_src  = wp_get_attachment_image_url( $gallery_id, 'full' );
                  $fullset   = wp_get_attachment_image_srcset( $gallery_id, 'full' );
                  $sizes     = wp_get_attachment_image_sizes( $gallery_id, 'full' );
                  $thumb_alt = get_post_meta( $gallery_id, '_wp_attachment_image_alt', true ) ?: get_the_title();
                  ?>
                  <button type="button" class="az-product-thumb <?php echo 0 === $thumb_index ? 'is-active' : ''; ?>" data-az-product-thumb data-full-src="<?php echo esc_url( $full_src ); ?>" data-full-srcset="<?php echo esc_attr( $fullset ?: '' ); ?>" data-full-sizes="<?php echo esc_attr( $sizes ?: '' ); ?>" data-full-alt="<?php echo esc_attr( $thumb_alt ); ?>" aria-label="<?php echo esc_attr( 'Xem ảnh ' . ( $thumb_index + 1 ) ); ?>">
                    <?php echo wp_get_attachment_image( $gallery_id, 'thumbnail' ); ?>
                  </button>
                <?php endforeach; ?>
              </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="az-product-info">
          <?php if ( $az_woo_show_brand && ! empty( $brands ) && ! is_wp_error( $brands ) ) : ?>
            <a class="az-product-brand" href="<?php echo esc_url( get_term_link( $brands[0] ) ); ?>"><?php echo esc_html( $brands[0]->name ); ?></a>
          <?php endif; ?>

          <h1><?php the_title(); ?></h1>

          <?php if ( $az_woo_show_sku && $product->get_sku() ) : ?>
            <div class="az-product-meta-row"><span>Mã SP: <b><?php echo esc_html( $product->get_sku() ); ?></b></span></div>
          <?php endif; ?>

          <?php $az_product_price_html = function_exists( 'az_storefront_product_price_html' ) ? az_storefront_product_price_html( $product ) : $product->get_price_html(); ?>
          <?php if ( $az_product_price_html ) : ?>
            <div class="az-product-detail-price az-price az-price--detail"><?php echo wp_kses_post( $az_product_price_html ); ?></div>
          <?php endif; ?>

          <?php if ( $az_woo_show_short && $product->get_short_description() ) : ?>
            <div class="az-product-short"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
          <?php endif; ?>

          <?php if ( $az_woo_show_actions && ( $product->is_purchasable() || $product->is_type( 'variable' ) ) ) : ?>
            <div class="az-product-actions">
              <?php woocommerce_template_single_add_to_cart(); ?>
            </div>
          <?php endif; ?>

          <?php if ( $az_woo_show_contact ) : ?>
          <?php
          $az_product_contact_title = trim( get_option( 'az_product_contact_title', '' ) );
          $az_product_contact_desc  = trim( get_option( 'az_product_contact_desc', '' ) );
          $az_product_contact_name  = trim( get_option( 'az_product_contact_name', '' ) );
          $az_product_contact_phone = trim( get_option( 'az_product_contact_phone', '' ) );
          $az_product_contact_zalo  = trim( get_option( 'az_product_contact_zalo', '' ) );

          if ( ! $az_product_contact_phone ) {
              $az_product_contact_phone = function_exists( 'az_storefront_contact_phone' ) ? az_storefront_contact_phone() : trim( get_option( 'az_contact_phone', get_theme_mod( 'az_contact_phone', '' ) ) );
          }
          if ( ! $az_product_contact_zalo ) {
              $az_product_contact_zalo = trim( get_option( 'az_contact_zalo_url', get_theme_mod( 'az_contact_zalo_url', '' ) ) );
          }

          $az_product_contact_phone_clean = preg_replace( '/[^0-9+]/', '', $az_product_contact_phone );
          $az_product_contact_zalo_phone  = preg_replace( '/[^0-9]/', '', $az_product_contact_phone_clean );
          if ( ! $az_product_contact_zalo && $az_product_contact_zalo_phone ) {
              $az_product_contact_zalo = 'https://zalo.me/' . $az_product_contact_zalo_phone;
          }
          $az_show_product_contact = $az_product_contact_title || $az_product_contact_desc || $az_product_contact_name || $az_product_contact_phone || $az_product_contact_zalo;
          ?>
          <?php if ( $az_show_product_contact ) : ?>
          <aside class="az-product-contact-box">
            <?php if ( $az_product_contact_title ) : ?><h2><?php echo esc_html( $az_product_contact_title ); ?></h2><?php endif; ?>
            <?php if ( $az_product_contact_desc ) : ?><p><?php echo esc_html( $az_product_contact_desc ); ?></p><?php endif; ?>
            <?php if ( $az_product_contact_name ) : ?><p class="az-product-contact-name"><strong><?php echo esc_html( $az_product_contact_name ); ?></strong></p><?php endif; ?>
            <div class="az-product-contact-list">
              <?php if ( $az_product_contact_phone ) : ?>
                <a class="az-product-phone" href="tel:<?php echo esc_attr( $az_product_contact_phone_clean ); ?>" aria-label="<?php echo esc_attr( 'Gọi Hotline ' . $az_product_contact_phone ); ?>">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.19 19a19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.09 4.18 2 2 0 0 1 4.08 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.69 2.79a2 2 0 0 1-.45 2.11L8.05 9.89a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.89.33 1.83.56 2.79.69A2 2 0 0 1 22 16.92Z"/></svg>
                  <span><?php echo esc_html( $az_product_contact_phone ); ?></span>
                </a>
              <?php endif; ?>
              <?php if ( $az_product_contact_zalo ) : ?>
                <a class="az-product-zalo az-zalo-chat" href="<?php echo esc_url( $az_product_contact_zalo ); ?>" data-zalo-web="<?php echo esc_url( $az_product_contact_zalo ); ?>" data-zalo-phone="<?php echo esc_attr( $az_product_contact_zalo_phone ); ?>" target="_blank" rel="noopener">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/><path d="M8 10h8M8 14h5"/></svg>
                  <span>Chat Zalo</span>
                </a>
              <?php endif; ?>
            </div>
          </aside>
          <?php endif; ?>
          <?php endif; ?>

          <?php
          $warehouse_names = ( ! empty( $warehouses ) && ! is_wp_error( $warehouses ) ) ? array_filter( wp_list_pluck( $warehouses, 'name' ) ) : array();
          $has_product_facts = $manufacturer || $origin || $warranty || ! empty( $warehouse_names );
          ?>
          <?php if ( $az_woo_show_facts && $has_product_facts ) : ?>
            <div class="az-product-facts">
              <?php if ( $manufacturer ) : ?><p><b>Nhà sản xuất:</b> <?php echo esc_html( $manufacturer ); ?></p><?php endif; ?>
              <?php if ( $origin ) : ?><p><b>Xuất xứ:</b> <?php echo esc_html( $origin ); ?></p><?php endif; ?>
              <?php if ( $warranty ) : ?><p><b>Bảo hành:</b> <?php echo esc_html( $warranty ); ?></p><?php endif; ?>
              <?php if ( ! empty( $warehouse_names ) ) : ?>
                <p><b>Kho hàng:</b> <?php echo esc_html( implode( ', ', $warehouse_names ) ); ?></p>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <?php if ( $az_woo_show_market_links && ( $shopee_url || $sendo_url || $lazada_url ) ) : ?>
            <div class="az-market-links">
              <?php if ( $shopee_url ) : ?><a class="is-shopee" href="<?php echo esc_url( $shopee_url ); ?>" target="_blank" rel="noopener nofollow">Shopee</a><?php endif; ?>
              <?php if ( $sendo_url ) : ?><a class="is-sendo" href="<?php echo esc_url( $sendo_url ); ?>" target="_blank" rel="noopener nofollow">Sendo</a><?php endif; ?>
              <?php if ( $lazada_url ) : ?><a class="is-lazada" href="<?php echo esc_url( $lazada_url ); ?>" target="_blank" rel="noopener nofollow">Lazada</a><?php endif; ?>
            </div>
          <?php endif; ?>
        </div>

      </section>

      <script>
      document.addEventListener('click', function(event) {
        function setGalleryImage(gallery, thumb) {
          var mainImage = gallery ? gallery.querySelector('[data-az-main-image]') : null;
          var nextSrc = thumb ? thumb.getAttribute('data-full-src') : '';
          if (!mainImage || !nextSrc) return;

          var nextSrcset = thumb.getAttribute('data-full-srcset') || '';
          var nextSizes = thumb.getAttribute('data-full-sizes') || '';
          var nextAlt = thumb.getAttribute('data-full-alt') || mainImage.alt || '';

          mainImage.classList.add('is-switching');
          window.setTimeout(function() {
            mainImage.src = nextSrc;
            if (nextSrcset) {
              mainImage.setAttribute('srcset', nextSrcset);
            } else {
              mainImage.removeAttribute('srcset');
            }
            if (nextSizes) {
              mainImage.setAttribute('sizes', nextSizes);
            } else {
              mainImage.removeAttribute('sizes');
            }
            mainImage.alt = nextAlt;
            mainImage.classList.remove('is-switching');
          }, 120);

          gallery.querySelectorAll('[data-az-product-thumb]').forEach(function(item) {
            item.classList.toggle('is-active', item === thumb);
          });
          thumb.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }

        function moveGallery(gallery, direction) {
          var thumbs = Array.prototype.slice.call(gallery.querySelectorAll('[data-az-product-thumb]'));
          if (!thumbs.length) return;
          var currentIndex = thumbs.findIndex(function(item) { return item.classList.contains('is-active'); });
          var nextIndex = (currentIndex + direction + thumbs.length) % thumbs.length;
          setGalleryImage(gallery, thumbs[nextIndex]);
        }

        function openLightbox(gallery) {
          var mainImage = gallery ? gallery.querySelector('[data-az-main-image]') : null;
          if (!mainImage) return;
          var existing = document.querySelector('.az-gallery-lightbox');
          if (existing) existing.remove();
          var lightbox = document.createElement('div');
          lightbox.className = 'az-gallery-lightbox';
          lightbox.innerHTML = '<button type="button" class="az-gallery-lightbox-close" aria-label="Đóng">×</button><img src="" alt="">';
          var image = lightbox.querySelector('img');
          image.src = mainImage.currentSrc || mainImage.src;
          image.alt = mainImage.alt || '';
          document.body.appendChild(lightbox);
          document.body.classList.add('az-gallery-lightbox-open');
        }

        var thumb = event.target.closest('[data-az-product-thumb]');
        if (thumb) {
          setGalleryImage(thumb.closest('.az-product-gallery'), thumb);
          return;
        }

        var prev = event.target.closest('[data-az-gallery-prev]');
        if (prev) {
          moveGallery(prev.closest('.az-product-gallery'), -1);
          return;
        }

        var next = event.target.closest('[data-az-gallery-next]');
        if (next) {
          moveGallery(next.closest('.az-product-gallery'), 1);
          return;
        }

        var zoom = event.target.closest('[data-az-gallery-zoom], [data-az-main-image]');
        if (zoom) {
          openLightbox(zoom.closest('.az-product-gallery'));
          return;
        }

        var close = event.target.closest('.az-gallery-lightbox-close');
        var backdrop = event.target.classList && event.target.classList.contains('az-gallery-lightbox');
        if (close || backdrop) {
          var lightbox = document.querySelector('.az-gallery-lightbox');
          if (lightbox) lightbox.remove();
          document.body.classList.remove('az-gallery-lightbox-open');
        }
      });

      document.addEventListener('keydown', function(event) {
        if (event.key !== 'Escape') return;
        var lightbox = document.querySelector('.az-gallery-lightbox');
        if (!lightbox) return;
        lightbox.remove();
        document.body.classList.remove('az-gallery-lightbox-open');
      });
      </script>

      <?php
      $az_detail_tabs = array();
      $description_html = apply_filters( 'the_content', get_the_content() );
      if ( trim( wp_strip_all_tags( $description_html ) ) ) {
          $az_detail_tabs['description'] = array( 'title' => 'Mô tả', 'content' => $description_html );
      }

      $additional_rows = array();
      foreach ( $product->get_attributes() as $attribute ) {
          if ( ! $attribute->get_visible() ) {
              continue;
          }

          $attribute_label = wc_attribute_label( $attribute->get_name() );
          $attribute_values = array();

          if ( $attribute->is_taxonomy() ) {
              $terms = wc_get_product_terms(
                  $product->get_id(),
                  $attribute->get_name(),
                  array( 'fields' => 'names' )
              );
              if ( ! is_wp_error( $terms ) ) {
                  $attribute_values = $terms;
              }
          } else {
              $attribute_values = $attribute->get_options();
          }

          $attribute_values = array_filter( array_map( 'wc_clean', $attribute_values ) );
          if ( ! $attribute_label || empty( $attribute_values ) ) {
              continue;
          }

          $additional_rows[] = array(
              'label' => $attribute_label,
              'value' => implode( ', ', $attribute_values ),
          );
      }

      if ( ! empty( $additional_rows ) ) {
          ob_start();
          ?>
          <table class="az-product-additional-table">
            <tbody>
              <?php foreach ( $additional_rows as $row ) : ?>
                <tr>
                  <th><?php echo esc_html( $row['label'] ); ?></th>
                  <td><?php echo esc_html( $row['value'] ); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php
          $az_detail_tabs['additional'] = array( 'title' => 'Thông tin bổ sung', 'content' => ob_get_clean() );
      }

      $technical_html = $tech_specs ? apply_filters( 'the_content', $tech_specs ) : '';
      if ( trim( wp_strip_all_tags( $technical_html ) ) ) {
          $az_detail_tabs['technical'] = array( 'title' => 'Thông số kỹ thuật', 'content' => $technical_html );
      }

      $practical_html = $practical ? apply_filters( 'the_content', $practical ) : '';
      if ( trim( wp_strip_all_tags( $practical_html ) ) ) {
          $az_detail_tabs['practical'] = array( 'title' => 'Ứng dụng thực tiễn', 'content' => $practical_html );
      }
      ?>

      <?php if ( $az_woo_show_product_tabs && ! empty( $az_detail_tabs ) ) : ?>
        <section class="az-container az-product-content">
          <div class="az-product-tab-panel">
            <div class="az-product-tab-nav" role="tablist">
              <?php $tab_index = 0; foreach ( $az_detail_tabs as $tab_key => $tab ) : ?>
                <button type="button" role="tab" class="<?php echo 0 === $tab_index ? 'is-active' : ''; ?>" data-az-product-tab="<?php echo esc_attr( $tab_key ); ?>" aria-selected="<?php echo 0 === $tab_index ? 'true' : 'false'; ?>"><?php echo esc_html( $tab['title'] ); ?></button>
              <?php $tab_index++; endforeach; ?>
            </div>
            <div class="az-product-tab-content">
              <?php $tab_index = 0; foreach ( $az_detail_tabs as $tab_key => $tab ) : ?>
                <div class="az-product-tab-pane <?php echo 0 === $tab_index ? 'is-active' : ''; ?>" role="tabpanel" data-az-product-pane="<?php echo esc_attr( $tab_key ); ?>">
                  <div class="az-product-tab-body az-content is-collapsed" data-az-tab-body>
                    <?php echo wp_kses_post( $tab['content'] ); ?>
                  </div>
                  <button type="button" class="az-product-tab-more" data-az-tab-more aria-expanded="false">Xem thêm</button>
                </div>
              <?php $tab_index++; endforeach; ?>
            </div>
          </div>
          <script>
          function azPrepareProductPane(pane) {
            var body = pane ? pane.querySelector('[data-az-tab-body]') : null;
            var more = pane ? pane.querySelector('[data-az-tab-more]') : null;
            if (!body || !more || body.scrollHeight === 0) return;
            if (body.scrollHeight <= 290) {
              body.classList.remove('is-collapsed');
              more.hidden = true;
            } else {
              body.classList.add('is-collapsed');
              more.hidden = false;
            }
          }

          document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-az-product-pane]').forEach(function(pane) {
              azPrepareProductPane(pane);
            });
          });

          document.addEventListener('click', function(event) {
            var more = event.target.closest('[data-az-tab-more]');
            if (more) {
              var pane = more.closest('[data-az-product-pane]');
              if (!pane) return;
              var body = pane.querySelector('[data-az-tab-body]');
              var expanded = pane.classList.toggle('is-expanded');
              if (body) body.classList.toggle('is-collapsed', !expanded);
              more.setAttribute('aria-expanded', expanded ? 'true' : 'false');
              more.textContent = expanded ? 'Rút gọn' : 'Xem thêm';
              if (!expanded) {
                var panel = pane.closest('.az-product-tab-panel') || pane;
                var headerOffset = window.matchMedia('(max-width: 767px)').matches ? 82 : 20;
                var top = panel.getBoundingClientRect().top + window.pageYOffset - headerOffset;
                window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
              }
              return;
            }

            var button = event.target.closest('[data-az-product-tab]');
            if (!button) return;
            var panel = button.closest('.az-product-tab-panel');
            var key = button.getAttribute('data-az-product-tab');
            panel.querySelectorAll('[data-az-product-tab]').forEach(function(item){
              var isSelected = item === button;
              item.classList.toggle('is-active', isSelected);
              item.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            });
            panel.querySelectorAll('[data-az-product-pane]').forEach(function(item){
              var isActive = item.getAttribute('data-az-product-pane') === key;
              item.classList.toggle('is-active', isActive);
              if (isActive) azPrepareProductPane(item);
              if (!isActive) {
                item.classList.remove('is-expanded');
                var itemBody = item.querySelector('[data-az-tab-body]');
                if (itemBody) itemBody.classList.add('is-collapsed');
                var itemMore = item.querySelector('[data-az-tab-more]');
                if (itemMore) {
                  itemMore.setAttribute('aria-expanded', 'false');
                  itemMore.textContent = 'Xem thêm';
                }
              }
            });
          });
          </script>
        </section>
      <?php endif; ?>

      <?php
      $product_video_urls = function_exists( 'az_get_product_video_urls' ) ? az_get_product_video_urls( get_the_ID() ) : array();
      $product_videos = array();
      foreach ( $product_video_urls as $video_url ) {
          $embed_url = function_exists( 'az_product_youtube_embed_url' ) ? az_product_youtube_embed_url( $video_url ) : '';
          $video_id  = function_exists( 'az_product_youtube_id_from_value' ) ? az_product_youtube_id_from_value( $video_url ) : '';
          if ( $embed_url && $video_id ) {
              $product_videos[] = array(
                  'embed' => $embed_url,
                  'thumb' => 'https://img.youtube.com/vi/' . $video_id . '/hqdefault.jpg',
              );
          }
      }
      ?>
      <?php if ( ! empty( $product_videos ) ) : ?>
        <section class="az-container az-product-video-section">
          <div class="az-product-video-panel" data-az-product-video-panel>
            <h2>Video sản phẩm</h2>
            <div class="az-product-video-frame">
              <iframe
                data-az-product-video-frame
                src="<?php echo esc_url( $product_videos[0]['embed'] ); ?>"
                title="<?php echo esc_attr( get_the_title() . ' video 1' ); ?>"
                loading="lazy"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen></iframe>
            </div>
            <?php if ( count( $product_videos ) > 1 ) : ?>
              <div class="az-product-video-thumbs">
                <?php foreach ( $product_videos as $video_index => $video ) : ?>
                  <button type="button" class="az-product-video-thumb <?php echo 0 === $video_index ? 'is-active' : ''; ?>" data-az-product-video-src="<?php echo esc_url( $video['embed'] ); ?>" aria-label="<?php echo esc_attr( 'Xem video ' . ( $video_index + 1 ) ); ?>">
                    <img src="<?php echo esc_url( $video['thumb'] ); ?>" alt="">
                    <span>Video <?php echo esc_html( $video_index + 1 ); ?></span>
                  </button>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
          <script>
          document.addEventListener('click', function(event) {
            var thumb = event.target.closest('[data-az-product-video-src]');
            if (!thumb) return;
            var panel = thumb.closest('[data-az-product-video-panel]');
            var frame = panel ? panel.querySelector('[data-az-product-video-frame]') : null;
            if (!frame) return;
            frame.src = thumb.getAttribute('data-az-product-video-src');
            panel.querySelectorAll('[data-az-product-video-src]').forEach(function(item) {
              item.classList.toggle('is-active', item === thumb);
            });
          });
          </script>
        </section>
      <?php endif; ?>

      <?php
      $related_ids = $az_woo_show_related ? wc_get_related_products( get_the_ID(), az_child_product_section_count( 'related_products_count' ) ) : array();
      if ( $az_woo_show_related && ! empty( $related_ids ) ) :
          ?>
          <section class="az-container az-product-related">
            <div class="az-section-head">
              <h2>Sản phẩm liên quan</h2>
            </div>
            <div class="az-shop-grid">
              <?php foreach ( $related_ids as $related_id ) : ?>
                <?php
                $related = wc_get_product( $related_id );
                if ( ! $related ) {
                    continue;
                }
                ?>
                <?php
                if ( function_exists( 'az_render_product_card' ) ) {
                    az_render_product_card(
                        $related,
                        array(
                            'context'      => 'related',
                            'show_actions' => $az_woo_show_actions,
                        )
                    );
                }
                ?>
              <?php endforeach; ?>
            </div>
          </section>
      <?php endif; ?>

      <?php
      $recent_ids = array();
      if ( ! empty( $_COOKIE['az_recent_products'] ) ) {
          $recent_ids = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_COOKIE['az_recent_products'] ) ) ) ) );
      }
      $recent_ids = array_values( array_diff( $recent_ids, array( get_the_ID() ) ) );
      $recent_ids = array_slice( array_unique( $recent_ids ), 0, az_child_product_section_count( 'recently_viewed_count' ) );

      if ( $az_woo_show_recent && ! empty( $recent_ids ) ) :
          ?>
          <section class="az-container az-product-related az-product-viewed">
            <div class="az-section-head">
              <h2>Sản phẩm đã xem</h2>
            </div>
            <div class="az-shop-grid">
              <?php foreach ( $recent_ids as $recent_id ) : ?>
                <?php
                $recent_product = wc_get_product( $recent_id );
                if ( ! $recent_product || 'publish' !== get_post_status( $recent_id ) ) {
                    continue;
                }
                ?>
                <?php
                if ( function_exists( 'az_render_product_card' ) ) {
                    az_render_product_card(
                        $recent_product,
                        array(
                            'context'      => 'recent',
                            'show_actions' => $az_woo_show_actions,
                        )
                    );
                }
                ?>
              <?php endforeach; ?>
            </div>
          </section>
      <?php endif; ?>
    </main>

<?php endwhile; ?>

<?php get_footer(); ?>
