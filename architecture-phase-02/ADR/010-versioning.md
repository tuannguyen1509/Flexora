# ADR 010 — Versioning độc lập

## Context

Một Parent update có thể đổi code mà schema/API không đổi; schema nhỏ có thể breaking dù file diff nhỏ. Cờ version theme không mô tả migration complete.

## Decision

SemVer riêng cho Parent, Child, Public API, Module, Template Map schema; migration ledger IDs/checksums và Content Pack format riêng. Breaking API/default/DOM/data meaning không minor silently. Deprecation cần replacement+support window, chưa auto sunset legacy.

## Reason

Child khai ranges, deploy code/data theo đúng compatibility; rollback biết code và data boundaries.

## Compatibility impact

az-v1 contract version được giữ, adapter parity test mỗi release. Parent rollback không tự downgrade data; cần verified restore plan.

## Tradeoffs

Nhiều version cần manifest/CI tooling; release notes rõ thay vì chỉ bump một số. Supported platform minima là release gate chờ runtime verification, không lấy metadata AZ cũ làm proof.
