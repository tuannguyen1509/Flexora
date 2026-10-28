<?php
/**
 * Product admin pages — shared <head> styles + scripts
 * Usage: include get_template_directory() . '/template-parts/product/head.php';
 * Expects $page_title to be set before including.
 */
?>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html($page_title ?? 'Products'); ?> — <?php bloginfo('name'); ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<?php wp_head(); ?>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#0f172a;}
#wpadminbar{display:none!important;}
.glass-card{background:rgba(16,29,35,.6);backdrop-filter:blur(12px);border:1px solid rgba(37,175,244,.2);}
.sidebar-nav{background:rgba(15,23,42,.98);border-right:1px solid rgba(37,175,244,.1);}
.sidebar-item{transition:all .25s;color:#94a3b8;}
.sidebar-item:hover,.sidebar-item.active{background:rgba(37,175,244,.15);color:#25aff4;}
.az-input{background:rgba(15,23,42,.8);border:1px solid rgba(37,175,244,.2);color:#fff;border-radius:.75rem;padding:.6rem 1rem;width:100%;outline:none;transition:border-color .2s;}
.az-input:focus{border-color:#25aff4;}
.az-input option{background:#0f172a;}
.az-btn-primary{background:#25aff4;color:#0f172a;border-radius:.75rem;padding:.6rem 1.25rem;font-weight:600;font-size:.875rem;cursor:pointer;transition:opacity .2s;}
.az-btn-primary:hover{opacity:.85;}
.az-btn-ghost{border:1px solid rgba(255,255,255,.1);color:#94a3b8;border-radius:.75rem;padding:.6rem 1.25rem;font-size:.875rem;cursor:pointer;transition:all .2s;}
.az-btn-ghost:hover{color:#fff;}
@media(min-width:768px){.sidebar-nav{display:flex;flex-direction:column;width:260px;position:fixed;height:100%;}.main-wrap{margin-left:260px;}}
@media(max-width:767px){.sidebar-nav{display:none!important;}.main-wrap{margin-left:0!important;}}
</style>
