<?php defined( 'ABSPATH' ) || exit; ?>
  <?php
  $az_home_sections = function_exists( 'az_home_category_sections_config' ) ? az_home_category_sections_config() : array();
  if ( empty( $az_home_sections ) ) {
      foreach ( array_slice( $az_categories, 0, 3 ) as $fallback_category ) {
          $az_home_sections[] = array(
              'category_id' => (int) $fallback_category->term_id,
              'title'       => $fallback_category->name,
              'tab_ids'     => array(),
              'visible'     => true,
          );
      }
  }
  $az_products_per_tab = $az_homepage_section['limit'];
  ?>
  <?php if ( '' !== $az_homepage_section['title'] ) : ?><h2 class="az-wrap"><?php echo esc_html( $az_homepage_section['title'] ); ?></h2><?php endif; ?>
  <?php foreach ( $az_home_sections as $index => $section ) : ?>
    <?php
    if ( empty( $section['visible'] ) ) {
        continue;
    }

    $section_category_id = $section['category_id'] ?? 0;
    $is_all_products     = 'all' === (string) $section_category_id;
    $category            = $is_all_products ? ( function_exists( 'az_home_all_products_term' ) ? az_home_all_products_term() : (object) array( 'term_id' => 'all', 'name' => 'Tất cả sản phẩm', 'slug' => 'tat-ca-san-pham' ) ) : get_term( (int) $section_category_id, 'product_cat' );
    if ( ! $category || is_wp_error( $category ) ) {
        continue;
    }

    $section_title = ! empty( $section['title'] ) ? $section['title'] : $category->name;
    $tab_terms     = array(
        (object) array(
            'term_id' => $category->term_id,
            'name'    => 'Tất cả',
            'slug'    => $category->slug,
        ),
    );

    foreach ( array_map( 'absint', (array) ( $section['tab_ids'] ?? array() ) ) as $tab_term_id ) {
        if ( ! $tab_term_id || (string) $tab_term_id === (string) $category->term_id ) {
            continue;
        }
        $tab_term = get_term( $tab_term_id, 'product_cat' );
        if ( $tab_term && ! is_wp_error( $tab_term ) ) {
            $tab_terms[] = $tab_term;
        }
    }

    $section_dom_id  = 'az-section-' . sanitize_key( (string) $category->term_id ) . '-' . absint( $index );
    $banner_img_url  = $is_all_products ? $az_theme_img : az_home_term_banner_url( $category, $az_theme_img );
    ?>
    <section class="az-category-band az-category-band-<?php echo esc_attr( $index + 1 ); ?>" data-az-product-tabs data-az-home-columns="<?php echo esc_attr( $az_homepage_section['columns'] ); ?>">
      <div class="az-wrap az-category-layout">
        <aside class="az-category-poster">
          <h2><?php echo esc_html( $section_title ); ?></h2>
          <img src="<?php echo esc_url( $banner_img_url ); ?>" alt="<?php echo esc_attr( $section_title ); ?>" loading="lazy">
        </aside>
        <div class="az-category-products">
          <header>
            <span></span>
            <nav aria-label="<?php echo esc_attr( $section_title ); ?>">
              <?php foreach ( $tab_terms as $tab_index => $tab_term ) : ?>
                <?php $panel_id = $section_dom_id . '-' . sanitize_key( (string) $tab_term->term_id ); ?>
                <button type="button" class="<?php echo 0 === (int) $tab_index ? 'is-active' : ''; ?>" data-az-tab-target="<?php echo esc_attr( $panel_id ); ?>"><?php echo esc_html( $tab_term->name ); ?></button>
              <?php endforeach; ?>
            </nav>
          </header>
          <?php foreach ( $tab_terms as $tab_index => $tab_term ) : ?>
            <?php
            $panel_id = $section_dom_id . '-' . sanitize_key( (string) $tab_term->term_id );
            az_home_render_product_panel( $panel_id, $tab_term->term_id, $az_products_per_tab, 0 === (int) $tab_index );
            ?>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endforeach; ?>
