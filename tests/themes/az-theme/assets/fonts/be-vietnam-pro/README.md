# Be Vietnam Pro

Font mặc định của AZ Theme cho Body và Heading. Các file WOFF2 được đóng gói
trong theme cha, dùng được khi cài theme trên website mới hoặc dùng theme con.
Không cần tải/kích hoạt font trong **Giao diện → Phông chữ** và không gửi yêu cầu
đến Google Fonts khi website hiển thị.

- Độ đậm: 100–900, gồm normal và italic.
- Bộ ký tự: Vietnamese, Latin và Latin extended. Trình duyệt chỉ tải các file
  tương ứng với bộ ký tự, độ đậm và kiểu chữ đang sử dụng.
- Đăng ký trong `theme.json`; WordPress 6.4+ tự xuất `@font-face` ở frontend
  và trình soạn thảo khối. Bản WordPress cũ dùng `css/be-vietnam-pro.css`.
- Theme Advanced → Typography hiển thị **Be Vietnam Pro (Có sẵn trong giao diện)**.
  Lựa chọn font đã lưu vẫn được giữ; font khác trong Font Library vẫn dùng được.
- Nguồn: [Google Fonts](https://github.com/google/fonts/tree/main/ofl/bevietnampro),
  file webfont từ Google Fonts CSS API (`fonts.gstatic.com`, phiên bản v12),
  tải ngày 2026-10-09.
- Giấy phép: SIL Open Font License 1.1, xem [OFL.txt](OFL.txt).

Khi đóng gói/copy theme, giữ nguyên thư mục này và `theme.json` cùng
`css/be-vietnam-pro.css`. Không cần copy thư mục uploads/font của website cũ.
