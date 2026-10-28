<?php
/** Homepage rendering helpers. All data remains in WordPress/WooCommerce. */
defined( 'ABSPATH' ) || exit;

function az_child_home_menu( $class = 'az-steel-menu' ) {
    az_child_steel_main_menu( $class );
}

function az_child_home_shop_url() {
    return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/san-pham/' );
}

function az_child_home_collection_url( $ids ) {
    foreach ( (array) $ids as $id ) {
        if ( ! absint( $id ) ) { continue; }
        $url = get_term_link( absint( $id ), 'product_cat' );
        if ( ! is_wp_error( $url ) ) { return $url; }
    }
    return az_child_home_shop_url();
}

function az_child_home_heading( $title, $url ) {
    ?>
    <div class="az-home-section-heading"><h2><?php echo esc_html( $title ); ?></h2><a href="<?php echo esc_url( $url ); ?>">Xem tất cả <span aria-hidden="true">›</span></a></div>
    <?php
}

function az_child_home_product( $product ) {
    if ( ! $product || ! $product->is_visible() ) { return; }
    $discount = 0;
    if ( $product->is_on_sale() ) {
        if ( $product->is_type( 'variable' ) ) {
            // Pair prices from the same variation to avoid misleading percentages.
            $prices = $product->get_variation_prices();
            foreach ( $prices['regular_price'] as $id => $regular ) {
                $sale = $prices['sale_price'][ $id ] ?? $regular;
                if ( (float) $regular > 0 && (float) $sale < (float) $regular ) {
                    $discount = max( $discount, (int) round( 100 * ( (float) $regular - (float) $sale ) / (float) $regular ) );
                }
            }
        } else {
            $regular = (float) $product->get_regular_price();
            $sale = (float) $product->get_sale_price();
            if ( $regular > 0 && $sale < $regular ) { $discount = (int) round( 100 * ( $regular - $sale ) / $regular ); }
        }
    }
    ?>
    <article class="az-home-product"><a href="<?php echo esc_url( $product->get_permalink() ); ?>">
      <div class="az-home-product__image"><?php echo $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy', 'alt' => $product->get_name() ) ); ?>
        <?php if ( $discount > 0 ) : ?><span class="az-home-discount"><?php echo esc_html( '-' . $discount . '%' ); ?></span><?php elseif ( $product->is_on_sale() ) : ?><span class="az-home-discount">Sale</span><?php endif; ?>
      </div><h3><?php echo esc_html( $product->get_name() ); ?></h3>
    </a><div class="az-home-product__price"><?php $price = $product->get_price_html(); if ( '' !== $price ) { echo wp_kses_post( $price ); } else { ?><a href="<?php echo esc_url( $product->get_permalink() ); ?>">Giá: Liên hệ</a><?php } ?></div></article>
    <?php
}

function az_child_home_articles( $config, $limit ) {
    if ( empty( $config['enabled'] ) ) { return array(); }
    $args = array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => $limit, 'ignore_sticky_posts' => true, 'has_password' => false );
    if ( ! empty( $config['category_id'] ) ) { $args['category'] = absint( $config['category_id'] ); }
    // A missing project category must not turn general news into completed projects.
    elseif ( ! empty( $config['require_category'] ) ) { return array(); }
    return get_posts( $args );
}

function az_child_home_archive_url( $config, $fallback = '/tin-tuc/' ) {
    if ( ! empty( $config['category_id'] ) ) {
        $url = get_category_link( absint( $config['category_id'] ) );
        if ( ! is_wp_error( $url ) ) { return $url; }
    }
    return home_url( $fallback );
}

function az_child_home_post_image( $article, $size = 'medium_large' ) {
    if ( has_post_thumbnail( $article ) ) {
        echo get_the_post_thumbnail( $article, $size, array( 'loading' => 'lazy' ) );
    } else {
        echo '<span class="az-home-image-placeholder" aria-hidden="true">' . az_child_steel_icon( 'box' ) . '</span>';
    }
}
