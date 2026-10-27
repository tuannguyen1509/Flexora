<?php
namespace FLX\Support;

/** Small explicit range grammar for the foundation; unsupported grammar fails closed. */
final class VersionRange {
	public static function matches( $version, $range ) {
		if ( '*' === $range ) {
			return true;
		}
		if ( ! is_string( $range ) || ! preg_match( '/^(\^|>=|=)?(\d+)\.(\d+)(?:\.(\d+))?$/D', $range, $parts ) ) {
			return false;
		}
		$base = $parts[2] . '.' . $parts[3] . '.' . ( isset( $parts[4] ) ? $parts[4] : '0' );
		$operator = $parts[1];
		if ( '>=' === $operator ) {
			return version_compare( $version, $base, '>=' );
		}
		if ( '^' === $operator ) {
			$major = (int) $parts[2];
			$minor = (int) $parts[3];
			$patch = isset( $parts[4] ) ? (int) $parts[4] : 0;
			$upper = $major > 0 ? ( $major + 1 ) . '.0.0' : ( $minor > 0 || ! isset( $parts[4] ) ? '0.' . ( $minor + 1 ) . '.0' : '0.0.' . ( $patch + 1 ) );
			return version_compare( $version, $base, '>=' ) && version_compare( $version, $upper, '<' );
		}
		return version_compare( $version, $base, '=' );
	}
}
