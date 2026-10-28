<?php
defined( 'ABSPATH' ) || exit;
class AZ_Theme_Options {
    public static function add_section( $id, $title, $fields = array() ) { AZ_Options_Registry::add_section( $id, $title, $fields ); }
    public static function add_field( $section, $field ) { AZ_Options_Registry::add_field( $section, $field ); }
    public static function get() { return AZ_Options_Registry::all(); }
}
