# Compatibility baseline

STEP 16: homepage fallback contracts and default rendering are documented in [module README](../inc/modules/homepage/README.md). Legacy PHP functions/options remain, templates moved without redesign. Product sections have explicit Woo guards; live Woo ON/OFF and visual regression tests still not run. The audit inventories are STEP 00 snapshots; their file hashes/line references are not current after STEP 16 edits.

## Hợp đồng giữ nguyên

Tất cả tên hàm/class, hook, option az_/az_, theme_mod, metadata, CPT, taxonomy, REST namespace, cookie và thứ tự require hiện tại được giữ trong STEP 00. Giữ header metadata/text-domain và HTML/CSS frontend. Form contact cũ đã được viết lại trước baseline; không coi thay đổi trước đó là việc của STEP 00.

## WooCommerce OFF

Chưa chạy deactivate/plugin test. Chỉ review tĩnh:

- Header guards `wc_get_cart_url`, `WC`; front-page guards `wc_get_product`.
- Notice translation guards WC/session; recent-view hook guards is_product; ensure pages guards class WooCommerce.
- Home AJAX checks WooCommerce + product_cat + product before work.
- Loader vẫn nạp PHP Woo modules dù plugin OFF; taxonomy product_warehouse vẫn đăng ký. Hook registration tự nó không chứng minh fatal, nhưng chưa đạt yêu cầu không-load module disabled.
- Các template archive-product/single-product/cart gọi Woo APIs trong luồng sản phẩm. Không thể kết luận tất cả route/custom template an toàn chỉ từ vài guard.

Do đó trạng thái: **partially guarded, runtime unverified**, không ghi Woo OFF PASS. STEP 01 phải giữ loader cũ; chuyển dependency gate có chủ đích ở STEP phù hợp, không tắt helper đang dùng trong header/home.

## Phụ thuộc và khoảng trống

- inc/service-areas-settings.php thiếu nhưng include có file_exists guard: optional/no load, không phải fatal vô điều kiện.
- az_get_plan_limit được gọi trực tiếp trong inc/license-api.php nhưng không có definition trong theme; endpoint verify có nguy cơ undefined function nếu provider ngoài theme không cung cấp.
- az_get_plan_post_limit là dependency tùy chọn có function_exists fallback trong license API.
- Testimonial CPT có metabox nhưng không tìm thấy registration. Không tự xóa metabox vì có thể plugin cung cấp CPT.
- Product brand/attribute taxonomy và contact-form-7 shortcode phụ thuộc plugin/data.
- Cart override không có @version WooCommerce template header; cần so với phiên bản plugin thực tế khi test, không suy ra tương thích từ theme header.
- Theme metadata khai báo PHP >=7.4, WP >=5.8, tested up to 6.4; đây là metadata cũ, không phải kết quả test môi trường hiện tại.

## Frontend/assets

CSS shared đã có prefix az- nhưng nhiều template/legacy selectors còn az-/ct-/wv-/pp-/arc-/gcg-/pg-. Chưa có screenshot baseline để khẳng định đồng bộ hoàn toàn. Tailwind/Font Awesome CDN, JS ngôn ngữ và CSS inline còn tồn tại. STEP 00 không sửa các điểm này.

Admin CSS mới chỉ load màn hình contact. Không mở rộng selector trên admin plugin/core. Các option doanh nghiệp mới có thể chỉ có form/lưu mà chưa render frontend; không xóa vì không có get_option literal tại template.

## Cập nhật 2026-10-05

Đã gỡ Prompt Execution API và các endpoint `az-prompt/v1` khỏi theme. License API khác được giữ nguyên. Thư mục `audit/` là snapshot lịch sử, không phản ánh code hiện tại.
