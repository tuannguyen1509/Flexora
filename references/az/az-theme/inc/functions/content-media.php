<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'az_is_local_image_url' ) ) {
    function az_is_local_image_url( $url ) {
        $url_host  = wp_parse_url( $url, PHP_URL_HOST );
        $site_host = wp_parse_url( home_url(), PHP_URL_HOST );

        return $url_host && $site_host && strtolower( $url_host ) === strtolower( $site_host );
    }
}

if ( ! function_exists( 'az_sideload_remote_image_url' ) ) {
    function az_sideload_remote_image_url( $url, $post_id = 0 ) {
        $url = esc_url_raw( html_entity_decode( $url ) );
        if ( ! $url || az_is_local_image_url( $url ) || ! preg_match( '/\.(jpe?g|png|gif|webp|avif)(\?.*)?$/i', $url ) ) {
            return $url;
        }

        if ( ! function_exists( 'media_sideload_image' ) ) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $existing_id = attachment_url_to_postid( $url );
        if ( $existing_id ) {
            $existing_url = wp_get_attachment_url( $existing_id );
            return $existing_url ?: $url;
        }

        $attachment_id = media_sideload_image( $url, $post_id, null, 'id' );
        if ( is_wp_error( $attachment_id ) ) {
            return $url;
        }

        $local_url = wp_get_attachment_url( $attachment_id );
        return $local_url ?: $url;
    }
}

if ( ! function_exists( 'az_localize_remote_images_in_html' ) ) {
    function az_localize_remote_images_in_html( $html, $post_id = 0 ) {
        if ( ! is_string( $html ) || false === stripos( $html, '<img' ) ) {
            return $html;
        }

        return preg_replace_callback(
            '/<img\b[^>]*\bsrc=["\']([^"\']+)["\'][^>]*>/i',
            function( $matches ) use ( $post_id ) {
                $old_url = $matches[1];
                $new_url = az_sideload_remote_image_url( $old_url, $post_id );

                if ( $new_url === $old_url ) {
                    return $matches[0];
                }

                return str_replace( $old_url, esc_url( $new_url ), $matches[0] );
            },
            $html
        );
    }
}

if ( ! function_exists( 'az_localize_remote_images_on_save' ) ) {
    function az_localize_remote_images_on_save( $post_id, $post, $update ) {
        // Importing media changes stored content and must be explicitly enabled.
        if ( ! az_get_option( 'general.import_remote_images', 0 ) || ! apply_filters( 'az_import_remote_images_on_save', true, $post_id ) ) {
            return;
        }
        if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $allowed_types = array( 'post', 'page', 'product' );
        if ( empty( $post->post_type ) || ! in_array( $post->post_type, $allowed_types, true ) ) {
            return;
        }

        $new_content = az_localize_remote_images_in_html( $post->post_content, $post_id );
        if ( $new_content !== $post->post_content ) {
            remove_action( 'save_post', 'az_localize_remote_images_on_save', 30 );
            wp_update_post(
                array(
                    'ID'           => $post_id,
                    'post_content' => $new_content,
                )
            );
            add_action( 'save_post', 'az_localize_remote_images_on_save', 30, 3 );
        }

        if ( 'product' === $post->post_type ) {
            foreach ( array( 'az_technical_specs', 'az_practical_applications' ) as $meta_key ) {
                $meta_value = get_post_meta( $post_id, $meta_key, true );
                $new_meta   = az_localize_remote_images_in_html( $meta_value, $post_id );
                if ( $new_meta !== $meta_value ) {
                    update_post_meta( $post_id, $meta_key, $new_meta );
                }
            }
        }
    }
}
add_action( 'save_post', 'az_localize_remote_images_on_save', 30, 3 );

if ( ! function_exists( 'az_strip_tinymce_selection_markers' ) ) {
    function az_strip_tinymce_selection_markers( $content ) {
        if ( ! is_string( $content ) || '' === $content ) {
            return $content;
        }

        $content = preg_replace( '/<span\b[^>]*class=["\'][^"\']*\bmce_SELRES_(?:start|end)\b[^"\']*["\'][^>]*>\s*<\/span>/i', '', $content );
        return str_replace( array( 'class="mce_SELRES_start"', "class='mce_SELRES_start'", 'class="mce_SELRES_end"', "class='mce_SELRES_end'" ), '', $content );
    }
}
add_filter( 'content_save_pre', 'az_strip_tinymce_selection_markers', 20 );

if ( ! function_exists( 'az_group_consecutive_content_images' ) ) {
    function az_group_consecutive_content_images( $content ) {
        if ( ! is_string( $content ) || false === stripos( $content, '<img' ) ) {
            return $content;
        }

        return preg_replace_callback(
            '/((?:<p>\s*(?:<a\b[^>]*>\s*<img\b[^>]*>\s*<\/a>|<img\b[^>]*>)\s*<\/p>\s*){2,})/i',
            function( $matches ) {
                preg_match_all( '/<a\b[^>]*>\s*<img\b[^>]*>\s*<\/a>|<img\b[^>]*>/i', $matches[1], $images );

                if ( empty( $images[0] ) || count( $images[0] ) < 2 ) {
                    return $matches[0];
                }

                return '<div class="az-content-image-row">' . implode( '', $images[0] ) . '</div>';
            },
            $content
        );
    }
}
add_filter( 'the_content', 'az_group_consecutive_content_images', 20 );
add_filter( 'woocommerce_short_description', 'az_group_consecutive_content_images', 20 );

if ( ! function_exists( 'az_tinymce_emoji_content_style' ) ) {
    function az_tinymce_emoji_content_style( $settings ) {
        $emoji_css = 'body img.emoji,body img.wp-smiley{display:inline!important;width:15px!important;min-width:15px!important;max-width:15px!important;height:15px!important;min-height:15px!important;max-height:15px!important;margin:0 .07em!important;padding:0!important;border:0!important;vertical-align:-.1em!important;object-fit:contain!important;}';

        $settings['content_style'] = empty( $settings['content_style'] ) ? $emoji_css : $settings['content_style'] . $emoji_css;
        return $settings;
    }
}
add_filter( 'tiny_mce_before_init', 'az_tinymce_emoji_content_style' );
