# Testing baseline

## 2026-10-05 — Back-to-top visibility repair

Back-to-top PHP suite đạt 25 kiểm tra, gồm CSS riêng/hash version, SVG/native anchor và dependency thực của WordPress khi Performance tắt parent style.css. JavaScript suite đạt 17 kiểm tra, thêm trạng thái trước khởi tạo, trang ngắn, fallback scroll offset, resize và pageshow. Chưa xác minh trực tiếp website báo lỗi; không có URL frontend trong yêu cầu. Khi đưa bản sửa lên website, cần có file mới `css/back-to-top.css` và xóa cache trang nếu có.

## 2026-10-05 — Footer back-to-top controls

`php -d error_reporting=32767 wp-content/themes/az-theme/tests/back-to-top.php` đạt 20 kiểm tra cho cài đặt Footer, tương thích General cũ, bật/tắt, vị trí trái/phải, giá trị không hợp lệ, Reset Section, enqueue và bỏ nút trùng ở child footer. `node wp-content/themes/az-theme/tests/back-to-top.cjs` đạt 9 kiểm tra cho hiện/ẩn theo cuộn, cuộn lên đầu, reduced motion và khởi tạo DOM. Chưa kiểm thử trực tiếp trên website/trình duyệt.

## 2026-10-05 — Style 5 layout repair

PHP Mobile suite đạt 39 kiểm tra (thêm SVG stroke, CSS riêng/đúng dependency, version hash nội dung và không tải override Kiểu 5 cho Kiểu 4 hoặc khi menu tắt); JavaScript suite đạt 33 kiểm tra. PHP lint, Header bottom/reusable regression, QA và `git diff --check` đạt. CSS đã rà soát cascade, phạm vi Mobile và panel content-height; chưa xác minh render trên trình duyệt hoặc website đang chạy. Không chạy lại truy cập localhost đã bị từ chối trước đó. File mới `css/mobile-menu-brand-dropdown.css` cần nằm trong gói cập nhật theme.

## 2026-10-05 — Mobile menu style 5

`php -d error_reporting=32767 wp-content/themes/az-theme/tests/mobile-menu.php` đạt 31 kiểm tra; `node wp-content/themes/az-theme/tests/mobile-menu.cjs` đạt 33 kiểm tra, gồm style 4 và style 5. Kiểm tra mới bao gồm sanitizer/class riêng, dùng lại logo/menu, menu con hai cấp, ID/nhãn nút, aria-expanded, mở/đóng nhánh độc lập, loại menu thu gọn khỏi vòng Tab, đóng bằng liên kết/Escape/nền phủ và đổi breakpoint. Đây là test cô lập với API WordPress/DOM giả lập, không kiểm thử layout trình duyệt hoặc database. PHP/Node syntax, Header bottom, reusable settings, upload limits, QA và `git diff --check` đạt. Quyền browser localhost đã bị từ chối trong phiên này; không chạy lại hoặc báo đạt visual/live WordPress validation.

## 2026-10-05 — Mobile menu style 4

`php -d error_reporting=32767 wp-content/themes/az-theme/tests/mobile-menu.php` đạt 23 kiểm tra render, logo dự phòng, menu selection, sanitizer và tránh ẩn header khi menu thiếu/tắt. `node wp-content/themes/az-theme/tests/mobile-menu.cjs` đạt 18 kiểm tra hành vi đóng/mở, Escape, focus/Tab, link/menu con, khóa cuộn, đổi breakpoint và hồi quy ba kiểu cũ. JavaScript dùng DOM giả lập; không phải kiểm thử trình duyệt thật. PHP lint, Node syntax, hồi quy Header bottom/reusable settings/upload limits, `tests/qa.cjs` và `git diff --check` đạt. Child theme không bị sửa. Bản xem thử trình duyệt bị chặn do quyền truy cập localhost bị từ chối; chưa xác minh hình ảnh thực tế hoặc lưu cài đặt trên WordPress đang chạy. Ma trận staging ở [HEADER-ARCHITECTURE.md](HEADER-ARCHITECTURE.md).

## 2026-10-05 — Upload file size limit

Kiểm thử cô lập `php -d error_reporting=32767 wp-content/themes/az-theme/tests/upload-limits.php` đạt 28 kiểm tra: mặc định/giá trị lỗi, biên dưới/bằng/trên giới hạn, giới hạn máy chủ/network thấp hơn, MB thập phân, giữ lỗi upload trước đó, kiểm tra file tạm khi thiếu/khai sai dung lượng, sanitizer/reset và render trường số MB. Test gọi handler upload/sideload thật của WordPress cho trường hợp từ chối file; API option/filter/giới hạn PHP được giả lập, không bootstrap database. PHP lint và regression Performance, Performance Security, reusable settings cùng `git diff --check` đạt. Chưa kiểm thử lưu cài đặt và tải file trong trình duyệt/REST trên WordPress đang chạy; xem ma trận trong [PERFORMANCE-SETTINGS.md](PERFORMANCE-SETTINGS.md).

## 2026-10-05 — Child theme installer

Đạt 28 kiểm tra trong `php wp-content/themes/az-theme/tests/child-theme-installer.php`, kể cả khi bật `error_reporting=32767`. Test dùng core filesystem và thư mục tạm, giả lập API option/theme/CSS; không bootstrap WordPress/database. PHP lint đạt cho bootstrap, installer, dashboard, starter child và test. Regression `tests/reusable.php`, `tests/performance.php` và `git diff --check` đạt. `git diff --exit-code -- wp-content/themes/az-theme-child` xác nhận child website hiện có không bị sửa. Ma trận staging chưa chạy, xem [CHILD-THEME.md](CHILD-THEME.md).

## STEP 16

Lệnh đã chạy: `node --check wp-content/themes/az-theme/inc/modules/homepage/homepage.js`, `node wp-content/themes/az-theme/docs/tools/check-homepage.cjs 34352bc9`, `git diff --check`. Ref 34352bc9 chứa front-page trước STEP 16; không dùng HEAD sau khi đã commit phần tách template. Script không có ref chỉ kiểm tra cấu trúc hiện tại.

JavaScript syntax, module template allowlist/completeness, duplicate new symbols and whitespace checks run. Original banner/tab JS and original product helper bodies compared with pre-change source. No PHP executable or installed browser test runner identified; PHP lint, live WordPress save/render and Woo ON/OFF tests not run. Explicitly test stable order, false/0/empty overrides, missing/malformed options, merge preserving other branches, disabled queries, custom page content/password and desktop/mobile grids on staging. Historical hash checks below apply only to STEP 00.

## Đã thực hiện trong STEP 00

- Quét đệ quy toàn theme (trừ docs/.git/node_modules), ghi inventory file/size/SHA256 và phân loại PHP/CSS/JS/template/override.
- Thu thập API callsites và biểu thức động; đối chiếu rg cho AJAX/REST/CPT/taxonomy/cron/includes và các điểm rủi ro chính.
- Quét declaration trùng tên: các ứng viên lặp start_lvl/end_lvl/start_el/end_el thuộc các nav walker khác nhau; refreshCaptcha là JS nhúng ở page khác nhau. Không coi là duplicate PHP global function. Chưa dùng PHP AST nên không khẳng định không có mọi lỗi compile.
- Node syntax check cho công cụ audit; chạy tạo tài liệu và kiểm tra output.
- Kiểm tra hash toàn bộ source trước/sau audit và git diff whitespace: chỉ docs thay đổi trong STEP 00.

## Chưa thực hiện

Không tìm thấy php trong PATH khi audit. Không sửa PHP nên PHP lint không áp dụng cho changed PHP, nhưng toàn theme chưa được lint. Không bootstrap WordPress (tránh DB side effects), không test frontend/admin browser, không đổi trạng thái WooCommerce, không gọi API/email/network, không chạy cron.

Không báo PASS cho Woo OFF, form lưu thật, migration hoặc pixel/render test. Header `Tested up to` không thay thế kiểm thử.

## Chạy lại audit

```powershell
node wp-content/themes/az-theme/docs/tools/audit-source.cjs
node --check wp-content/themes/az-theme/docs/tools/audit-source.cjs
git diff --check
```

Script tạo lại audit/*.md, audit/source-inventory.json và OPTIONS-SCHEMA.md; không sửa runtime file. Đây là scanner lexical, không phải parser PHP: đọc context ở callsite để phân biệt comment, JS nhúng và dynamic registration. Những key tính động không thể liệt kê giá trị runtime từ source; biểu thức được giữ đầy đủ trong JSON.

## Ma trận staging trước các STEP chức năng

| Area | Cases | Baseline |
| --- | --- | --- |
| Bootstrap | Parent/child theme, PHP supported, includes order, duplicate symbols | Not run |
| Woo OFF | No plugin và deactivate; home/page/blog/search/header/footer/admin/AJAX | Not run |
| Woo ON | Archive/taxonomy/detail/cart/checkout, fragments, price multiplier, variation cache | Not run |
| Contact admin | Existing values, option/mod fallback, missing fields, nonce/quyền, email/URL lỗi, media/accordion/mobile | Not run |
| Contact public | CAPTCHA expiry/reuse, save lead, SMTP error/success, limits | Not run |
| API/license | Verify/activate/counters auth, missing external helpers, cron expiry | Not run |
| Content/admin | Pricing/license/about/product metabox saves, autosave/revision, term/menu fields | Not run |
| Frontend | VI/EN, menus, responsive screenshots, CSS old/new selectors | Not run |
| Options future | Dot notation missing/null/false/0/empty, invalid stored type, zero DB writes on read | STEP 01 planned |
