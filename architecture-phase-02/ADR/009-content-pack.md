# ADR 009 — Local → Production Content Pack

## Context

Code ZIP không chuyển map values/media target IDs và không backup database. Whole DB import có thể ghi đè live orders/leads/users.

## Decision

Child Code Package riêng; Content Pack JSON selective scope, schema constraints, portable refs/checksums, explicit target mapping, fill-missing mặc định. Preview→snapshot verified→revision-checked apply→scoped conflict-aware rollback. Exclude live commerce/lead/user/secret/license/session data v1.

## Reason

Chuyển nội dung Local một lần không nhập lại từng field, bảo vệ production changes.

## Compatibility impact

Legacy keys vẫn bindings, typed remap chỉ known data. Unknown/unresolved local URL hoặc ID block thay vì global search/replace. Imported scripts không auto-trusted bằng HMAC cũ.

## Tradeoffs

Cần identity mapping/media parsers/snapshot backend. Checksums không là publisher authorization, rollback phải xử lý concurrent edits và không tự xóa shared media.
