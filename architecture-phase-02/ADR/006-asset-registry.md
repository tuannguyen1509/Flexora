# ADR 006 — Assets và providers

## Context

AZ có native behaviors, global CDN Tailwind/Font Awesome, nhiều CSS layers. Child chưa bundle vendor trùng nhưng duplicate engine/template.

## Decision

WP-handle-based logical registry, provider→behavior→component. Native-first; no new vendor by default. Core nhẹ, feature enabled+scope, component usage preflight trước head. Mutable legacy handle là physical owner trong az-v1.

## Reason

Parent chứa khả năng không có nghĩa load tất cả; reuse dependency và giữ Child inline/dequeue semantics.

## Compatibility impact

Old handles/DOM adapters giữ tới parity/cutover có test. Không chỉ enqueue canonical+alias rồi assume dequeue old chặn cả hai.

## Tradeoffs

Late dynamic render cần discovery manifest/lazy mount contract, không enqueue CSS muộn tùy tiện. Provider/version conflicts phải giải quyết explicit.
