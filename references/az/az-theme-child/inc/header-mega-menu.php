<?php
/** Category artwork alongside the primary menu's existing child links. */
defined( 'ABSPATH' ) || exit;

/** Resolve taxonomy menu items, or custom links to an exact local category URL. */
function az_child_mega_menu_term( $item ) {
    $taxonomies = array( 'product_cat', 'category' );
    if ( 'taxonomy' === $item->type && in_array( $item->object, $taxonomies, true ) && taxonomy_exists( $item->object ) ) {
        $term = get_term( absint( $item->object_id ), $item->object );
        return $term && ! is_wp_error( $term ) ? $term : null;
    }
    if ( 'custom' !== $item->type || empty( $item->url ) ) { return null; }
    $host = wp_parse_url( $item->url, PHP_URL_HOST );
    if ( $host && strtolower( $host ) !== strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) ) ) { return null; }
    $path = untrailingslashit( (string) wp_parse_url( $item->url, PHP_URL_PATH ) );
    $slug = rawurldecode( basename( $path ) );
    if ( ! $slug || '.' === $slug ) { return null; }
    foreach ( $taxonomies as $taxonomy ) {
        if ( ! taxonomy_exists( $taxonomy ) ) { continue; }
        $term = get_term_by( 'slug', $slug, $taxonomy );
        if ( ! $term ) { continue; }
        $url = get_term_link( $term );
        if ( ! is_wp_error( $url ) && $path === untrailingslashit( (string) wp_parse_url( $url, PHP_URL_PATH ) ) ) { return $term; }
    }
    return null;
}

function az_child_mega_menu_image( $item ) {
    $term = az_child_mega_menu_term( $item );
    if ( ! $term ) { return ''; }
    $key = 'product_cat' === $term->taxonomy ? 'thumbnail_id' : 'cp_cat_image_id';
    $id = absint( get_term_meta( $term->term_id, $key, true ) );
    if ( ! $id ) { return ''; }
    return wp_get_attachment_image( $id, 'medium_large', false, array(
        'class' => 'az-mega-menu__image', 'alt' => $term->name, 'loading' => 'lazy', 'decoding' => 'async',
    ) );
}

class AZ_Child_Mega_Menu_Walker extends Walker_Nav_Menu {
    private $panel = array();
    private $scope;

    public function __construct() {
        parent::__construct();
        $this->scope = wp_unique_id( 'az-mega-' );
    }

    public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
        $item = clone $data_object;
        $mega = 0 === $depth && $this->has_children && ( empty( $args->depth ) || $args->depth > 1 );
        if ( $mega ) {
            $item->classes = array_merge( (array) $item->classes, array( 'az-mega-menu' ) );
            $this->panel = array( 'id' => $this->scope . '-' . absint( $item->ID ), 'image' => az_child_mega_menu_image( $item ) );
        }
        // Keep WordPress URL, title/icon, active-item and attribute filters intact.
        parent::start_el( $output, $item, $depth, $args, $current_object_id );
        if ( $mega ) {
            $label = sprintf( __( 'Mở menu con: %s', 'az-theme-child' ), wp_strip_all_tags( $item->title ) );
            $output .= '<button class="az-mega-menu__toggle" type="button" data-az-mega-toggle aria-label="' . esc_attr( $label ) . '" aria-expanded="false" aria-controls="' . esc_attr( $this->panel['id'] ) . '"><svg viewBox="0 0 16 16" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m4 6 4 4 4-4"/></svg></button>';
        }
    }

    public function start_lvl( &$output, $depth = 0, $args = null ) {
        if ( 0 === $depth ) {
            $output .= '<div id="' . esc_attr( $this->panel['id'] ) . '" class="az-mega-menu__panel' . ( $this->panel['image'] ? ' az-mega-menu__panel--image' : '' ) . '">';
        }
        parent::start_lvl( $output, $depth, $args );
    }

    public function end_lvl( &$output, $depth = 0, $args = null ) {
        parent::end_lvl( $output, $depth, $args );
        if ( 0 === $depth ) {
            if ( $this->panel['image'] ) { $output .= '<div class="az-mega-menu__art">' . $this->panel['image'] . '</div>'; }
            $output .= '</div>';
        }
    }
}

/** Only desktop primary/sticky menus use this walker; mobile keeps its accordion. */
function az_child_header_mega_menu_args( $args ) {
    if ( in_array( $args['menu_class'] ?? '', array( 'az-steel-menu', 'az-sticky-menu' ), true ) && empty( $args['walker'] ) ) {
        $args['walker'] = new AZ_Child_Mega_Menu_Walker();
    }
    return $args;
}
add_filter( 'wp_nav_menu_args', 'az_child_header_mega_menu_args' );

function az_child_header_mega_menu_assets() {
    $base = get_stylesheet_directory();
    $url = get_stylesheet_directory_uri();
    wp_enqueue_style( 'az-header-mega-menu', $url . '/css/header-mega-menu.css', array( 'az-header-storefront' ), filemtime( $base . '/css/header-mega-menu.css' ) );
    wp_enqueue_script( 'az-header-mega-menu', $url . '/js/header-mega-menu.js', array(), filemtime( $base . '/js/header-mega-menu.js' ), true );
}
add_action( 'wp_enqueue_scripts', 'az_child_header_mega_menu_assets', 45 );
