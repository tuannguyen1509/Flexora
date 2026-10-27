<?php
/**
 * Small compatibility layer for Flatsome shortcodes retained in legacy content.
 *
 * @package AZ_AI_Theme
 */
defined( 'ABSPATH' ) || exit;

function az_flatsome_compat_enabled() {
    return (bool) az_get_option( 'compatibility.flatsome_shortcodes', 1 );
}

function az_flatsome_compat_classes( $value ) {
    $classes = preg_split( '/\s+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY );
    return implode( ' ', array_filter( array_map( 'sanitize_html_class', $classes ) ) );
}

function az_flatsome_compat_box_value( $value ) {
    $parts = preg_split( '/\s+/', trim( (string) $value ), -1, PREG_SPLIT_NO_EMPTY );
    if ( ! $parts || count( $parts ) > 4 ) { return ''; }
    foreach ( $parts as $part ) {
        if ( ! preg_match( '/^-?(?:\d+|\d*\.\d+)(?:px|em|rem|%|vh|vw)?$/i', $part ) ) { return ''; }
    }
    return implode( ' ', $parts );
}

function az_flatsome_compat_section_shortcode( $attributes, $content = '' ) {
    $attributes = shortcode_atts( array( 'class' => '', 'id' => '', 'padding' => '', 'margin' => '', 'bg_color' => '' ), $attributes, 'section' );
    $classes = trim( 'az-legacy-section ' . az_flatsome_compat_classes( $attributes['class'] ) );
    $styles = array();
    $padding = az_flatsome_compat_box_value( $attributes['padding'] );
    $margin = az_flatsome_compat_box_value( $attributes['margin'] );
    if ( $padding ) { $styles[] = 'padding:' . $padding; }
    if ( $margin ) { $styles[] = 'margin:' . $margin; }
    if ( sanitize_hex_color( $attributes['bg_color'] ) ) { $styles[] = 'background-color:' . sanitize_hex_color( $attributes['bg_color'] ); }
    return '<section' . ( $attributes['id'] ? ' id="' . esc_attr( sanitize_html_class( $attributes['id'] ) ) . '"' : '' ) . ' class="' . esc_attr( $classes ) . '"' . ( $styles ? ' style="' . esc_attr( implode( ';', $styles ) ) . '"' : '' ) . '>' . do_shortcode( $content ) . '</section>';
}

function az_flatsome_compat_row_shortcode( $attributes, $content = '' ) {
    $attributes = shortcode_atts( array( 'class' => '', 'id' => '' ), $attributes, 'row' );
    $classes = trim( 'az-legacy-row ' . az_flatsome_compat_classes( $attributes['class'] ) );
    return '<div' . ( $attributes['id'] ? ' id="' . esc_attr( sanitize_html_class( $attributes['id'] ) ) . '"' : '' ) . ' class="' . esc_attr( $classes ) . '">' . do_shortcode( $content ) . '</div>';
}

function az_flatsome_compat_col_shortcode( $attributes, $content = '' ) {
    $attributes = shortcode_atts( array( 'span' => 12, 'span__sm' => 12, 'class' => '' ), $attributes, 'col' );
    $span = max( 1, min( 12, absint( $attributes['span'] ) ) );
    $span_small = max( 1, min( 12, absint( $attributes['span__sm'] ) ) );
    $width = round( $span / 12 * 100, 6 );
    $width_small = round( $span_small / 12 * 100, 6 );
    $classes = trim( 'az-legacy-col ' . az_flatsome_compat_classes( $attributes['class'] ) );
    return '<div class="' . esc_attr( $classes ) . '" style="--az-legacy-col-width:' . esc_attr( $width ) . '%;--az-legacy-col-sm-width:' . esc_attr( $width_small ) . '%">' . do_shortcode( $content ) . '</div>';
}

function az_flatsome_compat_filter_base_url() {
    $queried = get_queried_object();
    if ( $queried instanceof WP_Term ) {
        $url = get_term_link( $queried );
        if ( ! is_wp_error( $url ) ) { return $url; }
    }
    if ( function_exists( 'is_shop' ) && is_shop() && function_exists( 'wc_get_page_permalink' ) ) {
        return wc_get_page_permalink( 'shop' );
    }
    $url = get_permalink();
    return $url ?: home_url( '/' );
}

function az_flatsome_compat_filter_url( $base_url, $taxonomy, $term_slug, $kind ) {
    $attribute = function_exists( 'wc_attribute_taxonomy_slug' ) ? wc_attribute_taxonomy_slug( $taxonomy ) : preg_replace( '/^pa_/', '', $taxonomy );
    $filter_key = 'taxonomy' === $kind ? 'az_filter_' . sanitize_key( $taxonomy ) : 'filter_' . sanitize_key( $attribute );
    $query_type_key = 'attribute' === $kind ? 'query_type_' . sanitize_key( $attribute ) : '';
    $query_args = array();
    foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( is_string( $value ) && ! in_array( sanitize_key( $key ), array( 'paged', 'product-page', 'add-to-cart' ), true ) ) {
            $query_args[ sanitize_key( $key ) ] = sanitize_text_field( wp_unslash( $value ) );
        }
    }
    $selected = array_filter( array_map( 'sanitize_title', explode( ',', (string) ( $query_args[ $filter_key ] ?? '' ) ) ) );
    $selected = in_array( $term_slug, $selected, true )
        ? array_values( array_diff( $selected, array( $term_slug ) ) )
        : array_merge( $selected, array( sanitize_title( $term_slug ) ) );
    if ( $selected ) {
        $query_args[ $filter_key ] = implode( ',', array_unique( $selected ) );
        if ( $query_type_key ) { $query_args[ $query_type_key ] = 'or'; }
    } else {
        unset( $query_args[ $filter_key ] );
        if ( $query_type_key ) { unset( $query_args[ $query_type_key ] ); }
    }
    return add_query_arg( $query_args, $base_url );
}

function az_flatsome_compat_shop_sidebar_fallback( $classes = '' ) {
    if ( ! class_exists( 'WooCommerce' ) ) { return ''; }
    $base_url = az_flatsome_compat_filter_base_url();
    $configured = function_exists( 'az_get_option' ) ? (array) az_get_option( 'woocommerce.sidebar_attribute_filters', array() ) : array();
    $definitions = function_exists( 'az_storefront_filter_definitions' ) ? az_storefront_filter_definitions() : array();
    ob_start();
    ?>
    <aside class="az-legacy-sidebar az-legacy-sidebar--fallback <?php echo esc_attr( $classes ); ?>">
        <section class="az-legacy-widget az-legacy-widget--categories">
            <h2 class="az-legacy-widget__title">Danh mục sản phẩm</h2>
            <ul class="az-legacy-filter-list az-legacy-category-list">
                <?php echo wp_kses_post( wp_list_categories( array( 'taxonomy' => 'product_cat', 'title_li' => '', 'hide_empty' => true, 'show_count' => true, 'echo' => false ) ) ); ?>
            </ul>
        </section>
        <?php foreach ( $configured as $filter ) : ?>
            <?php
            $key = sanitize_key( $filter['taxonomy'] ?? $filter['key'] ?? '' );
            if ( empty( $filter['enabled'] ) || empty( $definitions[ $key ] ) ) { continue; }
            $definition = $definitions[ $key ];
            ?>
            <section class="az-legacy-widget az-legacy-widget--filter">
                <h2 class="az-legacy-widget__title"><?php echo esc_html( $definition['label'] ); ?></h2>
                <?php if ( 'price' === $definition['kind'] ) : ?>
                    <form class="az-legacy-price-filter" action="<?php echo esc_url( $base_url ); ?>" method="get">
                        <?php foreach ( $_GET as $query_name => $query_value ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
                            <?php if ( is_string( $query_value ) && ! in_array( sanitize_key( $query_name ), array( 'min_price', 'max_price', 'paged', 'product-page', 'add-to-cart' ), true ) ) : ?>
                                <input type="hidden" name="<?php echo esc_attr( sanitize_key( $query_name ) ); ?>" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $query_value ) ) ); ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <div class="az-legacy-price-filter__fields">
                            <input type="number" name="min_price" min="0" step="1" placeholder="Giá từ" value="<?php echo esc_attr( isset( $_GET['min_price'] ) && is_string( $_GET['min_price'] ) ? wc_format_decimal( wp_unslash( $_GET['min_price'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>">
                            <input type="number" name="max_price" min="0" step="1" placeholder="Giá đến" value="<?php echo esc_attr( isset( $_GET['max_price'] ) && is_string( $_GET['max_price'] ) ? wc_format_decimal( wp_unslash( $_GET['max_price'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>">
                        </div>
                        <button type="submit">Lọc giá</button>
                    </form>
                <?php else : ?>
                    <?php
                    $taxonomy = $definition['taxonomy'];
                    $terms = taxonomy_exists( $taxonomy ) ? get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) ) : array();
                    ?>
                    <?php if ( ! is_wp_error( $terms ) && $terms ) : ?>
                        <ul class="az-legacy-filter-list">
                            <?php foreach ( $terms as $term ) : ?>
                                <?php $term_url = az_flatsome_compat_filter_url( $base_url, $taxonomy, $term->slug, $definition['kind'] ); ?>
                                <li><a href="<?php echo esc_url( $term_url ); ?>"><span><?php echo esc_html( $term->name ); ?></span><small><?php echo esc_html( number_format_i18n( $term->count ) ); ?></small></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </aside>
    <?php
    return trim( ob_get_clean() );
}

function az_flatsome_compat_sidebar_shortcode( $attributes ) {
    $attributes = shortcode_atts( array( 'id' => 'shop-sidebar', 'class' => '' ), $attributes, 'ux_sidebar' );
    $sidebar_id = sanitize_key( $attributes['id'] );
    $classes = az_flatsome_compat_classes( $attributes['class'] );
    $sidebar_ids = 'shop-sidebar' === $sidebar_id ? array( 'shop-sidebar', 'sidebar-shop' ) : array( $sidebar_id );
    foreach ( array_filter( $sidebar_ids ) as $registered_sidebar_id ) {
        if ( ! is_active_sidebar( $registered_sidebar_id ) ) { continue; }
        ob_start();
        dynamic_sidebar( $registered_sidebar_id );
        return '<aside class="az-legacy-sidebar ' . esc_attr( $classes ) . '">' . ob_get_clean() . '</aside>';
    }
    return 'shop-sidebar' === $sidebar_id ? az_flatsome_compat_shop_sidebar_fallback( $classes ) : '';
}

function az_flatsome_compat_products_query_filters( $query_args ) {
    $configured = function_exists( 'az_get_option' ) ? (array) az_get_option( 'woocommerce.sidebar_attribute_filters', array() ) : array();
    $definitions = function_exists( 'az_storefront_filter_definitions' ) ? az_storefront_filter_definitions() : array();
    $tax_query = isset( $query_args['tax_query'] ) && is_array( $query_args['tax_query'] ) ? $query_args['tax_query'] : array();
    $meta_query = isset( $query_args['meta_query'] ) && is_array( $query_args['meta_query'] ) ? $query_args['meta_query'] : array();

    foreach ( $configured as $filter ) {
        $key = sanitize_key( $filter['taxonomy'] ?? $filter['key'] ?? '' );
        if ( empty( $filter['enabled'] ) || empty( $definitions[ $key ] ) ) { continue; }
        $definition = $definitions[ $key ];
        if ( 'price' === $definition['kind'] ) {
            $min = isset( $_GET['min_price'] ) && is_string( $_GET['min_price'] ) ? (float) wc_format_decimal( wp_unslash( $_GET['min_price'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $max = isset( $_GET['max_price'] ) && is_string( $_GET['max_price'] ) ? (float) wc_format_decimal( wp_unslash( $_GET['max_price'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ( $min > 0 || $max > 0 ) {
                $meta_query[] = array(
                    'key'     => '_price',
                    'value'   => array( $min > 0 ? $min : 0, $max > 0 ? $max : PHP_INT_MAX ),
                    'compare' => 'BETWEEN',
                    'type'    => 'DECIMAL(20,4)',
                );
            }
            continue;
        }
        $taxonomy = $definition['taxonomy'];
        $attribute = function_exists( 'wc_attribute_taxonomy_slug' ) ? wc_attribute_taxonomy_slug( $taxonomy ) : preg_replace( '/^pa_/', '', $taxonomy );
        $query_key = 'taxonomy' === $definition['kind'] ? 'az_filter_' . $taxonomy : 'filter_' . sanitize_key( $attribute );
        if ( ! isset( $_GET[ $query_key ] ) || ! is_string( $_GET[ $query_key ] ) ) { continue; } // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $terms = array_values( array_filter( array_map( 'sanitize_title', explode( ',', sanitize_text_field( wp_unslash( $_GET[ $query_key ] ) ) ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( $terms ) {
            $tax_query[] = array( 'taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $terms, 'operator' => 'IN' );
        }
    }
    if ( $tax_query ) {
        if ( count( $tax_query ) > 1 ) { $tax_query['relation'] = 'AND'; }
        $query_args['tax_query'] = $tax_query;
    }
    if ( $meta_query ) { $query_args['meta_query'] = $meta_query; }
    return $query_args;
}

function az_flatsome_compat_products_shortcode( $attributes ) {
    if ( ! class_exists( 'WooCommerce' ) || ! shortcode_exists( 'products' ) ) { return ''; }
    $attributes = shortcode_atts( array( 'columns' => 3, 'cat' => '', 'limit' => 12, 'orderby' => 'date', 'order' => 'DESC', 'class' => '' ), $attributes, 'ux_products' );
    $columns = max( 1, min( 6, absint( $attributes['columns'] ) ) );
    $limit = max( 1, min( 48, absint( $attributes['limit'] ) ) );
    $category = sanitize_title( $attributes['cat'] );
    if ( absint( $attributes['cat'] ) ) {
        $term = get_term( absint( $attributes['cat'] ), 'product_cat' );
        $category = $term && ! is_wp_error( $term ) ? $term->slug : '';
    }
    $shortcode = '[products limit="' . $limit . '" columns="' . $columns . '" orderby="' . esc_attr( sanitize_key( $attributes['orderby'] ) ) . '" order="' . ( 'ASC' === strtoupper( $attributes['order'] ) ? 'ASC' : 'DESC' ) . '"' . ( $category ? ' category="' . esc_attr( $category ) . '"' : '' ) . ']';
    add_filter( 'woocommerce_shortcode_products_query', 'az_flatsome_compat_products_query_filters' );
    $products = do_shortcode( $shortcode );
    remove_filter( 'woocommerce_shortcode_products_query', 'az_flatsome_compat_products_query_filters' );
    return '<div class="az-legacy-products ' . esc_attr( az_flatsome_compat_classes( $attributes['class'] ) ) . '">' . $products . '</div>';
}

function az_flatsome_compat_shortcode_truthy( $value ) {
    return ! in_array( strtolower( trim( (string) $value ) ), array( '', '0', 'false', 'no', 'off', 'none' ), true );
}

function az_flatsome_compat_blog_image_height( $value ) {
    $value = trim( (string) $value );
    return preg_match( '/^\d+(?:\.\d+)?(?:%|px|rem|vh)?$/', $value ) ? $value : '65%';
}

function az_flatsome_compat_blog_posts_shortcode( $attributes ) {
    $attributes = shortcode_atts(
        array(
            'style'       => 'normal',
            'type'        => 'row',
            'columns'     => 3,
            'columns__md' => 2,
            'columns__sm' => 1,
            'cat'         => '',
            'posts'       => 6,
            'show_date'   => 'text',
            'excerpt'     => 'true',
            'show_category' => 'text',
            'comments'    => 'false',
            'image_height' => '65%',
            'text_align'  => 'left',
            'class'       => '',
        ),
        $attributes,
        'blog_posts'
    );

    $columns = max( 1, min( 6, absint( $attributes['columns'] ) ) );
    $columns_md = max( 1, min( 4, absint( $attributes['columns__md'] ) ) );
    $columns_sm = max( 1, min( 2, absint( $attributes['columns__sm'] ) ) );
    $post_count = max( 1, min( 50, absint( $attributes['posts'] ) ) );
    $style = in_array( sanitize_key( $attributes['style'] ), array( 'normal', 'shade' ), true ) ? sanitize_key( $attributes['style'] ) : 'normal';
    $type = in_array( sanitize_key( $attributes['type'] ), array( 'row', 'grid' ), true ) ? sanitize_key( $attributes['type'] ) : 'row';
    $align = in_array( sanitize_key( $attributes['text_align'] ), array( 'left', 'center', 'right' ), true ) ? sanitize_key( $attributes['text_align'] ) : 'left';
    $query_args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $post_count,
        'ignore_sticky_posts' => true,
    );
    $categories = array_filter( array_map( 'trim', explode( ',', (string) $attributes['cat'] ) ) );
    if ( $categories ) {
        $category_ids = array_filter( array_map( 'absint', $categories ) );
        if ( $category_ids ) {
            $query_args['category__in'] = $category_ids;
        } else {
            $query_args['category_name'] = implode( ',', array_map( 'sanitize_title', $categories ) );
        }
    }
    $posts_query = new WP_Query( $query_args );
    if ( ! $posts_query->have_posts() ) { return ''; }

    $show_date = az_flatsome_compat_shortcode_truthy( $attributes['show_date'] );
    $show_category = az_flatsome_compat_shortcode_truthy( $attributes['show_category'] );
    $show_excerpt = az_flatsome_compat_shortcode_truthy( $attributes['excerpt'] );
    $show_comments = az_flatsome_compat_shortcode_truthy( $attributes['comments'] );
    $classes = trim( 'az-legacy-blog-posts az-legacy-blog-posts--' . $style . ' az-legacy-blog-posts--' . $type . ' az-legacy-blog-posts--align-' . $align . ' ' . az_flatsome_compat_classes( $attributes['class'] ) );
    $styles = '--az-legacy-blog-columns:' . $columns . ';--az-legacy-blog-columns-md:' . $columns_md . ';--az-legacy-blog-columns-sm:' . $columns_sm . ';--az-legacy-blog-image-height:' . az_flatsome_compat_blog_image_height( $attributes['image_height'] ) . ';';

    ob_start();
    ?>
    <section class="<?php echo esc_attr( $classes ); ?>" style="<?php echo esc_attr( $styles ); ?>">
        <div class="az-legacy-blog-posts__grid">
            <?php while ( $posts_query->have_posts() ) : $posts_query->the_post(); ?>
                <?php $post_categories = get_the_category(); ?>
                <article <?php post_class( 'az-legacy-blog-posts__card' ); ?>>
                    <a class="az-legacy-blog-posts__image" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
                        <?php else : ?>
                            <span class="az-legacy-blog-posts__placeholder" aria-hidden="true"></span>
                        <?php endif; ?>
                    </a>
                    <div class="az-legacy-blog-posts__body">
                        <?php if ( $show_category && $post_categories ) : ?>
                            <a class="az-legacy-blog-posts__category" href="<?php echo esc_url( get_category_link( $post_categories[0]->term_id ) ); ?>"><?php echo esc_html( $post_categories[0]->name ); ?></a>
                        <?php endif; ?>
                        <?php if ( $show_date || $show_comments ) : ?>
                            <div class="az-legacy-blog-posts__meta">
                                <?php if ( $show_date ) : ?><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></time><?php endif; ?>
                                <?php if ( $show_comments ) : ?><span><?php echo esc_html( number_format_i18n( get_comments_number() ) ); ?> bình luận</span><?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <h3 class="az-legacy-blog-posts__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <?php if ( $show_excerpt ) : ?><p class="az-legacy-blog-posts__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p><?php endif; ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    </section>
    <?php
    wp_reset_postdata();
    return trim( ob_get_clean() );
}

function az_flatsome_compat_register_shortcodes() {
    if ( ! az_flatsome_compat_enabled() ) { return; }
    $shortcodes = array(
        'section'    => 'az_flatsome_compat_section_shortcode',
        'row'        => 'az_flatsome_compat_row_shortcode',
        'col'        => 'az_flatsome_compat_col_shortcode',
        'ux_sidebar' => 'az_flatsome_compat_sidebar_shortcode',
        'ux_products'=> 'az_flatsome_compat_products_shortcode',
        'blog_posts' => 'az_flatsome_compat_blog_posts_shortcode',
    );
    foreach ( $shortcodes as $tag => $callback ) {
        if ( ! shortcode_exists( $tag ) ) { add_shortcode( $tag, $callback ); }
    }
    if ( false === has_filter( 'term_description', 'do_shortcode' ) ) {
        add_filter( 'term_description', 'do_shortcode', 11 );
    }
}
add_action( 'init', 'az_flatsome_compat_register_shortcodes', 20 );

function az_flatsome_compat_assets() {
    if ( ! az_flatsome_compat_enabled() ) { return; }
    $style = get_template_directory() . '/css/flatsome-compat.css';
    wp_enqueue_style( 'az-flatsome-compat', get_template_directory_uri() . '/css/flatsome-compat.css', array( 'az-container-sync' ), filemtime( $style ) );
    if ( az_get_option( 'compatibility.flatsome_expand_content', 1 ) ) {
        $script = get_template_directory() . '/js/az-flatsome-compat.js';
        wp_enqueue_script( 'az-flatsome-compat', get_template_directory_uri() . '/js/az-flatsome-compat.js', array(), filemtime( $script ), true );
    }
}
add_action( 'wp_enqueue_scripts', 'az_flatsome_compat_assets', 25 );
