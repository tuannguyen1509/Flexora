# Performance Settings

The Advanced Performance section contains opt-in frontend optimizations: link prefetching, WordPress image lazy loading, and removal of optional WordPress assets. Riskier switches are disabled by default. Test plugin compatibility after enabling block CSS, MediaElement, jQuery Migrate, or theme stylesheet removal.

## Giới hạn tải file

Trong **AZ Theme Advanced → Performance → Giới hạn tải file**, nhập dung lượng tối đa cho **mỗi file** theo MB, rồi chọn **Save Changes**. Ví dụ `5` cho phép file tối đa 5 MB; có thể nhập số thập phân như `0.5`. Giá trị mặc định `0` dùng giới hạn hiện tại của máy chủ/WordPress. **Reset Section** đưa tùy chọn về `0`.

Lưu tại `az_theme_options.performance.upload_max_file_size_mb`. Tùy chọn hoạt động độc lập với công tắc nhóm Bảo mật, áp dụng cho mọi loại file và mọi tài khoản, kể cả quản trị viên. Media uploader hiển thị giới hạn qua `upload_size_limit`; kiểm tra phía máy chủ dùng `wp_handle_upload_prefilter` và `wp_handle_sideload_prefilter`, gồm upload REST dạng multipart/raw và import Media qua API WordPress chuẩn. File đúng bằng giới hạn vẫn được phép; file lớn hơn nhận thông báo dung lượng tối đa. File đã tồn tại không bị ảnh hưởng.

Giới hạn thực tế là mức nhỏ nhất giữa tùy chọn này và giới hạn PHP/multisite/bộ lọc WordPress hiện có. Theme không tăng được giới hạn của hosting; PHP/web server có thể từ chối request trước khi WordPress xử lý. Plugin tự ghi file hoặc dùng luồng upload riêng bỏ qua các hook chuẩn cần kiểm tra riêng. Đây là giới hạn từng file, không phải tổng dung lượng Media hoặc tổng request.

Kiểm thử cô lập: `php wp-content/themes/az-theme/tests/upload-limits.php`. Trên staging, lưu `0`, `0.5`, `5`; tải file dưới, bằng và trên giới hạn qua Media và REST; xác nhận mức thấp hơn của hosting/multisite vẫn được giữ và Reset Section khôi phục `0`.
