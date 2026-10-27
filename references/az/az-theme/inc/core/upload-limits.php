<?php
/** Optional per-file limit for WordPress uploads and sideloads. */
defined( 'ABSPATH' ) || exit;

function az_upload_file_limit_bytes() {
    $value = az_get_option( 'performance.upload_max_file_size_mb', 0 );
    if ( ! is_numeric( $value ) || ! is_finite( (float) $value ) || (float) $value <= 0 ) {
        return 0;
    }

    // Cap before converting to int to avoid overflow from malformed saved options.
    return (int) floor( min( (float) $value, intdiv( PHP_INT_MAX, MB_IN_BYTES ) ) * MB_IN_BYTES );
}

function az_upload_size_limit( $size ) {
    $limit = az_upload_file_limit_bytes();
    return $limit > 0 ? min( $size, $limit ) : $size;
}
add_filter( 'upload_size_limit', 'az_upload_size_limit', 100 );

function az_validate_upload_file_size( $file ) {
    $limit = az_upload_file_limit_bytes();
    if ( $limit <= 0 || ! empty( $file['error'] ) ) {
        return $file;
    }

    $limit = min( $limit, wp_max_upload_size() );
    $size = isset( $file['size'] ) ? (float) $file['size'] : 0;
    // REST raw uploads and remote media imports may omit the size field.
    if ( ! empty( $file['tmp_name'] ) && is_file( $file['tmp_name'] ) ) {
        $size = max( $size, wp_filesize( $file['tmp_name'] ) );
    }

    if ( $size > $limit ) {
        $file['error'] = sprintf(
            /* translators: %s: maximum allowed file size. */
            __( 'File vượt quá dung lượng cho phép. Dung lượng tối đa cho mỗi file là %s.', 'az-theme' ),
            size_format( $limit, 2 )
        );
    }
    return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'az_validate_upload_file_size', 100 );
add_filter( 'wp_handle_sideload_prefilter', 'az_validate_upload_file_size', 100 );
