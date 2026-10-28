<?php
/** Product URLs follow their category hierarchy without the catalogue base. */
defined( 'ABSPATH' ) || exit;

function az_child_product_pretty_urls_enabled() {
    return taxonomy_exists( 'product_cat' ) && post_type_exists( 'product' ) && get_option( 'permalink_structure' );
}

/** Build a complete, root-first path, even for categories deeper than two levels. */
function az_child_product_category_path( $term ) {
    if ( ! $term || is_wp_error( $term ) || 'product_cat' !== $term->taxonomy ) { return ''; }
    $slugs = array();
    foreach ( array_reverse( get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ) ) as $id ) {
        $ancestor = get_term( $id, 'product_cat' );
        if ( ! $ancestor || is_wp_error( $ancestor ) ) { return ''; }
        $slugs[] = $ancestor->slug;
    }
    $slugs[] = $term->slug;
    return implode( '/', $slugs );
}

/** Existing Pages and WooCommerce endpoints keep their URLs when slugs collide. */
function az_child_product_path_reserved( $path ) {
    global $wp_rewrite;
    $root = strtok( $path, '/' );
    $reserved = array( 'wp-admin', 'wp-content', 'wp-includes', rest_get_url_prefix(), 'wp-sitemap.xml', 'robots.txt', $wp_rewrite->feed_base, $wp_rewrite->author_base, $wp_rewrite->search_base, $wp_rewrite->pagination_base );
    if ( in_array( $root, $reserved, true ) ) { return true; }
    foreach ( array( 'shop', 'cart', 'checkout', 'myaccount' ) as $page_name ) {
        $page_id = wc_get_page_id( $page_name );
        $page_path = $page_id > 0 ? get_page_uri( $page_id ) : '';
        if ( $page_path && ( $path === $page_path || 0 === strpos( $path, $page_path . '/' ) ) ) { return true; }
    }
    $page = get_page_by_path( $path );
    return $page && in_array( $page->post_status, array( 'publish', 'private' ), true );
}

function az_child_product_category_link( $url, $term, $taxonomy ) {
    if ( 'product_cat' !== $taxonomy || ! az_child_product_pretty_urls_enabled() ) { return $url; }
    $path = az_child_product_category_path( $term );
    return $path && ! az_child_product_path_reserved( $path ) ? home_url( user_trailingslashit( $path, 'category' ) ) : $url;
}
add_filter( 'term_link', 'az_child_product_category_link', 30, 3 );

/** Preserve WooCommerce's category-selection hook, including SEO integrations. */
function az_child_product_permalink_category( $post ) {
    $terms = get_the_terms( $post->ID, 'product_cat' );
    if ( ! $terms || is_wp_error( $terms ) ) {
        $fallback = get_term( (int) get_option( 'default_product_cat' ), 'product_cat' );
        return $fallback && ! is_wp_error( $fallback ) ? $fallback : null;
    }
    $depths = array();
    foreach ( $terms as $term ) { $depths[ $term->term_id ] = count( get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ) ); }
    usort( $terms, static function( $a, $b ) use ( $depths ) {
        return ( $depths[ $b->term_id ] <=> $depths[ $a->term_id ] ) ?: ( $a->term_id <=> $b->term_id );
    } );
    $selected = apply_filters( 'wc_product_post_type_link_product_cat', $terms[0], $terms, $post );
    // Reject a stale primary category that is no longer assigned to this product.
    foreach ( $terms as $term ) {
        if ( is_object( $selected ) && $selected->term_id === $term->term_id ) { return $term; }
    }
    return $terms[0];
}

function az_child_product_permalink( $url, $post, $leavename, $sample ) {
    if ( 'product' !== $post->post_type || ! az_child_product_pretty_urls_enabled() || wp_force_plain_post_permalink( $post, $sample ? true : null ) ) { return $url; }
    $path = az_child_product_category_path( az_child_product_permalink_category( $post ) );
    $slug = $leavename ? '%product%' : $post->post_name;
    if ( ! $path || ! $slug || az_child_product_path_reserved( $path ) || az_child_product_path_reserved( $path . '/' . $slug ) ) { return $url; }
    // A product and a child category can legally have the same slug in WP.
    if ( ! $leavename ) {
        $categories = get_terms( array( 'taxonomy' => 'product_cat', 'slug' => $slug, 'hide_empty' => false ) );
        if ( ! is_wp_error( $categories ) ) {
            foreach ( $categories as $category ) {
                if ( az_child_product_category_path( $category ) === $path . '/' . $slug ) { return $url; }
            }
        }
    }
    return home_url( user_trailingslashit( $path . '/' . $slug, 'single' ) );
}
add_filter( 'post_type_link', 'az_child_product_permalink', 30, 4 );

/** Exact category prefixes avoid a generic root-level product catch-all. */
function az_child_product_permalink_rules( $rules ) {
    if ( ! az_child_product_pretty_urls_enabled() ) { return $rules; }
    $terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
    if ( is_wp_error( $terms ) ) { return $rules; }
    $paths = array();
    foreach ( $terms as $term ) {
        $path = az_child_product_category_path( $term );
        if ( $path && ! az_child_product_path_reserved( $path ) ) { $paths[] = $path; }
    }
    usort( $paths, static function( $a, $b ) {
        return ( substr_count( $b, '/' ) <=> substr_count( $a, '/' ) ) ?: strcmp( $a, $b );
    } );
    global $wp_rewrite;
    $pagination = preg_quote( $wp_rewrite->pagination_base, '#' );
    $comments = preg_quote( $wp_rewrite->comments_pagination_base, '#' );
    $feed = preg_quote( $wp_rewrite->feed_base, '#' );
    $feeds = '(feed|rdf|rss|rss2|atom)';
    $custom = array();
    foreach ( $paths as $path ) {
        $prefix = '^' . preg_quote( $path, '#' );
        $category = 'index.php?product_cat=' . rawurlencode( $path );
        $product = 'index.php?post_type=product&product=$matches[1]';
        $marker = '&az_child_product_route=1';
        $custom[ $prefix . '/' . $pagination . '/([0-9]+)/?$' ] = $category . '&paged=$matches[1]' . $marker;
        $custom[ $prefix . '/(?:' . $feed . '/)?' . $feeds . '/?$' ] = $category . '&feed=$matches[1]' . $marker;
        $custom[ $prefix . '/([^/]+)/(?:' . $feed . '/)?' . $feeds . '/?$' ] = $product . '&feed=$matches[2]' . $marker;
        $custom[ $prefix . '/([^/]+)/' . $comments . '-([0-9]+)/?$' ] = $product . '&cpage=$matches[2]' . $marker;
        $custom[ $prefix . '/([^/]+)/([0-9]+)/?$' ] = $product . '&page=$matches[2]' . $marker;
        $custom[ $prefix . '/([^/]+)/embed/?$' ] = $product . '&embed=true' . $marker;
        $custom[ $prefix . '/([^/]+)/trackback/?$' ] = $product . '&tb=1' . $marker;
        $custom[ $prefix . '/?$' ] = $category . $marker;
        $custom[ $prefix . '/([^/]+)/?$' ] = $product . $marker;
    }
    // Keep the old base rules so existing bookmarks can resolve and redirect.
    return $custom + $rules;
}
add_filter( 'rewrite_rules_array', 'az_child_product_permalink_rules', 30 );
add_filter( 'query_vars', static function( $vars ) { $vars[] = 'az_child_product_route'; return $vars; } );

/** A Page created after the rules were cached must still take precedence. */
function az_child_product_permalink_page_request( $vars ) {
    if ( empty( $vars['az_child_product_route'] ) ) { return $vars; }
    global $wp;
    $page = get_page_by_path( $wp->request );
    if ( ! $page || ! in_array( $page->post_status, array( 'publish', 'private' ), true ) ) { return $vars; }
    foreach ( array( 'az_child_product_route', 'product', 'product_cat', 'post_type', 'name', 'error', 'feed', 'paged', 'page', 'cpage', 'embed', 'tb' ) as $key ) { unset( $vars[ $key ] ); }
    $vars['page_id'] = $page->ID;
    return $vars;
}
add_filter( 'request', 'az_child_product_permalink_page_request', 30 );

function az_child_product_permalink_invalidate() { delete_option( 'az_child_product_rewrite_version' ); }
foreach ( array( 'created_product_cat', 'edited_product_cat', 'delete_product_cat', 'after_switch_theme' ) as $hook ) {
    add_action( $hook, 'az_child_product_permalink_invalidate' );
}
function az_child_product_permalink_refresh() {
    if ( ! az_child_product_pretty_urls_enabled() || '1' === get_option( 'az_child_product_rewrite_version' ) ) { return; }
    // One soft flush after deployment/category edits; never write .htaccess here.
    flush_rewrite_rules( false );
    update_option( 'az_child_product_rewrite_version', '1', false );
}
add_action( 'wp_loaded', 'az_child_product_permalink_refresh', 20 );

/** Return a canonical redirect target; preserve filters and tracking parameters. */
function az_child_product_permalink_redirect_url( $request_uri ) {
    if ( ! az_child_product_pretty_urls_enabled() || is_admin() || wp_doing_ajax() || is_preview() || is_404() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) { return false; }
    if ( ! in_array( $_SERVER['REQUEST_METHOD'] ?? 'GET', array( 'GET', 'HEAD' ), true ) ) { return false; }
    // WooCommerce can process this GET action before template_redirect runs.
    if ( isset( $_GET['add-to-cart'] ) || isset( $_GET['wc-ajax'] ) ) { return false; }
    $target = '';
    global $wp_rewrite;
    if ( is_singular( 'product' ) ) {
        $id = get_queried_object_id();
        $target = get_permalink( $id );
        if ( is_feed() ) { $target = get_post_comments_feed_link( $id, get_query_var( 'feed' ) ); }
        elseif ( is_embed() ) { $target = get_post_embed_url( $id ); }
        elseif ( is_trackback() ) { $target = trailingslashit( $target ) . user_trailingslashit( 'trackback', 'single_trackback' ); }
        elseif ( get_query_var( 'page' ) > 1 ) { $target = trailingslashit( $target ) . user_trailingslashit( (string) get_query_var( 'page' ), 'single_paged' ); }
        elseif ( get_query_var( 'cpage' ) > 1 ) { $target = trailingslashit( $target ) . user_trailingslashit( $wp_rewrite->comments_pagination_base . '-' . get_query_var( 'cpage' ), 'commentpaged' ); }
    } elseif ( is_tax( 'product_cat' ) ) {
        $term = get_queried_object();
        $target = is_feed() ? get_term_feed_link( $term->term_id, 'product_cat', get_query_var( 'feed' ) ) : get_term_link( $term, 'product_cat' );
        if ( ! is_feed() && get_query_var( 'paged' ) > 1 && ! is_wp_error( $target ) ) {
            $target = trailingslashit( $target ) . user_trailingslashit( $wp_rewrite->pagination_base . '/' . get_query_var( 'paged' ), 'paged' );
        }
    }
    if ( ! $target || is_wp_error( $target ) ) { return false; }
    $requested_path = wp_parse_url( $request_uri, PHP_URL_PATH );
    if ( $requested_path === wp_parse_url( $target, PHP_URL_PATH ) ) { return false; }
    $args = array();
    wp_parse_str( (string) wp_parse_url( $request_uri, PHP_URL_QUERY ), $args );
    foreach ( array( 'product', 'product_cat', 'post_type', 'p', 'name', 'page_id', 'pagename', 'az_child_product_route', 'paged', 'page', 'cpage', 'feed', 'embed', 'tb' ) as $key ) { unset( $args[ $key ] ); }
    return $args ? add_query_arg( $args, $target ) : $target;
}
function az_child_product_permalink_redirect() {
    $url = az_child_product_permalink_redirect_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
    if ( $url ) { wp_safe_redirect( $url, 301, 'AZ Theme Child' ); exit; }
}
add_action( 'template_redirect', 'az_child_product_permalink_redirect', 1 );
