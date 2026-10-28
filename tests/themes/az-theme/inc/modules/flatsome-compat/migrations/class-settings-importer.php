<?php
/**
 * Explicit settings importer for verified legacy migration entries.
 *
 * @package AZ_AI_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'AZ_Flatsome_Settings_Importer' ) ) {
    class AZ_Flatsome_Settings_Importer {
        /**
         * Import selected preview rows. The caller must provide a backup callback.
         *
         * @param array    $preview_rows Rows returned by the map preview.
         * @param array    $selected_ids Explicitly selected row IDs.
         * @param callable $backup_callback Callback executed before the first write.
         * @param array    $current_settings Existing AZ settings.
         * @return array|WP_Error
         */
        public static function import( $preview_rows, $selected_ids, $backup_callback, $current_settings = array() ) {
            if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
                return new WP_Error( 'az_migration_forbidden', 'Migration requires manage_options.' );
            }

            if ( ! is_array( $preview_rows ) || ! is_array( $selected_ids ) || ! is_callable( $backup_callback ) ) {
                return new WP_Error( 'az_migration_invalid_request', 'Preview, selection and backup callback are required.' );
            }

            $selected_ids = array_map( 'sanitize_key', $selected_ids );
            $settings     = is_array( $current_settings ) ? $current_settings : array();
            $writes       = array();

            foreach ( $preview_rows as $row ) {
                if ( ! is_array( $row ) || ! in_array( sanitize_key( $row['id'] ?? '' ), $selected_ids, true ) || null === ( $row['legacy_value'] ?? null ) ) {
                    continue;
                }
                $writes[] = $row;
            }

            if ( empty( $writes ) ) {
                return array( 'imported' => 0, 'skipped' => count( $preview_rows ), 'settings' => $settings );
            }

            $backup = call_user_func( $backup_callback, $settings, $writes );
            if ( is_wp_error( $backup ) || false === $backup ) {
                return is_wp_error( $backup ) ? $backup : new WP_Error( 'az_migration_backup_failed', 'Migration backup failed.' );
            }

            foreach ( $writes as $row ) {
                self::array_set( $settings, $row['target_path'], $row['legacy_value'] );
            }

            $updated = update_option( 'az_theme_options', $settings, false );
            if ( ! $updated && get_option( 'az_theme_options', array() ) !== $settings ) {
                return new WP_Error( 'az_migration_write_failed', 'AZ settings could not be saved.' );
            }

            return array(
                'imported' => count( $writes ),
                'skipped'  => count( $preview_rows ) - count( $writes ),
                'settings' => $settings,
            );
        }

        private static function array_set( &$array, $path, $value ) {
            $segments = explode( '.', (string) $path );
            $last     = sanitize_key( array_pop( $segments ) );
            $cursor   =& $array;
            foreach ( $segments as $segment ) {
                $segment = sanitize_key( $segment );
                if ( '' === $segment ) {
                    continue;
                }
                if ( ! isset( $cursor[ $segment ] ) || ! is_array( $cursor[ $segment ] ) ) {
                    $cursor[ $segment ] = array();
                }
                $cursor =& $cursor[ $segment ];
            }
            if ( '' !== $last ) {
                $cursor[ $last ] = $value;
            }
        }
    }
}
