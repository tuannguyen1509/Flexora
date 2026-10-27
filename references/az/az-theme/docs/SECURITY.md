# Security baseline

STEP 16 homepage save requires manage_options and nonce, accepts only allowed section fields, sanitizes text/bounds numbers and merges only homepage values. Template includes use definition IDs, not request paths. No new AJAX/REST route. Runtime permissions/invalid-input tests remain pending staging; see module README.

Review tĩnh, không pentest hoặc gọi endpoint thật. Các phát hiện dưới đây chưa được sửa trong STEP 00. Không truy xuất token/password/site data; inventory chỉ chứa source, không chứa giá trị database.

## Phát hiện cần ưu tiên

2. **Usage/token API cho phép đặt lại số đếm dựa vào license key**: [license-api.php](../inc/license-api.php#L314), [token callback](../inc/license-api.php#L355) cast int, tìm license rồi update metadata; không kiểm tra domain đã nhận trong hai callback, không thấy chống replay/giá trị âm. `__return_true` tại route không tự là lỗi nếu callback có auth, nhưng key-only mutation cần đánh giá giao thức client.
3. **CAPTCHA trả đáp án dạng text**: [contact-form.php](../inc/functions/contact-form.php#L20) trả token và code, refresh public có nonce. Cơ chế này không phải bot protection mạnh hoặc rate limiter; chưa thấy throttle trong callback. Không suy ra guest nonce là quyền riêng cho từng khách.
4. **Custom-code/SMTP cấu hình nhạy cảm**: admin settings lưu code và credential trong options; kiểm tra capability, unfiltered_html/multisite, access DB và logging trước mở rộng. Không đưa option values vào docs hoặc log.

## Điểm đã thấy có bảo vệ

- Contact admin-post kiểm tra manage_options, nonce, type, email/http(s), sanitize và escape khi render; validate tất cả trước khi ghi, bỏ qua field vắng trong POST.
- Contact AJAX nonce + sanitize + kiểm tra CAPTCHA; load categories có WC guard + check_ajax_referer + limit bounded.
- Remote image localization chỉ chạy với edit_post, loại autosave/revision và dùng WordPress media sideload APIs. Cần test network/file-size/time budget riêng, không kết luận SSRF chỉ từ URL input.
- Lead insert/delete dùng wpdb format; các query có tham số dùng prepare ở nhiều vị trí. `SHOW TABLES LIKE` trong license API còn nội suy chuỗi từ prefix; cần chuẩn hóa khi sửa module.

## Tiêu chí STEP sau

Kiểm tra từng mutation: capability/auth, nonce đúng scope khi áp dụng, ownership, validate schema, escape output, rate/range limits và error handling. REST public phải có mục đích và contract rõ. Xác định audit trail cho thay đổi license. Test requests thiếu nonce/quyền, kiểu array thay scalar, XSS payload, URL sai protocol và negative counters trên staging.

Không tự thêm sửa bảo mật, đổi auth hoặc vô hiệu hóa endpoint trong STEP 00 vì sẽ đổi chức năng đang dùng.

## Cập nhật 2026-10-05

Đã gỡ Prompt Execution API và các endpoint `az-prompt/v1` khỏi theme. License API khác được giữ nguyên. Thư mục `audit/` là snapshot lịch sử, không phản ánh code hiện tại.
