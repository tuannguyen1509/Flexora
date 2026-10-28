# Feature / Module README Template

Dùng cho `inc/modules/<module>/README.md`, `inc/woocommerce/<module>/README.md` hoặc component README được STEP chỉ định. Không coi feature hoàn thành trước khi điền bằng chứng/test thực tế.

## Overview

Tên, version, STEP và vấn đề/behavior giải quyết.

## Scope

Phạm vi được giao, route/screen liên quan, ranh giới không sửa trong STEP.

## Files

File thêm/sửa, bootstrap owner, PHP/CSS/JS/template liên quan.

## Dependencies

WP/PHP, module/plugin cần thiết, guard khi WooCommerce OFF và khi dependency thiếu.

## Option Keys

Nested paths/default/type/sanitizer, absent so với false/0/empty, legacy fallback và key không đổi. Link OPTIONS-SCHEMA.

## Hooks

Action/filter, callback, priority, arguments, lifecycle load/unload.

## AJAX / REST

Action/namespace/method, schema request/response, auth, nonce khi áp dụng, error/rate limits. Ghi None nếu không có.

## Security

Permission/ownership, validation/sanitization, output escaping, SQL, credentials/logging.

## Performance

Điều kiện load PHP/assets/handlers, query/cache/version/invalidation, tác động khi disabled.

## Compatibility

Legacy names/options/hooks/templates, parent/child theme, Woo ON/OFF, external providers.

## Test Cases

Lệnh/kết quả thực tế và case chưa chạy, input xấu, dependency OFF, responsive/accessibility nếu có UI.

## Migration

Data changes, idempotency, backup, mapping; ghi No migration nếu không có. Không tự xóa option cũ.

## Rollback

Cách hoàn tác riêng feature, restore data/cache/version khi có. Không dùng rollback phá thay đổi khác.

## Changelog / Version

Ngày, version, STEP, change và liên kết docs/CHANGELOG.md. Cập nhật ROADMAP/MODULES/ARCHITECTURE khi liên quan.
