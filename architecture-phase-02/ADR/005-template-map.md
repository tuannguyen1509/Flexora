# ADR 005 — Template Map và typed Field Engine

## Context

Advanced merge tốt nhưng registry/storage/render gắn cứng, nested fields thành text; Child schema riêng dùng numeric lists.

## Decision

Template và Map versioned pair; stable field IDs độc lập labels/storage path, stable repeater item IDs. Field registry typed với generic WP rich editor, typed nested controls. Retire field giữ data; rename/type change cần migration.

## Reason

AI sửa HTML/CSS dễ nhưng không làm mất editable content hoặc đổi identity theo layout.

## Compatibility impact

Legacy numeric list/schema còn adapter riêng, không chuyển UUID trên read. Existing az save semantics giữ profile; native omission/no-op và empty list/clear được định nghĩa riêng.

## Tradeoffs

Schema validation/editor lifecycle/concurrency phức tạp hơn clone rows. Không auto delete data khi UI bỏ field; cần retention/review sau.
