# Public API Contract v1

Status: thiết kế frozen for review, chưa implementation. Nguồn constraints: [Phase 01 Child contract](../audit-phase-01/PARENT-CHILD-CONTRACT.md). Public functions là façade mỏng, internal classes thay được. WordPress API hợp lệ vẫn dùng trực tiếp; cấm Child lấy service nội bộ Flexora bằng class/global/container.

## Entry points

Signature dưới là notation specification, không phải PHP source để nạp.

| API v1 | Return / semantics |
|---|---|
| `flexora()` | Read-only `FrameworkV1`: `api_version()`, `parent_version()`, `supports(feature, range)`, `diagnostics()` redacted. Không `get_service()`, không implicit boot/mutation. |
| `flexora_option(path, default = null, scope = site)` | Raw value đã normalize theo binding; default **chỉ khi missing**. Không escape HTML và không fallback theo truthiness. |
| `flexora_data(binding, context)` | `LookupV1 {present,value,source,schema_version,revision}`; giữ provenance, dùng khi cần phân biệt missing/null. Không write. |
| `flexora_component(id, props = [], context = current)` | `RenderResultV1`, trả HTML và usage/assets/errors; không tự echo. Unknown ID không include path tùy ý. |
| `flexora_render(id, props = [], context = current)` | Echo đúng HTML từ component API; return `RenderStatusV1` có errors. Chỉ gọi một trong render/component để không duplicate output. |
| `flexora_template(id, context, props = [])` | RenderResult theo Template registry/Map; không nhận filesystem path từ input user. |
| `flexora_icon(id, props = [])` | RenderResult SVG/icon, accessible label/decorative policy; không trả raw arbitrary SVG user nhập. |
| `flexora_asset(id, context = current)` | `UsageResultV1`; yêu cầu asset qua registry, idempotent theo request/context; không arbitrary URL. |
| `flexora_module(id)` | Immutable `ModuleStatusV1` (declared/enabled/available/booted/reason/version). Không toggle, install, activate hoặc register hook khi đọc. |
| `flexora_supports(feature, version_range = '*')` | Boolean capability available, không chỉ code tồn tại; không boot dependency. |
| `flexora_map(id, instance)` | Immutable MapContextV1: `lookup(field_id)`, `value(field_id, default)`, `schema_version()`, `revision()`. Không `save()` từ template. |

Public `flexora/register` action nhận RegistrarV1 với `component(spec)`, `variant(spec)`, `view(spec)`, `template(spec)`, `map(spec)`, `asset(spec)`, `provider(spec)`, `behavior(spec)`, `field_type(spec)`, `storage_adapter(spec)`, `module(spec)`. Spec là versioned public DTO; không lộ mutable internal registry. ID collision giữa owner khác bị reject; extend/override phải explicit theo policy. Register không enqueue/query DB/ghi defaults.

Mutations chỉ qua public **admin/application command boundary**, không qua helper đọc/render: `CommandV1.execute(command, payload, authority, expected_revision) → CommandResultV1`. Command IDs được allowlist: `map.patch`, `settings.patch`, `module.state.plan/apply`, `migration.plan/apply/rollback`, `content-pack.plan/apply/rollback`. Chỉ admin controller đã authorize mới có Command façade; không expose object này qua `flexora()` cho template. Không coi nonce là authority. REST/AJAX transport là adapter gọi cùng application command, không nhân đôi save logic.

## Values và presence — quy tắc bắt buộc

| Giá trị storage | Lookup | Read default | Patch write |
|---|---|---|---|
| Không có leaf | present=false | dùng registered default; fallback argument khi chưa có schema default | omission là no-op; explicit `unset` mới bỏ leaf |
| `null` | present=true, value=null | giữ null nếu schema nullable; không fallback | nonnullable → error, không biến thành missing |
| `false` | present=true | false | toggle false hợp lệ, number/text reject nếu sai type |
| `0` | present=true | 0 | number 0 giữ; media 0 chỉ trong legacy binding, native media dùng null nếu nullable |
| `""` | present=true | chuỗi rỗng | text/url/email cho phép nếu optional; required → validation error |
| `[]` | present=true | list/map rỗng | clear list hợp lệ; không bật legacy default lại |

`empty()`, `?:`, `isset` không được dùng để quyết định presence của new API. Dot-path lookup kiểm từng segment bằng key existence. Field ID là opaque stable string; dots trong ID không tự biến thành storage path; storage path là token array riêng trong binding. No automatic JSON stringify/serialize từ template. Saved invalid type → diagnostic + render-safe result theo field, giữ raw stored data cho recovery, không overwrite tại read.

Registration defaults là view-time fallback, **không tự persist**. Native merge chỉ missing leaf lấy default; empty array không recursively merge default rows. Giá trị source cũ đi qua adapter riêng, không sửa semantics `az_*` để giống v1. Ví dụ `az_get_social_links` vẫn trả display-escaped URL; v1 contact data trả validated raw URL, output esc_url tại view.

Thứ tự khi missing: registered schema default nếu được khai → caller fallback argument → null. Có thể dùng LookupV1 khi cần biết default chưa persist. Không dựa default value để suy ra presence.

## Context, errors và output

ContextV1 gồm request scope `frontend/admin/ajax/rest/cron/cli`, site ID, stylesheet identity, locale, object type/ID, user capability context và component instance ID. Không cho caller user input giả identity để vượt permission; object existence/access checks chạy ở command/storage boundary.

RenderResult: `html:string`, `usage:list<asset/behavior>`, `errors:list<ErrorV1>`, `variant`, `view_contract_version`. Missing component/module unavailable trả HTML rỗng + structured code (`component_unknown`, `module_unavailable`, `dependency_unavailable`); `flexora_render` ghi diagnostic redacted, không echo stacktrace/secrets. Required template thất bại → safe generic content fallback có error; không tự dùng view khác với data map khác. Registration/schema/security errors block activation of that definition; không silently render wrong field as text.

Native data raw, view escape theo context HTML/attr/URL/JS. Trusted HTML chỉ từ renderer/`editor` sanitizer với trust contract; code/secret không đi qua generic template raw echo. Public render không cấp quyền chạy PHP từ data/map.

## Public extension hooks v1

| Hook | Signature / giới hạn |
|---|---|
| `flexora/register` | RegistrarV1; registration window xác định trong architecture |
| `flexora/component/props` | props, component_id, ContextV1; output revalidated sau filter |
| `flexora/template/context` | public view data, template_id, ContextV1; không thêm authority |
| `flexora/map/before_save` | validated PatchV1, MapContext, authority; thay đổi phải validate lại; không ghi trực tiếp |
| `flexora/map/saved` | immutable change summary + revision sau commit; không secret values |
| `flexora/render/after_header` | ContextV1 event; bridge dispatch legacy đúng một lần |
| `flexora/module/state_changed` | status before/after sau operation thành công; không tự migration |

Hook payload/signature/order là API versioned. Không đăng ký wildcard filter vào internal class methods. AZ callback bridge xem legacy plan; không tự phát cả hai hook bằng vòng lặp tạo double execution.

## Version negotiation và acceptance

Child manifest khai `api: ^1.0`, `parent`, `modules`, `view_contracts`. Unsupported major → admin diagnostic và fallback content, không attempt install/download; Child không tự sửa Template header. Public additions minor khi optional; rename/remove/return type/default/fallback/DOM required breaking → major, có transition aliases và adapter tests. Legacy deprecation chỉ diagnostic trong admin/dev, không log spam/frontend warnings; không removal date suy đoán.

Acceptance tương lai: table-driven presence/type tests; module OFF no hooks/assets; output escaping; registration duplicate/lateness; Child gọi v1 không reference internals; URL/HTML/CSS/mobile/asset DOM regression với az-v1. Đây là kế hoạch kiểm thử, chưa chạy.
