# ADR 001 — Parent / Child ownership

## Context

AZ Parent có reusable helpers nhưng trộn license/leads/auth; Child có SOVIBAN brand cùng carousel/mega-menu reusable. Phase01 xác định ownership từ implementation.

## Decision

Parent là WordPress Website Framework, chứa Core, Reusable và Application Modules. Child = một website, giữ branding/layout/maps/site workflows. Không bắt buộc “non-presentation = plugin”. Promotion cần review ownership/API/data/assets.

## Reason

Tái dùng tối đa foundation và giảm plugin/dependency duplicate nhưng vẫn kiểm soát feature boot.

## Compatibility impact

Không di chuyển module hoặc ép Child đổi trong Phase02. Legacy UI/business APIs được adapters giữ; app module data không bị bỏ khi disable.

## Tradeoffs

Parent lớn hơn theme thuần presentation; cần descriptors/tests/security ownership. Khi plugin provider có sẵn phải election, không boot hai implementation.
