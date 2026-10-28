<?php
/**
 * @package AZ_AI_Theme
 */
if ( ! defined('ABSPATH') ) exit;
get_header();

// Lấy ảnh từ option của page-about
$gallery_ids = get_option( 'az_moments_gallery', [] );
$gallery = [];
foreach ( $gallery_ids as $id ) {
    $full  = wp_get_attachment_image_url( $id, 'large' );
    $thumb = wp_get_attachment_image_url( $id, 'medium_large' );
    $meta  = wp_get_attachment_metadata( $id );
    if ( $full ) {
        $gallery[] = [
            'id'    => $id,
            'full'  => $full,
            'thumb' => $thumb ?: $full,
            'alt'   => get_post_meta( $id, '_wp_attachment_image_alt', true ) ?: '',
        ];
    }
}

// Tiêu đề từ settings nếu có
$page_title = get_the_title();
$sub_title  = '';
if ( have_posts() ) { the_post(); $sub_title = get_the_excerpt(); }
?>

<div class="pg-wrap">
  <div class="pg-container">

    <!-- Header -->
    <div class="pg-header">
      <h1 class="pg-title"><?php echo esc_html( $page_title ?: 'Hình Ảnh' ); ?></h1>
      <?php if ( $sub_title ) : ?>
      <p class="pg-subtitle"><?php echo esc_html( $sub_title ); ?></p>
      <?php endif; ?>
      <p class="pg-count"><?php echo count( $gallery ); ?> khoảnh khắc</p>
    </div>

    <?php if ( ! empty( $gallery ) ) : ?>

    <!-- Grid Gallery -->
    <div class="pg-grid" id="pg-grid">
      <?php foreach ( $gallery as $i => $img ) : ?>
      <button
        class="pg-item"
        data-index="<?php echo $i; ?>"
        aria-label="Xem ảnh <?php echo $i + 1; ?>"
        type="button"
      >
        <img
          src="<?php echo esc_url( $img['thumb'] ); ?>"
          alt="<?php echo esc_attr( $img['alt'] ); ?>"
          loading="<?php echo $i < 8 ? 'eager' : 'lazy'; ?>"
        >
        <div class="pg-item-overlay">
          <svg width="28" height="28" fill="none" stroke="#fff" viewBox="0 0 24 24" stroke-width="2">
            <circle cx="11" cy="11" r="7"/>
            <path d="M21 21l-4.35-4.35M11 8v6M8 11h6"/>
          </svg>
        </div>
      </button>
      <?php endforeach; ?>
    </div>

    <!-- Lightbox -->
    <div class="pg-lb" id="pg-lb" role="dialog" aria-modal="true" aria-label="Xem ảnh" hidden>
      <div class="pg-lb-backdrop" id="pg-lb-bd"></div>

      <button class="pg-lb-close" id="pg-lb-close" aria-label="Đóng">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
          <path d="M18 6L6 18M6 6l12 12"/>
        </svg>
      </button>

      <button class="pg-lb-nav pg-lb-prev" id="pg-lb-prev" aria-label="Ảnh trước">
        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
          <path d="M15 18l-6-6 6-6"/>
        </svg>
      </button>

      <button class="pg-lb-nav pg-lb-next" id="pg-lb-next" aria-label="Ảnh sau">
        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
          <path d="M9 18l6-6-6-6"/>
        </svg>
      </button>

      <div class="pg-lb-stage">
        <img src="" alt="" id="pg-lb-img" class="pg-lb-img">
        <div class="pg-lb-counter" id="pg-lb-counter"></div>
      </div>

      <!-- Thumbnails strip -->
      <div class="pg-lb-strip" id="pg-lb-strip">
        <?php foreach ( $gallery as $i => $img ) : ?>
        <button class="pg-lb-thumb" data-index="<?php echo $i; ?>" type="button" aria-label="Ảnh <?php echo $i+1; ?>">
          <img src="<?php echo esc_url( wp_get_attachment_image_url( $img['id'], 'thumbnail' ) ?: $img['thumb'] ); ?>" alt="" loading="lazy">
        </button>
        <?php endforeach; ?>
      </div>
    </div>

    <?php else : ?>
    <div class="pg-empty">
      <svg width="56" height="56" fill="none" stroke="#c9a97a" viewBox="0 0 24 24" stroke-width="1.5">
        <rect x="3" y="3" width="18" height="18" rx="3"/>
        <circle cx="8.5" cy="8.5" r="1.5"/>
        <path d="M21 15l-5-5L5 21"/>
      </svg>
      <p>Chưa có hình ảnh nào.</p>
      <p style="font-size:13px;color:#4b658c;">Vào <strong>Appearance → Sửa Trang Giới Thiệu</strong> để thêm ảnh.</p>
    </div>
    <?php endif; ?>

  </div>
</div>

<script>
(function(){
    var imgs   = <?php echo wp_json_encode( array_column( $gallery, 'full' ) ); ?>;
    if ( ! imgs.length ) return;

    var lb     = document.getElementById('pg-lb');
    var lbImg  = document.getElementById('pg-lb-img');
    var lbCt   = document.getElementById('pg-lb-counter');
    var strip  = document.getElementById('pg-lb-strip');
    var thumbs = strip ? strip.querySelectorAll('.pg-lb-thumb') : [];
    var cur    = 0;

    function open(idx) {
        cur = ((idx % imgs.length) + imgs.length) % imgs.length;
        lbImg.style.opacity = '0';
        lbImg.onload = function(){ lbImg.style.opacity = '1'; };
        lbImg.src = imgs[cur];
        lbCt.textContent = (cur + 1) + ' / ' + imgs.length;
        lb.hidden = false;
        document.body.style.overflow = 'hidden';
        // active thumb
        thumbs.forEach(function(t, i){ t.classList.toggle('is-active', i === cur); });
        // scroll thumb into view
        if (thumbs[cur]) thumbs[cur].scrollIntoView({ inline: 'center', behavior: 'smooth' });
    }

    function close() {
        lb.hidden = true;
        document.body.style.overflow = '';
        lbImg.src = '';
    }

    // Mở từ grid
    document.querySelectorAll('.pg-item').forEach(function(el) {
        el.addEventListener('click', function() { open(parseInt(el.dataset.index)); });
    });

    document.getElementById('pg-lb-bd').addEventListener('click', close);
    document.getElementById('pg-lb-close').addEventListener('click', close);
    document.getElementById('pg-lb-prev').addEventListener('click', function(){ open(cur - 1); });
    document.getElementById('pg-lb-next').addEventListener('click', function(){ open(cur + 1); });

    // Thumbnail strip click
    thumbs.forEach(function(t, i){ t.addEventListener('click', function(){ open(i); }); });

    // Keyboard
    document.addEventListener('keydown', function(e){
        if (lb.hidden) return;
        if (e.key === 'Escape')      close();
        if (e.key === 'ArrowLeft')   open(cur - 1);
        if (e.key === 'ArrowRight')  open(cur + 1);
    });

    // Touch swipe
    var tx = null;
    lb.addEventListener('touchstart', function(e){ tx = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend',   function(e){
        if (tx === null) return;
        var dx = e.changedTouches[0].clientX - tx;
        if (Math.abs(dx) > 40) dx < 0 ? open(cur + 1) : open(cur - 1);
        tx = null;
    });
})();
</script>

<?php get_footer(); ?>
