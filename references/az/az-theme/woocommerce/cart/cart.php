<?php
/**
 * Custom WooCommerce cart template.
 *
 * @package AZ_AI_Theme
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );
?>

<div class="az-cart-page">
  <form class="woocommerce-cart-form az-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
    <?php do_action( 'woocommerce_before_cart_table' ); ?>

    <div class="az-cart-panel">
      <div class="az-cart-panel__header az-cart-panel-head">
        <h2>Thông tin giỏ hàng</h2>
        <span><?php echo esc_html( sprintf( '%d sản phẩm', WC()->cart->get_cart_contents_count() ) ); ?></span>
      </div>

      <div class="az-cart-panel__items az-cart-items">
        <?php do_action( 'woocommerce_before_cart_contents' ); ?>

        <?php
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

            if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                continue;
            }

            $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
            ?>
            <div class="az-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
              <div class="az-cart-item__media az-cart-item-media">
                <?php
                $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail' ), $cart_item, $cart_item_key );
                if ( $product_permalink ) {
                    printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                } else {
                    echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }
                ?>
              </div>

              <div class="az-cart-item__content az-cart-item-info">
                <div class="az-cart-item__title az-cart-item-title">
                  <?php
                  if ( $product_permalink ) {
                      echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
                  } else {
                      echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) );
                  }

                  do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
                  echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                  ?>
                </div>

                <div class="az-cart-item__mobile-price az-cart-item-mobile-price">
                  <?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
              </div>

              <div class="az-cart-item__price az-cart-price" data-title="Đơn giá">
                <?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
              </div>

              <div class="az-cart-item__quantity az-cart-quantity" data-title="Số lượng">
                <?php
                if ( $_product->is_sold_individually() ) {
                    $min_quantity = 1;
                    $max_quantity = 1;
                } else {
                    $min_quantity = 0;
                    $max_quantity = $_product->get_max_purchase_quantity();
                }

                echo apply_filters(
                    'woocommerce_cart_item_quantity',
                    woocommerce_quantity_input(
                        array(
                            'input_name'   => "cart[{$cart_item_key}][qty]",
                            'input_value'  => $cart_item['quantity'],
                            'max_value'    => $max_quantity,
                            'min_value'    => $min_quantity,
                            'product_name' => $_product->get_name(),
                        ),
                        $_product,
                        false
                    ),
                    $cart_item_key,
                    $cart_item
                ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
              </div>

              <div class="az-cart-item__subtotal az-cart-subtotal" data-title="Thành tiền">
                <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
              </div>

              <div class="az-cart-item__remove az-cart-remove">
                <?php
                echo apply_filters(
                    'woocommerce_cart_item_remove_link',
                    sprintf(
                        '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s"><svg class="az-cart-remove__icon" aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5m4-5v5"/></svg><span class="screen-reader-text">%s</span></a>',
                        esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                        esc_attr( sprintf( 'Xóa %s khỏi giỏ hàng', wp_strip_all_tags( $_product->get_name() ) ) ),
                        esc_attr( $product_id ),
                        esc_attr( $_product->get_sku() ),
                        esc_html( sprintf( 'Xóa %s khỏi giỏ hàng', wp_strip_all_tags( $_product->get_name() ) ) )
                    ),
                    $cart_item_key
                ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
              </div>
            </div>
            <?php
        }
        ?>

        <?php do_action( 'woocommerce_cart_contents' ); ?>
        <?php do_action( 'woocommerce_after_cart_contents' ); ?>
      </div>

      <div class="az-cart-actions">
        <?php if ( wc_coupons_enabled() ) : ?>
          <div class="az-cart-coupon az-cart-coupon coupon">
            <label for="coupon_code">Mã ưu đãi</label>
            <input type="text" name="coupon_code" id="coupon_code" value="" placeholder="Nhập mã giảm giá">
            <button type="submit" class="button az-button" name="apply_coupon" value="Áp dụng">Áp dụng</button>
            <?php do_action( 'woocommerce_cart_coupon' ); ?>
          </div>
        <?php endif; ?>

        <button type="submit" class="button az-button az-button--secondary az-cart-update" name="update_cart" value="Cập nhật giỏ hàng">Cập nhật giỏ hàng</button>
        <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
      </div>
    </div>

    <?php do_action( 'woocommerce_after_cart_table' ); ?>
  </form>

  <aside class="az-cart-summary">
    <div class="az-cart-summary__card az-cart-summary-card">
      <?php do_action( 'woocommerce_before_cart_collaterals' ); ?>
      <?php woocommerce_cart_totals(); ?>
    </div>
  </aside>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
