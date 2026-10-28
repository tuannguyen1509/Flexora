<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ── Cho phép HTML đầy đủ trong mô tả danh mục ── */
remove_filter( 'pre_term_description', 'wp_filter_kses' );
remove_filter( 'term_description',     'wp_kses_data'   );
add_filter( 'pre_term_description', 'wp_kses_post' );
add_filter( 'term_description',     'wp_kses_post' );

if ( ! function_exists( 'az_wc_attribute_type_selector' ) ) {
    function az_wc_attribute_type_selector( $types ) {
        $types['checkbox'] = 'Checkbox';
        return $types;
    }
}
add_filter( 'product_attributes_type_selector', 'az_wc_attribute_type_selector' );

if ( ! function_exists( 'az_wc_checkbox_attribute_taxonomies' ) ) {
    function az_wc_checkbox_attribute_taxonomies() {
        if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
            return array();
        }

        $checkbox_taxonomies = array();
        foreach ( wc_get_attribute_taxonomies() as $attribute ) {
            if ( isset( $attribute->attribute_type ) && 'checkbox' === $attribute->attribute_type ) {
                $checkbox_taxonomies[] = wc_attribute_taxonomy_name( $attribute->attribute_name );
            }
        }

        return $checkbox_taxonomies;
    }
}

add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
        return;
    }

    $screen = get_current_screen();
    if ( ! $screen || 'product' !== $screen->post_type ) {
        return;
    }

    // Keep WooCommerce's native Select2 value picker on product edit screens.
    // The checkbox replacement can hide Value(s) before Woo finishes loading terms.
    return;

    $checkbox_taxonomies = az_wc_checkbox_attribute_taxonomies();
    if ( empty( $checkbox_taxonomies ) ) {
        return;
    }

    wp_add_inline_script(
        'jquery',
        'window.azCheckboxProductAttributes = ' . wp_json_encode( array_values( $checkbox_taxonomies ) ) . ';',
        'before'
    );
    add_action( 'admin_footer', 'az_wc_checkbox_attribute_admin_js' );
    add_action( 'admin_head', 'az_wc_checkbox_attribute_admin_css' );
} );

if ( ! function_exists( 'az_wc_checkbox_attribute_admin_js' ) ) {
    function az_wc_checkbox_attribute_admin_js() {
        ?>
        <script>
        (function($){
            function getAttributeTaxonomy($attribute){
                var taxonomy = $attribute.find('select.attribute_taxonomy').val();
                if (!taxonomy) {
                    taxonomy = $attribute.find('input[name^="attribute_names["]').val();
                }
                return taxonomy || '';
            }

            function syncCheckboxes($box, $select){
                var selected = [];
                $box.find('input[type="checkbox"]:checked').each(function(){
                    selected.push($(this).val());
                });
                $select.val(selected).trigger('change');
            }

            function buildCheckboxList($attribute){
                var taxonomy = getAttributeTaxonomy($attribute);
                var $select = $attribute.find('select[name^="attribute_values["][multiple]');

                $attribute.find('.az-attribute-checkboxes').remove();

                if (!taxonomy || !window.azCheckboxProductAttributes || window.azCheckboxProductAttributes.indexOf(taxonomy) === -1) {
                    $select.show().siblings('.select2').show();
                    return;
                }

                if (!$select.length) {
                    return;
                }

                var $box = $('<div class="az-attribute-checkboxes"></div>');
                $select.find('option').each(function(){
                    var value = $(this).attr('value');
                    var text = $(this).text();
                    if (!value) return;
                    var id = 'az-attr-' + taxonomy + '-' + value;
                    var $label = $('<label class="az-attribute-checkbox"></label>');
                    var $input = $('<input type="checkbox">').attr({ value:value, id:id });
                    if ($(this).is(':selected')) {
                        $input.prop('checked', true);
                    }
                    $label.append($input).append($('<span></span>').text(text));
                    $box.append($label);
                });

                if (!$box.children().length) {
                    $select.show().siblings('.select2').show();
                    return;
                }

                $box.on('change', 'input[type="checkbox"]', function(){
                    syncCheckboxes($box, $select);
                });

                $select.after($box).hide();
                $select.siblings('.select2').hide();
            }

            function refreshCheckboxAttributes(){
                $('#product_attributes .woocommerce_attribute').each(function(){
                    buildCheckboxList($(this));
                });
            }

            $(document).ready(function(){
                refreshCheckboxAttributes();
                setTimeout(refreshCheckboxAttributes, 500);
                setTimeout(refreshCheckboxAttributes, 1200);
                $(document.body).on('woocommerce_added_attribute woocommerce_attributes_saved wc-enhanced-select-init', refreshCheckboxAttributes);
                $(document).on('change', '#product_attributes select.attribute_taxonomy', function(){
                    setTimeout(refreshCheckboxAttributes, 200);
                });
            });
        })(jQuery);
        </script>
        <?php
    }
}

if ( ! function_exists( 'az_wc_checkbox_attribute_admin_css' ) ) {
    function az_wc_checkbox_attribute_admin_css() {
        ?>
        <style>
        .az-attribute-checkboxes{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px;margin:8px 0 4px;max-height:220px;overflow:auto;padding:10px;border:1px solid #dcdcde;border-radius:6px;background:#fff}
        .az-attribute-checkbox{display:flex;align-items:center;gap:7px;margin:0;padding:7px 9px;border:1px solid #e6e8ec;border-radius:5px;background:#f8fafc;color:#1d2327;font-size:13px;line-height:1.35}
        .az-attribute-checkbox:hover{border-color:#2271b1;background:#f0f6fc}
        .az-attribute-checkbox input{margin:0}
        </style>
        <?php
    }
}

if ( ! function_exists( 'az_product_cat_rate_field_add' ) ) {
    function az_product_cat_rate_field_add() {
        ?>
        <div class="form-field term-az-price-rate-wrap">
            <label for="az_price_rate">Tỉ giá tăng giá (%)</label>
            <input type="number" name="az_price_rate" id="az_price_rate" value="" min="0" step="0.01" placeholder="VD: 10">
            <p>Nhập phần trăm tăng giá cho tất cả sản phẩm thuộc danh mục này. Ví dụ giá 1.000.000đ, nhập 10 sẽ hiển thị 1.100.000đ.</p>
        </div>
        <?php
    }
}
add_action( 'product_cat_add_form_fields', 'az_product_cat_rate_field_add' );

if ( ! function_exists( 'az_product_cat_rate_field_edit' ) ) {
    function az_product_cat_rate_field_edit( $term ) {
        $rate = get_term_meta( $term->term_id, 'az_price_rate', true );
        ?>
        <tr class="form-field term-az-price-rate-wrap">
            <th scope="row"><label for="az_price_rate">Tỉ giá tăng giá (%)</label></th>
            <td>
                <input type="number" name="az_price_rate" id="az_price_rate" value="<?php echo esc_attr( $rate ); ?>" min="0" step="0.01" placeholder="VD: 10">
                <p class="description">Nhập phần trăm tăng giá cho tất cả sản phẩm thuộc danh mục này. Ví dụ giá 1.000.000đ, nhập 10 sẽ hiển thị 1.100.000đ.</p>
            </td>
        </tr>
        <?php
    }
}
add_action( 'product_cat_edit_form_fields', 'az_product_cat_rate_field_edit' );

if ( ! function_exists( 'az_save_product_cat_price_rate' ) ) {
    function az_save_product_cat_price_rate( $term_id ) {
        if ( ! current_user_can( 'manage_product_terms' ) ) {
            return;
        }

        $rate = isset( $_POST['az_price_rate'] ) ? wc_format_decimal( wp_unslash( $_POST['az_price_rate'] ) ) : '';
        if ( '' === $rate || (float) $rate <= 0 ) {
            delete_term_meta( $term_id, 'az_price_rate' );
            az_clear_product_cat_rate_price_cache( $term_id );
            return;
        }

        update_term_meta( $term_id, 'az_price_rate', max( 0, (float) $rate ) );
        az_clear_product_cat_rate_price_cache( $term_id );
    }
}
add_action( 'created_product_cat', 'az_save_product_cat_price_rate' );
add_action( 'edited_product_cat', 'az_save_product_cat_price_rate' );
if ( ! function_exists( 'az_clear_product_cat_rate_price_cache' ) ) {
    function az_clear_product_cat_rate_price_cache( $term_id ) {
        if ( ! function_exists( 'wc_delete_product_transients' ) || ! taxonomy_exists( 'product_cat' ) ) {
            return;
        }

        $product_ids = get_posts(
            array(
                'post_type'      => 'product',
                'post_status'    => 'any',
                'fields'         => 'ids',
                'posts_per_page' => -1,
                'tax_query'      => array(
                    array(
                        'taxonomy'         => 'product_cat',
                        'field'            => 'term_id',
                        'terms'            => array( absint( $term_id ) ),
                        'include_children' => true,
                    ),
                ),
            )
        );

        foreach ( $product_ids as $product_id ) {
            wc_delete_product_transients( $product_id );
        }
    }
}

if ( ! function_exists( 'az_get_product_category_price_rate' ) ) {
    function az_get_product_category_price_rate( $product_id ) {
        $product_id = absint( $product_id );
        if ( ! $product_id || ! taxonomy_exists( 'product_cat' ) ) {
            return 0;
        }

        $term_ids = wc_get_product_term_ids( $product_id, 'product_cat' );
        if ( empty( $term_ids ) ) {
            return 0;
        }

        $rates = array();
        foreach ( $term_ids as $term_id ) {
            $ancestors = get_ancestors( $term_id, 'product_cat' );
            foreach ( array_merge( array( $term_id ), $ancestors ) as $related_term_id ) {
                $rate = (float) get_term_meta( $related_term_id, 'az_price_rate', true );
                if ( $rate > 0 ) {
                    $rates[] = $rate;
                }
            }
        }

        return empty( $rates ) ? 0 : max( $rates );
    }
}

if ( ! function_exists( 'az_apply_product_category_price_rate' ) ) {
    function az_apply_product_category_price_rate( $price, $product ) {
        if ( is_admin() && ! wp_doing_ajax() ) {
            return $price;
        }

        if ( '' === $price || ! is_numeric( $price ) || ! $product instanceof WC_Product ) {
            return $price;
        }

        $parent_id  = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
        $rate       = az_get_product_category_price_rate( $parent_id );
        $base_price = (float) $price;

        if ( $rate <= 0 || $base_price <= 0 ) {
            return $price;
        }

        return (string) wc_format_decimal( $base_price * ( 1 + ( $rate / 100 ) ), wc_get_price_decimals() );
    }
}
add_filter( 'woocommerce_product_get_price', 'az_apply_product_category_price_rate', 20, 2 );
add_filter( 'woocommerce_product_get_regular_price', 'az_apply_product_category_price_rate', 20, 2 );
add_filter( 'woocommerce_product_get_sale_price', 'az_apply_product_category_price_rate', 20, 2 );
add_filter( 'woocommerce_product_variation_get_price', 'az_apply_product_category_price_rate', 20, 2 );
add_filter( 'woocommerce_product_variation_get_regular_price', 'az_apply_product_category_price_rate', 20, 2 );
add_filter( 'woocommerce_product_variation_get_sale_price', 'az_apply_product_category_price_rate', 20, 2 );

if ( ! function_exists( 'az_product_cat_rate_variation_prices_hash' ) ) {
    function az_product_cat_rate_variation_prices_hash( $hash, $product, $for_display ) {
        if ( $product instanceof WC_Product ) {
            $hash['az_product_cat_price_rate'] = az_get_product_category_price_rate( $product->get_id() );
        }

        return $hash;
    }
}
add_filter( 'woocommerce_get_variation_prices_hash', 'az_product_cat_rate_variation_prices_hash', 20, 3 );
if ( ! function_exists( 'az_product_cat_rate_admin_column' ) ) {
    function az_product_cat_rate_admin_column( $columns ) {
        $columns['az_price_rate'] = 'Tỉ giá';
        return $columns;
    }
}
add_filter( 'manage_edit-product_cat_columns', 'az_product_cat_rate_admin_column' );

if ( ! function_exists( 'az_product_cat_rate_admin_column_content' ) ) {
    function az_product_cat_rate_admin_column_content( $content, $column_name, $term_id ) {
        if ( 'az_price_rate' !== $column_name ) {
            return $content;
        }

        $rate = (float) get_term_meta( $term_id, 'az_price_rate', true );
        return $rate > 0 ? esc_html( wc_format_decimal( $rate, 2 ) . '%' ) : '—';
    }
}
add_filter( 'manage_product_cat_custom_column', 'az_product_cat_rate_admin_column_content', 10, 3 );
