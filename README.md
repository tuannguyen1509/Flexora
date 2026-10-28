# Flexora — FLX WordPress Parent Theme

Author **Tunlamweb**. Parent/Child Starter **0.4.0 preview**, runtime API **0.1.0** (target contract 1.0.0 chưa đầy đủ).

**Canonical runtime source: `flexora/`.** Không phát triển trong phase history. Parent và Child Starter đã chạy trên WordPress localhost thật; đây vẫn là development preview, chưa cut-over website production.

```text
flexora/                     # self-contained WordPress Parent
flexora-child-starter/       # minimal Child, Template: flexora
docs/
  audit-phase-01/
  architecture-phase-02/
  implementation-history/phase-03/   # frozen Phase03–04 snapshot
references/az/              # original Parent/Child, reference only
tests/                      # regression + browser fixtures against flexora/
tools/                      # test, integrity, ZIP tools
local/                      # isolated Docker WordPress, ignored credentials/results
dist/                       # ignored ZIP outputs
```

## Local development

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File local/start.ps1
```

Mở **http://localhost:8090/**; admin **http://localhost:8090/wp-admin/**. Credentials development được sinh trong `local/.env` ignored; không nằm trong report/Git. WordPress bind-mount trực tiếp Parent và Child: **save → refresh**, không copy/build mỗi lần sửa. Final active theme: **Flexora Child Starter**. Xem [local guide](local/README.md).

Hiện có WordPress shell, **Header Foundation** (native logo/primary menu, accessible responsive navigation), Accessibility Skip Link, **FLX Field Engine + Advanced** và **Template Map cho Page**. Child đăng ký template/map; Page Admin dùng chung 13 field types/editor/media/repeater; dữ liệu lưu riêng từng Page và map, đọc bằng `flx_data()`. Xem [Field Engine](docs/FIELD-ENGINE.md) và [Template Map quickstart](docs/TEMPLATE-MAP-QUICKSTART.md). Chưa có Header/Footer builder, WooCommerce hay business module. Engine không include/depend source AZ; Phase04 decision supersedes old compatibility proposal trong architecture history.

## Test và build

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File tools/Test-Foundation.ps1 -Php 'C:\path\php.exe'
powershell -NoProfile -ExecutionPolicy Bypass -File tools/Test-LocalWordPress.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File tools/Test-Browser.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File tools/Verify-SourceIntegrity.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File tools/build-theme.ps1
```

PHP fixtures: **510 assertions** on PHP7.4.33 and Docker PHP8.3.35 (486 Phase07 retained + 24 Header). JS fixture23; real WordPress integration **154** (61 existing + field/storage16 + maps56 + Header21); real Chromium **292** (38 frontend + Advanced76 + maps42 + Header83 + logo14 + reuse regression39). Run `tools/Test-Fields.ps1`, then `tools/Test-TemplateMaps.ps1` and `tools/Test-Header.ps1` on the existing primary Child site. Mapped Pages use native Classic editing; Page revisions remain functional but map-meta historical restore is deferred. No Node dependency in themes; Playwright exists only in disposable tooling image. Results/screenshots/credentials are ignored under `local/results/`.

Build: `dist/flexora-0.4.0.zip`, `dist/flexora-child-starter-0.4.0.zip`. Parent ZIP root is only `flexora/`; no docs, reference, local stack, tests, credentials or historical code. Child ships generic empty-content template examples; no live data.

Current guide: [Child API](docs/CHILD-API-QUICKSTART.md). Current phase result: [Phase08 Header](docs/implementation-history/PHASE-08-FLX-HEADER.md), with explicit [reuse audit](docs/implementation-history/PHASE-08-REUSE-AUDIT.md). Earlier [Phase05](docs/implementation-history/PHASE-05-PROJECT-LOCAL-RUNTIME.md) remains historical. Baseline **435 hashes identical**. Reference code is read-only; reuse/adapt proven logic before creating a new implementation, without adding an AZ runtime dependency.
