<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'az_product_youtube_id_from_value' ) ) {
    function az_product_youtube_id_from_value( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        $value = html_entity_decode( $value, ENT_QUOTES, get_bloginfo( 'charset' ) );
        if ( preg_match( '/<iframe[^>]+src=["\']([^"\']+)["\']/i', $value, $matches ) ) {
            $value = html_entity_decode( $matches[1], ENT_QUOTES, get_bloginfo( 'charset' ) );
        }

        $parts = wp_parse_url( $value );
        if ( empty( $parts['host'] ) ) {
            return preg_match( '/^[a-zA-Z0-9_-]{11}$/', $value ) ? $value : '';
        }

        $host  = strtolower( preg_replace( '/^www\./', '', $parts['host'] ) );
        $path  = isset( $parts['path'] ) ? trim( $parts['path'], '/' ) : '';
        $query = array();
        if ( ! empty( $parts['query'] ) ) {
            wp_parse_str( $parts['query'], $query );
        }

        if ( in_array( $host, array( 'youtu.be', 'youtube.com', 'm.youtube.com' ), true ) ) {
            if ( 'youtu.be' === $host && $path ) {
                $video_id = strtok( $path, '/' );
                return preg_match( '/^[a-zA-Z0-9_-]{11}$/', $video_id ) ? $video_id : '';
            }

            if ( ! empty( $query['v'] ) && is_scalar( $query['v'] ) ) {
                return preg_match( '/^[a-zA-Z0-9_-]{11}$/', $query['v'] ) ? $query['v'] : '';
            }

            if ( preg_match( '#(?:embed|shorts|live)/([a-zA-Z0-9_-]{11})#', $path, $matches ) ) {
                return $matches[1];
            }
        }

        return '';
    }
}

if ( ! function_exists( 'az_product_youtube_watch_url' ) ) {
    function az_product_youtube_watch_url( $value ) {
        $video_id = az_product_youtube_id_from_value( $value );
        return $video_id ? 'https://www.youtube.com/watch?v=' . $video_id : '';
    }
}

if ( ! function_exists( 'az_product_youtube_embed_url' ) ) {
    function az_product_youtube_embed_url( $value ) {
        $video_id = az_product_youtube_id_from_value( $value );
        return $video_id ? 'https://www.youtube.com/embed/' . $video_id : '';
    }
}

if ( ! function_exists( 'az_get_product_video_urls' ) ) {
    function az_get_product_video_urls( $post_id ) {
        $urls = get_post_meta( $post_id, '_az_product_video_urls', true );
        $urls = is_array( $urls ) ? $urls : array();

        $legacy_count = absint( get_post_meta( $post_id, 'video', true ) );
        for ( $i = 0; $i < $legacy_count; $i++ ) {
            foreach ( array( "video_{$i}_video_ifame", "video_{$i}_video_iframe", "video_{$i}_video" ) as $legacy_key ) {
                $legacy_value = get_post_meta( $post_id, $legacy_key, true );
                if ( $legacy_value ) {
                    $urls[] = $legacy_value;
                    break;
                }
            }
        }

        foreach ( array( 'video_ifame', 'video_iframe', 'product_video', 'az_product_video' ) as $single_key ) {
            $single_value = get_post_meta( $post_id, $single_key, true );
            if ( $single_value ) {
                $urls[] = $single_value;
            }
        }

        $normalized = array();
        foreach ( $urls as $url ) {
            $watch_url = az_product_youtube_watch_url( $url );
            if ( $watch_url ) {
                $normalized[] = $watch_url;
            }
        }

        return array_values( array_unique( $normalized ) );
    }
}

if ( ! function_exists( 'az_add_product_video_metabox' ) ) {
    function az_add_product_video_metabox() {
        add_meta_box(
            'az_product_video_metabox',
            'Slider Video',
            'az_render_product_video_metabox',
            'product',
            'normal',
            'default'
        );
    }
}
add_action( 'add_meta_boxes_product', 'az_add_product_video_metabox' );

if ( ! function_exists( 'az_render_product_video_metabox' ) ) {
    function az_render_product_video_metabox( $post ) {
        wp_nonce_field( 'az_save_product_video_urls', 'az_product_video_nonce' );
        $video_urls = az_get_product_video_urls( $post->ID );
        if ( empty( $video_urls ) ) {
            $video_urls = array( '' );
        }
        ?>
        <div class="az-product-video-admin" data-az-product-video-admin>
            <p><strong>Video</strong></p>
            <p class="description">Nhập link YouTube cho từng video, ví dụ: https://www.youtube.com/watch?v=VIDEO_ID. Không cần dán mã iframe.</p>
            <div class="az-product-video-admin-rows" data-az-product-video-rows>
                <?php foreach ( $video_urls as $index => $video_url ) : ?>
                    <div class="az-product-video-admin-row">
                        <span><?php echo esc_html( $index + 1 ); ?></span>
                        <input type="url" name="az_product_video_urls[]" value="<?php echo esc_attr( $video_url ); ?>" placeholder="https://www.youtube.com/watch?v=..." class="widefat">
                        <button type="button" class="button" data-az-product-video-remove>Xóa</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <p><button type="button" class="button button-primary" data-az-product-video-add>Thêm video</button></p>
        </div>
        <style>
            .az-product-video-admin-row{display:grid;grid-template-columns:34px minmax(0,1fr) 70px;gap:8px;align-items:center;margin:8px 0}
            .az-product-video-admin-row span{color:#777;text-align:center}
        </style>
        <script>
        (function(){
            var root = document.querySelector('[data-az-product-video-admin]');
            if (!root) return;
            var rows = root.querySelector('[data-az-product-video-rows]');
            function refresh(){
                rows.querySelectorAll('.az-product-video-admin-row').forEach(function(row, index){
                    row.querySelector('span').textContent = index + 1;
                });
            }
            root.addEventListener('click', function(event){
                if (event.target.matches('[data-az-product-video-add]')) {
                    var row = document.createElement('div');
                    row.className = 'az-product-video-admin-row';
                    row.innerHTML = '<span></span><input type="url" name="az_product_video_urls[]" value="" placeholder="https://www.youtube.com/watch?v=..." class="widefat"><button type="button" class="button" data-az-product-video-remove>Xóa</button>';
                    rows.appendChild(row);
                    refresh();
                }
                if (event.target.matches('[data-az-product-video-remove]')) {
                    var row = event.target.closest('.az-product-video-admin-row');
                    if (row && rows.children.length > 1) {
                        row.remove();
                    } else if (row) {
                        row.querySelector('input').value = '';
                    }
                    refresh();
                }
            });
        })();
        </script>
        <?php
    }
}

if ( ! function_exists( 'az_save_product_video_urls' ) ) {
    function az_save_product_video_urls( $post_id ) {
        if ( ! isset( $_POST['az_product_video_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['az_product_video_nonce'] ) ), 'az_save_product_video_urls' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $urls = isset( $_POST['az_product_video_urls'] ) && is_array( $_POST['az_product_video_urls'] ) ? wp_unslash( $_POST['az_product_video_urls'] ) : array();
        $normalized = array();
        foreach ( $urls as $url ) {
            $watch_url = az_product_youtube_watch_url( $url );
            if ( $watch_url ) {
                $normalized[] = esc_url_raw( $watch_url );
            }
        }

        $normalized = array_values( array_unique( $normalized ) );
        if ( empty( $normalized ) ) {
            delete_post_meta( $post_id, '_az_product_video_urls' );
            return;
        }

        update_post_meta( $post_id, '_az_product_video_urls', $normalized );
    }
}
add_action( 'save_post_product', 'az_save_product_video_urls' );
