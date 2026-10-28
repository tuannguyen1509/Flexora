<?php
defined( 'ABSPATH' ) || exit;
/** Best-effort application limiter; use a server/WAF limit for concurrent floods. */
function az_auth_rate_allowed( $scope, $identity, $limit, $window ) {
    $key = 'az_rate_' . hash_hmac( 'sha256', $scope . ':' . $identity, wp_salt( 'auth' ) );
    $record = get_transient( $key );
    if ( ! is_array( $record ) || ( $record['until'] ?? 0 ) <= time() ) { $record = array( 'count' => 0, 'until' => time() + $window ); }
    if ( $record['count'] >= $limit ) { return false; }
    $record['count']++;
    return (bool) set_transient( $key, $record, max( 1, $record['until'] - time() ) );
}
function az_auth_client_identity() {
    // Never trust arbitrary X-Forwarded-For headers from visitors.
    return (string) ( $_SERVER['REMOTE_ADDR'] ?? 'unknown' );
}
