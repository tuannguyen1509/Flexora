# AZ Theme Child

Theme `az-theme-child` chứa giao diện riêng của website và kế thừa framework, thiết lập, WooCommerce và blog từ `az-theme`.

## Mẫu Trang Nội Dung Trung Tính

Mẫu do `az-theme` cung cấp, child tự kế thừa và không còn bản override riêng. Chọn **Trang Nội Dung Trung Tính** trong phần Mẫu/Template khi sửa một Page để dùng bố cục ảnh đại diện lớn + thẻ nội dung một cột. Tiêu đề, ảnh và nội dung chỉnh bằng WordPress như bình thường; không cần cấu hình metabox mới. Xem [cách dùng, file và kiểm tra](docs/NEUTRAL-PAGE.md).

## Trang chủ SOVIBAN hiện tại

Trang chủ đã được triển khai lại theo ảnh SOVIBAN của nhiệm vụ ngày 13/10/2026. Có banner, giới thiệu/dịch vụ, slider thành phố, banner tư vấn, đối tác và footer chung. Chỉnh tại **Trang → Trang chủ → Sửa → Nội dung Trang chủ** và **Banner trang**; các dữ liệu doanh nghiệp/menu/logo vẫn dùng Advanced/WordPress hiện có. Dữ liệu đã lưu được ưu tiên và không tự cập nhật database.

Xem [hướng dẫn quản trị, danh sách file, API tái sử dụng và kiểm tra](docs/SOVIBAN-HOMEPAGE.md), cùng [ảnh desktop dùng fixture](docs/soviban-homepage-desktop.jpg).

Nội dung Trang chủ hiện có 5 nhóm chính: **Giới thiệu SOVIBAN & dịch vụ**, **Slider thành phố & chân trang**, **Banner giới thiệu phía dưới**, **Đối tác của chúng tôi**, và **Nội dung bổ sung**. Nhóm bổ sung chứa sản phẩm, tin tức, dự án và video, mặc định thu gọn; các công tắc và dữ liệu đã lưu vẫn hoạt động.

Đã gỡ nhóm **Danh mục giải pháp & cam kết dịch vụ** của bố cục Tường Vinh cũ, editor sản phẩm nổi bật cũ và các script chỉ phục vụ chúng. Metadata cũ vẫn được giữ khi cập nhật trang. `template-parts/home-reference.php` vẫn là điểm vào tương thích để parent nhận diện homepage của child; không phải một nhóm nội dung quản trị. Stylesheet reference cũ chỉ còn phục vụ các fixture tương thích, không được nạp trên trang chủ SOVIBAN.

Xem [kiểm tra và chi tiết dọn nhóm quản trị](docs/SOVIBAN-HOMEPAGE.md#dọn-nhóm-quản-trị-nội-dung-trang-chủ). Kiểm tra riêng bằng `php wp-content/themes/az-theme-child/tests/home-admin.php` (metadata trong bộ nhớ, không truy cập database).

## Header đồng bộ toàn website

Mọi trang gọi cùng `header.php`: trang chủ, trang nội dung, bài viết, danh mục, cửa hàng, sản phẩm, giỏ hàng, thanh toán và 404. `header-shop.php` và `template-parts/header-home.php` chuyển về entry này. Logo, tìm kiếm, hotline, tiện ích, danh mục và menu chính dùng cùng bộ cấu hình `az_child_header_options()`. CSS header được nạp trên tất cả trang; stylesheet trang chủ không còn ghi đè bố cục, kích thước logo, màu CTA hay kiểu chữ menu. Màu xanh theo mẫu là mặc định chung; các màu đã lưu vẫn được ưu tiên. Chỉ trạng thái menu đang chọn thay đổi theo trang WordPress.

**AZ Theme Advanced → Header** (`page=az-theme-advanced&section=header`) điều khiển:

- Top bar: bật/tắt, nội dung bên trái và menu bên phải. Menu đã chọn được ưu tiên hơn vị trí Top Menu.
- Header main: chiều cao 64–180px áp dụng đúng số pixel trên desktop (từ 783px), logo co theo khoảng trống bên trong. Trên điện thoại, chiều cao là mức tối thiểu để đủ chỗ cho tìm kiếm xuống dòng; bật/tắt tìm kiếm, danh mục, tài khoản, giỏ hàng và CTA; nhãn, placeholder và URL CTA lấy từ cấu hình.
- Menu chính/danh mục: ưu tiên menu đã chọn. Menu danh mục trống dùng menu “Danh mục sản phẩm”, rồi danh mục WooCommerce. Khi không có danh mục, dùng liên kết về các nhóm trên trang chủ thay vì anchor của trang hiện tại.
- Menu chính/sticky lấy menu được chọn tại Header, sau đó vị trí Primary của WordPress; chưa có menu thì không xuất liên kết mẫu hoặc tự đổi danh sách theo trang. Top bar và danh mục nhận cùng bộ cấu hình đang render; danh mục vẫn dùng menu được chọn, menu “Danh mục sản phẩm”, rồi danh mục WooCommerce.
- **Hiện nút danh mục**: bật/tắt cả nút và nội dung menu danh mục, kể cả mục danh mục trong menu mobile kiểu 4/5. Tắt rồi **Save Changes** sẽ bỏ toàn bộ markup danh mục; menu chính dùng toàn bộ chiều rộng thanh điều hướng. Bật lại dùng menu danh mục đã chọn trước đó. Nếu tắt cả danh mục và Header bottom thì không xuất thanh điều hướng trống. Hàm dùng chung `az_child_steel_category_links()` cũng kiểm tra công tắc trước khi xuất menu hoặc danh mục WooCommerce.
- Child bổ sung **Hiện hotline**, **Số hotline trên Header**, **Nhãn hotline**. Khi chưa lưu số riêng, dùng số đầu tiên từ General. Nhập số và nhãn ở Header để áp dụng chung toàn website; để trống số rồi lưu để ẩn.
- Sticky: bật/tắt độc lập Top bar/main/bottom, tìm kiếm và menu sticky. Main dùng cùng hotline, tài khoản, giỏ hàng và CTA như header chính; tài khoản dùng URL WooCommerce khi có WooCommerce. Tìm kiếm và các phần sticky đã chọn tiếp tục hiển thị trên mobile kiểu 1–3.
- Mobile: giữ cả 5 kiểu menu, menu được chọn và các hành vi đóng/mở của parent. Kiểu 4/5 có thêm tìm kiếm, hotline, tài khoản, giỏ hàng, CTA và danh mục bên trong menu, theo đúng các công tắc Header main. Logo/menu trong header gọn giữ bố cục của kiểu đã chọn.
- Header trong suốt: nhận tùy chọn `header.transparent` trên mọi trang.

Logo vẫn lấy từ **Branding → Logo website**, hoặc logo WordPress dự phòng. Tài khoản/giỏ hàng lấy URL WooCommerce; giỏ hàng ẩn khi WooCommerce không hoạt động hoặc website đang ở chế độ catalogue. Tìm kiếm chuyển sang sản phẩm khi WooCommerce hoạt động, còn lại tìm nội dung website. Banner/breadcrumb của trang trong tiếp tục chạy qua hook `az_after_site_header`; trang chủ dùng banner riêng.

Kiểm tra bằng `php wp-content/themes/az-theme-child/tests/header-settings.php`: header chính/sticky, bật/tắt các thành phần, menu được chọn, nhãn, hotline/CTA trống, URL tài khoản, WooCommerce tắt, catalogue và cả 5 kiểu mobile; dùng helper Header/Mobile thật của parent. Preview tùy chọn: `php wp-content/themes/az-theme-child/tests/header-settings.php --preview left 0 110 inner` (kiểu menu, trong suốt, chiều cao, trang home/inner). Regression `header-bottom.php` và `mobile-menu.php` của parent đều pass. Bản xem trước trên trình duyệt xác nhận Header main 64/96/110/180px trên trang chủ và trang trong ở độ rộng 783/900/1280px, nền trong suốt, màu menu đã lưu, sticky khi cuộn, điều khiển mobile kiểu 4/5, menu con, danh mục và Escape; không tràn ngang ở 390px. Chưa kiểm tra trên database/uploads của website đang chạy. Lần đồng bộ header kiểm tra thêm entry `header.php`, `header-shop.php`, `template-parts/header-home.php`, menu thiếu cấu hình, cùng bộ options truyền vào các renderer và so sánh CSS trang chủ/trang trong ở 1280/900/390px với các trạng thái bật, tắt và trong suốt. Header có cùng kích thước, màu sắc và kiểu chữ; không tràn ngang. Menu danh mục, sticky, menu hai cột và drawer mobile kiểu 5 vẫn mở/đóng đúng.

## Cỡ chữ thanh menu

Vào **AZ Theme Advanced → Typography → Thanh menu → Cỡ chữ thanh menu** (`page=az-theme-advanced&section=typography`), chọn 12–24px rồi **Save Changes**. Menu chính, nút “Danh mục sản phẩm” và menu sticky nhận đúng cỡ chữ đã chọn trên trang chủ và các trang trong, không tự giảm xuống 11–12px ở breakpoint tablet. Khi không đủ chỗ, các mục tự xuống dòng. Cỡ chữ trong panel mega menu tiếp tục theo Typography → Body; menu trong drawer mobile giữ bố cục của kiểu menu đã chọn.

`inc/header-typography.php` đổi nhãn nhóm/trường Navigation sẵn có trong registry của parent, giữ nguyên nguồn lưu `typography.navigation_size` và biến `--az-navigation-size`. Không thêm thiết lập trùng hoặc ghi đè dữ liệu đã lưu.

## Menu con hai cột và ảnh danh mục

Menu chính và menu sticky dùng `inc/header-mega-menu.php`, `css/header-mega-menu.css` và `js/header-mega-menu.js`. Mục menu cấp đầu có menu con mở panel trắng: liên kết bên trái, ảnh danh mục của mục cha bên phải. Giữ nguyên thứ tự, liên kết, icon và trạng thái đang chọn của WordPress; các cấp sâu hơn vẫn hiển thị trong cột trái. Menu mobile trong drawer giữ accordion của parent; nếu dùng thanh menu desktop trên màn hình nhỏ, panel chuyển thành một cột và ảnh nằm dưới liên kết.

1. Vào **Sản phẩm → Danh mục**, chọn ảnh thumbnail cho danh mục cha. Với danh mục bài viết, dùng trường **Ảnh danh mục** trong **Bài viết → Danh mục**.
2. Vào **Giao diện → Menu**, thêm danh mục làm mục cha, kéo các liên kết bên dưới thụt vào để tạo menu con. Liên kết tùy chỉnh trỏ đúng URL danh mục nội bộ cũng nhận ảnh; mục không gắn danh mục hoặc chưa có ảnh chỉ hiện liên kết.
3. Chọn menu trong **AZ Theme Advanced → Header → Menu chính** (và menu sticky nếu dùng). Di chuột lên mục cha hoặc nhấn nút mũi tên để mở; Tab/Mũi tên lên/xuống đưa focus vào liên kết, Escape đóng panel. Bấm ngoài hoặc chuyển sang mục khác cũng đóng panel. Panel tự căn trong màn hình để không tràn ngang.

Kiểm tra `php wp-content/themes/az-theme-child/tests/header-mega-menu.php` dùng walker và hooks thật của WordPress: ảnh từng danh mục, ảnh bài viết, thiếu ảnh, liên kết tùy chỉnh nội bộ/ngoài, menu ba cấp, icon/active filters, panel ID riêng cho header và sticky, và giữ renderer mobile. Bản local dùng dữ liệu/ảnh minh họa để xem trước; chưa kiểm tra trên database/uploads hosting.

## Màu thanh menu

Vào **AZ Theme Advanced → Colors → Màu thanh menu** (`page=az-theme-advanced&section=colors`) để chọn:

- Màu nền thanh menu và màu chữ.
- Màu nền/chữ khi rê chuột hoặc dùng bàn phím.
- Màu nền/chữ của mục đang chọn, bao gồm mục cha của trang hiện tại.
- Màu nền/chữ nút danh mục và trạng thái rê chuột của nút.

Nhấn **Save Changes** để áp dụng cho mọi trang và menu sticky. Để trống trường màu để trở về màu mặc định. Khi Header trong suốt đang bật, nền thanh menu vẫn trong suốt theo tùy chọn Header.

`inc/header-storefront.php` chia sẻ các điều khiển giữa header chính, sticky và mobile; `template-parts/mobile-header.php` mở rộng kiểu 4/5. Chiều cao main trên tất cả trang đọc `--az-header-main-height` và chế độ trong suốt được ưu tiên hơn nền mặc định.

`inc/header-colors.php` bổ sung 10 trường vào registry Colors của parent và xuất biến CSS qua stylesheet `az-header-storefront`. Giá trị lưu ở `az_theme_options.colors.menu_*`, được sanitizer màu hex của parent xử lý. Không thay đổi source parent hoặc tự ghi dữ liệu. Màu mặc định dùng cùng palette xanh trên tất cả trang, gồm hover và mục đang chọn; không có bộ biến màu riêng cho trang chủ.

Đã kiểm tra registry không trùng trường, sanitizer WordPress/parent, màu tùy chỉnh, mã hex ngắn, trường trống và dữ liệu màu không hợp lệ; lint PHP và diff whitespace.

## Footer theo ảnh tham chiếu

Tất cả trang dùng footer của trang chủ: nền sáng, tiêu đề xanh và thanh bản quyền xanh. Bốn cột gồm **Thông tin doanh nghiệp**, **Hỗ trợ khách hàng**, **Hồ sơ năng lực**, **Fanpage**; tablet 2 cột và điện thoại 1 cột. `css/footer-storefront.css` chứa toàn bộ CSS footer; không còn nhánh bố cục tối hoặc CSS footer riêng trong stylesheet trang chủ. Cột Hồ sơ năng lực thay danh sách sản phẩm bằng ảnh bìa từ General, có thể bấm để xem file hồ sơ. Khi chưa chọn ảnh hoặc ảnh đã bị xóa, cột này ẩn và ba cột còn lại chia lại chiều rộng. Mục Đối tác vẫn chỉ render trước footer trang chủ.

Thông tin liên hệ và pháp lý lấy từ **AZ Theme Advanced → General** (`page=az-theme-advanced&section=general`), nhánh `az_theme_options.general`. Cột Fanpage dùng thiết lập Social:

- `addresses`, `phones`, `emails`: hiển thị mọi mục đã nhập, theo thứ tự và nhãn cấu hình. Hotline/email có link gọi hoặc gửi thư.
- Cụm **Chỉ đường / Đỗ ô tô** dưới Fanpage dùng nguyên ảnh `icon/location-button-1.png` và `icon/location-button-2.png`, dạng pill nền trắng, viền hồng và bóng nhẹ. Chỉ địa điểm có nhập link Google Maps hợp lệ trong **General → Địa chỉ & Bản đồ → Danh sách địa chỉ** tự thêm một cụm bên dưới cụm trước, theo thứ tự đã lưu. Nhập **Link Google Maps / Chỉ đường** (`maps_url`) rồi nhấn **Save Changes**. Để trống link sẽ ẩn cả hai icon, dù có nhập địa chỉ hoặc iframe. Các dòng còn link tiếp tục hiển thị theo thứ tự đã lưu.
- **Chỉ đường** có liên kết mở Google Maps ở tab mới. Không tự tạo link từ địa chỉ. Khi chưa có mục trong Danh sách địa chỉ, chỉ hiện cụm nếu có link dự phòng hoặc link legacy hợp lệ. Danh sách đã có mục nhưng link đều trống sẽ ẩn toàn bộ cụm, không dùng link dự phòng thay thế. **Đỗ ô tô** chỉ là hình ảnh, không có link, thao tác click, hover hay focus bàn phím; đã bỏ trường Link Đỗ ô tô và logic tìm điểm đỗ khỏi code child.
- Footer không render **Iframe Google Maps**, kể cả khi đã lưu `maps_embed`, `google_maps_embed` hoặc dữ liệu legacy. Trường iframe vẫn dùng được cho các chức năng khác. Cụm icon dùng chung trên tất cả trang.
- `company_name`, `tax_code`, `business_registration`, `registration_date`, `registration_authority`: hiển thị trong cột thông tin doanh nghiệp; có đọc dự phòng các option legacy khi chưa lưu trường mới.
- `fax`, `registration_date`, `registration_authority`: trường child bổ sung vào nhóm liên hệ/pháp lý của General để nhập Fax, Ngày cấp và Nơi cấp.
- Fanpage và các nút mạng xã hội lấy trực tiếp từ **AZ Theme Advanced → Social** (`page=az-theme-advanced&section=social`). Facebook dùng URL `social.facebook` để nhúng Page Plugin cao 150 px và liên kết Xem Fanpage. Khi Facebook trống, footer hiển thị các kênh Social đã cấu hình thay cho iframe. Trường Fanpage riêng ở General đã bỏ để tránh cấu hình trùng; giá trị General cũ không còn điều khiển Fanpage. Các URL legacy vẫn dùng theo helper Social của parent khi chưa lưu trường Social mới.
- `footer_policies`: danh sách chính sách có thể thêm/xóa, kéo đổi thứ tự, sửa tiêu đề và link. Hỗ trợ URL https hoặc đường dẫn nội bộ bắt đầu bằng `/`. Lần đầu dùng 7 liên kết theo mẫu; xóa hết và lưu sẽ giữ danh sách trống.
- `copyright`: chỉ hiện thanh bản quyền khi có nội dung và tùy chọn Footer → Hiện thanh bản quyền đang bật.

Child bổ sung trường qua registry `AZ_Options_Registry` sau khi parent khởi tạo, dùng luồng lưu và sanitizer của parent. Không sửa source parent, không tự ghi đè option/database và không hardcode thông tin doanh nghiệp trong ảnh. Giá trị trống được ẩn; kiểm tra các đường dẫn chính sách đã có Page tương ứng trên website. Facebook có thể yêu cầu đăng nhập hoặc không hiển thị do thiết lập riêng của Fanpage.

Kiểm tra đồng bộ: `php wp-content/themes/az-theme-child/tests/footer-sync.php` kiểm tra HTML footer chung ở các điểm vào, ảnh/link hồ sơ, trạng thái bản quyền, ảnh trống hoặc không hợp lệ và một lần gọi `wp_footer()`. Fixture trình duyệt dùng template/CSS thật cho thấy footer trang chủ và trang trong có cùng kiểu hiển thị; desktop 1280 px và điện thoại 390 px không tràn ngang. Fanpage trong fixture dùng nội dung minh họa. Chưa chạy trên WordPress/database hosting thật.

Kiểm tra cụm bản đồ: `php wp-content/themes/az-theme-child/tests/footer-location-actions.php` kiểm tra bỏ trường/logic link Đỗ ô tô, điều kiện có link Chỉ đường, thứ tự nhiều địa điểm, ẩn icon khi link trống hoặc không hợp lệ, giữ địa chỉ liên hệ và hai PNG. `tests/footer-sync.php` kiểm tra mọi điểm vào footer đều hiển thị đủ các cụm, không có iframe Google Maps. Chưa chạy trên database WordPress/hosting thật.

## Sidebar danh mục cửa hàng

`css/catalog-sidebar.css` chỉ nạp trên shop và taxonomy sản phẩm, sau CSS WooCommerce của parent. Các selector được scope trong `.az-shop-sidebar` để sửa xung đột padding/font của link với CSS sidebar chung. Panel trắng, dòng danh mục cân tên/số lượng/nút mở; mục đang chọn dùng nền nhạt và vạch màu thương hiệu; nhóm con có đường dẫn thụt vào. Giữ nguyên tên, số lượng, liên kết và accordion của parent. Mobile tăng kích thước nút mở và giới hạn chiều cao danh sách cuộn.

## Cấu hình WooCommerce

Các thiết lập giao diện cửa hàng nằm trong **AZ Theme Advanced → WooCommerce** (`page=az-theme-advanced&section=woocommerce`). `inc/customizer.php` gỡ các hook của `WC_Shop_Customizer` trước khi plugin đăng ký panel, section, setting và control vào **Giao diện → Tùy biến**, đồng thời gỡ CSS/JavaScript riêng của mục này. Không sửa plugin hoặc parent, không xóa các option WooCommerce đã lưu; các mục Customizer khác, endpoint tài khoản trong menu và hoạt động cửa hàng tiếp tục dùng bình thường.

## Tỷ lệ ảnh sản phẩm

**AZ Theme Advanced → WooCommerce → Kích thước ảnh sản phẩm**: chọn **1:1**, **4:3**, **3:2**, **16:9**, **Theo tỷ lệ ảnh gốc** hoặc **Tỷ lệ tùy chỉnh**, rồi nhấn **Save Changes**. Tỷ lệ tùy chỉnh dùng hai số chiều rộng/chiều cao (1–100); ví dụ 5 và 3 tương ứng 5:3. Mặc định là hình vuông 1:1. Dùng chung cho gallery trang chi tiết, thẻ sản phẩm trên trang cửa hàng, các trang phân loại sản phẩm, **Sản phẩm liên quan** và **Sản phẩm đã xem**. Ảnh hiển thị đầy đủ bằng `object-fit: contain`, không cắt hoặc kéo méo. Chế độ ảnh gốc dùng chiều cao tự nhiên, nên ảnh khác tỷ lệ có thể cao khác nhau. Giữ nguyên các khóa cấu hình đã lưu và file ảnh trong Media.

`inc/product-display.php` đăng ký ba trường child qua registry/sanitizer hiện có, xuất tỷ lệ hợp lệ vào biến CSS dùng chung; `single-product.php` đọc cùng helper. `css/product-gallery.css` chỉ nạp trên trang chi tiết, cửa hàng và phân loại sản phẩm, sau CSS WooCommerce và thẻ sản phẩm của parent. Ảnh nằm trong khung cố định để kích thước gốc không làm khung tràn; chế độ ảnh gốc dùng chiều cao tự nhiên. Filter `woocommerce_product_get_image` dùng ảnh đầy đủ có srcset cho thẻ trong cửa hàng/phân loại và trang chi tiết (liên quan/đã xem) thay vì thumbnail đã crop, giữ thuộc tính alt/loading và ảnh dự phòng. Kiểm tra logic bằng `php wp-content/themes/az-theme-child/tests/product-image-ratio.php`. Chưa kiểm tra trên database WordPress/hosting thật.

Đã xác nhận bản xem trước trên desktop 1280 px và mobile 390 px: ảnh mẫu hiển thị đủ nội dung; khung 1:1, 4:3 và tỷ lệ tự nhập 5:3 đúng kích thước; chế độ ảnh gốc đổi chiều cao khi chuyển sang ảnh ngang; chuyển ảnh và phóng lớn hoạt động.

Hai cụm **Sản phẩm liên quan** và **Sản phẩm đã xem** đã được kiểm tra bằng renderer thẻ sản phẩm dùng chung và CSS thực tế trên desktop 1280 px/mobile 390 px: cả sáu chế độ nhận đúng tỷ lệ, ảnh không bị crop, bố cục không tràn ngang. Chế độ ảnh gốc giữ chiều cao riêng theo từng ảnh.

Đã kiểm tra thêm cửa hàng và danh mục với renderer thẻ thật của parent: cùng tỷ lệ đã lưu, ảnh vuông và ảnh ngang đều nằm đầy đủ trong khung; khung 4:3 không tràn trên mobile; chế độ ảnh gốc giữ chiều cao riêng của từng ảnh.

Icon mục lục Easy Table of Contents trong `style.css` dùng màu đen. Riêng phần `.az-product-tab-body` dựng icon danh sách và mũi tên bằng CSS, vì `wp_kses_post()` ở template sản phẩm loại bỏ SVG nội tuyến của plugin. Giữ nguyên bộ lọc HTML và thao tác bật/tắt mục lục; trang tin tức tiếp tục dùng icon gốc. Đã kiểm tra bản xem trước khi span icon không còn SVG.

## Số lượng sản phẩm liên quan và đã xem

Vào **AZ Theme Advanced → WooCommerce → Sản phẩm bổ sung** (`page=az-theme-advanced&section=woocommerce`), đặt riêng **Số sản phẩm liên quan** và **Số sản phẩm đã xem**, rồi nhấn **Save Changes**. Mỗi trường nhận 1–50 sản phẩm, mặc định 5 khi chưa cấu hình. Công tắc hiển thị hiện có vẫn bật/tắt từng khối. Đây là số lượng tối đa; số thực tế phụ thuộc sản phẩm liên quan và lịch sử đã xem của khách. Lưới giữ 5 cột trên desktop và xuống dòng khi có nhiều sản phẩm.

`single-product.php` của child kế thừa bố cục hiện tại của parent và đọc giới hạn từ `az_theme_options.woocommerce`. `inc/product-display.php` đăng ký trường quản trị và thay hàm tracker pluggable của parent để cookie lưu đủ số mục đã chọn, cộng sản phẩm hiện tại; lịch sử cũ sẽ được bổ sung khi khách tiếp tục xem sản phẩm. Không sửa parent hoặc tự ghi đè thiết lập đã lưu.

Đã kiểm tra fixture PHP với registry/sanitizer/helper thật của parent và hai khối render trong template child: giới hạn lưu, mặc định 5, số lượng riêng 3/8, 10/20, 50/50, cookie đủ dung lượng, loại sản phẩm hiện tại và lịch sử trùng, lịch sử rỗng, công tắc bật/tắt. Chưa kiểm tra tích hợp trên WordPress/database thật.

## Khối thông tin công ty trong trình sửa sản phẩm

`inc/product-admin.php` thay renderer metabox pluggable của parent, chia nhóm Thông tin sản phẩm, Liên kết mua hàng, Thông số kỹ thuật và Ứng dụng thực tiễn. `css/product-admin.css` chỉ nạp tại màn hình thêm/sửa sản phẩm, chỉ tác động đến `#az_product_extra_info`. Ô nhập có nhãn phía trên, ba cột khi đủ rộng và một cột khi khối hẹp; các editor vẫn dùng `wp_editor` với nút thêm media, chế độ trực quan/mã và cấu hình hiện có. Giữ nguyên nonce, tên trường/meta và handler lưu của parent. Đã lint PHP và kiểm tra diff; chưa xác nhận trực quan trên WordPress thật.

## H1 và Meta description trang chủ

Trong **AZ Theme Advanced → Branding → Metadata** (`page=az-theme-advanced&section=branding`), **Homepage SEO H1** điều khiển H1 và **Homepage Meta description** điều khiển mô tả SEO trang chủ. Để trống H1 sẽ dùng **Tiêu đề trang web**; để trống Meta description sẽ dùng **Dòng mô tả** trong Cài đặt → Tổng quan/Nhận diện trang web. Không tự ghép khẩu hiệu vào H1.

Trang chủ giữ H1 do parent xuất qua `wp_body_open`; template child không xuất thêm H1 khi đang ở trang chủ. Page dùng template Trang chủ nhưng không được chọn làm trang chủ vẫn xuất một H1 theo cùng helper. Cấu hình và xử lý Meta description nay thuộc `az-theme/inc/core/branding-seo.php`, được mọi child kế thừa. `inc/branding-seo.php` của child chỉ mở rộng phạm vi cho Page template `front-page.php`; không đăng ký trường hoặc xuất thẻ meta riêng. Khi Rank Math hoặc Yoast hoạt động, parent dùng filter description của plugin; khi không có hai plugin này, parent xuất một thẻ meta trong `wp_head`. Không thay description của sản phẩm, bài viết hay các trang khác. Giá trị `branding.home_meta_description` đã lưu tiếp tục dùng được; hướng dẫn viết child mới có trong `az-theme/docs/BRANDING-SEO.md`.

Đã kiểm tra render cô lập với helper H1 thật của parent và template trang chủ thật của child: đúng một H1, nội dung tùy chỉnh và fallback khi để trống; đăng ký/sanitize Meta description; mã hóa ký tự, xuống dòng, dữ liệu sai kiểu; nhánh theme tự xuất và nhánh nhường thẻ meta cho plugin. Chưa xác nhận HTML trên WordPress/database hosting thật.

## Ghi chú kiểm tra bố cục cũ

Đã lint tất cả PHP của child, kiểm tra cú pháp JavaScript và SVG, kiểm tra diff whitespace. Render fixture PHP cô lập cho các trường hợp có WooCommerce, thiếu WooCommerce, dữ liệu trống, danh mục đã lưu và trang bảo vệ mật khẩu. Kiểm tra query lấy 10 sản phẩm/nhóm, phần trăm giảm giá biến thể và bảo toàn dữ liệu khi lưu.

Bản local không có thư mục uploads; cấu hình database trỏ tới socket hosting. Quyền mở localhost bằng trình duyệt bị từ chối, nên chưa kiểm tra trực quan hoặc chạy tích hợp trên WordPress/database thật. Cần kiểm tra trên website có dữ liệu thực tế sau khi cập nhật theme child.

Lần đầu kích hoạt, child sao chép các theme-mod chưa có từ parent để giữ logo, vị trí menu và thiết lập Customizer. Option `az_theme_options` tiếp tục dùng chung.

## All in One Contact — Kiểu 1 và Kiểu 2

Trong **AZ Theme Advanced → Social → All in One Contact**, chọn **Kiểu 2 – Nút liên hệ mở rộng tất cả Social**, bật Hiển thị All in One Contact và lưu. Danh sách chỉ giữ Kiểu 1 và Kiểu 2. Kiểu 1 là mặc định, hiển thị icon Messenger, Zalo và Hotline; các giá trị cũ `buttons`, `expand`, `cards`, `dock` hoặc không hợp lệ dùng Kiểu 1 ở quản trị và frontend. Các liên kết và số điện thoại đã lưu được giữ nguyên.

Với Kiểu 1, nhập đường dẫn nhắn tin Facebook (ví dụ `https://m.me/tenfanpage`) tại **Social → Messaging → Messenger** để hiện nút tròn xanh, tia sét trắng phía trên Zalo và Hotline. Để trống Messenger để ẩn riêng nút này. Renderer, icon và CSS nằm trong theme cha `az-theme`, dùng được khi chạy theme cha hoặc kế thừa qua theme con.

Nút tròn Liên hệ nằm ở góc dưới bên trái, mặc định đóng. Bấm để mở một bảng icon phía trên gồm các URL đã nhập trong Social Networks/Messaging: Facebook, YouTube, TikTok, Instagram, LinkedIn, Pinterest, X, Zalo, Messenger và WhatsApp. Hotline All in One là nút gọi bổ sung nếu số hợp lệ. Kênh trống hoặc URL không hợp lệ được ẩn; không có kênh thì không render nút hoặc nạp asset. Icon dùng chung với footer. Bấm lại, bấm bên ngoài, di chuyển focus ra ngoài hoặc nhấn Escape để đóng; Escape trả focus về nút liên hệ. Màn hình thấp cuộn danh sách icon trong bảng. Có nhãn hỗ trợ đọc màn hình, focus bàn phím và chế độ giảm chuyển động.

`inc/floating-contact.php` thay điểm gọi hook bằng dispatcher: Kiểu 1 dùng renderer và CSS của parent; Kiểu 2 dùng `css/social-contact.css` và `js/social-contact.js`. `php wp-content/themes/az-theme-child/tests/social-contact.php` kiểm tra danh sách chỉ còn hai kiểu, đăng ký/lưu lựa chọn, chuyển kiểu cũ về Kiểu 1, đủ 10 mạng xã hội và Hotline, URL query, trường trống, legacy URL, công tắc tắt và chỉ một widget. Đã kiểm tra fixture thực tế trên trình duyệt ở desktop 1280×720, mobile 390×600 và màn hình thấp 568×320; kiểm tra mở/đóng bằng chuột, Enter và Escape. Chưa chạy trên WordPress/database hosting thật.


### Cấu hình ảnh hồ sơ trong General

**AZ Theme Advanced → General → Hồ sơ ở chân trang** (`page=az-theme-advanced&section=general`) gồm:

- **Tiêu đề hồ sơ ở chân trang** (`footer_profile_title`): mặc định Hồ sơ năng lực.
- **Ảnh bìa hồ sơ** (`footer_profile_image`): chọn/thay/xóa ảnh trong Media; chỉ render attachment ảnh hợp lệ, giữ tỷ lệ gốc và tự co theo chiều rộng cột.
- **Link file hồ sơ** (`footer_profile_url`): dán URL file PDF hoặc trang xem hồ sơ. Bấm ảnh mở tab mới với `noopener noreferrer`, không dùng thuộc tính download. Việc xem trực tiếp PDF phụ thuộc trình duyệt và header phản hồi của nơi lưu file. Link trống thì ảnh hiển thị đơn thuần; ảnh trống thì ẩn cột. Không còn truy vấn danh mục trang chủ để render footer.

Các trường dùng registry, Media picker và sanitizer hiện có của parent; toàn bộ mã bổ sung nằm trong child. Đã kiểm tra render đồng nhất ở mọi điểm vào footer, lưu ID ảnh/link, bỏ danh mục cũ, trạng thái trống và URL không an toàn. Fixture desktop 1280 px, mobile 390 px không tràn ngang; bấm ảnh mở đúng trang xem thử ở tab mới. Ảnh bìa trong screenshot chỉ là minh họa; cần chọn ảnh và link hồ sơ thật ở General. Chưa kiểm tra trên database WordPress/hosting.

## Đường dẫn danh mục và sản phẩm

`inc/product-permalinks.php` bỏ tiền tố danh mục sản phẩm và tạo URL theo đầy đủ cây danh mục trên website đang dùng đường dẫn tĩnh:

- Danh mục cha: `/ten-danh-muc-san-pham/`.
- Danh mục con: `/ten-danh-muc-san-pham/ten-danh-muc-con/`.
- Sản phẩm: `/ten-danh-muc-san-pham/ten-danh-muc-con/ten-san-pham/`.

Sản phẩm thuộc nhiều danh mục dùng danh mục sâu nhất; cùng độ sâu thì ưu tiên ID nhỏ hơn để URL ổn định. Hook WooCommerce `wc_product_post_type_link_product_cat` vẫn cho phép tích hợp SEO chọn danh mục chính, miễn danh mục đó còn được gán cho sản phẩm. Sản phẩm chỉ thuộc danh mục cha có URL `/danh-muc-cha/san-pham/`; sản phẩm chưa gán danh mục dùng danh mục mặc định của WooCommerce. Cây nhiều hơn hai cấp được giữ đầy đủ.

Theme giữ các quy tắc URL cũ để chuyển hướng 301 tới URL mới, bao gồm phân trang và feed; tham số lọc/sắp xếp/theo dõi được giữ lại. Các thao tác giỏ hàng, POST, preview và AJAX không đi qua chuyển hướng này. Không thay cấu hình đường dẫn chung, option permalink WooCommerce hoặc URL tin tức. Quy tắc được cập nhật tự động một lần sau khi nạp mã mới và sau mỗi lần thêm/sửa/xóa danh mục; không ghi `.htaccess` hoặc cập nhật lại ở mọi lượt truy cập.

Page đang tồn tại, trang Cửa hàng/Giỏ hàng/Thanh toán/Tài khoản và tiền tố WordPress có ưu tiên khi trùng đường dẫn. Danh mục hoặc sản phẩm bị trùng giữ URL cũ để vẫn truy cập đúng nội dung. Sản phẩm trùng đường dẫn với danh mục con cũng giữ URL cũ; đổi slug nếu muốn dùng đường dẫn không tiền tố cho các trường hợp này.

Chạy `php wp-content/themes/az-theme-child/tests/product-permalinks.php` để kiểm tra API permalink và parser request thật của WordPress với dữ liệu trong bộ nhớ: cây danh mục, chọn danh mục chính, permalink trong trình soạn thảo, phân trang/feed, URL cũ, chuyển hướng, xung đột Page/danh mục và làm mới quy tắc. Đã lint PHP và chạy các bài kiểm tra child hiện có. Chưa xác minh trên database WordPress hoặc hosting thật.
