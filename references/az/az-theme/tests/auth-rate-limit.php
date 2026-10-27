<?php
define( 'ABSPATH', '/' );
$records = array();
function wp_salt( $scheme ) { return 'test-only-salt'; }
function get_transient( $key ) { return $GLOBALS['records'][$key] ?? false; }
function set_transient( $key, $value, $ttl ) { $GLOBALS['records'][$key] = $value; return true; }
function check( $ok, $label ) { if ( ! $ok ) { throw new RuntimeException( $label ); } echo "PASS $label\n"; }
require dirname( __DIR__ ) . '/inc/core/auth-rate-limit.php';
check( az_auth_rate_allowed( 'test', 'one', 2, 600 ), 'first request allowed' );
check( az_auth_rate_allowed( 'test', 'one', 2, 600 ), 'second request allowed' );
check( ! az_auth_rate_allowed( 'test', 'one', 2, 600 ), 'over limit denied' );
check( az_auth_rate_allowed( 'test', 'two', 2, 600 ), 'identities isolated' );
foreach ( $records as &$record ) { $record['until'] = time() - 1; } unset( $record );
check( az_auth_rate_allowed( 'test', 'one', 2, 600 ), 'expired limit resets' );
$_SERVER['REMOTE_ADDR'] = '127.0.0.1'; $_SERVER['HTTP_X_FORWARDED_FOR'] = 'spoof';
check( '127.0.0.1' === az_auth_client_identity(), 'untrusted proxy header ignored' );
