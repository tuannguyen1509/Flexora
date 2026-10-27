# Flexora Phase 01 — Data Compatibility

Ngày audit: 27/10/2026. Chỉ phân tích source, không truy cập hay ghi database.

Quy ước: `P/` = `../themes/themes/az-theme/`; `C/` = `../themes/themes/az-theme-child/`. Các đường dẫn dưới đây là đường dẫn source, không phải đường dẫn dự kiến của Flexora.

## 1. Danh mục và giới hạn bằng chứng

[DATA-KEY-REGISTER.md](DATA-KEY-REGISTER.md) liệt kê 123 key literal tại 374 callsite được bộ quét đối số nhận diện, gồm vị trí đọc, ghi/xóa/đăng ký và phụ thuộc trực tiếp từ Child. Đây **không phải toàn bộ key**: còn key ghép chuỗi, constant, vòng lặp field, tên trong mapping và dữ liệu WordPress/plugin. [DATA-CALLSITES.txt](DATA-CALLSITES.txt) giữ biểu thức nguồn, kể cả SQL và các trường hợp động. [PREFIX-LITERALS.tsv](PREFIX-LITERALS.tsv) là concordance literal có prefix, có cả hook/hàm/handle; không được xem tất cả dòng trong đó là database key.

Các nhóm động/gián tiếp được bổ sung dưới đây. Không suy diễn key chỉ xuất hiện trong docs lịch sử thành key đang được runtime hiện tại đọc/ghi. Key không thấy writer trong source được ghi rõ; có thể dữ liệu đã được tạo ở phiên bản cũ hoặc plugin ngoài theme.

## 2. Option chính `az_theme_options`

Lưu trong bảng options của WordPress theo prefix database thực tế. Dạng PHP array được WordPress serialize, không phải bảng riêng, không phải JSON chung cho toàn bộ options.

- Reader: `P/inc/helpers/options.php`: `az_get_options()`, `az_array_get()`, `az_get_option($path,$default)`; renderer/core/template đọc thêm từng nhánh.
- Writer chính: `P/inc/admin/class-advanced.php`: `AZ_Advanced::save()` và `reset()`; `P/inc/modules/homepage/admin.php`: `az_homepage_save()`; importer Flatsome ghi cùng option.
- `AZ_Advanced::save()` giữ field không gửi, nhánh không gửi, key chưa đăng ký; truyền `false` cho autoload. Homepage save không truyền tham số autoload; không thể suy ra autoload thực tế của bản ghi hiện có nếu không có DB.
- Child mở rộng registry, dữ liệu vẫn lưu **bên trong option Parent này**. Đổi theme không tự xóa option; engine mới không đọc đúng key/shape sẽ làm nội dung biến mất khỏi giao diện dù bản ghi còn nguyên.

| Nhánh | Field ID đang đăng ký ở Parent | Reader / ý nghĩa |
|---|---|---|
| `general` | `classic_editor`, `import_remote_images`, `security_headers`, `generic_login_errors`, `show_leads_menu`, `company_name`, `brand_name`, `website`, `tax_code`, `phones`, `emails`, `working_hours`, `contact_person`, `addresses`, `representative`, `business_registration`, `contact_page_title`, `contact_page_description`, `copyright`, `ministry_of_trade_url`, `breadcrumb`, `preloader` | `helpers/options.php`, `helpers/contact-maps.php`, editor/media/security/site-effects, `page-contact.php`, header/footer. Repeater phones/emails: `{label,value}`; addresses: `{label,value,maps_url,maps_embed}`. |
| `user` | `enabled`, `own_content`, `admin_only`, `show_id`, `hide_admin_bar`, `admin_bar_location`, `admin_bar_scope`, `avatar_upload` | `core/user-options.php`; cần cả master và feature switch. |
| `branding` | `logo`, `logo_mobile`, `favicon`, `og_image`, `home_h1`, `home_meta_description`, `logo_width`, `logo_width_mobile` | Helper logo/H1, SEO, dynamic CSS; **chưa thấy consumer tương ứng cho `favicon`/`og_image` trong implementation hiện tại**. Helper còn đọc `logo_sticky`, `logo_light`, `logo_dark` dù registry hiện không có field tương ứng. |
| `header` | `mobile_menu_enabled`, `mobile_menu_style`, `mobile_menu`, `top_bar`, `top_bar_text`, `top_menu`, `height`, `show_search`, `show_category`, `category_menu`, `category_button_text`, `search_placeholder`, `show_account`, `account_label`, `show_cart`, `cart_label`, `show_cta`, `cta_text`, `cta_url`, `show_primary_menu`, `primary_menu`, `sticky`, `sticky_top_bar`, `sticky_header_main`, `sticky_header_bottom`, `sticky_menu`, `sticky_show_search`, `transparent` | `helpers/header-components.php`, `core/mobile-menu.php`, Parent/Child header. Menu lưu term ID, không lưu HTML. |
| `footer` | `show_top_links`, `show_social`, `show_copyright`, `back_to_top`, `back_to_top_position` | `helpers/footer-components.php`, footer templates, `AZ_Site_Effects`; `back_to_top` fallback `general.back_to_top`. |
| `colors` | `primary`, `primary_hover`, `secondary`, `secondary_hover`, `accent`, `accent_hover`, `heading`, `text`, `muted`, `border`, `surface`, `page_background`, `success`, `warning`, `error`, `info` | `AZ_Dynamic_CSS`, Child SOVIBAN tokens. |
| `typography` | `font_library` (link UI), `body_family`, `body_size`, `body_size_tablet`, `body_size_mobile`, `body_weight`, `body_line_height`, `body_letter_spacing`, `heading_family`, `heading_weight`, `heading_line_height`, `heading_letter_spacing`, `h1_desktop/tablet/mobile`, `h2_desktop/tablet/mobile`, `h3_desktop/tablet/mobile`, `h4_desktop/tablet/mobile`, `navigation_size`, `navigation_weight`, `navigation_letter_spacing` | Dynamic CSS và Font Library resolver; link UI không phải nội dung cần migrate. |
| `layout` | `site_layout`, `container`, `container_tablet`, `mobile_padding`, `grid_gutter`, `section_spacing_desktop`, `section_spacing_tablet`, `section_spacing_mobile`, `radius_small`, `radius_medium`, `radius_large` | Dynamic CSS đọc container/gutter/radius. Chưa thấy runtime đọc `site_layout` và nhóm `section_spacing_*`; field tồn tại không chứng minh tính năng đang chạy. |
| `blog` | `image_title_description`, `layout`, `columns_desktop/tablet/mobile`, `posts_per_page`, `archive_sidebar`, `archive_show_title`, `archive_banner`, `archive_hero_layout`, `archive_banner_height`, `archive_banner_transparent`, `archive_banner_color`, `archive_banner_overlay`, `show_author`, `show_date`, `show_category`, `show_reading_time`, `author_avatar`, `author_name`, `author_description`, `author_url`, `single_sidebar`, `single_banner`, `single_banner_height`, `single_banner_transparent`, `single_banner_color`, `single_banner_overlay`, `show_comments`, `show_related` | `archive.php`, `single.php`, `AZ_Blog`, image-description module. `blog.sidebar` vẫn là fallback legacy. `single.php` chưa dùng `show_comments`, `show_related`, và không gate thời gian đọc bằng `show_reading_time`. |
| `woocommerce` | `columns_desktop/tablet/mobile`, `products_per_page`, `show_sidebar`, `show_sorting`, `show_result_count`, `sidebar_attribute_filters`, `show_breadcrumb`, `show_gallery`, `show_brand`, `show_sku`, `show_product_price`, `show_short_description`, `show_purchase_actions`, `show_contact_box`, `show_product_facts`, `show_market_links`, `show_product_tabs`, `show_related_products`, `show_recently_viewed` | Archive/product/card/query helpers. Bộ lọc lưu array `{taxonomy,enabled}`, có fallback đọc `key`. Archive hiện gọi sorting/result count mà không kiểm tra hai switch tương ứng. |
| `compatibility` | `flatsome_shortcodes`, `flatsome_expand_content` | Shortcode/widget adapter, CSS/JS legacy. |
| `social` | `contact_enabled`, `contact_style`, `contact_phone`, `facebook`, `youtube`, `tiktok`, `instagram`, `linkedin`, `pinterest`, `x`, `zalo`, `messenger`, `whatsapp` | Social helper, Parent floating contact, Child mở rộng launcher. |
| `performance` | `upload_max_file_size_mb`, `missing_media` (link UI), `redirect_404_home`, `content_protection`, `security_enabled`, `restrict_rest_guests`, `disable_xmlrpc`, `disable_wp_embed`, `disable_pingback`, `clean_head_info`, `disable_feeds`, `images_only_uploads`, `strip_asset_versions`, `minify_html`, `preload_pages`, `lazy_load_images`, `disable_theme_style`, `disable_emoji`, `disable_block_css`, `disable_mediaelement`, `disable_jquery_migrate`, `disable_embeds`, `disable_generator`, `disable_dashicons_guests`, `duplicate_content` | `core/class-performance.php`, `performance-*`, upload/minify/media/duplicate modules. |
| `custom_code` | `custom_css`, `header_code`, `body_open_code`, `footer_code` | `AZ_Custom_Code`; script có dạng `{code,signature}` HMAC theo `wp_salt('auth')`; còn đọc marker string cũ `<!--az-trusted:...-->`. Không đổi sang plain string hoặc regenerate salt rồi coi script đã trusted. |
| `google` | `enabled`, `login_enabled`, `client_id`, `client_secret`, `callback` (link UI), `show_login`, `allow_registration`, `captcha_type`, `site_key`, `captcha_secret` | OAuth/reCAPTCHA module. Secret lưu DB, ô trống khi save giữ giá trị; reset xóa về default. |
| `homepage` | `mode` được helper đọc; `sections.{id}.{enabled,title,order,limit,columns}` | Parent homepage module. Section IDs: `hero`, `featured-categories`, `weekly`, `wide-banners`, `category-products`, `suggested`, `news`, `services`, `custom-content`. `mode` chưa có routing consumer được xác nhận ngoài helper. |

Schema thực tế của từng field nằm trong `P/inc/admin/sections/*.php`; không sử dụng `docs/OPTIONS-SCHEMA.md` cũ thay cho registry hiện hành.

### Field do Child thêm vào option Parent

| Nhánh / IDs | Owner khai báo và reader |
|---|---|
| `general.fax`, `registration_date`, `registration_authority`, `footer_profile_title`, `footer_profile_image`, `footer_profile_url`, `footer_policies` | `C/inc/footer-storefront.php`: `az_child_footer_register_general_fields()`, `az_child_footer_data()`; policy `{label,url}`, profile image là attachment ID. |
| `header.show_hotline`, `hotline`, `hotline_label` | `C/inc/header-storefront.php`; render desktop/mobile/sticky. |
| `header.child_top_social`, `child_search_scope` | `C/inc/soviban-home.php`; defaults và registry tại priority 25. |
| `colors.menu_background`, `menu_text`, `menu_hover_background`, `menu_hover_text`, `menu_active_background`, `menu_active_text`, `menu_category_background`, `menu_category_text`, `menu_category_hover_background`, `menu_category_hover_text` | `C/inc/header-colors.php`; xuất `--az-menu-*` inline trên `az-header-storefront`. |
| `woocommerce.related_products_count`, `recently_viewed_count`, `gallery_image_ratio`, `gallery_ratio_width`, `gallery_ratio_height` | `C/inc/product-display.php`, `C/single-product.php`; count 1–50, custom ratio 1–100; ratio enum `square/landscape/wide/widescreen/original/custom`. |
| `social.contact_style = social_expand` | Child sửa options enum của field hiện có; Parent độc lập chỉ có `icons` và renderer contact luôn trả kiểu icons. |

Thay Child không xóa những field này khỏi option; tuy nhiên Parent độc lập không có UI/consumer đầy đủ. Giữ các nhánh lạ khi save là yêu cầu migration bắt buộc.

## 3. Option standalone legacy và writer/reader

| Key / family | Ai ghi | Ai đọc | Child phụ thuộc / rủi ro |
|---|---|---|---|
| `az_contact_company`, `az_contact_brand`, `az_contact_tax_code`, `az_contact_representative`, `az_contact_registration`, `az_contact_website`, `az_contact_person`, `az_contact_phone`, `az_contact_phone_2`, `az_contact_email`, `az_contact_sales_email`, `az_contact_support_email`, `az_contact_fax`, `az_contact_hours`, `az_contact_title_vi`, `az_contact_desc_vi` | Không thấy writer hiện hành cho toàn bộ family trong production source; registry khai báo `legacy`, không tự ghi ngược legacy | `helpers/options.php`, `admin/sections/general.php`, `page-contact.php`, product/contact helpers | Child footer đọc nhiều key trực tiếp hoặc qua helper; phải bảo vệ fallback. Không giả định Advanced mới đã migrate hết. |
| `az_contact_zalo_url`, `az_contact_messenger_url`, `az_footer_social_facebook/youtube/tiktok/linkedin` | Chưa thấy writer hiện hành | Social mapping `az_get_social_links()`, product contact | Child dùng social helper và product template. |
| `az_product_contact_title`, `az_product_contact_desc`, `az_product_contact_name`, `az_product_contact_phone`, `az_product_contact_zalo` | Chưa thấy writer hiện hành | Parent và Child `single-product.php` | Phụ thuộc trực tiếp; không coi là dữ liệu thừa vì thiếu UI. |
| `az_home_{weekly,suggested}_{enabled,title,count}` | Chưa thấy writer hiện hành | `az_homepage_settings()` | Parent fallback; Child homepage hiện không dùng layout này. |
| `az_home_weekly_category_id`, `az_home_weekly_orderby`, `az_home_suggested_category_id`, `az_home_suggested_orderby` | Chưa thấy writer hiện hành | Parent `front-page.php` | Giữ dữ liệu khi cần Parent fallback; suggested mặc định random. |
| `az_home_{main,mini_left,mini_right,side_promo,wide_left,wide_right}_banner_{img,slides,alt,link}` | Chưa thấy writer hiện hành | `az_home_banner_slot()` trong Parent `front-page.php` | Không đổi tên slot; `slides` là chuỗi URL xuống dòng/phẩy, không phải array attachment ID. Có slot được resolve nhưng layout hiện không xuất ra. |
| `az_home_category_sections` | Chưa thấy writer hiện hành | `home-products.php` | **JSON string** giải mã thành array `{category_id,title,tab_ids,visible}`; category_id có thể là `'all'`. |
| `az_home_featured_category_ids` | Chưa thấy writer hiện hành | Category helpers, featured-categories template | Danh sách ID bằng chuỗi phẩy/xuống dòng. |
| `az_home_products_per_category`, `az_home_category_batch_size`, `az_flash_sale_countdown_hours` | Chưa thấy writer hiện hành | Home query/AJAX/countdown | Không đồng nhất với Child `products.sections`. |
| `az_hfc_header_scripts`, `az_hfc_body_top_scripts`, `az_hfc_body_bottom_scripts`, `az_hfc_footer_scripts` | Chưa thấy writer hiện hành; URL admin cũ redirect đến Advanced | `AZ_Custom_Code::values()` | Dữ liệu cũ giữ nguyên; script chưa có chữ ký hợp lệ bị lọc, cần review/save có quyền sau migration. |
| `az_catalogue_lines` | `page-catalogue.php`, nhánh POST capability + nonce | `az_catalogue_items()` | Text nhiều dòng `title | URL`; Child kế thừa template. |
| `az_moments_gallery` | Chưa thấy writer hiện hành | `page-gallery.php` | Array attachment IDs; giao diện nhắc admin cũ không chứng minh màn hình đó còn tồn tại. |
| `cp_intro_text`, `cp_intro_gallery`, `cp_badge_role`, `cp_badge_city`, `cp_badge_hometown`, `cp_intro_link` | Chưa thấy writer hiện hành | `template-parts/sidebar-intro.php` | Gallery JSON string, có default gắn site cà phê; partial tồn tại nhưng chưa thấy callsite production hiện hành. Legacy / cần wrapper khi bật lại. |
| `az_site_analytics` | `AZ_Analytics::save()` (`const OPTION`) | Tracker, summary, `[az_stats]` | `{views_total,views_daily[date],clicks_total,clicks[event]}`; không có purge/bounded retention. |
| `az9s_lead_forms` | Admin-post save/delete lead form | Parser, shortcode, AJAX submit | Map theo form ID; các nhánh title, heading, intro, content, success, secondary_lang, heading_alt, intro_alt, content_alt, success_alt. Đổi form ID sẽ làm shortcode cũ không resolve. |
| `az9s_lead_notification_emails`, `az9s_leads_db_version`, `az9s_last_lead_mail_status`, `az9s_last_smtp_test_status` | Leads activation/upgrade, SMTP save, notification/test | Leads UI/mail | Không đổi version marker khi chưa đối chiếu schema thực. Mail status là dữ liệu vận hành, không phải nội dung giao diện. |
| `az_smtp_settings` | AZ9S admin-post save SMTP | `az9s_get_smtp_settings()`, `phpmailer_init`, notification | Array enabled/host/port/encryption/auth/username/password/from_email/from_name. Password giữ khi ô trống, xóa bằng cờ riêng; plaintext trong DB. |
| `az_lead_smtp_enabled`, `az_lead_notify_emails`, `az_lead_smtp_host`, `az_lead_smtp_port`, `az_lead_smtp_encryption`, `az_lead_smtp_username`, `az_lead_smtp_password`, `az_lead_smtp_from_email`, `az_lead_smtp_from_name`, `az_lead_smtp_debug` | Settings API `az_lead_smtp_group` | Legacy `admin-settings.php` mail/UI | AZ9S SMTP enabled + host được ưu tiên; cấu hình cũ vẫn là fallback. Không merge hai cấu hình một cách mù quáng. |
| `iaca_set` | `az_auto_sync_license_to_plugin()` ghi `license_key` khi login có `IACA_License_Manager` | Plugin bên ngoài; theme đọc array trước khi merge | Nghiệp vụ Content AI, Child không gọi trực tiếp. |
| `az_child_parent_mods_migrated` | Child thật và Child mẫu | Migration guard | Một cờ toàn site, không phân biệt từng Child mới. `true` có thể chặn copy khi đổi sang Child khác. |
| `az_child_product_rewrite_version` | Child refresh `wp_loaded`, invalidation category/theme | Child permalink module | Version `'1'`, soft flush một lần; không phải nội dung. |

**Medium — fallback khác nhau cần giữ:** `az_get_contact_items('phone'/'email')` fallback legacy khi list mới rỗng; vì vậy clear list chưa chắc ẩn số/email legacy. Ngược lại `az_get_social_links()` dùng `array_key_exists` để giữ URL rỗng đã lưu. `az_get_email()` không resolve repeater emails; `az_storefront_contact_phone()` đọc option/theme mod cũ, không đọc `general.phones`. Đây là behavior thực, cần adapter có chủ đích, không được tự đổi trong Phase 01.

## 4. Post meta / term meta / user meta

| Storage / key | Writer | Reader và Child dependency | Bảo vệ |
|---|---|---|---|
| Page `_az_home_page_data` | `C/inc/modules/homepage/page-fields.php` save priority 10; `C/inc/home-partners.php` priority 30; `C/inc/soviban-home.php` priority 40 | Toàn bộ homepage SOVIBAN, partners, footer toàn site qua `page_on_front`; Parent module chỉ locate/include schema Child | **High**: schema nằm Child, dữ liệu footer cũng nằm Page trang chủ. Giữ unknown branches và thay collection nguyên list thay vì merge chỉ số. |
| Page/term `_az_shared_banner` | `P/inc/core/shared-banner.php`: Page save, category/product_cat create/edit có nonce/cap | Shared banner, Child hero và header overlay | Array `layout,text,subtitle,images,height,mobile_height,overlay,transparent,autoplay,button_text,button_url`. `images` chuỗi ID ngăn phẩy; giới hạn save 20. |
| Page `_az_page_banner_layout`, `_az_page_banner_images`, `_az_page_banner_custom_text`, `_az_page_banner_height`; term `_az_category_banner_image` | Chưa thấy writer hiện hành | `az_shared_banner_get()` fallback **chỉ khi meta mới không phải array** | Đã có array mới rỗng thì không fallback. Layout mapping title→center, full_width→image, split_text_title→split. |
| Product `az_manufacturer`, `az_origin`, `az_warranty`, `az_shopee_url`, `az_sendo_url`, `az_lazada_url`, `az_technical_specs`, `az_practical_applications` | Parent `az_save_product_extra_info_meta()`; HTML còn có remote-image localization nếu bật | Parent/Child single product; Child thay metabox renderer nhưng Parent vẫn save | Không thêm underscore. Missing POST field sau nonce hợp lệ hiện có thể xóa field; editor/nonce/name phải giữ đồng bộ. |
| Product `_az_product_video_urls` | Parent video save | Parent/Child single-product qua helper | Array URL YouTube chuẩn hóa. |
| Product `video`, `video_{i}_video_ifame`, `video_{i}_video_iframe`, `video_{i}_video`, `video_ifame`, `video_iframe`, `product_video`, `az_product_video` | Chưa thấy writer hiện hành | `az_get_product_video_urls()` đọc cả mới+cũ rồi dedupe | Giữ cả spelling lỗi `ifame`; không sửa tên trực tiếp. |
| Term `az_price_rate` | `az_save_product_cat_price_rate()` | Price getters/variation hash | Tăng giá theo max rate ở term và ancestors; ảnh hưởng giá giao dịch chứ không chỉ CSS. |
| Category `cp_cat_image_id` | `cp_cat_image_save()` | `cp_get_cat_image_url()`, home helpers, Child mega menu | Attachment ID; giữ prefix cp_. |
| Term `_category_name_en` | `az_save_category_en_field()` cho category/post_tag | Admin field; chưa thấy consumer frontend trong source hiện tại | Không kết luận dữ liệu vô dụng. |
| Nav item `_menu_item_icon_url`, `_menu_item_icon_id`, `_menu_item_icon_width`, `_menu_item_icon_height` | `cp_save_menu_item_custom_fields()` | Icon title filter; Child dùng Walker core nên nhận filter | Không đổi URL thành ID duy nhất mà bỏ URL fallback; kích thước hiện dùng absint/default 20. |
| Nav item `_menu_item_label_en` | Chưa thấy writer hiện hành | Các AZ legacy walkers trong theme-setup | Child mega walker không kế thừa các AZ walkers; nó kế thừa `Walker_Nav_Menu` của WordPress. |
| Pricing `_az_ai_price`, `_az_ai_price_vnd`, `_az_ai_period`, `_az_ai_featured`, `_az_ai_description`, `_az_ai_description_en`, `_az_ai_button_text`, `_az_ai_button_text_en`, `_az_ai_button_url`, `_az_ai_token_limit`, `_az_ai_post_limit`, `_az_ai_plan_name_en`, `_az_ai_content_en` | `az_ai_save_pricing_meta()` | Metabox pricing; các consumer ngoài theme chưa xác nhận | CPT `pricing` hiện registered, UI ẩn; không đổi post_type/prefix. |
| Testimonial `_az_ai_author_name`, `_az_ai_author_position`, `_az_ai_author_position_en`, `_az_ai_content_en` | `az_ai_save_testimonial_meta()` | Testimonial metabox | Chưa thấy đăng ký CPT testimonial trong source theme; có thể provider ngoài. |
| License `_license_key`, `_license_domain`, `_license_status`, `_status`, `_plan_type`, `_plan_slug`, `_tokens_used`, `_tokens_limit`, `_api_calls_used`, `_api_calls_limit`, `_created_at`, `_expires_at`, `_expiry_date`, `_activated_at`, `_last_used_at` | License metabox save, API verify/activate/usage/tokens, cron | License API, columns, plugin-download/login sync | **High**: song song status và expiry alias; cron dùng `_expiry_date`, verify ưu tiên `_expires_at`. Không rename/dedupe thiếu chuyển đổi. |
| User `az_google_sub` | Google registration | Google login tìm user bằng subject | Không chuyển sang auto-link email; đó là thay đổi auth. |
| User `az_avatar_id` | Profile avatar save/remove | `get_avatar_data` filter | Attachment giữ trong Media khi đổi/gỡ avatar. |
| User `show_welcome_panel` | Child `hide_welcome_panel()` | Dashboard core/Child | Child vẫn ghi khi load-index. `show_admin_bar_front` writer legacy hiện bị tháo hook bởi user-options. |
| Page `_wv_about_original_content`, `_wv_about_seeded` | Chỉ có writer trong phần sau `return;` của `wv_seed_about_template_content()` | Phần seed dormant | Không chạy hiện tại; không xóa key đã tồn tại trên site cũ. `_az_gioi_thieu_data` chỉ được nhắc trong tài liệu lịch sử, không xác nhận consumer production hiện tại. |
| Core/plugin meta | WordPress/WooCommerce/plugin | `_wp_page_template`, `_thumbnail_id`, `_wp_attached_file`, `_wp_attachment_image_alt`, `thumbnail_id` term; `_price`, `_sale_price`, `total_sales` và API Woo product/gallery/variation/attributes | Đổi theme không xóa bản ghi, nhưng template filename/taxonomy registration và asset path có thể khiến dữ liệu không hiển thị. |

### Shape `_az_home_page_data` của Child

- `deal`: enabled/title/messages/category_id/end_at; vẫn có defaults và reader ở steel template cũ, không phải toàn bộ có control trong UI hiện tại.
- `products`: enabled, sections[] `{title,category_id,category_ids,image}`; giữ alias category_id, list `category_ids` ưu tiên trong renderer mới.
- `blog`: enabled/title/category_id/limit/require_category; frontend extras hiện lấy 4 bài dù có lưu limit.
- `projects`: enabled/title/category_id/require_category; hiện lấy 3 bài, không tự lấy tin chung khi thiếu category bắt buộc.
- `video`: enabled/title/items[] URL; YouTube embed được resolve riêng.
- `steel`: featured_image, promos[] `{title,image,url}`; save UI hiện xử lý tối đa 2 promo.
- `partners`: enabled/title/items[] `{id,image}`; Child còn đọc ảnh URL cũ, không render name/url cũ.
- `soviban`: `intro_enabled`, `intro_title`, `intro_description`, services[] `{title,image,url}`, `skyline_enabled`, `skyline_autoplay`, `skyline_feature_enabled`, `skyline_feature_image/title/description/url`, skyline_items[] `{image,title,url,feature_source,feature_image,feature_title,feature_description}`, `contact_band`, `footer_title`, `footer_description`, `footer_background`, `footer_logo`, `footer_tagline`, `footer_note`.
- Các list save có giới hạn riêng, services/skyline tối đa 12; partners save chưa có cap tương tự. Missing list dùng default; list đã clear cần giữ clear. Row index hiện **không phải stable item ID**.

## 5. Theme mods, customizer và nội dung core

`get_theme_mod()` lưu theo stylesheet đang active: `theme_mods_az-theme`, `theme_mods_az-theme-child` hoặc slug Child khác. Child migration thật đọc cứng `theme_mods_az-theme`, copy key chưa có rồi ghi cờ `az_child_parent_mods_migrated`.

- Đăng ký customizer hiện tại trong `pricing.php`: `az_site_name_en`, `az_logo_size`, `az_logo_text_size`. `customizer.php` xuất CSS logo sizes và preview jQuery.
- Core mods cần bảo vệ: `custom_logo`, `nav_menu_locations`, có thể `custom_css_post_id`; helper `az_theme_mod($key)` còn đọc key động `$key . '_en'`.
- Product template/contact helper đọc thêm legacy theme mods `az_contact_phone`, `az_contact_zalo_url`.
- Child mẫu **khác Child thật**: mẫu bỏ `custom_css_post_id`, copy Customizer CSS bằng `wp_get_custom_css()`/`wp_update_custom_css_post()` sang post riêng; Child thật copy tất cả missing mods và không clone CSS. Không được dùng mẫu để kết luận Child hiện tại đã an toàn về CSS.
- `custom_css` là post type của core theo stylesheet; Font Library có `wp_font_family` và `wp_font_face` với JSON trong post_content. Options typography chỉ chọn family, không chứa font file.
- `wp_posts.post_content` của Page/post, Gutenberg HTML/shortcode, attachments và `_wp_page_template` phải giữ; `page-about.php` hiện gọi shared `page-neutral` và content editor, không có foundation sáu nhóm About đang chạy.
- Widget assignments `sidebars_widgets` và `widget_*` do core quản lý, được tiêu thụ gián tiếp qua `is_active_sidebar()`/`dynamic_sidebar()`; IDs sáu vùng legacy là contract. Không tự rename/sidebar reset khi đổi Parent.

**High:** thay slug Parent khiến `Template: az-theme` của Child cũ không còn resolve. Thay slug stylesheet làm core chọn bộ theme mods/custom CSS khác. Đây là nguy cơ mất hiệu lực hiển thị, không tự động là xóa DB. Cần migration riêng có backup/rollback ở phase sau.

## 6. Custom tables

| Table (thêm `$wpdb->prefix` thực tế) | Owner / schema | Reader / writer / rủi ro |
|---|---|---|
| `az_contact_leads` | `az_create_lead_table()` trong `inc/functions/admin-settings.php`: id BIGINT UNSIGNED; customer_name VARCHAR190; phone VARCHAR80; email VARCHAR190; message TEXT; source VARCHAR190; created_at DATETIME; index created_at/email/phone | `after_switch_theme` và **mỗi admin_init** gọi dbDelta; legacy UI đọc/xóa bảng này. Contact submit mới không ghi bảng này. Không tự drop khi hợp nhất leads. |
| `az9s_leads` | `leads-manager.php`: id INT UNSIGNED; name VARCHAR150; phone VARCHAR30; email VARCHAR150; message TEXT; form_id VARCHAR100; form_title VARCHAR255; status VARCHAR20; ip VARCHAR45; created_at DATETIME; PK id | Contact adapter và hai AJAX lead submit ghi; admin list/filter/status/delete/TRUNCATE; upgrade thêm form columns nếu thiếu. Khác schema bảng cũ; không dùng rename table đơn thuần. |
| `iaca_license_usage` | Provider ngoài theme, không thấy CREATE trong theme | License verify SHOW TABLES + SELECT tokens_used/tokens_limit theo license_key/domain; chưa xác nhận DB tồn tại. |

Chưa có migration tự động được xác nhận chuyển toàn bộ `az_contact_leads` sang `az9s_leads`. Menu cũ ẩn không có nghĩa data cũ đã được chuyển. Child không trực tiếp query các bảng trên, nhưng Page liên hệ kế thừa Parent phụ thuộc gián tiếp.

## 7. Transients, cron, cookie, browser storage

| Key / contract | Owner | Tính bền vững |
|---|---|---|
| `az_contact_captcha_{uuid}` | Contact create/verify; TTL 20 phút; verify delete | Dữ liệu tạm, không migrate thành nội dung; cached Page có thể giữ challenge cũ. |
| `az_rate_{HMAC(scope:identity)}` | `auth-rate-limit.php`; Google start/callback/register, avatar | Counter best effort, read-modify-write không atomic; đổi auth salt đổi namespace effective. |
| `az_google_{sha256(state)}` | Google OAuth PKCE verifier, TTL 600s; callback delete | Phiên đang đăng nhập không được coi là nội dung bền vững. |
| `az_child_theme_install_status`, `az_child_theme_install_prompt` | Installer; status TTL một tuần, prompt chỉ delete hiện tại | Constant/legacy transient, không phải option nội dung. |
| Cron `az_check_expired_licenses` | `integrations-access.php` schedule daily ngay khi file load nếu chưa có; callback expire status | Core lưu trong option `cron`; không thấy cleanup khi switch theme. Event còn DB nhưng callback biến mất nếu module không nạp. |
| `az_language` | Cookie + localStorage; JS đọc localStorage, PHP đọc cookie | Giữ tên và `az:langchange`, global JS nếu có legacy consumers; cache/ngôn ngữ không đảm bảo nhất quán chỉ bởi comment “bypass cache”. |
| `az_recent_products` | Cookie tháng, HTTP-only; Parent hook dùng hàm Child override | Chuỗi ID phẩy; Parent 12, Child capacity max(12, configured count + 1). |
| `az_google_state` | Cookie HttpOnly/SameSite=Lax; state transient tương ứng | State auth, không đổi giữa bước start và callback. |

## 8. Kết luận data migration

Không có cơ chế Flexora migration hiện hành trong source này. Foundation có sẵn là fallback reader, merge bảo toàn nhánh, Flatsome preview/import có backup callback và Child migration một lần. Chưa có versioned migration toàn theme, transaction/rollback tổng, đồng bộ revision cho `_az_home_page_data`, hoặc test round-trip database thật.

Điều kiện tương lai: bảo vệ tên/shape/giá trị rỗng/default, taxonomy/CPT/menu IDs, URL media và template filename trước; thêm adapter đọc legacy và migration có version/backup/rollback sau khi được duyệt. Không cần rename dữ liệu chỉ để thương hiệu Parent đổi thành Flexora.

**PHASE 01 AUDIT COMPLETED — NO SOURCE CHANGES MADE**
