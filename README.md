# Flexora — FLX WordPress Parent Theme

Author **Tunlamweb**. Parent/Child Starter **0.3.0 preview**, runtime API **0.1.0** (target contract 1.0.0 chưa đầy đủ).

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

Hiện có minimal WordPress shell, Accessibility Skip Link, **FLX Field Engine + Advanced** và **Template Map cho Page**. Child đăng ký template/map; Page Admin dùng chung 13 field types/editor/media/repeater; dữ liệu lưu riêng từng Page và map, đọc bằng `flx_data()`. Xem [Field Engine](docs/FIELD-ENGINE.md) và [Template Map quickstart](docs/TEMPLATE-MAP-QUICKSTART.md). Chưa có Header/Footer builder, WooCommerce hay business module. Engine không include/depend source AZ; Phase04 decision supersedes old compatibility proposal trong architecture history.

## Test và build

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File tools/Test-Foundation.ps1 -Php 'C:\path\php.exe'
powershell -NoProfile -ExecutionPolicy Bypass -File tools/Test-LocalWordPress.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File tools/Test-Browser.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File tools/Verify-SourceIntegrity.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File tools/build-theme.ps1
```

PHP fixtures: **486 assertions** on PHP7.4.33 and Docker PHP8.3.35 (466 retained + 20 map registry). JS fixture23; real WordPress integration **133** (61 existing + field/storage16 + maps56); real Chromium **156** (38 frontend + Advanced76 + maps42). Run `tools/Test-Fields.ps1`, then `tools/Test-TemplateMaps.ps1` on the existing primary Child site. Mapped Pages use native Classic editing; Page revisions remain functional but map-meta historical restore is deferred. No Node dependency in themes; Playwright exists only in disposable tooling image. Results/screenshots/credentials are ignored under `local/results/`.

Build: `dist/flexora-0.3.0.zip`, `dist/flexora-child-starter-0.3.0.zip`. Parent ZIP root is only `flexora/`; no docs, reference, local stack, tests, credentials or historical code. Child ships generic empty-content template examples; no live data.

Current guide: [Child API](docs/CHILD-API-QUICKSTART.md). Full phase result: [Phase05](docs/implementation-history/PHASE-05-PROJECT-LOCAL-RUNTIME.md). Baseline **435 hashes identical**, move to references does not change content. No Git init; repository already existed, with pre-existing uncommitted Phase04 work. Staging preserved; no automatic commit combining that work.
