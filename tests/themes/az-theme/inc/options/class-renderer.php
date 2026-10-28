<?php
defined( 'ABSPATH' ) || exit;
class AZ_Options_Renderer {
    public static function field( $section, $field, $values ) {
        $id = sanitize_key( $field['id'] ); $type = $field['type']; $section_values = is_array( $values[ $section ] ?? null ) ? $values[ $section ] : array(); $has_value = array_key_exists( $id, $section_values ); $value = $has_value ? $section_values[ $id ] : ( $field['default'] ?? '' );
        if ( ! $has_value && ! empty( $field['legacy'] ) ) { $value = get_option( $field['legacy'], $value ); }
        if ( ! $has_value && ! empty( $field['legacy_theme_mod'] ) ) { $value = get_theme_mod( $field['legacy_theme_mod'], $value ); }
        if ( ! $has_value && ! empty( $field['legacy_path'] ) ) { $value = az_array_get( $values, $field['legacy_path'], $value ); }
        // A removed selection must show the registered default, not the first option.
        if ( 'select' === $type && ( ! is_scalar( $value ) || ! array_key_exists( (string) $value, $field['options'] ) ) ) { $value = $field['default'] ?? ''; }
        if ( 'code' === $type ) { $value = AZ_Options_Sanitizer::code_source( $value ); }
        $name = 'az_theme_options[' . $section . '][' . $id . ']'; $input_id = 'az-option-' . $section . '-' . $id; $help_id = $input_id . '-help';
        $width = ! empty( $field['width'] ) ? ' az-field--' . sanitize_html_class( $field['width'] ) : '';
        $icon = ! empty( $field['icon'] ) ? '<span class="dashicons dashicons-' . esc_attr( sanitize_html_class( $field['icon'] ) ) . '" aria-hidden="true"></span> ' : '';
        $switch_class = in_array( $type, array( 'checkbox', 'switch' ), true ) ? ' az-field--switch' : '';
        echo '<div class="az-advanced__field az-field' . esc_attr( $width ) . esc_attr( $switch_class ) . ' ' . esc_attr( $field['class'] ?? '' ) . '"><label class="az-advanced__field-label" for="' . esc_attr( $input_id ) . '">' . $icon . esc_html( $field['title'] ) . '</label>';
        if ( ! empty( $field['description'] ) ) { echo '<p id="' . esc_attr( $help_id ) . '" class="az-advanced__help">' . esc_html( $field['description'] ) . '</p>'; }
        if ( 'code' === $type && 'custom_css' !== $id ) {
            echo '<p class="az-advanced__help">' . esc_html__( 'Script chỉ được phép khi người lưu có quyền unfiltered_html. Sau khi chuyển website hoặc thay secret, hãy kiểm tra và lưu lại mã. Không dán mã không tin cậy.', 'az-ai-theme' ) . '</p>';
        }
        $shortcode = $field['shortcode'] ?? ( 'general' === $section ? ( 'phones' === $id ? '[az_contact type="phone"]' : ( 'emails' === $id ? '[az_contact type="email"]' : ( 'addresses' === $id ? '[az_contact type="address"]' : '[az_general field="' . $id . '"]' ) ) ) : '' );
        // Configuration switches are not content shortcodes; avoid crowding their labels.
        if ( $shortcode && ( ! in_array( $type, array( 'checkbox', 'switch' ), true ) || ! empty( $field['shortcode'] ) ) ) { echo '<p class="az-advanced__shortcode"><code>' . esc_html( $shortcode ) . '</code></p>'; }
        $described = ! empty( $field['description'] ) ? ' aria-describedby="' . esc_attr( $help_id ) . '"' : '';
        if ( 'secret' === $type ) {
            echo '<input type="password" id="' . esc_attr( $input_id ) . '" name="' . esc_attr( $name ) . '" value="" autocomplete="new-password" class="az-advanced__control" placeholder="' . esc_attr( $value ? 'Đã lưu — để trống để giữ nguyên' : 'Nhập Secret' ) . '"></div>';
            return;
        }
        if ( 'link' === $type ) { echo '<a class="button button-secondary az-advanced__link" href="' . esc_url( $field['url'] ?? '#' ) . '">' . esc_html( $field['link_label'] ?? 'Mở liên kết' ) . '</a>'; }
        elseif ( in_array( $type, array( 'checkbox', 'switch' ), true ) ) { echo '<div class="az-advanced__switch"><input type="hidden" name="' . esc_attr( $name ) . '" value="0"><input id="' . esc_attr( $input_id ) . '" type="checkbox" name="' . esc_attr( $name ) . '" value="1"' . $described . ' ' . checked( $value, 1, false ) . '><label for="' . esc_attr( $input_id ) . '" aria-label="' . esc_attr( $field['title'] ) . '"><span></span></label></div>'; }
        elseif ( in_array( $type, array( 'textarea', 'code', 'map_embed' ), true ) ) { echo '<textarea id="' . esc_attr( $input_id ) . '" name="' . esc_attr( $name ) . '" rows="' . ( 'code' === $type || 'map_embed' === $type ? '8' : '4' ) . '" class="az-advanced__control ' . ( 'code' === $type ? 'az-advanced__code' : '' ) . '"' . $described . '>' . esc_textarea( $value ) . '</textarea>'; }
        elseif ( 'select' === $type ) { echo '<select id="' . esc_attr( $input_id ) . '" name="' . esc_attr( $name ) . '" class="az-advanced__control"' . $described . '>'; foreach ( $field['options'] as $option_value => $option_label ) { echo '<option value="' . esc_attr( $option_value ) . '" ' . selected( $value, $option_value, false ) . '>' . esc_html( $option_label ) . '</option>'; } echo '</select>'; }
        elseif ( 'radio' === $type ) { echo '<div class="az-advanced__radio">'; foreach ( $field['options'] as $option_value => $option_label ) { echo '<label><input type="radio" name="' . esc_attr( $name ) . '" value="' . esc_attr( $option_value ) . '" ' . checked( $value, $option_value, false ) . '> ' . esc_html( $option_label ) . '</label>'; } echo '</div>'; }
        elseif ( 'attribute_filters' === $type ) { self::attribute_filter_list( $name, $value ); }
        elseif ( 'repeater' === $type ) { $rows = is_array( $value ) && $value ? $value : array( array() ); echo '<div class="az-advanced__repeater" data-repeater>'; foreach ( $rows as $index => $row ) { self::repeater_row( $name, $field, $row, $index ); } echo '<button type="button" class="button az-advanced__repeater-add" data-repeater-add><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>Thêm mục</button></div>'; }
        elseif ( in_array( $type, array( 'image', 'media' ), true ) ) { $url = $value ? wp_get_attachment_image_url( absint( $value ), 'thumbnail' ) : ''; $file = $value ? get_attached_file( absint( $value ) ) : ''; $filename = $file ? wp_basename( $file ) : ''; echo '<div class="az-advanced__media"><input id="' . esc_attr( $input_id ) . '" type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"><div class="az-advanced__media-preview" data-preview="' . esc_attr( $input_id ) . '">' . ( $url ? '<img src="' . esc_url( $url ) . '" alt="">' : '<span class="dashicons dashicons-format-image"></span>' ) . '</div><span class="az-advanced__media-name" data-name="' . esc_attr( $input_id ) . '">' . esc_html( $filename ?: 'No media selected' ) . '</span><button type="button" class="button az-advanced__media-button" data-target="' . esc_attr( $input_id ) . '">Change Image</button><button type="button" class="button-link-delete az-advanced__media-remove" data-target="' . esc_attr( $input_id ) . '" aria-label="Remove media" title="Remove media"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button></div>'; }
        elseif ( 'range' === $type ) { $range_attrs = ''; foreach ( array( 'min', 'max', 'step' ) as $attr ) { if ( isset( $field[ $attr ] ) ) { $range_attrs .= ' ' . $attr . '="' . esc_attr( $field[ $attr ] ) . '"'; } } echo '<div class="az-advanced__range" data-unit="' . esc_attr( isset( $field['unit'] ) ? $field['unit'] : '' ) . '"><input id="' . esc_attr( $input_id ) . '" type="range" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="az-advanced__range-input" data-az-range' . $described . $range_attrs . '><output class="az-advanced__range-value" for="' . esc_attr( $input_id ) . '" data-az-range-value>' . esc_html( $value ) . ( ! empty( $field['unit'] ) ? ' ' . esc_html( $field['unit'] ) : '' ) . '</output></div>'; }
        else { $input_type = in_array( $type, array( 'email', 'url', 'number' ), true ) ? $type : 'text'; $attrs = ''; foreach ( array( 'min', 'max', 'step', 'placeholder' ) as $attr ) { if ( isset( $field[ $attr ] ) ) { $attrs .= ' ' . $attr . '="' . esc_attr( $field[ $attr ] ) . '"'; } } echo '<div class="az-advanced__input-row"><input id="' . esc_attr( $input_id ) . '" type="' . esc_attr( $input_type ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="az-advanced__control ' . ( 'color' === $type ? 'az-advanced__color' : '' ) . '"' . $described . $attrs . '>' . ( ! empty( $field['unit'] ) ? '<span class="az-advanced__unit">' . esc_html( $field['unit'] ) . '</span>' : '' ) . '</div>'; }
        echo '</div>';
    }
    private static function attribute_filter_list( $name, $value ) {
        $available = function_exists( 'az_storefront_filter_definitions' ) ? az_storefront_filter_definitions() : array();

        $rows = array();
        $used = array();
        foreach ( (array) $value as $row ) {
            $key = sanitize_key( $row['taxonomy'] ?? $row['key'] ?? '' );
            if ( ! isset( $available[ $key ] ) || isset( $used[ $key ] ) ) {
                continue;
            }
            $rows[] = array( 'key' => $key, 'enabled' => empty( $row['enabled'] ) ? 0 : 1 );
            $used[ $key ] = true;
        }
        foreach ( $available as $key => $definition ) {
            if ( ! isset( $used[ $key ] ) ) {
                $rows[] = array( 'key' => $key, 'enabled' => 0 );
            }
        }

        echo '<div class="az-advanced__attribute-filters az-advanced__repeater" data-repeater>';
        foreach ( $rows as $index => $row ) {
            $key = $row['key'];
            $definition = $available[ $key ];
            $label = function_exists( 'az_storefront_filter_label' )
                ? az_storefront_filter_label( $definition['label'], $definition['taxonomy'] ?? $key, $definition['kind'] ?? '' )
                : $definition['label'];
            $kind_labels = array( 'price' => 'Lọc theo giá', 'attribute' => 'Thuộc tính sản phẩm', 'taxonomy' => 'Phân loại sản phẩm' );
            $kind_label = $kind_labels[ $definition['kind'] ] ?? 'Bộ lọc sản phẩm';
            $enabled = ! empty( $row['enabled'] );
            $switch_id = 'az-sidebar-filter-' . sanitize_html_class( $key );
            echo '<div class="az-advanced__repeater-row az-advanced__repeater-row--attribute" data-repeater-row>';
            echo '<button type="button" class="button az-advanced__repeater-action az-advanced__repeater-drag" data-repeater-drag draggable="true" aria-label="Giữ và kéo để sắp xếp bộ lọc" title="Giữ và kéo để sắp xếp"><span class="dashicons dashicons-move" aria-hidden="true"></span></button>';
            echo '<input type="hidden" name="' . esc_attr( $name . '[' . $index . '][taxonomy]' ) . '" value="' . esc_attr( $key ) . '">';
            echo '<div class="az-advanced__attribute-filter-info"><div class="az-advanced__attribute-filter-name"><strong>' . esc_html( $label ) . '</strong><span class="az-advanced__attribute-filter-kind">' . esc_html( $kind_label ) . '</span></div><code>' . esc_html( $key ) . '</code></div>';
            echo '<div class="az-advanced__attribute-filter-control"><span class="az-advanced__attribute-filter-status" data-enabled="Hiển thị" data-disabled="Đang tắt">' . esc_html( $enabled ? 'Hiển thị' : 'Đang tắt' ) . '</span><div class="az-advanced__switch"><input type="hidden" name="' . esc_attr( $name . '[' . $index . '][enabled]' ) . '" value="0"><input id="' . esc_attr( $switch_id ) . '" type="checkbox" name="' . esc_attr( $name . '[' . $index . '][enabled]' ) . '" value="1" ' . checked( $enabled, true, false ) . '><label for="' . esc_attr( $switch_id ) . '" aria-label="Bật bộ lọc ' . esc_attr( $label ) . '"><span></span></label></div></div>';
            echo '</div>';
        }
        echo '</div>';
    }

    private static function repeater_row( $name, $field, $row, $index ) { $row_class = 'az-advanced__repeater-row'; if ( false !== strpos( $name, '[addresses]' ) ) { $row_class .= ' az-advanced__repeater-row--addresses'; } echo '<div class="' . esc_attr( $row_class ) . '" data-repeater-row><button type="button" class="button az-advanced__repeater-action az-advanced__repeater-drag" data-repeater-drag draggable="true" aria-label="Giữ và kéo để sắp xếp mục này" title="Giữ và kéo để sắp xếp"><span class="dashicons dashicons-move" aria-hidden="true"></span></button>'; foreach ( $field['fields'] ?? array() as $subfield ) { $id = sanitize_key( $subfield['id'] ); $type = in_array( $subfield['type'], array( 'textarea', 'map_embed' ), true ) ? 'textarea' : ( 'email' === $subfield['type'] ? 'email' : ( 'url' === $subfield['type'] ? 'url' : 'text' ) ); $field_name = $name . '[' . $index . '][' . $id . ']'; echo '<label class="az-advanced__repeater-field"><span>' . esc_html( $subfield['title'] ) . '</span>'; if ( 'textarea' === $type ) { echo '<textarea name="' . esc_attr( $field_name ) . '" rows="3">' . esc_textarea( $row[ $id ] ?? '' ) . '</textarea>'; } else { echo '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( $field_name ) . '" value="' . esc_attr( $row[ $id ] ?? '' ) . '">'; } echo '</label>'; } echo '<div class="az-advanced__repeater-actions"><button type="button" class="button az-advanced__repeater-action az-advanced__repeater-remove" data-repeater-remove aria-label="Xóa mục này" title="Xóa mục này"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button></div></div>'; }
}
