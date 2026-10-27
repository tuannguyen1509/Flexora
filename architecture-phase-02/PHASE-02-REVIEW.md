# Phase 02 — Architecture & Compatibility Blueprint: review report

Ngày hoàn tất: 27/10/2026. Bộ tài liệu baseline1.0.0 frozen for review; **chưa runtime implementation**. Không tạo `flexora/`, sửa prefix/key/file source, di chuyển module, cài vendor, chạy migration/DB/endpoint/mail/commit.

## Kiến trúc đề xuất

Flexora Parent = Website Framework modular; Child = một website. Public API v1 và az-v1 đi vào cùng engine. Engine có lifecycle/data/fields/template-map/assets/components; providers dưới behaviors dưới components. Feature Application có thể ở Parent để giảm plugin nhưng opt-in, dependencies/permissions/side effects rõ. WP vẫn cung cấp lifecycle/theme hierarchy/editor/media/handles, không xây framework thay WordPress.

## Quyết định đã chốt trong blueprint

| Decision | Kết quả |
|---|---|
| Ownership | Core/Reusables/Application/Legacy/Site-specific; SOVIBAN skin/schema/images ở Child |
| Public API | Façade v1, immutable results, Registrar cho definitions, authorized commands cho mutations; Child không gọi internals |
| Compatibility | az-v1 giữ signature+semantics+load order+callback priority+DOM+asset+data+route; không “alias” chung chung |
| Packaging | Bridge slug az-theme hỗ trợ Child Template cũ; native slug flexora cho cài mới/migrated, không hai engines |
| Data | Original AZ keys retained qua binding; read không ghi defaults; native instance envelope chỉ cho map mới khi cần |
| Template Map | Stable field IDs, schema/view pairing cùng task; retire giữ data; typed generic WP editor và nested repeater có stable item IDs |
| Save | Omission no-op; empty/null/false/0/[] giữ intent theo type; unknown saved branches preserve; revision/conflict handling |
| Assets | Logical registry trên WP handles, usage preflight, native-first, no new vendor; old mutable handle physical owner trong bridge |
| Override/promotion | View/variant/style theo contract, behavior reuse; candidate→parent-ready qua review, no automatic copy |
| Modules | Disabled không feature hooks/assets/queries, data retained; plugin provider election; explicit activation/migration side effects |
| Content deployment | Code Package riêng; selective JSON Content Pack, target ID/media remap, fill-missing, preview/snapshot/rollback |
| Versions | Independent Parent/Child/Public API/Module/Map schema/Migration ledger/Pack format; breaking không minor silently |

Mười ADR ghi context/decision/reason/compatibility/tradeoffs. Chốt kiến trúc không có nghĩa người dùng đã phê duyệt triển khai; Phase tiếp theo cần yêu cầu riêng.

## Mapping foundation chính

| AZ foundation | Flexora target |
|---|---|
| bootstrap + helper wrappers | lifecycle coordinator + legacy declarations/path bridges |
| options registry/sanitizer/Advanced merge | Data bindings + Field Type Registry + reusable admin, giữ old DOM profile |
| dynamic CSS/local fonts/container | token/font assets và az-v1 variables |
| header/footer/mobile/sticky/contact | shell/navigation/components, native behavior/legacy DOM adapters |
| shared product card/video/image | commerce product module + public component/Support utilities |
| banner/steel/home carousel, inline tabs/gallery | native interaction providers/behaviors, parity test trước hợp nhất |
| Child page-fields/partners/SOVIBAN save | Template Map/instance adapter, Child-owned schema/skin |
| neutral/About | generic template fallback, stable root filenames |
| Leads/SMTP/Auth/Analytics/License/price policy | separate modules/provider/state/permission/lifecycle boundaries |
| Flatsome importer preview và child installer | migration/packaging coordinator, snapshot/rollback gates |

## Những decision còn mở và default an toàn

| Open gate | Bằng chứng cần | Trước khi có evidence |
|---|---|---|
| Supported PHP/WP/Woo/browser minimum | test actual core/Walker/editor/font/checkout/hooks | Không lấy metadata WP5.8/PHP7.4 AZ làm release proof |
| Carousel provider implementation winner | multiple-instance/a11y/DOM/performance parity | Giữ native candidates/legacy adapters; không thêm vendor |
| Secret encryption/key lifecycle | hosting secrets/backend/rotation/backup restore policy | Không hứa encryption; no credential export/import |
| Snapshot backend và locks | DB engine/cross-store/host file permissions/restore drill | Không allow operation mà restore chưa supported |
| License API hardening protocol | real Content AI clients/provider/credential/usage expectations | Không đổi endpoint/counter semantics hoặc migrate data |
| Bridge support horizon | external consumer inventory + replacement + release policy | Không tự sunset/delete alias/paths |
| Native performance budgets | benchmark representative site/pages/data | No fabricated byte/time PASS; preserve conditional-load constraints |

## Dữ liệu chưa được phép migration

Live orders/customers/leads/users; auth subjects/roles/sessions/password/salts; license keys/domain/status/usage/token/expiry aliases và provider table; SMTP/OAuth/CAPTCHA secrets; script trust signatures; merge hai leads tables; actual theme mods/Custom CSS linkage/one-time copy flag; cron/rewrite/transient raw state. Chỉ specification adapter hỗ trợ scope không phải authorization migrate scope đó.

Map values/options/page config/media refs có thể được thiết kế Content Pack nhưng **chưa có operation thực**. Tương lai vẫn cần allowlist + explicit preview + verified snapshot + target version/revision + rollback plan. Không whole DB import/search-replace.

## Rủi ro còn lại và runtime verification trước cutover

1. Child early require contact → remove_action → Parent bootstrap không reattach; hai pluggable functions Child-first; registry20/25 vs seal40. Chạy WP integration fixtures thật.
2. Paths/markers/root taxonomy proxies khác native resolver; home-reference.css mismatch hiện tại, inline styles/old handles/dequeue ordering và style-disable dependencies. So actual rendered head/footer và asset graph.
3. Legacy list-empty fallback vs native explicit empty; saved unknown branches và multi-writer priorities10/30/40; raw/escaped social URLs. Round-trip DB không chỉ mock array.
4. Actual Child migration khác mẫu về custom_css_post_id; validate logo/menu/CSS trước/sau stylesheet/Parent slug switch với snapshot restore.
5. Woo ON/OFF, templates/hooks/version, variations/getter pricing/related/recent, checkout/account, product URL/category collisions/feed/pagination/query redirect. Không tự module-off gây missing function.
6. Existing S01–S03 license mutation/missing helper/guest upload phải có module-specific security protocol/rate/file/authorization tests trước enable/cutover; không silently preserve risk như guarantee mới.
7. AJAX/REST/cron/admin dispatch timing và provider plugin collisions; owned jobs cancellation/re-enable không duplicate, data không mất khi disabled.
8. Browser a11y modal/mobile focus trap gồm inputs; tabs/repeater keyboard/editor lifecycle; reduced motion/visibility/multiple carousel instances.
9. Content Pack identity/media remap, rich HTML links/source-local URLs, conflict/replay/truncated form, snapshot readable, partial failure và concurrent edit rollback drill. Xác minh không chạm live excluded tables.

Không có WordPress/DB/runtime đi kèm workspace, nên các mục này là gates chưa chạy, không báo PASS. Có thể code prototype cô lập trong phase sau được authorise; **không được thay runtime AZ/activate migration trước các gate liên quan**.

## Traceability 25 yêu cầu

| Yêu cầu | Tài liệu xử lý |
|---|---|
| 1 Parent/Child | architecture, child contract, ADR001 |
| 2 Core engine/boundaries | architecture lifecycle/namespaces |
| 3 Public API/value semantics | public-api contract, ADR002 |
| 4 Legacy layers | legacy plan, ADR003 |
| 5 Không rename DB | migration strategy, ADR004 |
| 6 Library→behavior→component | asset/library architecture |
| 7 Registry/conditional loading | asset metadata/preflight/late-render policy |
| 8 Override/fallback | Child contract, ADR007 |
| 9 Promotion | Child status/workflow records |
| 10–11 Template Map/pairing/IDs | template-map architecture, ADR005 |
| 12 Field Engine/editor/repeater | typed registry + editor/item lifecycle |
| 13 Storage adapters | template-map storage interface + migration strategy |
| 14 Save semantics | normative patch/presence table |
| 15 Template HTML/CSS co-location | template-map + Child structure + asset usage |
| 16 Starter spec | child contract, no starter runtime created |
| 17–18 Module system/ownership | module system table/lifecycle, ADR008 |
| 19 Local→Production | content pack spec, ADR009 |
| 20 Snapshot/rollback | data migration strategy |
| 21 Versions | architecture version table, ADR010 |
| 22 AI coding | AI coding rules |
| 23 ADR | ten records + index |
| 24 Output | danh sách bên dưới |
| 25 Review/stop | báo cáo này, integrity, final handoff |

## Danh sách tài liệu tạo

Trong `architecture-phase-02/`:

- `FLEXORA-ARCHITECTURE.md`
- `PUBLIC-API-CONTRACT.md`
- `LEGACY-COMPATIBILITY-PLAN.md`
- `MODULE-SYSTEM.md`
- `ASSET-LIBRARY-ARCHITECTURE.md`
- `TEMPLATE-MAP-ARCHITECTURE.md`
- `CHILD-THEME-CONTRACT.md`
- `DATA-MIGRATION-STRATEGY.md`
- `CONTENT-PACK-SPEC.md`
- `AI-CODING-RULES.md`
- `PHASE-02-REVIEW.md`
- `SOURCE-INTEGRITY.md`
- `ADR/README.md`
- `ADR/001-parent-child-ownership.md`
- `ADR/002-public-api.md`
- `ADR/003-legacy-compatibility.md`
- `ADR/004-data-storage.md`
- `ADR/005-template-map.md`
- `ADR/006-asset-registry.md`
- `ADR/007-child-overrides.md`
- `ADR/008-module-system.md`
- `ADR/009-content-pack.md`
- `ADR/010-versioning.md`

Integrity: [SOURCE-INTEGRITY.md](SOURCE-INTEGRITY.md). Kết thúc tại blueprint để người dùng review; chưa có source/runtime starter/migration/vendor mới và không tự sang phase tiếp theo.

**PHASE 02 ARCHITECTURE COMPLETED — NO RUNTIME SOURCE CHANGES MADE**
