# ADR 008 — Module lifecycle và disabled state

## Context

AZ include nhiều module mọi request, auto instantiate hooks/schedule cron/schema checks. Guard rải rác không đồng nghĩa disabled.

## Decision

Module descriptor/version/dependency graph/scope/capability/route/job ownership. Disabled không feature hooks/assets/queries; façade declarations vẫn safe. Activation/migration side effects qua operation coordinator; disable giữ data, cleanup own hooks/jobs có plan.

## Reason

Framework có thể nhiều feature mà request nhẹ và predictable, không cần plugin cho mọi chức năng.

## Compatibility impact

Legacy effective states được inventory, không apply native off defaults vào site cũ. External AZ9S/plugin provider election tránh duplicate mail/leads. Không lazy-boot disabled module từ legacy getter.

## Tradeoffs

AJAX/REST/cron/init boot phases cần coordinator chính xác; missing dependency có thể block feature và yêu cầu fallback. High-risk license/upload cần protocol tests trước cutover.
