# Quy ước CSS của AZ Theme

Tài liệu này là quy ước bắt buộc trước khi thêm hoặc sửa CSS trong theme chính, theme child và phần CSS tùy chỉnh của website.

## Typography dùng chung

Không ghi cứng font, cỡ chữ, weight hoặc line-height cho các thành phần dùng chung. Dùng các biến sau để thiết lập kế thừa từ **Giao diện → Theme Advanced → Typography**:

- Body: `--az-font-main`, `--az-body-size`, `--az-font-weight`, `--az-body-line-height`, `--az-body-letter-spacing`.
- Heading: `--az-font-heading`, `--az-heading-weight`, `--az-heading-line-height`, `--az-heading-letter-spacing`.
- Cỡ heading: `--az-h1-size`, `--az-h2-size`, `--az-h3-size`, `--az-h4-size`.
- Navigation: `--az-navigation-size`, `--az-navigation-weight`, `--az-navigation-letter-spacing`.

Ví dụ cho một component riêng:

```css
.az-promo-title {
  font-family: var(--az-font-heading);
  font-size: var(--az-h2-size);
  font-weight: var(--az-heading-weight);
  line-height: var(--az-heading-line-height);
}
```

Biến cỡ chữ tự đổi ở tablet (tối đa 960px) và mobile (tối đa 782px). Component không cần tự tạo media query nếu chỉ cần kế thừa kích thước H1–H4 hoặc Body.

## CSS riêng cho từng website

Theme child được quyền thêm CSS riêng, nhưng phải giới hạn selector trong class của component, ví dụ `.az-site .az-promo-title`, thay vì sửa trực tiếp `h2`, `p`, `a` toàn site. Khi cần thay đổi thiết lập chung, ưu tiên điều khiển trong Theme Advanced thay vì viết CSS mới.

## Không dùng `!important`

Không thêm `!important` vào CSS mới. Hãy tăng tính cụ thể một cách vừa đủ bằng class component, đặt CSS tại đúng nơi được tải sau, hoặc sử dụng biến CSS. Trường hợp buộc phải sửa CSS của plugin bên thứ ba, chỉ dùng một selector rõ phạm vi, ghi chú lý do và không dùng nó làm cách xử lý mặc định.

## Tránh CSS dư thừa

- Dùng biến màu, container, radius và typography có sẵn thay vì tạo giá trị lặp lại.
- Không tạo selector trùng mục đích ở nhiều file.
- Không ghi đè CSS chỉ để sửa một giá trị đã có option trong Theme Advanced.
- Mỗi component mới bắt đầu bằng tiền tố `.az-`.

## Component và modifier là bắt buộc

Mỗi kiểu giao diện dùng chung chỉ có một selector component sở hữu các thuộc tính hiển thị. Khi khác màu, kích thước hoặc biến thể, thêm modifier `az-` thay vì chép lại toàn bộ block CSS.

Nút frontend bắt buộc dùng `.az-button` cùng modifier khi cần:

```html
<button class="az-button">Mặc định</button>
<button class="az-button az-button--secondary">Phụ</button>
<button class="az-button az-button--danger az-button--small">Xóa</button>
```

Selector theo trang chỉ được giữ phần bố cục của nút, ví dụ `width`, `margin`, `flex` hoặc vị trí. Không định nghĩa lại `padding`, màu, radius, weight và transition trong selector trang. Trước mỗi lần thêm CSS phải chạy tìm kiếm trên theme chính và child để bảo đảm component chưa có rule sở hữu cùng thuộc tính.

Quy ước chi tiết và danh sách modifier nằm tại `docs/CSS-ARCHITECTURE.md` và là tiêu chuẩn bắt buộc khi sửa CSS.
