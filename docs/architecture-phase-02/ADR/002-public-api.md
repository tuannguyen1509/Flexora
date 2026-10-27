# ADR 002 — Public façade và internal API

## Context

Child đang sửa AZ registry trực tiếp và dùng helpers/DOM/handles chưa versioned. Rename class không tạo API ổn định.

## Decision

Public functions/Contracts V1 là boundary; immutable read/render result, public Registrar và authorized command interface. Child mới không gọi internal classes/container. Presence explicit, raw data và escape tại view.

## Reason

AI/Child dùng đơn giản, implementation có thể thay mà signature/semantics còn ổn định.

## Compatibility impact

AZ wrappers vẫn có old semantics riêng, không ép escaped social URL/empty fallback giống v1. Public hooks và DOM contracts cũng versioned.

## Tradeoffs

Thêm façade/DTO/adapter và docs; phải test behavioral contract thay vì chỉ class visibility. Mutation không convenience-helper tùy ý trong template.
