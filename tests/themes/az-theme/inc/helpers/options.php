<?php
defined( 'ABSPATH' ) || exit;
function az_get_options() { $options = get_option( 'az_theme_options', array() ); return is_array( $options ) ? $options : array(); }
function az_array_get( $array, $path, $default = null ) { $value = $array; foreach ( explode( '.', (string) $path ) as $segment ) { if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) { return $default; } $value = $value[ $segment ]; } return $value; }
function az_get_option( $path, $default = null ) { return az_array_get( az_get_options(), $path, $default ); }
require_once __DIR__ . '/contact-maps.php';

/** The font ships with the parent theme and needs no Font Library installation. */
function az_default_font_family() {
    return '"Be Vietnam Pro", sans-serif';
}

function az_is_bundled_font_family( $family ) {
    $primary = trim( explode( ',', (string) $family )[0], " \t\n\r\0\x0B\"'" );
    return 0 === strcasecmp( $primary, 'Be Vietnam Pro' );
}

/** WordPress before 6.4 does not print theme.json font faces. */
function az_enqueue_bundled_font_fallback() {
    if ( function_exists( 'wp_print_font_faces' ) ) {
        return;
    }
    wp_enqueue_style( 'az-be-vietnam-pro', get_template_directory_uri() . '/css/be-vietnam-pro.css', array(), filemtime( get_template_directory() . '/css/be-vietnam-pro.css' ) );
}
add_action( 'wp_enqueue_scripts', 'az_enqueue_bundled_font_fallback' );
add_action( 'enqueue_block_assets', 'az_enqueue_bundled_font_fallback' );

function az_get_company_name() { $all = az_get_options(); return array_key_exists( 'company_name', $all['general'] ?? array() ) ? $all['general']['company_name'] : get_option( 'az_contact_company', get_bloginfo( 'name' ) ); }
function az_get_contact_items( $type = 'phone' ) { $key = 'address' === $type ? 'addresses' : ( 'email' === $type ? 'emails' : 'phones' ); $all = az_get_options(); $general = isset( $all['general'] ) && is_array( $all['general'] ) ? $all['general'] : array(); if ( ! empty( $general[ $key ] ) && is_array( $general[ $key ] ) ) { $items = array(); foreach ( $general[ $key ] as $row ) { if ( is_array( $row ) && ! empty( $row['value'] ) && is_scalar( $row['value'] ) ) { $items[] = array( 'label' => sanitize_text_field( $row['label'] ?? ( 'phone' === $type ? 'Hotline' : ( 'email' === $type ? 'Email' : 'Địa chỉ' ) ) ), 'value' => 'address' === $type ? sanitize_textarea_field( $row['value'] ) : ( 'email' === $type ? sanitize_email( $row['value'] ) : sanitize_text_field( $row['value'] ) ) ); } } return $items; } if ( 'address' === $type ) { return array(); } $legacy_keys = 'email' === $type ? array( 'email', 'sales_email', 'support_email' ) : array( 'phone' ); $items = array(); foreach ( $legacy_keys as $legacy_key ) { $raw = array_key_exists( $legacy_key, $general ) ? $general[ $legacy_key ] : get_option( 'email' === $type ? ( 'email' === $legacy_key ? 'az_contact_email' : ( 'sales_email' === $legacy_key ? 'az_contact_sales_email' : 'az_contact_support_email' ) ) : 'az_contact_phone', '' ); if ( '' !== trim( (string) $raw ) ) { $items[] = array( 'label' => 'phone' === $type ? 'Hotline' : ( 'email' === $type ? ucwords( str_replace( '_', ' ', $legacy_key ) ) : 'Địa chỉ' ), 'value' => 'email' === $type ? sanitize_email( $raw ) : trim( $raw ) ); } } return $items; }
function az_get_phone() { $items = az_get_contact_items( 'phone' ); return $items[0]['value'] ?? get_option( 'az_contact_phone', '' ); }
function az_get_email() { $all = az_get_options(); return array_key_exists( 'email', $all['general'] ?? array() ) ? $all['general']['email'] : get_option( 'az_contact_email', get_option( 'admin_email' ) ); }
function az_get_address() { $items = az_get_contact_items( 'address' ); return $items[0]['value'] ?? ''; }
function az_contact_shortcode( $atts ) { $atts = shortcode_atts( array( 'type' => 'phone', 'format' => 'list' ), $atts, 'az_contact' ); if ( 'map' === $atts['type'] ) { return az_google_map_shortcode(); } $type = in_array( $atts['type'], array( 'address', 'email' ), true ) ? $atts['type'] : 'phone'; $items = az_get_contact_items( $type ); if ( ! $items ) { return ''; } $html = '<div class="az-contact-list az-contact-list--' . esc_attr( $type ) . '">'; foreach ( $items as $item ) { $value = esc_html( $item['value'] ); if ( 'phone' === $type ) { $href = preg_replace( '/[^0-9+]/', '', $item['value'] ); $value = '<a href="tel:' . esc_attr( $href ) . '">' . $value . '</a>'; } elseif ( 'email' === $type ) { $value = '<a href="mailto:' . esc_attr( $item['value'] ) . '">' . $value . '</a>'; } $html .= '<div class="az-contact-item"><span class="az-contact-item__label">' . esc_html( $item['label'] ) . '</span><span class="az-contact-item__value">' . $value . '</span></div>'; } return $html . '</div>'; }
add_shortcode( 'az_contact', 'az_contact_shortcode' );
function az_general_shortcode( $atts ) { $atts = shortcode_atts( array( 'field' => '' ), $atts, 'az_general' ); $allowed = array( 'company_name', 'brand_name', 'tax_code', 'representative', 'business_registration', 'website', 'contact_person', 'phone', 'phone_secondary', 'email', 'sales_email', 'support_email', 'fax', 'working_hours', 'address', 'address_secondary', 'google_maps_url', 'google_maps_embed', 'copyright' ); $field = sanitize_key( $atts['field'] ); if ( ! in_array( $field, $allowed, true ) ) { return ''; } if ( 'google_maps_embed' === $field ) { return az_google_map_shortcode(); } if ( in_array( $field, array( 'address', 'address_secondary', 'google_maps_url' ), true ) ) { return esc_html( az_get_location_field( $field ) ); } $value = az_get_option( 'general.' . $field, null ); if ( null === $value || '' === $value ) { $legacy = array( 'company_name' => 'az_contact_company', 'brand_name' => 'az_contact_brand', 'tax_code' => 'az_contact_tax_code', 'representative' => 'az_contact_representative', 'business_registration' => 'az_contact_registration', 'website' => 'az_contact_website', 'contact_person' => 'az_contact_person', 'phone' => 'az_contact_phone', 'phone_secondary' => 'az_contact_phone_2', 'email' => 'az_contact_email', 'sales_email' => 'az_contact_sales_email', 'support_email' => 'az_contact_support_email', 'fax' => 'az_contact_fax', 'working_hours' => 'az_contact_hours', 'copyright' => '' ); $value = $legacy[ $field ] ? get_option( $legacy[ $field ], '' ) : ''; } return esc_html( $value ); }
add_shortcode( 'az_general', 'az_general_shortcode' );

function az_ministry_of_trade_badge() {
    $url = esc_url( az_get_option( 'general.ministry_of_trade_url', '' ), array( 'https', 'http' ) );
    if ( '' === $url ) { return ''; }
    return '<a class="az-ministry-badge" href="' . $url . '" target="_blank" rel="noopener noreferrer" aria-label="Xem hồ sơ thông báo Bộ Công Thương"><img src="https://fileserver.online.gov.vn/uploads/Resources/iconxacnhan/DaThongBao.png" alt="Đã thông báo với Bộ Công Thương" loading="lazy" decoding="async"></a>';
}
add_shortcode( 'az_ministry_of_trade', 'az_ministry_of_trade_badge' );
function az_get_social_links() { $map = array( 'facebook' => 'az_footer_social_facebook', 'youtube' => 'az_footer_social_youtube', 'tiktok' => 'az_footer_social_tiktok', 'instagram' => '', 'linkedin' => 'az_footer_social_linkedin', 'pinterest' => '', 'x' => '', 'zalo' => 'az_contact_zalo_url', 'messenger' => 'az_contact_messenger_url', 'whatsapp' => '' ); $all = az_get_options(); $social = isset( $all['social'] ) && is_array( $all['social'] ) ? $all['social'] : array(); $links = array(); foreach ( $map as $key => $legacy ) { if ( array_key_exists( $key, $social ) ) { $value = $social[ $key ]; } elseif ( $legacy ) { $value = get_option( $legacy, '' ); } else { $value = ''; } if ( $value ) { $links[ $key ] = esc_url( $value ); } } return $links; }
function az_get_logo_id( $variant = 'desktop' ) { $keys = array( 'desktop' => 'logo', 'mobile' => 'logo_mobile', 'sticky' => 'logo_sticky', 'light' => 'logo_light', 'dark' => 'logo_dark' ); $key = $keys[ $variant ] ?? 'logo'; $all = az_get_options(); if ( isset( $all['branding'][ $key ] ) && $all['branding'][ $key ] ) { return absint( $all['branding'][ $key ] ); } return 'desktop' === $variant ? absint( get_theme_mod( 'custom_logo', 0 ) ) : 0; }
function az_get_logo_url( $variant = 'desktop' ) { $id = az_get_logo_id( $variant ); return $id ? wp_get_attachment_image_url( $id, 'full' ) : ''; }
function az_get_homepage_mode() { return az_get_option( 'homepage.mode', 'shop' ); }
function az_get_home_h1() {
    $az_custom_h1 = trim( (string) az_get_option( 'branding.home_h1', '' ) );

    if ( '' !== $az_custom_h1 ) {
        return $az_custom_h1;
    }

    // WordPress site title is the canonical fallback used for og:site_name.
    $az_site_name = trim( wp_strip_all_tags( (string) get_bloginfo( 'name', 'display' ) ) );

    if ( '' !== $az_site_name ) {
        return $az_site_name;
    }

    // If the site title is empty, fall back to the front-page/document title
    // that normally provides the value for og:title.
    $az_front_page_id = absint( get_option( 'page_on_front' ) );
    $az_page_title    = $az_front_page_id ? get_the_title( $az_front_page_id ) : '';
    $az_page_title    = trim( wp_strip_all_tags( (string) $az_page_title ) );

    return '' !== $az_page_title ? $az_page_title : trim( wp_strip_all_tags( (string) wp_get_document_title() ) );
}

/**
 * Render the homepage's required SEO heading before any child-theme header.
 *
 * wp_body_open() is part of the WordPress header contract, so this remains
 * available when a site replaces the parent header with its own child header.
 */
function az_render_home_seo_h1() {
    if ( ! is_front_page() ) {
        return;
    }

    $az_home_h1 = az_get_home_h1();

    if ( '' === $az_home_h1 ) {
        return;
    }

    printf( '<h1 class="az-home-seo-h1 az-sr-only">%s</h1>', esc_html( $az_home_h1 ) );
}
add_action( 'wp_body_open', 'az_render_home_seo_h1', 5 );
/**
 * Return custom fonts saved through WordPress Font Library.
 *
 * Font Library stores each family as a wp_font_family post rather than in
 * theme.json, so wp_get_global_settings() alone cannot see inactive fonts.
 */
function az_get_font_library_families() {
    static $families = null;

    if ( null !== $families ) {
        return $families;
    }

    $families = array();
    if ( ! post_type_exists( 'wp_font_family' ) ) {
        return $families;
    }

    $posts = get_posts(
        array(
            'post_type'              => 'wp_font_family',
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'orderby'                => 'title',
            'order'                  => 'ASC',
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        )
    );

    foreach ( $posts as $post ) {
        $settings = json_decode( $post->post_content, true );
        $family   = is_array( $settings ) ? trim( (string) ( $settings['fontFamily'] ?? '' ) ) : '';
        if ( '' === $family ) {
            continue;
        }

        $families[ $family ] = array(
            'id'   => (int) $post->ID,
            'name' => $post->post_title ?: $family,
        );
    }

    return $families;
}

function az_collect_global_font_options() {
    $options = array();
    if ( ! function_exists( 'wp_get_global_settings' ) ) {
        return $options;
    }

    $settings = wp_get_global_settings( array( 'typography', 'fontFamilies' ) );
    $walk     = function( $items ) use ( &$walk, &$options ) {
        if ( ! is_array( $items ) ) {
            return;
        }
        if ( ! empty( $items['fontFamily'] ) && ! empty( $items['name'] ) ) {
            $options[ trim( (string) $items['fontFamily'] ) ] = (string) $items['name'];
            return;
        }
        foreach ( $items as $item ) {
            $walk( $item );
        }
    };
    $walk( $settings );

    return $options;
}

function az_get_font_options() {
    $options = array(
        'Arial, sans-serif'    => 'Arial',
        'Helvetica, sans-serif' => 'Helvetica',
        'Georgia, serif'       => 'Georgia',
        'system-ui, sans-serif' => 'System UI',
    );

    foreach ( az_collect_global_font_options() as $family => $name ) {
        $options[ $family ] = $name;
    }
    foreach ( az_get_font_library_families() as $family => $font ) {
        $options[ $family ] = $font['name'] . ' (Font Library)';
    }

    // Keep the bundled default available even before Font Library is populated.
    $options = array( az_default_font_family() => 'Be Vietnam Pro (Có sẵn trong giao diện)' ) + $options;

    return $options;
}

/**
 * Print local Font Library faces when a font is selected in Theme Advanced
 * but has not been activated in the global styles settings.
 */
function az_print_selected_font_library_faces() {
    if ( is_admin() || ! function_exists( 'wp_print_font_faces' ) ) {
        return;
    }

    $selected = array_filter(
        array(
            trim( (string) az_get_option( 'typography.body_family', '' ) ),
            trim( (string) az_get_option( 'typography.heading_family', '' ) ),
        )
    );
    if ( ! $selected ) {
        return;
    }

    $font_faces      = array();
    foreach ( az_get_font_library_families() as $family => $font ) {
        // Classic/child themes do not necessarily print faces from global settings.
        if ( az_is_bundled_font_family( $family ) || ! in_array( $family, $selected, true ) ) {
            continue;
        }

        $faces = get_children(
            array(
                'post_parent'            => $font['id'],
                'post_type'              => 'wp_font_face',
                'post_status'            => 'publish',
                'numberposts'            => -1,
                'orderby'                => 'ID',
                'order'                  => 'ASC',
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            )
        );
        foreach ( $faces as $face ) {
            $settings = json_decode( $face->post_content, true );
            if ( ! is_array( $settings ) || empty( $settings['src'] ) ) {
                continue;
            }
            $settings['font-family'] = $family;
            foreach ( array( 'fontStyle' => 'font-style', 'fontWeight' => 'font-weight', 'fontStretch' => 'font-stretch', 'fontDisplay' => 'font-display', 'unicodeRange' => 'unicode-range', 'sizeAdjust' => 'size-adjust', 'ascentOverride' => 'ascent-override', 'descentOverride' => 'descent-override', 'lineGapOverride' => 'line-gap-override', 'fontVariant' => 'font-variant', 'fontFeatureSettings' => 'font-feature-settings', 'fontVariationSettings' => 'font-variation-settings' ) as $source => $target ) {
                if ( isset( $settings[ $source ] ) ) {
                    $settings[ $target ] = $settings[ $source ];
                    unset( $settings[ $source ] );
                }
            }
            unset( $settings['fontFamily'] );
            $font_faces[] = $settings;
        }
    }

    if ( $font_faces ) {
        wp_print_font_faces( array( $font_faces ) );
    }
}
add_action( 'wp_head', 'az_print_selected_font_library_faces', 45 );
