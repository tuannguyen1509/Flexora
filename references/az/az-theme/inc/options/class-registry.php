<?php
defined( 'ABSPATH' ) || exit;
class AZ_Options_Registry {
    private static $sections = array();
    public static function add_section( $id, $title, $fields = array() ) { self::$sections[ sanitize_key( $id ) ] = array( 'id' => sanitize_key( $id ), 'title' => sanitize_text_field( $title ), 'fields' => $fields ); }
    public static function add_field( $section, $field ) { if ( isset( self::$sections[ $section ] ) ) { self::$sections[ $section ]['fields'][] = $field; } }
    public static function all() { return self::$sections; }
    public static function get( $id ) { return self::$sections[ sanitize_key( $id ) ] ?? null; }
}
