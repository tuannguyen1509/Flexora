<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* -- Front page mapped category helpers -- */
if ( ! function_exists( 'az_home_category_sections_config' ) ) {
    function az_home_category_sections_config() {
        $raw_sections = json_decode( (string) get_option( 'az_home_category_sections', '' ), true );
        $sections     = array();

        if ( is_array( $raw_sections ) ) {
            foreach ( $raw_sections as $section ) {
                $category_id = ( 'all' === (string) ( $section['category_id'] ?? '' ) ) ? 'all' : absint( $section['category_id'] ?? 0 );
                if ( ! $category_id ) {
                    continue;
                }

                $tab_ids = array_values(
                    array_unique(
                        array_filter(
                            array_map( 'absint', (array) ( $section['tab_ids'] ?? array() ) )
                        )
                    )
                );

                $sections[] = array(
                    'category_id' => $category_id,
                    'title'       => sanitize_text_field( $section['title'] ?? '' ),
                    'tab_ids'     => $tab_ids,
                    'visible'     => ! isset( $section['visible'] ) || '0' !== (string) $section['visible'],
                );
            }
        }

        if ( ! empty( $sections ) ) {
            return $sections;
        }

        $legacy_ids = array_filter( array_map( 'absint', preg_split( '/[\r\n,]+/', (string) get_option( 'az_home_featured_category_ids', '' ) ) ) );
        foreach ( array_values( array_unique( $legacy_ids ) ) as $legacy_id ) {
            $term = get_term( $legacy_id, 'product_cat' );
            if ( $term && ! is_wp_error( $term ) ) {
                $sections[] = array(
                    'category_id' => (int) $legacy_id,
                    'title'       => $term->name,
                    'tab_ids'     => array( (int) $legacy_id ),
                    'visible'     => true,
                );
            }
        }

        return $sections;
    }
}

if ( ! function_exists( 'az_home_category_has_products' ) ) {
    function az_home_category_has_products( $term_id ) {
        if ( 'all' === (string) $term_id ) {
            return post_type_exists( 'product' ) && (bool) wp_count_posts( 'product' )->publish;
        }

        if ( ! taxonomy_exists( 'product_cat' ) || ! post_type_exists( 'product' ) ) {
            return false;
        }

        $products = new WP_Query(
            array(
                'post_type'              => 'product',
                'post_status'            => 'publish',
                'posts_per_page'         => 1,
                'fields'                 => 'ids',
                'ignore_sticky_posts'    => true,
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'tax_query'              => array(
                    array(
                        'taxonomy'         => 'product_cat',
                        'field'            => 'term_id',
                        'terms'            => (int) $term_id,
                        'include_children' => true,
                    ),
                ),
            )
        );

        $has_products = $products->have_posts();
        wp_reset_postdata();

        return $has_products;
    }
}

if ( ! function_exists( 'az_home_all_products_term' ) ) {
    function az_home_all_products_term() {
        return (object) array(
            'term_id' => 'all',
            'name'    => 'Tất cả sản phẩm',
            'slug'    => 'tat-ca-san-pham',
        );
    }
}

if ( ! function_exists( 'az_home_selected_category_ids' ) ) {
    function az_home_selected_category_ids() {
        $configured_sections = function_exists( 'az_home_category_sections_config' ) ? az_home_category_sections_config() : array();
        if ( ! empty( $configured_sections ) ) {
            $visible_ids = array();
            foreach ( $configured_sections as $section ) {
                if ( empty( $section['visible'] ) ) {
                    continue;
                }
                if ( 'all' === (string) $section['category_id'] ) {
                    if ( az_home_category_has_products( 'all' ) ) {
                        $visible_ids[] = 'all';
                    }
                    continue;
                }
                $term = get_term( (int) $section['category_id'], 'product_cat' );
                if ( $term && ! is_wp_error( $term ) && az_home_category_has_products( $term->term_id ) ) {
                    $visible_ids[] = (int) $section['category_id'];
                }
            }

            return array_values( array_unique( $visible_ids ) );
        }

        $ids = array_filter( array_map( 'absint', preg_split( '/[\r\n,]+/', (string) get_option( 'az_home_featured_category_ids', '' ) ) ) );

        if ( ! empty( $ids ) ) {
            $visible_ids = array();
            foreach ( array_values( array_unique( $ids ) ) as $term_id ) {
                $term = get_term( $term_id, 'product_cat' );
                if ( $term && ! is_wp_error( $term ) && az_home_category_has_products( $term_id ) ) {
                    $visible_ids[] = (int) $term_id;
                }
            }
            return $visible_ids;
        }

        return array();
    }
}

if ( ! function_exists( 'az_home_section_config_for_term' ) ) {
    function az_home_section_config_for_term( $term_id ) {
        foreach ( az_home_category_sections_config() as $section ) {
            if ( (string) $section['category_id'] === (string) $term_id ) {
                return $section;
            }
        }

        return array();
    }
}

if ( ! function_exists( 'az_home_term_image' ) ) {
    function az_home_term_image( $term, $size = 'medium' ) {
        $image_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );

        if ( ! $image_id ) {
            $image_id = (int) get_term_meta( $term->term_id, 'cp_cat_image_id', true );
        }

        if ( $image_id ) {
            return wp_get_attachment_image( $image_id, $size, false, array( 'loading' => 'lazy', 'alt' => $term->name ) );
        }

        $logo_id = (int) get_theme_mod( 'custom_logo' );

        if ( $logo_id ) {
            return wp_get_attachment_image( $logo_id, $size, false, array( 'loading' => 'lazy', 'alt' => $term->name ) );
        }

        return '';
    }
}

if ( ! function_exists( 'az_storefront_show_prices' ) ) {
    function az_storefront_show_prices() {
        return function_exists( 'az_get_option' ) ? (bool) az_get_option( 'woocommerce.show_product_price', 1 ) : true;
    }
}

if ( ! function_exists( 'az_storefront_show_purchase_actions' ) ) {
    function az_storefront_show_purchase_actions() {
        return function_exists( 'az_get_option' ) ? (bool) az_get_option( 'woocommerce.show_purchase_actions', 1 ) : true;
    }
}

if ( ! function_exists( 'az_storefront_contact_phone' ) ) {
    function az_storefront_contact_phone() {
        $phone = trim( (string) get_option( 'az_contact_phone', get_theme_mod( 'az_contact_phone', '' ) ) );

        return $phone;
    }
}

if ( ! function_exists( 'az_storefront_contact_price_html' ) ) {
    function az_storefront_contact_price_html() {
        return __( 'Liên hệ', 'az-theme' );
    }
}

if ( ! function_exists( 'az_storefront_product_price_html' ) ) {
    function az_storefront_product_price_html( $product ) {
        if ( ! $product ) {
            return '';
        }

        if ( ! az_storefront_show_prices() ) {
            return az_storefront_contact_price_html();
        }

        return $product->get_price_html();
    }
}

if ( ! function_exists( 'az_storefront_product_discount_percentage' ) ) {
    /**
     * Return the largest active discount percentage for a product.
     *
     * Variable products are calculated per variation because their parent
     * sale price can be empty even when a discounted range is displayed.
     */
    function az_storefront_product_discount_percentage( $product ) {
        $product = is_a( $product, 'WC_Product' ) ? $product : ( function_exists( 'wc_get_product' ) ? wc_get_product( $product ) : null );
        if ( ! $product ) {
            return 0;
        }

        $discounts = array();
        if ( $product->is_type( 'variable' ) ) {
            $prices = $product->get_variation_prices( true );
            foreach ( (array) ( $prices['price'] ?? array() ) as $variation_id => $current_price ) {
                $regular_price = (float) ( $prices['regular_price'][ $variation_id ] ?? 0 );
                $current_price = (float) $current_price;
                if ( $regular_price > 0 && $current_price > 0 && $current_price < $regular_price ) {
                    $discounts[] = (int) round( ( ( $regular_price - $current_price ) / $regular_price ) * 100 );
                }
            }
        } elseif ( $product->is_type( 'grouped' ) ) {
            foreach ( $product->get_children() as $child_id ) {
                $child_discount = az_storefront_product_discount_percentage( $child_id );
                if ( $child_discount > 0 ) {
                    $discounts[] = $child_discount;
                }
            }
        } else {
            $regular_price = (float) $product->get_regular_price();
            $current_price = (float) $product->get_price();
            if ( $regular_price > 0 && $current_price > 0 && $current_price < $regular_price ) {
                $discounts[] = (int) round( ( ( $regular_price - $current_price ) / $regular_price ) * 100 );
            }
        }

        return $discounts ? max( $discounts ) : 0;
    }
}

if ( ! function_exists( 'az_storefront_render_product_card_actions' ) ) {
    function az_storefront_render_product_card_actions( $product ) {
        if ( ! $product || ! az_storefront_show_purchase_actions() || ! function_exists( 'woocommerce_template_loop_add_to_cart' ) ) {
            return;
        }

        $previous_product    = $GLOBALS['product'] ?? null;
        $GLOBALS['product'] = $product;
        echo '<div class="az-product-card__actions">';
        woocommerce_template_loop_add_to_cart();
        echo '</div>';

        if ( null === $previous_product ) {
            unset( $GLOBALS['product'] );
        } else {
            $GLOBALS['product'] = $previous_product;
        }
    }
}

add_filter(
    'woocommerce_get_price_html',
    function( $price_html ) {
        if ( is_admin() && ! wp_doing_ajax() ) {
            return $price_html;
        }

        return az_storefront_show_prices() ? $price_html : az_storefront_contact_price_html();
    },
    20
);

if ( ! function_exists( 'az_storefront_shop_products_per_page' ) ) {
    function az_storefront_shop_products_per_page() {
        return max( 1, min( 100, absint( az_get_option( 'woocommerce.products_per_page', 12 ) ) ) );
    }
}
add_filter( 'loop_shop_per_page', 'az_storefront_shop_products_per_page', 20 );

if ( ! function_exists( 'az_storefront_limit_shop_archive_query' ) ) {
    function az_storefront_limit_shop_archive_query( $query ) {
        if ( is_admin() || ! $query->is_main_query() || ! function_exists( 'is_shop' ) ) {
            return;
        }

        if ( is_shop() || is_product_taxonomy() ) {
            $query->set( 'posts_per_page', az_storefront_shop_products_per_page() );
        }
    }
}
add_action( 'pre_get_posts', 'az_storefront_limit_shop_archive_query', 20 );

if ( ! function_exists( 'az_render_product_card' ) ) {
    /**
     * Render the shared product card used across the storefront.
     *
     * @param WC_Product|int $product Product object or product ID.
     * @param array          $args    Card presentation arguments.
     * @return void
     */
    function az_render_product_card( $product, $args = array() ) {
        $product = is_a( $product, 'WC_Product' ) ? $product : ( function_exists( 'wc_get_product' ) ? wc_get_product( $product ) : null );
        if ( ! $product ) {
            return;
        }

        $args = wp_parse_args(
            $args,
            array(
                'context'      => 'default',
                'tag'          => 'article',
                'classes'      => array(),
                'image_size'   => 'woocommerce_thumbnail',
                'show_actions' => az_storefront_show_purchase_actions(),
            )
        );

        $tag = in_array( $args['tag'], array( 'article', 'li', 'div' ), true ) ? $args['tag'] : 'article';
        $context = sanitize_html_class( (string) $args['context'] );
        $classes = array_merge(
            array( 'az-product-card', 'az-product-card--shared', 'az-shop-card' ),
            $context ? array( 'az-product-card--' . $context ) : array(),
            array_map( 'sanitize_html_class', (array) $args['classes'] )
        );

        if ( function_exists( 'wc_get_product_class' ) ) {
            $classes = wc_get_product_class( $classes, $product );
        }

        $product_id = $product->get_id();
        $permalink = get_permalink( $product_id );
        $title = get_the_title( $product_id );
        $price_html = az_storefront_product_price_html( $product );
        $discount_percentage = az_storefront_product_discount_percentage( $product );
        ?>
        <<?php echo tag_escape( $tag ); ?> class="<?php echo esc_attr( implode( ' ', array_unique( array_filter( $classes ) ) ) ); ?>">
            <?php if ( $discount_percentage > 0 ) : ?>
                <span class="az-product-card__badge">-<?php echo esc_html( $discount_percentage ); ?>%</span>
            <?php endif; ?>
            <a class="az-product-card__image az-shop-card-img" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( $title ); ?>">
                <?php echo wp_kses_post( $product->get_image( $args['image_size'], array( 'loading' => 'lazy', 'decoding' => 'async' ) ) ); ?>
            </a>
            <div class="az-product-card__body">
                <h3 class="az-product-card__title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h3>
                <?php if ( $price_html ) : ?>
                    <strong class="az-product-card__price az-price az-price--card"><?php echo wp_kses_post( $price_html ); ?></strong>
                <?php endif; ?>
                <?php if ( $args['show_actions'] ) : ?>
                    <?php az_storefront_render_product_card_actions( $product ); ?>
                <?php endif; ?>
            </div>
        </<?php echo tag_escape( $tag ); ?>>
        <?php
    }
}

if ( ! function_exists( 'az_storefront_home_product_card' ) ) {
    function az_storefront_home_product_card( $post_id ) {
        az_render_product_card( $post_id, array( 'context' => 'home-category' ) );
    }
}

if ( ! function_exists( 'az_home_category_section' ) ) {
    function az_home_category_section( $category, $products_per_category ) {
        $is_all_products = 'all' === (string) $category->term_id;
        $section_classes = array(
            'az-container',
            'az-product-section',
            'az-product-section-' . sanitize_html_class( $category->slug ),
        );
        $is_flash_sale = false;
        if ( ! $is_all_products && ( stripos( $category->name, 'flash' ) !== false || stripos( $category->slug, 'flash' ) !== false ) ) {
            $is_flash_sale = true;
            $section_classes[] = 'az-flash-sale-section';
        }
        $flash_sale_countdown_seconds = max( 1, min( 168, (int) get_option( 'az_flash_sale_countdown_hours', 3 ) ) ) * HOUR_IN_SECONDS;
        $section_config = function_exists( 'az_home_section_config_for_term' ) ? az_home_section_config_for_term( $category->term_id ) : array();
        $section_title  = ! empty( $section_config['title'] ) ? $section_config['title'] : $category->name;
        $section_tab_terms = array();
        $configured_tab_ids = ! empty( $section_config['tab_ids'] ) ? (array) $section_config['tab_ids'] : array();
        if ( $is_all_products ) {
            $section_tab_terms = array( az_home_all_products_term() );
        } elseif ( ! empty( $configured_tab_ids ) ) {
            foreach ( $configured_tab_ids as $tab_term_id ) {
                $tab_term = get_term( $tab_term_id, 'product_cat' );
                if ( $tab_term && ! is_wp_error( $tab_term ) ) {
                    $section_tab_terms[] = $tab_term;
                }
            }
        }
        if ( empty( $section_tab_terms ) ) {
            $section_tab_terms = array( $category );
        }
        $section_tab_terms = array_values(
            array_filter(
                $section_tab_terms,
                function( $tab_term ) {
                    if ( 'all' === (string) $tab_term->term_id ) {
                        return az_home_category_has_products( 'all' );
                    }

                    return $tab_term && ! is_wp_error( $tab_term ) && az_home_category_has_products( $tab_term->term_id );
                }
            )
        );
        if ( empty( $section_tab_terms ) ) {
            return;
        }
        $section_dom_id = 'az-section-' . sanitize_key( (string) $category->term_id );
        ?>
        <section class="<?php echo esc_attr( implode( ' ', $section_classes ) ); ?>" data-az-product-tabs>
          <div class="az-section-head">
            <h2><?php echo esc_html( $section_title ); ?></h2>
            <?php if ( $is_flash_sale ) : ?>
              <div class="az-flash-countdown" data-az-flash-countdown="<?php echo esc_attr( $flash_sale_countdown_seconds ); ?>">
                <span>Kết thúc sau</span>
                <b data-countdown-hours>00</b><i>:</i><b data-countdown-minutes>00</b><i>:</i><b data-countdown-seconds>00</b>
              </div>
            <?php endif; ?>
            <?php if ( count( $section_tab_terms ) > 1 ) : ?>
              <nav class="az-section-tabs" aria-label="Danh mục sản phẩm">
                <?php foreach ( $section_tab_terms as $tab_index => $tab_term ) : ?>
                  <button type="button" class="<?php echo 0 === (int) $tab_index ? 'is-active' : ''; ?>" data-az-tab-target="<?php echo esc_attr( $section_dom_id . '-' . sanitize_key( (string) $tab_term->term_id ) ); ?>"><?php echo esc_html( function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $tab_term->name, 'UTF-8' ) : strtoupper( $tab_term->name ) ); ?></button>
                <?php endforeach; ?>
              </nav>
            <?php endif; ?>
          </div>
          <div class="az-product-tab-panels">
            <?php foreach ( $section_tab_terms as $tab_index => $tab_term ) : ?>
              <?php
              $product_query_args = array(
                  'post_type'           => 'product',
                  'post_status'         => 'publish',
                  'posts_per_page'      => $products_per_category,
                  'ignore_sticky_posts' => true,
                  'orderby'             => 'date',
                  'order'               => 'DESC',
              );

              if ( 'all' !== (string) $tab_term->term_id ) {
                  $product_query_args['tax_query'] = array(
                      array(
                          'taxonomy'         => 'product_cat',
                          'field'            => 'term_id',
                          'terms'            => (int) $tab_term->term_id,
                          'include_children' => true,
                      ),
                  );
              }

              $products = new WP_Query( $product_query_args );
              ?>
              <div id="<?php echo esc_attr( $section_dom_id . '-' . sanitize_key( (string) $tab_term->term_id ) ); ?>" class="az-product-grid az-home-product-tab-panel <?php echo 0 === (int) $tab_index ? 'is-active' : ''; ?>" <?php echo 0 === (int) $tab_index ? '' : 'hidden'; ?>>
                <?php
                if ( $products->have_posts() ) :
                    while ( $products->have_posts() ) :
                        $products->the_post();
                        az_storefront_home_product_card( get_the_ID() );
                    endwhile;
                endif;
                wp_reset_postdata();
                ?>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
        <?php
    }
}

add_action( 'wp_ajax_az_home_load_categories', 'az_home_load_categories_ajax' );
add_action( 'wp_ajax_nopriv_az_home_load_categories', 'az_home_load_categories_ajax' );
function az_home_load_categories_ajax() {
    if ( ! class_exists( 'WooCommerce' ) || ! taxonomy_exists( 'product_cat' ) || ! post_type_exists( 'product' ) ) {
        wp_send_json_error( array( 'message' => 'WooCommerce unavailable' ) );
    }

    check_ajax_referer( 'az_home_load_categories', 'nonce' );

    $offset = max( 0, (int) ( $_POST['offset'] ?? 0 ) );
    $limit  = max( 1, min( 12, (int) ( $_POST['limit'] ?? get_option( 'az_home_category_batch_size', 3 ) ) ) );
    $ids    = az_home_selected_category_ids();
    $slice  = array_slice( $ids, $offset, $limit );
    $terms  = array();

    foreach ( $slice as $term_id ) {
        if ( 'all' === (string) $term_id ) {
            $terms[] = az_home_all_products_term();
            continue;
        }

        $term = get_term( $term_id, 'product_cat' );
        if ( $term && ! is_wp_error( $term ) && (int) $term->count > 0 ) {
            $terms[] = $term;
        }
    }

    ob_start();
    $products_per_category = max( 1, min( 30, (int) get_option( 'az_home_products_per_category', 10 ) ) );

    foreach ( $terms as $category ) {
        az_home_category_section( $category, $products_per_category );
    }
    $html = ob_get_clean();
    $next = $offset + $limit;

    wp_send_json_success(
        array(
            'html'        => $html,
            'next_offset' => $next,
            'has_more'    => $next < count( $ids ),
        )
    );
}
