<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Call Google Translate unofficial API.
 * Splits long content into chunks to stay within URL limits.
 */
function az_google_translate( $text, $source = 'auto', $target = 'en', $is_html = false ) {
    // For HTML content, split by block-level tags to preserve structure
    if ( $is_html ) {
        return az_translate_html( $text, $source, $target );
    }

    return az_translate_chunk( $text, $source, $target );
}

function az_translate_chunk( $text, $source, $target ) {
    // Split plain text into ~3000-char chunks at sentence boundaries
    // (shorter chunks = more reliable with GET URL length limits)
    $chunks  = array();
    $parts   = preg_split( '/(?<=[.!?\n])\s+/u', $text );
    $current = '';

    foreach ( $parts as $part ) {
        if ( strlen( $current ) + strlen( $part ) > 3000 ) {
            if ( $current !== '' ) $chunks[] = $current;
            $current = $part;
        } else {
            $current .= ( $current ? ' ' : '' ) . $part;
        }
    }
    if ( $current !== '' ) $chunks[] = $current;

    $result = '';
    foreach ( $chunks as $chunk ) {
        $translated = az_call_google_translate( $chunk, $source, $target );
        if ( is_wp_error( $translated ) ) return $translated;
        $result .= $translated;
    }

    return $result;
}

function az_translate_html( $text, $source, $target ) {
    // Parse HTML into individual block nodes, translate each one's inner text,
    // then reassemble — preserving all tags, attributes, and structure.

    // Normalize line endings
    $text = str_replace( "\r\n", "\n", $text );

    // Split into top-level block elements using regex
    // Matches: <p>, <h1-h6>, <ul>, <ol>, <li>, <blockquote>, <figure>, <div>, <table>, etc.
    // Also handles self-closing and non-block content between blocks
    $block_pattern = '/(<!--.*?-->|<(figure|div|blockquote|table|thead|tbody|tr|ul|ol)[^>]*>.*?<\/\2>|<(p|h[1-6]|li|th|td|figcaption)[^>]*>.*?<\/\3>|<img[^>]*\/?>|<hr[^>]*\/?>)/si';

    // Split HTML into segments: matched blocks + text between them
    $segments = preg_split( $block_pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

    if ( $segments === false || count( $segments ) <= 1 ) {
        // Fallback: treat as plain text blocks separated by newlines
        return az_translate_html_fallback( $text, $source, $target );
    }

    // Better approach: use DOMDocument-style block splitting
    return az_translate_html_blocks( $text, $source, $target );
}

function az_translate_html_blocks( $html, $source, $target ) {
    // Split HTML into top-level blocks by splitting on block-level opening tags
    // Strategy: split at each top-level block tag, translate inner text, reassemble

    // Use a token-based approach: find all block-level elements at top level
    $len      = strlen( $html );

    // Block tags we want to split on
    $block_tags = 'p|h[1-6]|ul|ol|li|blockquote|figure|figcaption|div|table|thead|tbody|tr|th|td|pre|section|article|aside|header|footer|nav';

    // Match all top-level block elements
    $pattern = '/<(' . $block_tags . ')(\s[^>]*)?>.*?<\/\1>|<(img|hr|br)[^>]*\/?>/si';

    preg_match_all( $pattern, $html, $matches, PREG_OFFSET_CAPTURE );

    if ( empty( $matches[0] ) ) {
        // No block elements found — translate as plain text
        return az_translate_chunk( wp_strip_all_tags( $html ), $source, $target );
    }

    $blocks        = array(); // each: ['html' => full block html, 'offset' => int, 'length' => int]
    $last_offset   = 0;

    foreach ( $matches[0] as $match ) {
        $block_html   = $match[0];
        $block_offset = $match[1];

        // Capture any text/html between previous block and this one
        if ( $block_offset > $last_offset ) {
            $between = substr( $html, $last_offset, $block_offset - $last_offset );
            if ( trim( $between ) !== '' ) {
                $blocks[] = array( 'type' => 'raw', 'html' => $between );
            }
        }

        $blocks[]     = array( 'type' => 'block', 'html' => $block_html );
        $last_offset  = $block_offset + strlen( $block_html );
    }

    // Capture trailing content
    if ( $last_offset < $len ) {
        $trailing = substr( $html, $last_offset );
        if ( trim( $trailing ) !== '' ) {
            $blocks[] = array( 'type' => 'raw', 'html' => $trailing );
        }
    }

    // Now translate each block's inner text content
    $output = '';
    foreach ( $blocks as $block ) {
        if ( $block['type'] === 'raw' ) {
            // Raw text between blocks — translate if non-empty
            $inner = trim( $block['html'] );
            if ( $inner === '' || preg_match( '/^[\s\n\r]+$/', $inner ) ) {
                $output .= $block['html'];
            } else {
                $translated = az_translate_inner_html( $block['html'], $source, $target );
                $output    .= is_wp_error( $translated ) ? $block['html'] : $translated;
            }
        } else {
            // Block element — translate its inner content, preserve outer tag
            $translated = az_translate_block_element( $block['html'], $source, $target );
            $output    .= is_wp_error( $translated ) ? $block['html'] : $translated;
        }
    }

    return $output;
}

function az_translate_block_element( $block_html, $source, $target ) {
    // Self-closing tags (img, hr, br) — return as-is
    if ( preg_match( '/^<(img|hr|br|input)[^>]*\/?>$/si', trim( $block_html ) ) ) {
        return $block_html;
    }

    // Extract outer tag, attributes, inner HTML
    if ( ! preg_match( '/^(<([a-z][a-z0-9]*)\b([^>]*)>)(.*?)(<\/\2>)$/si', $block_html, $m ) ) {
        // Can't parse — translate as plain text
        $plain      = wp_strip_all_tags( $block_html );
        $translated = az_call_google_translate( $plain, $source, $target );
        return is_wp_error( $translated ) ? $block_html : $translated;
    }

    $open_tag  = $m[1]; // e.g. <h2 class="...">
    $inner     = $m[4]; // inner HTML
    $close_tag = $m[5]; // e.g. </h2>

    // If inner has nested block elements (ul/ol/li/table), recurse
    $has_nested_blocks = preg_match( '/<(ul|ol|li|table|thead|tbody|tr|th|td|blockquote|figure|div)\b/si', $inner );

    if ( $has_nested_blocks ) {
        $translated_inner = az_translate_html_blocks( $inner, $source, $target );
        return $open_tag . $translated_inner . $close_tag;
    }

    // Leaf block — translate inner HTML preserving inline tags
    $translated_inner = az_translate_inner_html( $inner, $source, $target );
    if ( is_wp_error( $translated_inner ) ) return $block_html;

    return $open_tag . $translated_inner . $close_tag;
}

function az_translate_inner_html( $html, $source, $target ) {
    // Extract plain text (strip inline tags), translate, then reinsert inline tags
    // Simple approach: translate the text content, keep inline HTML tags in place

    // If no HTML tags, translate directly
    if ( strpos( $html, '<' ) === false ) {
        $trimmed = trim( $html );
        if ( $trimmed === '' ) return $html;
        $translated = az_call_google_translate( $trimmed, $source, $target );
        if ( is_wp_error( $translated ) ) return $html;
        return $translated;
    }

    // Has inline tags (strong, em, a, span, img...) — translate text nodes only
    // Split into text segments and tag segments
    $parts      = preg_split( '/(<[^>]+>)/s', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
    $tag_map    = array();

    // Collect all text segments for batch translation
    $text_only = '';

    foreach ( $parts as $i => $part ) {
        if ( preg_match( '/^<[^>]+>$/', $part ) ) {
            // It's a tag — use placeholder
            $placeholder  = "\x00TAG{$i}\x00";
            $text_only   .= $placeholder;
            $tag_map[$placeholder] = $part;
        } else {
            $text_only .= $part;
        }
    }

    if ( trim( str_replace( array_keys( $tag_map ), '', $text_only ) ) === '' ) {
        return $html; // only tags, no text
    }

    // Translate the combined text+placeholders
    // Split into chunks if needed, but keep placeholders intact
    $translated = az_call_google_translate( $text_only, $source, $target );
    if ( is_wp_error( $translated ) ) return $html;

    // Restore tags from placeholders
    $result = str_replace( array_keys( $tag_map ), array_values( $tag_map ), $translated );

    return $result;
}

function az_translate_html_fallback( $html, $source, $target ) {
    // Last resort: split by double newlines, translate each paragraph
    $paragraphs = preg_split( '/\n{2,}/', $html );
    $output     = '';

    foreach ( $paragraphs as $para ) {
        $para = trim( $para );
        if ( $para === '' ) continue;

        $plain      = wp_strip_all_tags( $para );
        $translated = az_call_google_translate( $plain, $source, $target );
        if ( is_wp_error( $translated ) ) {
            $output .= '<p>' . esc_html( $plain ) . '</p>';
        } else {
            $output .= '<p>' . esc_html( $translated ) . '</p>';
        }
    }

    return $output;
}

function az_call_google_translate( $text, $source, $target ) {
    // Google Translate unofficial API — uses GET request with query params
    $url = 'https://translate.googleapis.com/translate_a/single?' . http_build_query( array(
        'client' => 'gtx',
        'sl'     => $source,  // source language (vi or auto)
        'tl'     => $target,  // target language
        'dt'     => 't',
        'q'      => $text,
    ) );

    $response = wp_remote_get( $url, array(
        'timeout' => 30,
        'headers' => array(
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
            'Referer'    => 'https://translate.google.com/',
        ),
    ) );

    if ( is_wp_error( $response ) ) {
        return new WP_Error( 'translate_error', 'Không thể kết nối đến dịch vụ dịch: ' . $response->get_error_message() );
    }

    $code = wp_remote_retrieve_response_code( $response );
    if ( $code !== 200 ) {
        return new WP_Error( 'translate_error', 'Dịch vụ trả về lỗi HTTP ' . $code . '. Vui lòng thử lại sau.' );
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( ! is_array( $data ) || empty( $data[0] ) ) {
        return new WP_Error( 'translate_error', 'Không thể phân tích kết quả dịch.' );
    }

    // Reassemble translated segments
    $translated = '';
    foreach ( $data[0] as $segment ) {
        if ( isset( $segment[0] ) && is_string( $segment[0] ) ) {
            $translated .= $segment[0];
        }
    }

    return $translated;
}
