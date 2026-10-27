# AZ Theme Child

Thư mục này chứa giao diện riêng của website và kế thừa chức năng từ `az-theme`.

H1 và Meta description trang chủ cấu hình trong **AZ Theme Advanced → Branding → Metadata**. Để trống sẽ dùng Tiêu đề trang web và Dòng mô tả của WordPress. Giữ `wp_body_open()`/`wp_head()` trong header để kế thừa; không thêm H1 trang chủ hoặc thẻ description thứ hai nếu dùng renderer tự động. Xem `az-theme/docs/BRANDING-SEO.md` nếu muốn tự đặt H1 hiển thị trong nội dung.

1. Vào **Giao diện → Themes**, kích hoạt **AZ Theme Child**.
2. Thêm CSS riêng vào `style.css`; thêm PHP riêng vào `functions.php`.
3. Muốn đổi bố cục, copy đúng template cần sửa từ parent sang child, giữ nguyên tên và đường dẫn: `header.php`, `footer.php`, `front-page.php`, `single.php`, `page.php` hoặc `template-parts/...`. Giữ các hook WordPress như `wp_head()`, `wp_footer()` và `wp_body_open()`.
4. Thêm ảnh, CSS, JS vào child; dùng `get_stylesheet_directory_uri()` hoặc `get_theme_file_uri()` để lấy URL. Enqueue stylesheet riêng với dependency `az-theme-child`.
5. Khi cập nhật framework, chỉ thay thư mục `az-theme`; giữ nguyên `az-theme-child`.

Child mới chưa ghi đè template nên ban đầu dùng bố cục parent. Lần đầu kích hoạt, child bổ sung các thiết lập Customizer còn thiếu từ parent (logo, menu, màu...) và sao chép Additional CSS nếu child chưa có bài CSS riêng. Các option dùng chung như `az_theme_options` vẫn giữ nguyên. Không copy toàn bộ `functions.php` của parent sang child vì WordPress tự nạp cả hai.

Tự tạo child không có nghĩa là tự kích hoạt: bấm **Kích hoạt AZ Theme Child** trong thông báo sau khi kích hoạt parent. Nếu website đã có child, bộ tạo giữ nguyên tất cả file, kể cả khi kích hoạt lại parent hoặc cập nhật parent.

Các file riêng trong child không bị bản cập nhật parent ghi đè. Những template, CSS và chức năng vẫn kế thừa từ parent có thể thay đổi khi cập nhật; kiểm tra trên staging trước khi cập nhật website thật.
