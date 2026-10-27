# Data key register - direct callsites

Static argument extraction, not a PHP AST or runtime data dump. Includes dormant callsites. Dynamic keys and indirect arrays are documented separately in DATA-COMPATIBILITY.md and DATA-CALLSITES.txt. Read/write locations are source evidence, not proof that values exist in a database. A missing writer means none found among direct literal calls; register_setting, loop fields, migrations or external plugins may write it.

| Storage / literal key | Read callsites | Write/delete/registration callsites | Direct Child dependency |
|---|---|---|---|
| `_activated_at` |  | update_post_meta az-theme/inc/license-api.php:289 | False |
| `_api_calls_limit` | az-theme/inc/license-api.php:138 |  | False |
| `_api_calls_used` | az-theme/inc/license-api.php:137 | update_post_meta az-theme/inc/license-api.php:337 | False |
| `_az_ai_author_name` | az-theme/inc/functions/pricing.php:279 | update_post_meta az-theme/inc/functions/pricing.php:318 | False |
| `_az_ai_author_position` | az-theme/inc/functions/pricing.php:280 | update_post_meta az-theme/inc/functions/pricing.php:322 | False |
| `_az_ai_author_position_en` | az-theme/inc/functions/pricing.php:281 | update_post_meta az-theme/inc/functions/pricing.php:326 | False |
| `_az_ai_button_text` | az-theme/inc/functions/pricing.php:122 | update_post_meta az-theme/inc/functions/pricing.php:240 | False |
| `_az_ai_button_text_en` | az-theme/inc/functions/pricing.php:179 | update_post_meta az-theme/inc/functions/pricing.php:244 | False |
| `_az_ai_button_url` | az-theme/inc/functions/pricing.php:123 | update_post_meta az-theme/inc/functions/pricing.php:256 | False |
| `_az_ai_content_en` | az-theme/inc/functions/pricing.php:189<br>az-theme/inc/functions/pricing.php:282 | update_post_meta az-theme/inc/functions/pricing.php:252<br>update_post_meta az-theme/inc/functions/pricing.php:330 | False |
| `_az_ai_description` | az-theme/inc/functions/pricing.php:121 | update_post_meta az-theme/inc/functions/pricing.php:225 | False |
| `_az_ai_description_en` | az-theme/inc/functions/pricing.php:151 | update_post_meta az-theme/inc/functions/pricing.php:248 | False |
| `_az_ai_featured` | az-theme/inc/functions/pricing.php:120 | update_post_meta az-theme/inc/functions/pricing.php:237 | False |
| `_az_ai_period` | az-theme/inc/functions/pricing.php:119 | update_post_meta az-theme/inc/functions/pricing.php:221 | False |
| `_az_ai_plan_name_en` | az-theme/inc/functions/pricing.php:129 | update_post_meta az-theme/inc/functions/pricing.php:217 | False |
| `_az_ai_post_limit` | az-theme/inc/functions/pricing.php:125 | update_post_meta az-theme/inc/functions/pricing.php:233 | False |
| `_az_ai_price` | az-theme/inc/functions/pricing.php:118 | update_post_meta az-theme/inc/functions/pricing.php:209 | False |
| `_az_ai_price_vnd` | az-theme/inc/functions/pricing.php:139 | update_post_meta az-theme/inc/functions/pricing.php:213 | False |
| `_az_ai_token_limit` | az-theme/inc/functions/pricing.php:124 | update_post_meta az-theme/inc/functions/pricing.php:229 | False |
| `_az_category_banner_image` | az-theme/inc/core/shared-banner.php:25 |  | False |
| `_az_home_page_data` | az-theme-child/inc/home-partners.php:7<br>az-theme-child/inc/home-partners.php:89<br>az-theme-child/inc/modules/homepage/page-fields.php:278<br>az-theme-child/inc/modules/homepage/page-fields.php:64<br>az-theme-child/inc/soviban-home.php:135<br>az-theme-child/inc/soviban-home.php:312<br>az-theme-child/inc/soviban-home.php:46 | update_post_meta az-theme-child/inc/home-partners.php:96<br>update_post_meta az-theme-child/inc/modules/homepage/page-fields.php:283<br>update_post_meta az-theme-child/inc/soviban-home.php:348 | True |
| `_az_page_banner_custom_text` | az-theme/inc/core/shared-banner.php:22 |  | False |
| `_az_page_banner_height` | az-theme/inc/core/shared-banner.php:22 |  | False |
| `_az_page_banner_images` | az-theme/inc/core/shared-banner.php:22 |  | False |
| `_az_page_banner_layout` | az-theme/inc/core/shared-banner.php:19 |  | False |
| `_az_product_video_urls` | az-theme/inc/functions/product-videos.php:67 | delete_post_meta az-theme/inc/functions/product-videos.php:199<br>update_post_meta az-theme/inc/functions/product-videos.php:203 | False |
| `_az_shared_banner` | az-theme/inc/core/shared-banner.php:14 | update_post_meta az-theme/inc/core/shared-banner.php:114<br>update_term_meta az-theme/inc/core/shared-banner.php:124 | False |
| `_category_name_en` | az-theme/inc/functions/term-language.php:16 | delete_term_meta az-theme/inc/functions/term-language.php:55<br>update_term_meta az-theme/inc/functions/term-language.php:53 | False |
| `_created_at` | az-theme/inc/license-api.php:139 |  | False |
| `_expires_at` | az-theme/inc/license-api.php:140 |  | False |
| `_expiry_date` | az-theme/inc/license-api.php:154 |  | False |
| `_last_used_at` |  | update_post_meta az-theme/inc/license-api.php:338<br>update_post_meta az-theme/inc/license-api.php:379 | False |
| `_license_domain` | az-theme/inc/functions/licenses.php:166<br>az-theme/inc/functions/licenses.php:97<br>az-theme/inc/license-api.php:141<br>az-theme/inc/license-api.php:277 | update_post_meta az-theme/inc/license-api.php:188<br>update_post_meta az-theme/inc/license-api.php:287 | False |
| `_license_key` | az-theme/inc/functions/licenses.php:165<br>az-theme/inc/functions/licenses.php:83<br>az-theme/inc/license-api.php:424 |  | False |
| `_license_status` | az-theme/inc/functions/licenses.php:123<br>az-theme/inc/functions/licenses.php:167 | update_post_meta az-theme/inc/functions/integrations-access.php:63<br>update_post_meta az-theme/inc/license-api.php:167 | False |
| `_menu_item_icon_height` | az-theme/inc/functions/menu-icons.php:134<br>az-theme/inc/functions/menu-icons.php:18 | update_post_meta az-theme/inc/functions/menu-icons.php:76 | False |
| `_menu_item_icon_id` | az-theme/inc/functions/menu-icons.php:16 | delete_post_meta az-theme/inc/functions/menu-icons.php:71<br>update_post_meta az-theme/inc/functions/menu-icons.php:70 | False |
| `_menu_item_icon_url` | az-theme/inc/functions/menu-icons.php:131<br>az-theme/inc/functions/menu-icons.php:15 | delete_post_meta az-theme/inc/functions/menu-icons.php:66<br>update_post_meta az-theme/inc/functions/menu-icons.php:65 | False |
| `_menu_item_icon_width` | az-theme/inc/functions/menu-icons.php:133<br>az-theme/inc/functions/menu-icons.php:17 | update_post_meta az-theme/inc/functions/menu-icons.php:74 | False |
| `_menu_item_label_en` | az-theme/inc/functions/theme-setup.php:26<br>az-theme/inc/functions/theme-setup.php:401<br>az-theme/inc/functions/theme-setup.php:429 |  | False |
| `_plan_slug` | az-theme/inc/functions/licenses.php:105<br>az-theme/inc/functions/licenses.php:169<br>az-theme/inc/license-api.php:136<br>az-theme/inc/license-api.php:292 |  | False |
| `_plan_type` | az-theme/inc/functions/licenses.php:107<br>az-theme/inc/functions/licenses.php:168 |  | False |
| `_status` | az-theme/inc/functions/licenses.php:125<br>az-theme/inc/license-api.php:135 | update_post_meta az-theme/inc/functions/integrations-access.php:62<br>update_post_meta az-theme/inc/functions/licenses.php:297<br>update_post_meta az-theme/inc/license-api.php:166<br>update_post_meta az-theme/inc/license-api.php:288 | False |
| `_tokens_limit` | az-theme/inc/functions/licenses.php:140<br>az-theme/inc/functions/licenses.php:171 |  | False |
| `_tokens_used` | az-theme/inc/functions/licenses.php:139<br>az-theme/inc/functions/licenses.php:170 | update_post_meta az-theme/inc/license-api.php:378 | False |
| `_wp_attached_file` | az-theme/inc/core/media-tools.php:46<br>az-theme/inc/core/media-tools.php:7 |  | False |
| `_wp_attachment_image_alt` | az-theme/inc/core/blog-image-description.php:15<br>az-theme/inc/core/blog-image-description.php:51<br>az-theme/page-gallery.php:20<br>az-theme/single-product.php:116<br>az-theme-child/single-product.php:117 | update_post_meta az-theme/inc/core/blog-image-description.php:16 | True |
| `_wv_about_original_content` |  | update_post_meta az-theme/inc/functions/admin-settings.php:495 | False |
| `_wv_about_seeded` | az-theme/inc/functions/admin-settings.php:493 | update_post_meta az-theme/inc/functions/admin-settings.php:504 | False |
| `admin_email` | az-theme/inc/functions/admin-settings.php:161<br>az-theme/inc/functions/admin-settings.php:165<br>az-theme/inc/functions/admin-settings.php:388<br>az-theme/inc/functions/admin-settings.php:412<br>az-theme/inc/functions/admin-settings.php:431<br>az-theme/inc/helpers/options.php:31<br>az-theme/inc/modules/leads/inc/leads-manager.php:61<br>az-theme/inc/modules/leads/inc/leads-manager.php:705<br>az-theme/inc/modules/leads/inc/leads-manager.php:746<br>az-theme/inc/modules/leads/inc/leads-manager.php:812<br>az-theme/inc/modules/leads/inc/leads-manager.php:827<br>az-theme/inc/modules/leads/module.php:43<br>az-theme/inc/modules/leads/module.php:71<br>az-theme/inc/modules/leads/module.php:85 |  | False |
| `az_avatar_id` | az-theme/inc/core/user-options.php:112 | delete_user_meta az-theme/inc/core/user-options.php:87<br>update_user_meta az-theme/inc/core/user-options.php:101 | False |
| `az_catalogue_lines` | az-theme/inc/functions/admin-settings.php:515<br>az-theme/page-catalogue.php:40 | update_option az-theme/page-catalogue.php:19 | False |
| `az_child_parent_mods_migrated` | az-theme/inc/admin/templates/child-theme/functions.php:14<br>az-theme-child/functions.php:30 | update_option az-theme/inc/admin/templates/child-theme/functions.php:39<br>update_option az-theme-child/functions.php:44 | True |
| `az_child_product_rewrite_version` | az-theme-child/inc/product-permalinks.php:139 | delete_option az-theme-child/inc/product-permalinks.php:134<br>update_option az-theme-child/inc/product-permalinks.php:142 | True |
| `az_child_theme_install_prompt` |  | delete_transient az-theme/inc/admin/class-child-theme-installer.php:163<br>delete_transient az-theme/inc/admin/class-child-theme-installer.php:173<br>delete_transient az-theme/inc/admin/class-child-theme-installer.php:39 | False |
| `az_contact_company` | az-theme/inc/helpers/options.php:28 |  | False |
| `az_contact_email` | az-theme/inc/functions/admin-settings.php:161<br>az-theme/inc/functions/admin-settings.php:388<br>az-theme/inc/functions/admin-settings.php:412<br>az-theme/inc/functions/admin-settings.php:431<br>az-theme/inc/helpers/options.php:31 |  | False |
| `az_contact_hours` | az-theme/page-contact.php:12 |  | False |
| `az_contact_phone` | az-theme/front-page.php:19<br>az-theme/inc/functions/home-products.php:196<br>az-theme/inc/helpers/options.php:30<br>az-theme/single-product.php:162<br>az-theme-child/single-product.php:163 |  | True |
| `az_contact_zalo_url` | az-theme/single-product.php:165<br>az-theme-child/single-product.php:166 |  | True |
| `az_flash_sale_countdown_hours` | az-theme/inc/functions/home-products.php:399 |  | False |
| `az_google_sub` |  | update_user_meta az-theme/inc/core/google-login.php:56 | False |
| `az_home_category_batch_size` | az-theme/inc/functions/home-products.php:504 |  | False |
| `az_home_category_sections` | az-theme/inc/functions/home-products.php:11 |  | False |
| `az_home_featured_category_ids` | az-theme/inc/functions/home-products.php:131<br>az-theme/inc/functions/home-products.php:42<br>az-theme/inc/modules/homepage/templates/featured-categories.php:3 |  | False |
| `az_home_products_per_category` | az-theme/inc/functions/home-products.php:522<br>az-theme/inc/modules/homepage/module.php:55 |  | False |
| `az_home_suggested_category_id` | az-theme/front-page.php:195 |  | False |
| `az_home_suggested_orderby` | az-theme/front-page.php:196 |  | False |
| `az_home_weekly_category_id` | az-theme/front-page.php:165 |  | False |
| `az_home_weekly_orderby` | az-theme/front-page.php:166 |  | False |
| `az_lazada_url` | az-theme/single-product.php:55<br>az-theme-child/single-product.php:55 |  | True |
| `az_lead_notify_emails` | az-theme/inc/functions/admin-settings.php:158<br>az-theme/inc/functions/admin-settings.php:388 | register_setting az-theme/inc/functions/admin-settings.php:43 | False |
| `az_lead_smtp_debug` | az-theme/inc/functions/admin-settings.php:233<br>az-theme/inc/functions/admin-settings.php:420 | register_setting az-theme/inc/functions/admin-settings.php:51 | False |
| `az_lead_smtp_enabled` | az-theme/inc/functions/admin-settings.php:214<br>az-theme/inc/functions/admin-settings.php:384 | register_setting az-theme/inc/functions/admin-settings.php:42 | False |
| `az_lead_smtp_encryption` | az-theme/inc/functions/admin-settings.php:236<br>az-theme/inc/functions/admin-settings.php:400 | register_setting az-theme/inc/functions/admin-settings.php:46 | False |
| `az_lead_smtp_from_email` | az-theme/inc/functions/admin-settings.php:241<br>az-theme/inc/functions/admin-settings.php:412 | register_setting az-theme/inc/functions/admin-settings.php:49 | False |
| `az_lead_smtp_from_name` | az-theme/inc/functions/admin-settings.php:245<br>az-theme/inc/functions/admin-settings.php:416 | register_setting az-theme/inc/functions/admin-settings.php:50 | False |
| `az_lead_smtp_host` | az-theme/inc/functions/admin-settings.php:218<br>az-theme/inc/functions/admin-settings.php:392 | register_setting az-theme/inc/functions/admin-settings.php:44 | False |
| `az_lead_smtp_password` | az-theme/inc/functions/admin-settings.php:221<br>az-theme/inc/functions/admin-settings.php:408 | register_setting az-theme/inc/functions/admin-settings.php:48 | False |
| `az_lead_smtp_port` | az-theme/inc/functions/admin-settings.php:219<br>az-theme/inc/functions/admin-settings.php:396 | register_setting az-theme/inc/functions/admin-settings.php:45 | False |
| `az_lead_smtp_username` | az-theme/inc/functions/admin-settings.php:220<br>az-theme/inc/functions/admin-settings.php:404 | register_setting az-theme/inc/functions/admin-settings.php:47 | False |
| `az_logo_size` | az-theme/inc/functions/customizer.php:17 |  | False |
| `az_logo_text_size` | az-theme/inc/functions/customizer.php:18 |  | False |
| `az_manufacturer` | az-theme/single-product.php:50<br>az-theme-child/single-product.php:50 |  | True |
| `az_moments_gallery` | az-theme/page-gallery.php:9 |  | False |
| `az_origin` | az-theme/single-product.php:51<br>az-theme-child/single-product.php:51 |  | True |
| `az_practical_applications` | az-theme/inc/functions/product-fields.php:60<br>az-theme/single-product.php:57<br>az-theme-child/single-product.php:57 |  | True |
| `az_price_rate` | az-theme/inc/functions/product-attributes-pricing.php:180<br>az-theme/inc/functions/product-attributes-pricing.php:258<br>az-theme/inc/functions/product-attributes-pricing.php:321 | delete_term_meta az-theme/inc/functions/product-attributes-pricing.php:202<br>update_term_meta az-theme/inc/functions/product-attributes-pricing.php:207 | False |
| `az_product_contact_desc` | az-theme/single-product.php:156<br>az-theme-child/single-product.php:157 |  | True |
| `az_product_contact_name` | az-theme/single-product.php:157<br>az-theme-child/single-product.php:158 |  | True |
| `az_product_contact_phone` | az-theme/single-product.php:158<br>az-theme-child/single-product.php:159 |  | True |
| `az_product_contact_title` | az-theme/single-product.php:155<br>az-theme-child/single-product.php:156 |  | True |
| `az_product_contact_zalo` | az-theme/single-product.php:159<br>az-theme-child/single-product.php:160 |  | True |
| `az_sendo_url` | az-theme/single-product.php:54<br>az-theme-child/single-product.php:54 |  | True |
| `az_shopee_url` | az-theme/single-product.php:53<br>az-theme-child/single-product.php:53 |  | True |
| `az_smtp_settings` | az-theme/inc/modules/leads/inc/leads-manager.php:105<br>az-theme/inc/modules/leads/module.php:89 | update_option az-theme/inc/modules/leads/inc/leads-manager.php:694 | False |
| `az_technical_specs` | az-theme/inc/functions/product-fields.php:46<br>az-theme/single-product.php:56<br>az-theme-child/single-product.php:56 |  | True |
| `az_theme_options` | az-theme/inc/helpers/options.php:3<br>az-theme/inc/modules/flatsome-compat/migrations/class-settings-importer.php:55<br>az-theme/inc/modules/homepage/module.php:21 | update_option az-theme/inc/admin/class-advanced.php:100<br>update_option az-theme/inc/admin/class-advanced.php:107<br>update_option az-theme/inc/modules/flatsome-compat/migrations/class-settings-importer.php:54<br>update_option az-theme/inc/modules/homepage/admin.php:69 | False |
| `az_warranty` | az-theme/single-product.php:52<br>az-theme-child/single-product.php:52 |  | True |
| `az9s_last_lead_mail_status` | az-theme/inc/modules/leads/inc/lead-forms.php:1054<br>az-theme/inc/modules/leads/inc/leads-manager.php:334 | delete_option az-theme/inc/modules/leads/module.php:48<br>update_option az-theme/inc/modules/leads/inc/leads-manager.php:128<br>update_option az-theme/inc/modules/leads/inc/leads-manager.php:64 | False |
| `az9s_last_smtp_test_status` | az-theme/inc/modules/leads/inc/leads-manager.php:747 | delete_option az-theme/inc/modules/leads/module.php:49<br>update_option az-theme/inc/modules/leads/inc/leads-manager.php:731 | False |
| `az9s_lead_forms` | az-theme/inc/modules/leads/inc/lead-forms.php:10 | update_option az-theme/inc/modules/leads/inc/lead-forms.php:122<br>update_option az-theme/inc/modules/leads/inc/lead-forms.php:144 | False |
| `az9s_lead_notification_emails` | az-theme/inc/modules/leads/inc/leads-manager.php:746<br>az-theme/inc/modules/leads/module.php:42<br>az-theme/inc/modules/leads/module.php:66 | update_option az-theme/inc/modules/leads/inc/leads-manager.php:691<br>update_option az-theme/inc/modules/leads/module.php:43 | False |
| `az9s_leads_db_version` | az-theme/inc/modules/leads/module.php:55 | update_option az-theme/inc/modules/leads/module.php:41<br>update_option az-theme/inc/modules/leads/module.php:59 | False |
| `cp_badge_city` | az-theme/template-parts/sidebar-intro.php:89 |  | False |
| `cp_badge_hometown` | az-theme/template-parts/sidebar-intro.php:95 |  | False |
| `cp_badge_role` | az-theme/template-parts/sidebar-intro.php:83 |  | False |
| `cp_cat_image_id` | az-theme/front-page.php:88<br>az-theme/inc/functions/category-images.php:121<br>az-theme/inc/functions/category-images.php:38<br>az-theme/inc/functions/home-products.php:165 | delete_term_meta az-theme/inc/functions/category-images.php:112<br>update_term_meta az-theme/inc/functions/category-images.php:110 | False |
| `cp_intro_gallery` | az-theme/template-parts/sidebar-intro.php:21 |  | False |
| `cp_intro_link` | az-theme/template-parts/sidebar-intro.php:117 |  | False |
| `cp_intro_text` | az-theme/template-parts/sidebar-intro.php:16 |  | False |
| `custom_logo` | az-theme/header.php:13<br>az-theme/inc/functions/home-products.php:172<br>az-theme/inc/helpers/options.php:45 |  | False |
| `default_product_cat` | az-theme-child/inc/product-permalinks.php:48 |  | True |
| `iaca_set` | az-theme/inc/license-api.php:427 | update_option az-theme/inc/license-api.php:429 | False |
| `page_comments` | az-theme/comments.php:193<br>az-theme/comments.php:201 |  | False |
| `page_for_posts` | az-theme/archive.php:22<br>az-theme/inc/core/class-site-effects.php:109<br>az-theme/inc/modules/homepage/templates/news.php:26 |  | False |
| `page_on_front` | az-theme/inc/helpers/options.php:64<br>az-theme-child/inc/home-partners.php:6<br>az-theme-child/inc/modules/homepage/page-fields.php:63<br>az-theme-child/inc/modules/homepage/page-fields.php:78<br>az-theme-child/inc/soviban-home.php:44<br>az-theme-child/inc/steel-storefront.php:37 |  | True |
| `permalink_structure` | az-theme-child/inc/product-permalinks.php:6 |  | True |
| `show_admin_bar_front` |  | update_user_meta az-theme/inc/functions/integrations-access.php:120 | False |
| `show_welcome_panel` | az-theme-child/functions.php:147 | update_user_meta az-theme-child/functions.php:148 | True |
| `theme_mods_az-theme` | az-theme/inc/admin/templates/child-theme/functions.php:18<br>az-theme-child/functions.php:32 |  | True |
| `thumbnail_id` | az-theme/archive-product.php:298<br>az-theme/front-page.php:86<br>az-theme/inc/functions/home-products.php:162 |  | False |
| `video` | az-theme/inc/functions/product-videos.php:70 |  | False |

## Dynamic arguments / constants / bulk-meta calls

- az-theme/front-page.php:34 `get_option('az_home_' . $slot . '_banner_img')`
- az-theme/front-page.php:35 `get_option('az_home_' . $slot . '_banner_slides')`
- az-theme/front-page.php:52 `get_option('az_home_' . $slot . '_banner_alt')`
- az-theme/front-page.php:53 `get_option('az_home_' . $slot . '_banner_link')`
- az-theme/inc/admin/class-child-theme-installer.php:117 `get_transient(self::NOTICE_KEY)`
- az-theme/inc/admin/class-child-theme-installer.php:162 `set_transient(self::NOTICE_KEY)`
- az-theme/inc/admin/class-child-theme-installer.php:172 `delete_transient(self::NOTICE_KEY)`
- az-theme/inc/admin/class-child-theme-installer.php:38 `set_transient(self::NOTICE_KEY)`
- az-theme/inc/admin/templates/child-theme/functions.php:25 `set_theme_mod($name)`
- az-theme/inc/core/auth-rate-limit.php:10 `set_transient($key)`
- az-theme/inc/core/auth-rate-limit.php:6 `get_transient($key)`
- az-theme/inc/core/class-custom-code.php:16 `get_option($option)`
- az-theme/inc/core/google-login.php:21 `set_transient('az_google_' . hash( 'sha256', $state ))`
- az-theme/inc/core/google-login.php:36 `get_transient($key)`
- az-theme/inc/core/google-login.php:37 `delete_transient($key)`
- az-theme/inc/functions/contact-form.php:24 `set_transient('az_contact_captcha_' . $token)`
- az-theme/inc/functions/contact-form.php:41 `get_transient($transient_key)`
- az-theme/inc/functions/contact-form.php:43 `delete_transient($transient_key)`
- az-theme/inc/functions/content-media.php:102 `get_post_meta($meta_key)`
- az-theme/inc/functions/content-media.php:105 `update_post_meta($meta_key)`
- az-theme/inc/functions/licenses.php:293 `update_post_meta($meta_key)`
- az-theme/inc/functions/product-fields.php:107 `delete_post_meta($key)`
- az-theme/inc/functions/product-fields.php:107 `update_post_meta($key)`
- az-theme/inc/functions/product-fields.php:39 `get_post_meta($key)`
- az-theme/inc/functions/product-fields.php:92 `update_post_meta($key)`
- az-theme/inc/functions/product-fields.php:92 `delete_post_meta($key)`
- az-theme/inc/functions/product-fields.php:98 `update_post_meta($key)`
- az-theme/inc/functions/product-fields.php:98 `delete_post_meta($key)`
- az-theme/inc/functions/product-videos.php:73 `get_post_meta($legacy_key)`
- az-theme/inc/functions/product-videos.php:82 `get_post_meta($single_key)`
- az-theme/inc/functions/theme-setup.php:184 `get_theme_mod($key . '_en')`
- az-theme/inc/functions/theme-setup.php:187 `get_theme_mod($key)`
- az-theme/inc/functions/woocommerce.php:747 `get_option($option_name)`
- az-theme/inc/functions/woocommerce.php:811 `update_option($option_name)`
- az-theme/inc/helpers/options.php:29 `get_option('email' === $type ? ( 'email' === $legacy_key ? 'az_contact_email' : ( 'sales_email' === $legacy_key ? 'az_contact_sales_email' : 'az_contact_support_email' ) ) : 'az_contact_phone')`
- az-theme/inc/helpers/options.php:35 `get_option($legacy[ $field ])`
- az-theme/inc/helpers/options.php:44 `get_option($legacy)`
- az-theme/inc/modules/analytics/class-analytics.php:10 `update_option(self::OPTION)`
- az-theme/inc/modules/analytics/class-analytics.php:16 `get_option(AZ_Analytics::OPTION)`
- az-theme/inc/modules/analytics/class-analytics.php:9 `get_option(self::OPTION)`
- az-theme/inc/modules/content-duplicate.php:56 `add_post_meta($key)`
- az-theme/inc/modules/homepage/module.php:49 `get_option('az_home_' . $id . '_enabled')`
- az-theme/inc/modules/homepage/module.php:50 `get_option('az_home_' . $id . '_title')`
- az-theme/inc/modules/homepage/module.php:51 `get_option('az_home_' . $id . '_count')`
- az-theme/inc/options/class-renderer.php:6 `get_option($field['legacy'])`
- az-theme/inc/options/class-renderer.php:7 `get_theme_mod($field['legacy_theme_mod'])`
- az-theme-child/functions.php:39 `set_theme_mod($name)`
- az-theme-child/inc/footer-storefront.php:50 `get_option($legacy)`
- az-theme-child/inc/header-mega-menu.php:32 `get_term_meta($key)`
- az-theme-child/inc/product-admin.php:47 `get_post_meta($key)`
- az-theme-child/inc/product-admin.php:56 `get_post_meta($key)`
