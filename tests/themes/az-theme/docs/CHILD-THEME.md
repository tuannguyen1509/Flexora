# Tự tạo AZ Theme Child

H1 và Meta description trang chủ được kế thừa từ **Branding → Metadata** trong parent; child không cần đăng ký lại trường hoặc tự thêm thẻ meta. Xem [Hướng dẫn Branding SEO](BRANDING-SEO.md) để tránh H1 trùng khi viết template và để mở rộng phạm vi metadata.

## Cài lên website mới

1. Copy nguyên thư mục `az-theme` vào `wp-content/themes/az-theme`, bao gồm `inc/admin/templates/child-theme/`.
2. Kích hoạt **AZ Theme** trong **Giao diện → Themes**. Ở lượt tải WordPress tiếp theo, theme tự tạo `wp-content/themes/az-theme-child` nếu chưa có. Hook được nạp cả trên frontend nên không phụ thuộc lượt tải tiếp theo là trang quản trị hay trang công khai.
3. Trong thông báo thành công, bấm **Kích hoạt AZ Theme Child**. Việc tạo thư mục không tự đổi theme đang dùng. Có thể kích hoạt child trực tiếp tại **Giao diện → Themes**.
4. Viết giao diện riêng trong child. Khi update framework, chỉ cập nhật `az-theme`.

Nếu `az-theme` đã được kích hoạt từ trước khi có tính năng này, dùng nút **Tạo theme child** trên dashboard AZ Theme hoặc kích hoạt lại parent. Không cần làm lại nếu website đang dùng child hợp lệ.

## Các file sinh ra

| File | Vai trò |
| --- | --- |
| `style.css` | Khai báo `Template: az-theme` và chứa CSS riêng |
| `functions.php` | Enqueue CSS child sau asset parent và chuyển thiết lập Customizer lần đầu |
| `README.md` | Hướng dẫn sửa giao diện, thêm asset và cập nhật parent |

Child mới không copy toàn bộ template hay `functions.php` của parent. Khi cần đổi bố cục, copy đúng file cần sửa với cùng tên/đường dẫn vào child. WordPress ưu tiên template child và tự nạp `functions.php` của cả child lẫn parent. CSS child enqueue ở priority 100, sau module homepage của parent (priority 20), với dependency `az-container-sync` và phiên bản theo thời gian sửa file.

Ví dụ chỉnh header/footer/trang chủ: thêm `header.php`, `footer.php`, `front-page.php` trong child; chỉnh template từng phần trong `template-parts/`. Giữ `wp_head()`, `wp_footer()`, `wp_body_open()` và các hook cần thiết. Các module nội bộ mà parent nạp trực tiếp bằng `require_once get_template_directory()` vẫn thuộc framework parent; không tự thay bằng file cùng đường dẫn trong child. Dùng hook/filter hoặc template được WordPress tìm trong child để tùy biến.

## Giữ dữ liệu và giao diện

- Một child hợp lệ đã tồn tại: trả về `exists`, không ghi file hoặc cập nhật giao diện child đó. Child tôn thép hiện có trong repository được giữ nguyên.
- Một thư mục/file/symlink đã chiếm tên `az-theme-child` nhưng không phải child hợp lệ: báo xung đột, không tự sửa hoặc ghi đè.
- Child mới, lần đầu kích hoạt: bổ sung theme-mod còn thiếu từ `theme_mods_az-theme`. Giữ giá trị child có sẵn, kể cả `false`, `0` hoặc chuỗi rỗng. WordPress tiếp tục xử lý ánh xạ menu/widget theo cơ chế chuyển theme của core.
- Additional CSS của parent được sao chép thành bài CSS riêng của child nếu child chưa có bài CSS. Không copy `custom_css_post_id` của parent. Lưu CSS thất bại không đánh dấu hoàn tất migration.
- Option dùng chung như `az_theme_options` không bị sửa. Option `az_child_parent_mods_migrated` đánh dấu đã chuyển thiết lập một lần trong mỗi website.

Các file riêng trong child không bị cập nhật parent ghi đè. Các phần vẫn kế thừa từ parent và API/helper/CSS dùng chung có thể thay đổi ở bản mới; không thể cam kết mọi cập nhật đều giữ nguyên pixel hoặc tương thích với mọi tùy biến. Nên thử trên staging, nhất là khi ghi đè template WooCommerce hoặc gọi helper của parent.

## Hosting và multisite

Bộ tạo sử dụng WordPress Filesystem API và đường dẫn được ánh xạ bởi `find_folder()`. Không mở form hỏi tài khoản FTP trong hook tự động. Nếu hosting không cho ghi hoặc cần credentials chưa cấu hình, admin thấy lỗi và nút **Thử tạo lại**. Có thể copy 3 file từ `az-theme/inc/admin/templates/child-theme/` sang `wp-content/themes/az-theme-child` bằng trình quản lý file của hosting.

Tôn trọng `DISALLOW_FILE_MODS` và filter `file_mod_allowed`. Thao tác thủ công cần quyền `install_themes`, `switch_themes` và nonce. Với multisite, chỉ super admin được tạo thư mục theme dùng chung; sau đó cần bật child cho website hoặc network trước khi kích hoạt. Nếu lượt tải sau activation đến từ khách/site admin multisite, dùng super admin để thử tạo lại. Trong một network, nhiều site cùng dùng slug `az-theme-child` sẽ dùng chung file giao diện; muốn giao diện khác nhau cần các child có slug riêng do quản trị mạng quản lý.

Thông báo kết quả lưu trong transient `az_child_theme_install_status` tối đa một tuần, chỉ hiển thị khi parent đang active cho người có quyền đổi theme. Đóng thông báo cần nonce. Ghi file thất bại sẽ xóa các file vừa tạo và thư mục mới nếu trống để lần thử tiếp theo không gặp child dở dang. Bộ mẫu được đọc đầy đủ trước khi ghi; `style.css` ghi sau cùng để theme chỉ được WordPress phát hiện khi đã đủ file.

## Kiểm tra

Chạy từ thư mục gốc WordPress:

```sh
php wp-content/themes/az-theme/tests/child-theme-installer.php
```

28 kiểm tra cô lập đã pass, dùng các lớp WordPress Filesystem thực để ghi/xóa fixture trong thư mục tạm. Bao gồm activation trên frontend, giữ file child, xung đột thư mục, policy/quyền/nonce, lỗi kết nối/tạo thư mục/ghi từng file, rollback/retry, chuyển theme-mod một lần, giữ CSS child và thứ tự enqueue. API option/theme/CSS được giả lập; không thay đổi database hoặc theme đang active của website này.

Cần kiểm tra trên WordPress staging thật: upload chỉ parent rồi kích hoạt, xem thông báo, kích hoạt child, kiểm tra logo/menu/widget/Additional CSS, thử template override, cập nhật parent và xác nhận file child vẫn nguyên. Kiểm tra hosting không ghi được và multisite nếu có sử dụng. Chưa kiểm thử tích hợp database hoặc giao diện trình duyệt trong lần thay đổi này.
