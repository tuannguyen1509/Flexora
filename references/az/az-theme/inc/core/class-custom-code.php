<?php
defined( 'ABSPATH' ) || exit;
class AZ_Custom_Code {
    private $rendered = array();

    /** One storage resolver for Advanced, legacy settings and frontend output. */
    private static function values( $key, $options = null ) {
        $options = null === $options ? az_get_options() : $options;
        $saved = is_array( $options['custom_code'] ?? null ) ? $options['custom_code'] : array();
        if ( array_key_exists( $key, $saved ) ) { return array( $saved[ $key ] ); }
        $legacy = array(
            'header_code' => array( 'az_hfc_header_scripts' ),
            'body_open_code' => array( 'az_hfc_body_top_scripts' ),
            'footer_code' => array( 'az_hfc_body_bottom_scripts', 'az_hfc_footer_scripts' ),
        );
        return array_map( static function( $option ) { return get_option( $option, '' ); }, $legacy[ $key ] ?? array() );
    }

    public static function editor_values( $options ) {
        $values = array();
        foreach ( array( 'custom_css', 'header_code', 'body_open_code', 'footer_code' ) as $key ) {
            $sources = array_map( array( 'AZ_Options_Sanitizer', 'code_source' ), self::values( $key, $options ) );
            $values[ $key ] = implode( "\n", array_unique( array_filter( $sources, static function( $source ) { return '' !== $source; } ) ) );
        }
        return $values;
    }

    public static function needs_review( $options ) {
        foreach ( array( 'header_code', 'body_open_code', 'footer_code' ) as $key ) {
            foreach ( self::values( $key, $options ) as $value ) {
                $source = AZ_Options_Sanitizer::code_source( $value );
                if ( '' !== $source && $source !== self::trusted_markup( $value ) ) { return true; }
            }
        }
        return false;
    }

    /** Raw scripts are accepted only after an authorized save on this installation. */
    public static function trusted_markup( $value ) {
        $code = AZ_Options_Sanitizer::code_source( $value );
        if ( is_array( $value ) ) {
            $signature = is_string( $value['signature'] ?? null ) ? $value['signature'] : '';
            if ( hash_equals( hash_hmac( 'sha256', $code, wp_salt( 'auth' ) ), $signature ) ) { return $code; }
        } elseif ( is_string( $value ) && preg_match( '/^<!--az-trusted:([a-f0-9]{64})-->/', $value, $match ) ) {
            if ( hash_equals( hash_hmac( 'sha256', $code, wp_salt( 'auth' ) ), $match[1] ) ) { return $code; }
        }
        // Invalid/imported scripts must not appear as visible JavaScript text.
        $safe = preg_replace( '#<(script|style|noscript|iframe)\b[^>]*>.*?</\1\s*>#is', '', $code );
        return wp_kses_post( $safe );
    }

    public function __construct() {
        add_action( 'wp_head', array( $this, 'head' ), 30 );
        add_action( 'wp_body_open', array( $this, 'body' ), 0 );
        add_action( 'wp_footer', array( $this, 'footer' ), 30 );
    }

    private function render( $key ) {
        if ( isset( $this->rendered[ $key ] ) ) { return; }
        $this->rendered[ $key ] = true;
        $seen = array();
        foreach ( self::values( $key ) as $value ) {
            $markup = self::trusted_markup( $value );
            if ( '' === $markup || isset( $seen[ $markup ] ) ) { continue; }
            $seen[ $markup ] = true;
            echo "\n" . $markup . "\n";
        }
    }

    public function head() {
        if ( ! isset( $this->rendered['custom_css'] ) ) {
            $this->rendered['custom_css'] = true;
            $values = self::editor_values( az_get_options() );
            $css = wp_strip_all_tags( $values['custom_css'] );
            if ( '' !== $css ) { echo '<style id="az-custom-css">' . $css . '</style>'; }
        }
        $this->render( 'header_code' );
    }
    public function body() { $this->render( 'body_open_code' ); }
    public function footer() { $this->render( 'footer_code' ); }
}
new AZ_Custom_Code();
