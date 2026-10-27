# Template Map / Field Engine / Storage Contract v1

Thiết kế, chưa code. Foundation: Advanced merge + primitive sanitizer/media, shared banner data và Child page-fields/partners/SOVIBAN. Không clone nguyên AZ_Advanced: generic typed field registry, explicit storage và schema lifecycle là boundary mới.

## Template–Map contract

Một editable Template Page definition có stable `template_id`, `version`, `view_contract`, `view`, `map_id`, `uses`, `style_assets`, `supported_contexts`. Map definition có `map_id`, `schema_version`, `owner`, `template_ids`, `storage_binding`, `fields`, `sections`, `retired_fields`, `migration_requirements`. Template/map references phải resolve cùng release; registry reject orphan template/map hoặc incompatible versions. Generic editor-only page cũng có map khai source `post_content`, không ép copy content vào meta.

Field ID ổn định như `about.hero.title`, `about.hero.description`, `about.hero.image`; ID là opaque identity, độc lập title/label/HTML location/storage path. Rename heading CSS class không rename field. Moving section chỉ đổi grouping/order. Xóa field khỏi UI → retired, giữ raw data và compatibility reader; không tự purge. Thay type/ID/meaning/storage cần versioned migration; không chỉ đổi map file rồi cast giá trị cũ.

### Ví dụ map concept

```yaml
map_id: site.about
schema_version: 1.0.0
owner: child:site-a
template_ids: [site.about]
storage_binding: {adapter: page-instance, scope: current-page, key: _flexora_template_data}
fields:
  about.hero.title: {type: text, default: '', required: false}
  about.hero.description: {type: editor, default: '', required: false}
  about.hero.image: {type: media, nullable: true, allowed: [image]}
  about.services:
    type: repeater
    default: []
    min_items: 0
    max_items: 24
    fields:
      service.title: {type: text}
      service.body: {type: editor}
      service.image: {type: media, nullable: true, allowed: [image]}
      service.url: {type: url}
      service.layout: {type: select, options: [default, compact]}
      service.visible: {type: toggle, default: true}
```

`_flexora_template_data` là key **đề xuất cho native map instances mới**, tạo vì envelope/version/typed identities, không đổi branding key cũ. Không tạo key trong Phase02. Existing AZ bindings tiếp tục `_az_home_page_data`, `_az_shared_banner`, `az_theme_options` qua adapter; không tự copy sang envelope native. Exact storage path được khai `binding.path` array riêng, không lấy dot field ID split để suy đoán key legacy.

Native page-instance envelope concept: `{format_version,map_id,schema_version,revision,values,retired_values}`. Values giữ stable field IDs; không gồm HTML/PHP/schema executable. Instance identity `(site,object_type,object_id,map_id,slot)`; một Page có nhiều named slot không ghi đè nhau. Schema in code; editable values in DB. Không đặt live data defaults trong Child package để overwrite update.

## Field Type Registry

Type descriptor: stable ID/version, accepted shapes, nullable policy, storage codecs, admin control, view formatting, capabilities, max length/depth/count, sanitize/normalize/validate/default/serialize, supports_nested/editor/lazy_render, portable export codec. Unknown type → definition error, **không fallback text**.

Pipeline command: decode transport/presence → reject shape/size → normalize safe representation → sanitize allowed syntax → validate semantic constraints → serialize adapter codec → commit. `validate` trả structured errors; sanitizer không được đổi invalid user selection thành default rồi báo success. Default chỉ read missing/new explicit reset. Deserialize storage không gọi PHP unserialize trên Content Pack; WP-managed serialization dùng WP APIs chỉ trong storage adapters.

| Primitive | Normalize/sanitize/validate | Render/serialize |
|---|---|---|
| text | string, max length, sanitize_text_field; reject array/bool | plain input; raw string storage, escape tại view |
| textarea | string multiline, sanitize_textarea_field | textarea, giữ line breaks |
| editor | rich string, allowed HTML profile bằng WP KSES; required/length; reject script | generic **WordPress editor API** (`wp_editor`/approved core editor adapter), media controls theo capability; save canonical HTML, không arbitrary code |
| number | finite int/decimal, min/max/step, explicit locale parser; reject bool/NaN/infinity | native numeric UI; number codec giữ 0, không cast ID float |
| url/email | optional empty giữ; scheme/domain policy / valid email; invalid không silently erase | input type đúng; validated raw string, no display-escaped storage |
| select/radio | exact value enum and type, reject unknown; same allowlist cho radio | options control, deterministic key codec |
| toggle | typed true/false; transport 0/1 chỉ adapter explicit | accessible switch + presence marker, bool storage |
| color | configured hex/alpha policy; no arbitrary CSS token string | core picker/control; canonical color string |
| media | native nullable attachment reference, MIME/object existence/access, ID origin remap import | WP media API, responsive image utility; local ID + portable reference resolver |
| gallery | ordered media refs, unique item IDs, bounds and MIME | multi-select+keyboard reorder; not CSV native |
| repeater | recursive typed child schema, depth/count/bytes limits, item IDs/order validation | each subfield control thật, editor/media/select/toggle hoạt động trong rows; no text fallback |
| code | content-type allowlist css/html/script, privilege checks, source signing profile | code editor/plain textarea adapter; HMAC trust local, no imported trusted flag |
| secret | write-only patch set/clear, bounded string; no read in views/export | masked UI value absent; blank unchanged, clear explicit command; credential adapter protected |

`secret` là ngoại lệ documented của form blank handling, không áp secret rule sang text. Encryption at rest backend chưa chốt; không hứa secret encrypted khi chưa có key lifecycle. Non-secret nullable type lưu null thật; legacy media0 được adapter giữ cho old caller.

## Editor và repeater identity

- Generic editor dùng unique DOM editor_id dựa map/instance/item/field, không nhét stable field ID có ký tự không hợp lệ trực tiếp. Row reorder không thay data identity. Init/destroy WordPress editor instance khi add/remove row, sync dirty content trước reorder/save, không clone editor iframe/DOM initialized.
- Repeater native: `{order:[uuidA,uuidB],items:{uuidA:{values:{...}},uuidB:{values:{...}}}}`. Server cấp/validate UUID, temp client IDs resolve một lần tại save; duplication tạo ID mới. Không dùng numeric index, title hay image URL làm identity.
- Partial item update patch theo item ID. List replace phải explicit `replace_collection`; omission no-op; clear list giữ empty `{order:[],items:{}}`. Delete item explicit và kiểm expected collection revision; unknown fields của item còn giữ cho retired/forward compatibility. Order phải unique, refer mọi active items đúng một lần; dangling IDs reject.
- Legacy numeric lists giữ shape cũ trong az-v1. Nếu promote sang native item IDs, preview index→UUID map và snapshot list; persist mapping một lần trong migration ledger/sidecar namespace để không regenerate ID mỗi read. Không dựa index để join sau user reorder.
- Keyboard add/remove/move-up/down/focus return/error association bắt buộc; pointer drag là enhancement.

## Storage interface (specification)

Public extension contract StorageAdapterV1:

```text
supports(binding, context) -> capability declaration
read(binding, context) -> LookupV1(present, value, revision, provenance)
plan_patch(binding, PatchV1, expected_revision, authority) -> validated write plan
commit(plan, operation_id, lock_token) -> CommitResultV1
snapshot(scope) / restore(plan) -> coordinator operations
export_reference(value) / resolve_reference(ref, target) -> portable codec
```

`plan_patch` không ghi; commit chỉ coordinator authorized gọi. Adapters option/post_meta/term_meta/user_meta/theme_mod/page-instance; each declares missing detection, multiple-meta handling, scope key, serialization, locking/CAS capability, portable fields. Không expose general DB writer cho template. `theme_mod` binding yêu cầu stylesheet explicit; user_meta yêu cầu edit_user/self policy; post/term capability check theo object. Template/page-instance thường ưu tiên post_meta, generic post_content uses core post APIs có revision policy.

WP meta API trả `''` không đủ phân biệt missing; adapter phải existence-check. Option container update phải patch leaf rồi preserve unrelated branches; no array_replace_recursive cho numeric collections. Shared physical row nhiều adapters phải chung lock và revision domain; không false atomicity giữa options/meta/custom tables.

## Save semantics normative

| Payload/Schema event | Hành vi |
|---|---|
| Field không gửi / hidden panel | no-op, giữ persisted value |
| present field `""` | set empty string nếu schema cho; không delete |
| present number `0` / toggle false | set đúng type, không fallback |
| present nullable null | set null; nonnullable reject |
| present list `[]` | clear collection; empty list là persisted intent |
| field removed from schema | retire metadata, giữ storage; read qua compatibility binding nếu cần |
| explicit `unset` | capability + schema cho phép; bỏ leaf để read default lại; khác clear |
| reset | explicit chọn registered default hoặc unset; preview affected fields, không legacy fallback guessed |
| unknown field trong request | reject unknown submitted paths; không ghi người dùng tùy ý |
| unknown branch đã lưu | preserve byte/shape hợp lệ qua adapter, không đưa vào public form data nếu unauthorized |

Patch envelope gồm operation ID, map/schema version, instance, expected revision, `present_fields`, `set`, `unset`, collection operations, completeness marker per submitted group. Không đoán checkbox missing là false; renderer gửi explicit bool. Truncated form/group → reject toàn group/command trước write. Validation failures trả field errors, không write phần trước rồi bỏ phần sau. Autosave/revision/preview có write mode riêng, không tự chạy publish migration/notifications.

`present_fields` phải khớp chính xác submitted set/unset/collection paths theo command mode; duplicate/conflicting operations hoặc parent+child path overlap reject. Không cho marker tự mở quyền ghi field ngoài schema/authority. Missing presence marker với nonempty payload là invalid request, không clear cả form.

Optimistic concurrency: stale revision → conflict, preview diff và reload/merge explicit. Legacy writer không hỗ trợ cùng lock → migration/import cần maintenance/quiesce owner hoặc reject; không hứa serialize mọi writer bên ngoài. Update schema code không write values, no implicit defaults persistence. Giữ điểm tốt Advanced partial merge nhưng không giữ lỗi typed repeater/radio.

## Template co-location và AI pairing

Child convention `templates/pages/about/{template.php,map.php,style.css}`; map.php chỉ trusted code declarative schema, không payload import executable. WP-selectable thin root `page-about.php` delegates logical template ID khi cần giữ filename core. Template definition declares style asset và uses components trước head; CSS chỉ trang dùng template. Shared JS/provider nằm Parent; Child không copy engine vào about/script.js để đổi markup.

Template PR/task sửa dynamic sections phải cùng update/check map: thêm field, retire field, reorder groups, media type, repeater schema, default semantics, migration requirement và fixture. Template field references unresolved reject CI/registration. Rich content editor, repeated user data repeater, text ngắn text, images media, link url. Static decorative label/icon có thể nằm code nếu không phải nội dung người dùng cần chỉnh; định nghĩa rõ để tránh hard-code content site.
