# ADR 003 — Legacy az-v1 và packaging

## Context

Child Template az-theme cần Parent slug đúng trước PHP. Child còn early require contact/remove callbacks, pluggable functions và registry20/25.

## Decision

Hai delivery profiles cùng engine: az-bridge giữ physical az-theme và native flexora cho site mới/migrated. Stable early path/global callbacks/class façades, old handles physical owner, az-v1 DOM/data/route adapters. Không chạy hai engine; không tự sunset API cũ.

## Reason

PHP alias không giải quyết WordPress theme resolution, remove_action hay dequeue/inline ordering.

## Compatibility impact

Child cũ không phải sửa Template trong bridge; native slug transition là explicit migration có mods/CSS snapshot. Hook bị Child remove không reattach sau bootstrap.

## Tradeoffs

Hai package profiles cần build parity và release test. Branding Flexora có thể cùng slug az-theme ở bridge; legacy layer làm tăng maintenance nhưng tránh phá site.
