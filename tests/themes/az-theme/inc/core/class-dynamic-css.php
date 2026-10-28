<?php
defined( 'ABSPATH' ) || exit;

class AZ_Dynamic_CSS {
    public function __construct() {
        add_action( 'wp_head', array( $this, 'render' ), 20 );
    }

    public function render() {
        $colors = array(
            'primary'         => '#f7941d',
            'primary_hover'   => '#d97706',
            'secondary'       => '#222222',
            'secondary_hover' => '#111111',
            'accent'          => '#f7941d',
            'accent_hover'    => '#d97706',
            'heading'         => '#222222',
            'text'            => '#424242',
            'muted'           => '#777777',
            'border'          => '#e5e5e5',
            'surface'         => '#ffffff',
            'page_background' => '#f4f6f8',
            'success'         => '#2e7d32',
            'warning'         => '#ed6c02',
            'error'           => '#d32f2f',
            'info'            => '#0288d1',
        );
        $vars   = array();

        foreach ( $colors as $key => $default ) {
            $name = array(
                'primary_hover'   => 'primary-dark',
                'secondary_hover' => 'secondary-dark',
                'accent_hover'    => 'accent-dark',
                'page_background' => 'page',
            )[ $key ] ?? $key;
            $vars[ '--az-color-' . $name ] = az_get_option( 'colors.' . $key, $default );
        }

        $number = static function( $key, $default, $min, $max ) {
            $value = (float) az_get_option( 'typography.' . $key, $default );
            return min( $max, max( $min, $value ) );
        };
        $px = static function( $key, $default, $min, $max ) use ( $number ) {
            return (int) round( $number( $key, $default, $min, $max ) ) . 'px';
        };
        $em = static function( $key, $default, $min, $max ) use ( $number ) {
            return rtrim( rtrim( (string) $number( $key, $default, $min, $max ), '0' ), '.' ) . 'em';
        };

        $vars['--az-font-main']                = az_get_option( 'typography.body_family', az_default_font_family() );
        $vars['--az-font-heading']             = az_get_option( 'typography.heading_family', az_default_font_family() );
        $vars['--az-body-size']                = $px( 'body_size', 16, 12, 24 );
        $vars['--az-body-size-tablet']         = $px( 'body_size_tablet', 16, 12, 22 );
        $vars['--az-body-size-mobile']         = $px( 'body_size_mobile', 16, 12, 20 );
        $vars['--az-font-weight']              = (int) $number( 'body_weight', 400, 300, 900 );
        $vars['--az-body-line-height']         = $number( 'body_line_height', 1.55, 1.2, 2.2 );
        $vars['--az-body-letter-spacing']      = $em( 'body_letter_spacing', 0, -0.05, 0.15 );
        $vars['--az-heading-weight']           = (int) $number( 'heading_weight', 600, 300, 900 );
        $vars['--az-heading-line-height']      = $number( 'heading_line_height', 1.2, 1, 1.6 );
        $vars['--az-heading-letter-spacing']   = $em( 'heading_letter_spacing', 0, -0.05, 0.1 );
        $vars['--az-navigation-size']          = $px( 'navigation_size', 16, 12, 24 );
        $vars['--az-navigation-weight']        = (int) $number( 'navigation_weight', 600, 300, 900 );
        $vars['--az-navigation-letter-spacing'] = $em( 'navigation_letter_spacing', 0, -0.05, 0.15 );

        foreach ( array( 'h1' => array( 48, 40, 32, 24, 96, 22, 80, 20, 64 ), 'h2' => array( 34, 30, 26, 20, 72, 20, 64, 18, 56 ), 'h3' => array( 26, 24, 22, 18, 56, 18, 48, 17, 42 ), 'h4' => array( 18, 18, 18, 16, 40, 16, 36, 16, 32 ) ) as $heading => $sizes ) {
            $vars[ '--az-' . $heading . '-size' ]        = $px( $heading . '_desktop', $sizes[0], $sizes[3], $sizes[4] );
            $vars[ '--az-' . $heading . '-size-tablet' ] = $px( $heading . '_tablet', $sizes[1], $sizes[5], $sizes[6] );
            $vars[ '--az-' . $heading . '-size-mobile' ] = $px( $heading . '_mobile', $sizes[2], $sizes[7], $sizes[8] );
        }

        $vars['--az-container']        = absint( az_get_option( 'layout.container', 1110 ) ) . 'px';
        $vars['--az-container-tablet'] = absint( az_get_option( 'layout.container_tablet', 960 ) ) . 'px';
        $vars['--az-gutter']           = absint( az_get_option( 'layout.grid_gutter', 24 ) ) . 'px';
        $vars['--az-mobile-gutter']    = absint( az_get_option( 'layout.mobile_padding', 24 ) ) . 'px';
        $vars['--az-radius-sm']        = absint( az_get_option( 'layout.radius_small', 4 ) ) . 'px';
        $vars['--az-radius-md']        = absint( az_get_option( 'layout.radius_medium', 8 ) ) . 'px';
        $vars['--az-radius-lg']        = absint( az_get_option( 'layout.radius_large', 14 ) ) . 'px';

        $logo_width_desktop = min( 600, max( 40, absint( az_get_option( 'branding.logo_width', 180 ) ) ) );
        $logo_width_mobile  = min( 400, max( 32, absint( az_get_option( 'branding.logo_width_mobile', 140 ) ) ) );

        echo '<style id="az-theme-dynamic-css">:root{';
        foreach ( $vars as $key => $value ) {
            // Style elements contain raw text: HTML entities break quoted font names.
            // Remove CSS declaration/HTML delimiters while preserving family quotes.
            $css_value = preg_replace( '/[<>;{}\r\n]/', '', (string) $value );
            echo esc_html( $key ) . ':' . $css_value . ';';
        }
        echo '}.az-site .az-site-header .az-logo img{width:' . esc_attr( $logo_width_desktop ) . 'px;max-width:100%;height:auto}@media(max-width:960px){:root{--az-body-size:var(--az-body-size-tablet);--az-h1-size:var(--az-h1-size-tablet);--az-h2-size:var(--az-h2-size-tablet);--az-h3-size:var(--az-h3-size-tablet);--az-h4-size:var(--az-h4-size-tablet)}}@media(max-width:782px){:root{--az-body-size:var(--az-body-size-mobile);--az-h1-size:var(--az-h1-size-mobile);--az-h2-size:var(--az-h2-size-mobile);--az-h3-size:var(--az-h3-size-mobile);--az-h4-size:var(--az-h4-size-mobile)}.az-site .az-site-header .az-logo img{width:' . esc_attr( $logo_width_mobile ) . 'px}}</style>';
    }
}

new AZ_Dynamic_CSS();
