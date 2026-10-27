# API callsites (generated)

Every matched source call is listed, including dynamic arguments. Counts are static candidates; comments and nested embedded code require context review. Full arguments are in source-inventory.json.

## get_option (107)

| Location | First argument | Second argument |
| --- | --- | --- |
| [comments.php:178](../../comments.php#L178) | 'page_comments' |  |
| [comments.php:186](../../comments.php#L186) | 'page_comments' |  |
| [footer.php:11](../../footer.php#L11) | 'az_contact_phone' | '0823.73.76.79' |
| [footer.php:12](../../footer.php#L12) | 'az_contact_email' | 'togismart@gmail.com' |
| [footer.php:13](../../footer.php#L13) | 'az_contact_address' | '117B Hoàng Hoa Thám, Phường 13, Quận Tân Bình, Tp.HCM' |
| [footer.php:14](../../footer.php#L14) | 'az_contact_address_2' | '250 Trần Nhân Tông, Phường Nam Sơn, Quận Kiến An, Tp.Hải Phòng' |
| [footer.php:15](../../footer.php#L15) | 'az_contact_hours' | '8:00-18:00 hằng ngày' |
| [footer.php:17](../../footer.php#L17) | 'az_footer_compliance_title' | 'Chính sách công ty' |
| [footer.php:18](../../footer.php#L18) | 'az_footer_compliance_links' | $az_policy_default_links |
| [footer.php:59](../../footer.php#L59) | 'az_footer_social_facebook' | '#' |
| [footer.php:60](../../footer.php#L60) | 'az_footer_social_youtube' | '#' |
| [front-page.php:14](../../front-page.php#L14) | 'az_contact_phone' | '0823.73.76.79' |
| [front-page.php:23](../../front-page.php#L23) | 'az_home_' . $slot . '_banner_img' | $fallback_img |
| [front-page.php:24](../../front-page.php#L24) | 'az_home_' . $slot . '_banner_slides' | '' |
| [front-page.php:41](../../front-page.php#L41) | 'az_home_' . $slot . '_banner_alt' | $fallback_alt |
| [front-page.php:42](../../front-page.php#L42) | 'az_home_' . $slot . '_banner_link' | $fallback_link |
| [front-page.php:177](../../front-page.php#L177) | 'az_home_weekly_enabled' | '1' |
| [front-page.php:178](../../front-page.php#L178) | 'az_home_weekly_title' | 'Top bán chạy nhất tuần' |
| [front-page.php:179](../../front-page.php#L179) | 'az_home_weekly_count' | 6 |
| [front-page.php:180](../../front-page.php#L180) | 'az_home_weekly_category_id' | 0 |
| [front-page.php:181](../../front-page.php#L181) | 'az_home_weekly_orderby' | 'date' |
| [front-page.php:207](../../front-page.php#L207) | 'az_home_suggested_enabled' | '1' |
| [front-page.php:208](../../front-page.php#L208) | 'az_home_suggested_title' | 'Có thể bạn thích:' |
| [front-page.php:209](../../front-page.php#L209) | 'az_home_suggested_count' | 5 |
| [front-page.php:210](../../front-page.php#L210) | 'az_home_suggested_category_id' | 0 |
| [front-page.php:211](../../front-page.php#L211) | 'az_home_suggested_orderby' | 'rand' |
| [front-page.php:254](../../front-page.php#L254) | 'az_home_h1_title' | '' |
| [front-page.php:349](../../front-page.php#L349) | 'az_home_products_per_category' | 4 |
| [front-page.php:451](../../front-page.php#L451) | 'page_for_posts' |  |
| [header.php:11](../../header.php#L11) | 'az_contact_phone' | '0823.73.76.79' |
| [header.php:12](../../header.php#L12) | 'az_contact_phone_2' | '0823.73.76.79' |
| [inc/functions/admin-settings.php:228](../../inc/functions/admin-settings.php#L228) | 'az_lead_notify_emails' | '' |
| [inc/functions/admin-settings.php:231](../../inc/functions/admin-settings.php#L231) | 'az_contact_email' | get_option( 'admin_email' ) |
| [inc/functions/admin-settings.php:231](../../inc/functions/admin-settings.php#L231) | 'admin_email' |  |
| [inc/functions/admin-settings.php:235](../../inc/functions/admin-settings.php#L235) | 'admin_email' |  |
| [inc/functions/admin-settings.php:279](../../inc/functions/admin-settings.php#L279) | 'az_lead_smtp_enabled' | 0 |
| [inc/functions/admin-settings.php:283](../../inc/functions/admin-settings.php#L283) | 'az_lead_smtp_host' | '' |
| [inc/functions/admin-settings.php:284](../../inc/functions/admin-settings.php#L284) | 'az_lead_smtp_port' | 587 |
| [inc/functions/admin-settings.php:285](../../inc/functions/admin-settings.php#L285) | 'az_lead_smtp_username' | '' |
| [inc/functions/admin-settings.php:286](../../inc/functions/admin-settings.php#L286) | 'az_lead_smtp_password' | '' |
| [inc/functions/admin-settings.php:298](../../inc/functions/admin-settings.php#L298) | 'az_lead_smtp_debug' | 0 |
| [inc/functions/admin-settings.php:301](../../inc/functions/admin-settings.php#L301) | 'az_lead_smtp_encryption' | 'tls' |
| [inc/functions/admin-settings.php:306](../../inc/functions/admin-settings.php#L306) | 'az_lead_smtp_from_email' | '' |
| [inc/functions/admin-settings.php:310](../../inc/functions/admin-settings.php#L310) | 'az_lead_smtp_from_name' | get_bloginfo( 'name' ) |
| [inc/functions/admin-settings.php:449](../../inc/functions/admin-settings.php#L449) | 'az_lead_smtp_enabled' | 0 |
| [inc/functions/admin-settings.php:453](../../inc/functions/admin-settings.php#L453) | 'az_lead_notify_emails' | get_option( 'az_contact_email', get_option( 'admin_email' ) ) |
| [inc/functions/admin-settings.php:453](../../inc/functions/admin-settings.php#L453) | 'az_contact_email' | get_option( 'admin_email' ) |
| [inc/functions/admin-settings.php:453](../../inc/functions/admin-settings.php#L453) | 'admin_email' |  |
| [inc/functions/admin-settings.php:457](../../inc/functions/admin-settings.php#L457) | 'az_lead_smtp_host' | '' |
| [inc/functions/admin-settings.php:461](../../inc/functions/admin-settings.php#L461) | 'az_lead_smtp_port' | 587 |
| [inc/functions/admin-settings.php:465](../../inc/functions/admin-settings.php#L465) | 'az_lead_smtp_encryption' | 'tls' |
| [inc/functions/admin-settings.php:465](../../inc/functions/admin-settings.php#L465) | 'az_lead_smtp_encryption' | 'tls' |
| [inc/functions/admin-settings.php:465](../../inc/functions/admin-settings.php#L465) | 'az_lead_smtp_encryption' | 'tls' |
| [inc/functions/admin-settings.php:469](../../inc/functions/admin-settings.php#L469) | 'az_lead_smtp_username' | '' |
| [inc/functions/admin-settings.php:473](../../inc/functions/admin-settings.php#L473) | 'az_lead_smtp_password' | '' |
| [inc/functions/admin-settings.php:477](../../inc/functions/admin-settings.php#L477) | 'az_lead_smtp_from_email' | get_option( 'az_contact_email', get_option( 'admin_email' ) ) |
| [inc/functions/admin-settings.php:477](../../inc/functions/admin-settings.php#L477) | 'az_contact_email' | get_option( 'admin_email' ) |
| [inc/functions/admin-settings.php:477](../../inc/functions/admin-settings.php#L477) | 'admin_email' |  |
| [inc/functions/admin-settings.php:481](../../inc/functions/admin-settings.php#L481) | 'az_lead_smtp_from_name' | get_bloginfo( 'name' ) |
| [inc/functions/admin-settings.php:485](../../inc/functions/admin-settings.php#L485) | 'az_lead_smtp_debug' | 0 |
| [inc/functions/admin-settings.php:496](../../inc/functions/admin-settings.php#L496) | 'az_contact_email' | get_option( 'admin_email' ) |
| [inc/functions/admin-settings.php:496](../../inc/functions/admin-settings.php#L496) | 'admin_email' |  |
| [inc/functions/admin-settings.php:554](../../inc/functions/admin-settings.php#L554) | $option_name | '' |
| [inc/functions/admin-settings.php:575](../../inc/functions/admin-settings.php#L575) | $option_name | '' |
| [inc/functions/admin-settings.php:667](../../inc/functions/admin-settings.php#L667) | 'az_catalogue_lines' | az_catalogue_default_lines() |
| [inc/functions/contact-settings.php:112](../../inc/functions/contact-settings.php#L112) | $key | get_theme_mod( $key, $field[3] ?? '' ) |
| [inc/functions/contact-settings.php:176](../../inc/functions/contact-settings.php#L176) | $key | $value |
| [inc/functions/home-products.php:11](../../inc/functions/home-products.php#L11) | 'az_home_category_sections' | '' |
| [inc/functions/home-products.php:42](../../inc/functions/home-products.php#L42) | 'az_home_featured_category_ids' | '' |
| [inc/functions/home-products.php:131](../../inc/functions/home-products.php#L131) | 'az_home_featured_category_ids' | '' |
| [inc/functions/home-products.php:184](../../inc/functions/home-products.php#L184) | 'az_home_show_prices' | '0' |
| [inc/functions/home-products.php:190](../../inc/functions/home-products.php#L190) | 'az_contact_phone' | get_theme_mod( 'az_contact_phone', '' ) |
| [inc/functions/home-products.php:279](../../inc/functions/home-products.php#L279) | 'az_flash_sale_countdown_hours' | 3 |
| [inc/functions/home-products.php:384](../../inc/functions/home-products.php#L384) | 'az_home_category_batch_size' | 3 |
| [inc/functions/home-products.php:402](../../inc/functions/home-products.php#L402) | 'az_home_products_per_category' | 10 |
| [inc/functions/woocommerce.php:501](../../inc/functions/woocommerce.php#L501) | $option_name |  |
| [inc/license-api.php:427](../../inc/license-api.php#L427) | 'iaca_set' | array() |
| [page-catalogue.php:40](../../page-catalogue.php#L40) | 'az_catalogue_lines' | function_exists( 'az_catalogue_default_lines' ) ? az_catalogue_default_lines() : '' |
| [page-contact.php:8](../../page-contact.php#L8) | 'az_contact_company' | '' |
| [page-contact.php:9](../../page-contact.php#L9) | 'az_contact_phone' | '' |
| [page-contact.php:10](../../page-contact.php#L10) | 'az_contact_phone_2' | '' |
| [page-contact.php:11](../../page-contact.php#L11) | 'az_contact_email' | '' |
| [page-contact.php:12](../../page-contact.php#L12) | 'az_contact_address' | '' |
| [page-contact.php:13](../../page-contact.php#L13) | 'az_contact_address_2' | '' |
| [page-contact.php:14](../../page-contact.php#L14) | 'az_contact_hours' | '' |
| [page-contact.php:15](../../page-contact.php#L15) | 'az_contact_map_embed' | get_theme_mod('az_contact_map_embed','') |
| [page-contact.php:16](../../page-contact.php#L16) | 'az_footer_social_facebook' | '' |
| [page-contact.php:17](../../page-contact.php#L17) | 'az_footer_social_linkedin' | '' |
| [page-contact.php:18](../../page-contact.php#L18) | 'az_contact_title_vi' | 'Tư vấn & Hỗ trợ' |
| [page-contact.php:19](../../page-contact.php#L19) | 'az_contact_desc_vi' | 'Đội ngũ Công ty chúng tôi sẵn sàng tư vấn sản phẩm, báo giá và hỗ trợ kỹ thuật nhanh chóng.' |
| [page-gallery.php:9](../../page-gallery.php#L9) | 'az_moments_gallery' | [] |
| [single-product.php:128](../../single-product.php#L128) | 'az_product_contact_title' | '' |
| [single-product.php:129](../../single-product.php#L129) | 'az_product_contact_desc' | '' |
| [single-product.php:130](../../single-product.php#L130) | 'az_product_contact_name' | '' |
| [single-product.php:131](../../single-product.php#L131) | 'az_product_contact_phone' | '' |
| [single-product.php:132](../../single-product.php#L132) | 'az_product_contact_zalo' | '' |
| [single-product.php:135](../../single-product.php#L135) | 'az_contact_phone' | get_theme_mod( 'az_contact_phone', '' ) |
| [single-product.php:138](../../single-product.php#L138) | 'az_contact_zalo_url' | get_theme_mod( 'az_contact_zalo_url', '' ) |
| [single.php:186](../../single.php#L186) | 'az_post_author_name' | '' |
| [single.php:187](../../single.php#L187) | 'az_post_author_desc' | '' |
| [single.php:188](../../single.php#L188) | 'az_post_author_url' | '' |
| [template-parts/sidebar-intro.php:16](../../template-parts/sidebar-intro.php#L16) | 'cp_intro_text' | "Chuyên Gia Cà Phê là nơi chia sẻ những kiến thức, trải nghiệm và giải pháp thực tế trong hành trình tạo ra những ly cà phê chất lượng.\nTừ việc lựa chọn hạt cà phê phù hợp, kỹ thuật rang chuẩn mực đến tối ưu chiết xuất trên máy pha cà phê, mỗi yếu t |
| [template-parts/sidebar-intro.php:21](../../template-parts/sidebar-intro.php#L21) | 'cp_intro_gallery' | '' |
| [template-parts/sidebar-intro.php:83](../../template-parts/sidebar-intro.php#L83) | 'cp_badge_role' | 'CEO & Founder CTY GU CÀ PHÊ VIỆT' |
| [template-parts/sidebar-intro.php:89](../../template-parts/sidebar-intro.php#L89) | 'cp_badge_city' | 'Sống tại TP. Hồ Chí Minh' |
| [template-parts/sidebar-intro.php:95](../../template-parts/sidebar-intro.php#L95) | 'cp_badge_hometown' | 'Đến từ BÌNH ĐỊNH' |
| [template-parts/sidebar-intro.php:117](../../template-parts/sidebar-intro.php#L117) | 'cp_intro_link' | home_url('/gioi-thieu') |

## update_option (4)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/contact-settings.php:175](../../inc/functions/contact-settings.php#L175) | $key | $value |
| [inc/functions/woocommerce.php:524](../../inc/functions/woocommerce.php#L524) | $option_name | (int) $page_id |
| [inc/license-api.php:429](../../inc/license-api.php#L429) | 'iaca_set' | $iaca_set |
| [page-catalogue.php:19](../../page-catalogue.php#L19) | 'az_catalogue_lines' | sanitize_textarea_field( wp_unslash( $_POST['az_catalogue_lines'] ?? '' ) ) |

## add_option (0)

No source call found.

## delete_option (0)

No source call found.

## get_theme_mod (12)

| Location | First argument | Second argument |
| --- | --- | --- |
| [archive.php:34](../../archive.php#L34) | 'custom_logo' |  |
| [header.php:49](../../header.php#L49) | 'custom_logo' |  |
| [inc/functions/contact-settings.php:112](../../inc/functions/contact-settings.php#L112) | $key | $field[3] ?? '' |
| [inc/functions/customizer.php:19](../../inc/functions/customizer.php#L19) | 'az_logo_size' | 52 |
| [inc/functions/customizer.php:20](../../inc/functions/customizer.php#L20) | 'az_logo_text_size' | 16 |
| [inc/functions/home-products.php:172](../../inc/functions/home-products.php#L172) | 'custom_logo' |  |
| [inc/functions/home-products.php:190](../../inc/functions/home-products.php#L190) | 'az_contact_phone' | '' |
| [inc/functions/theme-setup.php:177](../../inc/functions/theme-setup.php#L177) | $key . '_en' | $default_en |
| [inc/functions/theme-setup.php:180](../../inc/functions/theme-setup.php#L180) | $key | $default_vi |
| [page-contact.php:15](../../page-contact.php#L15) | 'az_contact_map_embed' | '' |
| [single-product.php:135](../../single-product.php#L135) | 'az_contact_phone' | '' |
| [single-product.php:138](../../single-product.php#L138) | 'az_contact_zalo_url' | '' |

## set_theme_mod (1)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/contact-settings.php:176](../../inc/functions/contact-settings.php#L176) | $key | get_option( $key, $value ) |

## remove_theme_mod (0)

No source call found.

## register_setting (57)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/admin-settings.php:64](../../inc/functions/admin-settings.php#L64) | 'az_contact_group' | 'az_contact_company' |
| [inc/functions/admin-settings.php:65](../../inc/functions/admin-settings.php#L65) | 'az_contact_group' | 'az_contact_person' |
| [inc/functions/admin-settings.php:66](../../inc/functions/admin-settings.php#L66) | 'az_contact_group' | 'az_contact_phone' |
| [inc/functions/admin-settings.php:67](../../inc/functions/admin-settings.php#L67) | 'az_contact_group' | 'az_contact_phone_2' |
| [inc/functions/admin-settings.php:68](../../inc/functions/admin-settings.php#L68) | 'az_contact_group' | 'az_contact_email' |
| [inc/functions/admin-settings.php:69](../../inc/functions/admin-settings.php#L69) | 'az_contact_group' | 'az_contact_address' |
| [inc/functions/admin-settings.php:70](../../inc/functions/admin-settings.php#L70) | 'az_contact_group' | 'az_contact_address_2' |
| [inc/functions/admin-settings.php:71](../../inc/functions/admin-settings.php#L71) | 'az_contact_group' | 'az_contact_hours' |
| [inc/functions/admin-settings.php:72](../../inc/functions/admin-settings.php#L72) | 'az_contact_group' | 'az_contact_map_embed' |
| [inc/functions/admin-settings.php:73](../../inc/functions/admin-settings.php#L73) | 'az_contact_group' | 'az_footer_contact_title' |
| [inc/functions/admin-settings.php:74](../../inc/functions/admin-settings.php#L74) | 'az_contact_group' | 'az_footer_address_label' |
| [inc/functions/admin-settings.php:75](../../inc/functions/admin-settings.php#L75) | 'az_contact_group' | 'az_footer_phone_label' |
| [inc/functions/admin-settings.php:76](../../inc/functions/admin-settings.php#L76) | 'az_contact_group' | 'az_footer_phone_name' |
| [inc/functions/admin-settings.php:77](../../inc/functions/admin-settings.php#L77) | 'az_contact_group' | 'az_footer_phone_2_name' |
| [inc/functions/admin-settings.php:78](../../inc/functions/admin-settings.php#L78) | 'az_contact_group' | 'az_footer_email_label' |
| [inc/functions/admin-settings.php:79](../../inc/functions/admin-settings.php#L79) | 'az_contact_group' | 'az_footer_hours_label' |
| [inc/functions/admin-settings.php:80](../../inc/functions/admin-settings.php#L80) | 'az_contact_group' | 'az_footer_map_title' |
| [inc/functions/admin-settings.php:81](../../inc/functions/admin-settings.php#L81) | 'az_contact_group' | 'az_product_contact_title' |
| [inc/functions/admin-settings.php:82](../../inc/functions/admin-settings.php#L82) | 'az_contact_group' | 'az_product_contact_desc' |
| [inc/functions/admin-settings.php:83](../../inc/functions/admin-settings.php#L83) | 'az_contact_group' | 'az_product_contact_name' |
| [inc/functions/admin-settings.php:84](../../inc/functions/admin-settings.php#L84) | 'az_contact_group' | 'az_product_contact_phone' |
| [inc/functions/admin-settings.php:85](../../inc/functions/admin-settings.php#L85) | 'az_contact_group' | 'az_product_contact_zalo' |
| [inc/functions/admin-settings.php:86](../../inc/functions/admin-settings.php#L86) | 'az_contact_group' | 'az_post_author_name' |
| [inc/functions/admin-settings.php:87](../../inc/functions/admin-settings.php#L87) | 'az_contact_group' | 'az_post_author_desc' |
| [inc/functions/admin-settings.php:88](../../inc/functions/admin-settings.php#L88) | 'az_contact_group' | 'az_post_author_url' |
| [inc/functions/admin-settings.php:91](../../inc/functions/admin-settings.php#L91) | 'az_contact_group' | 'az_footer_brand_desc' |
| [inc/functions/admin-settings.php:92](../../inc/functions/admin-settings.php#L92) | 'az_contact_group' | 'az_footer_social_facebook' |
| [inc/functions/admin-settings.php:93](../../inc/functions/admin-settings.php#L93) | 'az_contact_group' | 'az_footer_social_linkedin' |
| [inc/functions/admin-settings.php:94](../../inc/functions/admin-settings.php#L94) | 'az_contact_group' | 'az_footer_social_youtube' |
| [inc/functions/admin-settings.php:95](../../inc/functions/admin-settings.php#L95) | 'az_contact_group' | 'az_footer_social_tiktok' |
| [inc/functions/admin-settings.php:96](../../inc/functions/admin-settings.php#L96) | 'az_contact_group' | 'az_contact_messenger_url' |
| [inc/functions/admin-settings.php:97](../../inc/functions/admin-settings.php#L97) | 'az_contact_group' | 'az_contact_zalo_url' |
| [inc/functions/admin-settings.php:98](../../inc/functions/admin-settings.php#L98) | 'az_contact_group' | 'az_contact_zalo_url_2' |
| [inc/functions/admin-settings.php:99](../../inc/functions/admin-settings.php#L99) | 'az_contact_group' | 'az_contact_zalo_url_3' |
| [inc/functions/admin-settings.php:100](../../inc/functions/admin-settings.php#L100) | 'az_contact_group' | 'az_footer_quicklinks_title' |
| [inc/functions/admin-settings.php:101](../../inc/functions/admin-settings.php#L101) | 'az_contact_group' | 'az_footer_quicklinks' |
| [inc/functions/admin-settings.php:102](../../inc/functions/admin-settings.php#L102) | 'az_contact_group' | 'az_footer_compliance_title' |
| [inc/functions/admin-settings.php:103](../../inc/functions/admin-settings.php#L103) | 'az_contact_group' | 'az_footer_compliance_links' |
| [inc/functions/admin-settings.php:105](../../inc/functions/admin-settings.php#L105) | 'az_contact_group' | 'az_advisor_' . $advisor_i . '_name' |
| [inc/functions/admin-settings.php:106](../../inc/functions/admin-settings.php#L106) | 'az_contact_group' | 'az_advisor_' . $advisor_i . '_role' |
| [inc/functions/admin-settings.php:107](../../inc/functions/admin-settings.php#L107) | 'az_contact_group' | 'az_advisor_' . $advisor_i . '_phone' |
| [inc/functions/admin-settings.php:108](../../inc/functions/admin-settings.php#L108) | 'az_contact_group' | 'az_advisor_' . $advisor_i . '_zalo' |
| [inc/functions/admin-settings.php:109](../../inc/functions/admin-settings.php#L109) | 'az_contact_group' | 'az_advisor_' . $advisor_i . '_image' |
| [inc/functions/admin-settings.php:112](../../inc/functions/admin-settings.php#L112) | 'az_hfc_group' | 'az_hfc_header_scripts' |
| [inc/functions/admin-settings.php:113](../../inc/functions/admin-settings.php#L113) | 'az_hfc_group' | 'az_hfc_body_top_scripts' |
| [inc/functions/admin-settings.php:114](../../inc/functions/admin-settings.php#L114) | 'az_hfc_group' | 'az_hfc_body_bottom_scripts' |
| [inc/functions/admin-settings.php:115](../../inc/functions/admin-settings.php#L115) | 'az_hfc_group' | 'az_hfc_footer_scripts' |
| [inc/functions/admin-settings.php:117](../../inc/functions/admin-settings.php#L117) | 'az_lead_smtp_group' | 'az_lead_smtp_enabled' |
| [inc/functions/admin-settings.php:118](../../inc/functions/admin-settings.php#L118) | 'az_lead_smtp_group' | 'az_lead_notify_emails' |
| [inc/functions/admin-settings.php:119](../../inc/functions/admin-settings.php#L119) | 'az_lead_smtp_group' | 'az_lead_smtp_host' |
| [inc/functions/admin-settings.php:120](../../inc/functions/admin-settings.php#L120) | 'az_lead_smtp_group' | 'az_lead_smtp_port' |
| [inc/functions/admin-settings.php:121](../../inc/functions/admin-settings.php#L121) | 'az_lead_smtp_group' | 'az_lead_smtp_encryption' |
| [inc/functions/admin-settings.php:122](../../inc/functions/admin-settings.php#L122) | 'az_lead_smtp_group' | 'az_lead_smtp_username' |
| [inc/functions/admin-settings.php:123](../../inc/functions/admin-settings.php#L123) | 'az_lead_smtp_group' | 'az_lead_smtp_password' |
| [inc/functions/admin-settings.php:124](../../inc/functions/admin-settings.php#L124) | 'az_lead_smtp_group' | 'az_lead_smtp_from_email' |
| [inc/functions/admin-settings.php:125](../../inc/functions/admin-settings.php#L125) | 'az_lead_smtp_group' | 'az_lead_smtp_from_name' |
| [inc/functions/admin-settings.php:126](../../inc/functions/admin-settings.php#L126) | 'az_lead_smtp_group' | 'az_lead_smtp_debug' |

## add_action (91)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/admin-settings.php:23](../../inc/functions/admin-settings.php#L23) | 'admin_menu' | function() {     add_menu_page(         'Thông tin liên hệ',         'Thông tin liên hệ',         'manage_options',         'az-contact-settings',         'az_contact_settings_page',         'dashicons-phone',         61     );      add_submenu_page( |
| [inc/functions/admin-settings.php:62](../../inc/functions/admin-settings.php#L62) | 'admin_init' | function() {     // ── Thông tin liên hệ cơ bản (đang dùng trong footer) ──     register_setting( 'az_contact_group', 'az_contact_company', [ 'sanitize_callback' => 'sanitize_text_field' ] );     register_setting( 'az_contact_group', 'az_contact_pers |
| [inc/functions/admin-settings.php:203](../../inc/functions/admin-settings.php#L203) | 'after_switch_theme' | 'az_create_lead_table' |
| [inc/functions/admin-settings.php:204](../../inc/functions/admin-settings.php#L204) | 'admin_init' | 'az_create_lead_table' |
| [inc/functions/admin-settings.php:278](../../inc/functions/admin-settings.php#L278) | 'phpmailer_init' | function( $phpmailer ) {     if ( ! get_option( 'az_lead_smtp_enabled', 0 ) ) {         return;     }      $host = trim( (string) get_option( 'az_lead_smtp_host', '' ) );     $port = (int) get_option( 'az_lead_smtp_port', 587 );     $user = trim( (st |
| [inc/functions/admin-settings.php:316](../../inc/functions/admin-settings.php#L316) | 'wp_mail_failed' | function( $error ) {     if ( is_wp_error( $error ) ) {         error_log( '[AZ Lead Mail] ' . $error->get_error_message() );     } } |
| [inc/functions/admin-settings.php:583](../../inc/functions/admin-settings.php#L583) | 'wp_head' | function() {     az_hfc_print_code( 'az_hfc_header_scripts' ); } |
| [inc/functions/admin-settings.php:587](../../inc/functions/admin-settings.php#L587) | 'wp_body_open' | function() {     az_hfc_print_code( 'az_hfc_body_top_scripts' ); } |
| [inc/functions/admin-settings.php:591](../../inc/functions/admin-settings.php#L591) | 'wp_footer' | function() {     az_hfc_print_code( 'az_hfc_body_bottom_scripts' );     az_hfc_print_code( 'az_hfc_footer_scripts' ); } |
| [inc/functions/admin-settings.php:660](../../inc/functions/admin-settings.php#L660) | 'admin_init' | 'wv_seed_about_template_content' |
| [inc/functions/category-images.php:16](../../inc/functions/category-images.php#L16) | 'category_add_form_fields' | 'cp_cat_image_add_field' |
| [inc/functions/category-images.php:36](../../inc/functions/category-images.php#L36) | 'category_edit_form_fields' | 'cp_cat_image_edit_field' |
| [inc/functions/category-images.php:94](../../inc/functions/category-images.php#L94) | 'admin_enqueue_scripts' | function( $hook ) {     if ( in_array( $hook, [ 'edit-tags.php', 'term.php' ] ) &&          isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === 'category' ) {         wp_enqueue_media();     } } |
| [inc/functions/category-images.php:104](../../inc/functions/category-images.php#L104) | 'created_category' | 'cp_cat_image_save' |
| [inc/functions/category-images.php:105](../../inc/functions/category-images.php#L105) | 'edited_category' | 'cp_cat_image_save' |
| [inc/functions/contact-form.php:48](../../inc/functions/contact-form.php#L48) | 'wp_ajax_az_contact_refresh_captcha' | 'az_contact_refresh_captcha' |
| [inc/functions/contact-form.php:49](../../inc/functions/contact-form.php#L49) | 'wp_ajax_nopriv_az_contact_refresh_captcha' | 'az_contact_refresh_captcha' |
| [inc/functions/contact-form.php:58](../../inc/functions/contact-form.php#L58) | 'wp_ajax_az_contact_form_submit' | 'az_handle_contact_form' |
| [inc/functions/contact-form.php:59](../../inc/functions/contact-form.php#L59) | 'wp_ajax_nopriv_az_contact_form_submit' | 'az_handle_contact_form' |
| [inc/functions/contact-settings.php:181](../../inc/functions/contact-settings.php#L181) | 'admin_post_az_save_contact_settings' | 'az_save_contact_settings' |
| [inc/functions/contact-settings.php:189](../../inc/functions/contact-settings.php#L189) | 'admin_enqueue_scripts' | 'az_contact_settings_assets' |
| [inc/functions/content-media.php:93](../../inc/functions/content-media.php#L93) | 'save_post' | 'az_localize_remote_images_on_save' |
| [inc/functions/content-media.php:107](../../inc/functions/content-media.php#L107) | 'save_post' | 'az_localize_remote_images_on_save' |
| [inc/functions/customizer.php:18](../../inc/functions/customizer.php#L18) | 'wp_head' | function() {     $logo_size = get_theme_mod( 'az_logo_size', 52 );     $logo_text_size = get_theme_mod( 'az_logo_text_size', 16 );     $logo_pn_size = round( $logo_size * 0.38 ); // PN text = 38% of icon size     $logo_subtitle_size = round( $logo_te |
| [inc/functions/customizer.php:100](../../inc/functions/customizer.php#L100) | 'customize_preview_init', function() { |  |
| [inc/functions/customizer.php:101](../../inc/functions/customizer.php#L101) | 'wp_footer', function() { |  |
| [inc/functions/home-products.php:244](../../inc/functions/home-products.php#L244) | 'pre_get_posts' | 'az_storefront_limit_shop_archive_query' |
| [inc/functions/home-products.php:374](../../inc/functions/home-products.php#L374) | 'wp_ajax_az_home_load_categories' | 'az_home_load_categories_ajax' |
| [inc/functions/home-products.php:375](../../inc/functions/home-products.php#L375) | 'wp_ajax_nopriv_az_home_load_categories' | 'az_home_load_categories_ajax' |
| [inc/functions/integrations-access.php:12](../../inc/functions/integrations-access.php#L12) | 'login_head' | 'login_css' |
| [inc/functions/integrations-access.php:24](../../inc/functions/integrations-access.php#L24) | 'az_check_expired_licenses' | 'az_auto_expire_licenses' |
| [inc/functions/integrations-access.php:75](../../inc/functions/integrations-access.php#L75) | 'wp_enqueue_scripts' | 'az_enqueue_pricing_redirect_js' |
| [inc/functions/integrations-access.php:81](../../inc/functions/integrations-access.php#L81) | 'after_setup_theme' | 'az_hide_admin_bar_for_non_admins' |
| [inc/functions/integrations-access.php:93](../../inc/functions/integrations-access.php#L93) | 'get_header' | 'az_remove_admin_bar_css' |
| [inc/functions/integrations-access.php:104](../../inc/functions/integrations-access.php#L104) | 'admin_init' | 'az_disable_admin_bar_profile_option' |
| [inc/functions/integrations-access.php:115](../../inc/functions/integrations-access.php#L115) | 'admin_init' | 'az_redirect_non_admin_from_dashboard' |
| [inc/functions/integrations-access.php:133](../../inc/functions/integrations-access.php#L133) | 'template_redirect' | 'az_handle_plugin_download' |
| [inc/functions/licenses.php:44](../../inc/functions/licenses.php#L44) | 'init' | 'az_register_license_post_type' |
| [inc/functions/licenses.php:59](../../inc/functions/licenses.php#L59) | 'add_meta_boxes' | 'az_add_license_meta_boxes' |
| [inc/functions/licenses.php:79](../../inc/functions/licenses.php#L79) | 'manage_license_posts_custom_column' | 'az_license_column_content' |
| [inc/functions/licenses.php:302](../../inc/functions/licenses.php#L302) | 'save_post_license' | 'az_save_license_details' |
| [inc/functions/menu-icons.php:61](../../inc/functions/menu-icons.php#L61) | 'wp_update_nav_menu_item' | 'cp_save_menu_item_custom_fields' |
| [inc/functions/menu-icons.php:79](../../inc/functions/menu-icons.php#L79) | 'admin_enqueue_scripts' | function( $hook ) {     if ( $hook !== 'nav-menus.php' ) return;     wp_enqueue_media();     // Inline JS thay vì file riêng     add_action( 'admin_footer', 'cp_menu_icon_picker_js' ); } |
| [inc/functions/menu-icons.php:83](../../inc/functions/menu-icons.php#L83) | 'admin_footer' | 'cp_menu_icon_picker_js' |
| [inc/functions/menu-icons.php:138](../../inc/functions/menu-icons.php#L138) | 'admin_head-nav-menus.php' | function() { ?> <style> .cp-menu-fields { background:#fdf8f2; border:1px solid #d8e6f7; border-radius:6px; padding:10px 12px !important; margin:8px 0 4px !important; } .cp-icon-select:hover { background:var(--primary-color) !important; } </style> <?p |
| [inc/functions/pricing.php:44](../../inc/functions/pricing.php#L44) | 'init' | 'az_ai_register_pricing_cpt' |
| [inc/functions/pricing.php:47](../../inc/functions/pricing.php#L47) | 'customize_register' | function( $wp_customize ) {     // Reuse the built-in "Site Identity" section     $wp_customize->add_setting( 'az_site_name_en', array(         'default'           => '',         'sanitize_callback' => 'sanitize_text_field',         'transport'       |
| [inc/functions/pricing.php:114](../../inc/functions/pricing.php#L114) | 'add_meta_boxes' | 'az_ai_add_pricing_meta_boxes' |
| [inc/functions/pricing.php:261](../../inc/functions/pricing.php#L261) | 'save_post_pricing' | 'az_ai_save_pricing_meta' |
| [inc/functions/pricing.php:276](../../inc/functions/pricing.php#L276) | 'add_meta_boxes' | 'az_ai_add_testimonial_meta_boxes' |
| [inc/functions/pricing.php:335](../../inc/functions/pricing.php#L335) | 'save_post_testimonial' | 'az_ai_save_testimonial_meta' |
| [inc/functions/product-attributes-pricing.php:39](../../inc/functions/product-attributes-pricing.php#L39) | 'admin_enqueue_scripts', function( $hook ) { |  |
| [inc/functions/product-attributes-pricing.php:63](../../inc/functions/product-attributes-pricing.php#L63) | 'admin_footer' | 'az_wc_checkbox_attribute_admin_js' |
| [inc/functions/product-attributes-pricing.php:64](../../inc/functions/product-attributes-pricing.php#L64) | 'admin_head' | 'az_wc_checkbox_attribute_admin_css' |
| [inc/functions/product-attributes-pricing.php:176](../../inc/functions/product-attributes-pricing.php#L176) | 'product_cat_add_form_fields' | 'az_product_cat_rate_field_add' |
| [inc/functions/product-attributes-pricing.php:192](../../inc/functions/product-attributes-pricing.php#L192) | 'product_cat_edit_form_fields' | 'az_product_cat_rate_field_edit' |
| [inc/functions/product-attributes-pricing.php:211](../../inc/functions/product-attributes-pricing.php#L211) | 'created_product_cat' | 'az_save_product_cat_price_rate' |
| [inc/functions/product-attributes-pricing.php:212](../../inc/functions/product-attributes-pricing.php#L212) | 'edited_product_cat' | 'az_save_product_cat_price_rate' |
| [inc/functions/product-fields.php:20](../../inc/functions/product-fields.php#L20) | 'add_meta_boxes_product' | 'az_product_extra_info_meta_box' |
| [inc/functions/product-fields.php:111](../../inc/functions/product-fields.php#L111) | 'save_post_product' | 'az_save_product_extra_info_meta' |
| [inc/functions/product-videos.php:112](../../inc/functions/product-videos.php#L112) | 'add_meta_boxes_product' | 'az_add_product_video_metabox' |
| [inc/functions/product-videos.php:206](../../inc/functions/product-videos.php#L206) | 'save_post_product' | 'az_save_product_video_urls' |
| [inc/functions/security.php:8](../../inc/functions/security.php#L8) | 'send_headers' | function() {     if ( is_admin() \|\| headers_sent() ) {         return;     }      header_remove( 'X-Powered-By' );     header( 'X-Content-Type-Options: nosniff' );     header( 'X-Frame-Options: SAMEORIGIN' );     header( 'Referrer-Policy: strict-orig |
| [inc/functions/term-language.php:31](../../inc/functions/term-language.php#L31) | 'category_edit_form_fields' | 'az_category_en_field' |
| [inc/functions/term-language.php:32](../../inc/functions/term-language.php#L32) | 'post_tag_edit_form_fields' | 'az_category_en_field' |
| [inc/functions/term-language.php:45](../../inc/functions/term-language.php#L45) | 'category_add_form_fields' | 'az_category_en_add_field' |
| [inc/functions/term-language.php:46](../../inc/functions/term-language.php#L46) | 'post_tag_add_form_fields' | 'az_category_en_add_field' |
| [inc/functions/term-language.php:59](../../inc/functions/term-language.php#L59) | 'edited_category' | 'az_save_category_en_field' |
| [inc/functions/term-language.php:60](../../inc/functions/term-language.php#L60) | 'create_category' | 'az_save_category_en_field' |
| [inc/functions/term-language.php:61](../../inc/functions/term-language.php#L61) | 'edited_post_tag' | 'az_save_category_en_field' |
| [inc/functions/term-language.php:62](../../inc/functions/term-language.php#L62) | 'create_post_tag' | 'az_save_category_en_field' |
| [inc/functions/term-language.php:65](../../inc/functions/term-language.php#L65) | 'after_switch_theme' | function() { flush_rewrite_rules(); } |
| [inc/functions/theme-setup.php:95](../../inc/functions/theme-setup.php#L95) | 'after_setup_theme' | 'az_ai_theme_setup' |
| [inc/functions/theme-setup.php:135](../../inc/functions/theme-setup.php#L135) | 'init' | 'az_register_product_warehouse_taxonomy' |
| [inc/functions/theme-setup.php:240](../../inc/functions/theme-setup.php#L240) | 'wp_enqueue_scripts' | 'az_ai_theme_scripts' |
| [inc/functions/theme-setup.php:309](../../inc/functions/theme-setup.php#L309) | 'wp_head' | function() {     ?>     <style id="az-site-font-option">:root{--az-site-font:<?php echo esc_html( az_site_font_stack() ); ?>}body.az-storefront{font-family:var(--az-site-font)!important}</style>     <?php } |
| [inc/functions/theme-setup.php:400](../../inc/functions/theme-setup.php#L400) | 'wp_head' | function() { ?> <style> /* ── Desktop nav items ── */ .az-menu-item { position:relative; display:flex; align-items:center; } .az-menu-link {     display:flex; align-items:center; gap:.3rem;     color:#d1d5db; font-weight:500; font-size:.9rem;     tex |
| [inc/functions/woocommerce.php:239](../../inc/functions/woocommerce.php#L239) | 'template_redirect' | 'az_translate_stored_woocommerce_notices' |
| [inc/functions/woocommerce.php:368](../../inc/functions/woocommerce.php#L368) | 'template_redirect' | 'az_track_recently_viewed_product' |
| [inc/functions/woocommerce.php:370](../../inc/functions/woocommerce.php#L370) | 'wp_footer' | function() {     if ( ! function_exists( 'is_product' ) \|\| ! is_product() ) {         return;     }     ?>     <script>     (function($){         function chooseFirstVariationDefaults($form){             var changed = false;              $form.find(' |
| [inc/functions/woocommerce.php:529](../../inc/functions/woocommerce.php#L529) | 'admin_init' | 'az_ensure_woocommerce_pages' |
| [inc/functions/woocommerce.php:530](../../inc/functions/woocommerce.php#L530) | 'after_switch_theme' | 'az_ensure_woocommerce_pages' |
| [inc/functions/woocommerce.php:597](../../inc/functions/woocommerce.php#L597) | 'pre_get_posts' | 'az_filter_woocommerce_products_query' |
| [inc/gioi-thieu-page-fields.php:200](../../inc/gioi-thieu-page-fields.php#L200) | 'add_meta_boxes_page' | function( $post ) {     if ( az_gioi_thieu_is_page( $post ) ) {         add_meta_box( 'az_gioi_thieu_content', 'Nội dung trang Giới thiệu', 'az_gioi_thieu_meta_box', 'page', 'normal', 'high' );     } } |
| [inc/gioi-thieu-page-fields.php:206](../../inc/gioi-thieu-page-fields.php#L206) | 'admin_enqueue_scripts' | function( $hook ) {     if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {         wp_enqueue_media();         wp_enqueue_editor();     } } |
| [inc/gioi-thieu-page-fields.php:523](../../inc/gioi-thieu-page-fields.php#L523) | 'save_post_page' | function( $post_id, $post ) {     if ( ! isset( $_POST['az_gioi_thieu_nonce'] ) \|\| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['az_gioi_thieu_nonce'] ) ), 'az_gioi_thieu_save' ) ) {         return;     }     if ( defined( 'DOING_AUT |
| [inc/gioi-thieu-page-fields.php:581](../../inc/gioi-thieu-page-fields.php#L581) | 'admin_head-post.php' | 'az_gioi_thieu_hide_default_editor' |
| [inc/gioi-thieu-page-fields.php:582](../../inc/gioi-thieu-page-fields.php#L582) | 'admin_head-post-new.php' | 'az_gioi_thieu_hide_default_editor' |
| [inc/license-api.php:76](../../inc/license-api.php#L76) | 'rest_api_init' | 'az_register_license_api_endpoints' |
| [inc/license-api.php:394](../../inc/license-api.php#L394) | 'wp_login' | 'az_auto_sync_license_to_plugin' |
| [inc/prompt-execution-api.php:18](../../inc/prompt-execution-api.php#L18) | 'rest_api_init' | 'az_register_prompt_api_endpoints' |

## add_filter (38)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/admin-settings.php:9](../../inc/functions/admin-settings.php#L9) | 'use_block_editor_for_post' | '__return_false' |
| [inc/functions/admin-settings.php:15](../../inc/functions/admin-settings.php#L15) | 'login_headerurl' | 'wpc_url_login' |
| [inc/functions/admin-settings.php:18](../../inc/functions/admin-settings.php#L18) | 'admin_footer_text' | function() {     return 'Bởi <a href="https://tndigi.com" target="_blank" rel="noopener" style="color:#25aff4;text-decoration:none;font-weight:600;">TN Digi Solution</a>'; } |
| [inc/functions/content-media.php:119](../../inc/functions/content-media.php#L119) | 'content_save_pre' | 'az_strip_tinymce_selection_markers' |
| [inc/functions/content-media.php:142](../../inc/functions/content-media.php#L142) | 'the_content' | 'az_group_consecutive_content_images' |
| [inc/functions/content-media.php:143](../../inc/functions/content-media.php#L143) | 'woocommerce_short_description' | 'az_group_consecutive_content_images' |
| [inc/functions/content-media.php:153](../../inc/functions/content-media.php#L153) | 'tiny_mce_before_init' | 'az_tinymce_emoji_content_style' |
| [inc/functions/home-products.php:214](../../inc/functions/home-products.php#L214) | 'woocommerce_get_price_html' | function( $price_html ) {         if ( is_admin() && ! wp_doing_ajax() ) {             return $price_html;         }          return az_storefront_show_prices() ? $price_html : az_storefront_contact_price_html();     } |
| [inc/functions/home-products.php:231](../../inc/functions/home-products.php#L231) | 'loop_shop_per_page' | 'az_storefront_shop_products_per_page' |
| [inc/functions/licenses.php:64](../../inc/functions/licenses.php#L64) | 'manage_license_posts_columns' | 'az_license_columns' |
| [inc/functions/menu-icons.php:13](../../inc/functions/menu-icons.php#L13) | 'wp_nav_menu_item_custom_fields' | 'cp_menu_item_custom_fields' |
| [inc/functions/menu-icons.php:129](../../inc/functions/menu-icons.php#L129) | 'nav_menu_item_title' | 'cp_menu_item_add_icon' |
| [inc/functions/product-attributes-pricing.php:11](../../inc/functions/product-attributes-pricing.php#L11) | 'pre_term_description' | 'wp_kses_post' |
| [inc/functions/product-attributes-pricing.php:12](../../inc/functions/product-attributes-pricing.php#L12) | 'term_description' | 'wp_kses_post' |
| [inc/functions/product-attributes-pricing.php:20](../../inc/functions/product-attributes-pricing.php#L20) | 'product_attributes_type_selector' | 'az_wc_attribute_type_selector' |
| [inc/functions/product-attributes-pricing.php:290](../../inc/functions/product-attributes-pricing.php#L290) | 'woocommerce_product_get_price' | 'az_apply_product_category_price_rate' |
| [inc/functions/product-attributes-pricing.php:291](../../inc/functions/product-attributes-pricing.php#L291) | 'woocommerce_product_get_regular_price' | 'az_apply_product_category_price_rate' |
| [inc/functions/product-attributes-pricing.php:292](../../inc/functions/product-attributes-pricing.php#L292) | 'woocommerce_product_get_sale_price' | 'az_apply_product_category_price_rate' |
| [inc/functions/product-attributes-pricing.php:293](../../inc/functions/product-attributes-pricing.php#L293) | 'woocommerce_product_variation_get_price' | 'az_apply_product_category_price_rate' |
| [inc/functions/product-attributes-pricing.php:294](../../inc/functions/product-attributes-pricing.php#L294) | 'woocommerce_product_variation_get_regular_price' | 'az_apply_product_category_price_rate' |
| [inc/functions/product-attributes-pricing.php:295](../../inc/functions/product-attributes-pricing.php#L295) | 'woocommerce_product_variation_get_sale_price' | 'az_apply_product_category_price_rate' |
| [inc/functions/product-attributes-pricing.php:306](../../inc/functions/product-attributes-pricing.php#L306) | 'woocommerce_get_variation_prices_hash' | 'az_product_cat_rate_variation_prices_hash' |
| [inc/functions/product-attributes-pricing.php:313](../../inc/functions/product-attributes-pricing.php#L313) | 'manage_edit-product_cat_columns' | 'az_product_cat_rate_admin_column' |
| [inc/functions/product-attributes-pricing.php:325](../../inc/functions/product-attributes-pricing.php#L325) | 'manage_product_cat_custom_column' | 'az_product_cat_rate_admin_column_content' |
| [inc/functions/security.php:19](../../inc/functions/security.php#L19) | 'the_generator' | '__return_empty_string' |
| [inc/functions/security.php:20](../../inc/functions/security.php#L20) | 'login_errors' | function() {     return 'Thông tin đăng nhập không chính xác.'; } |
| [inc/functions/theme-setup.php:143](../../inc/functions/theme-setup.php#L143) | 'locale' | function($locale) {     $lang = az_get_current_language();     return ($lang === 'en') ? 'en_US' : 'vi'; } |
| [inc/functions/woocommerce.php:37](../../inc/functions/woocommerce.php#L37) | 'woocommerce_add_to_cart_fragments' | 'az_header_cart_fragments' |
| [inc/functions/woocommerce.php:186](../../inc/functions/woocommerce.php#L186) | 'gettext' | 'az_woocommerce_vietnamese_text' |
| [inc/functions/woocommerce.php:201](../../inc/functions/woocommerce.php#L201) | 'woocommerce_add_error' | 'az_woocommerce_notice_text' |
| [inc/functions/woocommerce.php:202](../../inc/functions/woocommerce.php#L202) | 'woocommerce_add_notice' | 'az_woocommerce_notice_text' |
| [inc/functions/woocommerce.php:266](../../inc/functions/woocommerce.php#L266) | 'ngettext' | 'az_woocommerce_vietnamese_plural_text' |
| [inc/functions/woocommerce.php:279](../../inc/functions/woocommerce.php#L279) | 'woocommerce_gateway_title' | 'az_translate_payment_gateway_text' |
| [inc/functions/woocommerce.php:280](../../inc/functions/woocommerce.php#L280) | 'woocommerce_gateway_description' | 'az_translate_payment_gateway_text' |
| [inc/functions/woocommerce.php:291](../../inc/functions/woocommerce.php#L291) | 'woocommerce_product_add_to_cart_text' | 'az_woocommerce_loop_add_to_cart_text' |
| [inc/functions/woocommerce.php:292](../../inc/functions/woocommerce.php#L292) | 'woocommerce_product_single_add_to_cart_text' | function() {     return 'Thêm vào giỏ hàng'; } |
| [inc/functions/woocommerce.php:296](../../inc/functions/woocommerce.php#L296) | 'woocommerce_get_script_data' | function( $params, $handle ) {     if ( 'wc-add-to-cart-variation' !== $handle \|\| ! is_array( $params ) ) {         return $params;     }      $params['i18n_no_matching_variations_text'] = 'Rất tiếc, tổ hợp sản phẩm này không khả dụng. Vui lòng chọn  |
| [inc/functions/woocommerce.php:342](../../inc/functions/woocommerce.php#L342) | 'wc_add_to_cart_message_html' | 'az_translate_added_to_cart_message' |

## remove_action (3)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/content-media.php:86](../../inc/functions/content-media.php#L86) | 'save_post' | 'az_localize_remote_images_on_save' |
| [inc/functions/integrations-access.php:96](../../inc/functions/integrations-access.php#L96) | 'wp_head' | '_admin_bar_bump_cb' |
| [inc/functions/security.php:18](../../inc/functions/security.php#L18) | 'wp_head' | 'wp_generator' |

## remove_filter (2)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/product-attributes-pricing.php:9](../../inc/functions/product-attributes-pricing.php#L9) | 'pre_term_description' | 'wp_filter_kses' |
| [inc/functions/product-attributes-pricing.php:10](../../inc/functions/product-attributes-pricing.php#L10) | 'term_description' | 'wp_kses_data' |

## do_action (10)

| Location | First argument | Second argument |
| --- | --- | --- |
| [woocommerce/cart/cart.php:10](../../woocommerce/cart/cart.php#L10) | 'woocommerce_before_cart' |  |
| [woocommerce/cart/cart.php:15](../../woocommerce/cart/cart.php#L15) | 'woocommerce_before_cart_table' |  |
| [woocommerce/cart/cart.php:24](../../woocommerce/cart/cart.php#L24) | 'woocommerce_before_cart_contents' |  |
| [woocommerce/cart/cart.php:58](../../woocommerce/cart/cart.php#L58) | 'woocommerce_after_cart_item_name' | $cart_item |
| [woocommerce/cart/cart.php:125](../../woocommerce/cart/cart.php#L125) | 'woocommerce_cart_contents' |  |
| [woocommerce/cart/cart.php:126](../../woocommerce/cart/cart.php#L126) | 'woocommerce_after_cart_contents' |  |
| [woocommerce/cart/cart.php:135](../../woocommerce/cart/cart.php#L135) | 'woocommerce_cart_coupon' |  |
| [woocommerce/cart/cart.php:144](../../woocommerce/cart/cart.php#L144) | 'woocommerce_after_cart_table' |  |
| [woocommerce/cart/cart.php:150](../../woocommerce/cart/cart.php#L150) | 'woocommerce_before_cart_collaterals' |  |
| [woocommerce/cart/cart.php:156](../../woocommerce/cart/cart.php#L156) | 'woocommerce_after_cart' |  |

## apply_filters (17)

| Location | First argument | Second argument |
| --- | --- | --- |
| [page-goc-chuyen-gia.php:37](../../page-goc-chuyen-gia.php#L37) | 'the_content' | $hero_page->post_content |
| [single-product.php:300](../../single-product.php#L300) | 'the_content' | get_the_content() |
| [single-product.php:355](../../single-product.php#L355) | 'the_content' | $tech_specs |
| [single-product.php:360](../../single-product.php#L360) | 'the_content' | $practical |
| [woocommerce/cart/cart.php:28](../../woocommerce/cart/cart.php#L28) | 'woocommerce_cart_item_product' | $cart_item['data'] |
| [woocommerce/cart/cart.php:29](../../woocommerce/cart/cart.php#L29) | 'woocommerce_cart_item_product_id' | $cart_item['product_id'] |
| [woocommerce/cart/cart.php:31](../../woocommerce/cart/cart.php#L31) | 'woocommerce_cart_item_visible' | true |
| [woocommerce/cart/cart.php:35](../../woocommerce/cart/cart.php#L35) | 'woocommerce_cart_item_permalink' | $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '' |
| [woocommerce/cart/cart.php:37](../../woocommerce/cart/cart.php#L37) | 'woocommerce_cart_item_class' | 'cart_item' |
| [woocommerce/cart/cart.php:40](../../woocommerce/cart/cart.php#L40) | 'woocommerce_cart_item_thumbnail' | $_product->get_image( 'woocommerce_thumbnail' ) |
| [woocommerce/cart/cart.php:53](../../woocommerce/cart/cart.php#L53) | 'woocommerce_cart_item_name' | sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ) |
| [woocommerce/cart/cart.php:55](../../woocommerce/cart/cart.php#L55) | 'woocommerce_cart_item_name' | $_product->get_name() |
| [woocommerce/cart/cart.php:64](../../woocommerce/cart/cart.php#L64) | 'woocommerce_cart_item_price' | WC()->cart->get_product_price( $_product ) |
| [woocommerce/cart/cart.php:69](../../woocommerce/cart/cart.php#L69) | 'woocommerce_cart_item_price' | WC()->cart->get_product_price( $_product ) |
| [woocommerce/cart/cart.php:82](../../woocommerce/cart/cart.php#L82) | 'woocommerce_cart_item_quantity' | woocommerce_quantity_input(                         array(                             'input_name'   => "cart[{$cart_item_key}][qty]",                             'input_value'  => $cart_item['quantity'],                             'max_value'    = |
| [woocommerce/cart/cart.php:102](../../woocommerce/cart/cart.php#L102) | 'woocommerce_cart_item_subtotal' | WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ) |
| [woocommerce/cart/cart.php:107](../../woocommerce/cart/cart.php#L107) | 'woocommerce_cart_item_remove_link' | sprintf(                         '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">Xóa</a>',                         esc_url( wc_get_cart_remove_url( $cart_item_key ) ),                         esc_attr( sprintf( |

## register_rest_route (6)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/license-api.php:79](../../inc/license-api.php#L79) | 'az-license/v1' | '/verify' |
| [inc/license-api.php:86](../../inc/license-api.php#L86) | 'az-license/v1' | '/activate' |
| [inc/license-api.php:93](../../inc/license-api.php#L93) | 'az-license/v1' | '/usage' |
| [inc/license-api.php:100](../../inc/license-api.php#L100) | 'az-license/v1' | '/update-tokens' |
| [inc/prompt-execution-api.php:28](../../inc/prompt-execution-api.php#L28) | 'az-prompt/v1' | '/templates' |
| [inc/prompt-execution-api.php:35](../../inc/prompt-execution-api.php#L35) | 'az-prompt/v1' | '/execute' |

## register_post_type (2)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/licenses.php:42](../../inc/functions/licenses.php#L42) | 'license' | $args |
| [inc/functions/pricing.php:42](../../inc/functions/pricing.php#L42) | 'pricing' | $args |

## register_taxonomy (1)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/theme-setup.php:101](../../inc/functions/theme-setup.php#L101) | 'product_warehouse' | array( 'product' ) |

## add_shortcode (0)

No source call found.

## do_shortcode (0)

No source call found.

## wp_schedule_event (1)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/integrations-access.php:66](../../inc/functions/integrations-access.php#L66) | time() | 'daily' |

## wp_schedule_single_event (0)

No source call found.

## wp_next_scheduled (1)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/integrations-access.php:65](../../inc/functions/integrations-access.php#L65) | 'az_check_expired_licenses' |  |

## wp_clear_scheduled_hook (0)

No source call found.

## add_menu_page (1)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/admin-settings.php:24](../../inc/functions/admin-settings.php#L24) | 'Thông tin liên hệ' | 'Thông tin liên hệ' |

## add_submenu_page (2)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/admin-settings.php:34](../../inc/functions/admin-settings.php#L34) | 'az-contact-settings' | 'Header & Footer Code' |
| [inc/functions/admin-settings.php:43](../../inc/functions/admin-settings.php#L43) | 'az-contact-settings' | 'Lead liên hệ' |

## add_meta_box (6)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/licenses.php:50](../../inc/functions/licenses.php#L50) | 'az_license_details' | __( 'License Details', 'az-ai-theme' ) |
| [inc/functions/pricing.php:105](../../inc/functions/pricing.php#L105) | 'az_ai_pricing_details' | esc_html__( 'Pricing Details', 'az-ai-theme' ) |
| [inc/functions/pricing.php:267](../../inc/functions/pricing.php#L267) | 'az_ai_testimonial_details' | esc_html__( 'Testimonial Details', 'az-ai-theme' ) |
| [inc/functions/product-fields.php:10](../../inc/functions/product-fields.php#L10) | 'az_product_extra_info' | 'Thông tin chi tiết Công ty chúng tôi' |
| [inc/functions/product-videos.php:102](../../inc/functions/product-videos.php#L102) | 'az_product_video_metabox' | 'Slider Video' |
| [inc/gioi-thieu-page-fields.php:202](../../inc/gioi-thieu-page-fields.php#L202) | 'az_gioi_thieu_content' | 'Nội dung trang Giới thiệu' |

## get_post_meta (76)

| Location | First argument | Second argument |
| --- | --- | --- |
| [archive.php:102](../../archive.php#L102) | get_the_ID() | 'post_views_count' |
| [inc/functions/admin-settings.php:645](../../inc/functions/admin-settings.php#L645) | $page_id | '_wv_about_seeded' |
| [inc/functions/content-media.php:98](../../inc/functions/content-media.php#L98) | $post_id | $meta_key |
| [inc/functions/licenses.php:83](../../inc/functions/licenses.php#L83) | $post_id | '_license_key' |
| [inc/functions/licenses.php:97](../../inc/functions/licenses.php#L97) | $post_id | '_license_domain' |
| [inc/functions/licenses.php:105](../../inc/functions/licenses.php#L105) | $post_id | '_plan_slug' |
| [inc/functions/licenses.php:107](../../inc/functions/licenses.php#L107) | $post_id | '_plan_type' |
| [inc/functions/licenses.php:123](../../inc/functions/licenses.php#L123) | $post_id | '_license_status' |
| [inc/functions/licenses.php:125](../../inc/functions/licenses.php#L125) | $post_id | '_status' |
| [inc/functions/licenses.php:139](../../inc/functions/licenses.php#L139) | $post_id | '_tokens_used' |
| [inc/functions/licenses.php:140](../../inc/functions/licenses.php#L140) | $post_id | '_tokens_limit' |
| [inc/functions/licenses.php:165](../../inc/functions/licenses.php#L165) | $post->ID | '_license_key' |
| [inc/functions/licenses.php:166](../../inc/functions/licenses.php#L166) | $post->ID | '_license_domain' |
| [inc/functions/licenses.php:167](../../inc/functions/licenses.php#L167) | $post->ID | '_license_status' |
| [inc/functions/licenses.php:168](../../inc/functions/licenses.php#L168) | $post->ID | '_plan_type' |
| [inc/functions/licenses.php:169](../../inc/functions/licenses.php#L169) | $post->ID | '_plan_slug' |
| [inc/functions/licenses.php:170](../../inc/functions/licenses.php#L170) | $post->ID | '_tokens_used' |
| [inc/functions/licenses.php:171](../../inc/functions/licenses.php#L171) | $post->ID | '_tokens_limit' |
| [inc/functions/menu-icons.php:15](../../inc/functions/menu-icons.php#L15) | $item_id | '_menu_item_icon_url' |
| [inc/functions/menu-icons.php:16](../../inc/functions/menu-icons.php#L16) | $item_id | '_menu_item_icon_id' |
| [inc/functions/menu-icons.php:17](../../inc/functions/menu-icons.php#L17) | $item_id | '_menu_item_icon_width' |
| [inc/functions/menu-icons.php:18](../../inc/functions/menu-icons.php#L18) | $item_id | '_menu_item_icon_height' |
| [inc/functions/menu-icons.php:131](../../inc/functions/menu-icons.php#L131) | $item->ID | '_menu_item_icon_url' |
| [inc/functions/menu-icons.php:133](../../inc/functions/menu-icons.php#L133) | $item->ID | '_menu_item_icon_width' |
| [inc/functions/menu-icons.php:134](../../inc/functions/menu-icons.php#L134) | $item->ID | '_menu_item_icon_height' |
| [inc/functions/pricing.php:119](../../inc/functions/pricing.php#L119) | $post->ID | '_az_ai_price' |
| [inc/functions/pricing.php:120](../../inc/functions/pricing.php#L120) | $post->ID | '_az_ai_period' |
| [inc/functions/pricing.php:121](../../inc/functions/pricing.php#L121) | $post->ID | '_az_ai_featured' |
| [inc/functions/pricing.php:122](../../inc/functions/pricing.php#L122) | $post->ID | '_az_ai_description' |
| [inc/functions/pricing.php:123](../../inc/functions/pricing.php#L123) | $post->ID | '_az_ai_button_text' |
| [inc/functions/pricing.php:124](../../inc/functions/pricing.php#L124) | $post->ID | '_az_ai_button_url' |
| [inc/functions/pricing.php:125](../../inc/functions/pricing.php#L125) | $post->ID | '_az_ai_token_limit' |
| [inc/functions/pricing.php:126](../../inc/functions/pricing.php#L126) | $post->ID | '_az_ai_post_limit' |
| [inc/functions/pricing.php:130](../../inc/functions/pricing.php#L130) | $post->ID | '_az_ai_plan_name_en' |
| [inc/functions/pricing.php:140](../../inc/functions/pricing.php#L140) | $post->ID | '_az_ai_price_vnd' |
| [inc/functions/pricing.php:152](../../inc/functions/pricing.php#L152) | $post->ID | '_az_ai_description_en' |
| [inc/functions/pricing.php:153](../../inc/functions/pricing.php#L153) | $post->ID | '_az_ai_button_text_en' |
| [inc/functions/pricing.php:181](../../inc/functions/pricing.php#L181) | $post->ID | '_az_ai_button_text_en' |
| [inc/functions/pricing.php:191](../../inc/functions/pricing.php#L191) | $post->ID | '_az_ai_content_en' |
| [inc/functions/pricing.php:281](../../inc/functions/pricing.php#L281) | $post->ID | '_az_ai_author_name' |
| [inc/functions/pricing.php:282](../../inc/functions/pricing.php#L282) | $post->ID | '_az_ai_author_position' |
| [inc/functions/pricing.php:283](../../inc/functions/pricing.php#L283) | $post->ID | '_az_ai_author_position_en' |
| [inc/functions/pricing.php:284](../../inc/functions/pricing.php#L284) | $post->ID | '_az_ai_content_en' |
| [inc/functions/product-fields.php:39](../../inc/functions/product-fields.php#L39) | $post->ID | $key |
| [inc/functions/product-fields.php:46](../../inc/functions/product-fields.php#L46) | $post->ID | 'az_technical_specs' |
| [inc/functions/product-fields.php:60](../../inc/functions/product-fields.php#L60) | $post->ID | 'az_practical_applications' |
| [inc/functions/product-videos.php:67](../../inc/functions/product-videos.php#L67) | $post_id | '_az_product_video_urls' |
| [inc/functions/product-videos.php:70](../../inc/functions/product-videos.php#L70) | $post_id | 'video' |
| [inc/functions/product-videos.php:73](../../inc/functions/product-videos.php#L73) | $post_id | $legacy_key |
| [inc/functions/product-videos.php:82](../../inc/functions/product-videos.php#L82) | $post_id | $single_key |
| [inc/functions/theme-setup.php:28](../../inc/functions/theme-setup.php#L28) | $item->ID | '_menu_item_label_en' |
| [inc/functions/theme-setup.php:341](../../inc/functions/theme-setup.php#L341) | $item->ID | '_menu_item_label_en' |
| [inc/functions/theme-setup.php:369](../../inc/functions/theme-setup.php#L369) | $item->ID | '_menu_item_label_en' |
| [inc/gioi-thieu-page-fields.php:113](../../inc/gioi-thieu-page-fields.php#L113) | $post_id | '_az_gioi_thieu_data' |
| [inc/license-api.php:135](../../inc/license-api.php#L135) | $license->ID | '_status' |
| [inc/license-api.php:136](../../inc/license-api.php#L136) | $license->ID | '_plan_slug' |
| [inc/license-api.php:137](../../inc/license-api.php#L137) | $license->ID | '_api_calls_used' |
| [inc/license-api.php:138](../../inc/license-api.php#L138) | $license->ID | '_api_calls_limit' |
| [inc/license-api.php:139](../../inc/license-api.php#L139) | $license->ID | '_created_at' |
| [inc/license-api.php:140](../../inc/license-api.php#L140) | $license->ID | '_expires_at' |
| [inc/license-api.php:141](../../inc/license-api.php#L141) | $license->ID | '_license_domain' |
| [inc/license-api.php:154](../../inc/license-api.php#L154) | $license->ID | '_expiry_date' |
| [inc/license-api.php:277](../../inc/license-api.php#L277) | $license->ID | '_license_domain' |
| [inc/license-api.php:292](../../inc/license-api.php#L292) | $license->ID | '_plan_slug' |
| [inc/license-api.php:424](../../inc/license-api.php#L424) | $license->ID | '_license_key' |
| [page-gallery.php:20](../../page-gallery.php#L20) | $id | '_wp_attachment_image_alt' |
| [single-product.php:33](../../single-product.php#L33) | get_the_ID() | 'az_manufacturer' |
| [single-product.php:34](../../single-product.php#L34) | get_the_ID() | 'az_origin' |
| [single-product.php:35](../../single-product.php#L35) | get_the_ID() | 'az_warranty' |
| [single-product.php:36](../../single-product.php#L36) | get_the_ID() | 'az_shopee_url' |
| [single-product.php:37](../../single-product.php#L37) | get_the_ID() | 'az_sendo_url' |
| [single-product.php:38](../../single-product.php#L38) | get_the_ID() | 'az_lazada_url' |
| [single-product.php:39](../../single-product.php#L39) | get_the_ID() | 'az_technical_specs' |
| [single-product.php:40](../../single-product.php#L40) | get_the_ID() | 'az_practical_applications' |
| [single-product.php:94](../../single-product.php#L94) | $gallery_id | '_wp_attachment_image_alt' |
| [single.php:88](../../single.php#L88) | get_the_ID() | 'post_views_count' |

## update_post_meta (43)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/admin-settings.php:647](../../inc/functions/admin-settings.php#L647) | $page_id | '_wv_about_original_content' |
| [inc/functions/admin-settings.php:656](../../inc/functions/admin-settings.php#L656) | $page_id | '_wv_about_seeded' |
| [inc/functions/content-media.php:101](../../inc/functions/content-media.php#L101) | $post_id | $meta_key |
| [inc/functions/integrations-access.php:49](../../inc/functions/integrations-access.php#L49) | $license->ID | '_status' |
| [inc/functions/integrations-access.php:50](../../inc/functions/integrations-access.php#L50) | $license->ID | '_license_status' |
| [inc/functions/licenses.php:293](../../inc/functions/licenses.php#L293) | $post_id | $meta_key |
| [inc/functions/licenses.php:297](../../inc/functions/licenses.php#L297) | $post_id | '_status' |
| [inc/functions/menu-icons.php:65](../../inc/functions/menu-icons.php#L65) | $menu_item_db_id | '_menu_item_icon_url' |
| [inc/functions/menu-icons.php:70](../../inc/functions/menu-icons.php#L70) | $menu_item_db_id | '_menu_item_icon_id' |
| [inc/functions/menu-icons.php:74](../../inc/functions/menu-icons.php#L74) | $menu_item_db_id | '_menu_item_icon_width' |
| [inc/functions/menu-icons.php:76](../../inc/functions/menu-icons.php#L76) | $menu_item_db_id | '_menu_item_icon_height' |
| [inc/functions/pricing.php:211](../../inc/functions/pricing.php#L211) | $post_id | '_az_ai_price' |
| [inc/functions/pricing.php:215](../../inc/functions/pricing.php#L215) | $post_id | '_az_ai_price_vnd' |
| [inc/functions/pricing.php:219](../../inc/functions/pricing.php#L219) | $post_id | '_az_ai_plan_name_en' |
| [inc/functions/pricing.php:223](../../inc/functions/pricing.php#L223) | $post_id | '_az_ai_period' |
| [inc/functions/pricing.php:227](../../inc/functions/pricing.php#L227) | $post_id | '_az_ai_description' |
| [inc/functions/pricing.php:231](../../inc/functions/pricing.php#L231) | $post_id | '_az_ai_token_limit' |
| [inc/functions/pricing.php:235](../../inc/functions/pricing.php#L235) | $post_id | '_az_ai_post_limit' |
| [inc/functions/pricing.php:239](../../inc/functions/pricing.php#L239) | $post_id | '_az_ai_featured' |
| [inc/functions/pricing.php:242](../../inc/functions/pricing.php#L242) | $post_id | '_az_ai_button_text' |
| [inc/functions/pricing.php:246](../../inc/functions/pricing.php#L246) | $post_id | '_az_ai_button_text_en' |
| [inc/functions/pricing.php:250](../../inc/functions/pricing.php#L250) | $post_id | '_az_ai_description_en' |
| [inc/functions/pricing.php:254](../../inc/functions/pricing.php#L254) | $post_id | '_az_ai_content_en' |
| [inc/functions/pricing.php:258](../../inc/functions/pricing.php#L258) | $post_id | '_az_ai_button_url' |
| [inc/functions/pricing.php:320](../../inc/functions/pricing.php#L320) | $post_id | '_az_ai_author_name' |
| [inc/functions/pricing.php:324](../../inc/functions/pricing.php#L324) | $post_id | '_az_ai_author_position' |
| [inc/functions/pricing.php:328](../../inc/functions/pricing.php#L328) | $post_id | '_az_ai_author_position_en' |
| [inc/functions/pricing.php:332](../../inc/functions/pricing.php#L332) | $post_id | '_az_ai_content_en' |
| [inc/functions/product-fields.php:92](../../inc/functions/product-fields.php#L92) | $post_id | $key |
| [inc/functions/product-fields.php:98](../../inc/functions/product-fields.php#L98) | $post_id | $key |
| [inc/functions/product-fields.php:107](../../inc/functions/product-fields.php#L107) | $post_id | $key |
| [inc/functions/product-videos.php:203](../../inc/functions/product-videos.php#L203) | $post_id | '_az_product_video_urls' |
| [inc/gioi-thieu-page-fields.php:578](../../inc/gioi-thieu-page-fields.php#L578) | $post_id | '_az_gioi_thieu_data' |
| [inc/license-api.php:166](../../inc/license-api.php#L166) | $license->ID | '_status' |
| [inc/license-api.php:167](../../inc/license-api.php#L167) | $license->ID | '_license_status' |
| [inc/license-api.php:188](../../inc/license-api.php#L188) | $license->ID | '_license_domain' |
| [inc/license-api.php:287](../../inc/license-api.php#L287) | $license->ID | '_license_domain' |
| [inc/license-api.php:288](../../inc/license-api.php#L288) | $license->ID | '_status' |
| [inc/license-api.php:289](../../inc/license-api.php#L289) | $license->ID | '_activated_at' |
| [inc/license-api.php:337](../../inc/license-api.php#L337) | $license->ID | '_api_calls_used' |
| [inc/license-api.php:338](../../inc/license-api.php#L338) | $license->ID | '_last_used_at' |
| [inc/license-api.php:378](../../inc/license-api.php#L378) | $license->ID | '_tokens_used' |
| [inc/license-api.php:379](../../inc/license-api.php#L379) | $license->ID | '_last_used_at' |

## get_term_meta (11)

| Location | First argument | Second argument |
| --- | --- | --- |
| [archive.php:24](../../archive.php#L24) | $term->term_id | 'cp_cat_image_id' |
| [front-page.php:101](../../front-page.php#L101) | $term->term_id | 'thumbnail_id' |
| [front-page.php:103](../../front-page.php#L103) | $term->term_id | 'cp_cat_image_id' |
| [inc/functions/category-images.php:38](../../inc/functions/category-images.php#L38) | $term->term_id | 'cp_cat_image_id' |
| [inc/functions/category-images.php:121](../../inc/functions/category-images.php#L121) | $term_id | 'cp_cat_image_id' |
| [inc/functions/home-products.php:162](../../inc/functions/home-products.php#L162) | $term->term_id | 'thumbnail_id' |
| [inc/functions/home-products.php:165](../../inc/functions/home-products.php#L165) | $term->term_id | 'cp_cat_image_id' |
| [inc/functions/product-attributes-pricing.php:180](../../inc/functions/product-attributes-pricing.php#L180) | $term->term_id | 'az_price_rate' |
| [inc/functions/product-attributes-pricing.php:258](../../inc/functions/product-attributes-pricing.php#L258) | $related_term_id | 'az_price_rate' |
| [inc/functions/product-attributes-pricing.php:321](../../inc/functions/product-attributes-pricing.php#L321) | $term_id | 'az_price_rate' |
| [inc/functions/term-language.php:16](../../inc/functions/term-language.php#L16) | $term->term_id | '_category_name_en' |

## update_term_meta (3)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/category-images.php:110](../../inc/functions/category-images.php#L110) | $term_id | 'cp_cat_image_id' |
| [inc/functions/product-attributes-pricing.php:207](../../inc/functions/product-attributes-pricing.php#L207) | $term_id | 'az_price_rate' |
| [inc/functions/term-language.php:53](../../inc/functions/term-language.php#L53) | $term_id | '_category_name_en' |

## set_transient (1)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/contact-form.php:24](../../inc/functions/contact-form.php#L24) | 'az_contact_captcha_' . $token | $code |

## get_transient (1)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/contact-form.php:41](../../inc/functions/contact-form.php#L41) | $transient_key |  |

## delete_transient (1)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/contact-form.php:43](../../inc/functions/contact-form.php#L43) | $transient_key |  |

## wp_enqueue_style (6)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/contact-settings.php:186](../../inc/functions/contact-settings.php#L186) | 'az-admin' | get_template_directory_uri() . '/css/admin.css' |
| [inc/functions/integrations-access.php:10](../../inc/functions/integrations-access.php#L10) | 'login_css' | get_stylesheet_directory_uri() . '/login.css' |
| [inc/functions/theme-setup.php:189](../../inc/functions/theme-setup.php#L189) | 'az-ai-theme-style' | get_stylesheet_uri() |
| [inc/functions/theme-setup.php:190](../../inc/functions/theme-setup.php#L190) | 'az-container-sync' | get_template_directory_uri() . '/css/container-sync.css' |
| [inc/functions/theme-setup.php:194](../../inc/functions/theme-setup.php#L194) | 'az-page-style' | get_template_directory_uri() . '/css/page.css' |
| [inc/functions/theme-setup.php:197](../../inc/functions/theme-setup.php#L197) | 'font-awesome' | 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' |

## wp_enqueue_script (4)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/contact-settings.php:187](../../inc/functions/contact-settings.php#L187) | 'az-admin-contact' | get_template_directory_uri() . '/js/admin-contact.js' |
| [inc/functions/integrations-access.php:72](../../inc/functions/integrations-access.php#L72) | 'az-pricing-redirect' | get_template_directory_uri() . '/js/pricing-redirect.js' |
| [inc/functions/theme-setup.php:200](../../inc/functions/theme-setup.php#L200) | 'az-language' | get_template_directory_uri() . '/js/az-language.js' |
| [inc/functions/theme-setup.php:202](../../inc/functions/theme-setup.php#L202) | 'tailwindcss' | 'https://cdn.tailwindcss.com?plugins=forms,container-queries' |

## wp_add_inline_style (0)

No source call found.

## wp_add_inline_script (2)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/product-attributes-pricing.php:58](../../inc/functions/product-attributes-pricing.php#L58) | 'jquery' | 'window.azCheckboxProductAttributes = ' . wp_json_encode( array_values( $checkbox_taxonomies ) ) . ';' |
| [inc/functions/theme-setup.php:204](../../inc/functions/theme-setup.php#L204) | 'tailwindcss' | "         tailwind.config = {             theme: {                 extend: {                     colors: {                         brand: {                             dark: '#111111',                             primary: '#292023',                   |

## get_template_part (2)

| Location | First argument | Second argument |
| --- | --- | --- |
| [template-parts/language-switcher-simple.php:3](../../template-parts/language-switcher-simple.php#L3) | 'template-parts/language-switcher' |  |
| [template-parts/sidebar-intro.php:4](../../template-parts/sidebar-intro.php#L4) | 'template-parts/sidebar-intro' |  |

## az_theme_mod (1)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/theme-setup.php:175](../../inc/functions/theme-setup.php#L175) | $key | $default_vi = '' |

## add_setting (3)

| Location | First argument | Second argument |
| --- | --- | --- |
| [inc/functions/pricing.php:49](../../inc/functions/pricing.php#L49) | 'az_site_name_en' | array(         'default'           => '',         'sanitize_callback' => 'sanitize_text_field',         'transport'         => 'refresh',     ) |
| [inc/functions/pricing.php:63](../../inc/functions/pricing.php#L63) | 'az_logo_size' | array(         'default'           => '52',         'sanitize_callback' => 'absint',         'transport'         => 'postMessage',     ) |
| [inc/functions/pricing.php:82](../../inc/functions/pricing.php#L82) | 'az_logo_text_size' | array(         'default'           => '16',         'sanitize_callback' => 'absint',         'transport'         => 'postMessage',     ) |
