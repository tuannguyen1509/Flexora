# Legacy Compatibility Plan — az-v1

Đầu vào normative: [Phase 01 contract](../audit-phase-01/PARENT-CHILD-CONTRACT.md), [data inventory](../audit-phase-01/DATA-COMPATIBILITY.md), [PHP callsites](../audit-phase-01/CHILD-PARENT-CALLS.tsv), [hooks](../audit-phase-01/HOOKS-SHORTCODES-REST.txt), [assets](../audit-phase-01/ASSET-CALLSITES.txt). Plan không đổi source hiện tại. “Legacy / cần wrapper” không đồng nghĩa có thể bỏ functionality.

## Packaging và phạm vi hỗ trợ

`az-bridge` giữ physical `az-theme`, Child Template header không đổi; native `flexora` không thể tự nhận Child cũ bằng PHP alias. Hai package cùng engine, một package active; không hai bộ hooks/module chạy song song. Khi nâng site cũ, compatibility profile là manifest explicit được operator chọn sau inventory, không detect bằng substring tên Child.

Bridge support mặc định **không hết hạn** cho đến khi có inventory consumer + replacement path + notice release rõ. Chưa biết consumer ngoài bundle → không loại API. Shim file/function/class load không có permission biến read thành write. Giữ legacy behavior trước, security changes là versioned operation/release riêng có gate, không hợp thức hóa lỗ hổng bằng nhãn compatibility.

## Adapter PHP cụ thể

| Legacy surface | Cách hoạt động dự kiến | Điều kiện compatibility |
|---|---|---|
| `az_get_options`, `az_get_option`, `az_array_get` | Global wrapper gọi `AzOptionsBinding` đọc **az_theme_options**, áp lookup semantics cũ; không normalize dữ liệu tại read | giữ null/false/0/empty và default như implementation gốc, không dùng v1 option helper trực tiếp nếu semantics khác |
| `az_get_contact_items`, `az_get_phone`, `az_get_email`, `az_get_address`, company/general/location helpers | Family-specific resolver giữ thứ tự legacy và shape | phone/email list rỗng vẫn legacy fallback; az_get_email không tự chuyển sang repeater; storefront phone vẫn option/mod cũ |
| `az_get_social_links` | Wrapper normalize old mapping rồi escape URL cho display như cũ | explicit empty key ngăn fallback; Child decode/escape lại vẫn hoạt động |
| `az_get_logo_id/url`, `az_get_home_h1`, font helpers | Legacy branding/font binding + old fallback chain | logo zero/default/custom_logo và SOVIBAN fallback không được âm thầm đồng nhất |
| `az_header_options/default_options/menu_id`, `az_product_category_menu_id` | Return full array defaults+saved unknown keys, preserve name/slug resolution | Parent vs Child category selection order giữ theo caller adapter, không thay một rule chung |
| `az_render_header_logo/action/menu/sticky`, `az_render_mobile_menu`, mobile markup/style | Wrapper gọi component với **view profile az-v1** và DOM cũ | echo/return giữ đúng; mobile render-once guard; native views không thay DOM legacy |
| `az_footer_options/default_options` | Defaults+saved merge, unknown retained | show_top_links/show_social/show_copyright và back-to-top fallback |
| `az_shared_banner_*`, shared breadcrumb | Explicit post/term binding; old layout mapper, view profile | only fallback when new meta not array; Child hero ignoring layout none vẫn thuộc Child contract |
| `az_render_product_card` và `az_storefront_*` | Product module façade + az-v1 view/price/action adapter | object hoặc ID, context/tag/classes/image_size/show_actions, saved $GLOBALS product restore, discount semantics giữ |
| `az_get_product_video_urls`, `az_product_youtube_*` | Old key family reader + canonicalizer | đọc typo ifame, mới+cũ dedupe; không rewrite meta |
| `az_contact_assets/icon/render_contact` | Stable global function wrapper + early file shim | Callback identity vẫn global string, không tự hook method nội bộ song song |
| `az_track_recently_viewed_product`, `az_product_extra_info_meta_box_html` | `function_exists` declaration guard giữ; Parent hook tiếp tục đăng ký string function đã resolve | Child khai báo trước thắng. Native registry extension không đè Child cũ |
| `AZ_Options_Registry::get/add_field/add_section/all`, `AZ_Theme_Options::*` | Global class façade forwarding tới legacy mutable registry; get trả array như cũ, add_section thay cả section | Không class_alias tới new immutable registry. Registry cho Child sửa tại after_setup_theme20/25, compile sau đó; chỉ registered legacy sections được old admin save |
| `AZ_*` core/admin classes, walkers còn lại | Inventory-based method/constructor shim hoặc giữ implementation trong adapter trước khi thay | Không claim auto class_alias an toàn cho class tự gắn hook trong constructor; hook ledger ngăn duplicate instance |
| Language/shortcodes/widgets | Global az_t/az_theme_mod/translation + shortcode/parser/sidebar adapters | tags az_contact/az_general/az_google_map/az_stats/google-login/az_google_login/az9s_lead_form và Flatsome section/row/col/ux_sidebar/ux_products/blog_posts giữ; sidebar IDs giữ |

Các symbol không có Child call trực tiếp vẫn nằm inventory bảo tồn; mapping cụ thể trước implementation lấy `SYMBOLS.txt` và source callback/signature. Không chấp nhận policy “unmapped → delete”. Unknown legacy symbol consumer là release gate, không tạo wrapper guessed arguments.

## Load order và hook ledger

1. Giữ `functions.php`, `inc/bootstrap.php` entrypoint và **`inc/floating-contact.php` tại đường dẫn cũ** trong az-bridge. Child require contact trong functions-first phải nhận declarations và callback strings ngay lúc đó. Các declaration chỉ phụ thuộc lazy service access; body functions dùng sau boot, không cần full kernel khi require sớm.
2. Early file gắn `az_contact_assets` ở wp_enqueue_scripts35 và `az_render_contact` ở wp_footer5 **đúng một lần**. Child remove_action ngay sau require phải có hiệu lực. Parent include sau bằng require_once/ledger không reattach callback bị Child tháo. Không `ensure_registered()` sau seal cho legacy này.
3. Hai pluggable functions do Child định nghĩa trước vẫn được hook Parent sử dụng. Tránh “wrapper đã khai báo trước Child” vì WordPress nạp Child trước; bridge không cài plugin loader để đảo thứ tự.
4. Trong admin, Advanced/legacy section arrays sẵn trước Child callback priority20; SOVIBAN defaults priority25. Seal tại40. Registry translation sang new engine giữ all unknown data, field types unsupported chạy old renderer profile thay vì đổi thành text mới.
5. User-options tháo bốn restriction legacy tại after_setup_theme0 vẫn chạy trước callback priority10. Không giữ cả old/new restrictions rồi duplicate redirect.
6. Homepage page-fields **của Child** vẫn được locate/include qua Parent ở thời điểm cũ. Không nhận `az_home_page_*` là Parent chỉ vì prefix. Three writer passes10→30→40 giữ trong legacy mode; new Map writer không đồng thời ghi cùng branches.

Hook ledger ghi `{hook, callback identity, priority, accepted_args, owner, profile, registration phase}`. Gắn hooks native và legacy theo ownership, không auto rebind từ callback string sang closure. Callback từng bị remove không tự sinh lại trong request. State table được diagnostic redacted, không public mutable API.

| Hook contract cụ thể | Adapter event dispatch |
|---|---|
| `az_after_site_header` | az-v1 view vẫn gọi hook cũ đúng chỗ: Parent sau mobile/sticky; Child không gọi khi home. Native header gọi event coordinator; coordinator phát old5 breadcrumb/old10 banner chỉ khi profile yêu cầu và chưa dispatch. Native consumers nhận event v1, không duplicate same renderer ở hai hooks |
| `az_home_meta_description_context` | Native context tính trước, rồi legacy filter tại điểm tương đương; Child mở rộng page-template front-page.php vẫn tác động vào legacy SEO |
| `save_post_page` | Giữ nonce names, POST names, priorities10/30/40 trong legacy mode; không chạy native map.save cho cùng owner field |
| `nav_menu_item_title` | core Walker vẫn chạy icon filter cp_menu_item_add_icon; new menu renderer muốn giữ legacy phải delegate WP core, không tự escape bỏ icon markup |
| `wp_nav_menu_args` | Giữ exact `az-steel-menu`, `az-sticky-menu` để Child walker attach; mobile/top/category không bị đổi walker |
| `woocommerce_product_get_image:20` | Same product get_image pipeline, Child full-image ratio policy còn nhận filter |
| Other AZ filters | signatures/priority at source preserved cho security headers, guest REST exception, Google allow-login, analytics online, classic-editor types, import-images, missing cleanup, minify, 404 redirect, storefront labels, duplicate action |

## Storage, mods, path, route adapters

| Loại | Adapter rõ ràng |
|---|---|
| Options/meta | Data bindings tới original physical keys (`az_theme_options`, `_az_home_page_data`, `_az_shared_banner`, product/term/user families); leaf mapping không rewrite unknown branches. Single writer per binding, old vs native mode explicit |
| Theme mods | Read theo explicit source stylesheet identity, không global `get_theme_mod` mù khi migrate slug. `nav_menu_locations`, custom_logo và unknown mods giữ; không bật lại cờ az_child_parent_mods_migrated để import lần hai |
| Custom CSS | Snapshot CSS post và stylesheet linkage; native migration tạo CSS post riêng cho destination sau preview. Không copy old custom_css_post_id vào stylesheet mới. Child thật và mẫu khác nhau → inspect actual linkage |
| Template filenames | bridge vẫn có front-page.php/page-neutral.php/page-about.php và các named header/footer/partial marker; map logical ID tách path. Các root Woo taxonomy proxies giữ parent-only resolution trong az-v1 dù native resolver cho Child override |
| Media URLs/IDs | Adapter biết field kiểu ID, URL string, CSV IDs, JSON old lists; không global search/replace serialized values. Child asset defaults resolve package root, không import thành live URL hard-code |
| Rewrite/permalink | Child product policy tiếp tục owner trong bridge, engine không register duplicate rewrite. Giữ priority30 filters, query_var az_child_product_route, soft-flush marker và redirects/collision/excluded requests. Promote sau phải có single owner cutover và explicit flush plan |
| CPT/taxonomy/widgets | Giữ pricing/license/product_warehouse names và sáu sidebar IDs; registration đúng init/widgets_init, không rename vì branding. Plugin provider resolution trước register để tránh conflict |
| REST/AJAX/cron | Namespace az-license/v1 và action IDs giữ trong compatibility protocol; payload/status code/cookie/job names là contract. Disable/hardening là explicit plan, không ẩn mutation verify thành health-check |

## Handle aliases và DOM adapters

Không dùng alias JS/CSS “src=false + depends canonical” nếu Child cũ gọi dequeue/add_inline lên old handle: dequeue alias không chắc chặn canonical, inline order sẽ khác. **Trong az-v1, old WordPress handle là physical owner**; Flexora logical asset ID resolve thẳng sang handle đó. Native profile dùng flexora handles. Hai tên API không enqueue hai handles cùng URL. Child raw wp_enqueue/dequeue/add_inline cũ còn tác dụng trên physical handle, version/deps được giữ theo compatibility manifest. Nếu thực sự cần dual registered names, zero-src alias chỉ cho read-only consumers đã kiểm chứng, không dùng mặc định cho mutable old handles.

Các handles tối thiểu bảo tồn: `az-ai-theme-style`, `az-container-sync`, `az-content`, `az-price`, `az-product-card`, `az-woocommerce`, `az-homepage`, `az-floating-contact`, `az-mobile-menu`, `az-shared-banner`, `az-sticky-header`, `az-logo-variants`, `az-accessibility`, `az-neutral-page`, `font-awesome`, `tailwindcss`, `az-toggle-titles` và cả chain admin styles/scripts từ asset inventory. Child-owned handles không đăng ký lại từ Parent; `az-header-storefront` giữ làm inline colors attachment tại40.

DOM compatibility dùng `view_contract=az-v1`, không chỉ PHP alias:

- Giữ `#page.az-site`, `.az-site-header`, `.az-logo`, `.az-product-card__image.az-shop-card-img`, `.az-price`; giữ sticky dataset/threshold140 và old mobile classes/IDs/submenu labels.
- Giữ Parent shared-banner DOM và Child steel slider datasets bằng **hai adapter**, cùng native engine sau khi parity đạt; mỗi node chỉ init một behavior provider. Không cả old script + new init cùng node.
- Token alias map xuất giá trị tương đương cho `--az-color-*`, font/body/heading/navigation/container và `--primary-color`. Native tokens có namespace mới nhưng old profile không đổi breakpoint/cascade order âm thầm.
- Admin giữ form/input IDs/names, data-az-panel/repeater/row/add/remove/drag, `.az-settings-card--group`; renderer new dùng DOM profile cũ khi Child CSS còn phụ thuộc.
- JS globals `azApplyLang`, `azGetLang`, localized azLogoVariants, events `az:langchange`, variation sync và cookies/browser storage giữ khi profile dùng chúng. Inline configs in trước consumer, không duplicate globals.

## Release gates

Compatibility matrix bắt buộc: Child functions-first + early require + remove_action; registry default modifications20/25; both pluggable overrides; Parent-only/Child inherited neutral; SOVIBAN home/footer/banner none+empty data; legacy product/gallery/Woo ON/OFF; native API same storage; style disable+inline/dequeue; menu classes+core Walker; CSS post/mods; product URLs/feed/pagination/query params; module disable+external provider collisions. So expected outputs bằng fixtures và WP runtime, không chỉ string assertions.

Chưa được migrate: hai bảng leads, license status/counters/domain/provider table, Google subject linking, credentials, cron schedule, old option families, actual Child mods/CSS cho tới khi có runtime inventory và plan riêng. Không có migration thực thi trong Phase 02.
