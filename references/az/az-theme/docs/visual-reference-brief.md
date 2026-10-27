# Brief khi code giao diện từ ảnh

Mỗi ảnh tham chiếu cần ghi rõ: kích thước màn hình của ảnh, phần nào thuộc desktop/mobile, logo và icon gốc, nội dung nào cần lấy động từ WordPress/WooCommerce, cùng các trạng thái tương tác (hover, menu xổ xuống, sticky, mobile).

Quy trình áp dụng:

1. Đo lề khung nội dung, chiều cao từng thanh, chiều rộng các cột và khoảng cách trực tiếp theo ảnh.
2. Đưa các số cần quản trị vào trang Advanced; không sao chép màu hoặc font cố định vào CSS.
3. CSS của từng giao diện phải có namespace riêng, nhưng luôn dùng `--az-color-*`, `--az-font-*` và `--az-font-size` để đồng bộ Colors và Typography.
4. Dùng ảnh/logo/icon thật của website thay cho hình mô phỏng. Chỉ từ một ảnh không thể suy ra chính xác trạng thái hover, mobile hoặc dữ liệu động; các trạng thái này cần thêm ảnh nếu muốn khớp tuyệt đối.
5. So sánh ở đúng kích thước ảnh tham chiếu trước khi chốt.

## Profile Piano hiện tại

- Header dùng **container chung** trong `Advanced → Layout`; không có container riêng cho Header.
- Menu “Danh mục sản phẩm” ưu tiên menu danh mục riêng; khi chưa chọn, tự dùng “Menu chính (WordPress)”.
- Màu nút danh mục và nút tìm kiếm lấy từ `Advanced → Colors → Accent`; nền header lấy từ màu Primary.
