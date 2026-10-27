# Flexora Phase 01 — Parent → Child Compatibility Contract

Contract dưới đây rút từ Child đang có, không phải API mong muốn. `P/` là `themes/themes/az-theme/`, `C/` là `themes/themes/az-theme-child/`. Symbol nội bộ đang được Child dùng phải được coi là **Legacy / cần wrapper**, không tự xóa khi chuẩn hóa Flexora.

## 1. Thứ tự nạp là một phần của API

WordPress nạp functions Child trước functions Parent. Trong source hiện tại:

1. `C/functions.php` require 15 file inc; phần lớn chỉ khai báo hàm và đăng ký callback để gọi API Parent sau này.
2. Ngoại lệ: `C/inc/floating-contact.php` **require trực tiếp** `get_template_directory().'/inc/floating-contact.php'` ngay khi Child load. Nó tháo hook Parent ở priority 35/5 và đăng ký dispatcher Child. Parent bootstrap về sau `require_once` cùng file nên không gắn lại callback. Đổi sang autoloader hoặc gọi bootstrap contact lần hai có thể làm double-render.
3. `C/inc/product-display.php` khai báo `az_track_recently_viewed_product()` trước; `C/inc/product-admin.php` khai báo `az_product_extra_info_meta_box_html()` trước. Parent có `function_exists` guard nhưng vẫn đăng ký hook bằng tên hàm chung. Kết quả: hook/save Parent sử dụng renderer/tracker Child.
4. Parent bootstrap tạo registry `AZ_Advanced` trong admin trước `after_setup_theme`; Child sửa field registry ở priority 20 và SOVIBAN priority 25. Không được chuyển registry registration sang priority muộn mà bỏ điểm mở rộng tương đương.
5. Parent homepage module locate `inc/modules/homepage/page-fields.php` từ Child. File Child này được Parent include cả frontend/admin, dù không được require trực tiếp trong Child functions.

Đây là phụ thuộc implementation thực, không phải contract public đã version hóa.

## 2. API PHP Child gọi

Xem đầy đủ vị trí ở [CHILD-PARENT-CALLS.tsv](CHILD-PARENT-CALLS.tsv). Đây là kết quả lexical, có thể gồm declaration trùng Child mẫu; bảng sau đã phân loại thủ công các phụ thuộc thật. Không suy ra method WordPress `parent::start_el()` là method AZ.

| Nhóm | Function/class và contract | Caller Child | Mức ổn định hiện tại |
|---|---|---|---|
| Options | `az_get_options(): array`, `az_get_option($dot_path,$default)` | Header/footer/contact/product/SOVIBAN/colors | API helper thực tế; cần giữ cả missing/null/false/0/empty semantics. |
| Business contact | `az_get_contact_items($type)` → rows label/value; `az_get_phone()`; `az_get_social_links()` → map URL **đã escape display** | `footer-storefront.php`, `header-storefront.php`, `steel-storefront.php`, `floating-contact.php` | Có coupling kiểu dữ liệu; Child decode social URLs trước escape lại tại nhiều renderer. Wrapper mới cần giữ contract cũ, API mới có thể trả raw URL riêng. |
| Branding | `az_get_logo_id($variant='desktop')`, `az_get_home_h1()` | Child header/mobile/sticky/home/SOVIBAN | Logo attachment ID, fallback `custom_logo`; missing logo và explicit zero cần kiểm tra cả behavior fallback lẫn SOVIBAN bundled default. |
| Header settings | `az_header_options()`, `az_header_menu_id($slot,$options)`, `az_product_category_menu_id($fallback)` | `header-storefront.php`, `steel-storefront.php` | Child cần toàn array defaults + saved extra keys, không chỉ field Parent biết. Menu resolver dựa cả tên/slug tiếng Việt. |
| Header renderers | `az_render_header_logo($args)`, `az_render_header_action($type,$args)` | Desktop utilities, sticky, mobile partial, SOVIBAN | HTML class/action/data/icon là contract ngoài signature. |
| Mobile | `az_mobile_menu_style()`, `az_mobile_menu_markup()`, `az_render_mobile_menu()` | Child mobile dispatcher | enum none/brand_right/brand_dropdown; markup trả string core menu list; CSS/JS Parent chạy trên DOM Child. |
| Footer | `az_footer_options()` | `template-parts/footer-soviban.php` | Defaults show_top_links/show_social/show_copyright phải còn; không chỉ lấy saved array chưa merge defaults. |
| Banner | `az_shared_banner_get($id,$term=false)` | `soviban-home.php`, steel legacy partial | Cần shape/default/legacy mapping như DATA-COMPATIBILITY. Child SOVIBAN **không tuân theo layout none để bỏ hero**: dùng toàn bộ ảnh hoặc hero đóng gói, autoplay chỉ điều khiển chuyển ảnh. |
| Woo status/actions | `az_is_woocommerce_active()`, `az_storefront_show_purchase_actions()` | Header, home extras | Có guard Woo + behavior ẩn purchase UI. Ẩn nút không phải quyền chặn mua hàng ở server. |
| Product card/price | `az_render_product_card($product_or_id,$args)`, `az_storefront_product_price_html($product)`, `az_storefront_contact_phone()` | Home extras, Child single related/recent/detail | Card contexts child-home/related/recent, args show_actions; class az-product-card/az-shop-card/az-price là contract CSS. Phone helper hiện dùng option/mod cũ. |
| Video | `az_get_product_video_urls($id)`, `az_product_youtube_embed_url($value)`, `az_product_youtube_id_from_value($value)` | Child single-product | URL normalization và legacy fields phải giữ. |
| Floating contact | `az_contact_assets()`, `az_contact_icon($name)`, `az_render_contact()` | Child dispatcher | Child gọi Parent fallback và giữ handle az-floating-contact. File path và priority callback là coupling nội bộ. |
| Field registry | `AZ_Options_Registry::get/add_field/add_section`, `AZ_Theme_Options::add_field` | Footer/header/colors/typography/product/contact/SOVIBAN | Child chỉnh array fields trực tiếp, thay options/default/title/group/description rồi add_section để replace. Đây là **API nội bộ đang bị dùng**. |
| Pluggable override | `az_product_extra_info_meta_box_html($post)`, `az_track_recently_viewed_product()` | Hai inc product Child | Declaration Child thắng, Parent cung cấp save/hook. Không thể bỏ function_exists hoặc đổi registration trực tiếp sang method mới. |

**Không thấy Child trực tiếp khởi tạo** `AZ_Dynamic_CSS`, `AZ_Blog`, `AZ_Performance`, `AZ_Custom_Code`, `AZ_Site_Effects`, `AZ_Analytics` hay gọi license/leads class. Child hưởng behavior do Parent tự instantiate. Không thấy tham chiếu constant AZ tùy biến từ Child production; các constant ABSPATH/DOING_AUTOSAVE/COOKIEPATH/COOKIE_DOMAIN/MONTH_IN_SECONDS là từ WordPress, không phải API do AZ định nghĩa.

`AZ_Child_Mega_Menu_Walker` extends **WordPress `Walker_Nav_Menu`**, không extends `AZ_AI_*_Nav_Walker`. Nó gọi constructor và start/end methods của core. Phiên bản core thấp nhất tương thích constructor/signature này **Chưa xác nhận** trong workspace chỉ có theme; metadata WP 5.8 không phải bằng chứng tương thích runtime.

## 3. Hook/filter và callback identity

| Contract | Implementation / thứ tự cần giữ |
|---|---|
| `az_after_site_header` | Parent header gọi sau mobile/sticky. Child header chỉ gọi khi không phải steel/home context. Parent site-effects breadcrumb priority 5, shared-banner default 10. Chuyển hook có thể lặp banner/breadcrumb hoặc bỏ nội dung. |
| `az_home_meta_description_context` | Parent `core/branding-seo.php` apply filter; Child `inc/branding-seo.php` mở rộng `is_page_template('front-page.php')`. |
| `after_setup_theme` 20/25 | Registry Child sửa sau Parent init. Parent user-options callback priority 0 tháo legacy admin restrictions trước priority 10 chạy. |
| `remove_action('wp_enqueue_scripts','az_contact_assets',35)` và `remove_action('wp_footer','az_render_contact',5)` | Phải giữ callback identity/priority hoặc adapter tương đương; đổi method class làm remove_action cũ không tác dụng. |
| `save_post_page` 10→30→40 | Base homepage fields → partners → SOVIBAN lần lượt read-merge-write chung `_az_home_page_data`; nonce chung az_home_page_save. Parent banner save dùng key khác. |
| `wp_nav_menu_args` | Child chọn walker dựa **exact menu_class** `az-steel-menu` hoặc `az-sticky-menu`; không áp dụng mobile/category/top. |
| `nav_menu_item_title` | Parent `cp_menu_item_add_icon` filter được core walker Child giữ lại; active/attribute filters của core cũng được giữ. |
| `woocommerce_product_get_image` priority 20 | Child thay thumbnail bằng ảnh full trên product/catalogue; Parent product card gọi get_image nên filter vẫn tác động. |
| `customize_register` priority 0 | Child quét `$wp_filter[hook]->callbacks` để tháo callback instance `WC_Shop_Customizer` trên 5 hook. Phụ thuộc nội bộ Woo + cấu trúc WP_Hook, không phải extension API ổn định của Parent. |
| Permalink filters | Child `term_link/post_type_link/rewrite_rules_array/request` priority 30, query_vars, wp_loaded 20, template_redirect 1, term create/edit/delete và after_switch_theme invalidation. Giữ đường dẫn/redirect/collision behavior là contract site đang dùng. |

Các hook khác Parent công khai qua source: `az_security_headers`, `az_security_allow_guest_rest`, `az_google_allow_login`, `az_analytics_online_count`, `az_classic_editor_post_types`, `az_import_remote_images_on_save`, `az_allow_missing_media_cleanup`, `az_allow_html_minification`, `az_allow_404_home_redirect`, `az_storefront_filter_label`, `az_after_duplicate_content`. **Chưa thấy Child hiện tại attach các hook này**; vẫn nên giữ alias trong legacy adapter cho consumer ngoài source được cung cấp. Inventory toàn source ở [HOOKS-SHORTCODES-REST.txt](HOOKS-SHORTCODES-REST.txt).

## 4. Asset handles, thứ tự và DOM/CSS contract

| Consumer Child | Dependency / hành vi |
|---|---|
| `az-theme-child` priority 15 | Style phụ thuộc `az-container-sync` → `az-ai-theme-style`. |
| `az_child_steel_assets()` priority 30 | Dequeue script/style `az-homepage` khi home; load `az-steel-storefront` CSS+JS toàn frontend, `az-header-storefront`, `az-footer-storefront`; home-only `az-home-storefront`. |
| Catalogue/product overrides priority 35 | `az-catalog-sidebar`, `az-child-product-gallery` phụ thuộc Child; thêm `az-woocommerce` và `az-product-card` nếu enqueue; commerce-buttons thêm az-woocommerce khi có. |
| Contact priority 35 | Parent `az-floating-contact`, Child `az-child-social-contact`; dispatcher dùng đúng parent URI/file, không bundle lại CSS Parent. |
| Menu colors priority 40, mega menu 45, SOVIBAN 50 | Inline variables gắn vào handle `az-header-storefront`; `az-header-mega-menu` phụ thuộc header-storefront; `az-child-soviban` phụ thuộc header/footer/home styles tương ứng. |
| Child admin header CSS priority 30 | `az-child-header-admin` phụ thuộc **`az-toggle-titles`** của Parent. |
| Homepage admin | WP `media-views`, `wp.media`, `selectWoo`, `woocommerce_admin_styles`; không có bản vendor Child riêng. |

Parent sử dụng `get_template_directory_uri()` cho asset dùng chung; một số page/Woo/content/product assets dùng `get_theme_file_path/uri()` để ưu tiên Child override. Phải giữ sự khác biệt này; không chuyển mọi path sang một hàm tùy ý.

Các DOM contract thực tế:

- Wrapper `#page.az-site`, `.az-site-header`, `.az-logo`; CSS tokens `--az-font-*`, `--az-body-*`, `--az-heading-*`, `--az-navigation-*`, `--az-container`, `--az-color-*`, alias legacy `--primary-color`.
- Sticky `[data-az-sticky-header]`, `.is-visible`, `aria-hidden`; JS Parent ngưỡng scroll 140. Child chỉ render layout.
- Mobile `.az-mobile-menu`, summary, `.az-mobile-menu__panel`, close/shade, list `#az-mobile-navigation`, `[data-submenu-open-label]` và close-label; IDs menu/panel dùng giữa renderer và JS.
- Skip link Parent `.az-skip-link`, main selector; Child có thêm link `.az-steel-skip` tới `#main`. Không xóa main IDs khi thay template.
- Product card `.az-product-card`, `.az-product-card__image.az-shop-card-img`, `.az-price`; gallery `[data-az-main-image]`, `[data-az-product-thumb]`, data-full-src/srcset/sizes/alt; tabs/panes/video data attrs. Child style can thiệp trực tiếp DOM Parent.
- Banner `_az_shared_banner` + `.az-shared-banner` và Child `[data-az-steel-slider/slide/prev/next/dot/status/pause/feature]` là hai engine DOM khác nhau; không đổi selector chỉ vì đổi thương hiệu.
- Admin `az_theme_options[section][id]`, `.az-advanced__field`, `.az-settings-card--group`, `[data-az-panel]`, `[data-repeater]`, `[data-repeater-row]`, `[data-repeater-add/remove/drag]`, IDs `az-option-*`, form IDs `az-advanced-form`, `az-reset-section-form`. Child CSS và extension registry đang dựa chúng.

Không thấy Child bundle lại Bootstrap/Font Awesome/Tailwind/carousel vendor. Duplicate ở đây chủ yếu là **logic/layout CSS và JS tự viết**, không phải hai bản package vendor giống nhau.

## 5. Template path và ownership cần giữ

| Entry | Luồng hiện tại / compatibility |
|---|---|
| `C/front-page.php` | `get_header()` → locate `template-parts/home-storefront.php` → `get_footer()`; filename cũng là Page template “Trang chủ”. `_wp_page_template=front-page.php` không được đổi trực tiếp. |
| `C/header-shop.php`, `footer-shop.php` | Require Child header/footer chung; tồn tại để caller có dùng named shop header/footer. |
| `C/template-parts/header-home.php`, `footer-home.php` | Compatibility entrypoint require header/footer Child; không nên xóa chỉ vì luồng chính hiện ít gọi. |
| `C/template-parts/home-reference.php` | Marker/entrypoint require home-storefront; Parent dùng **sự tồn tại file này** để ẩn admin homepage cũ và bỏ shared hero home. |
| `C/template-parts/footer-reference.php` | Hiển thị partners trên homepage rồi require footer-soviban vì hàm SOVIBAN luôn có; nhánh footer reference fallback vẫn tồn tại. Đóng wrapper và gọi wp_footer nằm trong partial này. |
| `C/template-parts/home-steel.php` | Layout steel cũ vẫn tồn tại, có query/render riêng; không phải homepage SOVIBAN chính hiện tại. Legacy / giữ entrypoint trước khi biết consumer ngoài. |
| `C/inc/modules/homepage/page-fields.php` | **Parent locate file Child** để cung cấp admin + data functions. Tên az_home_page_* có prefix giống Parent nhưng definition nằm Child; không được đánh đồng ownership từ tên. |
| `C/single-product.php` | Override root WP product template; khác Parent chủ yếu ratio, contact markup, section count và recent list. Copy gần nguyên khối chứa gallery/tabs/video inline JS làm tăng chi phí đồng bộ. |
| Neutral/About | Child không có override riêng; kế thừa `P/page-neutral.php`, `P/page-about.php` và `P/inc/core/neutral-page.php`. Header/footer vẫn của Child. |
| Product taxonomy proxies | `P/taxonomy-product_cat.php`, `taxonomy-product_warehouse.php`, `taxonomy-product_brand.php`, `taxonomy-pa_brand.php`, `taxonomy-pa_thuong-hieu.php` require **Parent** archive-product qua get_template_directory, không locate override file archive Child tại nhánh này. |
| Woo `woocommerce/` | Child không có thư mục override Woo; kế thừa 6 override Parent: content-product, cart/cart, cart/cart-empty, checkout/form-checkout, myaccount/my-account, order/form-tracking. Root single-product Child là cơ chế override khác. |

**High — marker không đồng nhất:** Parent asset loader tìm cả `template-parts/home-reference.php` **và** `css/home-reference.css`. Child có marker PHP nhưng chỉ có `css/home-reference-layout.css`, không có home-reference.css. Do đó nhánh asset reference của Parent không chạy; Parent enqueue homepage thông thường rồi Child dequeue. Không được suy ra CSS reference hiện được load chỉ vì file reference PHP có mặt.

## 6. Ownership của toàn bộ nhóm code Child

| File / nhóm | Site-specific — tiếp tục ở Child | Reusable candidate — chỉ đánh dấu | Business/application |
|---|---|---|---|
| `functions.php` | Admin bar/dashboard cleanup theo nhu cầu site | Asset dependencies, migration adapter có thể chuẩn hóa | Không thấy CRM/API tích hợp riêng tại đây. |
| `inc/steel-storefront.php` | Fallback labels ngành thép, anchor/slug, social presentation | Product query/category source, icon/social helper, menu resolver, card | Không coi taxonomy kho là shipping provider. |
| `inc/home-storefront.php` | Tiêu đề/link archive/layout homepage | Product discount/card/image/query/archive helpers | Không có business workflow riêng. |
| `inc/soviban-home.php` | Branding, copy, bundled images, service links, header/color defaults, footer text | Resolve local published post into slide, repeatable content admin/save mechanics | Nội dung bất động sản là site content, không tự gọi là CRM. |
| `inc/modules/homepage/page-fields.php` | Schema + defaults homepage riêng | Media picker/repeater/save-preserve pattern là foundation; engine hiện chưa generic | Không chuyển toàn schema SOVIBAN lên Parent. |
| `inc/home-partners.php` | Section title/default logo set | Gallery IDs + legacy URL, multi-select/dedupe/render | Không có đối tác API. |
| `inc/header-*` | Layout, default colors/typography/hotline content | Mega Menu, shared desktop/sticky/mobile behavior và option field extensions | Woo Customizer removal là integration-specific, cần adapter rõ. |
| `inc/footer-storefront.php` | Policy defaults, footer profile/legal/location artwork | Contact data normalization, reusable footer sections, social rendering | Không có Maps API key/client ở đây. |
| `inc/floating-contact.php` | Style launcher nếu site cần | Accessible contact disclosure + social list | Link external đơn thuần không phải Messenger/Zalo API integration. |
| `inc/product-display.php`, `product-admin.php` | Lựa chọn trình bày site | Ratio/count controls, shared gallery policy, extra-info renderer, tracker capacity | — |
| `inc/product-permalinks.php` | URL policy của site hiện tại | Có thể thành module opt-in sau kiểm thử, không bật cho mọi site | SEO/routing policy, phải giữ redirect/collision rules. |
| `inc/branding-seo.php`, `customizer.php` | Context Page homepage của site | SEO extension hook, integration adapter mẫu | Không có auth/CRM đặc thù trong Child. |
| Header/footer/front-page và các partial | HTML/branding/service/home/footer của site | Carousel/gallery/product/tab primitives nếu tách markup/config | — |
| `js/steel-storefront.js` | Selector và feature layout SOVIBAN | Carousel engine có autoplay/focus/hover/reduced-motion/dots/visibility; dropdown | — |
| `js/header-mega-menu.js`, `social-contact.js` | Skin/labels | Accessible menu/disclosure behavior | — |
| `js/home-partners-admin.js`, `soviban-admin.js` | Field names/schema cụ thể | Media gallery/repeater behavior | — |
| `css/*.css`, `style.css` | Skin SOVIBAN/steel/reference, plugin TOC overrides, header/footer/home | Component rule candidates gallery/mega-menu/buttons sau loại site tokens | Không có vendor package riêng được xác nhận. |
| `assets/images`, `icon` | Toàn bộ branding/artwork/template illustration của site | Không chuyển ảnh SOVIBAN thành default Flexora | — |
| `tests`, `docs`, screenshot | Fixtures và lịch sử triển khai site | Dùng regression baseline khi chuẩn hóa | Test không phải feature frontend. |

Không thấy CRM, shipping provider, auth flow riêng của Child trong source được cung cấp. Các nghiệp vụ license/leads/SMTP/auth nằm **Parent**, cần module boundary tương lai chứ không tự xóa để làm Parent “sạch”.

## 7. Điều kiện chấp nhận compatibility tương lai

1. Child cũ resolve được Parent `az-theme` hoặc có quá trình chuyển Template slug riêng; legacy alias PHP/hook/data/handle/DOM hoạt động trước khi thay engine.
2. Child functions-first/pluggable override và thứ tự registry/contact/bootstrap không gây redeclare, double hook, mất field.
3. Header/footer/home/banner/product/contact/neutral/About chạy với dữ liệu cũ, cả zero/empty/missing, chưa lưu Advanced và sau lưu từng tab.
4. Woo ON/OFF, cart/checkout/account, variation/gallery và category routing giữ behavior đã xác định; bật/tắt module không làm mất helper Child cần.
5. Snapshot/rollback bao gồm DB option/meta/custom table, core mods/custom CSS/widget assignments và media; không chỉ zip theme.

Đây là checklist đề xuất cho phase sau, **chưa chạy** và **chưa triển khai adapter**.

**PHASE 01 AUDIT COMPLETED — NO SOURCE CHANGES MADE**
