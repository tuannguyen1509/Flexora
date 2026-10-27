# AI Coding Contract — Flexora

Áp dụng cho các phase implementation sau khi người dùng authorise. Tài liệu này không cho phép code runtime trong Phase02. AI phải đọc [architecture](FLEXORA-ARCHITECTURE.md), contract liên quan, ADR và source thực trước sửa. Quy tắc dựa Phase01, không mặc định AZ code tốt/xấu theo tên.

## Trước khi code

1. Xác định task scope, package profile az-bridge/native, Child identity, API/module/map/view versions và dữ liệu cần bảo vệ.
2. Search Parent source/docs/Public API, **Asset Registry**, **Component Registry**, Behavior/Provider Registry trước tạo chức năng. Ghi IDs đã tìm, reuse/extend/override choice và lý do. Không tìm thấy thì tạo proposal candidate, không tự cài vendor.
3. Đọc ownership record: site-specific/candidate/parent-ready/deprecated-local. Site-specific ở Child; reusable ưu tiên Parent qua review; không đưa logo/copy/schema site vào default Parent.
4. Xác định consumer legacy: functions/classes/load order/pluggability/hooks/priorities/handles/inline/DOM/tokens/routes/storage. Search consumer đủ rộng, không chỉ filename đại diện.
5. Thiếu runtime/platform/provider info thì ghi **Chưa xác nhận**, không đoán PASS. Không thực hiện mutation để “check health” của license verify.

## Khi thay template hoặc behavior

- Child gọi Public API v1 hoặc WordPress API đúng contract; không import internal Flexora classes, global services hoặc bypass registry bằng require implementation. Legacy Child exception chỉ trong az-v1 được ghi rõ.
- Không bundle dependency Parent đã có; không thêm Swiper/GSAP/Bootstrap cho tiện. Native engine hiện có được đánh giá trước; dependency mới có ADR benefit/size/license/a11y/security/version.
- Không copy Parent engine chỉ đổi HTML/CSS. Register view/variant/style override; giữ required data selectors/roles/version; behavior JS shared trong Parent. Custom Child chỉ site-only; candidate có record.
- Dynamic template content thay đổi → kiểm Map trong **cùng task**: add/retire/type/repeater/media/section, reference validation, schema version và migration requirement. Không yêu cầu “đã sửa map” nếu task thực không đổi dynamic contract; ghi no-map-change rationale.
- Stable field/template/item IDs không tự rename theo label/layout. Nếu cần rename/type/storage meaning → migration plan+snapshot+rollback, không direct DB replace.
- Nội dung lặp dùng repeater; rich content dài editor; plain multiline textarea; short text text; ảnh media; link URL; lựa chọn select/radio; toggle typed boolean. Không fallback nested field unsupported thành text.
- Editable content không hard-code trong template. Static UI label có translation, decorative artwork dùng package asset reference. Không hard-code domain/localhost/uploads URLs hoặc environment-specific IDs.
- Template CSS co-located và conditional via uses/registry preflight; không enqueue mọi template stylesheet toàn site. No arbitrary inline eval/script/library tags.
- New behaviors idempotent, init/update/destroy, full keyboard/focus/reduced-motion/visibility handling; không để old/new scripts init trùng node. Không gọi visual “accessible” chỉ vì có aria-label.

## Khi thay data/admin/module

- New save omission=no-op; explicit empty/false/0/list clear giữ intent; unknown stored branches preserve. Truncated request/unknown submitted field/invalid typed value → reject trước write, không xóa data bằng cast.
- Registry schema ở code, values ở storage adapter. Read/render không persist defaults, schedule cron, seed Page, dbDelta hoặc send mail.
- Module disabled không hook/assets/query/notification feature; phụ thuộc thiếu → unavailable rõ, không fatal. Đừng tắt dependency Child cần mà không plan/fallback.
- Không rename legacy DB keys/slug/callback identity/asset handle/DOM vì branding. Compatibility wrapper phải giữ semantics cũ; API native mới được quy định riêng.
- Nonce không là authorization; command cần capability+object access, schema/range validation, file/provider policy khi áp dụng. Secret/code/export trust không qua raw generic field.
- Import/migration phải preview concrete diff, snapshot verified restore, expected revisions/lock/idempotent journal, scoped rollback. Không destructive operation hoặc live content export ngoài phạm vi. Code ZIP không data backup.
- Không import orders/customers/leads/users hoặc creds/salts/license/session qua Content Pack v1. Không dùng source numeric IDs/domain làm target identity.

## Review checklist cho mỗi task tương lai

| Bằng chứng cần có | Nội dung |
|---|---|
| Reuse decision | Existing component/asset/behavior/provider IDs hoặc lý do candidate |
| Ownership | owner/status, files, no cross-site content |
| Contract diff | Public API/module/view/map/data changes, legacy impact, version bump |
| Data safety | presence cases, unknown preservation, concurrency, snapshot/rollback nếu có mutation |
| Asset plan | exact IDs/deps/context, head preflight/late loading, no duplicate provider |
| Verification | meaningful PHP/WP/Woo/browser tests theo change; ghi rõ mock vs integration, skipped/unknown |
| Release | supported version ranges, deprecation path, deployment code vs data, no hidden migration |

Các invariant bắt buộc kiểm: missing/null/false/0/empty/list, legacy empty fallback, Child early contact require/remove_action, registry20/25, pluggable functions, inline/dequeue handles, stylesheet mods/CSS, Woo ON/OFF, permalink redirects, full focusable modal/mobile loops, Content Pack replay/conflict/rollback. Chọn subset liên quan task, không chạy checklist hình thức không có evidence.

Report kết quả bằng changed behavior + why + verification + material unknowns; không báo PASS từ comment/docs cũ. Không tự chuyển phase hoặc tạo runtime skeleton khi người dùng chỉ yêu cầu architecture/audit.
