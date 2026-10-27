# Roadmap

## 2026-10-05 — Tự tạo child theme

Hoàn thành theo phạm vi riêng: tự tạo `az-theme-child` khi kích hoạt parent, giữ child có sẵn, thông báo kích hoạt/thử lại, chuyển thiết lập lần đầu cho starter mới và tài liệu cập nhật an toàn. 28 kiểm tra cô lập và PHP lint đạt; kiểm tra WordPress/database/browser trên staging còn lại. Xem [CHILD-THEME.md](CHILD-THEME.md). Không thay đổi trạng thái các STEP khác bên dưới.

STEP 00 đã hoàn thành current source audit. Theo yêu cầu mới, STEP 16 được triển khai độc lập; STEP 01–15 và các STEP mới vẫn planned, không tự chạy bù các bước nền tảng.

| STEP | Nội dung | Trạng thái / giới hạn |
| --- | --- | --- |
| 00 | Current source audit | Hoàn thành audit tĩnh toàn bộ source/docs và [CURRENT-AUDIT.md](CURRENT-AUDIT.md); không sửa frontend, database hoặc runtime. Runtime tests chưa chạy. |
| 01 | Bootstrap foundation | Tiếp theo: bootstrap.php, AZ_Theme, helpers options/utilities. Giữ đủ 19 legacy requires và thứ tự; không chuyển hàng loạt. |
| 02 | Options registry + sanitizer | Planned: AZ_Theme_Options, registry, types cơ bản, az_theme_options. |
| 03 | Admin settings UI | Planned: AZ Theme Dashboard/Theme Settings render từ registry, không SPA. Form contact hiện tại là legacy, không tính là STEP 03 đã xong. |
| 04 | Advanced fields | Planned: color/media/gallery/editor/code/range/spacing/dimension/icon/sortable; chưa repeater phức tạp. |
| 05 | General settings | Planned: general fields và fallback contact legacy; không xóa option. |
| 06 | Branding | Planned: biến thể logo, favicon/OG, fallback Custom Logo. |
| 07 | Colors | Planned: token + alias an toàn cho --az-color-* cũ. |
| 08 | Typography | Planned: nhóm chữ, size desktop/tablet/mobile, nguồn font metadata. |
| 09 | Custom fonts | Planned: WOFF/WOFF2, font-display swap, chỉ tải font đang dùng. |
| 10 | Flatsome Settings Migration Map | Implemented foundation: verified-entry map, preview and explicit importer under `inc/modules/flatsome-compat/migrations/`; no guessed Flatsome keys, no automatic import, no overwrite by default. |
| 11 | Dynamic CSS | Planned: build/sanitize/cache/version, fallback filesystem, hooks. |
| 12 | Module manager | Planned: registry, dependency, enable/disable PHP/assets/handlers; không tắt legacy đột ngột. |
| 13 | Header | Planned: settings + giữ structure; đọc header trước sửa. |
| 14 | Mobile header/navigation | Planned: offcanvas, bottom nav tùy chọn, keyboard/ARIA. |
| 15 | Footer | Planned: menu/social/payment/contact/copyright, fallback az_footer_*. |
| 16 | Homepage Framework | Implemented; UI riêng, nested options + fallback. Static checks đạt, PHP/browser/runtime chưa chạy. |
| 17 | Blog / News | Planned, bước tiếp theo theo chuỗi mới; chưa thực hiện. |
| 18 | Page-level settings | Planned, post meta overrides. |
| 19 | Breadcrumb + Social Share | Planned. |
| 20 | WooCommerce foundation | Planned. |
| 21 | Shop / Product archive | Planned. |
| 22 | Product card | Planned. |
| 23 | Single product | Planned. |
| 24 | Cart + Checkout | Planned. |
| 25 | AJAX search | Planned. |
| 26 | Wishlist | Planned. |
| 27 | Compare | Planned. |
| 28 | Recently viewed | Planned. |
| 29 | Floating contact + Social | Planned. |
| 30 | Performance + Tools + Import/export | Planned, bao gồm audit tổng; không tự xóa legacy. |

Advanced Theme Options implementation is complete for the current step. Live WordPress/PHP verification remains pending because the current environment has no PHP runtime or running WordPress server.

## Trước STEP 01

Đọc ARCHITECTURE, MODULES, OPTIONS-SCHEMA, CHANGELOG và loader hiện tại. Không giả định các helper `az_get_option`, `az_array_get` hay coordinator đã có. Kiểm tra trùng tên trước khi thêm. Không tạo admin UI hay migration trong STEP 01.

Các phát hiện security/performance trong baseline là backlog đã ghi nhận, không phải quyền tự chạy sửa ngoài STEP. Chỉ xử lý khi STEP được giao có phạm vi phù hợp.
