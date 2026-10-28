<?php
/**
 * Front page - Togismart style storefront.
 *
 * Template Name: Trang chủ
 * Template Post Type: page
 *
 * @package AZ_AI_Theme
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$az_homepage_config = az_homepage_settings();

$az_theme_img = get_template_directory_uri() . '/assets/images/banner-tbi-motion.jpg';
$az_phone     = function_exists( 'az_storefront_contact_phone' ) ? az_storefront_contact_phone() : get_option( 'az_contact_phone', '0823.73.76.79' );
$az_has_wc    = class_exists( 'WooCommerce' ) && post_type_exists( 'product' ) && taxonomy_exists( 'product_cat' );
$az_header_settings = function_exists( 'az_header_options' ) ? az_header_options() : array();
$az_category_menu_id = function_exists( 'az_product_category_menu_id' )
    ? az_product_category_menu_id( $az_header_settings['category_menu'] ?? 0 )
    : absint( $az_header_settings['category_menu'] ?? 0 );
$az_category_menu = az_homepage_enabled( 'hero' )
    ? ( $az_category_menu_id ? wp_get_nav_menu_object( $az_category_menu_id ) : wp_get_nav_menu_object( 'Danh mục sản phẩm' ) )
    : false;
$az_shop_url  = get_post_type_archive_link( 'product' ) ?: home_url( '/san-pham/' );

if ( ! function_exists( 'az_home_banner_slot' ) ) {
    function az_home_banner_slot( $slot, $fallback_img, $fallback_alt = '', $fallback_link = '' ) {
        $slot = sanitize_key( $slot );
        $fallback_alt = $fallback_alt ?: get_bloginfo( 'name' );
        $img = get_option( 'az_home_' . $slot . '_banner_img', $fallback_img ) ?: $fallback_img;
        $slides_raw = get_option( 'az_home_' . $slot . '_banner_slides', '' );
        $slides = array_values(
            array_filter(
                array_map(
                    'esc_url_raw',
                    preg_split( '/[\r\n,]+/', (string) $slides_raw )
                )
            )
        );

        if ( empty( $slides ) ) {
            $slides = array( $img ?: $fallback_img );
        }

        return array(
            'img'    => $img,
            'slides' => $slides,
            'alt'    => get_option( 'az_home_' . $slot . '_banner_alt', $fallback_alt ),
            'link'   => get_option( 'az_home_' . $slot . '_banner_link', $fallback_link ),
        );
    }
}

$az_home_banners = array(
    'main'        => az_home_banner_slot( 'main', $az_theme_img, get_bloginfo( 'name' ), $az_shop_url ),
    'mini_left'   => az_home_banner_slot( 'mini_left', $az_theme_img, 'Tiện nghi mỗi ngày', $az_shop_url ),
    'mini_right'  => az_home_banner_slot( 'mini_right', $az_theme_img, 'An tâm mỗi ngày', $az_shop_url ),
    'side_promo'  => az_home_banner_slot( 'side_promo', $az_theme_img, 'Khóa vân tay hiện đại', $az_shop_url ),
    'wide_left'   => az_home_banner_slot( 'wide_left', $az_theme_img, 'Khóa vân tay hiện đại', $az_shop_url ),
    'wide_right'  => az_home_banner_slot( 'wide_right', $az_theme_img, 'Tiêu chuẩn mới nâng tầm cuộc sống', $az_shop_url ),
);

if ( ! function_exists( 'az_home_product_card' ) ) {
    function az_home_product_card( $post_id, $compact = false ) {
        if ( function_exists( 'az_render_product_card' ) ) {
            az_render_product_card(
                $post_id,
                array(
                    'context' => $compact ? 'home-weekly' : 'home-suggested',
                )
            );
        }
    }
}

if ( ! function_exists( 'az_home_term_banner_url' ) ) {
    function az_home_term_banner_url( $term, $fallback_url ) {
        if ( ! $term || is_wp_error( $term ) || empty( $term->term_id ) ) {
            return $fallback_url;
        }

        $image_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
        if ( ! $image_id ) {
            $image_id = (int) get_term_meta( $term->term_id, 'cp_cat_image_id', true );
        }

        $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : '';

        return $image_url ?: $fallback_url;
    }
}

if ( ! function_exists( 'az_home_render_product_panel' ) ) {
    function az_home_render_product_panel( $panel_id, $term_id, $products_per_page, $active = false ) {
        $query_args = array(
            'post_type'           => 'product',
            'post_status'         => 'publish',
            'posts_per_page'      => $products_per_page,
            'ignore_sticky_posts' => true,
            'orderby'             => 'date',
            'order'               => 'DESC',
        );

        if ( 'all' !== (string) $term_id ) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy'         => 'product_cat',
                    'field'            => 'term_id',
                    'terms'            => (int) $term_id,
                    'include_children' => true,
                ),
            );
        }

        $products = new WP_Query( $query_args );
        ?>
        <div id="<?php echo esc_attr( $panel_id ); ?>" class="az-product-row az-product-tab-panel <?php echo $active ? 'is-active' : ''; ?>" <?php echo $active ? '' : 'hidden'; ?>>
          <?php
          if ( $products->have_posts() ) :
              while ( $products->have_posts() ) :
                  $products->the_post();
                  az_home_product_card( get_the_ID() );
              endwhile;
          endif;
          wp_reset_postdata();
          ?>
        </div>
        <?php
    }
}

$az_categories = array();
if ( $az_has_wc && ( az_homepage_enabled( 'hero' ) || az_homepage_enabled( 'category-products' ) || az_homepage_enabled( 'featured-categories' ) ) ) {
    $az_categories = get_terms(
        array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => 0,
            'orderby'    => 'menu_order',
            'order'      => 'ASC',
            'number'     => 8,
        )
    );
    if ( is_wp_error( $az_categories ) || empty( $az_categories ) ) {
        $az_categories = get_terms(
            array(
                'taxonomy'   => 'product_cat',
                'hide_empty' => false,
                'parent'     => 0,
                'orderby'    => 'name',
                'number'     => 8,
            )
        );
    }
}
$az_categories = is_wp_error( $az_categories ) ? array() : $az_categories;

$az_weekly_enabled = az_homepage_enabled( 'weekly' );
$az_weekly_title   = $az_homepage_config['weekly']['title'];
$az_weekly_count   = $az_homepage_config['weekly']['limit'];
$az_weekly_cat_id  = absint( get_option( 'az_home_weekly_category_id', 0 ) );
$az_weekly_orderby = get_option( 'az_home_weekly_orderby', 'date' );
$az_weekly_args    = array(
    'post_type'           => 'product',
    'post_status'         => 'publish',
    'posts_per_page'      => $az_weekly_count,
    'ignore_sticky_posts' => true,
    'orderby'             => 'date',
    'order'               => 'DESC',
);
if ( 'sales' === $az_weekly_orderby ) {
    $az_weekly_args['meta_key'] = 'total_sales';
    $az_weekly_args['orderby']  = 'meta_value_num';
} elseif ( 'rand' === $az_weekly_orderby ) {
    $az_weekly_args['orderby'] = 'rand';
}
if ( $az_weekly_cat_id ) {
    $az_weekly_args['tax_query'] = array(
        array(
            'taxonomy'         => 'product_cat',
            'field'            => 'term_id',
            'terms'            => $az_weekly_cat_id,
            'include_children' => true,
        ),
    );
}
$az_featured_products = ( $az_has_wc && $az_weekly_enabled ) ? new WP_Query( $az_weekly_args ) : null;
$az_suggested_enabled = az_homepage_enabled( 'suggested' );
$az_suggested_title   = $az_homepage_config['suggested']['title'];
$az_suggested_count   = $az_homepage_config['suggested']['limit'];
$az_suggested_cat_id  = absint( get_option( 'az_home_suggested_category_id', 0 ) );
$az_suggested_orderby = get_option( 'az_home_suggested_orderby', 'rand' );
$az_suggested_args    = array(
    'post_type'           => 'product',
    'post_status'         => 'publish',
    'posts_per_page'      => $az_suggested_count,
    'ignore_sticky_posts' => true,
    'orderby'             => 'rand',
);
if ( 'date' === $az_suggested_orderby ) {
    $az_suggested_args['orderby'] = 'date';
    $az_suggested_args['order']   = 'DESC';
} elseif ( 'sales' === $az_suggested_orderby ) {
    $az_suggested_args['meta_key'] = 'total_sales';
    $az_suggested_args['orderby']  = 'meta_value_num';
    $az_suggested_args['order']    = 'DESC';
}
if ( $az_suggested_cat_id ) {
    $az_suggested_args['tax_query'] = array(
        array(
            'taxonomy'         => 'product_cat',
            'field'            => 'term_id',
            'terms'            => $az_suggested_cat_id,
            'include_children' => true,
        ),
    );
}
$az_suggested = ( $az_has_wc && $az_suggested_enabled ) ? new WP_Query( $az_suggested_args ) : null;
$az_news_category = az_homepage_enabled( 'news' ) ? get_category_by_slug( 'tin-tuc' ) : false;
if ( az_homepage_enabled( 'news' ) && ! $az_news_category ) {
    $az_news_category = get_term_by( 'name', 'Tin tức', 'category' );
}
$az_news_args = array(
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'posts_per_page'      => $az_homepage_config['news']['limit'],
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
);
if ( $az_news_category && ! is_wp_error( $az_news_category ) ) {
    $az_news_args['cat'] = (int) $az_news_category->term_id;
}
$az_news_query = az_homepage_enabled( 'news' ) ? new WP_Query( $az_news_args ) : null;
$az_home_page_id = (int) get_queried_object_id();
?>

<main id="az-main-content" tabindex="-1" class="az-main az-home az-home--piano az-landing-page">
  <?php foreach ( az_homepage_ordered_sections() as $az_homepage_id ) : ?>
    <?php
    if ( 'hero' === $az_homepage_id && az_shared_banner_active() ) { continue; }
    $az_homepage_section = $az_homepage_config[ $az_homepage_id ];
    // IDs come from the module allowlist, never from request data.
    require get_template_directory() . '/inc/modules/homepage/templates/' . $az_homepage_id . '.php';
    ?>
  <?php endforeach; ?>
</main>
<?php get_footer(); ?>
