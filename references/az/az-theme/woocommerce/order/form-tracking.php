<?php
/**
 * Order tracking form layout for AZ Theme.
 *
 * @package AZ_AI_Theme
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

global $post;
?>

<div class="az-order-tracking-page">
    <form action="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" method="post" class="woocommerce-form woocommerce-form-track-order track_order az-order-tracking-form">
        <?php do_action( 'woocommerce_order_tracking_form_start' ); ?>

        <h2><?php esc_html_e( 'Tra cứu đơn hàng', 'woocommerce' ); ?></h2>
        <p><?php esc_html_e( 'Nhập mã đơn hàng và email thanh toán để xem trạng thái đơn hàng của bạn.', 'woocommerce' ); ?></p>

        <p class="form-row form-row-first">
            <label for="orderid"><?php esc_html_e( 'Order ID', 'woocommerce' ); ?></label>
            <input class="input-text" type="text" name="orderid" id="orderid" value="<?php echo isset( $_REQUEST['orderid'] ) ? esc_attr( wp_unslash( $_REQUEST['orderid'] ) ) : ''; ?>" placeholder="<?php esc_attr_e( 'Found in your order confirmation email.', 'woocommerce' ); ?>" />
        </p>
        <p class="form-row form-row-last">
            <label for="order_email"><?php esc_html_e( 'Billing email', 'woocommerce' ); ?></label>
            <input class="input-text" type="email" name="order_email" id="order_email" value="<?php echo isset( $_REQUEST['order_email'] ) ? esc_attr( wp_unslash( $_REQUEST['order_email'] ) ) : ''; ?>" placeholder="<?php esc_attr_e( 'Email you used during checkout.', 'woocommerce' ); ?>" />
        </p>
        <div class="clear"></div>

        <?php do_action( 'woocommerce_order_tracking_form' ); ?>

        <p class="form-row">
            <button type="submit" class="button az-button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="track" value="<?php esc_attr_e( 'Track', 'woocommerce' ); ?>"><?php esc_html_e( 'Track', 'woocommerce' ); ?></button>
        </p>
        <?php wp_nonce_field( 'woocommerce-order_tracking', 'woocommerce-order-tracking-nonce' ); ?>
        <?php do_action( 'woocommerce_order_tracking_form_end' ); ?>
    </form>
</div>
