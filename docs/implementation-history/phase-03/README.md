# Flexora / FLX — Native Engine Preview

Author **Tunlamweb**. **Flexora = product name; FLX = code prefix.** Runtime API implementation **0.1.0 preview**, target Public API contract **1.0.0** chưa đầy đủ. `flx_supports('api', '^1.0')` trả false. Đây là implementation cô lập ngoài baseline, chưa phải Parent package có thể activate/deploy.

Phase04 user quyết định bỏ hướng compatibility runtime của Phase02. Source theme cũ chỉ là behavior reference; một native engine, không bridge/profile/legacy adapters hay runtime dependency. Future migration tool là task riêng. Không sửa Phase01/02 history; đây là quyết định mới, không giả vờ đã tồn tại từ Phase03.

## Runtime hiện tại

- Namespaces `FLX\*`, public functions `flx()`, `flx_supports()`, `flx_module()`, `flx_use_component()`, `flx_component()`, `flx_render()`, `flx_asset()`; hooks `flx/*`. Không giữ former preview API aliases vì workspace không có consumer production được tìm thấy.
- Một engine trong `src/`, một metadata profile `profiles/flx-native.php`. Bootstrap/native assets dự kiến đặt tại Parent root trong packaging phase sau; không tự activate source preview.
- Registration `after_setup_theme:30`, seal40; readonly facade, immutable results, descriptors/ownership, dependency graph, diagnostics, module scope/boot-once và Hook Ledger.
- Component preflight tự resolve behavior/provider/assets; không renderer, query hoặc write. Registry chỉ WP-register/enqueue assets được dùng; shared handle enqueue một lần, late component dependency có diagnostic.
- First native feature: `flx.accessibility` → `flx.accessibility.skip-link` component/behavior → native CSS + footer JS. Default frontend enabled, module preflights at wp, emits once at wp_body_open1. No Child asset/JS setup, no DB/options/vendor.
- Generic Child-first/early-removal/pluggable patterns vẫn được test, không kéo runtime theme reference vào engine.

## Chưa triển khai

Header/Footer/menu/sticky/contact/banner/Homepage/product/Woo/blog/Advanced/Dynamic CSS/Leads/SMTP/Auth/Analytics/License/Template Map/field engine/storage adapters/migration tools vẫn ngoài scope. Không fake missing API, default data, page/cron/installer hoặc DB write. Không chuyển hay cut-over website.

## Docs và verification

1. [Phase04 authoritative report](PHASE-04-FLX-ACCESSIBILITY.md) — naming reversal, native seam, IDs/handles/DOM/lifecycle, deviations và gates.
2. [Child API quickstart](CHILD-API-QUICKSTART.md) — Parent feature dùng ngay, extension registration là vai trò khác.
3. [Implementation notes](IMPLEMENTATION-NOTES.md) — implementation details; historical compatibility sections explicitly superseded.
4. [Test report](TEST-REPORT.md) — lịch sử và current results; [source integrity](SOURCE-INTEGRITY.md).
5. [Phase03A historical report](PHASE-03A-STABILIZATION.md) — preview/preflight rationale; old two-profile direction no longer applies.

PHP7.4.33: **319 assertions PASS**, lint **40 files**. Trong đó foundation252, accessibility46, naming/CSS21. JS: **23 assertions PASS** trong tools V8 DOM fixture, không real browser. PHP8.x executable bị Application Control chặn; WordPress core/DB chưa có. **WORDPRESS INTEGRATION GATE = OPEN**. Baseline **435/435**, modified0/missing0/added0.

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\implementation-phase-03\tools\Test-Foundation.ps1 -Php 'C:\path\php.exe'
# Khi có Node được policy cho phép: thêm -Node 'C:\path\node.exe'
# Hoặc: node implementation-phase-03/tests/skip-link.test.js
```

PowerShell switch chỉ áp dụng process test mới, không thay machine/user settings; không bypass Application Control. No Composer/vendor dependencies. PHP runner ghi rõ SKIP nếu không truyền Node; kết quả JS tools V8 riêng tại [phase-04-js.txt](tests/phase-04-js.txt).

**PHASE 04 FLX ACCESSIBILITY COMPLETED — AZ USED AS REFERENCE ONLY**
