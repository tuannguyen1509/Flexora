<?php
/** @package AZ_AI_Theme */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/* ── Include Service Areas Settings ── */
$service_areas_file = get_template_directory() . '/inc/service-areas-settings.php';
if ( file_exists( $service_areas_file ) ) {
    require_once $service_areas_file;
}

// ── Dynamic Logo Size CSS ─────────────────────────────────────────────────────
add_action( 'wp_head', function() {
    $logo_size = get_theme_mod( 'az_logo_size', 52 );
    $logo_text_size = get_theme_mod( 'az_logo_text_size', 16 );
    $logo_pn_size = round( $logo_size * 0.38 ); // PN text = 38% of icon size
    $logo_subtitle_size = round( $logo_text_size * 0.55 ); // Subtitle = 55% of main text
    
    // Footer logo sizes (slightly smaller than header)
    $ft_logo_size = round( $logo_size * 0.69 ); // Footer icon = 69% of header (36px default)
    $ft_logo_pn_size = round( $ft_logo_size * 0.31 ); // Footer PN text
    $ft_logo_text_size = round( $logo_text_size * 0.9 ); // Footer text = 90% of header (14px default)
    ?>
    <style id="az-logo-size-css">
        /* ═══════════════════════════════════════════════════════════
           HEADER LOGO SIZES
        ═══════════════════════════════════════════════════════════ */
        
        /* Logo Icon Size */
        body.home .logo-fallback-icon,
        body.home #site-header #logo-wrap .logo-fallback-icon,
        body:not(.home) #site-header #logo-wrap .logo-fallback-icon {
            width: <?php echo $logo_size; ?>px !important;
            height: <?php echo $logo_size; ?>px !important;
        }
        
        /* Logo PN Text Size */
        body.home .logo-fallback-icon span,
        body.home #site-header #logo-wrap .logo-fallback-icon span,
        body:not(.home) #site-header #logo-wrap .logo-fallback-icon span {
            font-size: <?php echo $logo_pn_size; ?>px !important;
        }
        
        /* Logo Main Text Size */
        body.home .logo-fallback-text,
        body.home #site-header #logo-wrap .logo-fallback-text,
        body:not(.home) #site-header #logo-wrap .logo-fallback-text {
            font-size: <?php echo $logo_text_size; ?>px !important;
            width: <?php echo round( $logo_text_size * 12 ); ?>px !important;
        }
        
        /* Logo Subtitle Size */
        body.home .logo-fallback-text::before,
        body.home #site-header #logo-wrap .logo-fallback-text::before,
        body:not(.home) #site-header #logo-wrap .logo-fallback-text::before {
            font-size: <?php echo $logo_subtitle_size; ?>px !important;
        }
        
        /* Custom Logo Image */
        #logo-wrap .custom-logo-link img,
        #logo-wrap img.custom-logo {
            height: <?php echo round( $logo_size * 1.08 ); ?>px !important;
        }
        
        /* ═══════════════════════════════════════════════════════════
           FOOTER LOGO SIZES
        ═══════════════════════════════════════════════════════════ */
        
        /* Footer Logo Icon Size */
        #site-footer .ft-logo-fallback-icon {
            width: <?php echo $ft_logo_size; ?>px !important;
            height: <?php echo $ft_logo_size; ?>px !important;
        }
        
        /* Footer Logo PN Text Size */
        #site-footer .ft-logo-fallback-icon span {
            font-size: <?php echo $ft_logo_pn_size; ?>px !important;
        }
        
        /* Footer Logo Main Text Size */
        #site-footer .ft-logo-fallback-text {
            font-size: <?php echo $ft_logo_text_size; ?>px !important;
        }
        
        /* Footer Custom Logo Image */
        #ft-logo-wrap .custom-logo-link img,
        #ft-logo-wrap img.custom-logo {
            height: <?php echo round( $logo_size * 0.85 ); ?>px !important;
        }
    </style>
    <?php
}, 100 );

// ── Customizer Live Preview JS ────────────────────────────────────────────────
add_action( 'customize_preview_init', function() {
    add_action( 'wp_footer', function() {
        ?>
        <script>
        (function($) {
            // Logo Size
            wp.customize('az_logo_size', function(value) {
                value.bind(function(newval) {
                    // Header logo
                    var pnSize = Math.round(newval * 0.38);
                    var imgSize = Math.round(newval * 1.08);
                    $('#site-header .logo-fallback-icon').css({
                        'width': newval + 'px',
                        'height': newval + 'px'
                    });
                    $('#site-header .logo-fallback-icon span').css('font-size', pnSize + 'px');
                    $('#logo-wrap .custom-logo-link img, #logo-wrap img.custom-logo').css('height', imgSize + 'px');
                    
                    // Footer logo (69% of header size)
                    var ftLogoSize = Math.round(newval * 0.69);
                    var ftPnSize = Math.round(ftLogoSize * 0.31);
                    var ftImgSize = Math.round(newval * 0.85);
                    $('#site-footer .ft-logo-fallback-icon').css({
                        'width': ftLogoSize + 'px',
                        'height': ftLogoSize + 'px'
                    });
                    $('#site-footer .ft-logo-fallback-icon span').css('font-size', ftPnSize + 'px');
                    $('#ft-logo-wrap .custom-logo-link img, #ft-logo-wrap img.custom-logo').css('height', ftImgSize + 'px');
                });
            });
            
            // Logo Text Size
            wp.customize('az_logo_text_size', function(value) {
                value.bind(function(newval) {
                    // Header logo text
                    var subtitleSize = Math.round(newval * 0.55);
                    var width = Math.round(newval * 12);
                    $('#site-header .logo-fallback-text').css({
                        'font-size': newval + 'px',
                        'width': width + 'px'
                    });
                    
                    // Update subtitle using a style tag (::before can't be directly modified)
                    var styleId = 'az-logo-subtitle-preview';
                    $('#' + styleId).remove();
                    $('<style id="' + styleId + '">#site-header .logo-fallback-text::before { font-size: ' + subtitleSize + 'px !important; }</style>').appendTo('head');
                    
                    // Footer logo text (90% of header size)
                    var ftTextSize = Math.round(newval * 0.9);
                    $('#site-footer .ft-logo-fallback-text').css('font-size', ftTextSize + 'px');
                });
            });
        })(jQuery);
        </script>
        <?php
    });
} );



