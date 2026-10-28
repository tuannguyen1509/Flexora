<?php
/**
 * Optional, site-wide visual effects controlled from Advanced > General / Footer.
 *
 * Each renderer checks its own setting so child headers and templates can
 * continue using the same controls without copying any implementation.
 */
defined( 'ABSPATH' ) || exit;

class AZ_Site_Effects {
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 30 );
        add_action( 'wp_body_open', array( $this, 'render_preloader' ), 1 );
        add_action( 'az_after_site_header', array( $this, 'render_breadcrumb' ), 5 );
        add_action( 'wp_footer', array( $this, 'render_back_to_top' ), 5 );
    }

    public function enqueue_assets() {
        $back_to_top = $this->back_to_top_enabled();
        if ( ! $back_to_top && ! az_get_option( 'general.preloader', 0 ) ) {
            return;
        }

        if ( $back_to_top ) {
            // Load independently of the optional parent style.css and its cached copies.
            $css_file = get_template_directory() . '/css/back-to-top.css';
            wp_enqueue_style( 'az-back-to-top', get_template_directory_uri() . '/css/back-to-top.css', array(), substr( hash_file( 'sha256', $css_file ), 0, 12 ) );
        }
        $file = get_template_directory() . '/js/az-site-effects.js';
        wp_enqueue_script( 'az-site-effects', get_template_directory_uri() . '/js/az-site-effects.js', array(), substr( hash_file( 'sha256', $file ), 0, 12 ), true );
    }

    public function render_preloader() {
        if ( ! az_get_option( 'general.preloader', 0 ) ) {
            return;
        }

        echo '<div class="az-preloader" data-az-preloader role="status" aria-live="polite"><span class="az-preloader__spinner" aria-hidden="true"></span><span class="screen-reader-text">Đang tải trang</span></div>';
    }

    public function render_back_to_top() {
        if ( ! $this->back_to_top_enabled() ) {
            return;
        }

        $position = 'left' === az_get_option( 'footer.back_to_top_position', 'right' ) ? 'left' : 'right';
        echo '<a class="az-back-to-top az-back-to-top--' . esc_attr( $position ) . '" href="#page" data-az-back-to-top aria-label="Lên đầu trang"><svg aria-hidden="true" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="m6 14 6-6 6 6M12 9v11"/></svg></a>';
    }

    private function back_to_top_enabled() {
        // Keep the former General setting until Footer is saved, including explicit off.
        return (bool) az_get_option( 'footer.back_to_top', az_get_option( 'general.back_to_top', 0 ) );
    }

    public function render_breadcrumb() {
        $is_shop = function_exists( 'is_shop' ) && is_shop();
        if ( ! az_get_option( 'general.breadcrumb', 1 ) || is_front_page() || az_shared_banner_context() || ( $this->is_woocommerce_screen() && ! $is_shop ) ) {
            return;
        }

        $items = $this->breadcrumb_items();
        if ( count( $items ) < 2 ) {
            return;
        }

        echo '<nav class="az-site-breadcrumb" aria-label="Đường dẫn trang"><div class="az-container"><ol>';
        foreach ( $items as $index => $item ) {
            $current = $index === count( $items ) - 1;
            echo '<li>';
            if ( ! $current && $item['url'] ) {
                echo '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a>';
            } else {
                echo '<span' . ( $current ? ' aria-current="page"' : '' ) . '>' . esc_html( $item['label'] ) . '</span>';
            }
            echo '</li>';
        }
        echo '</ol></div></nav>';
    }

    private function is_woocommerce_screen() {
        if ( ! function_exists( 'is_woocommerce' ) ) {
            return false;
        }

        return is_woocommerce()
            || ( function_exists( 'is_cart' ) && is_cart() )
            || ( function_exists( 'is_checkout' ) && is_checkout() )
            || ( function_exists( 'is_account_page' ) && is_account_page() );
    }

    private function breadcrumb_items() {
        $items = array( array( 'url' => home_url( '/' ), 'label' => 'Trang chủ' ) );
        $title = '';

        if ( function_exists( 'is_shop' ) && is_shop() ) {
            $shop_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : 0;
            $title = $shop_id > 0 ? get_the_title( $shop_id ) : 'Cửa hàng';
            if ( ! $title ) { $title = 'Cửa hàng'; }
        } elseif ( is_single() ) {
            $categories = get_the_category();
            if ( $categories ) {
                $category = $categories[0];
                foreach ( array_reverse( get_ancestors( $category->term_id, 'category', 'taxonomy' ) ) as $ancestor_id ) {
                    $items[] = array( 'url' => get_category_link( $ancestor_id ), 'label' => get_cat_name( $ancestor_id ) );
                }
                $items[] = array( 'url' => get_category_link( $category->term_id ), 'label' => $category->name );
            }
            $title = get_the_title();
        } elseif ( is_home() && ( $posts_page = absint( get_option( 'page_for_posts' ) ) ) ) {
            $title = get_the_title( $posts_page );
        } elseif ( is_search() ) {
            $title = sprintf( 'Kết quả tìm kiếm: %s', get_search_query() );
        } elseif ( is_404() ) {
            $title = 'Không tìm thấy trang';
        } elseif ( is_author() ) {
            $title = 'Tác giả: ' . get_the_author_meta( 'display_name', get_queried_object_id() );
        } elseif ( is_archive() ) {
            $title = wp_strip_all_tags( get_the_archive_title() );
        }

        if ( $title ) {
            $items[] = array( 'url' => '', 'label' => $title );
        }

        return $items;
    }
}

new AZ_Site_Effects();
