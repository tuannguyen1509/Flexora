# Header Architecture

AZ Theme uses one Universal Header markup for the current implementation. Header options control real behavior such as top bar, search, cart, CTA, sticky state and colors. The previous layout selector was removed because it changed only a class and did not provide separate HTML templates. Desktop and mobile share the same navigation source; mobile keeps a lightweight toggle behavior with minimal variation.

## Mobile — Kiểu 1: Logo + menu trượt phải

Chọn **AZ Theme Advanced → Header → Menu Mobile → Kiểu menu Mobile → Kiểu 1 — Logo + menu trượt phải**, chọn **Menu dùng trên Mobile**, rồi **Save Changes**. Bật **Hiển thị menu Mobile**. Danh sách chỉ còn **None — Ẩn menu**, **Kiểu 1** và **Kiểu 2**. Kiểu 1 là mặc định; các giá trị legacy `left`, `right`, `dropdown` hoặc không hợp lệ dùng kiểu 1 ở cả quản trị và frontend, giữ nguyên menu đã chọn. Lưu lại sẽ ghi kiểu được chọn trong danh sách mới.

Ở màn hình tối đa 782px, Kiểu 1 thay header parent/bundled child bằng thanh trắng gọn: logo bên trái, nút hamburger bo góc màu xanh đậm bên phải. Menu mở từ phải, gồm logo và nút đóng ở đầu, từng liên kết có đường phân cách, nền trang phủ tối. Desktop giữ header hiện có. Nếu menu tắt hoặc chưa có menu hợp lệ, header cũ vẫn hiển thị. Header tùy chỉnh bên ngoài hai header có sẵn cần tự tích hợp `az_render_mobile_menu()` và quy tắc ẩn header tương ứng.

Logo lấy từ **Branding → Logo Mobile**, dự phòng bằng logo Desktop rồi tên website. Menu ưu tiên `header.mobile_menu`, sau đó `header.primary_menu`, rồi vị trí WordPress `primary`; không tạo hoặc thay đổi nội dung menu. Chữ menu chính 15px, menu con 13.5px; hàng menu chính cao tối thiểu 48px. Panel rộng tối đa 360px và 88vw, giúp nhãn dài xuống dòng tự nhiên. Menu con thu gọn mặc định, có nút mũi tên 44px riêng để mở từng nhánh; khi JavaScript không chạy, menu con vẫn hiển thị thụt lề. `header.mobile_menu_style = brand_right` lưu qua sanitizer select hiện có.

Đóng menu bằng nút ×, nền phủ, Escape hoặc chọn liên kết; hỗ trợ Tab/Shift+Tab trong panel, trả focus về nút menu và khóa cuộn nền khi mở. Đổi sang desktop tự đóng menu. Animation tôn trọng `prefers-reduced-motion`. **Sticky header + Sticky Header main** giữ thanh Mobile mới khi cuộn.

Mã dùng chung ở `inc/core/mobile-menu.php`, `css/mobile-menu.css`, `js/mobile-menu.js`; header child lấy kiểu đã chuẩn hóa từ `az_mobile_menu_style()`. Kiểm tra cô lập bằng `php tests/mobile-menu.php` và `node tests/mobile-menu.cjs` từ thư mục theme. Kiểm tra staging: lưu kiểu mới, chọn menu, logo Mobile/thiếu logo, màn hình 320/390/782/desktop, menu con, menu dài cần cuộn, link neo, Escape/Tab, nền phủ, sticky và thanh quản trị; đổi giữa kiểu 1/2, None và tắt menu; xác nhận cài đặt kiểu cũ dùng kiểu 1.

## Mobile — Kiểu 2: Logo + menu xổ xuống nâu/kem

Chọn **Header → Menu Mobile → Kiểu menu Mobile → Kiểu 2 — Logo + menu xổ xuống nâu/kem**, bật **Hiển thị menu Mobile**, chọn menu rồi **Save Changes**. Lưu tại `header.mobile_menu_style = brand_dropdown`. Kiểu này lấy logo/menu và điều kiện sticky giống Kiểu 1.

Header trắng cao tối thiểu 68px với một logo bên trái, nút hamburger nâu trên nền kem bên phải. Khi mở, panel trắng phủ từ đầu màn hình, rộng toàn chiều ngang và cao theo nội dung; menu dài cuộn trong panel. Đầu panel có logo và nút × nền kem, đường nâu phân cách. Các hàng menu cao tối thiểu 48px, chữ 16px và khoảng cách gọn. Mục WordPress đánh dấu `current-menu-item`/`current-menu-ancestor` có nền kem và vạch nâu bên trái. Vùng ngoài panel giữ nội dung trang hiển thị và có thể bấm để đóng; menu không đẩy nội dung xuống.

CSS riêng ở `css/mobile-menu-brand-dropdown.css`, chỉ enqueue khi chọn Kiểu 2 và phụ thuộc `az-mobile-menu` để tải sau CSS dùng chung. CSS ẩn header parent/child theo body class và cả vị trí header thực tế trong `#page`, tránh lặp header khi body class bị lọc. Logo/header desktop giữ nguyên ngoài breakpoint Mobile. Icon SVG có thuộc tính stroke ngay trong HTML. CSS/JS Mobile có version lấy từ hash nội dung thay vì filemtime, tránh dùng phiên bản cũ khi triển khai giữ nguyên timestamp. Khi cập nhật theme phải chuyển cả file CSS riêng mới; cache toàn trang/CDN giữ HTML cũ cần purge theo công cụ cache của website.

JavaScript thêm nút chevron riêng cho từng mục có menu con, hỗ trợ nhiều cấp, `aria-controls`/`aria-expanded` và nhãn dịch. Bấm mũi tên chỉ mở/đóng nhánh; bấm liên kết cha vẫn điều hướng và đóng menu. Menu con thu gọn không nằm trong vòng Tab. Nếu JavaScript không chạy, menu con vẫn hiện để truy cập liên kết. Escape, nút đóng, bấm bên ngoài và đổi breakpoint đều đóng panel; nền khóa cuộn khi mở. Motion giảm theo cài đặt thiết bị.

Staging cần kiểm tra thêm: đánh dấu mục hiện tại, nhiều cấp menu con, mục cha là link thật, nhãn dài, mở/đóng nhánh bằng Enter/Space, cuộn panel khi menu vượt chiều cao màn hình và hồi quy Kiểu 1. Chưa xác minh hình ảnh trong trình duyệt hoặc thao tác lưu và tương tác menu trên WordPress đang chạy.
