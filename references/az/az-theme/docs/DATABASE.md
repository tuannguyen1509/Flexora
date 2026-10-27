# Database baseline

STEP 16 addition: admin save can create/update nested `az_theme_options.homepage.sections`. No new table or migration; no legacy writes. Other branches are preserved on save. Static homepage custom content remains in wp_posts, not options. Baseline below describes legacy storage.

Audit chỉ đọc source, không kết nối DB. Các bảng/key sau là hợp đồng mã nguồn, không khẳng định đã tồn tại hoặc có dữ liệu trên máy chủ.

## Custom table

`{$wpdb->prefix}az_contact_leads`, định nghĩa tại [admin-settings.php](../inc/functions/admin-settings.php#L179), tạo qua dbDelta:

| Column | Schema |
| --- | --- |
| id | BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY |
| customer_name | VARCHAR(190) NOT NULL DEFAULT '' |
| phone | VARCHAR(80) NOT NULL DEFAULT '', indexed |
| email | VARCHAR(190) NOT NULL DEFAULT '', indexed |
| message | TEXT NULL |
| source | VARCHAR(190) NOT NULL DEFAULT '' |
| created_at | DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, indexed |

Tạo/kiểm tra khi after_switch_theme, admin_init và trước insert lead. Hiện chưa thấy schema version guard. Không chạy dbDelta trong STEP 00. Form có trường topic nhưng schema không có cột topic riêng; kiểm tra logic ghép nội dung khi thay đổi data contract.

License API có đọc bảng tích hợp `{$wpdb->prefix}iaca_license_usage` nếu tồn tại; không coi đây là bảng do theme tạo. Query SHOW TABLES dùng nội suy chuỗi; ghi nhận để audit sau.

## WordPress data

- wp_options (prefix thực theo site): option az_/az_, WordPress/Woo page IDs, settings SMTP và custom code; xem OPTIONS-SCHEMA.
- Theme mods do WordPress lưu trong theme_mods_{stylesheet}; contact save mirror sang theme_mod. Không rename stylesheet/theme option tùy tiện.
- wp_posts/wp_postmeta: pricing, license; license keys/status/expiry/usage/token metadata; product extra info/video; page about structured JSON `_az_gioi_thieu_data`.
- wp_termmeta: EN category/tag, category image, tỷ lệ giá danh mục sản phẩm.
- Menu item metadata: icon tùy chỉnh.
- Toàn bộ get/update_post_meta và get/update_term_meta với biểu thức động: audit/CALLSITES.md và JSON, không chỉ những ví dụ trên.

## Transient / cookie / cron

CAPTCHA transient `az_contact_captcha_{uuid}`, TTL 20 phút; delete khi verify. Recently viewed dùng cookie trong module WooCommerce. Ngôn ngữ dùng cookie và browser storage. Đây không phải option cần nhập tự động vào az_theme_options.

Cron `az_check_expired_licenses` mỗi ngày cập nhật trạng thái license quá hạn, không đổi cấu trúc bảng. Không kích hoạt cron trong audit.

## Ghi dữ liệu nền hiện có

Theme activation/admin_init có thể tạo Woo pages, tạo lead table và seed nội dung about; module cron có scheduling trên load. Vì vậy không dùng include functions.php như một phép thử tĩnh vô hại. STEP tương lai phải kiểm tra idempotency và chỉ chạy trên DB staging có backup.

Ngoài ra page-catalogue.php có nhánh update_option('az_catalogue_lines') ngay trong template; license login sync cập nhật option plugin `iaca_set`. Các tác động này vẫn giữ nguyên, chưa thực thi trong audit.

Migration/rollback STEP 00: không có migration. Chỉ thêm docs; gỡ docs không làm thay đổi dữ liệu hoặc runtime. Không xóa option/meta/CPT cũ khi chưa có mapping và rollback riêng.
