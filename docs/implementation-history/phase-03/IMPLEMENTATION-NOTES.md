# Implementation Notes — Phase 03

> Phase04 normative update: Flexora product uses FLX code prefix. Implementation doc API examples were normalized now; this naming decision did not exist in Phase03. The old two-profile/compatibility paragraphs below are historical and superseded. Engine has only flx-native metadata, no compatibility manifest/runtime. New native seam and the explicit user-approved architecture reversal are documented in [Phase04](PHASE-04-FLX-ACCESSIBILITY.md). Runtime API remains preview 0.1.0, no aliases for former preview names.

Ngày: 27/10/2026. Input đã đọc: ba báo cáo chính Phase01; Architecture/Public API/Legacy/Module/Asset/Child/AI rules Phase02 và ADR 002/003/006/008. Authorization Phase03 giới hạn foundation + harness trong thư mục mới.

## Quyết định và architecture alignment

Không thay architecture/ADR và không sửa lịch sử Phase01/02. **Phase03A correction:** runtime API implementation là `0.1.0 preview`; target contract vẫn `1.0.0`. Phase03 đã claim runtime API `1.0.0` quá sớm; nay `api_version()` trả `0.1.0` và `supports('api','^1.0')` false. Namespace `Contracts/V1` chỉ contract đích, không bằng chứng implementation hoàn tất. Xem [Phase03A specification/report](PHASE-03A-STABILIZATION.md). API preflight mới là bổ sung được user yêu cầu cho cơ chế preflight vốn có trong blueprint, không thay architecture hoặc ADR.

Một engine `src/`, một composition root nội bộ `Core/Runtime.php`. `profiles/` chỉ metadata slug/compatibility/entry; `bootstrap.php` chọn đúng một profile. Lần initialize thứ hai cùng profile không attach lại hooks; profile khác bị reject. Không tạo theme `flexora/`, không tạo gói az-engine/native-engine riêng.

`Contracts/V1` là surface công khai; `FrameworkV1` chỉ đọc version/supports/diagnostics. Child không được dùng constructor Kernel, Runtime hoặc internal registry. `SpecV1` là immutable descriptor dùng chung cho năm loại definition, giúp tránh nhiều lớp spec rỗng. Các result DTO có lớp riêng vì return contract độc lập. PHP magic getter dùng private array; mutation/unset bị từ chối; array trả theo value.

## Lifecycle và request scope

| Phase | Implementation / behavior |
|---|---|
| Child functions first | Fixture khai callback `flx/register`; không dùng Parent service trước bootstrap |
| Parent load | Autoload + public declarations + composition; chỉ attach lifecycle dispatchers, không query/write/enqueue/seed |
| `after_setup_theme:20/25` | Legacy fixture extension callbacks trước registration mới; không dựng Advanced registry thật |
| `after_setup_theme:30` | `Kernel::register()` phát `flx/register` một lần; immutable spec vào RegistrarV1 |
| `after_setup_theme:40` | Seal, dependency validation, collision/cycle detection; saved Registrar từ chối mọi đăng ký muộn |
| Scope boot | Frontend `wp`, admin/AJAX `admin_init`, REST `rest_api_init`, cron/CLI `init`; chỉ boot scope tương ứng sau seal |
| Usage → enqueue | Usage trước enqueue pass; flush ở enqueue hooks priority100; WP registration/enqueue chỉ asset được yêu cầu |

Module fixtures chỉ boot scoped UI hooks, **không khai CPT/route/job**. Descriptor endpoint/CPT/init-phase router, external provider election, stop/disable commands và activation plans chưa implement; không dùng scheduler foundation để migrate module có các yêu cầu đó. `init` không boot frontend UI. Scope tự xác định từ flags WordPress, không từ props/map input. Context hiện có scope/site/stylesheet/locale khi globals có; object/capability/instance enrichment chưa có storage/template engine nên để null/empty, không query để giả đủ context.

Kernel giữ registration state/registry sealing, profile, references đến registry/module coordinator, request context và diagnostics. Module status giữ boot state theo scope. State change command, persistence và auto-enabling không tồn tại.

## Descriptor subset hoạt động

Mỗi spec bắt buộc có `id`, `owner`, `version`, `dependencies`. ID là `[a-z][a-z0-9_.-]*`; version descriptor ở phase này dùng exact `major.minor.patch`. ID unique trong từng registry; physical asset handle unique toàn asset registry. Same ID different owner vẫn reject; không có implicit replace/override. Registration order là WordPress priority + insertion order; graph validation chạy sau toàn window nên cho phép forward references.

| Kind | Field bổ sung đã hoạt động |
|---|---|
| Asset | type style/script, handle, source, context scopes; optional module, strategy head/footer, override_policy=reference |
| Component | renderer callable trả string, assets, behaviors; optional module, validate_props callable, variant/view_contract_version metadata |
| Module | enabled bool, available bool, scope list, capabilities list, boot callable; dependencies là module IDs |
| Provider | assets, dependencies provider IDs; declaration-only, không tự init JS |
| Behavior | provider ID, assets, dependencies behavior IDs; declaration-only, không tự init JS |

`dependencies` luôn là IDs cùng kind, không phải arbitrary class/callback/service locator. Cross-kind edges: component.assets/behaviors, behavior.provider/assets, provider.assets và definition.module. Component dependency kiểm usable và thu usage, **không tự render component dependency**. Missing/cycle/cross-type style↔script edge bị block. Module disabled hoặc dependency disabled không tự enable; blocked dependent không boot.

Descriptor advanced metadata của target (version constraints per dependency, provider replacement, view/variant resolver, platform gates, permissions/routes/jobs, font strategy, content hashes/localization/inline slots) chưa có runtime. Field ngoài subset bị reject với `unsupported_definition_field`, không silently ignore requirements rồi coi definition là supported. Registrar chỉ expose năm method thực thi, không có stub view/map/field APIs. SemVer range helper hiện hỗ trợ `*`, exact `x.y[.z]`, `=`, `>=`, caret; grammar khác fail closed. Chưa có Child package-manifest negotiation hoặc full SemVer expression parser.

## Asset và component behavior

`Assets/AssetRegistry` thu logical usage, topological dependencies và bridge sang `wp_register_style/script`, `wp_enqueue_style/script`. Không loader JS riêng, không vendor. Before enqueue, validate whole requested graph để tránh load một nửa khi root handle conflict. Used dependency chỉ enqueue một lần dù nhiều component/flush yêu cầu. Không enqueue asset chỉ vì đăng ký/module enabled.

Code-owned source URI do trusted registration cung cấp; không nhận URL từ `flx_asset()` hay request. `override_policy=reference` chỉ dùng handle WP đã đăng ký, không thay source/version của core/plugin. Handle đã có mà chưa explicit reference bị conflict. Trong future az-v1 packaging, descriptor handle phải là physical old handle; foundation chưa map tất cả production handles hoặc tạo aliases. Không re-enqueue asset đã flush sau raw Child dequeue trong cùng request.

`ComponentRegistry` check dependencies, filter `flx/component/props` và revalidate props, collect usage, gọi renderer một lần. Renderer return string, không echo. Output buffer chặn accidental output/error; failures trả HTML rỗng, error code, không echo exception/secret. Renderer code phải escape output theo context; registry không đoán HTML sanitizer để thay trách nhiệm view. No arbitrary path include. Production view fallback/override resolver chưa implement.

## Compatibility harness

Phase03A: route gọi `flx_use_component(id)` sau seal và trước enqueue flush/head. Cùng dependency traversal được render gọi lại để cross-check; preflight không gọi props filter hoặc renderer. `UsageResultV1` có accepted/assets/usage/errors; không thêm DTO mới. Thiếu asset chưa enqueue khi flush window đã qua → component_enqueue_window_closed; sau head → late_component_style_usage hoặc late_component_script_usage, HTML rỗng. Dependency đã enqueue qua component khác được tái sử dụng; component static không asset có thể xuất hiện muộn. Không tự enqueue muộn từ render. Script footer được queue cùng preflight, WP in footer; component có script không render sau wp_print_footer_scripts. Lazy mount/dynamic browser loader vẫn deferred. Template Resolver tương lai gọi cùng API, không phải duyệt assets riêng.

`Compatibility/AzV1/manifest.php` ghi protected slug/paths/keys và `production_compatibility=false`. Synthetic `tests/fixtures/early-contact.php` khai function và attach string callbacks35/5 trước Kernel. Child tháo đúng callback/priority. Parent require_once + Hook Ledger không gắn lại. Ledger giữ record sau external removal (tombstone), không `ensure_registered`.

Synthetic `parent-pluggable.php` dùng function_exists guard, hook string dùng function Child khai trước. Không define hoặc migrate `az_track_recently_viewed_product`, `az_product_extra_info_meta_box_html` thật. Tests không require source production AZ; marker/DOM/option/mods/Woo/rewrite/old handles chưa parity-test.

Hook Ledger là tracker cho WordPress add_action/add_filter, không thay WordPress dispatcher. Track hook/callback identity/priority/accepted_args/owner/profile. Module boot nhận public HookRegistrarV1, không ledger. Exception khi boot rollback các hooks mới attach qua registrar và block module; không thể rollback side effect tùy ý từ extension tự bypass boundary. Vì vậy boot contract cấm DB writes/query content/schedule/HTTP; feature setup sau này cần operation riêng.

## Data và release gates

Lookup phân biệt missing với null/false/0/empty string/empty array; ReadOnlyBindingV1 chỉ interface. Không có AZ adapter hoặc writer. Data/Fields/Templates có README boundary thay vì placeholder class rỗng. Không có migration coordinator/command bus/service container không cần thiết.

Không chốt minimum PHP/WP/Woo mới. Source dùng cú pháp PHP truyền thống; kiểm thực tế PHP7.4.33 không chứng nhận toàn platform range. PHP8.4 executable bị Windows Application Control chặn nên không chạy; không bypass policy. WP core/DB/Woo/browser chưa có; xem test report. Trước feature cutover cần WordPress lifecycle thật, real WP_Hook mutation/priority, old Child parity, physical-handle inline/dequeue, platform matrix và snapshot/rollback riêng. Không suy từ fixture sang production compatibility.
