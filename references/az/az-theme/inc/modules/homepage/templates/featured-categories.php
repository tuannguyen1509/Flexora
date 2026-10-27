<?php
defined( 'ABSPATH' ) || exit;
$az_featured_ids = array_values( array_unique( array_filter( array_map( 'absint', preg_split( '/[\r\n,]+/', (string) get_option( 'az_home_featured_category_ids', '' ) ) ) ) ) );
$az_term_args = array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'number' => $az_homepage_section['limit'] );
if ( $az_featured_ids ) {
    $az_term_args['include'] = array_slice( $az_featured_ids, 0, $az_homepage_section['limit'] );
    $az_term_args['orderby'] = 'include';
} else {
    $az_term_args['parent'] = 0;
    $az_term_args['orderby'] = 'name';
}
$az_featured_terms = get_terms( $az_term_args );
if ( is_wp_error( $az_featured_terms ) || ! $az_featured_terms ) { return; }
?>
<section class="az-wrap az-home-featured" data-az-home-columns="<?php echo esc_attr( $az_homepage_section['columns'] ); ?>">
    <?php if ( '' !== $az_homepage_section['title'] ) : ?><h2><?php echo esc_html( $az_homepage_section['title'] ); ?></h2><?php endif; ?>
    <div class="az-home-featured-grid">
        <?php foreach ( $az_featured_terms as $az_featured_term ) :
            $az_featured_url = get_term_link( $az_featured_term );
            if ( is_wp_error( $az_featured_url ) ) { continue; }
            ?>
            <a class="az-home-featured-item" href="<?php echo esc_url( $az_featured_url ); ?>">
                <img src="<?php echo esc_url( az_home_term_banner_url( $az_featured_term, $az_theme_img ) ); ?>" alt="" loading="lazy">
                <h3><?php echo esc_html( $az_featured_term->name ); ?></h3>
            </a>
        <?php endforeach; ?>
    </div>
</section>
