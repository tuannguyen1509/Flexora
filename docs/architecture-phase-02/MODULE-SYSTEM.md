# Parent Module System

Module là boundary trong Parent, không tự là plugin. Core chỉ chứa lifecycle, public declarations, context, diagnostics và registration/usage/storage interfaces nhẹ. Không analytics views, mail hooks, cron scheduling, HTTP, seeding hay DB schema upgrade từ core boot.

## Descriptor v1

| Metadata bắt buộc | Semantics |
|---|---|
| `id`, `version`, `owner`, `category` | Stable namespaced ID; Core / Reusable / Application / Legacy / Site-specific |
| `engine`, `api`, `requires`, `optional`, `conflicts` | SemVer constraints và capability/provider requirements; graph không cycle |
| `default_state`, `legacy_state_binding` | Native default; legacy effective state phải được inventory, không lấy native default ghi vào AZ |
| `scopes`, `boot_conditions`, `hook_phase` | frontend/admin/AJAX/REST/cron/CLI; pure condition, không ghi DB hoặc query side effect |
| `assets`, `components`, `behaviors`, `public_capabilities` | IDs không chứa implementation class cho Child |
| `permissions`, `data_bindings`, `routes`, `jobs` | Object-level capabilities, table ownership, endpoint protocol và schedule policy |
| `legacy_adapter`, `activation_plan`, `deactivation_plan` | Side effects chỉ explicit command; no hidden install on first read |

Module implementation `register → boot(scope) → stop(scope)` internal; declarations được đọc không include file tự gắn hook. Manager ghi hook ledger và assets owner để teardown xác định. Không cố unload PHP function; shim/API declarations có thể còn, return unavailable an toàn.

States: `declared`, `disabled`, `enabled_pending_dependencies`, `available`, `booted(scope)`, `blocked(reason)`, `externally_provided`. Enabled là lựa chọn; available yêu cầu dependency phù hợp; booted là scope đã đăng ký side-effect hooks. `flexora_supports` chỉ true khi capability usable. Không tự enable dependency thay người dùng; state-change plan chỉ ra ảnh hưởng dependent, apply cả kế hoạch được chọn hoặc reject.

## Scheduling và khi tắt

- Tại Parent load: descriptor registry/public façade/legacy declarations, không feature hook mới nếu disabled. `init` cần đăng ký CPT/taxonomy chỉ cho module enabled có dependencies. Public endpoint router metadata phải có trước admin_post/wp_ajax/rest_api_init dispatch; callback implementation load on dispatch sau authorize. Không dùng is_front_page lúc load để boot module.
- Frontend visual hooks boot có điều kiện tại `wp`, enqueue tại wp_enqueue_scripts; admin screen UI boot ở current_screen/admin_enqueue_scripts, save endpoints theo ownership không phụ thuộc screen render.
- Disabled module không frontend/admin feature hooks, assets, queries, mail interception hoặc schedule mới. Generic shared dispatcher có thể tồn tại trong core; route của disabled module không được register. Legacy callable shim có thể trả empty/WP_Error theo old signature; không lazy-enable cả module.
- Disable live là operation riêng: stop hook ledger, cancel **own job names+args**, flush rewrites nếu module owns changed rules, retain data/options/tables, không uninstall. Không cleanup các cron event/plugin tables chỉ vì cùng prefix. Pending jobs use a minimal ownership-aware dispatcher khi cần báo disabled; không boot module để chạy job cũ.
- Nếu Child declared `requires` feature thì disable plan blocked trừ khi chọn fallback/Child update; không để white screen bằng cách tắt helper bất ngờ.
- Trước khi một legacy seam đã được chuyển sang manager, descriptor ghi `disable_supported=false/legacy_boot_owned`: không hiển thị công tắc giả “disabled” trong khi old include vẫn gắn hooks. Disable command bị reject với prerequisite cutover; chỉ nhận disable sau khi ledger/teardown và data retention đã được kiểm chứng. Không thay đổi trạng thái AZ hiện tại để giả lập module system.
- Plugin coexistence: provider election trước boot. AZ9S Leads active → externally_provided cho lead runtime, Parent adapter dùng API đã kiểm version; không gắn hai phpmailer handlers. Conflict/unsupported provider → blocked + diagnostic, không âm thầm ghi đè.
- Activation/schema changes qua migration coordinator có snapshot + idempotent ledger. Không dbDelta mỗi admin_init, không tự tạo Woo Pages hoặc schedule license cron khi chỉ include. Đây là target behavior mới, phải cutover khỏi old routines có explicit plan; az-v1 trước cutover vẫn giữ behavior cũ.

## Ownership hiện tại → target

| Nhóm AZ / file foundation | Target ID / loại | Native boot | Compatibility |
|---|---|---|---|
| Theme UI/tokens/options helpers | `core` + `ui.tokens` Reusable | minimal base tokens cần site; feature tokens theo usage | AZ variables/styles bridge |
| Header/Footer | `ui.shell` Reusable | enabled cho theme shell, components theo usage | helpers/DOM/hooks/header defaults |
| Menu/Mega/mobile/sticky | `ui.navigation` Reusable | enabled; mobile/sticky assets only when rendered/enabled | old walker/handles/menu classes |
| Carousel/modal/tabs/accordion/gallery | `ui.interactions` Reusable | definitions available, behaviors/assets chỉ khi component dùng | DOM adapters riêng shared-banner/steel/product/gallery |
| Blog | `content.blog` Reusable | enabled nếu site supports blog; query gate post routes | old layout/meta/comments behavior |
| WooCommerce | `commerce.woocommerce` Reusable | opt-in + Woo dependency; không bootstrap khi thiếu Woo | public helper shim safe, template/proxy profile |
| Product card/video/fields | `commerce.product` Reusable | requires Woo for product APIs; image/video parsers có thể Support primitive | old post meta/card API |
| Homepage section framework | `content.homepage` Reusable + `legacy.az-homepage` Legacy Adapter | native use Template Map, no sample seeding | Parent section IDs/options và Child marker paths |
| SOVIBAN home/footer/steel layouts | `site.*` Site-specific Child | Child declares | schema defaults/media/branding giữ Child |
| Leads/forms/contact submit | `app.leads` Application | opt-in; endpoints only enabled; external AZ9S provider election | two table bindings, old actions/shortcode |
| SMTP | `app.smtp` Application | opt-in, intercept wp_mail only after provider resolution | AZ9S priority vs legacy settings preserved |
| Auth/Google | `app.google-auth` Application | opt-in + configured credentials, callback known | Google subject/state/callback/allow-login |
| Analytics | `app.analytics` Application | native off; bounded counters/storage design required | AZ counters/shortcode; no invented online5 new API |
| License/Content AI + pricing CPT | `app.content-ai` Application + az-license protocol adapter | native off; client/provider/security gate | CPT/meta/routes/download/job/option iaca |
| Category price policy | `commerce.category-price-policy` Application | native off, requires Woo | term az_price_rate/getter/variation hash preserve |
| Product pretty URLs Child | `site.product-routing` Site-specific → candidate | not default Parent | no duplicate owner/flush/redirect |
| Performance switches | `tools.performance` Reusable | opt-in switches, scope relevant | old disable handles, minify/filter hooks |
| Security switches/headers | `tools.security` Reusable | minimal documented headers if configured; restrictive switches opt-in | REST exception/header filter; no claim replacing WAF |
| Custom code | `tools.custom-code` Reusable privileged module | opt-in snippets explicitly configured | old sources/HMAC/unfiltered_html/attachment order |
| Media tools/import | `tools.media` Reusable | admin tools; content import opt-in only | MIME/size/local-offload guards |
| User options/avatar | `tools.users` Reusable | switches opt-in; profile/admin scopes | subject/avatar/meta/cap restrictions |
| Flatsome content | `legacy.flatsome` Legacy Adapter | native off; legacy content sites explicit | shortcodes/sidebar assignments |
| Admin fields/UI | `admin.fields` Reusable infrastructure | admin only for registered maps/settings | old field DOM/profile and Child field extensions |

UI modules share core support a11y/responsive/image/icons/AJAX permission helpers. AJAX helper cung cấp nonce verification khi thích hợp, capability+object access, typed schema, bounded paging/rate policy và structured result. Không tạo public mutation handler chỉ vì registry có ID.

## Disabled data và security gates

Data ownership không biến mất khi module disabled. Read-only export/map adapter có thể mở bằng maintenance command authorized, không boot notification/query hooks. No DROP/TRUNCATE/unset options vì disable. Chưa migrate live users/customers/orders/leads theo Content Pack.

S01–S03 Phase 01 là release blockers khi đưa module tương ứng sang engine mới: xác thực protocol Content AI, missing helper provider, guest upload field/MIME/total-size/count/rate limits và DB insert error handling. Không bật native application modules bằng default chỉ vì package có code. Cách giữ legacy endpoint hoặc harden phải explicit release decision với client test; blueprint không tự sửa AZ.
