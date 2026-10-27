<?php defined( 'ABSPATH' ) || exit; ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'flx-site' ); ?>>
<?php wp_body_open(); ?>
<header class="flx-site-header">
	<?php if ( has_custom_logo() ) { the_custom_logo(); } ?>
	<p class="flx-site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></p>
	<?php if ( has_nav_menu( 'flx-primary' ) ) : ?>
	<nav aria-label="<?php echo esc_attr__( 'Primary navigation', 'flexora' ); ?>">
		<?php wp_nav_menu( array( 'theme_location' => 'flx-primary', 'container' => false, 'fallback_cb' => false ) ); ?>
	</nav>
	<?php endif; ?>
</header>
