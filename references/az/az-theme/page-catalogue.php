<?php
/**
 * Template Name: Catalogue
 *
 * @package AZ_AI_Theme
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$catalogue_saved = false;
if (
    current_user_can( 'manage_options' )
    && isset( $_POST['az_catalogue_page_nonce'] )
    && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['az_catalogue_page_nonce'] ) ), 'az_catalogue_page_save' )
) {
    update_option( 'az_catalogue_lines', sanitize_textarea_field( wp_unslash( $_POST['az_catalogue_lines'] ?? '' ) ) );
    $catalogue_saved = true;
}

$catalogue_items = function_exists( 'az_catalogue_items' ) ? az_catalogue_items() : array();
?>

<main class="az-catalogue-page" id="primary">
    <section class="az-container az-catalogue-section">
        <h1>CATALOGUE CÔNG TY CHÚNG TÔI</h1>

        <?php if ( current_user_can( 'manage_options' ) ) : ?>
            <form class="az-catalogue-editor" method="post" action="">
                <?php wp_nonce_field( 'az_catalogue_page_save', 'az_catalogue_page_nonce' ); ?>
                <?php if ( $catalogue_saved ) : ?>
                    <div class="az-catalogue-editor-notice">Đã lưu catalogue.</div>
                <?php endif; ?>
                <div class="az-catalogue-editor-head">
                    <strong>Chỉnh sửa Catalogue</strong>
                    <button type="submit">Lưu Catalogue</button>
                </div>
                <textarea name="az_catalogue_lines" rows="5" placeholder="Tên catalogue | Link file PDF"><?php echo esc_textarea( get_option( 'az_catalogue_lines', function_exists( 'az_catalogue_default_lines' ) ? az_catalogue_default_lines() : '' ) ); ?></textarea>
                <p>Mỗi dòng một catalogue, định dạng: <code>Tên hiển thị | Link tải</code>.</p>
            </form>
        <?php endif; ?>

        <div class="az-catalogue-table" role="table" aria-label="Catalogue Công ty chúng tôi">
            <div class="az-catalogue-row az-catalogue-head" role="row">
                <div role="columnheader">NỘI DUNG</div>
                <div role="columnheader">TẢI XUỐNG</div>
            </div>

            <?php if ( ! empty( $catalogue_items ) ) : ?>
                <?php foreach ( $catalogue_items as $catalogue_item ) : ?>
                    <div class="az-catalogue-row" role="row">
                        <div class="az-catalogue-title" role="cell">
                            <?php echo esc_html( $catalogue_item['title'] ); ?>
                        </div>
                        <div class="az-catalogue-download" role="cell">
                            <a href="<?php echo esc_url( $catalogue_item['url'] ); ?>" <?php echo '#' !== $catalogue_item['url'] ? 'download target="_blank" rel="noopener"' : ''; ?> aria-label="<?php echo esc_attr( 'Tải xuống ' . $catalogue_item['title'] ); ?>">
                                <svg viewBox="0 0 64 64" aria-hidden="true" focusable="false">
                                    <path class="az-catalogue-cloud" d="M48.4 27.4C47 18.9 39.7 12.5 31 12.5c-7.1 0-13.4 4.2-16.2 10.5C8 23.7 2.7 29.4 2.7 36.4c0 7.6 6.2 13.8 13.8 13.8h31c7.6 0 13.8-6.2 13.8-13.8 0-6.9-5.1-12.7-12.9-9z"/>
                                    <circle class="az-catalogue-circle" cx="32" cy="42" r="10.5"/>
                                    <path class="az-catalogue-arrow" d="M32 34.5v12M26.8 41.8 32 47l5.2-5.2"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="az-catalogue-row" role="row">
                    <div class="az-catalogue-title" role="cell">Chưa có catalogue</div>
                    <div class="az-catalogue-download" role="cell">-</div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
get_footer();
