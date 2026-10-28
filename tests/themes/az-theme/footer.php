<?php
/**
 * Generic parent-theme footer.
 *
 * Website-specific footers belong in the active child theme.
 *
 * @package AZ_AI_Theme
 */

defined( 'ABSPATH' ) || exit;

$az_general  = az_get_options()['general'] ?? array();
$az_company  = function_exists( 'az_get_company_name' ) ? az_get_company_name() : get_bloginfo( 'name' );
$az_phones   = function_exists( 'az_get_contact_items' ) ? az_get_contact_items( 'phone' ) : array();
$az_emails   = function_exists( 'az_get_contact_items' ) ? az_get_contact_items( 'email' ) : array();
$az_social   = function_exists( 'az_get_social_links' ) ? az_get_social_links() : array();
$az_addresses = ! empty( $az_general['addresses'] ) && is_array( $az_general['addresses'] ) ? $az_general['addresses'] : array();
$az_footer_options = function_exists( 'az_footer_options' ) ? az_footer_options() : array();
?>
<footer class="az-site-footer">
    <div class="az-footer-main">
        <div class="az-wrap az-footer-grid">
            <section>
                <h2><?php echo esc_html( $az_company ); ?></h2>
                <?php if ( ! empty( $az_general['business_registration'] ) ) : ?><p><?php echo nl2br( esc_html( $az_general['business_registration'] ) ); ?></p><?php endif; ?>
                <?php if ( ! empty( $az_general['tax_code'] ) ) : ?><p>Mã số thuế: <?php echo esc_html( $az_general['tax_code'] ); ?></p><?php endif; ?>
            </section>

            <section>
                <h2>Liên hệ</h2>
                <?php foreach ( $az_phones as $az_phone ) : ?>
                    <?php
                    $az_phone_label = rtrim( trim( (string) ( $az_phone['label'] ?? '' ) ), " \t\n\r\0\x0B:" );
                    $az_phone_text = trim( ( $az_phone_label ? $az_phone_label . ': ' : '' ) . ( $az_phone['value'] ?? '' ) );
                    ?>
                    <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $az_phone['value'] ?? '' ) ); ?>"><?php echo esc_html( $az_phone_text ); ?></a>
                <?php endforeach; ?>
                <?php foreach ( $az_emails as $az_email ) : ?>
                    <a href="mailto:<?php echo esc_attr( $az_email['value'] ?? '' ); ?>"><?php echo esc_html( $az_email['value'] ?? '' ); ?></a>
                <?php endforeach; ?>
            </section>

            <section>
                <h2>Địa chỉ</h2>
                <?php if ( $az_addresses ) : ?>
                    <?php foreach ( $az_addresses as $az_address ) : ?><p><?php echo nl2br( esc_html( $az_address['value'] ?? '' ) ); ?></p><?php endforeach; ?>
                <?php elseif ( ! empty( $az_general['address'] ) ) : ?>
                    <p><?php echo nl2br( esc_html( $az_general['address'] ) ); ?></p>
                <?php endif; ?>
            </section>

            <?php if ( ! empty( $az_footer_options['show_top_links'] ) && has_nav_menu( 'footer' ) ) : ?>
                <section>
                    <h2>Thông tin</h2>
                    <?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'depth' => 1 ) ); ?>
                </section>
            <?php endif; ?>

            <?php if ( ! empty( $az_footer_options['show_social'] ) && $az_social ) : ?>
                <section>
                    <h2>Mạng xã hội</h2>
                    <nav aria-label="Mạng xã hội">
                        <?php foreach ( $az_social as $az_network => $az_url ) : ?>
                            <a href="<?php echo esc_url( $az_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( ucfirst( $az_network ) ); ?></a>
                        <?php endforeach; ?>
                    </nav>
                </section>
            <?php endif; ?>
        </div>
    </div>
    <?php if ( ! empty( $az_footer_options['show_copyright'] ) ) : ?><div class="az-footer-copy">© <?php echo esc_html( gmdate( 'Y' ) . ' ' . $az_company ); ?></div><?php endif; ?>
</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
