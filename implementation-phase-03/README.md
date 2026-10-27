# Flexora Phase 03 — Engine Foundation

Author: **Tunlamweb**. Foundation version **0.1.0**, runtime API implementation **0.1.0 preview**, target Public API contract **1.0.0**. `api_version()` và `supports('api', range)` dùng runtime version; `supports('api', '^1.0')` là false. Architecture reference: [Baseline 1.0.0](../architecture-phase-02/FLEXORA-ARCHITECTURE.md). Đây là implementation cô lập để review và test, chưa phải Parent Theme có thể activate/deploy. Xem [Phase03A report](PHASE-03A-STABILIZATION.md).

Một engine duy nhất nằm tại `src/`; hai file `profiles/` chỉ chứa metadata. Không include source AZ, không copy engine cho bridge/native, không sửa theme slug hoặc database. Profile `az-bridge` hiện chỉ chứng minh compatibility patterns bằng fixture; **chưa hỗ trợ chạy Child AZ production**.

## Đã implement

- Kernel lifecycle; registration `after_setup_theme:30`, seal `:40`; scoped boot; diagnostics.
- Public functions: `flexora()`, `flexora_supports()`, `flexora_module()`, `flexora_use_component()`, `flexora_asset()`, `flexora_component()`, `flexora_render()`.
- Component preflight tự resolve behavior/provider/assets, không render hoặc cần Child biết dependency nội bộ.
- Public read-only façade và immutable V1 spec/context/results; Registrar chỉ có extension surfaces đã hoạt động.
- Component, asset, module registries cùng provider/behavior descriptors tối thiểu; ownership, duplicate/late rejection, dependency DAG.
- WordPress handle registration/enqueue theo usage; dependencies, idempotence, scope, physical-handle conflict và late-CSS diagnostics.
- Module fixture enabled/disabled, exactly-once scoped boot; Hook Ledger giữ callback identity/priority/accepted_args/owner/profile.
- `Compatibility/AzV1/manifest.php` và harness Child-first, early-require/remove, pluggable function, single engine/profile.
- Lookup presence và read-only binding interface; không có storage implementation.

## Chưa implement

Header/Footer/menu/sticky/contact/banner/Homepage/product/Woo/blog/Advanced/Dynamic CSS/Leads/SMTP/Auth/Analytics/License và mọi feature AZ production vẫn nằm nguyên baseline. Chưa có Template Map, field engine/admin/editor/repeater, legacy PHP façade toàn bộ, legacy data reader, migration/Content Pack/snapshot runtime, packaging/activation hoặc production asset/provider replacement.

Không expose `flexora_option/data/map/template/icon()` và Registrar `view/variant/map/template/field_type/storage_adapter()` khi các dependency runtime chưa có. Không giả dữ liệu hoặc false implementation để làm đầy API.

## Đọc và kiểm thử

1. [Child API quickstart](CHILD-API-QUICKSTART.md) — workflow component-first, không cần biết dependency nội bộ hoặc Kernel/container.
2. [Implementation notes](IMPLEMENTATION-NOTES.md) — subset, lifecycle, schema descriptor, boundaries và release gates.
3. [Test report](TEST-REPORT.md) — kết quả thực tế và các test SKIP.
4. [Source integrity](SOURCE-INTEGRITY.md) — 435 file baseline.

Chạy trên Windows với PHP CLI được phép thực thi:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\implementation-phase-03\tools\Test-Foundation.ps1 -Php 'C:\path\to\php.exe'
```

`ExecutionPolicy Bypass` chỉ áp dụng process chạy script test mới; không thay cấu hình hệ thống. Không bypass Application Control. Runner lint PHP, chạy mỗi fixture/profile/scope trong process riêng và kiểm SHA-256 baseline. Không cần Composer, JS framework, WP core hoặc DB để chạy unit fixtures này. Chúng **không thay thế integration WordPress**.

**PHASE 03 FOUNDATION COMPLETED — LEGACY RUNTIME UNCHANGED**
