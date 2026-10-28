<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Custom Nav Walker for Header Menu — supports 2-level dropdown
 */
class AZ_AI_Nav_Walker extends Walker_Nav_Menu {

    function start_lvl( &$output, $depth = 0, $args = null ) {
        // Sub-menu dropdown
        $output .= '<ul class="az-submenu">';
    }

    function end_lvl( &$output, $depth = 0, $args = null ) {
        $output .= '</ul>';
    }

    function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $has_children = in_array( 'menu-item-has-children', (array) $item->classes );

        // Bilingual data attrs
        $label_en  = get_post_meta( $item->ID, '_menu_item_label_en', true );
        $bilingual = '';
        $link_attributes = array( 'target' => $item->target ?? '', 'rel' => $item->xfn ?? '', 'title' => $item->attr_title ?? '' );
        if ( '_blank' === $link_attributes['target'] ) { $link_attributes['rel'] = trim( $link_attributes['rel'] . ' noopener' ); }
        if ( ! empty( $item->current ) ) { $link_attributes['aria-current'] = 'page'; }
        $link_attributes = apply_filters( 'nav_menu_link_attributes', $link_attributes, $item, $args, $depth );
        foreach ( $link_attributes as $name => $value ) {
            if ( '' !== (string) $value && preg_match( '/^[a-zA-Z][a-zA-Z0-9_-]*$/', $name ) && ! preg_match( '/^on/i', $name ) ) {
                $bilingual .= ' ' . $name . '="' . esc_attr( $value ) . '"';
            }
        }
        if ( ! empty( $label_en ) ) {
            $bilingual = ' data-vi="' . esc_attr( $item->title ) . '" data-en="' . esc_attr( $label_en ) . '"';
        }

        if ( $depth === 0 ) {
            // Top-level item
            if ( $has_children ) {
                $output .= '<div class="az-menu-item az-has-dropdown">';
                $output .= '<a href="' . esc_url( $item->url ) . '" class="az-menu-link"' . $bilingual . '>';
                $output .= esc_html( $item->title );
                $output .= '<svg class="az-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/></svg>';
                $output .= '</a>';
            } else {
                $output .= '<div class="az-menu-item">';
                $output .= '<a href="' . esc_url( $item->url ) . '" class="az-menu-link"' . $bilingual . '>';
                $output .= esc_html( $item->title );
                $output .= '</a>';
            }
        } else {
            // Sub-level item
            $output .= '<li>';
            $output .= '<a href="' . esc_url( $item->url ) . '" class="az-submenu-link"' . $bilingual . '>';
            $output .= esc_html( $item->title );
            $output .= '</a>';
        }
    }

    function end_el( &$output, $item, $depth = 0, $args = null ) {
        if ( $depth === 0 ) {
            $output .= '</div>'; // close .az-menu-item
        } else {
            $output .= '</li>';
        }
    }
}

/**
 * Theme Setup
 */
function az_ai_theme_setup() {
    load_theme_textdomain( 'az-ai-theme', get_template_directory() . '/languages' );
    
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'woocommerce' );
    add_theme_support( 'custom-logo', array(
        'height'      => 40,
        'width'       => 40,
        'flex-height' => true,
        'flex-width'  => true,
    ) );
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ) );
    
    register_nav_menus( array(
        'primary' => esc_html__( 'Primary Menu', 'az-ai-theme' ),
        'top'     => esc_html__( 'Top Menu', 'az-ai-theme' ),
        'footer'  => esc_html__( 'Footer Menu — Về chúng tôi', 'az-ai-theme' ),
    ) );
}
add_action( 'after_setup_theme', 'az_ai_theme_setup' );

/**
 * Product warehouse taxonomy.
 */
function az_register_product_warehouse_taxonomy() {
    register_taxonomy(
        'product_warehouse',
        array( 'product' ),
        array(
            'labels' => array(
                'name'                       => 'Kho',
                'singular_name'              => 'Kho',
                'menu_name'                  => 'Kho',
                'all_items'                  => 'Tất cả kho',
                'edit_item'                  => 'Sửa kho',
                'view_item'                  => 'Xem kho',
                'update_item'                => 'Cập nhật kho',
                'add_new_item'               => 'Thêm kho mới',
                'new_item_name'              => 'Tên kho mới',
                'parent_item'                => 'Kho cha',
                'parent_item_colon'          => 'Kho cha:',
                'search_items'               => 'Tìm kho',
                'popular_items'              => 'Kho phổ biến',
                'separate_items_with_commas' => 'Phân cách các kho bằng dấu phẩy',
                'add_or_remove_items'        => 'Thêm hoặc xóa kho',
                'choose_from_most_used'      => 'Chọn từ kho dùng nhiều',
                'not_found'                  => 'Không tìm thấy kho',
            ),
            'public'            => true,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_menu'      => true,
            'show_in_rest'      => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'kho' ),
        )
    );
}
add_action( 'init', 'az_register_product_warehouse_taxonomy', 30 );

// Simple language detection from cookie (no geolocation)
function az_get_current_language() {
    return isset($_COOKIE['az_language']) ? sanitize_text_field($_COOKIE['az_language']) : 'vi';
}

// Apply WordPress locale based on cookie
add_filter('locale', function($locale) {
    $lang = az_get_current_language();
    return ($lang === 'en') ? 'en_US' : 'vi';
}, 10);

/**
 * Bilingual string helper: returns EN string when EN is active, VI string otherwise.
 * Usage: az_t( 'Tiếng Việt', 'English text' )
 * NOTE: Server-side fallback only. Client-side switching is handled by JS.
 */
function az_t( $vi_text, $en_text ) {
    // Server-side: check cookie (for SEO/initial render)
    $lang = az_get_current_language();
    return ( $lang === 'en' ) ? $en_text : $vi_text;
}

/**
 * Output a bilingual element with both VI and EN versions embedded.
 * JS will show/hide the correct one based on localStorage.
 * Usage: az_bilingual( 'Tiếng Việt', 'English text', 'span', 'class-name' )
 */
function az_bilingual( $vi_text, $en_text, $tag = 'span', $class = '' ) {
    $class_attr = $class ? ' class="' . esc_attr( $class ) . '"' : '';
    echo '<' . $tag . ' data-vi="' . esc_attr( $vi_text ) . '" data-en="' . esc_attr( $en_text ) . '"' . $class_attr . '>';
    echo esc_html( $vi_text ); // default VI, JS will swap if needed
    echo '</' . $tag . '>';
}

/**
 * Bilingual theme_mod: returns EN value from a separate _en option key when EN is active.
 * Falls back to the base theme_mod value (Vietnamese) if no EN override is set.
 */
function az_theme_mod( $key, $default_vi = '', $default_en = '' ) {
    if ( az_get_current_language() === 'en' ) {
        $en_val = get_theme_mod( $key . '_en', $default_en );
        return ! empty( $en_val ) ? $en_val : $default_en;
    }
    return get_theme_mod( $key, $default_vi );
}

// No language detector classes needed - removed geolocation system

/**
 * Enqueue Scripts and Styles
 */
function az_ai_theme_scripts() {
    $az_parent_style = get_template_directory() . '/style.css';
    wp_enqueue_style( 'az-ai-theme-style', get_template_directory_uri() . '/style.css', array(), filemtime( $az_parent_style ) );
    wp_enqueue_style( 'az-container-sync', get_template_directory_uri() . '/css/container-sync.css', array( 'az-ai-theme-style' ), filemtime( get_template_directory() . '/css/container-sync.css' ) );
    $az_current_post = get_post();
    $az_is_order_tracking = is_page() && $az_current_post instanceof WP_Post && has_shortcode( $az_current_post->post_content, 'woocommerce_order_tracking' );
    if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() || $az_is_order_tracking ) ) {
        $az_woocommerce_css_path = get_theme_file_path( 'css/woocommerce.css' );
        if ( file_exists( $az_woocommerce_css_path ) ) {
            wp_enqueue_style( 'az-woocommerce', get_theme_file_uri( 'css/woocommerce.css' ), array( 'az-container-sync' ), filemtime( $az_woocommerce_css_path ) );
        }
        if ( is_shop() || is_product_taxonomy() ) {
            $az_shop_categories_script = get_theme_file_path( 'js/az-shop-categories.js' );
            if ( file_exists( $az_shop_categories_script ) ) {
                wp_enqueue_script( 'az-shop-categories', get_theme_file_uri( 'js/az-shop-categories.js' ), array(), filemtime( $az_shop_categories_script ), true );
            }
        }
        if ( is_product() ) {
            $az_product_variation_script = get_theme_file_path( 'js/az-product-variation.js' );
            if ( file_exists( $az_product_variation_script ) ) {
                wp_enqueue_script( 'az-product-variation', get_theme_file_uri( 'js/az-product-variation.js' ), array(), filemtime( $az_product_variation_script ), true );
            }
        }
    }
    $az_is_blog_archive = is_home() || is_category() || is_tag() || is_author() || is_date();
    $az_is_blog_single = is_singular( 'post' );
    if ( $az_is_blog_archive || $az_is_blog_single ) {
        if ( $az_is_blog_archive ) {
            wp_enqueue_style( 'az-blog-archive', get_template_directory_uri() . '/css/blog-archive.css', array( 'az-container-sync' ), filemtime( get_template_directory() . '/css/blog-archive.css' ) );
            wp_enqueue_style( 'az-blog-author', get_template_directory_uri() . '/css/blog-author.css', array( 'az-blog-archive' ), filemtime( get_template_directory() . '/css/blog-author.css' ) );
            wp_enqueue_style( 'az-blog-meta', get_template_directory_uri() . '/css/blog-meta.css', array( 'az-blog-archive' ), filemtime( get_template_directory() . '/css/blog-meta.css' ) );
            wp_enqueue_style( 'az-blog-layout', get_template_directory_uri() . '/css/blog-layout.css', array( 'az-blog-meta' ), filemtime( get_template_directory() . '/css/blog-layout.css' ) );
            wp_enqueue_style( 'az-blog-sidebar', get_template_directory_uri() . '/css/blog-sidebar.css', array( 'az-blog-layout' ), filemtime( get_template_directory() . '/css/blog-sidebar.css' ) );
            wp_enqueue_style( 'az-blog-card', get_template_directory_uri() . '/css/blog-card.css', array( 'az-blog-sidebar' ), filemtime( get_template_directory() . '/css/blog-card.css' ) );
            wp_enqueue_style( 'az-blog-hero', get_template_directory_uri() . '/css/blog-hero.css', array( 'az-blog-card' ), filemtime( get_template_directory() . '/css/blog-hero.css' ) );
            wp_enqueue_style( 'az-blog-archive-hero-layout', get_template_directory_uri() . '/css/blog-archive-hero-layout.css', array( 'az-blog-hero' ), filemtime( get_template_directory() . '/css/blog-archive-hero-layout.css' ) );
        }
        if ( $az_is_blog_single ) {
            wp_enqueue_style( 'az-blog-author', get_template_directory_uri() . '/css/blog-author.css', array( 'az-container-sync' ), filemtime( get_template_directory() . '/css/blog-author.css' ) );
            wp_enqueue_style( 'az-blog-single', get_template_directory_uri() . '/css/blog-single.css', array( 'az-blog-author' ), filemtime( get_template_directory() . '/css/blog-single.css' ) );
            wp_enqueue_style( 'az-blog-single-unified', get_template_directory_uri() . '/css/blog-single-unified.css', array( 'az-blog-single' ), filemtime( get_template_directory() . '/css/blog-single-unified.css' ) );
        }
        $az_blog_container_dependency = $az_is_blog_single ? 'az-blog-single-unified' : 'az-blog-archive-hero-layout';
        wp_enqueue_style( 'az-blog-container-sync', get_template_directory_uri() . '/css/blog-container-sync.css', array( $az_blog_container_dependency ), filemtime( get_template_directory() . '/css/blog-container-sync.css' ) );
    }
    if ( is_page() ) {
        $az_page_css_path = get_theme_file_path( 'css/page.css' );
        if ( file_exists( $az_page_css_path ) ) {
            wp_enqueue_style( 'az-page-style', get_theme_file_uri( 'css/page.css' ), array( 'az-ai-theme-style' ), filemtime( $az_page_css_path ) );
        }
    }
    $az_content_css_path = get_theme_file_path( 'css/az-content.css' );
    if ( file_exists( $az_content_css_path ) ) {
        $az_content_dependencies = array( 'az-container-sync' );
        foreach ( array( 'az-woocommerce', 'az-blog-container-sync' ) as $az_content_dependency ) {
            if ( wp_style_is( $az_content_dependency, 'enqueued' ) ) {
                $az_content_dependencies[] = $az_content_dependency;
            }
        }
        wp_enqueue_style( 'az-content', get_theme_file_uri( 'css/az-content.css' ), $az_content_dependencies, filemtime( $az_content_css_path ) );
    }
    $az_price_css_path = get_theme_file_path( 'css/az-price.css' );
    if ( file_exists( $az_price_css_path ) ) {
        $az_price_dependencies = array( 'az-container-sync' );
        if ( wp_style_is( 'az-woocommerce', 'enqueued' ) ) {
            $az_price_dependencies[] = 'az-woocommerce';
        }
        wp_enqueue_style( 'az-price', get_theme_file_uri( 'css/az-price.css' ), $az_price_dependencies, filemtime( $az_price_css_path ) );
    }
    $az_product_card_css_path = get_theme_file_path( 'css/az-product-card.css' );
    if ( file_exists( $az_product_card_css_path ) ) {
        $az_product_card_dependencies = wp_style_is( 'az-price', 'enqueued' ) ? array( 'az-price' ) : array( 'az-container-sync' );
        wp_enqueue_style( 'az-product-card', get_theme_file_uri( 'css/az-product-card.css' ), $az_product_card_dependencies, filemtime( $az_product_card_css_path ) );
    }
    wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0' );
    
    // Language switcher - load in head so it runs before paint (no flash)
    wp_enqueue_script( 'az-language', get_template_directory_uri() . '/js/az-language.js', array(), '1.1.0', false );
    wp_enqueue_script( 'az-logo-variants', get_template_directory_uri() . '/js/az-logo-variants.js', array(), filemtime( get_template_directory() . '/js/az-logo-variants.js' ), true );
    wp_enqueue_script( 'az-sticky-header', get_template_directory_uri() . '/js/az-sticky-header.js', array(), filemtime( get_template_directory() . '/js/az-sticky-header.js' ), true );
    wp_localize_script(
        'az-logo-variants',
        'azLogoVariants',
        array(
            'desktop' => az_get_logo_url( 'desktop' ),
            'mobile'  => az_get_logo_url( 'mobile' ),
            'alt'     => get_bloginfo( 'name' ),
        )
    );
    
    wp_enqueue_script( 'tailwindcss', 'https://cdn.tailwindcss.com?plugins=forms,container-queries', array(), null, false );
    
    wp_add_inline_script( 'tailwindcss', "
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            dark: '#111111',
                            primary: '#292023',
                            cyan: 'var(--primary-color)',
                            magenta: '#fede0c',
                        },
                        dark: {
                            900: '#111111',
                            800: '#181818',
                            700: '#252525',
                        },
                        neon: {
                            blue: 'var(--primary-color)',
                            purple: '#fede0c',
                            green: '#627D47',
                        }
                    },
                    fontFamily: {
                        sans: ['var(--az-font-main)', 'Arial', 'sans-serif'],
                    },
                    boxShadow: {
                        'glow-blue': '0 0 20px -5px rgba(41,32,35,0.45), inset 0 0 10px -5px rgba(41,32,35,0.25)',
                        'glow-purple': '0 0 20px -5px rgba(254,222,12,0.4), inset 0 0 10px -5px rgba(254,222,12,0.22)',
                        'glow-green': '0 0 20px -5px rgba(98,125,71,0.3), inset 0 0 10px -5px rgba(98,125,71,0.18)',
                        'glow-text-blue': '0 0 10px rgba(41,32,35,0.35)',
                    }
                }
            }
        }
    ", 'after' );
}
add_action( 'wp_enqueue_scripts', 'az_ai_theme_scripts' );

function az_find_font_family_in_settings( $settings, $target_slug = '' ) {
    if ( ! is_array( $settings ) ) {
        return '';
    }

    if ( isset( $settings['slug'], $settings['fontFamily'] ) && is_string( $settings['fontFamily'] ) ) {
        if ( '' === $target_slug || sanitize_title( $settings['slug'] ) === $target_slug ) {
            return trim( $settings['fontFamily'] );
        }
    }

    foreach ( array( 'custom', 'theme', 'default' ) as $preferred_key ) {
        if ( isset( $settings[ $preferred_key ] ) ) {
            $found = az_find_font_family_in_settings( $settings[ $preferred_key ], $target_slug );
            if ( $found ) {
                return $found;
            }
        }
    }

    foreach ( $settings as $child ) {
        $found = az_find_font_family_in_settings( $child, $target_slug );
        if ( $found ) {
            return $found;
        }
    }

    return '';
}

function az_normalize_core_font_family( $font_family, $font_settings = array() ) {
    $font_family = is_string( $font_family ) ? trim( $font_family ) : '';
    if ( '' === $font_family ) {
        return '';
    }

    if ( 0 === strpos( $font_family, 'var:preset|font-family|' ) ) {
        $font_slug = sanitize_title( substr( $font_family, strlen( 'var:preset|font-family|' ) ) );
        $resolved  = az_find_font_family_in_settings( $font_settings, $font_slug );

        return $resolved ?: 'var(--wp--preset--font-family--' . $font_slug . ')';
    }

    return $font_family;
}

function az_site_font_stack() {
    return az_get_option( 'typography.body_family', az_default_font_family() );
}

/**
 * Header Nav Walker — renders flat <a> tags for desktop nav
 * Outputs: <a href="..." class="[active]">Label</a>
 */
class AZ_AI_Header_Nav_Walker extends Walker_Nav_Menu {

    public function start_lvl( &$output, $depth = 0, $args = null ) {
        /* Suppress sub-menu UL for top-level — desktop nav is flat */
        if ( $depth === 0 ) return;
        $output .= '<ul class="site-nav-sub">';
    }

    public function end_lvl( &$output, $depth = 0, $args = null ) {
        if ( $depth === 0 ) return;
        $output .= '</ul>';
    }

    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        if ( $depth > 0 ) {
            $output .= '<li>';
            $output .= '<a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a>';
            return;
        }

        $classes  = (array) $item->classes;
        $active   = ( in_array( 'current-menu-item', $classes ) || in_array( 'current-page-ancestor', $classes ) ) ? ' active' : '';
        $label_en = get_post_meta( $item->ID, '_menu_item_label_en', true );
        $bilingual = $label_en
            ? ' data-vi="' . esc_attr( $item->title ) . '" data-en="' . esc_attr( $label_en ) . '"'
            : '';

        $output .= '<a href="' . esc_url( $item->url ) . '" class="' . trim( $active ) . '"' . $bilingual . '>'
                 . esc_html( $item->title )
                 . '</a>';
    }

    public function end_el( &$output, $item, $depth = 0, $args = null ) {
        if ( $depth > 0 ) $output .= '</li>';
    }
}

/**
 * Mobile Nav Walker — accordion-style sub-menus
 */
class AZ_AI_Mobile_Nav_Walker extends Walker_Nav_Menu {

    function start_lvl( &$output, $depth = 0, $args = null ) {
        $output .= '<ul class="az-mobile-submenu">';
    }
    function end_lvl( &$output, $depth = 0, $args = null ) {
        $output .= '</ul>';
    }
    function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $has_children = in_array( 'menu-item-has-children', (array) $item->classes );
        $label_en  = get_post_meta( $item->ID, '_menu_item_label_en', true );
        $bilingual = $label_en ? ' data-vi="' . esc_attr( $item->title ) . '" data-en="' . esc_attr( $label_en ) . '"' : '';
        $uid = 'az-mob-sub-' . $item->ID;

        if ( $depth === 0 ) {
            $output .= '<li class="az-mobile-item' . ( $has_children ? ' az-mobile-has-sub' : '' ) . '">';
            if ( $has_children ) {
                $output .= '<div class="az-mobile-row">';
                $output .= '<a href="' . esc_url( $item->url ) . '" class="az-mobile-link"' . $bilingual . '>' . esc_html( $item->title ) . '</a>';
                $output .= '<button class="az-mobile-toggle" onclick="azMobileToggle(\'' . esc_js( $uid ) . '\',this)" aria-expanded="false">';
                $output .= '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/></svg>';
                $output .= '</button></div>';
                $output .= '<div id="' . esc_attr( $uid ) . '" class="az-mobile-sub-wrap" style="display:none;">';
            } else {
                $output .= '<a href="' . esc_url( $item->url ) . '" class="az-mobile-link"' . $bilingual . '>' . esc_html( $item->title ) . '</a>';
            }
        } else {
            $output .= '<li>';
            $output .= '<a href="' . esc_url( $item->url ) . '" class="az-mobile-sublink"' . $bilingual . '>' . esc_html( $item->title ) . '</a>';
        }
    }
    function end_el( &$output, $item, $depth = 0, $args = null ) {
        $has_children = in_array( 'menu-item-has-children', (array) $item->classes );
        if ( $depth === 0 && $has_children ) {
            $output .= '</div>'; // close az-mobile-sub-wrap
        }
        $output .= '</li>';
    }
}

// ── Dropdown nav CSS ──────────────────────────────────────────────────────────
add_action( 'wp_head', function() { ?>
<style>
/* ── Desktop nav items ── */
.az-menu-item { position:relative; display:flex; align-items:center; }
.az-menu-link {
    display:flex; align-items:center; gap:.3rem;
    color:#d1d5db; font-weight:500; font-size:.9rem;
    text-decoration:none; white-space:nowrap;
    padding:.25rem 0; transition:color .15s;
}
.az-menu-link:hover { color:#fff; }
.az-chevron { transition:transform .2s; flex-shrink:0; margin-top:1px; }

/* ── Dropdown ── */
.az-submenu {
    display:none;
    position:absolute; top:100%; left:50%;
    transform:translateX(-50%);
    min-width:220px;
    background:rgba(10,18,35,.97);
    border:1px solid rgba(37,175,244,.18);
    border-radius:.875rem;
    /* padding-top tạo vùng bridge liền mạch từ menu item xuống dropdown */
    padding:.5rem .4rem .4rem;
    list-style:none; margin:0;
    box-shadow:0 16px 40px rgba(0,0,0,.5);
    z-index:9999;
}
/* Bridge vô hình lấp khoảng trống giữa menu item và dropdown */
.az-submenu::before {
    content:'';
    position:absolute; top:-1rem; left:0; right:0; height:1rem;
    background:transparent;
}
/* Arrow decorative */
.az-submenu::after {
    content:'';
    position:absolute; top:-.45rem; left:50%; transform:translateX(-50%);
    border-left:.45rem solid transparent;
    border-right:.45rem solid transparent;
    border-bottom:.45rem solid rgba(37,175,244,.25);
    pointer-events:none;
}
.az-has-dropdown:hover .az-submenu,
.az-has-dropdown:focus-within .az-submenu { display:block; }
.az-has-dropdown:hover .az-chevron { transform:rotate(180deg); }

.az-submenu-link {
    display:block;
    /* padding rộng hơn để vùng bấm lớn hơn */
    padding:.7rem 1.1rem;
    color:#cbd5e1; font-size:.875rem; font-weight:500;
    text-decoration:none; border-radius:.5rem;
    transition:background .12s, color .12s;
    white-space:nowrap;
}
.az-submenu-link:hover {
    background:rgba(37,175,244,.12);
    color:#25aff4;
}

/* ── Mobile nav ── */
.az-mobile-nav { list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:.25rem; }
.az-mobile-item { list-style:none; }
.az-mobile-row { display:flex; align-items:center; justify-content:space-between; }
.az-mobile-link {
    flex:1; display:block; padding:.6rem 0;
    color:#d1d5db; font-weight:500; font-size:.95rem;
    text-decoration:none; transition:color .15s;
}
.az-mobile-link:hover { color:#25aff4; }
.az-mobile-toggle {
    background:none; border:none; color:#64748b; cursor:pointer;
    padding:.4rem; border-radius:.375rem; transition:color .15s, transform .2s;
}
.az-mobile-toggle:hover { color:#25aff4; }
.az-mobile-toggle.open { transform:rotate(180deg); color:#25aff4; }
.az-mobile-sub-wrap { padding-left:.75rem; border-left:2px solid rgba(37,175,244,.2); margin:.25rem 0 .5rem; }
.az-mobile-submenu { list-style:none; margin:0; padding:0; }
.az-mobile-sublink {
    display:block; padding:.45rem .5rem;
    color:#94a3b8; font-size:.875rem; font-weight:500;
    text-decoration:none; border-radius:.375rem; transition:color .12s, background .12s;
}
.az-mobile-sublink:hover { color:#25aff4; background:rgba(37,175,244,.08); }
</style>
<script>
function azMobileToggle(id, btn) {
    var el = document.getElementById(id);
    if (!el) return;
    var open = el.style.display !== 'none';
    el.style.display = open ? 'none' : 'block';
    btn.classList.toggle('open', !open);
    btn.setAttribute('aria-expanded', String(!open));
}
</script>
<?php }, 5 );
