<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'az_storefront_filter_label' ) ) {
    /** Return a consistent Vietnamese label for automatically discovered filters. */
    function az_storefront_filter_label( $label, $taxonomy = '', $kind = '' ) {
        $taxonomy = sanitize_key( $taxonomy );
        $lookup = sanitize_title( remove_accents( (string) $label ) );

        $taxonomy_labels = array(
            'pa_color'         => 'Màu sắc',
            'pa_colour'        => 'Màu sắc',
            'pa_brand'         => 'Thương hiệu',
            'pa_thuong-hieu'   => 'Thương hiệu',
            'pwb-brand'        => 'Thương hiệu',
            'product_brand'    => 'Thương hiệu',
            'product_tag'      => 'Nhãn sản phẩm',
        );
        $name_labels = array(
            'color'       => 'Màu sắc',
            'colour'      => 'Màu sắc',
            'brand'       => 'Thương hiệu',
            'brands'      => 'Thương hiệu',
            'tag'         => 'Nhãn sản phẩm',
            'product-tag' => 'Nhãn sản phẩm',
        );

        if ( isset( $taxonomy_labels[ $taxonomy ] ) ) {
            $label = $taxonomy_labels[ $taxonomy ];
        } elseif ( isset( $name_labels[ $lookup ] ) ) {
            $label = $name_labels[ $lookup ];
        } elseif ( 'price' === $kind ) {
            $label = 'Khoảng giá';
        }

        return apply_filters( 'az_storefront_filter_label', sanitize_text_field( (string) $label ), $taxonomy, $kind );
    }
}

if ( ! function_exists( 'az_storefront_filter_definitions' ) ) {
    /** Discover filters available for the product archive sidebar. */
    function az_storefront_filter_definitions() {
        $definitions = array(
            'price' => array(
                'key'      => 'price',
                'kind'     => 'price',
                'taxonomy' => '',
                'label'    => 'Khoảng giá',
            ),
        );

        if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
            foreach ( wc_get_attribute_taxonomies() as $attribute ) {
                $taxonomy = function_exists( 'wc_attribute_taxonomy_name' ) ? wc_attribute_taxonomy_name( $attribute->attribute_name ) : 'pa_' . sanitize_title( $attribute->attribute_name );
                $definitions[ $taxonomy ] = array(
                    'key'      => $taxonomy,
                    'kind'     => 'attribute',
                    'taxonomy' => $taxonomy,
                    'label'    => az_storefront_filter_label( $attribute->attribute_label ?: $attribute->attribute_name, $taxonomy, 'attribute' ),
                );
            }
        }

        $excluded_taxonomies = array( 'product_cat', 'product_type', 'product_visibility', 'product_shipping_class' );
        foreach ( get_object_taxonomies( 'product', 'objects' ) as $taxonomy => $object ) {
            if ( in_array( $taxonomy, $excluded_taxonomies, true ) || 0 === strpos( $taxonomy, 'pa_' ) || empty( $object->public ) ) {
                continue;
            }

            $term_ids = get_terms(
                array(
                    'taxonomy'   => $taxonomy,
                    'hide_empty' => false,
                    'fields'     => 'ids',
                    'number'     => 1,
                )
            );
            if ( is_wp_error( $term_ids ) || empty( $term_ids ) ) {
                continue;
            }

            $definitions[ $taxonomy ] = array(
                'key'      => $taxonomy,
                'kind'     => 'taxonomy',
                'taxonomy' => $taxonomy,
                'label'    => az_storefront_filter_label( $object->labels->singular_name ?: $object->label, $taxonomy, 'taxonomy' ),
            );
        }

        return $definitions;
    }
}

if ( ! function_exists( 'az_storefront_apply_taxonomy_filters' ) ) {
    /** Apply configured custom-taxonomy filters to the main product query. */
    function az_storefront_apply_taxonomy_filters( $query ) {
        if ( is_admin() || ! $query->is_main_query() ) {
            return;
        }

        $is_shop_archive = function_exists( 'is_shop' ) && is_shop();
        $is_product_taxonomy = function_exists( 'is_product_taxonomy' ) && is_product_taxonomy();
        if ( ! $is_shop_archive && ! $is_product_taxonomy ) {
            return;
        }

        $configured = function_exists( 'az_get_option' ) ? (array) az_get_option( 'woocommerce.sidebar_attribute_filters', array() ) : array();
        $definitions = az_storefront_filter_definitions();
        $tax_query = $query->get( 'tax_query' );
        $tax_query = is_array( $tax_query ) ? $tax_query : array();

        foreach ( $configured as $filter ) {
            $key = sanitize_key( $filter['taxonomy'] ?? $filter['key'] ?? '' );
            if ( empty( $filter['enabled'] ) || empty( $definitions[ $key ] ) || 'taxonomy' !== $definitions[ $key ]['kind'] ) {
                continue;
            }

            $query_key = 'az_filter_' . $key;
            if ( ! isset( $_GET[ $query_key ] ) || ! is_string( $_GET[ $query_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                continue;
            }

            $terms = array_values( array_filter( array_map( 'sanitize_title', explode( ',', sanitize_text_field( wp_unslash( $_GET[ $query_key ] ) ) ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ( $terms ) {
                $tax_query[] = array(
                    'taxonomy' => $key,
                    'field'    => 'slug',
                    'terms'    => $terms,
                    'operator' => 'IN',
                );
            }
        }

        if ( $tax_query ) {
            if ( count( $tax_query ) > 1 ) {
                $tax_query['relation'] = 'AND';
            }
            $query->set( 'tax_query', $tax_query );
        }
    }
}
add_action( 'pre_get_posts', 'az_storefront_apply_taxonomy_filters', 24 );

if ( ! function_exists( 'az_get_header_cart_html' ) ) {
    function az_get_header_cart_html() {
        if ( function_exists( 'az_storefront_show_purchase_actions' ) && ! az_storefront_show_purchase_actions() ) {
            return '';
        }

        $cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '#';
        $cart_count = 0;
        $cart_total = '0đ';

        if ( function_exists( 'WC' ) && WC()->cart ) {
            $cart_count = (int) WC()->cart->get_cart_contents_count();
            $cart_total = wp_strip_all_tags( WC()->cart->get_cart_subtotal() );
        }

        ob_start();
        ?>
        <a class="az-cart" href="<?php echo esc_url( $cart_url ); ?>" aria-label="<?php echo esc_attr( sprintf( 'Giỏ hàng có %d sản phẩm', $cart_count ) ); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M6 6h15l-2 9H8L6 3H3"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/></svg>
            <span>Giỏ hàng <b><?php echo esc_html( $cart_total ); ?></b></span>
            <em><?php echo esc_html( $cart_count ); ?></em>
        </a>
        <?php
        return trim( ob_get_clean() );
    }
}

if ( ! function_exists( 'az_woocommerce_purchase_actions_enabled' ) ) {
    function az_woocommerce_purchase_actions_enabled() {
        return ! function_exists( 'az_storefront_show_purchase_actions' ) || az_storefront_show_purchase_actions();
    }
}

if ( ! function_exists( 'az_woocommerce_loop_add_to_cart_link' ) ) {
    function az_woocommerce_loop_add_to_cart_link( $html ) {
        return az_woocommerce_purchase_actions_enabled() ? $html : '';
    }
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'az_woocommerce_loop_add_to_cart_link', 100 );

if ( ! function_exists( 'az_woocommerce_loop_button_args' ) ) {
    function az_woocommerce_loop_button_args( $args ) {
        $classes = preg_split( '/\s+/', trim( (string) ( $args['class'] ?? 'button' ) ) );
        $classes = array_filter( array_merge( $classes, array( 'az-button', 'az-button--small', 'az-button--accent' ) ) );
        $args['class'] = implode( ' ', array_unique( $classes ) );
        return $args;
    }
}
add_filter( 'woocommerce_loop_add_to_cart_args', 'az_woocommerce_loop_button_args' );

add_action( 'wp', function() {
    if ( ! az_woocommerce_purchase_actions_enabled() ) {
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
    }
}, 20 );

if ( ! function_exists( 'az_header_cart_fragments' ) ) {
    function az_header_cart_fragments( $fragments ) {
        $fragments['a.az-cart'] = az_get_header_cart_html();
        return $fragments;
    }
}
add_filter( 'woocommerce_add_to_cart_fragments', 'az_header_cart_fragments' );

if ( ! function_exists( 'az_woocommerce_vietnamese_text' ) ) {
    function az_woocommerce_vietnamese_text( $translated, $text, $domain ) {
        if ( 'woocommerce' !== $domain || ! az_woocommerce_is_vietnamese() ) {
            return $translated;
        }

        $translations = array(
            'Add to cart' => 'Thêm vào giỏ hàng',
            'Buy product' => 'Mua sản phẩm',
            'Select options' => 'Chọn tùy chọn',
            'Choose an option' => 'Chọn một tùy chọn',
            'Clear' => 'Xóa lựa chọn',
            'Clear selection' => 'Xóa lựa chọn',
            'In stock' => 'Còn hàng',
            'Out of stock' => 'Hết hàng',
            'Available on backorder' => 'Cho phép đặt trước',
            'This product is currently out of stock and unavailable.' => 'Sản phẩm này hiện đã hết hàng và chưa thể đặt mua.',
            'Please select some product options before adding this product to your cart.' => 'Vui lòng chọn đầy đủ tùy chọn sản phẩm trước khi thêm vào giỏ hàng.',
            'Please choose product options by visiting %s.' => 'Vui lòng chọn tùy chọn sản phẩm tại %s.',
            'Sorry, this product is unavailable. Please choose a different combination.' => 'Rất tiếc, tổ hợp sản phẩm này không khả dụng. Vui lòng chọn tùy chọn khác.',
            'Cart totals' => 'Tổng giỏ hàng',
            'Subtotal' => 'Tạm tính',
            'Total' => 'Tổng cộng',
            'Proceed to checkout' => 'Tiến hành thanh toán',
            'Checkout' => 'Thanh toán',
            'Update cart' => 'Cập nhật giỏ hàng',
            'Apply coupon' => 'Áp dụng',
            'Coupon code' => 'Mã giảm giá',
            'Coupon:' => 'Mã ưu đãi:',
            'Remove this item' => 'Xóa sản phẩm này',
            'Product' => 'Sản phẩm',
            'Price' => 'Đơn giá',
            'Quantity' => 'Số lượng',
            'Confirm your email address to check for past orders and link them to your account.' => 'Xác nhận địa chỉ email để kiểm tra các đơn hàng trước đây và liên kết chúng với tài khoản của bạn.',
            'Confirm email address' => 'Xác nhận địa chỉ email',
        );

        $translations = array_merge(
            $translations,
            array(
                'View cart' => 'Xem giỏ hàng',
                'Cart' => 'Giỏ hàng',
                'Your cart' => 'Giỏ hàng của bạn',
                'Cart updated.' => 'Giỏ hàng đã được cập nhật.',
                '&ldquo;%s&rdquo; removed. %sUndo?%s' => '&ldquo;%s&rdquo; đã được xóa khỏi giỏ hàng. %sHoàn tác?%s',
                '“%s” removed. %sUndo?%s' => '“%s” đã được xóa khỏi giỏ hàng. %sHoàn tác?%s',
                '%s removed. %sUndo?%s' => '%s đã được xóa khỏi giỏ hàng. %sHoàn tác?%s',
                'Undo?' => 'Hoàn tác?',
                'Your cart is currently empty.' => 'Giỏ hàng của bạn hiện đang trống.',
                'Return to shop' => 'Tiếp tục mua hàng',
                'Continue shopping' => 'Tiếp tục mua hàng',
                'Remove item' => 'Xóa sản phẩm',
                'Product' => 'Sản phẩm',
                'Products' => 'Sản phẩm',
                'Checkout' => 'Thanh toán',
                'Have a coupon?' => 'Bạn có mã ưu đãi?',
                'Click here to enter your code' => 'Bấm vào đây để nhập mã',
                'If you have a coupon code, please apply it below.' => 'Nếu bạn có mã ưu đãi, vui lòng nhập bên dưới.',
                'Direct bank transfer' => 'Chuyển khoản ngân hàng',
                'Cash on delivery' => 'Thanh toán khi nhận hàng',
                'Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.' => 'Vui lòng chuyển khoản trực tiếp vào tài khoản ngân hàng của chúng tôi. Sử dụng mã đơn hàng làm nội dung thanh toán. Đơn hàng sẽ được xử lý sau khi chúng tôi xác nhận thanh toán.',
                'Pay with cash upon delivery.' => 'Thanh toán bằng tiền mặt khi nhận hàng.',
                'Notes about your order, e.g. special notes for delivery.' => 'Ghi chú về đơn hàng, ví dụ thời gian nhận hàng hoặc yêu cầu giao hàng.',
                'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our %s.' => 'Thông tin cá nhân của bạn sẽ được dùng để xử lý đơn hàng, hỗ trợ trải nghiệm trên website và cho các mục đích khác được nêu trong %s.',
                'privacy policy' => 'chính sách bảo mật',
                'I have read and agree to the website %s' => 'Tôi đã đọc và đồng ý với %s của website',
                'terms and conditions' => 'điều khoản sử dụng',
                'Billing details' => 'Thông tin thanh toán',
                'Additional information' => 'Thông tin bổ sung',
                'Your order' => 'Đơn hàng của bạn',
                'Place order' => 'Đặt hàng',
                'Ship to a different address?' => 'Giao đến địa chỉ khác?',
                'Order notes' => 'Ghi chú đơn hàng',
                'Order notes (optional)' => 'Ghi chú đơn hàng (không bắt buộc)',
                'First name' => 'Tên',
                'Last name' => 'Họ',
                'Company name' => 'Tên công ty',
                'Country / Region' => 'Quốc gia / Khu vực',
                'Street address' => 'Địa chỉ',
                'Apartment, suite, unit, etc. (optional)' => 'Căn hộ, tòa nhà, đơn vị... (không bắt buộc)',
                'Town / City' => 'Tỉnh / Thành phố',
                'State / County' => 'Quận / Huyện',
                'Postcode / ZIP' => 'Mã bưu điện',
                'Phone' => 'Số điện thoại',
                'Email address' => 'Địa chỉ email',
                'Payment' => 'Thanh toán',
                'Shipping' => 'Giao hàng',
                'Shipping options' => 'Tùy chọn giao hàng',
                'No shipping options were found.' => 'Không tìm thấy tùy chọn giao hàng.',
                'Order received' => 'Đã nhận đơn hàng',
                'Thank you. Your order has been received.' => 'Cảm ơn bạn. Đơn hàng của bạn đã được ghi nhận.',
                'Order number:' => 'Mã đơn hàng:',
                'Date:' => 'Ngày:',
                'Email:' => 'Email:',
                'Payment method:' => 'Phương thức thanh toán:',
                'Order details' => html_entity_decode( 'Chi ti&#7871;t &#273;&#417;n h&agrave;ng', ENT_QUOTES, 'UTF-8' ),
                'Billing address' => html_entity_decode( '&#272;&#7883;a ch&#7881; thanh to&aacute;n', ENT_QUOTES, 'UTF-8' ),
                'Shipping address' => html_entity_decode( '&#272;&#7883;a ch&#7881; giao h&agrave;ng', ENT_QUOTES, 'UTF-8' ),
                'Subtotal:' => html_entity_decode( 'T&#7841;m t&iacute;nh:', ENT_QUOTES, 'UTF-8' ),
                'Total:' => html_entity_decode( 'T&#7893;ng c&#7897;ng:', ENT_QUOTES, 'UTF-8' ),
                'TOTAL:' => html_entity_decode( 'T&#7893;ng c&#7897;ng:', ENT_QUOTES, 'UTF-8' ),
                'Order:' => html_entity_decode( '&#272;&#417;n h&agrave;ng:', ENT_QUOTES, 'UTF-8' ),
                'Order date:' => html_entity_decode( 'Ng&agrave;y &#273;&#7863;t h&agrave;ng:', ENT_QUOTES, 'UTF-8' ),
                'Order total:' => html_entity_decode( 'T&#7893;ng ti&#7873;n:', ENT_QUOTES, 'UTF-8' ),
                'Order again' => html_entity_decode( '&#272;&#7863;t l&#7841;i', ENT_QUOTES, 'UTF-8' ),
                'View order' => html_entity_decode( 'Xem &#273;&#417;n h&agrave;ng', ENT_QUOTES, 'UTF-8' ),
                'My account' => 'Tài khoản của tôi',
                'Dashboard' => 'Bảng điều khiển',
                'Orders' => 'Đơn hàng',
                'Downloads' => 'Tải xuống',
                'Addresses' => 'Địa chỉ',
                'Account details' => 'Thông tin tài khoản',
                'Logout' => 'Đăng xuất',
                'Login' => 'Đăng nhập',
                'Register' => 'Đăng ký',
                'Username or email address' => 'Tên đăng nhập hoặc email',
                'Password' => 'Mật khẩu',
                'Remember me' => 'Ghi nhớ đăng nhập',
                'Lost your password?' => 'Quên mật khẩu?',
                'Related products' => 'Sản phẩm liên quan',
                'You may also like&hellip;' => 'Có thể bạn cũng thích...',
                'Description' => 'Mô tả',
                'Reviews' => 'Đánh giá',
                'There are no reviews yet.' => 'Chưa có đánh giá nào.',
                'Add a review' => 'Thêm đánh giá',
                'Your rating' => 'Đánh giá của bạn',
                'Your review' => 'Nhận xét của bạn',
                'Submit' => 'Gửi',
                'SKU:' => 'Mã sản phẩm:',
                'Category:' => 'Danh mục:',
                'Categories:' => 'Danh mục:',
                'Tag:' => 'Thẻ:',
                'Tags:' => 'Thẻ:',
                'Sale!' => 'Giảm giá!',
                'Read more' => 'Xem thêm',
                'Search products&hellip;' => 'Tìm sản phẩm...',
                'Search' => 'Tìm kiếm',
                'Showing the single result' => 'Hiển thị một kết quả',
                'Default sorting' => 'Sắp xếp mặc định',
                'Sort by popularity' => 'Sắp xếp theo độ phổ biến',
                'Sort by average rating' => 'Sắp xếp theo đánh giá',
                'Sort by latest' => 'Mới nhất',
                'Sort by price: low to high' => 'Giá thấp đến cao',
                'Sort by price: high to low' => 'Giá cao đến thấp',
            )
        );
        return isset( $translations[ $text ] ) ? $translations[ $text ] : $translated;
    }
}
add_filter( 'gettext', 'az_woocommerce_vietnamese_text', 20, 3 );

if ( ! function_exists( 'az_woocommerce_is_vietnamese' ) ) {
    function az_woocommerce_is_vietnamese() {
        return ! function_exists( 'az_get_current_language' ) || 'en' !== az_get_current_language();
    }
}

if ( ! function_exists( 'az_woocommerce_notice_text' ) ) {
    function az_woocommerce_notice_text( $message ) {
        if ( ! az_woocommerce_is_vietnamese() ) {
            return $message;
        }

        if ( false === stripos( wp_strip_all_tags( $message ), 'Please choose product options by visiting' ) ) {
            return $message;
        }

        return preg_replace(
            '/Please choose product options by visiting\s*(.*?)(?:\.|$)/i',
            'Vui lòng chọn tùy chọn sản phẩm tại $1.',
            $message
        );
    }
}
add_filter( 'woocommerce_add_error', 'az_woocommerce_notice_text', 20 );
add_filter( 'woocommerce_add_notice', 'az_woocommerce_notice_text', 20 );

if ( ! function_exists( 'az_translate_stored_woocommerce_notices' ) ) {
    function az_translate_stored_woocommerce_notices() {
        if ( is_admin() || ! function_exists( 'WC' ) || ! WC()->session ) {
            return;
        }

        $notices = WC()->session->get( 'wc_notices', array() );
        if ( empty( $notices ) || ! is_array( $notices ) ) {
            return;
        }

        $changed = false;
        foreach ( $notices as $type => $items ) {
            if ( empty( $items ) || ! is_array( $items ) ) {
                continue;
            }

            foreach ( $items as $index => $notice ) {
                if ( empty( $notice['notice'] ) ) {
                    continue;
                }

                $translated_notice = az_woocommerce_notice_text( $notice['notice'] );
                if ( $translated_notice !== $notice['notice'] ) {
                    $notices[ $type ][ $index ]['notice'] = $translated_notice;
                    $changed = true;
                }
            }
        }

        if ( $changed ) {
            WC()->session->set( 'wc_notices', $notices );
        }
    }
}
add_action( 'template_redirect', 'az_translate_stored_woocommerce_notices', 5 );
if ( ! function_exists( 'az_woocommerce_vietnamese_plural_text' ) ) {
    function az_woocommerce_vietnamese_plural_text( $translation, $single, $plural, $number, $domain ) {
        if ( 'woocommerce' !== $domain || ! az_woocommerce_is_vietnamese() ) {
            return $translation;
        }

        $plural_translations = array(
            'Showing %1$d&ndash;%2$d of %3$d result' => 'Hiển thị %1$d-%2$d trong %3$d kết quả',
            'Showing %1$d&ndash;%2$d of %3$d results' => 'Hiển thị %1$d-%2$d trong %3$d kết quả',
            '%d item' => '%d sản phẩm',
            '%d items' => '%d sản phẩm',
            '%s review' => '%s đánh giá',
            '%s reviews' => '%s đánh giá',
        );

        if ( isset( $plural_translations[ $single ] ) ) {
            return $plural_translations[ $single ];
        }

        if ( isset( $plural_translations[ $plural ] ) ) {
            return $plural_translations[ $plural ];
        }

        return $translation;
    }
}
add_filter( 'ngettext', 'az_woocommerce_vietnamese_plural_text', 20, 5 );
if ( ! function_exists( 'az_translate_payment_gateway_text' ) ) {
    function az_translate_payment_gateway_text( $text, $gateway_id = '' ) {
        if ( ! az_woocommerce_is_vietnamese() ) {
            return $text;
        }

        $map = array(
            'Direct bank transfer' => 'Chuyển khoản ngân hàng',
            'Cash on delivery' => 'Thanh toán khi nhận hàng',
            'Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.' => 'Vui lòng chuyển khoản trực tiếp vào tài khoản ngân hàng của chúng tôi. Sử dụng mã đơn hàng làm nội dung thanh toán. Đơn hàng sẽ được xử lý sau khi chúng tôi xác nhận thanh toán.',
            'Pay with cash upon delivery.' => 'Thanh toán bằng tiền mặt khi nhận hàng.',
        );

        return isset( $map[ $text ] ) ? $map[ $text ] : $text;
    }
}
add_filter( 'woocommerce_gateway_title', 'az_translate_payment_gateway_text', 20, 2 );
add_filter( 'woocommerce_gateway_description', 'az_translate_payment_gateway_text', 20, 2 );

if ( ! function_exists( 'az_woocommerce_loop_add_to_cart_text' ) ) {
    function az_woocommerce_loop_add_to_cart_text( $text, $product = null ) {
        if ( $product && method_exists( $product, 'is_type' ) && $product->is_type( 'variable' ) ) {
            return function_exists( 'az_t' ) ? az_t( 'Chọn tùy chọn', 'Select options' ) : 'Chọn tùy chọn';
        }

        return function_exists( 'az_t' ) ? az_t( 'Thêm vào giỏ hàng', 'Add to cart' ) : 'Thêm vào giỏ hàng';
    }
}
add_filter( 'woocommerce_product_add_to_cart_text', 'az_woocommerce_loop_add_to_cart_text', 20, 2 );
add_filter( 'woocommerce_product_single_add_to_cart_text', function() {
    return function_exists( 'az_t' ) ? az_t( 'Thêm vào giỏ hàng', 'Add to cart' ) : 'Thêm vào giỏ hàng';
} );

add_filter( 'woocommerce_get_script_data', function( $params, $handle ) {
    if ( 'wc-add-to-cart-variation' !== $handle || ! is_array( $params ) || ! az_woocommerce_is_vietnamese() ) {
        return $params;
    }

    $params['i18n_no_matching_variations_text'] = 'Rất tiếc, tổ hợp sản phẩm này không khả dụng. Vui lòng chọn tùy chọn khác.';
    $params['i18n_make_a_selection_text']       = 'Vui lòng chọn đầy đủ tùy chọn sản phẩm trước khi thêm vào giỏ hàng.';
    $params['i18n_unavailable_text']            = 'Sản phẩm này hiện không khả dụng.';

    return $params;
}, 20, 2 );

if ( ! function_exists( 'az_translate_added_to_cart_message' ) ) {
    function az_translate_added_to_cart_message( $message, $products, $show_qty ) {
        if ( empty( $products ) || ! is_array( $products ) ) {
            return $message;
        }

        $items = array();
        foreach ( $products as $product_id => $qty ) {
            $product = wc_get_product( $product_id );
            if ( ! $product ) {
                continue;
            }

            $name = $product->get_name();
            if ( $show_qty && $qty > 1 ) {
                $name .= ' x ' . $qty;
            }
            $items[] = '&ldquo;' . esc_html( $name ) . '&rdquo;';
        }

        if ( empty( $items ) ) {
            return $message;
        }

        $product_text = implode( ', ', $items );
        $cart_link    = sprintf(
            ' <a href="%s" class="button az-button wc-forward">%s</a>',
            esc_url( wc_get_cart_url() ),
            esc_html( function_exists( 'az_t' ) ? az_t( 'Xem giỏ hàng', 'View cart' ) : 'Xem giỏ hàng' )
        );

        return az_woocommerce_is_vietnamese()
            ? sprintf( '%s đã được thêm vào giỏ hàng.%s', $product_text, $cart_link )
            : sprintf( '%s has been added to your cart.%s', $product_text, $cart_link );
    }
}
add_filter( 'wc_add_to_cart_message_html', 'az_translate_added_to_cart_message', 20, 3 );

if ( ! function_exists( 'az_track_recently_viewed_product' ) ) {
    function az_track_recently_viewed_product() {
        if ( is_admin() || ! function_exists( 'is_product' ) || ! is_product() ) {
            return;
        }

        $product_id = get_queried_object_id();
        if ( ! $product_id ) {
            return;
        }

        $viewed = array();
        if ( ! empty( $_COOKIE['az_recent_products'] ) ) {
            $viewed = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_COOKIE['az_recent_products'] ) ) ) ) );
        }

        $viewed = array_values( array_diff( $viewed, array( (int) $product_id ) ) );
        array_unshift( $viewed, (int) $product_id );
        $viewed = array_slice( array_unique( $viewed ), 0, 12 );

        setcookie( 'az_recent_products', implode( ',', $viewed ), time() + MONTH_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true );
        $_COOKIE['az_recent_products'] = implode( ',', $viewed );
    }
}
add_action( 'template_redirect', 'az_track_recently_viewed_product' );

if ( ! function_exists( 'az_translate_color_attribute_label' ) ) {
    /**
     * Use a consistent Vietnamese label for the common color attribute.
     *
     * @param string          $label   Attribute label.
     * @param string          $name    Attribute name.
     * @param WC_Product|null $product Product instance.
     * @return string
     */
    function az_translate_color_attribute_label( $label, $name = '', $product = null ) {
        unset( $product );

        $attribute_name = sanitize_title( (string) $name );
        $attribute_label = sanitize_title( wp_strip_all_tags( (string) $label ) );
        $color_names = array( 'pa_color', 'pa-color', 'color', 'pa_colour', 'pa-colour', 'colour' );

        if ( in_array( $attribute_name, $color_names, true ) || in_array( $attribute_label, array( 'color', 'colour' ), true ) ) {
            return 'Màu sắc';
        }

        return $label;
    }
}
add_filter( 'woocommerce_attribute_label', 'az_translate_color_attribute_label', 20, 3 );

add_action( 'wp_footer', function() {
    if ( ! function_exists( 'is_product' ) || ! is_product() ) {
        return;
    }
    ?>
    <script>
    (function($){
        function chooseFirstVariationDefaults($form){
            var changed = false;

            $form.find('table.variations select').each(function(){
                var $select = $(this);

                if ($select.val()) {
                    return;
                }

                var firstValue = '';
                $select.find('option:not(:disabled)').each(function(){
                    var value = $(this).attr('value');
                    if (value) {
                        firstValue = value;
                        return false;
                    }
                });

                if (firstValue) {
                    $select.val(firstValue);
                    changed = true;
                }
            });

            if (changed) {
                $form.find('table.variations select').first().trigger('change');
                $form.trigger('check_variations');
            }
        }

        function buildVariationLabels($form){
            $form.find('table.variations select').each(function(){
                var $select = $(this);
                if ($select.data('az-label-ready')) return;

                var $cell = $select.closest('td');
                var $wrap = $('<div class="az-variation-labels" role="radiogroup"></div>');
                var selectId = $select.attr('id');
                var groupLabel = selectId ? $.trim($form.find('label[for="' + selectId + '"]').first().text()) : '';

                if (groupLabel) {
                    $wrap.attr('aria-label', groupLabel);
                }

                function syncVariationLabels(){
                    var selected = String($select.val() || '');

                    $wrap.find('.az-variation-label').each(function(){
                        var $button = $(this);
                        var value = String($button.attr('data-value') || '');
                        var $option = $select.find('option').filter(function(){
                            return String($(this).attr('value') || '') === value;
                        }).first();
                        var isSelected = value === selected;
                        var isDisabled = !$option.length || $option.prop('disabled');

                        $button
                            .toggleClass('is-selected', isSelected)
                            .toggleClass('is-disabled', isDisabled)
                            .prop('disabled', isDisabled)
                            .attr('aria-checked', isSelected ? 'true' : 'false');
                    });
                }

                $select.find('option').each(function(){
                    var value = $(this).attr('value');
                    var text = $.trim($(this).text());
                    if (!value) return;

                    var $button = $('<button type="button" class="az-variation-label"></button>');
                    $button
                        .attr({
                            'data-value': value,
                            'role': 'radio',
                            'aria-checked': 'false'
                        })
                        .text(text);
                    $wrap.append($button);
                });

                if (!$wrap.children().length) return;

                $wrap.on('click', '.az-variation-label', function(){
                    if ($(this).prop('disabled')) return;

                    var value = $(this).attr('data-value');
                    $select.val(value).trigger('change');
                    syncVariationLabels();
                });

                $select.on('change az_sync_variation_labels', syncVariationLabels);

                $cell.append($wrap);
                $select.addClass('az-variation-select-hidden').data('az-label-ready', true);
                syncVariationLabels();
            });
        }

        $(function(){
            $('.variations_form').each(function(){
                var $form = $(this);
                chooseFirstVariationDefaults($form);
                buildVariationLabels($form);
            });
            $(document.body).on('woocommerce_variation_has_changed woocommerce_update_variation_values reset_data', function(){
                $('.variations_form').each(function(){
                    var $form = $(this);
                    chooseFirstVariationDefaults($form);
                    buildVariationLabels($form);
                    $form.find('table.variations select').trigger('az_sync_variation_labels');
                });
            });
        });
    })(jQuery);
    </script>
    <?php
}, 30 );

if ( ! function_exists( 'az_ensure_woocommerce_pages' ) ) {
    function az_ensure_woocommerce_pages() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return;
        }

        $pages = array(
            'woocommerce_shop_page_id' => array(
                'title'   => 'Cửa hàng',
                'slug'    => 'cua-hang',
                'content' => '',
            ),
            'woocommerce_cart_page_id' => array(
                'title'   => 'Giỏ hàng',
                'slug'    => 'gio-hang',
                'content' => '[woocommerce_cart]',
            ),
            'woocommerce_checkout_page_id' => array(
                'title'   => 'Thanh toán',
                'slug'    => 'thanh-toan',
                'content' => '[woocommerce_checkout]',
            ),
            'woocommerce_myaccount_page_id' => array(
                'title'   => 'Tài khoản',
                'slug'    => 'tai-khoan',
                'content' => '[woocommerce_my_account]',
            ),
            'woocommerce_order_tracking_page_id' => array(
                'title'   => 'Theo dõi đơn hàng',
                'slug'    => 'theo-doi-don-hang',
                'content' => '[woocommerce_order_tracking]',
            ),
        );

        foreach ( $pages as $option_name => $page_data ) {
            $page_id = absint( get_option( $option_name ) );

            $page = $page_id ? get_post( $page_id ) : null;

            if ( $page && 'page' === $page->post_type ) {
                // WooCommerce can create the page record before its shortcode is
                // added. Populate only an empty system page; never replace
                // content the site owner has already customized.
                if ( '' !== $page_data['content'] && '' === trim( $page->post_content ) ) {
                    wp_update_post(
                        array(
                            'ID'           => $page->ID,
                            'post_content' => $page_data['content'],
                        )
                    );
                }

                if ( in_array( $page->post_title, array( 'Shop', 'Cart', 'Checkout', 'My account', 'My Account', 'Order tracking', 'Order Tracking' ), true ) ) {
                    wp_update_post(
                        array(
                            'ID'         => $page->ID,
                            'post_title' => $page_data['title'],
                        )
                    );
                }

                continue;
            }

            $existing_page = get_page_by_path( $page_data['slug'], OBJECT, 'page' );

            if ( $existing_page ) {
                $page_id = (int) $existing_page->ID;

                if ( '' !== $page_data['content'] && '' === trim( $existing_page->post_content ) ) {
                    wp_update_post(
                        array(
                            'ID'           => $page_id,
                            'post_content' => $page_data['content'],
                        )
                    );
                }

                if ( in_array( $existing_page->post_title, array( 'Shop', 'Cart', 'Checkout', 'My account', 'My Account', 'Order tracking', 'Order Tracking' ), true ) ) {
                    wp_update_post(
                        array(
                            'ID'         => $page_id,
                            'post_title' => $page_data['title'],
                        )
                    );
                }
            } else {
                $page_id = wp_insert_post(
                    array(
                        'post_type'    => 'page',
                        'post_status'  => 'publish',
                        'post_title'   => $page_data['title'],
                        'post_name'    => $page_data['slug'],
                        'post_content' => $page_data['content'],
                    )
                );
            }

            if ( $page_id && ! is_wp_error( $page_id ) ) {
                update_option( $option_name, (int) $page_id );
            }
        }
    }
}
add_action( 'admin_init', 'az_ensure_woocommerce_pages' );
add_action( 'after_switch_theme', 'az_ensure_woocommerce_pages' );

if ( ! function_exists( 'az_is_woocommerce_system_page' ) ) {
    /**
     * Whether the current page is one of WooCommerce's utility pages.
     *
     * These pages use shortcodes and should not inherit the normal page
     * template's article sidebar and table of contents.
     *
     * @return bool
     */
    function az_is_woocommerce_system_page() {
        if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_page_id' ) || ! is_page() ) {
            return false;
        }

        $page_ids = array_filter(
            array_map(
                'absint',
                array(
                    wc_get_page_id( 'cart' ),
                    wc_get_page_id( 'checkout' ),
                    wc_get_page_id( 'myaccount' ),
                    wc_get_page_id( 'order_tracking' ),
                )
            )
        );

        return ! empty( $page_ids ) && is_page( $page_ids );
    }
}

if ( ! function_exists( 'az_filter_woocommerce_products_query' ) ) {
    function az_filter_woocommerce_products_query( $query ) {
        if ( is_admin() || ! $query->is_main_query() || ! post_type_exists( 'product' ) ) {
            return;
        }

        $is_product_request = is_post_type_archive( 'product' ) || is_tax( 'product_cat' ) || $query->get( 'post_type' ) === 'product' || ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'product' );

        if ( ! $is_product_request ) {
            return;
        }

        $tax_query = (array) $query->get( 'tax_query' );

        foreach ( array( 'product_cat', 'product_warehouse', 'product_brand', 'pa_brand', 'pa_thuong-hieu' ) as $taxonomy ) {
            if ( empty( $_GET[ $taxonomy ] ) || ! taxonomy_exists( $taxonomy ) ) {
                continue;
            }

            $tax_query[] = array(
                'taxonomy'         => $taxonomy,
                'field'            => 'slug',
                'terms'            => sanitize_title( wp_unslash( $_GET[ $taxonomy ] ) ),
                'include_children' => true,
            );
        }

        if ( count( $tax_query ) > 1 ) {
            $tax_query['relation'] = 'AND';
        }

        if ( ! empty( $tax_query ) ) {
            $query->set( 'tax_query', $tax_query );
        }

        $meta_query = (array) $query->get( 'meta_query' );

        $orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : '';
        if ( 'sales' === $orderby ) {
            $query->set( 'meta_key', 'total_sales' );
            $query->set( 'orderby', 'meta_value_num' );
            $query->set( 'order', 'DESC' );
        } elseif ( 'price' === $orderby || 'price-desc' === $orderby ) {
            $query->set( 'meta_key', '_price' );
            $query->set( 'orderby', 'meta_value_num' );
            $query->set( 'order', 'price-desc' === $orderby ? 'DESC' : 'ASC' );
        } elseif ( 'sale' === $orderby ) {
            $meta_query[] = array(
                'key'     => '_sale_price',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'DECIMAL',
            );
        } elseif ( 'date' === $orderby || '' === $orderby ) {
            $query->set( 'orderby', 'date' );
            $query->set( 'order', 'DESC' );
        }

        if ( ! empty( $meta_query ) ) {
            $query->set( 'meta_query', $meta_query );
        }

        $query->set( 'post_type', 'product' );
    }
}
add_action( 'pre_get_posts', 'az_filter_woocommerce_products_query' );
