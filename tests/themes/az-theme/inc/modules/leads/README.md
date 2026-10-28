# AZ9S Leads tích hợp

Chuyển từ plugin AZ9S Leads 1.2.3 do người dùng cung cấp. Module được nạp bởi functions.php, không cần cài plugin. Nếu plugin AZ9S Leads còn hoạt động, theme không nạp module trùng.

Menu quản trị: Quản lý Lead (manage_options), gồm Danh sách Leads, Biểu mẫu và SMTP. Giữ nguyên shortcode, AJAX và option của plugin; biểu mẫu đã tạo tiếp tục hoạt động. Bảng dữ liệu giữ nguyên wp_prefix + az9s_leads và được nâng cấp qua init. Không chạy uninstall hoặc xoá dữ liệu khi chuyển theme.

Dữ liệu cũ của theme nằm ở bảng az_contact_leads. Mục Lead liên hệ đã ẩn khỏi menu Cài đặt; quản trị viên vẫn có thể truy cập trang cũ tại `options-general.php?page=az-contact-leads`. Hai bảng không tự gộp để tránh trùng/mất dữ liệu. Đăng ký mới từ template `page-contact.php` (AJAX `az_contact_form_submit`) nay ghi trực tiếp vào `az9s_leads`, dùng tên form nguồn và giữ chủ đề trong nội dung; hiển thị tại `admin.php?page=az9s-leads`. Khi bảng AZ9S cũ chưa có cột form và hosting không cho nâng cấp, lưu tên form trong nội dung. Chỉ báo thành công sau khi ghi được lead; email lỗi không làm mất lead đã ghi. Thông báo dùng danh sách email/SMTP AZ9S khi module hoặc plugin cung cấp helper, dự phòng bằng mail legacy nếu helper không có. Khi SMTP AZ9S bật và có host, nó được ưu tiên hơn SMTP legacy của theme. Nếu có plugin SMTP khác, chỉ bật một bộ cấu hình SMTP.

Triển khai: upload functions.php, inc/functions/admin-settings.php và toàn bộ thư mục inc/modules/leads. Không xoá plugin trước khi sao lưu database; có thể tắt plugin sau khi upload module. Chưa kiểm thử gửi email hoặc ghi database trên hosting.

Sửa kết nối form liên hệ: cập nhật `inc/functions/admin-settings.php`, `inc/functions/contact-form.php` và `page-contact.php` của parent; child kế thừa handler tự động. Nếu child ghi đè trang liên hệ, giữ action AJAX `az_contact_form_submit` cùng nonce/CAPTCHA để dùng handler này.

Kiểm tra `php wp-content/themes/az-theme/tests/contact-leads.php`: 17 trường hợp với handler, helper lưu và notifier thật; API database/WordPress/email được giả lập. Kiểm tra bảng đích theo prefix, số lead được ghi, chủ đề/nguồn, trạng thái mới, khách và người đăng nhập, gửi email lỗi, ghi database lỗi, schema cũ, nonce/required/CAPTCHA sai và CAPTCHA dùng lại. Không gửi email ra ngoài hoặc ghi dữ liệu thật khi chạy test.
