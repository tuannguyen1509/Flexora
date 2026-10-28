<?php
/** Data and shared rendering for the child theme's steel storefront. */
defined( 'ABSPATH' ) || exit;

function az_child_is_steel_home() {
    return is_front_page() || is_page_template( 'front-page.php' );
}

function az_child_steel_categories() {
    if ( ! taxonomy_exists( 'product_cat' ) ) { return array(); }
    $terms = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => 0, 'hide_empty' => false, 'number' => 8, 'orderby' => 'name' ) );
    return is_wp_error( $terms ) ? array() : $terms;
}

/** Same category source for the homepage rail and inner-page dropdown. */
function az_child_steel_category_links( $homepage = false, $options = null ) {
    $options = $options ?? az_header_options();
    // The Header switch controls every category-menu renderer, including callers
    // outside header.php. Keep the selected menu saved for the next activation.
    if ( empty( $options['show_category'] ) ) { return; }
    // Honour the explicit selection first; never fall back to the primary menu.
    $menus = array_unique( array_filter( array( absint( $options['category_menu'] ?? 0 ), az_product_category_menu_id() ) ) );
    foreach ( $menus as $menu ) {
        $html = wp_nav_menu( array( 'menu' => $menu, 'container' => false, 'menu_class' => 'az-steel-category-list', 'fallback_cb' => false, 'echo' => false ) );
        if ( $html ) { echo $html; return; }
    }
    echo '<a class="az-steel-categories__featured" href="' . esc_url( home_url( '/#az-steel-featured' ) ) . '">SẢN PHẨM NỔI BẬT <span>MỚI</span></a>';
    $terms = az_child_steel_categories();
    echo '<ul class="az-steel-category-list">';
    if ( $terms ) {
        foreach ( $terms as $term ) {
            $url = get_term_link( $term );
            if ( ! is_wp_error( $url ) ) { echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . '</a></li>'; }
        }
    } else {
        $url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/san-pham/' );
        $labels = $homepage ? array_column( az_home_page_get_data( absint( get_option( 'page_on_front' ) ) )['products']['sections'], 'title' ) : array( 'Tôn – Thép xây dựng', 'Tôn nhựa lấy sáng', 'Cuộn – Tấm – Ống Inox', 'Cách nhiệt PE – PU', 'Xà gồ chữ C–Z', 'Lam gió', 'Bảng màu' );
        foreach ( $labels as $index => $label ) {
            $target = $homepage ? home_url( '/#az-home-products-' . absint( $index + 1 ) ) : $url;
            echo '<li><a href="' . esc_url( $target ) . '">' . esc_html( $label ) . '</a></li>';
        }
    }
    echo '</ul>';
}

function az_child_steel_products( $category_ids = array(), $featured = false, $limit = 4 ) {
    if ( ! function_exists( 'wc_get_products' ) ) { return array(); }
    $query = array( 'status' => 'publish', 'limit' => max( 1, min( 20, absint( $limit ) ) ), 'visibility' => 'catalog', 'orderby' => 'date', 'order' => 'DESC' );
    $slugs = array();
    foreach ( array_filter( array_map( 'absint', (array) $category_ids ) ) as $id ) {
        $term = get_term( $id, 'product_cat' );
        if ( $term && ! is_wp_error( $term ) ) {
            $slugs[] = $term->slug;
            $children = get_term_children( $id, 'product_cat' );
            foreach ( is_wp_error( $children ) ? array() : $children as $child_id ) {
                $child = get_term( $child_id, 'product_cat' );
                if ( $child && ! is_wp_error( $child ) ) { $slugs[] = $child->slug; }
            }
        }
    }
    if ( $category_ids && ! $slugs ) { return array(); }
    if ( $slugs ) { $query['category'] = array_values( array_unique( $slugs ) ); }
    if ( $featured ) { $query['featured'] = true; }
    $products = wc_get_products( $query );
    if ( $featured && ! $products ) { unset( $query['featured'] ); $products = wc_get_products( $query ); }
    return $products;
}

function az_child_steel_card( $product, $compact = false ) {
    if ( ! $product || ! $product->is_visible() ) { return; }
    $name = $product->get_name();
    ?>
    <article class="az-steel-product<?php echo $compact ? ' az-steel-product--compact' : ''; ?>">
      <a href="<?php echo esc_url( $product->get_permalink() ); ?>">
        <div class="az-steel-product__image"><?php echo $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy', 'alt' => $name ) ); ?></div>
        <h3><?php echo esc_html( $name ); ?></h3>
      </a>
    </article>
    <?php
}

function az_child_steel_news() {
    static $news = null;
    if ( null !== $news ) { return $news; }
    $config = az_home_page_get_data()['blog'];
    if ( empty( $config['enabled'] ) ) { return $news = array(); }
    $query = array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => max( 4, min( 24, absint( $config['limit'] ?? 4 ) ) ), 'ignore_sticky_posts' => true );
    if ( ! empty( $config['category_id'] ) ) { $query['category'] = absint( $config['category_id'] ); }
    return $news = get_posts( $query );
}

function az_child_steel_icon( $name ) {
    $paths = array(
        'truck' => '<path d="M3 6h11v11H3zM14 10h4l3 4v3h-7"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>',
        'phone' => '<path d="M5 3h4l2 5-3 2c1 3 3 5 6 6l2-3 5 2v4c0 2-3 3-5 2C9 19 5 15 3 8 2 5 3 3 5 3Z"/>',
        'box' => '<path d="m3 7 9-5 9 5v10l-9 5-9-5ZM3 7l9 5 9-5M12 12v10M7 4l10 6"/>',
        'chat' => '<path d="M3 3h15v12h-8l-5 4v-4H3zM18 8h4v12h-4v3l-4-3h-3"/>',
        'search' => '<circle cx="10" cy="10" r="6"/><path d="m15 15 6 6"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
    );
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( $paths[ $name ] ?? $paths['box'] ) . '</svg>';
}

/** Shared network labels and artwork for footer and floating contact controls. */
function az_child_steel_social_icons() {
    return array(
        'facebook' => array( 'Facebook', '<path d="M14 22v-9h3l.5-4H14V7c0-1 .3-2 2-2h2V1.5L15 1c-3 0-5 2-5 5v3H7v4h3v9z"/>' ),
        'youtube' => array( 'YouTube', '<rect x="1" y="4" width="22" height="16" rx="5"/><path d="m10 8 6 4-6 4z" fill="var(--az-steel-tint)"/>' ),
        'tiktok' => array( 'TikTok', '<path d="M14 2h4c0 3 2 5 5 5v4c-2 0-4-1-5-2v8a6 6 0 1 1-6-6v4a2 2 0 1 0 2 2z"/>' ),
        'instagram' => array( 'Instagram', '<g fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="6"/><circle cx="12" cy="12" r="5"/></g><circle cx="18" cy="6" r="1.5"/>' ),
        'linkedin' => array( 'LinkedIn', '<circle cx="4" cy="4" r="2"/><path d="M2 8h4v14H2zm7 0h4v2c1-2 3-3 5-2 3 0 4 2 4 5v9h-4v-8c0-3-5-3-5 0v8H9z"/>' ),
        'pinterest' => array( 'Pinterest', '<path d="M12 1a11 11 0 0 0-4 21l2-8c-1-2 0-7 2-7 3 0 1 5 1 7 0 3 5 2 5-3 0-7-11-7-11 0 0 1 1 2 1 3l-1 2C1 13 4 3 12 3c11 0 11 16 3 16-2 0-3-1-4-2l-1 6A11 11 0 1 0 12 1z"/>' ),
        'x' => array( 'X', '<path d="M2 2h6l6 8 7-8h2l-8 10 8 10h-6l-6-8-7 8H1l9-10zm4 2 12 16h2L8 4z"/>' ),
        'zalo' => array( 'Zalo', '<path d="M2 2h20v16H9l-7 5z"/><text x="12" y="13" text-anchor="middle" font-size="8" font-family="Arial,sans-serif" font-weight="700" fill="var(--az-steel-tint)">Zalo</text>' ),
        'messenger' => array( 'Messenger', '<path d="M12 1C6 1 1 5 1 11c0 3 1 6 4 8v4l4-2 3 1c6 0 11-5 11-11S18 1 12 1z"/><path d="m5 15 5-7 4 3 5-3-5 7-4-3z" fill="var(--az-steel-tint)"/>' ),
        'whatsapp' => array( 'WhatsApp', '<g fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18 1 23l6-2a10 10 0 1 0-4-3Z"/><path d="m8 6-2 2c0 5 5 10 10 10l2-2-4-2-1 2c-3-1-4-3-5-5l2-1z"/></g>' ),
    );
}

/** Social URLs come from the parent Theme Advanced settings, not hardcoded links. */
function az_child_steel_social_links() {
    $links = az_get_social_links();
    $icons = az_child_steel_social_icons();
    $items = '';
    foreach ( $links as $network => $url ) {
        $url = esc_url( $url, array( 'https', 'http' ) );
        if ( ! $url || ! isset( $icons[ $network ] ) ) { continue; }
        list( $label, $svg ) = $icons[ $network ];
        $items .= '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr( $label ) . '" title="' . esc_attr( $label ) . '"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">' . $svg . '</svg></a>';
    }
    if ( $items ) { echo '<nav class="az-steel-social" aria-label="Mạng xã hội">' . $items . '</nav>'; }
}

/** Prefer the Top Menu location assigned in Appearance > Menus. */
function az_child_steel_top_menu( $options = null ) {
    $options = $options ?? az_header_options();
    $args = array(
        'container' => false,
        'menu_class' => 'az-steel-top-menu',
        'depth' => 1,
        'fallback_cb' => false,
    );
    if ( ! empty( $options['top_menu'] ) ) {
        $args['menu'] = absint( $options['top_menu'] );
    } elseif ( has_nav_menu( 'top' ) ) {
        $args['theme_location'] = 'top';
    } else {
        return;
    }
    wp_nav_menu( $args );
}

/** One WordPress navigation source for all pages and the sticky header. */
function az_child_steel_main_menu( $class = 'az-steel-menu', $options = null, $slot = 'primary' ) {
    $options = $options ?? array_merge( array( 'show_primary_menu' => az_get_option( 'header.show_primary_menu', 1 ) ), az_header_options() );
    if ( empty( $options['show_primary_menu'] ) ) { return; }
    $menu = absint( $options['primary_menu'] ?? 0 );
    if ( 'sticky' === $slot ) { $menu = absint( $options['sticky_menu'] ?? 0 ) ?: $menu; }
    if ( ! $menu && ! has_nav_menu( 'primary' ) ) { return; }
    wp_nav_menu( array( 'menu' => $menu, 'theme_location' => 'primary', 'container' => false, 'menu_class' => $class, 'depth' => 0, 'fallback_cb' => false ) );
}
