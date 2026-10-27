# Trang Nội Dung Trung Tính — mẫu dùng chung của AZ Theme

Từ 13/10/2026, `az-theme/page-neutral.php` cung cấp bố cục một cột: ảnh đại diện lớn ở đầu thẻ, nội dung có khoảng đệm, viền/bo góc/shadow nhẹ. Mẫu dùng được khi kích hoạt parent trực tiếp hoặc bất kỳ child nào kế thừa `Template: az-theme`; không cần sao chép PHP/CSS vào child. Bố cục ảnh nền hero cũ của cùng mẫu được thay bằng thẻ nội dung này.

## Quản trị

1. Sửa hoặc tạo Page trong WordPress.
2. Chọn **Trang Nội Dung Trung Tính** hoặc **Trang Giới Thiệu** ở **Mẫu / Template** hoặc **Thuộc tính Trang → Mẫu**.
3. Nhập tiêu đề/nội dung như bài viết chi tiết; hỗ trợ block, heading, ảnh, bảng, danh sách, shortcode và embed qua `the_content()`.
4. Chọn **Ảnh đại diện** nếu muốn ảnh lớn ở đầu thẻ. Không có ảnh thì chỉ hiện nội dung; ảnh chèn giữa bài vẫn giữ vị trí đã chọn.
5. Nhấn **Cập nhật / Đăng**.

Giữ filename `page-neutral.php` và tên mẫu nên các Page đã chọn mẫu không cần đổi `_wp_page_template`. Không tạo metabox, option, migration hoặc sửa dữ liệu. Các website dùng template parent cũ sẽ nhận bố cục mới sau khi cập nhật parent. Nếu một child khác vẫn có `page-neutral.php`, WordPress tiếp tục ưu tiên override đó; gỡ override trùng khi muốn dùng mẫu parent mới. `az-theme-child` của SOVIBAN đã gỡ các bản trùng trong lần chuyển này.

## Trang Giới Thiệu dùng nội dung WordPress

Mẫu **Trang Giới Thiệu** (`page-about.php`) nay gọi `get_template_part('page-neutral')` để dùng cùng bố cục một cột và `the_content()` của mẫu trung tính. Giữ tên mẫu và filename nên các Page đang chọn Trang Giới Thiệu không phải đổi mẫu trong database. Nội dung lấy từ editor WordPress; ảnh đại diện lấy từ Page. Không phát các nội dung mặc định, sáu khối metadata riêng hoặc form liên hệ được gắn sẵn trong template cũ.

Đã xóa module `inc/gioi-thieu-page-fields.php` và dòng nạp trong `inc/functions/customizer.php`: không còn hộp **Nội dung trang Giới thiệu**, sáu tab, editor con, picker, callback lưu, hooks/JS quản trị riêng hoặc điều kiện theo slug `gioi-thieu`. Các tùy chọn Classic/Block Editor của AZ Theme và metabox Banner trang hiện có được giữ. Admin nhập nội dung ngay trong editor chính, format đoạn văn/heading/ảnh/danh sách/liên kết như WordPress rồi **Cập nhật**.

Không chuyển/xóa `_az_gioi_thieu_data` trong database. Dữ liệu form cũ vẫn tồn tại nhưng không còn được render hoặc ghi bởi theme; `post_content` đã có được dùng trực tiếp. Nếu editor đang trống, frontend không tự lấy đoạn văn từ form cũ hoặc phục hồi nội dung mẫu; nhập nội dung muốn hiển thị vào editor. Không có migration tự động.

Kiểm tra `tests/neutral-page-admin.php` nay chạy luồng nạp Customizer thật của parent với WordPress stubs, xác nhận không có metabox/save/assets cũ cho About/Neutral/default slug, giữ dữ liệu và hooks logo/preview. Chưa kiểm tra save/reload trong wp-admin thật.

## Thiết lập và contracts

- Container lấy `.az-container` / **Advanced → Layout**; font, màu và bo góc dùng tokens `--az-*` của parent. Không gắn thương hiệu, màu SOVIBAN hoặc phụ thuộc handle child.
- Header/footer vẫn đi qua `get_header()` / `get_footer()` nên mỗi website nhận giao diện riêng của theme đang dùng.
- Mẫu không phát lại tên Page thành tiêu đề lớn bên dưới breadcrumb. Tên Page vẫn chỉnh trong WordPress và dùng bởi breadcrumb; heading trong nội dung do người biên tập nhập được giữ. Banner chung tiếp tục render qua hook parent và có thể hiển thị tiêu đề khi được bật. Muốn ảnh trên thẻ như mẫu, chọn **Không dùng banner** và dùng **Ảnh đại diện**.
- Giữ WordPress loop, `the_content()`, `wp_link_pages()`, `post_class()` và skip targets `main` / `az-main-content`.
- Khi Page còn khóa mật khẩu, chỉ đi qua form nội dung của WordPress, không phát ảnh đại diện hoặc bình luận. Bình luận hiện khi được bật hoặc đã có bình luận.
- Bảng/code có scroll ngang trong vùng nội dung; ảnh/block wide được giới hạn trong thẻ. Typography nội dung kế thừa `.az-content`.

## Source và mở rộng

- `page-about.php`: giữ tên mẫu Trang Giới Thiệu, dùng `get_template_part()` để kế thừa layout trung tính.
- `page-neutral.php`: template dùng chung, domain `az-ai-theme`, classes `.az-neutral-page` và `.az-neutral-page__*`.
- `inc/core/neutral-page.php`: `az_neutral_page_assets()` đăng ký `wp_enqueue_scripts` priority 60, enqueue cho `page-neutral.php` hoặc `page-about.php`.
- `inc/bootstrap.php`: nạp module này cùng các module core; không gọi API truy vấn template ngay khi child đang load.
- `css/neutral-page.css`: handle `az-neutral-page`, dependencies `az-content` và `az-page-style`, version từ `filemtime()`.

Asset được resolve bằng `get_theme_file_path('css/neutral-page.css')` và `get_theme_file_uri(...)`. Child có thể cung cấp stylesheet cùng đường dẫn nếu chủ đích thay toàn asset; tùy chỉnh nhỏ nên enqueue CSS riêng sau handle `az-neutral-page`. Không require bootstrap parent từ child. Không đổi `page.php` mặc định hoặc `single.php`.

CSS chuẩn hóa wrapper chat được copy vào nội dung khi có đúng dấu hiệu `div.group[class*="dark:bg-"]` chứa `.markdown.prose`: bỏ nền tối, màu chữ sáng và giới hạn cột hẹp, giữ HTML/ảnh/link trong nội dung. Không sửa `post_content`, không đổi Group block WordPress thông thường hay callout có nền do biên tập viên đặt.

## Kiểm tra và giới hạn

- `php wp-content/themes/az-theme/tests/neutral-page.php`: 21 kiểm tra parent độc lập, không nạp code child/WordPress/database. Kiểm tra đăng ký module, assets không phụ thuộc child, getter override file/URI, loại mẫu khác, không lặp tiêu đề, ảnh/nội dung, mật khẩu, banner, phân trang và bình luận.
- `php wp-content/themes/az-theme-child/tests/neutral-page.php`: 14 kiểm tra kế thừa với helpers banner parent thật và header/footer SOVIBAN qua fixture. Fixture nay require template/CSS/module parent, không còn bản override trong child.
- Tổng 13 suite PHP được chạy trong lần chuyển: 522 checks đạt; lint 189 PHP file parent/child và `git diff --check` đạt.
- Browser fixture 1440px/390px không tràn ngang, ảnh tải thành công, không lặp tên Page dưới breadcrumb và stylesheet tải từ `/az-theme/css/neutral-page.css`. Wrapper copy cũ về chữ #424242/nền trong suốt; Group block nền vàng được giữ. Không có lỗi JavaScript ở lượt preview sau khi sửa đường dẫn stylesheet trong fixture.
- Chưa kiểm tra chọn mẫu, save/reload, plugin/shortcode hoặc mật khẩu thật qua wp-admin. Không kích hoạt theme, cập nhật production hoặc ghi database. Website khác có CSS/override riêng cần kiểm tra trên staging sau khi cập nhật parent.

## Bỏ tiêu đề lặp dưới breadcrumb

Đã xóa header tiêu đề tự động và CSS `.az-neutral-page__heading` khỏi template/CSS parent. Nội dung bắt đầu bằng ảnh đại diện hoặc thẻ bài viết; giữ khoảng đệm 32px desktop / 24px mobile. Không đổi tiêu đề Page đã lưu, breadcrumb, banner hay heading bên trong nội dung. Kiểm tra parent/child neutral và admin: 43 ca đạt; preview 1440px/390px không có heading tự động hoặc tràn ngang. Chưa kiểm tra trên WordPress production.

## Kiểm tra lần chuyển Trang Giới Thiệu sang editor

Files: thay `page-about.php`, mở rộng asset guard trong `inc/core/neutral-page.php`, bỏ include trong `inc/functions/customizer.php`, xóa `inc/gioi-thieu-page-fields.php`; cập nhật hai tests parent, hai fixtures child và tài liệu. Tái sử dụng WordPress `get_template_part()`, `the_content()`, loop, phân trang/bình luận và assets `.az-content` / `.az-neutral-page` hiện có. Không viết lại formatter hoặc schema nội dung riêng.

Kiểm tra trọng tâm: parent 21 ca, admin cleanup 10 ca, child 14 ca. Nội dung heading/chữ đậm/liên kết được giữ; About và Neutral render cùng bố cục, nội dung rỗng giữ rỗng, ảnh/bình luận không lộ khi khóa mật khẩu. Form contact gắn cứng trong template cũ đã bỏ cùng các section cũ; shortcode/form được biên tập viên đặt trong editor vẫn đi qua `the_content()`.

Kết quả lần sửa này: 14 suite PHP cô lập, **538 checks** đạt; lint 189 PHP files parent/child và `git diff --check` đạt. Browser About fixture 1440px/390px không tràn ngang, giữ breadcrumb Giới thiệu và định dạng editor, ảnh tải thành công, stylesheet từ parent; không thấy lỗi JavaScript. Chưa kiểm tra wp-admin/Media modal, save/reload hay shortcode/plugin thật trên production.
