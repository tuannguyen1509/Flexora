# Địa chỉ và Google Maps

Nguồn chung là **AZ Theme Advanced → General → Địa chỉ & Bản đồ → Danh sách địa chỉ** (`general.addresses`). Nhóm Dữ liệu địa chỉ dự phòng đã được bỏ khỏi quản trị. Địa chỉ, link chỉ đường và iframe cũ ở các trường dự phòng không được dùng để render.

- `value`: địa chỉ chữ cho trang Liên hệ, footer và shortcode địa chỉ.
- `maps_url`: link chỉ đường của địa điểm; footer chỉ hiện cụm icon khi có link hợp lệ.
- `maps_embed`: iframe Google Maps cho trang Liên hệ và các vị trí chèn shortcode bản đồ, kể cả khi chưa nhập địa chỉ chữ.

Chèn `[az_google_map]` vào nội dung hoặc widget hỗ trợ shortcode để hiện các bản đồ theo thứ tự danh sách. Chèn `[az_google_map index="2"]` để chỉ hiện bản đồ của dòng địa chỉ thứ hai; số thứ tự tính cả những dòng chưa có iframe. Mặc định không hiển thị tiêu đề cụm bản đồ. Có thể thêm `title="Bản đồ"` nếu muốn đặt tiêu đề riêng. Shortcode không tự thêm bản đồ vào vị trí chưa được chèn.

`[az_contact type="map"]` và `[az_general field="google_maps_embed"]` cũng dùng cùng nguồn. Các shortcode `[az_general field="address"]`, `[az_general field="address_secondary"]` và `[az_general field="google_maps_url"]` đọc danh sách hiện tại.

Template Liên hệ và shortcode dùng chung renderer trong `inc/helpers/contact-maps.php`. Renderer lọc iframe theo sanitizer của theme, không đưa các thẻ/script đi kèm ra frontend. Một bản đồ chiếm trọn chiều ngang; nhiều bản đồ dùng hai cột trên desktop, một cột trên mobile. Xoá iframe hoặc xoá danh sách sẽ ẩn bản đồ, không khôi phục bản đồ dự phòng.

Kiểm tra cô lập: `php tests/contact-maps.php`; footer của theme con: `php tests/footer-location-actions.php` và `php tests/footer-sync.php`.
