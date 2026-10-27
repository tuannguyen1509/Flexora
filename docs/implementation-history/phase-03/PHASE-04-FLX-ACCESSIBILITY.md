# Phase04 — FLX naming + native Accessibility Skip Link

Ngày: 27/10/2026. Product **Flexora**, author **Tunlamweb**, final code prefix **FLX**. Runtime API implementation vẫn **0.1.0 preview**; target Public API **1.0.0** chưa hoàn tất. Không deploy/activate hoặc cut-over website.

## Quyết định mới và ảnh hưởng architecture

User Phase04 chốt lại boundary: theme cũ chỉ là audit/behavior reference; engine mới không phụ thuộc runtime hoặc compatibility profile của nó. Đây là **thay đổi architecture có chủ đích được user duyệt**, supersede hai-profile/compatibility direction trong Phase02. Không sửa audit/architecture history để giả quyết định này có từ đầu. Nếu tương lai chuyển site cũ, cần Migration Tool riêng, snapshot/rollback và authorization riêng; chưa implement trong phase này.

Một engine native duy nhất. Xóa profile/manifest executable cũ khỏi khu implementation, không xóa baseline. `profiles/flx-native.php` chỉ metadata slug product `flexora` và entrypoint chung. Không Compatibility namespace, không legacy view/handle/data adapter, không include theme reference. Generic Hook Ledger/Child-first/remove/pluggable fixtures vẫn hữu ích và tiếp tục được test; chúng không là compatibility runtime.

## Normalization

Đã search toàn workspace trước thay đổi. Không thấy consumer production của former preview functions ngoài implementation/tests/docs; không có alias cần giữ. Kết luận chỉ trong workspace được cung cấp, không suy đoán consumer ngoài filesystem.

| Former preview symbol | Runtime hiện tại |
|---|---|
| flexora entry facade | `flx()` |
| product-prefix supports/module | `flx_supports()`, `flx_module()` |
| product-prefix use_component/component/render/asset | `flx_use_component()`, `flx_component()`, `flx_render()`, `flx_asset()` |
| Product-name PHP namespace | `FLX\Core`, `FLX\Contracts\V1`, `FLX\Assets`, `FLX\Components`, `FLX\Modules`, `FLX\Data`, `FLX\Templates`, `FLX\Fields`, `FLX\Support` |
| register/props hooks | `flx/register`, `flx/component/props` |
| Native module config hook | `flx/modules/enabled` (pure code filter, no settings writer) |

API facade vẫn read-only; không container/service getter; no fake Map/data APIs; `flx_supports('api','^1.0')` false. Namespace Contracts/V1 giữ ý nghĩa target, không claim completeness. Runtime metadata/handles/DOM mới dùng FLX. Product slug/text domain `flexora` là tên sản phẩm hợp lệ, không phải alias API.

Implementation docs examples đã normalize trong Phase04. Header lịch sử Phase03/03A được đánh dấu superseded, không pretend FLX đã là quyết định ban đầu. Phase01/02 không bị sửa. Negative test patterns và historical/reference docs được loại khỏi executable-runtime scan; **không có exception trong Accessibility hoặc runtime PHP/JS/CSS**.

## Behavior reference đã đọc

Đọc toàn implementation reference `inc/core/accessibility.php`, `css/accessibility.css`, `js/accessibility.js` trong baseline. Reference gắn body-open priority1, enqueue stylesheet/script frontend, script footer, fallback main/role/content/primary, tạo ID nếu thiếu, thêm tabindex khi focus và focus preventScroll; thiếu main thì hide link. CSS fixed offscreen/focus ring và reduced-motion tắt smooth scrolling. Không require/call các file hoặc callback này trong FLX runtime.

FLX viết native implementation theo graph mới, không yêu cầu pixel parity. Giữ các behavior có ích trên; cải thiện:

- Thứ tự chọn `main` trước role/wrapper là explicit, không bị document order của combined selector lấy wrapper bên ngoài main.
- Unique generated ID tránh collision, fragment có encode, giữ nguyên ID có sẵn.
- Init lặp/re-execute không nhân listeners; cập nhật main sau reinit, khôi phục link khi target xuất hiện.
- Không main → hidden + bỏ href; synthetic click cũng bị chặn, không broken navigation.
- Preserve existing tabindex; thêm -1 khi thiếu; focus preventScroll, fallback khi focus options không được browser hỗ trợ; giữ native anchor navigation, không thêm scroll animation riêng.
- CSS logical positioning hỗ trợ hướng văn bản, max width + box sizing cho viewport hẹp; offscreen dựa chiều cao link, rõ focus ring, hidden rule, reduced-motion. Không claim WCAG/pixel/browser PASS từ source checks.

## Production graph và ownership

| Surface | ID / file |
|---|---|
| Module | `flx.accessibility`; `src/Modules/Accessibility/Module.php` |
| Component | `flx.accessibility.skip-link`; `src/Components/Accessibility/SkipLink.php` |
| Behavior | `flx.accessibility.skip-link` |
| Provider descriptor | `flx.accessibility.native` (vanilla, no vendor) |
| Style logical ID | `flx.accessibility.skip-link.style` |
| Script logical ID | `flx.accessibility.skip-link.script` |
| Physical handles | `flx-accessibility-skip-link`, `flx-accessibility-skip-link-js` |
| Assets | `assets/accessibility/skip-link.css`, `assets/accessibility/skip-link.js` |
| DOM | `a.flx-skip-link`, initial href `#flx-main-content`; label “Bỏ qua tới nội dung”, escaped and translated using product text domain |
| JS surface | `window.FLXSkipLink.init(document)`; repeated execution idempotent, no required Child call |

Module register declares assets/behavior/provider/component/module with parent ownership. Component references style + behavior; behavior references script + native provider. Module boot consumer calls only `flx_use_component('flx.accessibility.skip-link')`; no direct asset call or render-to-discover.

Component renderer returns markup only. No hooks/enqueue/query/writes. Optional typed label prop is a renderer extension/test surface, not a new Advanced setting. Default module render uses default label, one request-level output guard. Hook callback owned by FLX Module, no reference callback identity.

## Lifecycle

1. Native Parent entry declares only lightweight lifecycle and trusted package base URI (`get_template_directory_uri()`); package expects src/assets alongside bootstrap at Parent root. This isolated workspace is not yet an installable theme package.
2. At after_setup_theme30, register native definitions before Child extensions; seal40.
3. Frontend `wp`: enabled/available module boots once, preflights component, attaches `wp_body_open:1`. No content queries or data operations.
4. Enqueue hook100: registry WP-registers/enqueues only component graph. CSS before head; script footer flag.
5. Actual template must call `wp_head`, `wp_body_open`, `wp_footer` and include valid main content. Body-open emits once, using component render cross-check against enqueued assets. Child does not declare assets/JS or duplicate skip markup.
6. Footer script resolves actual main, updates href, keyboard focus behavior. JS init runs immediately and once DOM-ready if necessary. No main → safe hidden link. No JS/global main-template engine is otherwise added.

Disabled state uses module descriptor enabled=false (pure filter for code/testing). Manager does not boot: no body-open callback, no usage, no styles/scripts, no queries or DB writes. Definitions may exist in memory; disabled does not delete data (module owns none). Admin/AJAX/REST/cron/CLI scopes do not boot this frontend module. No settings, cron, network, vendor, plugin dependency or migration reader.

Preflight cutoff from Phase03A retained: unknown asset after head fails with diagnostics, not unstyled output. No late script-only/lazy mount promise. Future Template Resolver can use the same component usage API; no Template Map implementation here.

## Verification

| Group | Result |
|---|---|
| Foundation regression | **252 PHP assertions PASS**, behavior tests retained |
| Accessibility module/component | **46 PHP assertions PASS**: enabled14, disabled8, admin8, explicit component consumer16 |
| Naming + CSS source contracts | **21 PHP assertions PASS**, zero forbidden runtime identifiers, no runtime exceptions |
| All PHP | **319 assertions**, lint **40 files PASS**, PHP7.4.33 |
| JavaScript | **23 assertions PASS** in tools V8, actual behavior source + dependency-free DOM fixture; not browser integration |
| Source integrity | **435/435 hashes/sizes match**, modified0/missing0/added0 |
| PHP8.x | Existing PHP8.4 executable policy-blocked: **SKIP**, no bypass |
| WordPress integration | **SKIP**: no core/DB; actual WP_Hook/WP_Styles/WP_Scripts/lifecycle printing not exercised |
| Browser/visual | **Not run**: focus/scroll/reduced-motion/pixel behavior needs real browser acceptance |

Before normalization, 252 fixture assertions used old profile scenarios. Those scenario labels are now native/reentry; their assertion counts and core behaviors remain (64+32 twice). Manifest-specific checks replaced with native metadata/single-entry checks because retaining a runtime compatibility manifest would violate the new decision. Generic early declarations/removal/pluggability still exercised without dependency on baseline source. No behavior assertion was deleted just to pass.

New JS tests cover no link, no main, existing/default/collision ID, selector priority/fallbacks, href encoding, tabindex preservation, preventScroll/focus fallback, DOM-ready, target replacement and repeated init/script execution. No new third-party JS library. Local Node executable was not found; harness executed through V8 tools with mocked DOM and is directly runnable with Node when available. JS evidence: [phase-04-js.txt](tests/phase-04-js.txt). PHP evidence: [last-run.txt](tests/last-run.txt).

No actual WordPress integration PASS claimed. **WORDPRESS INTEGRATION GATE = OPEN**. Next acceptance work requires a separately prepared WP/test environment and browser run, not more simulated PASS labels.

## Scope and final safety

No other seam transferred: Header/Footer/Advanced/Woo/Homepage/blog/slider/product card/Leads/Auth/SMTP/Analytics/License/Template Map remain out of scope. No database write, installer, migration or activation. No statement that the old site/theme has migrated or cut over. Source baseline protected by independent manifest check before and after. No changes to Phase01/02 history.

**PHASE 04 FLX ACCESSIBILITY COMPLETED — AZ USED AS REFERENCE ONLY**
