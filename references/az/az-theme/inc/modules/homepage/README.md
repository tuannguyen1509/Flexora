# Homepage Framework — STEP 16

## Overview

Version 1.0.0, 2026-09-17. Quản lý các khối trang chủ hiện có, không xây page builder. UI: **Giao diện > Trang chủ** (`themes.php?page=az-homepage`). Chỉ STEP 16 được triển khai; STEP 01–15 chưa có, không tự triển khai chúng.

## Scope

Chín khối: hero, featured-categories, weekly, wide-banners, category-products, suggested, news, services, custom-content. Giữ các khối tin tức/banner ngang/cam kết cũ để không mất nội dung. Hai khối mới featured-categories và custom-content mặc định tắt.

Mỗi khối có enabled/title/order. Danh sách có limit và columns; banner cố định, cam kết và nội dung trang không có hai control không áp dụng này. Limit category-products là số sản phẩm **mỗi tab**, không phải số nhóm danh mục. Columns chỉ áp dụng desktop >1100px; 0 giữ grid cũ, mobile/tablet dùng responsive cũ. Tiêu đề rỗng được lưu chủ động sẽ ẩn tiêu đề chung; các tiêu đề danh mục con cũ vẫn giữ.

Custom content lấy nội dung Page tĩnh đang được chọn làm trang chủ qua WordPress editor; không hiển thị ở chế độ latest posts, không sao chép nội dung vào option. Có xử lý trang bảo vệ mật khẩu. Không có trình soạn thảo/page builder riêng trong setting.

## Files

- module.php: definitions, storage adapter, normalize, fallback, enabled/dependency, stable ordering, enqueue.
- admin.php: submenu Appearance, form, quyền/nonce/save; chỉ include khi is_admin.
- templates/*.php: bảy khối legacy được chuyển nguyên markup nền từ front-page và hai khối mới.
- homepage.js: tương tác banner/tab cũ được chuyển khỏi template.
- homepage.css: grid override có điều kiện, style hai khối mới.
- functions.php thêm một require; front-page.php giữ các helper/query cũ và render theo allowlist có thứ tự.
- css/admin.css bổ sung kích thước checkbox dùng chung.
- Child theme có thể cung cấp `inc/modules/homepage/page-fields.php`, `template-parts/home-reference.php` và asset tương ứng để thay toàn bộ nội dung riêng của website mà không sửa module parent.

## Dependencies

WordPress Classic Theme; PHP >=7.4 theo metadata theme. Product sections yêu cầu class WooCommerce, post type product và taxonomy product_cat. Thiếu Woo: bỏ hoàn toàn template/query khối sản phẩm, giữ hero/news/services/custom content. Điều này không chứng minh toàn bộ theme legacy đã pass Woo OFF.

Chưa có option registry/shared helpers: az_homepage_options là adapter duy nhất, ưu tiên az_get_options nếu sau này có; nếu chưa thì đọc az_theme_options an toàn. Không định nghĩa giả AZ_Theme_Options hoặc triển khai STEP 01/02 ngầm.

## Option Keys

`az_theme_options.homepage.sections.<id>.{enabled,title,limit,columns,order}`. Không tạo option riêng mới. Save merge vào array hiện tại, giữ các nhánh khác/field lạ, chỉ xử lý ID allowlist. Thiếu field không xóa giá trị.

| ID | Enabled mặc định | Order | Limit | Columns |
| --- | --- | --- | --- | --- |
| hero | true | 10 | không áp dụng | không áp dụng |
| featured-categories | false | 15 | 8 | 4 |
| weekly | legacy hoặc true | 20 | legacy hoặc 6 | 0 |
| wide-banners | true | 30 | không áp dụng | không áp dụng |
| category-products | true | 40 | legacy hoặc 4 mỗi tab | 0 |
| suggested | legacy hoặc true | 50 | legacy hoặc 5 | 0 |
| news | true | 60 | 4 | 0 |
| services | true | 65 | không áp dụng | không áp dụng |
| custom-content | false | 70 | không áp dụng | không áp dụng |

## Legacy fallback

- Weekly/suggested: az_home_*_enabled, *_title, *_count. Category ID và orderby vẫn dùng đường đọc cũ. Legacy title rỗng giữ fallback cũ; title rỗng trong nested setting có nghĩa ẩn.
- Category products: az_home_products_per_category, az_home_category_sections, az_home_featured_category_ids và visible/tab_ids cũ tiếp tục hoạt động.
- Featured categories mới dùng az_home_featured_category_ids nếu có, nếu không lấy root categories có giới hạn.
- Hero/wide banners giữ az_home_<slot>_banner_img/alt/link/slides; H1 giữ az_home_h1_title và fallback Page/site name.
- Các key az_home_banner_*, brand, load-more, benefits/category-strip flag không được front-page hiện tại dùng vẫn giữ nguyên trong DB. Không tự gán những flag trước đây không có hiệu lực vào khối mới vì sẽ đổi mặc định.

## Hooks

wp_enqueue_scripts priority 20, admin_menu, admin_enqueue_scripts, admin_post_az_homepage_save. Không thay đổi hooks legacy hoặc orderby truy vấn sản phẩm.

## AJAX / REST

Không có handler AJAX/REST mới. AJAX az_home_load_categories cũ giữ nguyên; đây không phải cơ chế render mới của homepage.

## Security

manage_options + check_admin_referer khi save. Chỉ nhận field allowlist; sanitize_text_field cho title; enabled boolean; limit 1..30, columns 0..6, order 0..999. Escape khi render. Include template lấy ID từ definitions, không từ request. Không ghi option legacy, không DB schema migration.

## Performance

Config cache trong request. Các product query/week/suggested/news bị tắt không chạy. Không include template category-products khi tắt/Woo thiếu. Featured query chỉ khi bật, bounded limit. Query danh mục chung vẫn phục vụ hero/category fallback. Asset chỉ nạp front page; JS chỉ khi hero hoặc category-products có hiệu lực; tất cả khối tắt không enqueue asset module.

## Compatibility

Legacy markup/card/tab IDs/query filters được giữ; chỉ bổ sung attributes/control headings. Default order của các khối cũ giữ nguyên, services không gắn với toggle news. Luồng shortcode/content dùng filter WordPress, không xử lý ngoài theme. Setting mới thắng fallback ngay cả false/0/title rỗng hợp lệ.

## Test Cases

Đã kiểm tra syntax JavaScript, cấu trúc template allowlist và declaration mới, diff whitespace. Chưa tìm thấy PHP CLI; chưa chạy PHP lint/WordPress/browser rendering. Cần staging: Woo ON/OFF; bật/tắt từng khối; đổi order/tie; limits/columns; custom Page/password; save nonce/quyền; giữ nhánh options khác; malformed options; banner/tab và responsive screenshot.

## Migration

Không tự migrate. Option nested chỉ được ghi khi admin bấm Lưu. Fallback đọc không ghi dữ liệu. Sau lần lưu, các giá trị đã lưu là override và không còn bám thay đổi của option cũ tương ứng.

## Rollback

Hoàn tác riêng commit/file tích hợp STEP 16 cùng front-page trước khi gỡ module; không chỉ xóa module trong khi loader còn require. Giữ az_theme_options và option legacy; không cần rollback DB. Muốn bỏ override phải backup rồi chỉ bỏ nhánh homepage tương ứng bằng thao tác quản trị được duyệt, không xóa toàn bộ az_theme_options.

## Changelog / Version

1.0.0: homepage section controls, legacy extraction/fallback, Woo guards, scoped assets. Không thực hiện STEP 17–30.
