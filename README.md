# Flexora — FLX WordPress Parent Theme

Author **Tunlamweb**. Parent/package **0.1.0 preview**, runtime API **0.1.0** (target contract 1.0.0 chưa đầy đủ).

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

Hiện có minimal WordPress shell + native Accessibility Skip Link; không Header/Footer builder, Advanced, Template Map, WooCommerce hay business module. Engine không include/depend source AZ; Phase04 decision supersedes old compatibility proposal trong architecture history.

## Test và build

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File tools/Test-Foundation.ps1 -Php 'C:\path\php.exe'
powershell -NoProfile -ExecutionPolicy Bypass -File tools/Test-LocalWordPress.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File tools/Test-Browser.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File tools/Verify-SourceIntegrity.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File tools/build-theme.ps1
```

PHP fixtures: **323 assertions** (319 preserved + 4 real-head-timing regressions) on PHP7.4.33 and Docker PHP8.3.35. JS fixture23; real WordPress integration61; real Chromium browser19 per Parent/Child. No Node dependency in themes; Playwright exists only in disposable tooling image. Test results/logs under ignored `local/results/`; historical evidence remains under docs.

Build: `dist/flexora-0.1.0.zip`, `dist/flexora-child-starter-0.1.0.zip`. Parent ZIP root is only `flexora/`; no docs, reference, local stack, tests, credentials or historical code.

Current guide: [Child API](docs/CHILD-API-QUICKSTART.md). Full phase result: [Phase05](docs/implementation-history/PHASE-05-PROJECT-LOCAL-RUNTIME.md). Baseline **435 hashes identical**, move to references does not change content. No Git init; repository already existed, with pre-existing uncommitted Phase04 work. Staging preserved; no automatic commit combining that work.
