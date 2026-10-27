# ADR 004 — Data adapters và legacy storage

## Context

AZ data có options arrays, JSON, CSV IDs, Page/term/user meta, mods/CSS, hai leads tables và plugin integration; cùng prefix không cùng schema.

## Decision

Giữ physical legacy keys; Flexora đọc/ghi qua explicit binding. Native maps mới có versioned page-instance envelope khi cần; không auto migrate old data. Support nhiều storage adapters với existence/revision/codec và single writer.

## Reason

Đổi branding không có lợi ích dữ liệu. Adapter cho phép engine thay và bảo toàn empty/default/unknown semantics.

## Compatibility impact

az_theme_options/_az_home_page_data/_az_shared_banner tiếp tục hoạt động; mods theo stylesheet explicit, không copy Custom CSS post ID. Unknown branches được giữ.

## Tradeoffs

Phải duy trì nhiều codecs/bindings, không một option global cho mọi thứ. Cross-store transaction không được giả định; cần journal/snapshot/locks.
