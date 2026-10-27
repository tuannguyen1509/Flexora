<?php
namespace FLX\Contracts\V1;

final class LookupV1 extends ValueV1 {
	public static function missing( $source = null ) {
		return new self( array( 'present' => false, 'value' => null, 'source' => $source, 'schema_version' => null, 'revision' => null ) );
	}
	public static function found( $value, $source = null, $schema_version = null, $revision = null ) {
		return new self( array( 'present' => true, 'value' => $value, 'source' => $source, 'schema_version' => $schema_version, 'revision' => $revision ) );
	}
}
