# Phase 03 — Source Integrity

Ngày: **27/10/2026**. Baseline authoritative: [Phase01 source-manifest.json](../../audit-phase-01/source-manifest.json), SHA-256 manifest `90BA133B5C6BB692C70E2D7200F4196C70E34AAB92BBEC7AB5B5200952EC14EE`.

| Scope | Baseline | Kết quả |
|---|---:|---|
| `themes/themes/az-theme/` | 330 files | Giữ nguyên |
| `themes/themes/az-theme-child/` | 105 files | Giữ nguyên |
| Tổng | **435 files** | **435 khớp SHA-256 và byte size; 0 changed, 0 missing, 0 added** |

Đã đối chiếu 435 hashes trước implementation và kiểm lại cuối bằng [Verify-SourceIntegrity.ps1](tools/Verify-SourceIntegrity.ps1). Script enumerate cả hidden files (`-Force`) trong hai theme để phát hiện file thêm ngoài manifest; không chỉ hash các file còn tồn tại.

Mọi source/docs/test mới nằm trong `implementation-phase-03/`. Không sửa báo cáo Phase01, blueprint/ADR Phase02, prefix/key/slug, baseline Parent/Child; không di chuyển/xóa source, không DB/live runtime, không activate theme, không migration và không commit. PHP test tools tạm nằm ngoài workspace trong `%TEMP%`.

Kiểm lại độc lập:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\implementation-phase-03\tools\Verify-SourceIntegrity.ps1
```

Raw evidence cùng test runner: [tests/last-run.txt](tests/last-run.txt). Đây là content integrity, không phải Git diff (workspace không có Git repository).

**PHASE 03 FOUNDATION COMPLETED — LEGACY RUNTIME UNCHANGED**

Phase03A stabilization: kiểm lại trước/sau thay foundation; vẫn **435 expected / 435 actual, 0 changed, 0 missing, 0 added**. Không sửa source/history Phase01/02, không DB hoặc migration. Source/docs/tests thay đổi chỉ trong implementation-phase-03. Raw evidence hiện tại: [tests/last-run.txt](tests/last-run.txt).

Phase04: FLX normalization + native skip-link files stay outside baseline. Baseline checks before/after **435/435, modified0/missing0/added0**. Removed former compatibility/profile implementation files only within preview area by the new user decision. Reference themes and Phase01/02 unchanged. No persistent writes, runtime dependency, activation or cut-over to the reference themes.
