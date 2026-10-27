# Child API Quickstart — runtime preview 0.1.0

Phase04 naming: **Flexora product → FLX code prefix**, `FLX\*` namespace, `flx/*` hooks. No aliases for the former preview API. Using `Contracts/V1` does not claim complete runtime v1.

## Skip Link có sẵn — không cần Child setup

Native Parent tự đăng ký/bật module `flx.accessibility` ở frontend, tự preflight component `flx.accessibility.skip-link` tại `wp`, tự queue CSS/JS và render tại `wp_body_open:1`. Child chỉ cần template WordPress hợp lệ gọi `wp_head()`, `wp_body_open()`, `wp_footer()` và có main content. Không đăng ký Spec, gọi asset, khai JS hoặc thêm skip link thứ hai. Không phụ thuộc theme reference. Feature default tốt, không có Advanced setting mới.

Extension/test cần khai một component ở route khác vẫn dùng workflow bên dưới; riêng skip link mặc định không yêu cầu Child gọi thủ công. Code-level module state filter `flx/modules/enabled` dành cho integration/testing; không DB settings.

Target Public API vẫn là **1.0.0**; implementation hiện mới là preview **0.1.0**. `flx_supports('api', '^1.0')` trả false. Không có option/data/map/template/icon runtime giả. Foundation chưa là Parent package có thể activate, chưa có component AZ production được chuyển.

## Dùng component có sẵn

**Using Parent feature ≠ registering a new feature.** Khi Parent cung cấp component, Child chỉ cần ID và props công khai; không cần SpecV1, internal class, CSS/JS/library/provider IDs. `site.card` dưới đây là ví dụ một ID đã đăng ký (cách đăng ký chỉ dành cho extension ở cuối).

Trong `functions.php`, khai hook route/template; không gọi Parent API ngay lúc Child file load trước Parent:

```php
add_action( 'wp_enqueue_scripts', function () {
    if ( is_page_template( 'page-example.php' ) ) {
        flx_use_component( 'site.card' );
    }
}, 20 );
```

Trong template sau header, lấy `$title` từ nguồn nội dung của trang:

```php
flx_render( 'site.card', array( 'title' => $title ) );
```

Workflow mặc định: **khai component trước → render một lần**. Component tự biết CSS/JS/behavior/provider/dependencies. Parent đổi asset hoặc implementation provider, Child vẫn gọi cùng component ID. Không render hai lần để preflight. Template Resolver/Map tương lai cũng có thể gọi `flx_use_component()` cho danh sách component; chưa có Map runtime trong phase này.

## Bảy API thông dụng

| API | Kết quả |
|---|---|
| `flx()` | Read-only version/supports/diagnostics; không get_service |
| `flx_supports('feature', range)` | Capability hiện có, không boot; `api` kiểm runtime preview version |
| `flx_module('id')` | Trạng thái declared/enabled/available/booted scopes/reason/version, không activate |
| `flx_use_component('id')` | Preflight dependency graph, trả UsageResultV1; không render/enqueue ngay |
| `flx_component('id', $props)` | RenderResultV1: html/usage/errors/variant/view_contract_version, không echo |
| `flx_render('id', $props)` | Echo đúng một lần, trả RenderStatusV1; dùng một trong hai API render |
| `flx_asset('id')` | Advanced/standalone asset usage; không cần cho workflow component |

## Thời điểm và lỗi

- Preflight sau `after_setup_theme:40`, trước enqueue flush priority100 và trước `wp_head`. Hook enqueue priority20 như trên đáp ứng window. Đăng ký definition không tự load assets.
- Engine queue dependency đúng một lần ở enqueue pass. Script có footer strategy được WordPress in ở footer; Child không cần biết script nào.
- Render sau head chỉ thành công nếu toàn bộ dependency đã enqueue. Có thể dùng asset shared đã được component khác preflight; không cần preflight lặp cho từng instance.
- Thiếu CSS khi phát hiện sau head → `late_component_style_usage`; thiếu script → `late_component_script_usage`; window enqueue đã qua nhưng head chưa chạy → `component_enqueue_window_closed`. HTML rỗng, renderer không chạy; diagnostics đọc qua `flx()->diagnostics()`.
- Component có script không được render sau `wp_print_footer_scripts`: `component_script_phase_closed`. Phase này không hỗ trợ late script-only component, AJAX mount hoặc lazy loader; quy tắc component đồng nhất CSS/JS. Component static không asset vẫn có thể render muộn.
- Missing/disabled dependency không tự bật module hoặc tải tất cả CSS. Result/diagnostics không expose stacktrace hoặc props/secret.

## Advanced: standalone assets

Chỉ khi asset độc lập với component hoặc integration thực sự cần, khai `flx_asset('site.standalone-style')` trong cùng window trước enqueue/head. Đây không phải cách Child đoán CSS/JS của component. Không dựa vào request asset sau enqueue để engine tự flush lại. Component usage đã resolve dependency nên không lặp direct asset call.

## Extension: đăng ký component mới

Chỉ extension author cần SpecV1. Search Parent trước; nếu component đã có thì tái sử dụng. Ví dụ component site-specific mới, không phải hướng dẫn đăng ký lại feature Parent:

```php
use FLX\Contracts\V1\SpecV1;

add_action( 'flx/register', function ( $register ) {
    $register->asset( new SpecV1( array(
        'id' => 'site.card-style', 'owner' => 'child:example', 'version' => '1.0.0',
        'type' => 'style', 'handle' => 'example-card',
        'source' => get_stylesheet_directory_uri() . '/assets/card.css',
        'dependencies' => array(), 'context' => array( 'frontend' ),
    ) ) );
    $register->component( new SpecV1( array(
        'id' => 'site.card', 'owner' => 'child:example', 'version' => '1.0.0',
        'dependencies' => array(), 'assets' => array( 'site.card-style' ), 'behaviors' => array(),
        'validate_props' => function ( $props ) {
            return array_key_exists( 'title', $props ) && is_string( $props['title'] );
        },
        'renderer' => function ( $props ) {
            return '<article class="example-card"><h2>' . esc_html( $props['title'] ) . '</h2></article>';
        },
    ) ) );
} );
```

Chỉ phần khai definition biết asset IDs. Consumer của `site.card` dùng hai call ở đầu tài liệu; không gọi internal services hoặc hiểu provider.
