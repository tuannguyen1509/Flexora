# H1 và Meta description trang chủ

## Cấu hình dùng chung

Vào **AZ Theme Advanced → Branding → Metadata** (`page=az-theme-advanced&section=branding`):

- **Homepage SEO H1**: lưu ở `az_theme_options.branding.home_h1`. Để trống dùng **Tiêu đề trang web** (`blogname`). Khi cả tiêu đề website cũng trống, helper dự phòng bằng tên Page được chọn làm trang chủ, rồi tiêu đề tài liệu.
- **Homepage Meta description**: lưu ở `az_theme_options.branding.home_meta_description`. Để trống dùng **Dòng mô tả** (`blogdescription`). Nếu cả hai trống, không xuất thẻ description rỗng.

Hai giá trị WordPress mặc định chỉnh trong **Cài đặt → Tổng quan** hoặc **Tùy biến → Nhận diện trang web**. Không tự nối dòng mô tả vào H1. Cấu hình đã lưu từ child dùng cùng option path nên tiếp tục hoạt động, không cần chuyển dữ liệu.

Theme chính nạp `inc/core/branding-seo.php` qua bootstrap trên cả parent và mọi child. Trường Meta description đăng ký trực tiếp trong `inc/admin/sections/branding.php`; child không cần đăng ký lại. Thông tin sản phẩm, bài viết và trang trong giữ mô tả SEO riêng.

## Kế thừa trong theme child

Giữ `wp_head()` trong `<head>` và `wp_body_open()` ngay sau `<body>` khi ghi đè `header.php`. Framework tự xuất H1 trên trang chủ qua `wp_body_open` và tự xử lý Meta description qua `wp_head`.

Không xuất thêm H1 trang chủ trong template nếu dùng H1 tự động. Tiêu đề trong shared banner của parent là H2 khi ở trang chủ; các trang trong vẫn dùng H1. H2 dùng cùng kiểu chữ của banner nên bố cục không đổi.

Nếu child muốn tự đặt H1 hiển thị trong nội dung, tắt hook H1 tự động sau khi parent đã nạp:

```php
add_action( 'after_setup_theme', static function() {
    remove_action( 'wp_body_open', 'az_render_home_seo_h1', 5 );
}, 20 );
```

Sau đó chỉ render một lần trong template trang chủ:

```php
<h1><?php echo esc_html( az_get_home_h1() ); ?></h1>
```

Meta description mặc định áp dụng khi `is_front_page()` đúng. Nếu child có Page template trang chủ riêng cần áp dụng ngoài trang chủ WordPress, mở rộng bằng filter (dùng đúng tên template của website):

```php
add_filter( 'az_home_meta_description_context', static function( $is_home ) {
    return $is_home || is_page_template( 'front-page.php' );
} );
```

`az_get_home_meta_description()` trả về mô tả đã lấy fallback và chuẩn hóa xuống dòng; dùng `esc_attr()` khi tự chèn vào thuộc tính HTML. `az_get_home_h1()` trả về nội dung H1; dùng `esc_html()` khi render.

## Plugin SEO

Rank Math và Yoast đang hoạt động sẽ nhận mô tả qua `rank_math/frontend/description` hoặc `wpseo_metadesc`. Framework không tự xuất thêm meta description khi có `RANK_MATH_VERSION` hoặc `WPSEO_VERSION`. Khi không có hai plugin này, framework tự xuất thẻ meta trên trang chủ. Website dùng plugin SEO khác cần tích hợp renderer của plugin đó để tránh xuất trùng.

## Kiểm tra

Chạy regression parent độc lập:

```sh
php wp-content/themes/az-theme/tests/branding-seo.php
php wp-content/themes/az-theme/tests/branding-seo.php rankmath
php wp-content/themes/az-theme/tests/branding-seo.php yoast
```

Thêm đối số `child` sau mode để kiểm tra tích hợp với child của repository, ví dụ `php wp-content/themes/az-theme/tests/branding-seo.php plain child`. Test gọi helper/registry fields/sanitizer/banner thật và template child khi được chọn; API WordPress và renderer của plugin SEO được giả lập, không truy cập database. Các trường hợp gồm nội dung tùy chỉnh, fallback, dữ liệu rỗng/sai kiểu, một H1/meta, ký tự đặc biệt, trang trong và phạm vi mở rộng từ child. Cần kiểm tra HTML trên WordPress có dữ liệu thật sau cập nhật.
