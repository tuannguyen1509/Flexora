<?php
/**
 * Partial: Breadcrumb — hiển thị ngay dưới header
 * Không hiện trên trang chủ
 * @package AZ_AI_Theme
 */
if ( ! defined('ABSPATH') ) exit;

// Không hiện trên trang chủ
if ( is_front_page() || is_home() ) return;

$bc_items = []; // mảng [ 'url' => '', 'label' => '', 'current' => bool ]

// Trang chủ luôn là item đầu tiên
$bc_items[] = [ 'url' => home_url('/'), 'label' => 'Trang chủ', 'current' => false ];

if ( is_single() ) {
    // Bài viết — thêm category cha đầu tiên
    $cats = get_the_category();
    if ( $cats ) {
        // Lấy category gốc (không có parent)
        $main_cat = null;
        foreach ( $cats as $cat ) {
            if ( ! $cat->parent ) { $main_cat = $cat; break; }
        }
        if ( ! $main_cat ) $main_cat = $cats[0];
        // Nếu category có parent, thêm parent trước
        if ( $main_cat->parent ) {
            $parent_cat = get_category( $main_cat->parent );
            if ( $parent_cat && ! is_wp_error( $parent_cat ) ) {
                $bc_items[] = [ 'url' => get_category_link( $parent_cat->term_id ), 'label' => $parent_cat->name, 'current' => false ];
            }
        }
        $bc_items[] = [ 'url' => get_category_link( $main_cat->term_id ), 'label' => $main_cat->name, 'current' => false ];
    }
    $bc_items[] = [ 'url' => '', 'label' => get_the_title(), 'current' => true ];

} elseif ( is_page() ) {
    // Page — thêm parent pages nếu có
    $ancestors = array_reverse( get_post_ancestors( get_the_ID() ) );
    foreach ( $ancestors as $ancestor_id ) {
        $bc_items[] = [ 'url' => get_permalink( $ancestor_id ), 'label' => get_the_title( $ancestor_id ), 'current' => false ];
    }
    $bc_items[] = [ 'url' => '', 'label' => get_the_title(), 'current' => true ];

} elseif ( is_category() ) {
    $cat = get_queried_object();
    if ( $cat->parent ) {
        $parent = get_category( $cat->parent );
        if ( $parent && ! is_wp_error( $parent ) ) {
            $bc_items[] = [ 'url' => get_category_link( $parent->term_id ), 'label' => $parent->name, 'current' => false ];
        }
    }
    $bc_items[] = [ 'url' => '', 'label' => 'Danh mục: ' . single_cat_title('', false), 'current' => true ];

} elseif ( is_tag() ) {
    $bc_items[] = [ 'url' => '', 'label' => 'Thẻ: ' . single_tag_title('', false), 'current' => true ];

} elseif ( is_tax() ) {
    $term = get_queried_object();
    $bc_items[] = [ 'url' => '', 'label' => $term->name, 'current' => true ];

} elseif ( is_author() ) {
    $bc_items[] = [ 'url' => '', 'label' => 'Tác giả: ' . get_the_author_meta('display_name', get_queried_object_id()), 'current' => true ];

} elseif ( is_search() ) {
    $bc_items[] = [ 'url' => '', 'label' => 'Kết quả tìm kiếm: "' . get_search_query() . '"', 'current' => true ];

} elseif ( is_404() ) {
    $bc_items[] = [ 'url' => '', 'label' => 'Không tìm thấy trang', 'current' => true ];

} elseif ( is_archive() ) {
    $bc_items[] = [ 'url' => '', 'label' => get_the_archive_title(), 'current' => true ];
}

// Tổng số item
$total = count( $bc_items );
if ( $total <= 1 ) return; // Chỉ có trang chủ — không render
?>
<nav class="nh-breadcrumb" aria-label="Breadcrumb">
  <div class="nh-bc-inner">
    <ol class="nh-bc-list" itemscope itemtype="https://schema.org/BreadcrumbList">
      <?php foreach ( $bc_items as $i => $item ) :
        $pos = $i + 1;
      ?>
      <li class="nh-bc-item<?php echo $item['current'] ? ' nh-bc-current' : ''; ?>"
          itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
        <?php if ( ! $item['current'] && $item['url'] ) : ?>
          <a href="<?php echo esc_url( $item['url'] ); ?>" class="nh-bc-link" itemprop="item">
            <?php if ( $i === 0 ) : // Icon nhà cho trang chủ ?>
            <svg class="nh-bc-home-icon" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2" aria-hidden="true">
              <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <meta itemprop="name" content="<?php echo esc_attr( $item['label'] ); ?>">
            <?php else : ?>
            <span itemprop="name"><?php echo esc_html( $item['label'] ); ?></span>
            <?php endif; ?>
          </a>
        <?php else : ?>
          <span class="nh-bc-text" itemprop="name" aria-current="page">
            <?php echo esc_html( wp_trim_words( $item['label'], 10, '…' ) ); ?>
          </span>
        <?php endif; ?>
        <meta itemprop="position" content="<?php echo esc_attr( $pos ); ?>">
        <?php if ( ! $item['current'] ) : ?>
        <span class="nh-bc-sep" aria-hidden="true">
          <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </span>
        <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
</nav>

<style>
.nh-breadcrumb {
    background: rgba(250, 247, 244, 0.5);
    border-bottom: 1px solid rgba(0,152,218,.08);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}
.nh-bc-inner {
    max-width: 1300px;
    margin: 0 auto;
    padding: 0 40px;
}
.nh-bc-list {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    list-style: none;
    margin: 0;
    padding: 12px 0;
    gap: 4px;
}
.nh-bc-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 400;
    line-height: 1.2;
    max-width: 280px;
}
.nh-bc-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: var(--primary-color);
    text-decoration: none;
    white-space: nowrap;
    transition: all .2s ease;
    padding: 4px 8px;
    border-radius: 4px;
    background: transparent;
}
.nh-bc-link:hover { 
    color: #5a2e0a; 
    background: rgba(0,152,218,.08);
}
.nh-bc-home-icon { 
    flex-shrink: 0; 
    opacity: .9;
    width: 14px;
    height: 14px;
}
.nh-bc-sep {
    display: inline-flex;
    align-items: center;
    color: #b89968;
    margin: 0 2px;
    flex-shrink: 0;
    opacity: .7;
}
.nh-bc-text {
    color: #4a3828;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 260px;
    display: inline-block;
    padding: 4px 8px;
}
.nh-bc-current { 
    font-weight: 500;
}
.nh-bc-link span {
    position: relative;
}

@media (max-width: 768px) {
    .nh-bc-inner { padding: 0 16px; }
    .nh-bc-list { padding: 10px 0; gap: 3px; }
    .nh-bc-item { max-width: 180px; font-size: 12px; gap: 4px; }
    .nh-bc-link { padding: 3px 6px; }
    .nh-bc-text { max-width: 160px; padding: 3px 6px; }
    .nh-bc-home-icon { width: 12px; height: 12px; }
}
@media (max-width: 480px) {
    .nh-bc-inner { padding: 0 12px; }
    .nh-bc-list { padding: 8px 0; }
    .nh-bc-item { font-size: 11.5px; }
    /* Trên mobile nhỏ, ẩn bớt item giữa nếu quá dài */
    .nh-bc-item:not(:first-child):not(:last-child):not(:nth-last-child(2)) {
        display: none;
    }
    /* Thêm dấu "..." khi ẩn item */
    .nh-bc-item:nth-child(2) .nh-bc-sep::after {
        content: "...";
        font-weight:600;
        color: #b89968;
        margin: 0 4px;
    }
}
</style>
