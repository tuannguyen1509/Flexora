# Phase 03A — Foundation Stabilization

Ngày 27/10/2026. Phạm vi: chỉ foundation Phase03 và tests/docs. Không production feature migration, database write, bridge production hoặc vendor frontend mới.

## 1. Runtime API version trung thực

Target Public API contract ở Phase02 vẫn **1.0.0**, không sửa architecture history. Runtime `Kernel::API_VERSION` hiện là **0.1.0**, tách về ý nghĩa với Parent foundation version (hiện cũng 0.1.0). `flexora()->api_version()` trả 0.1.0; `flexora_supports('api', '^1.0')` false; `^0.1` true. `api: *` chỉ hỏi có implementation API bất kỳ, không chứng nhận full v1.

Sửa claim 1.0.0 quá sớm của Phase03. Namespace Contracts/V1 và descriptor/view versions không phải completeness signal. Chưa tạo `flexora_option/data/map/template/icon()` hoặc fake result. Chỉ khi mandatory target contract thực thi đầy đủ và qua acceptance mới được nâng runtime API lên 1.x; không bump chỉ vì thêm đủ tên hàm.

## 2. Component usage preflight

Public API bổ sung theo yêu cầu Phase03A:

```php
flexora_use_component( $id, $context = null ); // UsageResultV1
```

Call sau registry seal, trước enqueue/head. Kernel xác minh current context, rồi ComponentRegistry duyệt component.dependencies/assets/behaviors → behavior.provider/assets → provider.dependencies/assets → asset.dependencies. Cùng đường traversal được render dùng để cross-check. Không renderer, props filter, content query hoặc DB write trong preflight. Availability kiểm module/dependencies, không lazy-enable hoặc boot.

Result immutable: accepted bool; assets là resolved asset IDs cho diagnostic/advanced tooling; usage là typed asset/behavior entries; errors là redacted codes/IDs. Child thông thường chỉ cần component ID và kiểm accepted/errors khi cần, không cần đọc asset IDs. Không thêm nhiều DTO hoặc container abstraction.

Preflight validate toàn graph trước ghi usage; repeated calls không enqueue/register lặp. Registration chỉ là khai khả năng, preflight chỉ thu usage, enqueue pass mới gọi WordPress. Hai components hero-slider/product-carousel fixture dùng chung behavior/provider/script được enqueue đúng một lần. Đây là synthetic descriptor pipeline, không migrate slider production hay thêm thư viện.

## 3. Lifecycle quyết định rõ

| Thời điểm | Hành vi |
|---|---|
| Sau after_setup_theme:40, trước enqueue flush | Route gọi component preflight; không render. Có thể gọi trong wp hoặc wp_enqueue_scripts priority20 |
| wp_enqueue_scripts/admin_enqueue_scripts:100 | WP-register/enqueue đúng requested graph, dedupe physical handle; không load mọi component |
| Sau flush, trước head | Dependency chưa enqueue → component_enqueue_window_closed; không âm thầm đợi flush khác |
| Sau wp_head | Missing stylesheet → late_component_style_usage; missing script → late_component_script_usage; reject HTML, không chạy renderer |
| Dependency đã được enqueue sẵn | Có thể render/reuse sau head, kể cả asset shared do component khác khai; không cần per-instance preflight |
| Footer | Footer script đã enqueue trước đó do WP in; không chủ động inline/eval/inject từ renderer |
| Sau wp_print_footer_scripts | Component có script → component_script_phase_closed, tránh HTML mới sau phase init/print; static asset-free component vẫn hợp lệ |

Late script policy cố ý dùng cùng preflight window với CSS. WordPress có khả năng enqueue footer script muộn, nhưng foundation này không hứa late component mount. Không loader browser/AJAX/lazy runtime. `flexora_asset()` giữ lại cho standalone/integration; advanced caller vẫn phải khai usage trước flush. Không tự re-enqueue raw Child-dequeued handles để vượt quyết định của Child.

## 4. Child contract và future resolver

Default workflow là `flexora_use_component('site.card')` rồi `flexora_render('site.card', $props)`. Component đổi CSS A→B hoặc đổi provider vẫn không đổi consumer. Chỉ extension author đăng ký definition mới mới dùng SpecV1/asset metadata; using Parent component không phải registering new feature. [Quickstart](CHILD-API-QUICKSTART.md) đã tách rõ hai vai trò.

Template Resolver/Map tương lai có thể lặp qua danh sách component và gọi cùng preflight API; không cần thêm dependency resolver song song. Chưa implement Template Map, dynamic discovery, Page creation, UI, editor hoặc repeater. Descriptor dependency graph hiện static; dynamic components phải nằm trong khai báo preflight, không được giấu trong renderer rồi yêu cầu CSS sau head.

## 5. Verification và compatibility

- Trước: 188 assertions/37 PHP files. Sau: **252 assertions/38 PHP files**, 12 fixture processes trên PHP7.4.33. Hai assertion version cũ được sửa expected behavior theo yêu cầu; không bỏ regression nào. Hai lượt preflight native/bridge thêm 32 assertions mỗi lượt.
- Giữ PASS Child-first, early require/remove_action, pluggable, disabled module, scope dispatch, registrations/dependency graph và assets fixtures.
- Test mới: preview negotiation/no fake APIs; component-only stylesheet usage; behavior/provider shared asset dedupe; recursive dependencies; no render during preflight; idempotence; unsupported context; late style/script/cutoff; render sau head một lần; no DB/query. Simulated Child flow không có direct flexora_asset call.
- PHP7.4.33 CLI chạy lại toàn suite; PHP8.4.27-1 executable sẵn trong TEMP tiếp tục bị Windows Application Control chặn: **SKIP**. Không bypass security policy. PHP8.4.24 PASS do user cung cấp từ review độc lập là evidence bên ngoài, không phải lần chạy của agent trong Phase03A.
- WordPress core/DB/WP_Hook/CSS printing/footer execution/browser integration vẫn **chưa chạy**. Fixture PASS chỉ chứng minh API calls, graph và lifecycle mô phỏng. Không chốt support minimum mới.
- Source baseline **435/435 file nguyên SHA-256 + size**, zero changed/missing/added. Không sửa az-theme/az-theme-child, dữ liệu hoặc Phase01/02.

## 6. Architecture impact và giới hạn

Không deviation khỏi mục tiêu Phase02: sử dụng đúng usage-preflight → WP handles → render, một engine/hai profile, Child không biết provider. Bổ sung function preflight và sửa preview version được user yêu cầu rõ trong Phase03A; ghi tại implementation layer, không tự sửa ADR hoặc mandatory API target. Full v1 implementation và production compatibility vẫn deferred.

Files runtime sửa: public-api.php, Core/Kernel.php, Components/ComponentRegistry.php, Assets/AssetRegistry.php. Tests: cập nhật version expectations trong foundation.php; thêm component-preflight.php; runner chạy cả hai profile. Docs cập nhật README, IMPLEMENTATION-NOTES, CHILD-API-QUICKSTART, TEST-REPORT, SOURCE-INTEGRITY và report này. Raw output mới: [tests/last-run.txt](tests/last-run.txt); kết quả Phase03 cũ được giữ ở [tests/phase-03-last-run.txt](tests/phase-03-last-run.txt).

**PHASE 03A FOUNDATION STABILIZED — NO PRODUCTION FEATURE MIGRATION**
