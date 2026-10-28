<?php
/**
 * 404 Template
 *
 * @package AZ_AI_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main id="az-main-content" tabindex="-1" class="az-main az-page-404 pt-32 pb-20" style="background:#f7f7f7;">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="glass-panel p-12 rounded-2xl">
            <div class="mb-8">
                <div class="w-24 h-24 mx-auto rounded-full flex items-center justify-center mb-6" style="background:var(--primary-color);">
                    <svg class="w-12 h-12" style="color:#111;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                    </svg>
                </div>
                <h1 class="text-6xl font-bold mb-4" style="color:#181818;">404</h1>
                <h2 class="text-3xl font-bold mb-4" style="color:#181818;"><?php esc_html_e( 'Page Not Found', 'az-ai-theme' ); ?></h2>
                <p class="text-lg mb-8" style="color:#333;">
                    <?php esc_html_e( 'Sorry, the page you are looking for does not exist or has been moved.', 'az-ai-theme' ); ?>
                </p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="px-8 py-3 rounded-full font-semibold shadow-lg transition-all" style="background:var(--primary-color);color:#fff;">
                    <?php esc_html_e( 'Go to Homepage', 'az-ai-theme' ); ?>
                </a>
                <a href="javascript:history.back()" class="px-8 py-3 rounded-full font-semibold transition-all" style="background:#111;color:#fff;border:1px solid #111;">
                    <?php esc_html_e( 'Go Back', 'az-ai-theme' ); ?>
                </a>
            </div>
            
            <div class="mt-12 pt-8" style="border-top:1px solid rgba(17,17,17,.12);">
                <h3 class="text-xl font-bold mb-4" style="color:#181818;"><?php esc_html_e( 'Try searching:', 'az-ai-theme' ); ?></h3>
                <?php get_search_form(); ?>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
