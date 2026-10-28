# Dọn mã nguồn ngày 2026-10-03

Đối chiếu source theme chính, child theme, chỗ enqueue/include và cơ chế nạp động trước khi xoá. Không sửa database hoặc triển khai hosting.

## File đã xoá

Các file sau không có đường nạp trong source hiện tại:

- `assets/admin/css/az-stat-icons.css`: stylesheet không enqueue; markup thống kê hiện tại không dùng `az-admin-stat__top`.
- `assets/admin/js/az-admin.js`: script admin cũ; giao diện hiện tại dùng `az-advanced.js` với các selector khác.
- `css/language-switcher.css`, `js/language-switcher.js`, `js/language-switcher-localhost.js`: bộ chuyển ngôn ngữ cũ; hiện tại nạp `js/az-language.js` và template `az-lang-switcher`.
- `css/mobile-shared.css`: stylesheet legacy không enqueue hoặc import.
- `js/catering.js`: script quản lý catering không được nạp, không có UI/handler tương ứng trong theme.
- `js/product/admin.js`, `js/product/frontend.js`: script quản lý/catalog cũ không được nạp, không có UI tương ứng trong theme; script WooCommerce hiện tại được giữ.
- `inc/admin/sections/homepage.php`: schema cũ không có trong danh sách section của `AZ_Advanced`; module homepage và page fields của child theme được giữ.

Tổng file xoá: 10, khoảng 89 KiB. Các file đều được Git theo dõi nên có thể khôi phục từ lịch sử Git.

## Code đã dọn

- Gộp ba điều kiện frontend giống nhau trong bootstrap, giữ nguyên thứ tự include.
- Bỏ thuộc tính private `current_depth` không được dùng trong nav walker.
- Bỏ các biến cục bộ không được đọc trong xử lý HTML dịch.
- Bỏ key `has_archive` trùng trong CPT pricing và truy vấn metadata gán vào biến không được dùng.
- Sửa kiểm tra homepage để không nhầm `.az-news-body` với selector phần tử `body`; giữ nguyên CSS.

Giữ các template WordPress/taxonomy dù nội dung giống nhau vì WordPress chọn theo tên file. Giữ API, helper công khai, options/meta, tài liệu và kiểm thử có vai trò riêng.

## Kiểm tra

- PHP lint toàn bộ 107 file PHP còn lại: đạt.
- Node syntax toàn bộ 19 file JS/CJS còn lại: đạt.
- `tests/qa.cjs`: 10 kiểm tra đạt.
- `docs/tools/check-homepage.cjs`: kiểm tra template/module homepage và quy ước CSS.
- `git diff --check`.

Chưa kiểm tra render/save bằng WordPress đang chạy; kiểm tra source không xác nhận được các URL asset được lưu thủ công trong database hoặc trong code bên ngoài workspace. Tài liệu audit STEP 00 cũ là snapshot lịch sử, không dùng để kết luận file hiện tại còn tồn tại.
