<?php
defined( 'ABSPATH' ) || exit;
class AZ_Options_Sanitizer {
    public static function code_source( $value ) {
        if ( is_array( $value ) ) { return is_string( $value['code'] ?? null ) ? $value['code'] : ''; }
        return is_scalar( $value ) ? preg_replace( '/^<!--az-trusted:[a-f0-9]{64}-->/', '', (string) $value ) : '';
    }
    public static function sanitize( $value, $type, $field = array() ) {
        switch ( $type ) {
            case 'email': return sanitize_email( (string) $value );
            case 'url': return esc_url_raw( (string) $value );
            case 'integer': case 'number': case 'range':
                $number = is_numeric( $value ) ? ( 'integer' === $type ? (int) $value : (float) $value ) : 0;
                if ( isset( $field['min'] ) ) { $number = max( $field['min'], $number ); }
                if ( isset( $field['max'] ) ) { $number = min( $field['max'], $number ); }
                return $number;
            case 'color': return sanitize_hex_color( $value ) ?: '';
            case 'checkbox': case 'switch': return empty( $value ) ? 0 : 1;
            case 'image': case 'media': return absint( $value );
            case 'attribute_filters':
                $allowed = function_exists( 'az_storefront_filter_definitions' ) ? az_storefront_filter_definitions() : array();
                $rows = is_array( $value ) ? $value : array();
                $clean = array();
                $used = array();
                foreach ( $rows as $row ) {
                    if ( ! is_array( $row ) ) { continue; }
                    $key = sanitize_key( $row['taxonomy'] ?? $row['key'] ?? '' );
                    if ( ! isset( $allowed[ $key ] ) || isset( $used[ $key ] ) ) { continue; }
                    $clean[] = array( 'taxonomy' => $key, 'enabled' => empty( $row['enabled'] ) ? 0 : 1 );
                    $used[ $key ] = true;
                }
                return $clean;
            case 'repeater':
                $rows = is_array( $value ) ? $value : array(); $clean = array();
                foreach ( $rows as $row ) { if ( ! is_array( $row ) ) { continue; } $item = array(); foreach ( $field['fields'] ?? array() as $subfield ) { $item[ $subfield['id'] ] = self::sanitize( $row[ $subfield['id'] ] ?? '', $subfield['type'], $subfield ); } if ( array_filter( $item, static function( $item_value ) { return '' !== $item_value && 0 !== $item_value; } ) ) { $clean[] = $item; } }
                return $clean;
            case 'code':
                if ( ! current_user_can( 'manage_options' ) ) { return ''; }
                $code = self::code_source( $value );
                if ( 'custom_css' === ( $field['id'] ?? '' ) ) { return wp_strip_all_tags( $code ); }
                if ( ! current_user_can( 'unfiltered_html' ) ) { return wp_kses_post( $code ); }
                return array( 'code' => $code, 'signature' => hash_hmac( 'sha256', $code, wp_salt( 'auth' ) ) );
            case 'map_embed':
                // Rebuild a single trusted iframe; never retain unchecked extra frames.
                if ( ! preg_match( '/<iframe\b[^>]*\bsrc\s*=\s*(["\x27])(.*?)\1/is', (string) $value, $source ) ) { return ''; }
                $src = html_entity_decode( $source[2], ENT_QUOTES, 'UTF-8' );
                $host = strtolower( (string) wp_parse_url( $src, PHP_URL_HOST ) );
                if ( 'https' !== wp_parse_url( $src, PHP_URL_SCHEME ) || ! in_array( $host, array( 'google.com', 'www.google.com', 'maps.google.com', 'google.com.vn', 'www.google.com.vn', 'maps.google.com.vn' ), true ) ) { return ''; }
                return '<iframe src="' . esc_url( $src ) . '" title="Google Maps" width="100%" height="320" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>';
            case 'html': return wp_kses_post( (string) $value );
            case 'textarea': return sanitize_textarea_field( (string) $value );
            case 'select': return isset( $field['options'][ $value ] ) ? $value : ( $field['default'] ?? '' );
            default: return sanitize_text_field( (string) $value );
        }
    }
}
