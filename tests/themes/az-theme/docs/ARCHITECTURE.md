# Architecture

## Hiện tại

WordPress nạp [functions.php](../functions.php), kiểm tra ABSPATH rồi require_once tuần tự 19 file trong `inc/functions/`. Các file đăng ký hooks và khai báo helper trực tiếp; chưa có dependency registry hoặc cơ chế tắt toàn module.

Thứ tự: security, woocommerce, theme-setup, pricing, licenses, admin-settings, contact-settings, contact-form, integrations-access, translation, term-language, home-products, customizer, category-images, menu-icons, product-attributes-pricing, product-fields, content-media, product-videos.

`integrations-access.php` nạp `inc/license-api.php`. `customizer.php` nạp `inc/gioi-thieu-page-fields.php`, kiểm tra tồn tại trước nạp `inc/service-areas-settings.php` (file này đang thiếu). Một số hàm media/lead nạp WordPress admin includes khi cần. Danh sách include đầy đủ, kể cả biểu thức động/comment ứng viên, nằm trong [inventory](audit/FILES-AND-PREFIXES.md).

Template hierarchy dùng PHP root: front-page, page và các page template riêng, archive/single, archive-product/single-product, taxonomy sản phẩm, searchform, comments, 404, header/footer. Template parts trong `template-parts/`. Override theo đường dẫn WooCommerce duy nhất là `woocommerce/cart/cart.php`; template sản phẩm ở root là phần tùy biến bổ sung, không phải file nằm trong override directory.

## Parent và child theme

`az-theme` là framework dùng chung. Parent giữ option API, admin, helper, blog, WooCommerce và các template dự phòng trung tính. `az-theme-child` chứa phần thay đổi theo website: header, footer, trang chủ, schema trường trang chủ, các partial cùng CSS/JS tương ứng. Asset trong parent dùng đường dẫn parent rõ ràng; asset có thể ghi đè dùng `get_theme_file_path()` và `get_theme_file_uri()` để ưu tiên child. Template trang chủ tùy biến được tìm bằng `locate_template()`.

## Assets và admin

- `style.css` dùng chung frontend; `css/page.css` nạp khi is_page; `container-sync.css` vẫn nạp cùng style.
- `theme-setup.php` nạp Font Awesome và Tailwind CDN cùng script ngôn ngữ. Vẫn có CSS/JS nhúng trong PHP (menu, logo, admin, templates).
- `css/admin.css` và `js/admin-contact.js` được screen-gate tại trang `az-contact-settings`; chưa áp dụng cho toàn bộ admin.
- `login.css`, nhóm language/mobile/product/catering asset tồn tại; tồn tại file không đồng nghĩa asset được enqueue ở runtime. Xem callsites để xác định owner.
- Contact schema + renderer + save ở `contact-settings.php`; các màn hình lead/SMTP/custom code vẫn ở `admin-settings.php`. Options đọc trực tiếp và theme_mod song song.

## CSS architecture

Frontend được scope bởi wrapper `<div id="page" class="az-site">`. Component dùng class độc lập như `.az-header`, `.az-main`, `.az-footer`, `.az-container`, `.az-product-card` và BEM element như `.az-product-card__title`. Page scope nằm trên nội dung, ví dụ `.az-shop-page`, `.az-product-page`, `.az-cart-page`, `.az-blog-page` và `.az-single-post`.

Admin bootstrap now loads `inc/admin/` only when `is_admin()`. `AZ_Admin` owns the minimal top-level AZ Theme menu, `AZ_Dashboard` renders navigation shortcuts, and `AZ_Advanced` renders registry-defined sections and saves the nested `az_theme_options` option. Homepage section/order management remains owned by `inc/modules/homepage/admin.php` under `themes.php?page=az-homepage`, so Homepage is intentionally not duplicated in Advanced. Advanced assets use the `az-theme-admin` handle and are screen-gated; media/color dependencies load only on Advanced. Legacy standalone admin settings remain loaded in their original order.

Biến theme được khai báo tại `:root` với prefix `--az-*`. `body_class()` vẫn tồn tại để tương thích WordPress/plugin và debug, nhưng CSS mới không dùng body class làm scope. CSS legacy `body.az-storefront ...`, `.az-*` và các selector cũ được giữ tạm thời ở compatibility layer trong quá trình migration.

## Kiến trúc đích (chưa triển khai)

```text
functions.php -> inc/bootstrap.php -> inc/core/class-theme.php (AZ_Theme)
inc/helpers/       options.php, utilities.php
inc/options/       registry, sanitizer, options API
inc/admin/         registry-driven settings UI
inc/modules/       optional feature modules + README
inc/compatibility/ legacy contracts/fallbacks
inc/woocommerce/   dependency-gated WooCommerce features
assets/            managed frontend/admin assets
```

Chưa có `theme.json`, bootstrap coordinator, options registry hoặc module manager. STEP 01 phải bao lấy loader hiện tại trước; không đổi thứ tự hook/include hoặc tạo plugin core. Tách tài liệu khỏi thực thi để audit không kích hoạt cron/dbDelta/seed dữ liệu.

## STEP 16 — Homepage

Loader hiện có thêm `inc/modules/homepage/module.php` sau 19 requires cũ. Module dùng một adapter đọc `az_theme_options` (ưu tiên helper shared nếu có sau này), chỉ ghi nhánh homepage qua admin-post; không giả lập toàn bộ options framework. `front-page.php` giữ helpers/query và include template allowlist theo thứ tự settings. Legacy banner/tab JS chuyển thành asset front-page. Admin riêng tại Appearance > Trang chủ, CSS dùng chung az-admin. Xem [README module](../inc/modules/homepage/README.md). Các STEP 01–15 vẫn chưa triển khai.

## Cập nhật 2026-10-05

Đã gỡ Prompt Execution API và các endpoint `az-prompt/v1` khỏi theme. License API khác được giữ nguyên. Thư mục `audit/` là snapshot lịch sử, không phản ánh code hiện tại.
