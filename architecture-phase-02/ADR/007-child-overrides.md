# ADR 007 — Child override và promotion

## Context

Child single-product copy gần nguyên Parent để đổi vài phần, CSS phụ thuộc DOM sâu. Cần đổi skin không nhân đôi engine.

## Decision

Declared renderer/view/variant/style override theo contract version; behavior/provider reuse. Development search/reuse/extend trước override. Candidate→parent-ready qua review; deprecated-local sau Parent release và Child chuyển Public API.

## Reason

Nâng Parent được mà không synchronize engine copied trong mọi Child.

## Compatibility impact

Old Child được bridge; không buộc rewrite ngay. Parent-only taxonomy proxy behavior giữ trong az-v1; native resolver policy riêng explicit.

## Tradeoffs

Child phải khai metadata và giữ required DOM roles. Provider replace bị hạn chế hơn raw script enqueue, đổi lại có kiểm dependency và a11y.
