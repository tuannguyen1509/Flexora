# FLEXORA — PHASE 01: AUDIT SOURCE AZ THEME

Ngày: **27/10/2026**. Định hướng sản phẩm tương lai: **Flexora Parent Theme — Author Tunlamweb**. Metadata và tên source hiện tại được giữ nguyên.

## 1. Kết luận chính

AZ Theme đã có foundation đáng kế thừa: bootstrap tập trung, options dot-path, registry/renderer/sanitizer, nhiều helper header/footer/product, shared banner, typography/container tokens, template kế thừa Child và một lớp tương thích nội dung Flatsome. Flexora nên phát triển tiếp các foundation này với adapter legacy; không cần viết lại từ đầu.

Tuy nhiên source hiện tại chưa phải framework có public API và module lifecycle ổn định. Parent trộn giao diện với license Content AI, leads/SMTP/auth; Child đang dùng nhiều API implementation của Parent, chỉnh registry trực tiếp, thay hàm pluggable và phụ thuộc DOM/asset handle/path. **Compatibility không chỉ là giữ prefix `az_`.**

Các rủi ro ưu tiên là license mutation API, guest upload của lead form, helper ngoài theme chưa được bảo vệ, slug/theme mods/custom CSS, data homepage/footer nằm Child, duplicate template sản phẩm và tracker analytics ghi option theo request. Những điểm này chỉ được ghi nhận, chưa sửa.

## 2. Phạm vi, phương pháp và kiểm chứng

- Parent thực tế: [az-theme](../themes/themes/az-theme/). Child thực tế: [az-theme-child](../themes/themes/az-theme-child/). Có hai lớp thư mục `themes/themes`, không suy ra theme ở cwd mang tên Flexora đã tồn tại.
- Inventory **435 file**: Parent 330, Child 105. Gồm **344 file văn bản** được đọc toàn nội dung bằng full-file scan; 91 file binary gồm font/ảnh/catalog gettext compiled được kiểm kê và hash. PHP/JS/CSS/config/template được đối chiếu implementation và callsite, không chỉ dựa tên thư mục. Review trọng tâm thủ công là luồng runtime, save/auth/security, renderer và contract Child; inventory tự động không được coi là bằng chứng mỗi dòng đều đã được kiểm chứng runtime.
- Bằng chứng: [source-manifest.json](source-manifest.json), [TEXT-FILE-COVERAGE.md](TEXT-FILE-COVERAGE.md), [SYMBOLS.txt](SYMBOLS.txt), [DATA-CALLSITES.txt](DATA-CALLSITES.txt), [HOOKS-SHORTCODES-REST.txt](HOOKS-SHORTCODES-REST.txt), [ASSET-CALLSITES.txt](ASSET-CALLSITES.txt), [CSS-INVENTORY.md](CSS-INVENTORY.md). Các file index là phân tích lexical; kết luận quan trọng bên dưới đã đối chiếu implementation, không xem comment là behavior.
- Chỉ workspace theme, **không có WordPress core, WooCommerce/plugin source, DB hoặc site runtime**. Không include functions.php để thử, vì load có thể schedule cron/ghi DB. Không gọi endpoint hay gửi mail. Không chạy activation/installer/migration.
- PHP và Node không có trên PATH trong môi trường audit; **không chạy PHP lint, test suite, build hoặc browser**. Không dùng kết quả PASS ghi trong docs cũ làm kết quả của audit này. Cwd không phải Git repository; không có commit.
- Chỉ tạo thư mục báo cáo `audit-phase-01/` ngoài hai theme; không sửa code/theme data. Hash kiểm tra cuối ở [SOURCE-INTEGRITY.md](SOURCE-INTEGRITY.md).

Hai tài liệu chi tiết cần đọc cùng báo cáo:

1. **[Parent → Child Compatibility Contract](PARENT-CHILD-CONTRACT.md)** — API, load-order, hook, data/DOM/handle/path, ownership Child.
2. **[Data Compatibility](DATA-COMPATIBILITY.md)** — field IDs, option/meta/theme mods/tables/transients/cron; kèm [direct key register](DATA-KEY-REGISTER.md).

Các tên `P/` và `C/` bên dưới lần lượt chỉ Parent và Child trong inventory, không phải thư mục kiến trúc mới.

## 3. Kiến trúc hiện tại và bootstrap

Luồng thực tế:

```text
WordPress
  → Child functions.php (15 inc files; contact Parent được require sớm)
  → Parent functions.php
    → inc/bootstrap.php
      → az_theme_legacy_includes() (static guard)
        → 18 file inc/functions theo thứ tự cố định
        → homepage/module.php → locate page-fields của Child nếu có
      → Flatsome map/importer
      → options helpers, branding SEO, header/contact/footer components
      → Flatsome widgets/shortcodes, duplicate, analytics, utilities
      → core performance/upload/minify/security/tools/media/editor/user/auth
      → Google login, mobile menu, accessibility, blog, banner, neutral page
      → frontend only: site effects và dynamic CSS
      → sanitizer, custom-code, registry/options/renderer
      → child installer (mọi request để nhận after_switch_theme)
      → admin only: admin assets, dashboard, Advanced, menu
    → leads module nếu AZ9S plugin/function chưa tồn tại
```

Nguồn: [functions.php](../themes/themes/az-theme/functions.php), [bootstrap.php](../themes/themes/az-theme/inc/bootstrap.php), [Child functions.php](../themes/themes/az-theme-child/functions.php).

Danh sách legacy include thực: security, woocommerce, theme-setup, pricing, licenses, admin-settings, contact-form, integrations-access, translation, term-language, home-products, customizer, category-images, menu-icons, product-attributes-pricing, product-fields, content-media, product-videos. Một số docs cũ còn nói 19 file/contact-settings/About schema, **không khớp bootstrap hiện tại**.

Nhiều file vừa khai báo vừa `add_action` hoặc `new Class` ngay khi include. Không có dependency graph/module registry/toggle toàn module hay một `AZ_Theme` lifecycle chung đang chạy. Class `AZ_Theme` trong các sơ đồ docs là đề xuất, không phải class có implementation hiện hành.

### Core classes và trách nhiệm

| Class | Implementation đã thấy |
|---|---|
| `AZ_Performance` | `inc/core/class-performance.php`: init 20 bỏ emoji/embed/generator theo switch; enqueue 100 vô hiệu asset nhưng giữ handle; lazy-load filter; prefetch opt-in. |
| `AZ_Dynamic_CSS` | `inc/core/class-dynamic-css.php`: wp_head 20 xuất inline variables màu/font/size/container/radius/logo widths. Numeric được clamp; CSS value bỏ delimiter HTML/CSS. Không cache ra file. |
| `AZ_Site_Effects` | `inc/core/class-site-effects.php`: conditional preloader/back-to-top assets, breadcrumb hook và render; back-to-top fallback General cũ. |
| `AZ_Blog` | `inc/core/class-blog.php`: pre_get_posts 20 chỉnh posts_per_page main archive/home, loại shop/product category nhưng chưa loại toàn bộ taxonomy product; `az_blog_show()` đọc switch. |
| `AZ_Custom_Code` | `inc/core/class-custom-code.php`: resolver mới/legacy, dedupe output, HMAC trusted script, lọc mã chưa trusted; wp_head/body_open/footer. |
| `AZ_Analytics` | `inc/modules/analytics/class-analytics.php`: ghi views/clicks vào một option, frontend CSS, click beacon, shortcode summary. Không phải GA integration. |
| Options classes | `AZ_Options_Registry`, `AZ_Theme_Options`, `AZ_Options_Renderer`, `AZ_Options_Sanitizer` trong `inc/options/`; API static, sections trong memory. |
| Admin classes | `AZ_Admin`, `AZ_Dashboard`, `AZ_Admin_Assets`, `AZ_Advanced`, `AZ_Child_Theme_Installer`: menu, shortcut, scoped assets, settings render/save/reset, filesystem installer. |
| Flatsome migration | `AZ_Flatsome_Settings_Map` và `AZ_Flatsome_Settings_Importer`: explicit mapping/preview/import foundation; không có scanner/UI migration hoàn chỉnh đang được wiring. |
| Walkers | `AZ_AI_Nav_Walker`, `AZ_AI_Header_Nav_Walker`, `AZ_AI_Mobile_Nav_Walker` ở theme-setup; đây là legacy renderers. Child Mega Menu extends core WordPress Walker, không extends các class này. |

Helpers công khai ở cấp PHP nhưng chưa có visibility/version policy: options/contact/maps/logo/fonts/home H1/header/footer/Woo/card/video/language/translation/image utilities. Danh mục symbol toàn source ở [SYMBOLS.txt](SYMBOLS.txt); các helper Child đang dùng được tách rõ trong Compatibility Contract.

## 4. Các module và luồng giao diện

| Nhóm | Source / implementation / nhận xét |
|---|---|
| Theme setup | `inc/functions/theme-setup.php`: title/thumbnail/html5/custom-logo/Woo supports; nav locations primary/top/footer; `product_warehouse` taxonomy ở init 30; locale theo cookie vi/en. Có legacy dropdown CSS/JS wp_head toàn site. |
| Header/footer | Root header/footer là fallback tương đối generic, dùng options/helper. Child thay toàn layout, vẫn dùng Parent logo/action/mobile/sticky JS/tokens. Header Parent category menu ưu tiên tên “Danh mục sản phẩm”; Child ưu tiên menu ID đã chọn trước — cùng setting nhưng thứ tự fallback khác. Footer Parent copyright hiện year+company; Child dùng general.copyright. |
| Menu/Mega Menu | Parent menu icons lưu `_menu_item_icon_*`, category image `cp_cat_image_id`; Child `inc/header-mega-menu.php` dùng core walker, submenu artwork từ term thumbnail, JS pointer/keyboard/Escape/outside; không có vendor mega menu. |
| Blog | `archive.php`, `home.php` proxy, `single.php`, comments; configurable layout/sidebar/banner/meta, related/latest queries; card markup nằm template và Flatsome shortcode, chưa có shared `az_render_post_card()` engine được xác nhận. Comment CSS inline riêng; related/comments switch không được template single tiêu thụ. |
| Homepage Parent | `front-page.php`, `inc/modules/homepage/*`: definitions allowlist/sort/enable/order/limit/columns; templates require Parent path, dùng biến cục bộ từ front-page. Nhiều query panel tạo sẵn kể cả panel ẩn; suggested mặc định rand. |
| Homepage Child | `front-page.php` → home-storefront SOVIBAN, own schema + Parent banner settings, services/skyline/promos/partners/footer. Có steel/reference compatibility partial. Parent `homepage.sections` không phải nguồn chính cho SOVIBAN. |
| Page templates | `page.php` plain editor, Woo system-page special case; neutral/About cùng editor-based layout; contact form+maps; catalogue tự save option khi admin POST có nonce; gallery đọc moments option và lightbox inline. |
| WooCommerce | `inc/functions/woocommerce.php`: taxonomy filters, card actions/cart fragments, translation/no­tices, variation labels inline jQuery, recent cookie, tạo system pages. `home-products.php`: shared card/discount/price/hide-actions/query helpers. PHP files vẫn include dù Woo OFF, nhiều callback có guard. Không kết luận Woo OFF đã pass. |
| Product fields/pricing/video | Extra-info meta+editor save, YouTube canonicalization/legacy fallback; category rate áp vào **price getters** và variation hash, invalidation quét toàn bộ product trong category. Đây là nghiệp vụ giá, cần opt-in module tương lai và giữ behavior cũ. |
| Woo templates | Sáu override trong `woocommerce/`; root archive/single và 5 taxonomy proxies tùy chỉnh. Checkout vẫn gọi billing/shipping/order-review hooks, cart vẫn dùng core Woo APIs/nonce. Custom single bỏ qua phần lớn standard summary/tab hooks nên plugin dựa vào những hook đó cần integration test. Không khẳng định mọi plugin Woo đều tương thích. |
| Components | Product card đã tập trung tương đối tốt. Header actions/logo/mobile/shared banner/back-to-top/contact là reusable foundation. Post card, gallery, lightbox, tabs, carousel còn nhiều implementation lặp. |
| Template loader | Chủ yếu WP hierarchy/get_template_part/locate_template, không thấy standalone generic template map loader. Homepage schema locate Child; homepage section require Parent allowlist; taxonomy proxy require Parent archive. Path strategy không đồng nhất. |
| Content/media | `content-media.php`: import ảnh ngoài opt-in khi save có edit_post, dùng WordPress sideload; strip TinyMCE markers, nhóm ảnh liên tiếp. `blog-image-description.php` điền ALT/title còn thiếu ở render/save, không mutate block HTML ở wp_insert_post_data; save metadata ảnh là side effect thực. |
| Auth/User | `user-options.php` master+feature switch, own post/media lists + map_meta_cap, avatar image validation/rate limit; tháo bốn restriction legacy. `google-login.php` OAuth state cookie+transient, PKCE, verified-email, subject lookup, không auto-link email đã tồn tại, subscriber registration, hook allow-login, reCAPTCHA v2 login. Không có auth riêng trong Child. |
| Leads | `functions.php` tránh nạp module khi AZ9S plugin/version/function đã có; `inc/modules/leads/module.php` + manager/forms. Hai handler guest legacy/dynamic, admin status/delete/SMTP/forms; Contact Page adapter ghi AZ9S, legacy contact table/UI vẫn tồn tại. Không phải CRM đầy đủ. |
| SMTP | Hai cấu hình: AZ9S `az_smtp_settings` ưu tiên khi enabled+host; legacy `az_lead_smtp_*` fallback. Cả hai áp qua `phpmailer_init` cho wp_mail toàn site, không chỉ lead. Password DB plaintext, form AZ9S để trống giữ password. |
| Analytics | Bot regex loại một số UA, mọi request frontend hợp lệ tăng counter; click listener mọi a/button gửi beacon; online count mặc định **5** qua filter, không đo online user thực. Không có GA/GTM engine riêng ngoài custom-code snippet. |
| License/Content AI | `licenses.php`, `pricing.php`, `integrations-access.php`, `inc/license-api.php`: hidden CPT pricing/license, metabox/counter/expiry/domain, download ZIP plugin theo license, login ghi option iaca_set. Rõ business/application-specific; không xóa endpoint vì “không thuộc theme”. |
| Cron | Daily `az_check_expired_licenses`, schedule ngay trên include nếu chưa có; update `_status` và `_license_status` theo `_expiry_date`; không thấy clear scheduled hook khi switch theme. |
| Migration | Fallback readers, Child mods import, settings map/importer có selected rows và backup callback; không có migration Flexora/global versioning/rollback engine. Seeder About có `return` đầu nên phần ghi bên dưới hiện dormant. |
| Child installer | Hard-code az-theme/az-theme-child; filesystem API; không overwrite existing dir; kiểm multisite/file mods; đọc nguồn trước và ghi style.css cuối; cleanup chỉ file nó tạo. Tự tạo khi Parent active, không tự activate Child. Child mẫu khác Child đang chạy về mods/custom CSS. |

## 5. Những gì làm tốt và nên giữ

1. **Có phân lớp nền hữu dụng**: bootstrap, helpers/options/core/admin/modules, guard admin/frontend ở nhiều module. Cần cải tiến boundary, không cần phủ nhận giá trị code hiện có.
2. **Options merge bảo toàn dữ liệu**: Advanced save bỏ qua field/nhánh không gửi và giữ unknown keys; có end marker `az_complete`; JS chỉ submit tab hiện tại để giảm max_input_vars. Đây là foundation quan trọng cho template-map data sau này.
3. **Legacy readers có chủ đích**: custom code, banner layouts/meta, contact/social, video spelling cũ, footer back-to-top General → Footer. Có thể phát triển thành legacy adapters.
4. **Shared rendering có sử dụng thật**: product card dùng ở shop/home/related/recent/Woo loop; Child dùng lại header logo/action, mobile/sticky behavior và tokens. Không nên copy chúng thành API mới riêng biệt không có adapter.
5. **Một số security implementation khá rõ**: Advanced capability+nonce, custom-script HMAC/unfiltered_html, Google PKCE/state/verified subject và không auto-link email, avatar kiểm MIME/size/rate, maps chỉ dựng iframe HTTPS host allowlist, missing-media cleanup xác minh path/local/offload trước xóa.
6. **Accessibility đã có nền**: skip link tự tìm main, mobile Escape/focus loop/submenu aria, Child Mega Menu keyboard, SOVIBAN/shared banner autoplay dừng khi hover/focus/hidden/reduced-motion, back-to-top native anchor fallback.
7. **Bundled font và một phần conditional assets**: font local, Woo/blog/page assets có gate, admin scoped, independent back-to-top CSS vẫn hoạt động khi tắt parent style handle.
8. **Installer không phá Child cũ và importer không tự đoán source keys**: đáng giữ nguyên nguyên tắc bảo toàn dữ liệu/không overwrite trước khi thay thiết kế.

Các điểm trên dựa implementation; không đồng nghĩa tất cả luồng đã được test trên WordPress thật.

## 6. Advanced/Admin và nền cho Template Map Engine

Nguồn chính: `inc/options/class-{registry,options,renderer,sanitizer}.php`, `inc/admin/class-advanced.php`, `inc/admin/sections/*.php`, `assets/admin/js/az-advanced*.js`, repeater-sort/banner-editor; Child page-fields/home-partners/soviban admin.

### Pipeline hiện tại

```text
AZ_Advanced private section allowlist
  → require field arrays
  → AZ_Options_Registry memory sections
  → Child sửa/add fields ở after_setup_theme
  → renderer lấy saved/default/legacy option/mod/path
  → form az_theme_options[section][field]
  → admin-post authorize + completeness check
  → whitelist registry fields + type sanitizer
  → merge into az_theme_options, autoload false
```

Tabs là panel client-side với history.pushState/popstate, tất cả panel render vào một form, inactive controls disable lúc submit. Groups dùng field metadata `group/group_title/group_collapsible`, `details` hoặc section. Thêm một field trong section có sẵn được Child dùng thật; **thêm section mới vào registry chưa tự xuất hiện/save** vì danh sách sections của Advanced là private/hard-code.

| Field | Renderer / sanitizer hiện tại | Khả năng tái sử dụng |
|---|---|---|
| Text | Input text, sanitize_text_field | Tốt cho primitive, cần validate shape scalar trước cast. |
| Textarea | Textarea, sanitize_textarea_field | Tốt cho plain multiline; không phải rich editor. |
| Editor/HTML | Không có nhánh `wp_editor` trong generic renderer; sanitizer có `html → wp_kses_post` | Rich editors hiện ở product metabox riêng; homepage HTML mô tả dùng textarea+KSES. **Chưa có generic editor field**. |
| Select | Options lookup, invalid stored chọn registered default; sanitizer allowlist | Tái sử dụng được; cần type validation trước index lookup khi input malformed array. |
| Checkbox/switch | Hidden 0 + checkbox 1; sanitize boolean 0/1 | Tốt, nhưng engine mới phải định nghĩa missing vs unchecked/partial save. |
| URL/email | HTML input đúng type; esc_url_raw/sanitize_email | Có primitive, validation/error feedback còn đơn giản. |
| Number/integer/range | Numeric conversion + optional min/max, range output JS | `integer` sanitizer có nhưng renderer generic không chọn number cho type integer; một số number fields không khai min/max. |
| Color | Text input + wpColorPicker, sanitize_hex_color | Dùng lại được. |
| Image/media | Attachment ID hidden + media frame/preview/remove; absint | Chưa ràng buộc type media theo field ở sanitizer; engine cần contract riêng ID/URL. |
| Repeater | Row renderer text/email/url/textarea/map_embed; clone first row, add/remove/drag-renumber | Sanitizer recursive theo subfields, nhưng renderer **không recursive**, không có image/editor/toggle/select nested UI thật; nested typed fields sẽ thành text. Không phải generic field engine hoàn chỉnh. |
| Radio | Có render options | Sanitizer không có nhánh radio allowlist, rơi về sanitize_text_field. Legacy / cần wrapper và validator đúng type ở tương lai. |
| Map embed | Textarea; parse src và dựng một Google iframe allowlisted | Specialized reusable field nếu nhu cầu rõ, không coi là HTML arbitrary. |
| Secret | Password input không đưa giá trị đã lưu vào HTML; empty save giữ | Storage vẫn plaintext DB, không có encryption adapter. |
| Code | Textarea; custom CSS strip tags, script chỉ ký khi manage_options+unfiltered_html | Giữ trust boundary/HMAC khi bọc engine mới. |
| Attribute filters | Dynamic Woo definitions, enabled/order rows | Specialized Woo field; không nên buộc core engine phụ thuộc Woo. |
| Link | Button URL, không input/save/reset như nội dung | UI action, không phải content field. |

### Hạn chế cần giải quyết trong phase sau

- Storage target gắn cứng option `az_theme_options`, chưa abstraction post/term/user/template-instance storage.
- Không có template ID → field schema map, schema version/hash, stable repeater item IDs, migration registry/rollback, dependency checks toàn schema hay typed validation errors.
- Renderer/sanitizer dùng chuỗi if/switch; chưa field-type registry có renderer/sanitize/validate riêng mà Child đăng ký được.
- Repeater dùng numeric indexes, clone DOM và drag pointer; chưa có keyboard move-up/down; không hỗ trợ mọi nested field dù sanitizer recursive.
- Reset lấy registered defaults, không phải “khôi phục legacy effective value”; có thể đưa explicit defaults vào storage làm chấm dứt fallback. Cần giữ và giải thích behavior cũ trong adapter.
- Unknown fields được save giữ, nhưng vài module khác save bằng rebuild/missing-field delete; không thể mặc định mọi metabox đều có partial-save safety giống Advanced.
- Page-fields Child đã có data-driven render/save/default và collection replacement nhưng schema/admin markup/inline JS còn hard-code. Đây là foundation thứ hai, **không phải Template Map Engine đã triển khai**.

Kết luận: tái sử dụng primitives, media integration, sanitizer, settings UI pattern và merge logic; phát triển adapter/storage/field registry/template mapping sau. Không copy nguyên Advanced rồi chỉ rename thành Flexora Field Engine.

## 7. Asset/library và khả năng Asset Registry

Callsite chi tiết: [ASSET-CALLSITES.txt](ASSET-CALLSITES.txt). Source sizes: [CSS-INVENTORY.md](CSS-INVENTORY.md), không phải số byte tải sau gzip/cache.

| Thư viện/engine | Version nguồn xác định được | Loader / phạm vi |
|---|---|---|
| Tailwind browser CDN | URL không pin version; **version runtime Chưa xác nhận** | `az_ai_theme_scripts()` load `tailwindcss` ở head toàn site, plugins=forms,container-queries + inline config. |
| Tailwind build | `package.json` pin **3.4.17**, forms **0.5.10**, container-queries **0.1.1** | Script build-css từ assets/src/utilities.css; không có `css/utilities.min.css` trong gói và không thấy loader dùng output. Có config content quét cả Child. Không đồng nhất build dependency với CDN version. |
| Font Awesome | **6.4.0** CDN + package devDependency cùng version | Handle font-awesome load toàn site từ cdnjs; không thấy Child bundle bản riêng. |
| jQuery | Core/plugin cung cấp, version Chưa xác nhận | Advanced wp-color-picker, customizer preview, menu/category media picker, variation-label inline `(jQuery)` trên product page. Các JS standalone frontend phần lớn vanilla. |
| SelectWoo / Select2 CSS | Woo cung cấp, version Chưa xác nhận | Child homepage admin enqueue selectWoo/woocommerce_admin_styles trên post editor khi có Woo. Có fallback khi plugin không có. |
| WP media / color picker | Core runtime | Advanced, banner/category/menu/product/home admin. Dùng chung core handle, không bundled copy. |
| Chart.js | URL `@4` chỉ xác định major **4** | `template-parts/product/head.php` thẻ script trực tiếp; **không thấy callsite production include partial này**. Không báo là chart.js đang tải toàn site. Partial cũng nhúng Tailwind lần nữa nếu được gọi. |
| Be Vietnam Pro | README ghi Google webfont v12, OFL 1.1; có 54 WOFF2, weight100–900 normal/italic, 3 subsets | theme.json và CSS fallback khi không có wp_print_font_faces; Font Library selected faces được in riêng. Không có request Google Fonts bắt buộc từ loader font này. |
| Carousel/lightbox/animation | Code tự viết, không có package version | Parent homepage.js/shared-banner.js, Child steel-storefront.js; product/gallery lightbox inline; CSS animation/site effects. |
| Google reCAPTCHA | API public không pin version package | Login only khi master+captcha enabled và keys đủ. |
| Bootstrap/Swiper/Slick/Owl/Flickity/GSAP/AOS | **Không tìm thấy implementation/import/bundle tương ứng** trong production source được cung cấp | `inc/bootstrap.php` là bootstrap PHP của theme, không phải Bootstrap CSS framework. Plugin ngoài source có thể load thêm — Chưa xác nhận. |

### Load pattern và duplication

- Parent luôn load style.css ~60.7 KB, container-sync, az-content, az-price, az-product-card, Font Awesome, Tailwind head, language head, logo variants/sticky, accessibility; thêm analytics CSS dù file đặt ở `assets/admin/css` vì nội dung thực là frontend stats bar.
- Woo CSS ~75 KB gate Woo/shop/cart/checkout/account/order-tracking; vẫn chung một file cho nhiều màn hình. Blog archive tải nhiều lớp patch CSS nhỏ nối dependency chain; single có unified override trên single CSS; page.css ~22.5 KB dùng mọi Page.
- Flatsome compatibility default enabled nên CSS/JS load toàn site; maps CSS load khi có bất kỳ map đã lưu, không kiểm trang có render map. Shared banner assets load theo context, kể cả layout none.
- Child toàn frontend: child style, steel CSS/JS, header/footer CSS, mega menu CSS/JS, SOVIBAN CSS; home-storefront conditional homepage; product/catalog/cart styles conditional. Không thấy package vendor bị bundle lại giữa Parent/Child.
- Duplicate **engine**: ba carousel variants; product/gallery lightbox riêng; product single Parent/Child gần như copy; legacy mobile walker inline toggle cùng mobile-menu engine mới; multiple header/footer/home skins còn rules cùng xuất hiện.
- `home-reference-layout.css`, Parent `blog-card.css`/`blog-hero.css`, `js/floating-contact.js`, `js/pricing-redirect.js` là ví dụ cần phân biệt tồn tại và reachability. Reference layout CSS chỉ thấy fixture/test tham chiếu; pricing redirect gate template `page-pricing-order.php` hiện không có. Không đề xuất xóa: **Legacy / cần wrapper hoặc inventory consumer trước**.

Asset Registry tương lai nên tái sử dụng WordPress handle registry, thêm ownership/module/context/dependencies/version/source/override policy và inline attachment points. Giữ alias handle cũ cho Child, pin/build dependency có chủ đích sau. Không bundle thêm slider/lightbox chỉ vì muốn framework nếu engine hiện tại có thể chuẩn hóa được.

## 8. Security và rủi ro theo mức độ

Đây là source audit, không pentest. **Không gắn Critical cho một exploit production chưa kiểm chứng.** Các finding High dưới đây có logic nguồn rõ; khả năng khai thác thực tế phụ thuộc module/data/routes/plugin đang bật.

| ID / mức | Phát hiện và bằng chứng | Ảnh hưởng / ranh giới xác nhận |
|---|---|---|
| S01 **High** | `inc/license-api.php`: cả bốn route permission_callback __return_true; usage/update-tokens chỉ tìm license bằng key rồi ghi số đếm, nhận domain nhưng không kiểm binding/status/expiry; counters cast int, không chặn âm, không signature/rate limit. activate có thể ghi status active mà không gate suspended/expiry. | Người biết valid key có thể đặt counter/thay state ngoài policy mong muốn. Public route tự nó không là lỗi; thiếu protocol authorization/integrity trong callbacks là vấn đề. Không gọi endpoint để khai thác. |
| S02 **High** | `az_api_verify_license()` gọi **`az_get_plan_limit()` không guard**, không có definition trong theme. Trả customer name/email khi verify có key; còn có nhánh bind domain khi chưa bound và update expired status. | Verify không chỉ read-only và có thể fatal nếu thiếu provider ngoài. Chưa xác nhận provider ngoài có cài hay không; đừng đưa route này vào health-check vô hại. |
| S03 **High** | `az9s_dynamic_lead_submit()` public guest AJAX: valid form+public nonce+honeypot rồi xử lý **mọi `$_FILES`**, media_handle_upload, max5MB/file; chưa đối chiếu field name/filetypes config server-side, không throttle/request-total limit. | Có nguy cơ upload/spam/storage/mail abuse khi form được công khai. WordPress MIME validation vẫn áp dụng; **không kết luận arbitrary PHP execution**. Rule images-only/upload-limit nếu bật có thể giảm tập file nhưng không thay thế schema/rate limit. |
| S04 **Medium** | Contact CAPTCHA trả cả token và code qua HTML/refresh JSON; guest handlers không có rate limit tương ứng auth limiter. Legacy az9s_lead nonce+required nhưng không CAPTCHA và không kiểm insert result trước success. | CAPTCHA chống bot yếu; spam/DB/mail load và false success. Nonce không phải chống bot hoặc quyền riêng từng guest. |
| S05 **Medium** | Google/SMTP secrets plaintext options; legacy SMTP UI đưa password saved vào value của input password; legacy debug SMTP log; license verify log có license key khi expiry. | Cần bảo vệ DB/admin/logs và không xuất secrets khi migration. Không có hardcoded credential thực được xác nhận từ source. |
| S06 **Medium** | `performance-security.php` có switch chặn mọi guest REST trừ filter exception; ẩn purchase UI không chặn endpoint/cart server; own-content lọc admin/REST không bảo vệ public media URL. | Không quảng bá các switch UI như ACL tổng; bật guest REST restriction có thể làm gãy Woo Store API/integration. Runtime cụ thể chưa test. |
| S07 **Medium** | Nhiều legacy term/menu save callback không có local nonce/cap check (`category-images`, `term-language`, `menu-icons`); thường được gọi từ workflow core đã authorize. | Không suy ra CSRF đã khai thác chỉ từ thiếu check trong hook. Cần test các entrypoint thật, thêm validation defense-in-depth ở phase riêng. |
| S08 **Low** | Nav legacy bilingual branch gán lại `$bilingual`, làm mất attributes đã build; language JS dùng textContent có thể bỏ icon/nested markup trong data-vi/en. | Legacy menu/language behavior có regression risk; Child mega walker core hiện giữ title/attribute filters. |

Các điểm bảo vệ tốt đã nêu ở mục 5 cần được giữ khi wrapper/refactor; không thay thế bằng sanitizer đơn giản hơn. Auth limiter dùng transient read-increment-write, best effort chứ không atomic flood protection.

## 9. Performance và accessibility ở mức source

| ID / mức | Finding có implementation cụ thể | Hướng đánh dấu cho tương lai |
|---|---|---|
| P01 **High** | `AZ_Analytics::track_view/track_click()` đọc rồi ghi lại toàn option mỗi request/click, không atomic, views_daily/event keys không retention/cap. Click beacon trên mọi link/button; page cache có thể bỏ qua PHP view tracking. | Analytics module optional + bounded/atomic storage sau; hiện không thể coi counter chính xác đồng thời/cache. `online=5` không phải measurement. |
| P02 **Medium** | Tailwind runtime CDN + config head và Font Awesome toàn site; local build output không được gói/enqueue. | Asset Registry/build pipeline ứng viên; chưa đo CPU/network hoặc download CDN trong audit. |
| P03 **Medium** | Giá/product-card CSS toàn site; Child steel/header/footer/mega/SOVIBAN toàn site; Parent default compatibility/map styles load rộng. | Module/context gate; header/footer chung hợp lý nhưng home/legacy rules trong các file lớn có thể tách. Không mặc định mọi CSS toàn site là sai. |
| P04 **Medium** | Parent home tạo query cho mỗi product tab dù hidden, rand suggested, has-products queries lặp; category price cache clear dùng posts_per_page -1; lead select_posts cũng -1. | Lazy panel/batched invalidation/cache/index theo module, cần benchmark data thật. |
| P05 **Medium** | `az_create_lead_table()` dbDelta mọi admin_init; `az_ensure_woocommerce_pages()` mọi admin_init kiểm 5 system pages và có thể sửa title/content trống/tạo Page; upgrade AZ9S còn SHOW TABLE/COLUMN trên submit. | Lifecycle/schema version gate sau; không chạy tự động chỉ để mở admin. |
| P06 **Medium** | `AZ_Blog::query()` áp cho main archive rộng, chỉ exclude shop/product_cat; brand/warehouse/product attributes có thể đi qua; Woo callback khác có thể set lại posts_per_page cùng priority. | Xác nhận thứ tự query WP/Woo thật, không suy ra toàn bộ product taxonomy đã được exclude. |
| P07 **Low** | Logo variants JS scroll/resize query headers và refresh logo; sticky script scroll query DOM; mobile body_class gọi render menu rồi header render lại. | Cache DOM/memoize/condition load ở phase tối ưu, không thay hiện tại. |
| P08 **Medium** | Source CSS nhiều lớp container/blog/unified/Child patches, breakpoints 640/720/767/782/900/960/1100; source có !important dù docs đặt nguyên tắc tránh. | Chuẩn hóa token/cascade từng component; không search-replace hoặc xóa rules chỉ dựa tên. |
| P09 **Low** | Hash_file một số asset mỗi request; inline CSS/JS ở theme-setup/customizer/comments/contact/product/leads không cache riêng; media import opt-in có nhiều network fetch khi save. | Version/build cache, extract engine khi preserve DOM; đặt save time budget khi thiết kế module media. |

Autoload: Advanced, analytics, AZ9S forms/SMTP/status phần lớn truyền false; không có DB nên **Chưa xác nhận** tổng autoload bytes hoặc option thực đang autoload. Homepage/schema/lead forms/custom-code/analytics có khả năng lớn theo dữ liệu, không có số đo để kết luận vượt ngưỡng.

Accessibility đã có nền nhưng chưa đạt kết luận WCAG:

- Product lightbox inline trong Parent/Child single-product không có dialog role/focus trap/focus return; gallery Page có role dialog/Escape/arrows nhưng không quản lý focus đầy đủ, ảnh phóng lớn không lấy lại alt từ gallery source.
- Product tabs có role tab/tabpanel/aria-selected nhưng thiếu aria-controls/labelledby/roving-tabindex/arrow keyboard navigation chuẩn; `wp_kses_post` trên tab content có thể loại iframe/SVG của plugin. UI “expand” không phải tabs engine hoàn chỉnh.
- Mobile focus loop chỉ chọn a/button; Child nhét search input vào panel, nên focus loop không bao quát mọi focusable. Cần browser test focus order thật; không báo PASS từ mock tests.
- Repeater drag chưa có keyboard reorder; sticky aria-hidden cần đối chiếu focusability qua CSS/browser; legacy carousel homepage không có các pause/reduced-motion guard như engine SOVIBAN/shared banner.
- Skip link Parent tự đổi target main, Child thêm skip riêng; kiểm nội dung/heading/duplicate IDs với templates thực trong phase test. `page-gallery.php` không có main landmark trong markup riêng.

## 10. Test và docs

Source có **32 test PHP, 4 test CJS và 1 checklist Markdown** trong hai thư mục tests. Có thêm 2 công cụ CJS dưới docs/tools và tailwind config CJS. Không có test chạy trong production bootstrap.

- Parent test groups: auth limiter, back-to-top PHP/JS, blog image descriptions, branding SEO, installer, contact/leads/maps, custom code, editor, Google login, header bottom, minify, media tools, mobile PHP/JS, neutral-page/admin cleanup, performance/security, reusable primitives, shared banner JS, upload limits, user options, QA string checks.
- Child: footer location/sync, mega menu, header settings, home admin/partners, neutral inherited, product ratio/permalinks, social contact, SOVIBAN fixtures.
- Một số test stub WordPress/DOM, số khác require core WP dependency/walker/rewrite/html API từ layout `wp-content/themes`. Workspace hiện `themes/themes` không có core files theo đường dẫn đó. Test installer còn tạo file fixture, không nên chạy như source read-only check chưa chuẩn bị môi trường.
- `shared-banner.cjs` require jsdom và php-parser nhưng package.json devDependencies chỉ có asset packages; không có lockfile/test script tái lập đầy đủ. QA CJS kiểm chuỗi không thay integration/render/save tests.
- Test/API coverage cho license auth/counters/expiry, negative cases guest upload/abuse, DB migrations/rollback, Woo plugin compatibility và browser a11y thực **Chưa xác nhận**.

Docs có nhiều giá trị nhưng trộn lịch sử và hiện trạng. Ví dụ `docs/ARCHITECTURE.md` còn mô tả file contact-settings/About fields và chỉ một Woo override; source hiện khác. `docs/DATABASE.md` nhắc About schema cũ trong khi module hiện không nạp; `docs/audit/*` là snapshot cũ, `docs/SECURITY.md` cũng ghi chú không còn phản ánh source mới. README Child và docs SOVIBAN/Neutral mô tả migration frontend gần đây. **Source hiện hành được ưu tiên hơn docs trong mọi kết luận audit này.**

Danh mục bằng chứng test/docs: [TEST-SOURCE-INDEX.txt](TEST-SOURCE-INDEX.txt), [DOC-SOURCE-INDEX.txt](DOC-SOURCE-INDEX.txt). Đây là index để review, không chứng nhận test đã pass.

## 11. Các rủi ro migration tuyệt đối không xử lý bằng rename trực tiếp

| Mức | Contract phải bảo vệ |
|---|---|
| **High** | `Template: az-theme`, Parent/Child slug, theme_mods_{stylesheet}, custom_css posts và một lần migration flag. Child mẫu không đại diện Child thật. |
| **High** | `_az_home_page_data` và `_az_shared_banner` shape/unknown branches/empty semantics; footer toàn site đọc meta Page trang chủ; `page_on_front`/template filename giữ nguyên. |
| **High** | PHP function pluggability, contact require sớm, callback identity/priority, registry ready trước Child extension; không thay method/hook không alias. |
| **High** | License CPT/status/expiry aliases/API namespace/protocol/plugin option/table integration; phải harden theo protocol có migration, không xóa endpoint âm thầm. |
| **High** | Hai leads tables và hai SMTP config, shortcode form IDs; giữ records trước khi unify, không bỏ bảng legacy vì UI ẩn. |
| **High** | Child product URL policy, reserved path/category collision, redirect 301 query preservation, rewrite marker/version. Không flush/delete rewrite tùy tiện trong audit. |
| **Medium** | `az-*` handles, script dependency/inline handle, DOM data attrs, CSS variables/menu classes; Child phụ thuộc sâu dù PHP signature giữ nguyên. |
| **Medium** | Video typo `ifame`, cp_/wv_ prefixes, sidebar IDs/widget data, legacy option/mod fallbacks, signed custom-code source. |
| **Medium** | Woo order/cart/checkout/account APIs/hooks/version; các templates không đủ standard hooks như plugin kỳ vọng, chưa có live matrix. |

Không có đề xuất “Delete API cũ”. Thành phần chưa tốt được xếp **Legacy / cần wrapper** hoặc module adapter, kể cả file ít reachability trong source hiện tại; consumer bên ngoài bộ theme này **Chưa xác nhận**.

## 12. Kiến trúc đích tổng quan — chỉ đề xuất

```text
Child cũ → Legacy Compatibility Layer → Flexora Engine
Child mới → Flexora Public API        → Flexora Engine

Engine:
  lifecycle + module/dependency registry
  options/data adapters + versioned migration boundary
  field primitives + renderer/sanitizer/validator registry
  template resolver + Template Map schema contract
  asset registry trên WordPress handles
  shared components + Woo integration module

Module opt-in / application adapters:
  Leads/SMTP, Auth/Google, Analytics, Content AI/License, price policy

Child:
  branding + skin + page HTML/CSS + template map riêng + site workflow thực sự
```

Tái sử dụng code có triển vọng nhất:

- Core helper/data resolver và fallback adapters; không đổi DB key để đổi thương hiệu.
- Field primitives/Advanced merge/media/color, banner editor và Child repeater/gallery patterns; bổ sung schema/storage/versioning sau khi duyệt.
- Header/mobile/sticky/contact/back-to-top, product card/price/video/image helpers; carousel/gallery/tabs từ Parent/Child chuẩn hóa qua wrapper giữ DOM cũ.
- Template neutral/About kế thừa đang hoạt động theo hierarchy; mở rộng resolver nhất quán sau, không copy lại vào mỗi Child.
- Installer nguyên tắc không overwrite và importer explicit preview+backup; nâng cấp lifecycle riêng, không chạy migration tự động trong audit.

Template Map sau này cần field ID ổn định và schema tương ứng HTML/template; text/editor/repeater/media/URL/toggle phải có ownership+storage rõ, migration khi đổi map, và không xóa data khi field bị ẩn/retire. AZ hiện có **các phần nền**, chưa có engine map này. Phase 01 không tạo implementation hoặc skeleton kiến trúc mới.

## 13. Trạng thái kết thúc

Báo cáo và các phụ lục đã xuất ngoài source theme. Không refactor, đổi tên theme/prefix, sửa key/DB, di chuyển/xóa code, thay behavior, build asset hoặc commit. Không tự bắt đầu Phase tiếp theo. Chờ người dùng review báo cáo.

**PHASE 01 AUDIT COMPLETED — NO SOURCE CHANGES MADE**
