<?php
namespace FLX\Fields;

/** WordPress admin rendering. Names and values are supplied, never fetched from storage. */
final class Renderer {
	private $engine;
	private $errors;
	public function __construct( Engine $engine, array $errors = array() ) { $this->engine = $engine; $this->errors = $errors; }
	public function field( $key, array $field, $value, $name, $path = '', $template = false ) {
		$id = 'flx-field-' . preg_replace( '/[^a-zA-Z0-9_-]/', '-', $name );
		$path = $path ? $path : $key;
		$field['_template'] = $template; $field['_path'] = $path;
		$is_group = in_array( $field['type'], array( 'radio', 'repeater' ), true );
		echo '<div class="flx-field" data-flx-field="' . esc_attr( $key ) . '"><' . ( $is_group ? 'span id="' . esc_attr( $id . '-label' ) . '"' : 'label for="' . esc_attr( $id ) . '"' ) . ' class="flx-field-label">' . esc_html( isset( $field['label'] ) ? $field['label'] : $key ) . ( ! empty( $field['required'] ) ? ' <span aria-hidden="true">*</span>' : '' ) . ( $is_group ? '</span>' : '</label>' );
		$type = $this->engine->types()->get( $field['type'] );
		ob_start(); call_user_func( $type['render'], $this, $field, $value, $name, $id ); $html = ob_get_clean();
		// Connect every control to its help/error text, including rich editor textareas.
		$attributes = ' aria-describedby="' . esc_attr( $id . '-help' ) . '"' . ( isset( $this->errors[ $path ] ) ? ' aria-invalid="true"' : '' );
		echo preg_replace( '/<(input|textarea|select)\b/', '<$1' . $attributes, $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div id="' . esc_attr( $id . '-help' ) . '">';
		if ( isset( $field['description'] ) ) { echo '<p class="flx-field-description">' . esc_html( $field['description'] ) . '</p>'; }
		if ( isset( $this->errors[ $path ] ) ) { echo '<p class="flx-field-error" role="alert">' . esc_html( $this->errors[ $path ] ) . '</p>'; }
		echo '</div></div>';
	}
	private function input( $type, $field, $value, $name, $id ) {
		echo '<input type="' . esc_attr( $type ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( is_scalar( $value ) ? $value : '' ) . '"';
		foreach ( array( 'placeholder', 'maxlength', 'min', 'max', 'step' ) as $attribute ) { if ( isset( $field[ $attribute ] ) ) { echo ' ' . esc_attr( $attribute ) . '="' . esc_attr( $field[ $attribute ] ) . '"'; } }
		if ( ! empty( $field['required'] ) ) { echo ' aria-required="true"'; }
		echo '>';
	}
	public function text( $f, $v, $n, $id ) { $this->input( 'text', $f, $v, $n, $id ); }
	public function number( $f, $v, $n, $id ) { $this->input( 'number', $f, $v, $n, $id ); }
	public function url( $f, $v, $n, $id ) { $this->input( 'url', $f, $v, $n, $id ); }
	public function email( $f, $v, $n, $id ) { $this->input( 'email', $f, $v, $n, $id ); }
	public function textarea( $f, $v, $n, $id ) {
		echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $n ) . '" rows="' . esc_attr( isset( $f['rows'] ) ? $f['rows'] : 4 ) . '"' . ( isset( $f['maxlength'] ) ? ' maxlength="' . esc_attr( $f['maxlength'] ) . '"' : '' ) . ' placeholder="' . esc_attr( isset( $f['placeholder'] ) ? $f['placeholder'] : '' ) . '">' . esc_textarea( is_string( $v ) ? $v : '' ) . '</textarea>';
	}
	public function editor( $f, $v, $n, $id ) {
		echo '<div data-flx-editor>';
		if ( ! empty( $f['_template'] ) ) {
			echo '<textarea class="flx-rich-editor" id="' . esc_attr( $id ) . '" name="' . esc_attr( $n ) . '">' . esc_textarea( is_string( $v ) ? $v : '' ) . '</textarea>';
		} else {
			wp_editor( is_string( $v ) ? $v : '', $id, array( 'textarea_name' => $n, 'textarea_rows' => 7, 'media_buttons' => false, 'editor_class' => 'flx-rich-editor', 'tinymce' => true, 'quicktags' => true ) );
		}
		echo '</div>';
	}
	public function select( $f, $v, $n, $id ) {
		echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $n ) . '">';
		foreach ( $f['options'] as $key => $label ) { echo '<option value="' . esc_attr( $key ) . '" ' . selected( (string) $v, (string) $key, false ) . '>' . esc_html( $label ) . '</option>'; }
		echo '</select>';
	}
	public function radio( $f, $v, $n, $id ) {
		echo '<div role="group" aria-labelledby="' . esc_attr( $id . '-label' ) . '">';
		foreach ( $f['options'] as $key => $label ) { echo '<label class="flx-radio"><input type="radio" id="' . esc_attr( $id . '-' . $key ) . '" name="' . esc_attr( $n ) . '" value="' . esc_attr( $key ) . '" ' . checked( (string) $v, (string) $key, false ) . '>' . esc_html( $label ) . '</label>'; }
		echo '</div>';
	}
	public function toggle( $f, $v, $n, $id ) {
		echo '<input type="hidden" name="' . esc_attr( $n ) . '" value="0"><input class="flx-toggle" role="switch" type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $n ) . '" value="1" ' . checked( true, $v, false ) . '>';
	}
	public function color( $f, $v, $n, $id ) {
		echo '<input class="flx-color" type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $n ) . '" value="' . esc_attr( is_string( $v ) ? $v : '' ) . '" data-default-color="' . esc_attr( isset( $f['default'] ) ? $f['default'] : '' ) . '">';
	}
	public function media( $f, $v, $n, $id ) {
		echo '<div data-flx-media><input type="hidden" id="' . esc_attr( $id . '-value' ) . '" name="' . esc_attr( $n ) . '" value="' . esc_attr( is_int( $v ) ? $v : 0 ) . '"><div class="flx-media-preview">';
		if ( is_int( $v ) && $v > 0 ) { echo wp_get_attachment_image( $v, 'thumbnail' ); }
		echo '</div><button type="button" id="' . esc_attr( $id ) . '" class="button" data-flx-media-select>' . ( $v ? 'Thay ảnh' : 'Chọn ảnh' ) . '</button> <button type="button" class="button-link-delete" data-flx-media-remove>Xóa ảnh</button></div>';
	}
	public function gallery( $f, $v, $n, $id ) {
		echo '<div data-flx-gallery data-flx-name="' . esc_attr( $n ) . '"><input type="hidden" name="' . esc_attr( $n ) . '" value=""><div class="flx-gallery-items">';
		foreach ( is_array( $v ) ? $v : array() as $image ) {
			echo '<div class="flx-gallery-item" draggable="true"><input type="hidden" name="' . esc_attr( $n . '[]' ) . '" value="' . esc_attr( $image ) . '">' . wp_get_attachment_image( $image, 'thumbnail' ) . '<div><button type="button" class="button" data-flx-gallery-up aria-label="Đưa ảnh lên">↑</button><button type="button" class="button" data-flx-gallery-down aria-label="Đưa ảnh xuống">↓</button><button type="button" class="button" data-flx-gallery-remove aria-label="Xóa ảnh">×</button></div></div>';
		}
		echo '</div><button type="button" id="' . esc_attr( $id ) . '" class="button" data-flx-gallery-select>Chọn nhiều ảnh</button></div>';
	}
	public function repeater( $f, $v, $n, $id ) {
		echo '<div data-flx-repeater role="group" aria-labelledby="' . esc_attr( $id . '-label' ) . '" data-flx-name="' . esc_attr( $n ) . '"><input type="hidden" name="' . esc_attr( $n ) . '" value=""><div class="flx-repeater-rows">';
		foreach ( is_array( $v ) ? $v : array() as $row ) { if ( isset( $row['_id'] ) ) { $this->row( $f, $row, $n, $row['_id'], false ); } }
		echo '</div><template data-flx-row-template>';
		$this->row( $f, $this->engine->defaults( $f['fields'] ), $n, '__FLX_ROW__', true );
		echo '</template><button type="button" class="button button-secondary" data-flx-row-add>+ Thêm ' . esc_html( isset( $f['item_label'] ) ? $f['item_label'] : 'mục' ) . '</button><span class="screen-reader-text" role="status" data-flx-row-status></span></div>';
	}
	private function row( $field, $row, $name, $row_id, $template ) {
		$label = isset( $field['item_label'] ) ? $field['item_label'] : 'Mục';
		echo '<div class="flx-repeater-row" data-flx-row="' . esc_attr( $row_id ) . '"><input type="hidden" name="' . esc_attr( $name . '[' . $row_id . '][_id]' ) . '" value="' . esc_attr( $row_id ) . '"><div class="flx-row-toolbar"><span class="flx-drag-handle" draggable="true" title="Kéo để sắp xếp" aria-hidden="true">⠿</span><strong>' . esc_html( $label ) . '</strong><div class="flx-row-actions"><button type="button" class="button" data-flx-row-collapse aria-expanded="true">Thu gọn</button><button type="button" class="button" data-flx-row-up aria-label="Đưa dòng lên">↑</button><button type="button" class="button" data-flx-row-down aria-label="Đưa dòng xuống">↓</button><button type="button" class="button" data-flx-row-duplicate>Nhân bản</button><button type="button" class="button" data-flx-row-remove>Xóa</button></div></div><div class="flx-row-fields">';
		$values = array_replace( $this->engine->defaults( $field['fields'] ), $row );
		foreach ( $field['fields'] as $key => $subfield ) { $this->field( $key, $subfield, $values[ $key ], $name . '[' . $row_id . '][' . $key . ']', $field['_path'] . '.' . $row_id . '.' . $key, $template ); }
		echo '</div></div>';
	}
}
