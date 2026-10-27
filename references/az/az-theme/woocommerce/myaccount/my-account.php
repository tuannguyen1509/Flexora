<?php
/**
 * My Account layout for AZ Theme.
 *
 * @package AZ_AI_Theme
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="az-account-page woocommerce-MyAccount">
    <aside class="az-account-page__navigation">
        <?php do_action( 'woocommerce_account_navigation' ); ?>
    </aside>
    <div class="az-account-page__content woocommerce-MyAccount-content">
        <?php do_action( 'woocommerce_account_content' ); ?>
    </div>
</div>
