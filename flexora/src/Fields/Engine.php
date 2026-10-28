<?php
namespace FLX\Fields;

/** Typed partial patches. Missing is represented by absence, never by a falsey value. */
final class Engine {
	private $types;
	public function __construct( TypeRegistry $types ) { $this->types = $types; }
	public function types() { return $this->types; }
	public function check_schema( array $fields, $nested = false ) {
		foreach ( $fields as $id => $field ) {
			if ( ! preg_match( '/^[a-z][a-z0-9_.-]*$/D', $id ) || ! is_array( $field ) || ! isset( $field['type'] ) ) { throw new \InvalidArgumentException( 'Invalid field schema.' ); }
			$this->types->get( $field['type'] );
			if ( in_array( $field['type'], array( 'select', 'radio' ), true ) && ( ! isset( $field['options'] ) || ! is_array( $field['options'] ) ) ) { throw new \InvalidArgumentException( 'Choice fields need options.' ); }
			if ( 'repeater' === $field['type'] ) {
				if ( $nested || ! isset( $field['fields'] ) || ! is_array( $field['fields'] ) ) { throw new \InvalidArgumentException( 'Nested repeater is not supported.' ); }
				$this->check_schema( $field['fields'], true );
			}
		}
	}
	public function defaults( array $fields ) {
		$out = array();
		foreach ( $fields as $id => $field ) {
			$type = $this->types->get( $field['type'] );
			$out[ $id ] = array_key_exists( 'default', $field ) ? $field['default'] : call_user_func( $type['default'], $field );
		}
		return $out;
	}
	public function patch( array $fields, array $old, array $input, $path = '' ) {
		$out = $old; $errors = array(); $display = array();
		foreach ( $input as $id => $raw ) {
			$key = '' === $path ? $id : $path . '.' . $id;
			if ( ! array_key_exists( $id, $fields ) ) { $errors[ $key ] = 'Trường không có trong schema hiện tại.'; continue; }
			$field = $fields[ $id ]; $type = $this->types->get( $field['type'] );
			if ( null === $raw && ! empty( $field['nullable'] ) ) {
				if ( ! empty( $field['required'] ) ) { $errors[ $key ] = 'Vui lòng điền trường này.'; } else { $out[ $id ] = null; }
				continue;
			}
			$value = call_user_func( $type['normalize'], $raw, $field );
			$error = call_user_func( $type['validate'], $value, $field );
			if ( ! $error && ! empty( $field['required'] ) && ( null === $value || '' === $value || array() === $value || 'media' === $field['type'] && 0 === $value ) ) { $error = 'Vui lòng điền trường này.'; }
			if ( $error ) {
				$errors[ $key ] = $error;
				if ( is_string( $raw ) && ! in_array( $field['type'], array( 'repeater', 'gallery', 'media', 'toggle' ), true ) ) { $display[ $id ] = $raw; }
				continue;
			}
			if ( 'repeater' === $field['type'] ) {
				$rows = array(); $display_rows = array(); $ids = array(); $previous = array();
				foreach ( isset( $old[ $id ] ) && is_array( $old[ $id ] ) ? $old[ $id ] : array() as $row ) { if ( isset( $row['_id'] ) ) { $previous[ $row['_id'] ] = $row; } }
				foreach ( $value as $index => $row ) {
					if ( ! is_array( $row ) || ! isset( $row['_id'] ) || ! is_string( $row['_id'] ) || ! preg_match( '/^[a-zA-Z0-9_-]{8,64}$/D', $row['_id'] ) || isset( $ids[ $row['_id'] ] ) ) { $errors[ $key ] = 'ID của dòng lặp không hợp lệ hoặc bị trùng.'; break; }
					$row_id = $row['_id']; $ids[ $row_id ] = true; unset( $row['_id'] );
					$base = isset( $previous[ $row_id ] ) ? $previous[ $row_id ] : $this->defaults( $field['fields'] );
					$base['_id'] = $row_id;
					$child = $this->patch( $field['fields'], $base, $row, $key . '.' . $row_id );
					$errors = array_merge( $errors, $child['errors'] ); $rows[] = $child['values']; $display_rows[] = $child['display'];
				}
				$out[ $id ] = $rows;
				$display[ $id ] = $display_rows;
			} else { $out[ $id ] = call_user_func( $type['sanitize'], $value, $field ); }
		}
		return array( 'values' => $out, 'errors' => $errors, 'display' => array_replace( $out, $display ) );
	}
}
