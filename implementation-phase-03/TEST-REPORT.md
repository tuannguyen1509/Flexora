# Phase 03 — Test Report

Ngày chạy: **27/10/2026**, Windows x64 / PowerShell. Phần Phase03 dưới đây giữ kết quả lịch sử 188 assertions: [tests/phase-03-last-run.txt](tests/phase-03-last-run.txt). Kết quả stabilization mới xem mục Phase03A cuối tài liệu và [tests/last-run.txt](tests/last-run.txt).

## Kết quả thực tế

| Kiểm tra | Kết quả | Evidence / giới hạn |
|---|---|---|
| PHP lint | **PASS — 37 PHP files** | Toàn implementation, profiles, manifest và fixtures |
| Foundation native | **PASS — 64 assertions** | `tests/foundation.php flexora-native` |
| Foundation bridge | **PASS — 64 assertions** | Cùng file/cùng engine; profile az-bridge |
| Registration | **PASS — 10 assertions** | `tests/registration.php`: order, one dispatch, invalid/unsupported declarations, range helper |
| Assets | **PASS — 12 assertions** | `tests/assets.php`: graph atomic enqueue, physical collisions, core reference, scripts, late CSS |
| Compatibility patterns | **PASS — 13 assertions** | `tests/compatibility.php`: Child-first, early remove, pluggable, ledger, one engine/profile |
| Scope admin | **PASS — 5 assertions** | `tests/scope.php admin` |
| Scope AJAX | **PASS — 5 assertions** | `tests/scope.php ajax` |
| Scope REST | **PASS — 5 assertions** | `tests/scope.php rest` |
| Scope cron | **PASS — 5 assertions** | `tests/scope.php cron` |
| Scope CLI | **PASS — 5 assertions** | `tests/scope.php cli` |
| Baseline source integrity | **PASS — 435/435 files** | SHA-256 + size + missing/added check; zero differences |

Tổng **188 assertions** trong 10 lượt fixture process. Public façade chỉ expose read-only methods; supports/status không boot; registration không enqueue; no DB writes/content query counters; renderer một lần/echo một lần; unknown component safe; explicit presence cases; module disabled không hooks/assets; enabled boot một lần; exceptions không lộ secret trong result/diagnostics; callback identity/priority/accepted_args được kiểm. Các nội dung này kiểm bằng hành vi fixture, không chỉ search chuỗi source.

## Runtime và provenance

PHP không có trên PATH. Dùng **PHP 7.4.33 CLI**, `-n` (không php.ini), portable tại `%TEMP%/flexora-php-7.4.33/`; không đưa binary/vendor vào workspace hoặc Parent/Child. ZIP từ `windows.php.net/downloads/releases/archives/php-7.4.33-Win32-vc15-x64.zip`, đối chiếu SHA-256 metadata official releases JSON: `cdbb85b45f38f282f05764ca08648b5f92db99c75b2fb3848eb4a559f6553b48`.

Portable runtime chỉ phục vụ CLI fixture/lint, không dựng server. Điều này **không chốt minimum PHP** và không chứng nhận AZ hay Flexora full-platform support. Framework preview không thêm `Requires PHP/WP` metadata.

Đã thử chuẩn bị portable PHP8.4 build `8.4.27-1` ngoài workspace, checksum khớp metadata `dbae154a0a89791f36c3d48e68bae797878333e7d6cb5721e74617de59b63ab7`; Windows Application Control chặn executable. **SKIP execution PHP8.4**, không bypass policy, không báo PASS lint/runtime trên phiên bản này.

PowerShell mặc định chặn `.ps1`; runner được chạy bằng process riêng `-ExecutionPolicy Bypass`, không thay machine/user policy. Lỗi PowerShell đọc manifest array trong lần chạy đầu đã sửa; lần chạy cuối kiểm hash PASS. Không phải source AZ thay đổi.

## Chưa chạy / SKIP integration

| Kiểm tra | Trạng thái / lý do |
|---|---|
| WordPress integration thật | **SKIP** — không có WP core, test install hoặc DB trong workspace |
| WP_Hook mutation, lifecycle scope/order thật | **SKIP integration** — fixture API không phải implementation WP_Hook |
| Child AZ thật chạy trên bridge | **Chưa chạy** — chưa migrate functions/classes/path/DOM/data adapters; không activate package foundation |
| WordPress CSS printing/inline/dequeue/browser | **SKIP integration** — chỉ kiểm lời gọi register/enqueue và physical handles bằng fixture |
| Woo ON/OFF, permalink, Advanced, header/footer/home parity | **Ngoài phạm vi Phase03**, production features chưa chuyển |
| PHP8.4 execution | **SKIP** — Application Control block như trên |
| Các version PHP/WP/Woo/browser khác | **Chưa xác nhận** — support matrix là release gate còn mở |
| DB migration/import/rollback | **Không chạy** — không có implementation hoặc data mutation trong phase này |

`tests/wp-fixture.php` tự mô phỏng hook/asset APIs và fail khi gọi writer/content query thông dụng. Stub không là security sandbox và không chứng minh mọi third-party callback đều read-only. Source foundation không có SQL, network, cron scheduling, mail hoặc data write implementation. Không dùng fixture PASS để tuyên bố production parity hoặc full WordPress compatibility.

## Tái lập

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\implementation-phase-03\tools\Test-Foundation.ps1 -Php 'C:\path\to\php.exe'
```

Runner fail-fast khi lint/fixture/hash lỗi. Không tải vendor, không activate theme, không include AZ runtime. Cần PHP CLI được hệ điều hành cho phép chạy; portable PHP không là dependency production.

## Phase03A — kết quả stabilization hiện tại

Toàn bộ suite đã chạy lại trên **PHP7.4.33**. Lint **38 PHP files PASS**. Tổng **252 assertions** trong 12 process:

| Suite | Assertions PASS |
|---|---:|
| Foundation native / bridge | 64 + 64 |
| Component preflight native / bridge (mới) | 32 + 32 |
| Registration / Assets / Compatibility | 10 + 12 + 13 |
| Scope admin/AJAX/REST/cron/CLI | 5 × 5 |

Baseline Phase03: 188; Phase03A thêm 64. Các regression cũ được giữ, chỉ sửa expected API version/range theo behavior preview được yêu cầu. Không fake missing API. Fixture mới không gọi direct asset API trong consumer; component CSS và shared provider script tự resolve, render sau head một lần, repeated preflight/enqueue idempotent, cutoff/late CSS/script có diagnostic và không output HTML sai.

PHP8.4.27-1 sẵn có được thử lại nhưng **Application Control vẫn chặn executable — SKIP**. Không bypass policy. User báo PHP8.4.24 PASS qua review độc lập; không ghi nhận đó là lần chạy agent. Không có PHP8.x executable chạy được được tìm thấy trong các vị trí đã kiểm.

WordPress integration vẫn SKIP: không WP core/DB. Test chưa chứng nhận actual WP_Hook, CSS printing/footer execution/DOM/browser hoặc production AZ parity. SHA-256 **435/435 baseline PASS**, zero changed/missing/added. Specification và limitations: [PHASE-03A-STABILIZATION.md](PHASE-03A-STABILIZATION.md).
