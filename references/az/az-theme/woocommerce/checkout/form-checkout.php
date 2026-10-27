<?php
/**
 * Checkout form layout for AZ Theme.
 *
 * @package AZ_AI_Theme
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
    echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
    return;
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout az-checkout-form" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">
    <div class="az-checkout-page">
        <div class="az-checkout-page__customer">
            <?php if ( $checkout->get_checkout_fields() ) : ?>
                <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
                <div id="customer_details" class="col2-set az-checkout-fields">
                    <div class="col-1 az-checkout-fields__billing">
                        <?php do_action( 'woocommerce_checkout_billing' ); ?>
                    </div>
                    <div class="col-2 az-checkout-fields__shipping">
                        <?php do_action( 'woocommerce_checkout_shipping' ); ?>
                    </div>
                </div>
                <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
            <?php endif; ?>
        </div>

        <aside class="az-checkout-page__review">
            <?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
            <h2 id="order_review_heading"><?php esc_html_e( 'Đơn hàng của bạn', 'woocommerce' ); ?></h2>
            <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
            <div id="order_review" class="woocommerce-checkout-review-order">
                <?php do_action( 'woocommerce_checkout_order_review' ); ?>
            </div>
            <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
        </aside>
    </div>
</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
