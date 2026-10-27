# Nút lên đầu trang

Vào **AZ Theme Advanced → Footer → Nút lên đầu trang**:

1. Bật **Hiển thị nút lên đầu trang**.
2. Chọn **Vị trí nút lên đầu trang**: Bên trái hoặc Bên phải.
3. Nhấn **Save Changes**.

Nút nổi xuất hiện ở góc dưới màn hình khi cuộn xuống hơn 360px, trên cả máy tính và điện thoại. Với trang ngắn có khoảng cuộn dưới 720px, nút xuất hiện sau khi cuộn quá nửa khoảng cuộn. Nhấn nút để cuộn về đầu trang; nút tự ẩn khi trở lại phía trên. Khi thiết bị chọn giảm chuyển động, cuộn lên đầu không dùng hoạt ảnh.

CSS của nút nằm riêng trong `css/back-to-top.css`, vẫn tải khi Performance tắt `style.css`. CSS và script dùng version hash nội dung. Nếu JavaScript chưa chạy hoặc bị chặn, nút vẫn hiện và liên kết `#page` cuộn về đầu trang. Khi JavaScript hoạt động, nút áp dụng hiện/ẩn theo cuộn và hiệu ứng cuộn mượt. Nếu dùng plugin cache, hãy xóa cache trang sau khi cập nhật; tùy chọn ẩn version tài nguyên hoặc CDN có thể vẫn giữ nội dung cũ.

Mặc định nút tắt và vị trí bên phải. Cài đặt `footer.back_to_top` thay thế công tắc General cũ; nếu chưa lưu giá trị Footer, theme đọc `general.back_to_top` để giữ trạng thái trước đó. `footer.back_to_top_position` chỉ nhận `left` hoặc `right`. Reset Section của Footer tắt nút và đặt lại vị trí bên phải.

Nút được render qua `wp_footer()` nên dùng chung cho parent và child theme. Child footer đi kèm đã bỏ mũi tên riêng để không hiển thị hai nút. Khi cập nhật website dùng child này, cần cập nhật cả `az-theme-child/footer.php` cùng các thay đổi của parent theme.
