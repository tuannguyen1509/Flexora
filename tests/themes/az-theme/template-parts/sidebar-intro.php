<?php
/**
 * Partial: Widget Giới Thiệu — dùng chung front-page + page-about
 * Gọi bằng: get_template_part('template-parts/sidebar-intro')
 * @package AZ_AI_Theme
 */
if ( ! defined('ABSPATH') ) exit;

/* ID unique tránh xung đột nếu partial được include 2 lần trên cùng trang */
static $si_idx = 0;
$si_idx++;
$id_text   = 'cp-intro-text-'   . $si_idx;
$id_fade   = 'cp-intro-fade-'   . $si_idx;
$id_toggle = 'cp-intro-toggle-' . $si_idx;

$cp_intro = get_option( 'cp_intro_text',
    "Chuyên Gia Cà Phê là nơi chia sẻ những kiến thức, trải nghiệm và giải pháp thực tế trong hành trình tạo ra những ly cà phê chất lượng.\nTừ việc lựa chọn hạt cà phê phù hợp, kỹ thuật rang chuẩn mực đến tối ưu chiết xuất trên máy pha cà phê, mỗi yếu tố đều đóng vai trò quan trọng trong việc tạo nên một ly cà phê đậm đà, ổn định và đúng GU thưởng thức của người Việt.\nTôi tin rằng một ly cà phê ngon không chỉ đến từ thiết bị hiện đại, mà còn đến từ sự thấu hiểu sâu sắc về hạt cà phê, nghệ thuật rang và kỹ thuật chiết xuất chuẩn xác.\nThông qua nền tảng Chuyên Gia Cà Phê, tôi hy vọng với những kinh nghiệm được tích lũy trong nhiều năm làm nghề sẽ giúp người yêu cà phê và các chủ quán: hiểu đúng về cà phê rang mộc, chọn đúng thiết bị và tạo ra những ly cà phê chất lượng.\nTừng bước nâng cao trải nghiệm thưởng thức cà phê mỗi ngày và cùng nhau Nâng Tầm Gu Cà Phê Việt."
);
$cp_lines = array_filter( array_map( 'trim', explode( "\n", $cp_intro ) ) );

$intro_imgs_raw     = get_option( 'cp_intro_gallery', '' );
$intro_imgs         = $intro_imgs_raw ? json_decode( $intro_imgs_raw, true ) : [];
$intro_imgs_default = [
    [ 'url' => 'https://chuyengia.az9s.com/wp-content/uploads/2026/06/gioi-thieu-chuyen-gia-1.webp', 'link' => '' ],
    [ 'url' => 'https://chuyengia.az9s.com/wp-content/uploads/2026/06/gioi-thieu-chuyen-gia-2.webp', 'link' => '' ],
    [ 'url' => 'https://chuyengia.az9s.com/wp-content/uploads/2026/06/gioi-thieu-chuyen-gia-3.webp', 'link' => '' ],
];
$intro_imgs = array_slice( (array) $intro_imgs, 0, 3 );
foreach ( $intro_imgs_default as $di => $dv ) {
    if ( empty( $intro_imgs[$di]['url'] ) ) $intro_imgs[$di] = $dv;
}
while ( count( $intro_imgs ) < 3 ) {
    $i            = count( $intro_imgs );
    $intro_imgs[] = $intro_imgs_default[$i] ?? [ 'url' => '', 'link' => '' ];
}
?>
<!-- WIDGET: GIỚI THIỆU -->
<div class="cp-widget cp-widget-intro">
  <h3 class="cp-widget-title">Giới thiệu</h3>
  <div class="cp-widget-body">

    <div class="cp-intro-collapse" id="cp-intro-wrap">
      <div id="<?php echo esc_attr($id_text); ?>" class="cp-intro-text">
        <?php foreach ( $cp_lines as $line ) : ?>
        <p><?php echo esc_html( $line ); ?></p>
        <?php endforeach; ?>
      </div>
      <div id="<?php echo esc_attr($id_fade); ?>" class="cp-intro-fade"></div>
    </div>

    <button type="button" id="<?php echo esc_attr($id_toggle); ?>" class="cp-intro-toggle-btn" aria-expanded="false">
      Đọc thêm <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" width="12" height="12"><path d="M6 9l6 6 6-6"/></svg>
    </button>

    <script>
    (function(){
      var box  = document.getElementById('<?php echo esc_js($id_text); ?>');
      var fade = document.getElementById('<?php echo esc_js($id_fade); ?>');
      var btn  = document.getElementById('<?php echo esc_js($id_toggle); ?>');
      if (!box || !btn) return;
      var MAX  = 14 * 1.75 * 8; // 8 dòng như ban đầu
      var full = box.scrollHeight;
      if (full <= MAX + 4) { btn.style.display = 'none'; fade.style.display = 'none'; return; }
      box.style.maxHeight = MAX + 'px';
      var open = false;
      btn.addEventListener('click', function(){
        open = !open;
        box.style.maxHeight = (open ? full : MAX) + 'px';
        fade.style.display  = open ? 'none' : 'block';
        btn.innerHTML = open
          ? 'Thu gọn <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" width="12" height="12"><path d="M18 15l-6-6-6 6"/></svg>'
          : 'Đọc thêm <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" width="12" height="12"><path d="M6 9l6 6 6-6"/></svg>';
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    })();
    </script>

    <ul class="cp-intro-badges">
      <li>
        <span class="cp-badge-icon cp-badge-icon--gold">
          <svg viewBox="0 0 24 24" fill="currentColor" width="12" height="12"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </span>
        <?php echo esc_html( get_option( 'cp_badge_role', 'CEO & Founder CTY GU CÀ PHÊ VIỆT' ) ); ?>
      </li>
      <li>
        <span class="cp-badge-icon cp-badge-icon--red">
          <svg viewBox="0 0 24 24" fill="currentColor" width="12" height="12"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8h5z"/></svg>
        </span>
        <?php echo esc_html( get_option( 'cp_badge_city', 'Sống tại TP. Hồ Chí Minh' ) ); ?>
      </li>
      <li>
        <span class="cp-badge-icon cp-badge-icon--blue">
          <svg viewBox="0 0 24 24" fill="currentColor" width="12" height="12"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
        </span>
        <?php echo esc_html( get_option( 'cp_badge_hometown', 'Đến từ BÌNH ĐỊNH' ) ); ?>
      </li>
    </ul>

    <div class="cp-intro-gallery">
      <?php foreach ( $intro_imgs as $idx => $ig ) :
        $ig_url  = is_array($ig) ? ( $ig['url']  ?? '' ) : (string)$ig;
        $ig_link = is_array($ig) ? ( $ig['link'] ?? '' ) : '';
        if ( ! $ig_url ) $ig_url = 'https://placehold.co/120x120/e8d5c0/8b5e3c?text=Ảnh+' . ($idx + 1);
      ?>
      <?php if ( $ig_link ) : ?>
        <a href="<?php echo esc_url($ig_link); ?>" class="cp-intro-gallery-item">
          <img src="<?php echo esc_url($ig_url); ?>" alt="" loading="lazy">
        </a>
      <?php else : ?>
        <span class="cp-intro-gallery-item">
          <img src="<?php echo esc_url($ig_url); ?>" alt="" loading="lazy">
        </span>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <a href="<?php echo esc_url( get_option( 'cp_intro_link', home_url('/gioi-thieu') ) ); ?>"
       class="cp-btn-more cp-btn-more--full">Xem nhiều hơn</a>

  </div>
</div>
