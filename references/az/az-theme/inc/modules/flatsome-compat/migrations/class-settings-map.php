<?php
/**
 * Verified legacy settings to AZ settings map.
 *
 * @package AZ_AI_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'AZ_Flatsome_Settings_Map' ) ) {
    class AZ_Flatsome_Settings_Map {
        /**
         * Return only mappings explicitly registered by a verified scanner result.
         * No Flatsome option name is assumed here.
         *
         * @param array $entries Verified entries from a scanner or adapter.
         * @return array
         */
        public static function normalize( $entries ) {
            if ( ! is_array( $entries ) ) {
                return array();
            }

            $normalized = array();
            foreach ( $entries as $entry ) {
                if ( ! is_array( $entry ) || empty( $entry['source_key'] ) || empty( $entry['target_path'] ) ) {
                    continue;
                }

                $source_type = isset( $entry['source_type'] ) ? sanitize_key( $entry['source_type'] ) : '';
                if ( ! in_array( $source_type, array( 'option', 'theme_mod' ), true ) ) {
                    continue;
                }

                $normalized[] = array(
                    'id'          => sanitize_key( $entry['id'] ?? $entry['source_key'] ),
                    'source_type' => $source_type,
                    'source_key'  => sanitize_text_field( $entry['source_key'] ),
                    'target_path' => sanitize_text_field( $entry['target_path'] ),
                    'label'       => sanitize_text_field( $entry['label'] ?? $entry['source_key'] ),
                    'category'    => sanitize_key( $entry['category'] ?? 'general' ),
                    'evidence'    => sanitize_text_field( $entry['evidence'] ?? 'Scanner verified' ),
                    'value'       => $entry['value'] ?? null,
                );
            }

            return $normalized;
        }

        /**
         * Build a user-facing preview without reading or writing the database.
         *
         * @param array $entries Verified map entries with values.
         * @param array $az_values Existing AZ settings.
         * @return array
         */
        public static function preview( $entries, $az_values ) {
            $az_values = is_array( $az_values ) ? $az_values : array();
            $preview   = array();

            foreach ( self::normalize( $entries ) as $entry ) {
                $existing = self::array_get( $az_values, $entry['target_path'], null );
                $has_value = null !== $entry['value'];
                $preview[] = array(
                    'id'            => $entry['id'],
                    'legacy_value'  => $has_value ? $entry['value'] : null,
                    'az_value'      => $existing,
                    'target_path'   => $entry['target_path'],
                    'action'        => ! $has_value ? 'skip-no-verified-value' : ( null === $existing ? 'add' : 'preserve-unless-selected' ),
                    'evidence'      => $entry['evidence'],
                    'category'      => $entry['category'],
                    'label'         => $entry['label'],
                );
            }

            return $preview;
        }

        private static function array_get( $array, $path, $default = null ) {
            $value = $array;
            foreach ( explode( '.', (string) $path ) as $segment ) {
                if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
                    return $default;
                }
                $value = $value[ $segment ];
            }
            return $value;
        }
    }
}
