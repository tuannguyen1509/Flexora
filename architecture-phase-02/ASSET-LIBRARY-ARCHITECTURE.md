# Asset / Library / Behavior / Component Architecture

Nguồn: [Phase 01 assets](../audit-phase-01/ASSET-CALLSITES.txt), [CSS inventory](../audit-phase-01/CSS-INVENTORY.md), [audit §7–9](../audit-phase-01/FLEXORA-PHASE-01-AUDIT.md). Chốt **native-first**, không thêm Swiper/GSAP/Bootstrap hay vendor mới trong blueprint.

## Ba lớp và quan hệ với view

| Lớp | Contract | Ví dụ từ AZ |
|---|---|---|
| Provider/library | Implementation adapter cho một capability; version/license/dependencies; không biết brand/site HTML | Native carousel dựa shared-banner/steel scripts; WP media/editor; local fonts |
| Behavior | DOM contract + init/update/destroy; keyboard/a11y/options; provider selectable theo compatibility | carousel, disclosure, tabs, accordion, modal, gallery, sticky-header, mobile-menu |
| Component | Props/schema/view/variant/assets/behaviors; không reimplement timer/focus/gallery engine | hero-slider, product-carousel, testimonial-carousel, contact-launcher, product-card |

Provider không tự enqueue hoặc query data toàn site. Component view render HTML, usage collector yêu cầu behavior; behavior resolve provider + dependencies. Parent có nhiều capabilities nhưng request chỉ load subgraph được dùng. `hero-slider` và `product-carousel` share một provider nếu capability/version phù hợp; không duplicate vendor theo mỗi component.

## Registry metadata v1

| Field | Quy tắc |
|---|---|
| `id`, `type` | Namespaced logical ID, type style/script/font; public stable ID, physical WP handle do bridge resolve |
| `owner`, `module` | parent/child:<site>/provider:<plugin>; single owner, required module |
| `source` | trusted package-relative file hoặc registered WP handle; URL chỉ provider explicit/pinned, không user input |
| `version` | immutable build hash hoặc vendor exact version; không file hash mỗi request trong target |
| `dependencies` | IDs với type/phase; DAG, same dependency once; không global dequeue dependency của module khác |
| `context` | scope + screen/route/usage predicate; pure and bounded |
| `provider` | provider ID/version/capabilities; dependency resolution trước usage |
| `strategy` | CSS head; script defer/footer/module theo support matrix; legacy head/inline order retained khi cần |
| `legacy_handles` | profile→physical handle mapping; không đồng nghĩa enqueue nhiều handles |
| `override_policy` | extend-style / replace-view-css / provider-replace-explicit / forbidden; xem Child contract |
| `integrity`, `license`, `cache` | build/content hash, optional SRI với external pinned asset; cache identity include owner/profile/version |
| `before`, `after`, `localization` | named inline slots attach vào **physical handle**; value encode, no unsafe arbitrary JS data |

ID collisions reject. WordPress registered handle do plugin/core sở hữu được reference, không deregister/rebundle. Không suy ra “cùng basename là cùng lib”: so provider identity/version/instance requirement. Hai version bắt buộc không tương thích → conflict resolution explicit; không tự tải cả hai global namespace.

Ví dụ metadata concept (chỉ tài liệu):

```yaml
id: parent.behavior.carousel
type: script
owner: parent
module: ui.interactions
source: assets/behaviors/carousel.js
version: build-content-hash
dependencies: [parent.provider.carousel-native]
context: {scope: frontend, usage: carousel}
provider: native.carousel@1
strategy: defer
legacy_handles: {az-v1: az-shared-banner}
override_policy: provider-replace-explicit
```

Ví dụ là native ID; **không tự map cả az-homepage và steel script vào az-shared-banner**: những engine legacy có behavior khác phải có asset IDs/DOM adapters riêng tới khi parity được chứng minh. Child-owned `az-steel-storefront` vẫn do Child enqueue trong bridge.

## Conditional loading và late-render policy

1. **Core**: CSS tokens/layout/a11y base nhỏ và bootstrap runtime nếu trang có behavior; không Tailwind runtime/Font Awesome/analytics mặc định ở native profile.
2. **Feature**: enabled + dependencies available → cho phép đăng ký declarations; chỉ enqueue feature-wide asset nếu contract feature luôn cần ở scope đó.
3. **Component**: resolved template/map preflight thu component tree/assets/behaviors **trước wp_head**. Native Page templates khai `uses` trong definition; shortcode/block có discovery adapter; conditional sections theo validated data.
4. View override phải khai required usage trước render. RenderResult có usage list để cross-check. Không chỉ enqueue CSS lúc view chạy sau head: nếu feature xuất hiện không discover được, cần explicit route/template declaration hoặc critical CSS đã declared; unexpected late CSS là dev diagnostic, không silently flash hoặc load toàn site để che lỗi.
5. Late AJAX/lazy component response trả trusted `required_asset_ids`, không raw asset URLs/scripts; frontend resolve qua same registry/version/allowlist, load CSS trước mount, script once, init behavior khi node inserted. Không inline eval.
6. Cached HTML phải có deterministic asset graph/manifest cho template/data revision; cache key gồm profile/module state/schema/locale/provider versions. Không dựa server render call mỗi request nếu full-page cache phục vụ HTML.

No JS needed cho static component → không behavior runtime. Module disabled → no assets dù file tồn tại. Lazy media dùng WP responsive image APIs, explicit LCP eager/high priority; không lazy-load tất cả ảnh một cách máy móc.

## Behavior contract

Mỗi instance có unique ID, public `data-flexora-behavior` + version/options encode; view contract khai required selectors/roles. `init(root,options)` idempotent; duplicate init không tạo timer/listener; `update` preserve focus/state; `destroy` remove listeners/observers/timers và restore scroll locks/reference counts. Reduced-motion, visibility/hover/focus pause, touch/keyboard, focus return đều thuộc shared behavior, không từng Child tự viết.

| Behavior | Baseline cần phát triển từ AZ | Native acceptance |
|---|---|---|
| carousel | shared-banner + steel-storefront; homepage.js legacy adapter | pause/play, live status không spam, reduced motion, hidden tabs, dots/arrows, per-instance timers, multiple instances |
| modal/gallery/lightbox | product/gallery inline patterns | dialog semantics, full focusable trap incl inputs, escape/restore focus, nested modal lock, alt captions, responsive image |
| tabs | product/home panels | tablist/controls/labelledby, arrows/Home/End, roving tabindex, hidden state, no content data loss |
| accordion/disclosure | mobile/category/contact/legacy expand | button semantics, expanded/control IDs, keyboard native action, no double toggles |
| navigation/sticky | Parent mobile/sticky + Child mega menu | core WP menu filters retained, viewport transition, hidden sticky not focusable, search/input in focus loop |

Legacy adapters preserve selectors/old options; new behavior accessibility improvements không được âm thầm đổi legacy snapshot trong minor nếu làm thay navigation. Không duplicate old/new init cùng DOM.

## Provider decisions từ inventory

- Carousel/lightbox native code là ứng viên chuẩn hóa, **chưa chọn một engine toàn thắng** trước parity/a11y test. Provider ID contract frozen, implementation winner open gate.
- Tailwind build3.4.17 và Font Awesome6.4.0 có trong package config, runtime Tailwind CDN không pin. Native target dùng compiled CSS nếu template thực cần utility; không ship runtime compiler toàn site. Giữ old handles/CDN order trong az-v1 trước explicit asset cutover.
- Icons: Parent icon registry dùng reusable SVG reviewed hiện có, local font/icon provider tùy nhu cầu. Không sao chép SVG/logo thương hiệu SOVIBAN vào generic icon defaults.
- Font provider reuse 54 local Be Vietnam Pro WOFF2 + WP font-face/Font Library; unicode subsets/weights on demand. Không thêm Google Fonts request bắt buộc.
- jQuery, wp-media, wp-color-picker, selectWoo reference WP/Woo handles đúng scope; vanilla native behavior không khai dependency jQuery nếu không dùng. Inline product jQuery legacy được giữ gate/dependency đến khi extract.
- Chart.js chỉ legacy partial chưa thấy production caller; không đưa vào core registry auto-load. Bootstrap không có trong inventory; PHP bootstrap filename không là CSS vendor.
- Vendor mới phải có ADR benefit/size/a11y/security/license/version/alternatives + compatibility/override plan; architecture này không authorise cài dependency.

## Override policy và compatibility handles

Child được thêm skin sau component base CSS hoặc replace view CSS theo declared policy, không thay provider JS bằng CSS override. Provider replacement phải tương thích behavior contract/version/capabilities, registered manifest explicit, kiểm không tạo duplicate source/global state. Asset ID/physical old handle/inline slots mapping giữ theo [legacy plan](LEGACY-COMPATIBILITY-PLAN.md). Với mutable legacy handle, old handle là physical owner trong az-v1 để Child dequeue/add_inline còn hiệu lực.

CSS template đặt cạnh template nhưng register usage template-only; imported shared CSS reference parent logical ID. Không `@import` library Parent lần nữa. API WordPress get_template_directory (Parent) vs stylesheet_directory (Child) và child-overridable get_theme_file resolution phải theo owner, không đổi toàn bộ bằng search/replace.
