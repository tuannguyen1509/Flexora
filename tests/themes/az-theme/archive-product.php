<?php
/**
 * WooCommerce product archive and product category template.
 *
 * @package AZ_AI_Theme
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$queried = get_queried_object();
$is_term = $queried instanceof WP_Term;
$title   = $is_term ? $queried->name : get_the_title( wc_get_page_id( 'shop' ) );
$desc    = $is_term ? term_description( $queried ) : '';

$product_categories = taxonomy_exists( 'product_cat' ) ? get_terms(
    array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    )
) : array();

if ( is_wp_error( $product_categories ) ) {
    $product_categories = array();
}

$az_child_categories = array();
if ( $is_term && 'product_cat' === $queried->taxonomy ) {
    $az_child_categories = get_terms(
        array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => (int) $queried->term_id,
            'orderby'    => 'menu_order',
            'order'      => 'ASC',
        )
    );
    if ( is_wp_error( $az_child_categories ) ) {
        $az_child_categories = array();
    }
}

if ( ! function_exists( 'az_render_product_cat_options' ) ) {
    function az_render_product_cat_options( $terms, $selected_slug, $parent = 0, $depth = 0 ) {
        foreach ( $terms as $term ) {
            if ( (int) $term->parent !== (int) $parent ) {
                continue;
            }

            $prefix = $depth > 0 ? str_repeat( '   ', $depth ) . str_repeat( '- ', $depth ) : '';
            ?>
            <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $selected_slug, $term->slug ); ?>>
                <?php echo esc_html( $prefix . $term->name ); ?>
            </option>
            <?php
            az_render_product_cat_options( $terms, $selected_slug, $term->term_id, $depth + 1 );
        }
    }
}

if ( ! function_exists( 'az_render_product_cat_tree' ) ) {
    function az_render_product_cat_tree( $terms, $selected_slug, $open_term_ids = array(), $parent = 0, $depth = 0 ) {
        foreach ( $terms as $term ) {
            if ( (int) $term->parent !== (int) $parent ) {
                continue;
            }

            $term_link = get_term_link( $term );
            if ( is_wp_error( $term_link ) ) {
                continue;
            }

            $is_active = $selected_slug === $term->slug;
            $is_expanded = $is_active || in_array( (int) $term->term_id, $open_term_ids, true );
            $has_children = false;
            foreach ( $terms as $child_term ) {
                if ( (int) $child_term->parent === (int) $term->term_id ) {
                    $has_children = true;
                    break;
                }
            }
            ?>
            <div class="az-shop-cat-item az-shop-cat-item--level-<?php echo esc_attr( min( $depth, 5 ) ); ?><?php echo $has_children ? ' has-children' : ''; ?><?php echo $is_active ? ' is-active' : ''; ?><?php echo $is_expanded ? ' is-expanded' : ''; ?>">
              <div class="az-shop-cat-row">
                <a href="<?php echo esc_url( $term_link ); ?>" class="az-shop-cat-link"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
                  <span class="az-shop-cat-name">
                    <?php if ( $depth > 0 ) : ?><i aria-hidden="true"></i><?php endif; ?>
                    <?php echo esc_html( $term->name ); ?>
                  </span>
                  <b><?php echo esc_html( number_format_i18n( (int) $term->count ) ); ?></b>
                </a>
                <?php if ( $has_children ) : ?>
                  <button class="az-shop-cat-toggle" type="button" aria-expanded="<?php echo $is_expanded ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( sprintf( $is_expanded ? 'Thu gọn danh mục %s' : 'Mở danh mục %s', $term->name ) ); ?>">
                    <span aria-hidden="true"></span>
                  </button>
                <?php endif; ?>
              </div>
              <?php if ( $has_children ) : ?>
                <div class="az-shop-cat-children"<?php echo $is_expanded ? '' : ' hidden'; ?>>
                  <?php az_render_product_cat_tree( $terms, $selected_slug, $open_term_ids, $term->term_id, $depth + 1 ); ?>
                </div>
              <?php endif; ?>
            </div>
            <?php
        }
    }
}

if ( ! function_exists( 'az_shop_term_filter_url' ) ) {
    /** Build an attribute or product-taxonomy filter URL. */
    function az_shop_term_filter_url( $base_url, $taxonomy, $term_slug, $kind = 'attribute' ) {
        $attribute = function_exists( 'wc_attribute_taxonomy_slug' ) ? wc_attribute_taxonomy_slug( $taxonomy ) : preg_replace( '/^pa_/', '', $taxonomy );
        $filter_key = 'taxonomy' === $kind ? 'az_filter_' . sanitize_key( $taxonomy ) : 'filter_' . sanitize_key( $attribute );
        $query_type_key = 'attribute' === $kind ? 'query_type_' . sanitize_key( $attribute ) : '';
        $query_args = array();

        foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ( ! is_string( $value ) ) {
                continue;
            }
            $clean_key = sanitize_key( $key );
            if ( in_array( $clean_key, array( 'paged', 'product-page', 'add-to-cart' ), true ) ) {
                continue;
            }
            $query_args[ $clean_key ] = sanitize_text_field( wp_unslash( $value ) );
        }

        $selected = array_filter( array_map( 'sanitize_title', explode( ',', (string) ( $query_args[ $filter_key ] ?? '' ) ) ) );
        if ( in_array( $term_slug, $selected, true ) ) {
            $selected = array_values( array_diff( $selected, array( $term_slug ) ) );
        } else {
            $selected[] = sanitize_title( $term_slug );
        }

        if ( $selected ) {
            $query_args[ $filter_key ] = implode( ',', array_unique( $selected ) );
            if ( $query_type_key ) {
                $query_args[ $query_type_key ] = 'or';
            }
        } else {
            unset( $query_args[ $filter_key ] );
            if ( $query_type_key ) {
                unset( $query_args[ $query_type_key ] );
            }
        }

        return add_query_arg( $query_args, $base_url );
    }
}

if ( ! function_exists( 'az_shop_clear_filter_url' ) ) {
    function az_shop_clear_filter_url( $base_url ) {
        $query_args = array();
        foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ( ! is_string( $value ) ) {
                continue;
            }
            $clean_key = sanitize_key( $key );
            if ( 0 === strpos( $clean_key, 'filter_' ) || 0 === strpos( $clean_key, 'query_type_' ) || 0 === strpos( $clean_key, 'az_filter_' ) || in_array( $clean_key, array( 'min_price', 'max_price', 'paged', 'product-page', 'add-to-cart' ), true ) ) {
                continue;
            }
            $query_args[ $clean_key ] = sanitize_text_field( wp_unslash( $value ) );
        }
        return add_query_arg( $query_args, $base_url );
    }
}

$selected_category  = isset( $_GET['product_cat'] ) ? sanitize_title( wp_unslash( $_GET['product_cat'] ) ) : ( is_tax( 'product_cat' ) && $is_term ? $queried->slug : '' );
$selected_term      = $selected_category ? get_term_by( 'slug', $selected_category, 'product_cat' ) : false;
$open_term_ids      = $selected_term && ! is_wp_error( $selected_term ) ? array_map( 'absint', get_ancestors( $selected_term->term_id, 'product_cat', 'taxonomy' ) ) : array();
$shop_url           = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$filter_url         = $is_term ? get_term_link( $queried ) : $shop_url;
$az_woo_show_actions = function_exists( 'az_storefront_show_purchase_actions' ) ? az_storefront_show_purchase_actions() : true;
$az_woo_show_sidebar = function_exists( 'az_get_option' ) ? (bool) az_get_option( 'woocommerce.show_sidebar', 1 ) : true;
$az_woo_filter_config = function_exists( 'az_get_option' ) ? (array) az_get_option( 'woocommerce.sidebar_attribute_filters', array() ) : array();
$az_woo_filter_definitions = function_exists( 'az_storefront_filter_definitions' ) ? az_storefront_filter_definitions() : array();
$az_woo_sidebar_filters = array();
$az_woo_has_active_filters = false;
foreach ( $az_woo_filter_config as $az_woo_filter_item ) {
    $az_woo_filter_key = sanitize_key( $az_woo_filter_item['taxonomy'] ?? $az_woo_filter_item['key'] ?? '' );
    if ( empty( $az_woo_filter_item['enabled'] ) || empty( $az_woo_filter_definitions[ $az_woo_filter_key ] ) ) {
        continue;
    }
    $az_woo_filter_definition = $az_woo_filter_definitions[ $az_woo_filter_key ];
    if ( 'price' === $az_woo_filter_definition['kind'] ) {
        $az_woo_min_price = isset( $_GET['min_price'] ) && is_string( $_GET['min_price'] ) ? wc_format_decimal( wp_unslash( $_GET['min_price'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $az_woo_max_price = isset( $_GET['max_price'] ) && is_string( $_GET['max_price'] ) ? wc_format_decimal( wp_unslash( $_GET['max_price'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( '' !== $az_woo_min_price || '' !== $az_woo_max_price ) {
            $az_woo_has_active_filters = true;
        }
        $az_woo_sidebar_filters[] = array(
            'key'   => 'price',
            'kind'  => 'price',
            'label' => $az_woo_filter_definition['label'],
            'min'   => $az_woo_min_price,
            'max'   => $az_woo_max_price,
        );
        continue;
    }

    $az_woo_filter_taxonomy = $az_woo_filter_definition['taxonomy'];
    if ( ! taxonomy_exists( $az_woo_filter_taxonomy ) ) {
        continue;
    }
    $az_woo_filter_terms = get_terms(
        array(
            'taxonomy'   => $az_woo_filter_taxonomy,
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'ASC',
        )
    );
    if ( is_wp_error( $az_woo_filter_terms ) || empty( $az_woo_filter_terms ) ) {
        continue;
    }
    $az_woo_filter_attribute = function_exists( 'wc_attribute_taxonomy_slug' ) ? wc_attribute_taxonomy_slug( $az_woo_filter_taxonomy ) : preg_replace( '/^pa_/', '', $az_woo_filter_taxonomy );
    $az_woo_query_key = 'taxonomy' === $az_woo_filter_definition['kind'] ? 'az_filter_' . $az_woo_filter_taxonomy : 'filter_' . sanitize_key( $az_woo_filter_attribute );
    $az_woo_selected_terms = isset( $_GET[ $az_woo_query_key ] ) && is_string( $_GET[ $az_woo_query_key ] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        ? array_filter( array_map( 'sanitize_title', explode( ',', sanitize_text_field( wp_unslash( $_GET[ $az_woo_query_key ] ) ) ) ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        : array();
    if ( $az_woo_selected_terms ) {
        $az_woo_has_active_filters = true;
    }
    $az_woo_sidebar_filters[] = array(
        'key'      => $az_woo_filter_key,
        'kind'     => $az_woo_filter_definition['kind'],
        'taxonomy' => $az_woo_filter_taxonomy,
        'label'    => $az_woo_filter_definition['label'],
        'terms'    => $az_woo_filter_terms,
        'selected' => $az_woo_selected_terms,
    );
}
if ( is_wp_error( $filter_url ) ) {
    $filter_url = $shop_url;
}
?>

<main id="az-main-content" tabindex="-1" class="az-main az-shop-page" style="<?php
foreach ( array( 'desktop' => 4, 'tablet' => 3, 'mobile' => 2 ) as $device => $default_columns ) {
    $columns = max( 1, min( 6, absint( az_get_option( 'woocommerce.columns_' . $device, $default_columns ) ) ) );
    echo '--az-shop-columns-' . esc_attr( $device ) . ':' . (int) $columns . ';';
}
?>">
  <section class="az-container az-shop-notices">
    <?php woocommerce_output_all_notices(); ?>
  </section>

  <section class="az-container az-shop-layout<?php echo $az_woo_show_sidebar ? '' : ' az-shop-layout--no-sidebar'; ?><?php echo $is_term ? ' az-shop-layout--category' : ''; ?>" style="--az-shop-filter-row:<?php echo ( $desc || $az_child_categories ) ? 3 : 2; ?>;--az-shop-product-row:<?php echo 2 + (int) (bool) ( $desc || $az_child_categories ) + (int) (bool) $az_woo_sidebar_filters; ?>">
    <?php if ( $az_woo_show_sidebar ) : ?>
      <aside class="az-shop-sidebar" aria-label="Danh mục sản phẩm">
        <section class="az-shop-sidebar__panel az-shop-sidebar__panel--categories">
          <header class="az-shop-sidebar__title">
            <h2>Danh mục sản phẩm</h2>
          </header>
          <nav class="az-shop-cat-tree" aria-label="Danh sách danh mục sản phẩm">
            <a class="az-shop-cat-all<?php echo $selected_category ? '' : ' is-active'; ?>" href="<?php echo esc_url( $shop_url ); ?>"<?php echo $selected_category ? '' : ' aria-current="page"'; ?>>
              <span>Tất cả sản phẩm</span>
              <b aria-hidden="true">→</b>
            </a>
            <div class="az-shop-cat-list">
              <?php az_render_product_cat_tree( $product_categories, $selected_category, $open_term_ids ); ?>
            </div>
          </nav>
        </section>

      </aside>
    <?php endif; ?>

    <div class="az-shop-results">
      <?php if ( have_posts() ) : ?>
        <div class="az-shop-catalog-toolbar">
          <?php woocommerce_result_count(); ?>
          <?php woocommerce_catalog_ordering(); ?>
        </div>
      <?php endif; ?>
      <?php if ( $is_term && ( $desc || $az_child_categories ) ) : ?>
        <section class="az-shop-category-overview" aria-label="Thông tin danh mục">
          <?php if ( $desc ) : ?>
            <div class="az-shop-title-desc-wrap">
              <div id="az-shop-title-desc" class="az-shop-title-desc is-collapsed"><?php echo wp_kses_post( $desc ); ?></div>
              <button class="az-shop-title-desc-toggle" type="button" data-az-shop-description-toggle aria-controls="az-shop-title-desc" aria-expanded="false">
                <span class="az-shop-title-desc-toggle-more">Xem thêm</span>
                <span class="az-shop-title-desc-toggle-less">Thu gọn</span>
                <span class="az-shop-title-desc-toggle-icon" aria-hidden="true"></span>
              </button>
            </div>
          <?php endif; ?>
          <?php if ( $az_child_categories ) : ?>
            <nav class="az-shop-child-categories" aria-label="Danh mục con của <?php echo esc_attr( $title ); ?>">
              <?php foreach ( $az_child_categories as $az_child_category ) : ?>
                <?php
                $az_child_link = get_term_link( $az_child_category );
                if ( is_wp_error( $az_child_link ) ) { continue; }
                $az_child_thumbnail_id = absint( get_term_meta( $az_child_category->term_id, 'thumbnail_id', true ) );
                ?>
                <a class="az-shop-child-category" href="<?php echo esc_url( $az_child_link ); ?>">
                  <span class="az-shop-child-category__media">
                    <?php if ( $az_child_thumbnail_id ) : ?>
                      <?php echo wp_get_attachment_image( $az_child_thumbnail_id, 'thumbnail', false, array( 'loading' => 'lazy' ) ); ?>
                    <?php else : ?>
                      <span aria-hidden="true"><?php echo esc_html( function_exists( 'mb_substr' ) ? mb_substr( $az_child_category->name, 0, 1 ) : substr( $az_child_category->name, 0, 1 ) ); ?></span>
                    <?php endif; ?>
                  </span>
                  <strong><?php echo esc_html( $az_child_category->name ); ?></strong>
                </a>
              <?php endforeach; ?>
            </nav>
          <?php endif; ?>
        </section>
      <?php endif; ?>
      <?php if ( $az_woo_sidebar_filters ) : ?>
        <section class="az-shop-top-filters" aria-labelledby="az-shop-top-filters-title">
          <div class="az-shop-top-filters__header">
            <div><span aria-hidden="true"></span><h2 id="az-shop-top-filters-title">Bộ lọc sản phẩm</h2></div>
            <?php if ( $az_woo_has_active_filters ) : ?><a class="az-shop-filters__clear" href="<?php echo esc_url( az_shop_clear_filter_url( $filter_url ) ); ?>">Xóa bộ lọc</a><?php endif; ?>
          </div>
          <div class="az-shop-top-filters__grid">
            <?php foreach ( $az_woo_sidebar_filters as $az_woo_sidebar_filter ) : ?>
              <?php if ( 'price' === $az_woo_sidebar_filter['kind'] ) : ?>
                <details class="az-shop-filter-dropdown az-shop-filter-dropdown--price">
                  <summary><?php echo esc_html( $az_woo_sidebar_filter['label'] ); ?><span aria-hidden="true"></span></summary>
                  <div class="az-shop-filter-dropdown__panel">
                    <form class="az-shop-price-filter" action="<?php echo esc_url( $filter_url ); ?>" method="get">
                    <?php foreach ( $_GET as $az_woo_query_name => $az_woo_query_value ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
                      <?php
                      $az_woo_query_name = sanitize_key( $az_woo_query_name );
                      if ( ! is_string( $az_woo_query_value ) || in_array( $az_woo_query_name, array( 'min_price', 'max_price', 'paged', 'product-page', 'add-to-cart' ), true ) ) { continue; }
                      ?>
                      <input type="hidden" name="<?php echo esc_attr( $az_woo_query_name ); ?>" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $az_woo_query_value ) ) ); ?>">
                    <?php endforeach; ?>
                    <div class="az-shop-price-filter__fields">
                      <label><span>Từ</span><input type="number" name="min_price" min="0" step="1" inputmode="decimal" placeholder="0" value="<?php echo esc_attr( $az_woo_sidebar_filter['min'] ); ?>"></label>
                      <i aria-hidden="true">–</i>
                      <label><span>Đến</span><input type="number" name="max_price" min="0" step="1" inputmode="decimal" placeholder="Không giới hạn" value="<?php echo esc_attr( $az_woo_sidebar_filter['max'] ); ?>"></label>
                    </div>
                    <button type="submit">Lọc giá</button>
                    </form>
                  </div>
                </details>
              <?php else : ?>
                <details class="az-shop-filter-dropdown">
                  <summary><?php echo esc_html( $az_woo_sidebar_filter['label'] ); ?><span aria-hidden="true"></span></summary>
                  <ul>
                    <?php foreach ( $az_woo_sidebar_filter['terms'] as $az_woo_filter_term ) : ?>
                      <?php
                      $az_woo_filter_selected = in_array( $az_woo_filter_term->slug, $az_woo_sidebar_filter['selected'], true );
                      $az_woo_filter_term_url = az_shop_term_filter_url( $filter_url, $az_woo_sidebar_filter['taxonomy'], $az_woo_filter_term->slug, $az_woo_sidebar_filter['kind'] );
                      ?>
                      <li><a class="az-shop-filter-link<?php echo $az_woo_filter_selected ? ' is-selected' : ''; ?>" href="<?php echo esc_url( $az_woo_filter_term_url ); ?>"<?php echo $az_woo_filter_selected ? ' aria-current="true"' : ''; ?>><span class="az-shop-filter-check" aria-hidden="true"></span><span class="az-shop-filter-name"><?php echo esc_html( $az_woo_filter_term->name ); ?></span><small><?php echo esc_html( number_format_i18n( (int) $az_woo_filter_term->count ) ); ?></small></a></li>
                    <?php endforeach; ?>
                  </ul>
                </details>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
      <?php if ( have_posts() ) : ?>
        <div class="az-shop-grid">
          <?php
          while ( have_posts() ) :
              the_post();
              $product = wc_get_product( get_the_ID() );
              if ( ! $product ) {
                  continue;
              }
              ?>
              <?php
              if ( function_exists( 'az_render_product_card' ) ) {
                  az_render_product_card(
                      $product,
                      array(
                          'context'      => 'shop',
                          'show_actions' => $az_woo_show_actions,
                      )
                  );
              }
              ?>
          <?php endwhile; ?>
        </div>
        <?php woocommerce_pagination(); ?>
      <?php else : ?>
        <div class="az-shop-empty">
          <h2>Chưa có sản phẩm phù hợp</h2>
          <p>Vui lòng thử lại với từ khóa hoặc bộ lọc khác.</p>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php get_footer(); ?>
