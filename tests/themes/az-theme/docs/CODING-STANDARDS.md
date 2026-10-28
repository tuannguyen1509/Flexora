# Coding Standards

## Hợp đồng phát triển

Framework nằm trong Classic Theme, không tạo core plugin và không viết lại toàn bộ theme. Làm từng STEP. Không tự đổi frontend HTML, database schema, option key hoặc chức năng không thuộc STEP.

Code mới: function `az_*`, class/constant `AZ_*`, CSS `.az-*`, variable `--az-*`, option/AJAX `az_*`, REST namespace `az/v1`. Không tạo code mới `tn_`/`az_`. `az_`, `cp_`, `wv_`, `wpc_`, `login_css` và namespace REST cũ được giữ cho compatibility, không rename cơ học.

Prefix hiện tại xác nhận trong PHP theme: `az_`, `AZ_`, `az_`, `cp_`, `wv_`, `wpc_`; còn tên legacy `login_css`. `start_*`/`end_*` là methods walker, không prefix module. JavaScript có `azMobileToggle`, `azGt*`, các helper không prefix; CSS có `az-`, `az-`, `ct-`, `wv-`, `pp-`, `arc-`, `gcg-`, `pg-`, `cp-` và tên generic. Danh sách literal đầy đủ (gồm prefix của WP/plugin như `wp_`, `wc_`, `IACA_`) nằm trong audit/FILES-AND-PREFIXES.md; không đổi tên API của dependency.

`functions.php` là bootstrap. Chọn helper/module theo trách nhiệm, tránh tạo hàm hoặc hook trùng; không gọi `get_option('az_theme_options')` rải rác khi helper được triển khai. Options mới dùng nested array và dot notation; chưa áp dụng migration trong STEP 00.

## CSS/admin

Frontend và admin tách biệt. Không thêm CSS inline trong template, không selector body, không !important; các điểm legacy vi phạm chỉ sửa trong STEP phù hợp. Advanced Theme Options dùng wrapper và class `.az-advanced*`, stylesheet/handle riêng và enqueue đúng screen. Các màn hình legacy vẫn giữ `.az-admin-*` cho compatibility. Không ép style lên admin WordPress/plugin.

Giữ key dữ liệu khi sửa class; label/for, aria-describedby, focus bàn phím và mở nhóm có input lỗi phải hoạt động. Trạng thái dùng HTML native hoặc `az-is-*`. Không sao chép component CSS riêng cho từng trang.

### CSS architecture

FORBIDDEN:

- `body.az-storefront .xxx`
- `body.az-theme .xxx`
- selector nesting quá sâu hoặc phụ thuộc body class để style component
- tạo mới class `az-*`
- dùng `!important` khi không thật sự cần

PREFERRED:

- `.az-component`
- `.az-component__element`
- `:where(.az-page) .az-component`

Frontend theme dùng wrapper `<div id="page" class="az-site">`, page scope trên `main`/content wrapper và biến CSS tại `:root`. `body_class()` vẫn được giữ cho plugin compatibility, third-party integration và debugging nhưng không phải scope kiến trúc chính. Selector legacy chỉ được loại bỏ sau khi HTML/template mới đã được kiểm thử.

## Security/performance

Guard ABSPATH; quyền và nonce cho thao tác ghi; validate type/range/enum trước sanitize; escape theo ngữ cảnh khi render. SQL động dùng prepare hoặc API có format. Không tin dữ liệu request/AJAX/REST. Không coi nonce là authorization hay CAPTCHA là rate limiter.

Module disabled không nạp PHP/assets/handlers sau khi module manager được triển khai. WooCommerce cần guard phụ thuộc. Tránh DB query lặp và CSS generation lớn mỗi request. Không thay đổi dữ liệu trong hàm render chỉ để đồng bộ mặc định.

## Hoàn tất STEP

Đọc source và docs liên quan trước sửa; giữ compatibility; lint PHP khi có runtime; kiểm tra duplicate symbols và Woo OFF nếu liên quan; cập nhật docs/README module, schema, changelog và roadmap. Báo Completed, Files Added, Files Modified, Compatibility, Tests, Remaining; dừng đúng STEP.
