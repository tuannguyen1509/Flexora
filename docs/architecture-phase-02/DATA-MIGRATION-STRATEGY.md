# Data Adapter / Migration / Snapshot / Rollback Strategy

Normative source key families: [Phase01 Data Compatibility](../audit-phase-01/DATA-COMPATIBILITY.md) và [direct register](../audit-phase-01/DATA-KEY-REGISTER.md). Không rename key chỉ đổi branding. Phase02 chỉ specification, không DB connection/migration script.

## Data modes

1. **Legacy bound:** Flexora API → adapter → original physical AZ key/table/mod. `az_theme_options`, `_az_home_page_data`, `_az_shared_banner`, product/term/user keys giữ nguyên. Logical field ID không đồng nghĩa physical key. Mỗi family có explicit reader/default/empty/codec policy, không generic fallback truthy.
2. **Native new instance:** map mới dùng versioned envelope `_flexora_template_data` có mục đích typed values/revision/item identities; chưa tạo trong phase này. Core/module metadata mới chỉ tạo khi chức năng cần trong phase implementation đã authorise, không rename toàn bộ DB.
3. **Migrated:** target schema explicit mapping/version; old reader còn đến khi retirement có consumer evidence. Một binding chỉ một authoritative writer; không tự dual-write options/meta nếu không có conflict/reconciliation protocol.

Chốt logical storage APIs support option/post_meta/term_meta/user_meta/theme_mod/page-instance; adapter codec giữ PHP array vs JSON string vs CSV/media IDs. Source `az_home_category_sections` JSON không được xử lý như serialized array; `images` banner CSV không tự biến thành URL list. Direct SQL chỉ module-specific migration đã verify schema, không “replace prefix” trên DB.

## Migration descriptor và operation lifecycle

Descriptor: ID/version/checksum immutable, owner/module, source/target schema constraints, binding allowlist, prerequisites, scope selector, pure mapping/diff, loss classification, estimated records/bytes, locks/conflict policy, backup/restore adapter, verification, idempotency, recovery steps. No arbitrary migration code từ Content Pack.

```text
inventory/read-only → validate dependencies → build plan/diff
  → snapshot touched scopes + verify restorable
  → explicit apply command (expected versions/checksum/target revisions)
  → acquire locks / recheck no drift
  → journal + bounded batches + verify each batch
  → verify render/data references → finalize ledger
failure → stop → compensating restore plan → verify → mark recovered/conflicted
```

Plan là artifact cụ thể gồm exact keys/objects/rows/media refs/old+new hashes/presence, create/update/retire actions, exclusions, conflicts, backup reference. Stale plan/unknown schema/unsupported codec/permission/missing dependency → reject, không partial guess. Version ledger không dựa only Parent version; operation ID retry không tạo duplicate Pages/attachments/list item IDs. Dry run không upload files, schedule cron, send mail hay persist defaults.

Multiple WP stores và external APIs không có global transaction guarantee. Nếu DB backend hỗ trợ transaction cho touched own tables có thể dùng trong adapter; vẫn cần journal và compensating rollback cho WP side effects/media. Không gửi mail/HTTP notification trong data copy; defer/quarantine side effects và replay chỉ command riêng nếu cần. Legacy writers không tham gia lock → maintenance/quiesce **touched scope** hoặc reject import; không tuyên bố optimistic revision giải quyết mọi race.

## Snapshot specification

Snapshot ID, timestamp UTC/site timezone, operation/scope, site identity, code/API/module/schema versions, per-record present flag/raw encoding/value hash/reference, restore adapter/version, checksums, protected location/access/retention. Snapshot chứa nhạy cảm phải private + restricted + encrypted transport/storage backend theo deployment; không đặt public uploads ZIP. Portability pack không thay snapshot local.

| Scope | Phải ghi nhận trước write |
|---|---|
| Options | Exact keys/container leaves và unknown siblings, value/encoding/autoload hiện tại; absent khác explicit null/empty |
| Post/term/user meta | object identity, key, multiple rows/order nếu có, missing/values, mapping source/destination IDs; core post fields được chọn nếu create/update Page |
| Custom tables | Chỉ named table/module rows bị tác động, schema/index/version, stable row keys, count/checksum và restore capability; no whole DB import |
| Theme mods | source/destination stylesheet-specific option + nav_menu_locations/custom_logo/unknown mods; one-time old flag state |
| Custom CSS | post identity/content/stylesheet binding/current link; không copy post ID sang theme khác |
| Widgets/menus | selected sidebars_widgets/widget_* entries, term/menu item assignments/refs khi operation chạm; không reset global widgets vì đổi Child |
| Media | before refs+target resolution map, attachment metadata/hash/file inventory của tài nguyên sẽ tạo/sửa; binary backup nếu file hiện hữu sẽ bị thay |
| Cron/module state | Exact owned job name+args+schedule nếu migration thay; không snapshot/restore nguyên cron làm mất job plugin phát sinh sau |

Snapshot verification phải chứng minh readable/integrity và restore adapter hỗ trợ trước commit. Code ZIP chỉ backup code, không data. Nếu operation chạm custom table mà restore backend chưa hỗ trợ → operation blocked; không export “backup” giả chỉ chứa theme ZIP.

## Rollback contract

Rollback là plan trên touched scope, không restore toàn DB. So current value với post-import hash/revision; chỉ revert nếu chưa có edit mới. Concurrent/live edits → conflict, không ghi đè; trình bày restore-selective/manual resolution. Restore absent bằng delete đúng leaf/record đã tạo, restore empty/null chính xác, preserve unrelated siblings. Với physical option row, merge conditional affected leaves; nếu shape/array order changed và không chắc branch disjoint → conflict.

Created Page chỉ delete/trash khi operation owns và chưa có external references/live edits; nếu không thì detach/flag manual. New media rollback mặc định khôi phục references, **không tự xóa attachment/file** vì có thể được nội dung mới dùng; garbage collection riêng có reference analysis và review. Không làm rollback xóa live lead/order/customer sau migration. Restore CSS post/menu/mod linkage và invalidate caches/rewrite **owned** sau verify; resume jobs theo owner policy.

## Local → Production identity và data ownership

Child Code Package deploy độc lập data. Content Pack mang explicit values/references, source IDs chỉ là origin hints. Target binding dùng content logical identity UUID + verified target match, không cùng numeric ID. Existing site chưa có UUID → preview mapping URL slug/type+human selection tới record, persist deployment identity index ở operation metadata sau apply; không tự overwrite Page có cùng slug. Dữ liệu người dùng chỉnh production được giữ bằng default fill-missing/conflict detection.

Không hard-code source domain/localhost/uploads. Media reference hash/logical ID/package asset ID resolve target attachment/package URI; local internal links typed page/term refs được remap; external URL literal chỉ khi field cho. Rich editor HTML cần structured link/media rewrite (HTML processor/core parser), unknown/local-source URL unresolved báo conflict; không regex search/replace toàn serialized data.

## Những gì chưa được phép migrate

- Live orders/customers/leads/users, WordPress auth/session/user linking, Google subject IDs, avatar ownership: ngoài scope Content Pack v1. User_meta adapter support **không** trao quyền import users.
- `az_contact_leads`↔`az9s_leads`, `iaca_license_usage`, license status/domain/counters/expiry aliases: cần module-specific plan/schema/retention/client protocol và restore test.
- SMTP/OAuth/CAPTCHA secrets, auth salts, passwords, license keys, trust signatures: không portable pack. Configure target qua credential workflow; imported script không auto-trust.
- Current Child one-time mods flag và Custom CSS linkage: không rerun copy cờ chung, cần inventory source/destination trước explicit slug migration.
- Cron events/rewrite rules/options/transients/cookies/rate counters: không generic config import. Routing cutover có explicit plan/flush, auth transient không migrate.

## Runtime verification gates trước code cutover

WP/PHP/Woo matrix, DB engine/locking/serialization/existence semantics; export/import Page revisions với empty/unknown branches; core Font Library/media IDs; modified-at/checksum conflict; old Child save passes10/30/40; old mods/custom CSS thực vs template child mẫu; cron owner/callback dependency; AZ9S provider/plugin coexistence; license clients và absent helper; contact file validators; staging restore drill. Chưa có môi trường nên chưa coi gate nào PASS.
