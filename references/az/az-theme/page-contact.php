<?php
/**
 * Template Name: Trang Liên Hệ
 *
 * @package AZ_Theme
 */
defined( 'ABSPATH' ) || exit;

$az_contact_company  = az_get_company_name();
$az_contact_phones   = az_get_contact_items( 'phone' );
$az_contact_emails   = az_get_contact_items( 'email' );
$az_contact_hours    = az_get_option( 'general.working_hours', get_option( 'az_contact_hours', '' ) );
$az_contact_addresses = (array) az_get_option( 'general.addresses', array() );
$az_contact_addresses = array_values( array_filter( $az_contact_addresses, static function( $address ) {
    return is_array( $address ) && ! empty( $address['value'] );
} ) );
$az_contact_title    = az_get_option( 'general.contact_page_title', 'Tư vấn & Hỗ trợ' );
$az_contact_intro    = az_get_option( 'general.contact_page_description', 'Đội ngũ của chúng tôi sẵn sàng tư vấn sản phẩm và hỗ trợ nhanh chóng.' );
$az_contact_page_id  = get_queried_object_id();
$az_contact_hero     = $az_contact_page_id ? get_the_post_thumbnail_url( $az_contact_page_id, 'full' ) : '';
$az_contact_captcha  = function_exists( 'az_contact_create_captcha' ) ? az_contact_create_captcha() : array( 'token' => '', 'code' => '' );

get_header();
?>

<main id="az-main-content" tabindex="-1" class="az-main az-contact-page">
    <?php if ( ! az_shared_banner_active() ) : ?>
        <section class="az-contact-hero"<?php echo $az_contact_hero ? ' style="background-image:url(' . esc_url( $az_contact_hero ) . ')"' : ''; ?>>
            <div class="az-contact-hero-inner">
                <span class="az-contact-badge">Liên hệ với chúng tôi</span>
                <h1><?php echo esc_html( $az_contact_title ); ?></h1>
                <?php if ( $az_contact_intro ) : ?><p><?php echo esc_html( $az_contact_intro ); ?></p><?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <div class="az-contact-layout">
        <aside class="az-contact-info" aria-label="Thông tin liên hệ">
            <?php if ( $az_contact_company ) : ?>
                <section class="az-contact-info-card">
                    <span class="az-contact-info-icon" aria-hidden="true">⌂</span>
                    <div><p class="az-contact-info-label">Công ty</p><p class="az-contact-info-value"><?php echo esc_html( $az_contact_company ); ?></p></div>
                </section>
            <?php endif; ?>

            <?php if ( $az_contact_phones ) : ?>
                <section class="az-contact-info-card">
                    <span class="az-contact-info-icon" aria-hidden="true">☎</span>
                    <div><p class="az-contact-info-label">Hotline</p><p class="az-contact-info-value"><?php foreach ( $az_contact_phones as $index => $phone ) : ?><?php if ( $index ) : ?><br><?php endif; ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone['value'] ) ); ?>"><?php echo esc_html( trim( ( $phone['label'] ? $phone['label'] . ': ' : '' ) . $phone['value'] ) ); ?></a><?php endforeach; ?></p></div>
                </section>
            <?php endif; ?>

            <?php if ( $az_contact_emails ) : ?>
                <section class="az-contact-info-card">
                    <span class="az-contact-info-icon" aria-hidden="true">✉</span>
                    <div><p class="az-contact-info-label">Email</p><p class="az-contact-info-value"><?php foreach ( $az_contact_emails as $index => $email ) : ?><?php if ( $index ) : ?><br><?php endif; ?><a href="mailto:<?php echo esc_attr( $email['value'] ); ?>"><?php echo esc_html( trim( ( $email['label'] ? $email['label'] . ': ' : '' ) . $email['value'] ) ); ?></a><?php endforeach; ?></p></div>
                </section>
            <?php endif; ?>

            <?php foreach ( $az_contact_addresses as $address ) : ?>
                <section class="az-contact-info-card">
                    <span class="az-contact-info-icon" aria-hidden="true">⌖</span>
                    <div><p class="az-contact-info-label"><?php echo esc_html( ! empty( $address['label'] ) ? $address['label'] : 'Địa chỉ' ); ?></p><p class="az-contact-info-value"><?php echo nl2br( esc_html( $address['value'] ) ); ?></p><?php if ( ! empty( $address['maps_url'] ) ) : ?><p><a href="<?php echo esc_url( $address['maps_url'] ); ?>" target="_blank" rel="noopener noreferrer">Chỉ đường trên Google Maps ↗</a></p><?php endif; ?></div>
                </section>
            <?php endforeach; ?>

            <?php if ( $az_contact_hours ) : ?>
                <section class="az-contact-info-card">
                    <span class="az-contact-info-icon" aria-hidden="true">◷</span>
                    <div><p class="az-contact-info-label">Giờ làm việc</p><p class="az-contact-info-value"><?php echo esc_html( $az_contact_hours ); ?></p></div>
                </section>
            <?php endif; ?>
        </aside>

        <section class="az-contact-form-card" aria-labelledby="az-contact-form-title">
            <header class="az-contact-form-header">
                <h2 id="az-contact-form-title">Gửi yêu cầu tư vấn</h2>
                <p>Điền thông tin bên dưới, chúng tôi sẽ phản hồi sớm nhất có thể.</p>
            </header>
            <div class="az-contact-form-body">
                <div id="az-contact-success" class="az-contact-success" hidden>
                    <span class="az-contact-success-icon" aria-hidden="true">✓</span>
                    <h3>Gửi thành công!</h3>
                    <p>Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi trong thời gian sớm nhất.</p>
                </div>

                <form id="az-contact-form" novalidate>
                    <?php wp_nonce_field( 'az_contact_form', 'az_contact_nonce' ); ?>
                    <input type="hidden" name="source" value="Form liên hệ">
                    <div class="az-contact-field-row">
                        <p class="az-contact-field"><label for="az-contact-name">Họ và tên <b>*</b></label><input id="az-contact-name" type="text" name="name" placeholder="Nguyễn Văn A" required></p>
                        <p class="az-contact-field"><label for="az-contact-phone">Số điện thoại <b>*</b></label><input id="az-contact-phone" type="tel" name="phone" placeholder="0912 345 678" required></p>
                    </div>
                    <p class="az-contact-field"><label for="az-contact-email">Email</label><input id="az-contact-email" type="email" name="email" placeholder="email@example.com"></p>
                    <p class="az-contact-field"><label for="az-contact-message">Nội dung <b>*</b></label><textarea id="az-contact-message" name="message" placeholder="Mô tả yêu cầu của bạn..." required></textarea></p>
                    <p class="az-contact-field"><label for="az-contact-captcha">Mã bảo vệ <b>*</b></label><span class="az-contact-captcha-wrap"><input id="az-contact-captcha" type="text" name="captcha_answer" placeholder="Nhập mã" autocomplete="off" required><input type="hidden" name="captcha_token" value="<?php echo esc_attr( $az_contact_captcha['token'] ); ?>"><strong id="az-contact-captcha-code" class="az-contact-captcha-code"><?php echo esc_html( $az_contact_captcha['code'] ); ?></strong><button id="az-contact-captcha-refresh" class="az-contact-captcha-refresh" type="button" aria-label="Đổi mã bảo vệ">↻</button></span></p>
                    <button id="az-contact-submit" class="az-contact-submit" type="submit">Gửi yêu cầu tư vấn</button>
                    <p class="az-contact-submit-note">Thông tin của bạn được bảo mật tuyệt đối.</p>
                </form>
            </div>
        </section>

        <?php echo az_google_map_shortcode(); // Sanitized maps from General > Addresses. ?>
    </div>
</main>

<script>
(() => {
  const form = document.getElementById('az-contact-form');
  if (!form) return;
  const submit = document.getElementById('az-contact-submit');
  const captchaCode = document.getElementById('az-contact-captcha-code');
  const captchaRefresh = document.getElementById('az-contact-captcha-refresh');
  const captchaToken = form.querySelector('[name="captcha_token"]');
  const captchaAnswer = document.getElementById('az-contact-captcha');
  const nonce = form.querySelector('[name="az_contact_nonce"]');
  const refreshCaptcha = async () => {
    if (!captchaRefresh || !nonce) return;
    captchaRefresh.disabled = true;
    try {
      const data = new FormData();
      data.append('action', 'az_contact_refresh_captcha');
      data.append('az_contact_nonce', nonce.value);
      const response = await fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', { method: 'POST', body: data });
      const result = await response.json();
      if (!result.success) throw new Error();
      captchaToken.value = result.data.token || '';
      captchaCode.textContent = result.data.code || '';
      captchaAnswer.value = '';
    } catch (error) { window.alert('Không thể đổi mã bảo vệ. Vui lòng thử lại.'); }
    captchaRefresh.disabled = false;
  };
  captchaRefresh?.addEventListener('click', refreshCaptcha);
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.querySelector('[name="name"]').value.trim() || !form.querySelector('[name="phone"]').value.trim() || !form.querySelector('[name="message"]').value.trim() || !captchaAnswer.value.trim()) {
      window.alert('Vui lòng điền đầy đủ các trường bắt buộc.');
      return;
    }
    submit.disabled = true;
    submit.textContent = 'Đang gửi...';
    try {
      const data = new FormData(form);
      data.append('action', 'az_contact_form_submit');
      const response = await fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', { method: 'POST', body: data });
      const result = await response.json();
      if (!result.success) throw new Error((typeof result.data === 'string' ? result.data : result.data?.message) || 'Có lỗi xảy ra.');
      form.hidden = true;
      document.getElementById('az-contact-success').hidden = false;
    } catch (error) {
      window.alert(error.message || 'Không thể gửi yêu cầu. Vui lòng thử lại.');
      submit.disabled = false;
      submit.textContent = 'Gửi yêu cầu tư vấn';
      refreshCaptcha();
    }
  });
})();
</script>

<?php get_footer();
