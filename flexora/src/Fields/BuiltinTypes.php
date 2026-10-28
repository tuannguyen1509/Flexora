<?php
namespace FLX\Fields;

/** Native WordPress primitives, registered independently rather than a renderer switch. */
final class BuiltinTypes {
	public static function register( TypeRegistry $registry ) {
		$strings = array(
			'text' => 'sanitize_text_field', 'textarea' => 'sanitize_textarea_field',
			'editor' => 'wp_kses_post', 'url' => 'esc_url_raw', 'email' => 'sanitize_email', 'color' => 'sanitize_hex_color',
		);
		foreach ( $strings as $type => $sanitizer ) {
			self::add( $registry, $type, '', function ( $value ) { return is_scalar( $value ) && ! is_bool( $value ) ? (string) $value : $value; }, $sanitizer,
				function ( $value, $field ) use ( $type ) {
					if ( ! is_string( $value ) ) { return 'Cần nhập nội dung dạng văn bản.'; }
					$length = function_exists( 'mb_strlen' ) ? mb_strlen( $value ) : strlen( $value );
					if ( isset( $field['maxlength'] ) && $length > $field['maxlength'] ) { return 'Nội dung vượt quá độ dài cho phép.'; }
					if ( '' === $value ) { return null; }
					if ( 'url' === $type && ( ! filter_var( $value, FILTER_VALIDATE_URL ) || ! in_array( strtolower( (string) parse_url( $value, PHP_URL_SCHEME ) ), array( 'http', 'https' ), true ) ) ) { return 'Nhập URL hợp lệ bắt đầu bằng http hoặc https.'; }
					if ( 'email' === $type && ! is_email( $value ) ) { return 'Địa chỉ email chưa hợp lệ.'; }
					if ( 'color' === $type && ! sanitize_hex_color( $value ) ) { return 'Màu cần có định dạng hex hợp lệ.'; }
					return null;
				} );
		}
		self::add( $registry, 'number', '', function ( $v ) { return is_numeric( $v ) && ! is_bool( $v ) ? 0 + $v : $v; }, function ( $v ) { return $v; }, function ( $v, $f ) {
			if ( '' === $v ) { return null; }
			if ( ! is_int( $v ) && ! is_float( $v ) || ! is_finite( (float) $v ) ) { return 'Nhập một số hợp lệ.'; }
			if ( isset( $f['min'] ) && $v < $f['min'] || isset( $f['max'] ) && $v > $f['max'] ) { return 'Giá trị nằm ngoài khoảng cho phép.'; }
			if ( isset( $f['step'] ) && is_numeric( $f['step'] ) && $f['step'] > 0 ) {
				$steps = ( $v - ( isset( $f['min'] ) ? $f['min'] : 0 ) ) / $f['step'];
				if ( abs( $steps - round( $steps ) ) > 0.000001 ) { return 'Giá trị không khớp bước tăng.'; }
			}
			return null;
		} );
		foreach ( array( 'select', 'radio' ) as $type ) {
			self::add( $registry, $type, '', function ( $v ) { return is_scalar( $v ) && ! is_bool( $v ) ? (string) $v : $v; }, 'sanitize_text_field', function ( $v, $f ) {
				return is_string( $v ) && array_key_exists( $v, $f['options'] ) ? null : 'Chọn một giá trị trong danh sách.';
			} );
		}
		self::add( $registry, 'toggle', false, function ( $v ) {
			if ( in_array( $v, array( true, 1, '1' ), true ) ) { return true; }
			if ( in_array( $v, array( false, 0, '0' ), true ) ) { return false; }
			return $v;
		}, function ( $v ) { return $v; }, function ( $v ) { return is_bool( $v ) ? null : 'Giá trị bật/tắt chưa hợp lệ.'; } );
		self::add( $registry, 'media', 0, function ( $v ) { return is_int( $v ) || is_string( $v ) && ctype_digit( $v ) ? (int) $v : $v; }, function ( $v ) { return $v; }, array( self::class, 'validate_media' ) );
		self::add( $registry, 'gallery', array(), function ( $v ) {
			if ( '' === $v ) { return array(); }
			return is_array( $v ) ? array_map( function ( $id ) { return is_int( $id ) || is_string( $id ) && ctype_digit( $id ) ? (int) $id : $id; }, $v ) : $v;
		}, function ( $v ) { return array_values( $v ); }, function ( $v ) {
			if ( ! is_array( $v ) || count( $v ) > 100 ) { return 'Danh sách ảnh chưa hợp lệ (tối đa 100).'; }
			foreach ( $v as $id ) { if ( 0 === $id || self::validate_media( $id ) ) { return 'Danh sách có ảnh không hợp lệ.'; } }
			return count( array_unique( $v ) ) === count( $v ) ? null : 'Ảnh trong bộ sưu tập bị trùng.';
		} );
		self::add( $registry, 'repeater', array(), function ( $v ) { return '' === $v ? array() : $v; }, function ( $v ) { return $v; }, function ( $v ) { return is_array( $v ) && count( $v ) <= 100 ? null : 'Danh sách lặp chưa hợp lệ (tối đa 100).'; } );
	}
	private static function add( $registry, $type, $default, $normalize, $sanitize, $validate ) {
		$registry->register( $type, array(
			'default' => function () use ( $default ) { return $default; }, 'normalize' => $normalize,
			'sanitize' => $sanitize, 'validate' => $validate,
			'render' => function ( $renderer, $field, $value, $name, $id ) use ( $type ) { $renderer->$type( $field, $value, $name, $id ); },
		) );
	}
	public static function validate_media( $id ) {
		return is_int( $id ) && $id >= 0 && ( 0 === $id || 'attachment' === get_post_type( $id ) && wp_attachment_is_image( $id ) && current_user_can( 'read_post', $id ) ) ? null : 'Chọn một ảnh hợp lệ trong thư viện.';
	}
}
