# Chuẩn hoá parent theme

## Tự tạo child cho website mới

Kích hoạt `az-theme` tự tạo `az-theme-child` ở lượt tải WordPress tiếp theo. Child hiện có được giữ nguyên. Bấm **Kích hoạt AZ Theme Child** để dùng child, rồi sửa CSS/PHP/template riêng tại đó. Xem [hướng dẫn cài đặt và cập nhật](CHILD-THEME.md) và chạy `php wp-content/themes/az-theme/tests/child-theme-installer.php` để kiểm tra bộ tạo/migration cô lập.

## Tài nguyên frontend và Custom Code

Đã sửa cấu hình Tailwind chạy sau thư viện, bổ sung skip link và điểm focus nội dung cho các template parent có main. Child có main riêng được script hỗ trợ; header child cần gọi wp_body_open. Custom Code mới lưu code/signature riêng; giao diện chỉ hiển thị code, vẫn đọc chữ ký dạng comment cũ. Người không có unfiltered_html lưu cấu hình khác sẽ không làm mất script đã được tin cậy.

Custom Code do parent `az-theme` quản lý và dùng chung khi kích hoạt theme cha hoặc theme con. Một bộ resolver đọc `az_theme_options.custom_code` và các option `az_hfc_*` cũ cho cả form Advanced lẫn frontend. Footer Code nhận cả Body bottom và Footer cũ theo thứ tự; giá trị đã lưu tại Advanced (kể cả trống) được ưu tiên. Mỗi vị trí chỉ render một lần; callbacks chèn mã cũ không chạy song song. Body Open chạy ở priority 0 của `wp_body_open`; Header/CSS và Footer chạy qua `wp_head`/`wp_footer`. Template riêng của website phải giữ các hook WordPress này.

Chức năng Header & Footer cũ đã được gỡ: không đăng ký trang, nhóm lưu `az_hfc_group`, form, sanitizer hoặc hàm in mã riêng. URL quản trị cũ `options-general.php?page=az-hfc-settings` chuyển đến **AZ Theme → Advanced → Custom Code**. Parent chỉ đọc các option `az_hfc_*` để giữ mã hiện có trong luồng Custom Code; không xoá dữ liệu hoặc tạo lại giao diện cũ. Tất cả chỉnh sửa/lưu mới thực hiện tại Custom Code.

Form Custom Code hiển thị source cũ để kiểm tra và lưu lại. Mã chưa có chữ ký hoặc chữ ký không hợp lệ có thông báo ngay trong panel, kể cả khi mở panel bằng tab JavaScript. Sau khi chuyển website/thay secret, lưu bằng tài khoản có `unfiltered_html` để xác nhận mã trên installation mới. Không tự xác nhận script từ database hoặc bỏ kiểm tra chữ ký. Script chưa hợp lệ cũng không bị in thành JavaScript dạng chữ trên trang. Custom CSS đọc được cả chuỗi và cấu trúc code array.

package.json, tailwind.config.cjs và assets/src/utilities.css là cấu hình build chuẩn bị cho CSS tĩnh. Việc tải dependency chưa được cho phép trong đợt này, nên chưa có CSS build và frontend vẫn dùng CDN. Không triển khai chuyển enqueue sang file utilities.min.css khi chưa build và kiểm tra giao diện. Cần npm install rồi npm run build:css trong thư mục theme trên môi trường được phép; sau đó kiểm tra và chuyển enqueue, đóng gói Font Awesome cùng giấy phép. Không cần node_modules trên hosting.

Build content hiện bao gồm parent và child hiện có; child mới hoặc utility classes lưu trong nội dung database cần bổ sung content/safelist trước khi bỏ CDN. Chưa thay Font Awesome CDN, chưa chạy kiểm thử tích hợp hoặc xác minh trình duyệt trong đợt này.

## Giai đoạn tương thích tiếp theo

General có công tắc nhập ảnh ngoài khi lưu bài (mặc định tắt), security headers và thông báo lỗi đăng nhập. Filter `az_import_remote_images_on_save` cho phép chặn nhập từng bài; `az_security_headers` cho phép đổi hoặc bỏ header iframe. Không tự sửa lại nội dung đã lưu.

Custom Code chỉ cho phép script nguyên bản khi người lưu có cả `manage_options` và `unfiltered_html`. Mã được ký theo secret của installation và kiểm tra khi render, không kiểm tra quyền khách truy cập. Mã cũ, mã bị thay đổi hoặc chuyển sang installation/secret khác được lọc HTML an toàn; quản trị viên có quyền cần kiểm tra và lưu lại. Không bỏ lọc script cho quản trị viên site con trong multisite. Không dán mã không tin cậy.

Menu giữ target, rel, title, aria-current và hook nav_menu_link_attributes. Thay đổi này không viết lại cấu trúc HTML/CSS menu cũ.

Chạy thêm `php wp-content/themes/az-theme/tests/reusable.php`. Đây là kiểm thử cô lập với hàm giả lập cho quyền/KSES, không thay thế kiểm thử WordPress thực tế.

`php wp-content/themes/az-theme/tests/custom-code.php` kiểm tra các hook, source/signature, mã cũ, lưu Advanced, trường trống, tài khoản hạn chế và đổi installation. Không tải Google tag hay gửi dữ liệu tracking trong kiểm thử này.

Chưa tách Lead/SMTP, pricing/license sang plugin hoặc thêm theme.json trong đợt này. Các thay đổi đó cần migration và kiểm thử cấu hình/editor để không làm mất shortcode hay xung đột CSS hiện có. Không nâng Tested up to nếu chưa kiểm chứng phiên bản tương ứng.

All in One Contact hiện nằm trong parent (`inc/floating-contact.php`, CSS/JS cùng tên). Child hiện có cầu nối require_once, không đăng ký hai lần; child mới không cần sao chép module này. Giữ nguyên các option Social và tất cả kiểu liên hệ. Khi triển khai cần cập nhật cả parent và child cùng lúc.

Performance: giữ dependency handle khi tắt nguồn CSS/JS, loại jquery-migrate khỏi dependency, gỡ hook Emoji CSS mới, sửa bộ lọc oEmbed, giữ Dashicons trên trang Blog. Không bật Disable theme style/Block CSS trừ khi đã có stylesheet thay thế. Prefetch giới hạn 10 URL, bỏ qua URL query/hash, các đường dẫn giao dịch phổ biến và kết nối tiết kiệm dữ liệu. Website có endpoint giao dịch tuỳ chỉnh cần kiểm tra trước khi bật.

Không còn ép toàn site dùng Classic Editor. Nội dung đã lưu không bị chuyển đổi. Tên tác giả mặc định bỏ thương hiệu piano; logo đăng nhập dẫn về chính website. Module services không tải ảnh từ website demo.

Kiểm thử hồi quy: `php wp-content/themes/az-theme/tests/performance.php` từ thư mục gốc WordPress. Đây là kiểm thử cô lập dùng dependency resolver thật, không phải kiểm thử database hoặc gửi email.

Giới hạn: parent còn các layout và lớp CSS legacy để tương thích nội dung đã lưu. Lead AZ9S và lead legacy không tự gộp bảng dữ liệu. Cần staging để kiểm tra Gutenberg/Classic, WooCommerce bật/tắt, giỏ hàng/checkout, form/SMTP, trình duyệt mobile và plugin của từng website trước khi phát hành rộng rãi. Không cam kết tương thích mọi plugin hoặc mọi loại website.
