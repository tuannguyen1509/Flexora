<?php
/**
 * Search Form Template
 *
 * @package AZ_AI_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <div class="flex gap-2">
        <input type="search" class="rounded-lg px-4 py-2 text-sm w-full" style="background:#fff;border:1px solid rgba(17,17,17,.15);color:#181818;" placeholder="<?php echo esc_attr_x( 'Search...', 'placeholder', 'az-ai-theme' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
        <button type="submit" class="text-sm font-bold px-6 py-2 rounded-lg transition-colors" style="background:var(--primary-color);color:#fff;border:1px solid var(--primary-color);">
            <?php echo esc_html_x( 'Search', 'submit button', 'az-ai-theme' ); ?>
        </button>
    </div>
</form>
