# Architecture Decision Records

Status chung: **Accepted in blueprint baseline 1.0.0 — pending user review before implementation**. Không ADR nào là lệnh refactor Phase02. Mỗi ADR có Context, Decision, Reason, Compatibility impact, Tradeoffs. Thay quyết định bằng ADR superseding, không sửa ngầm semantics đã chốt.

| ADR | Quyết định |
|---|---|
| [001](001-parent-child-ownership.md) | Parent framework modular, Child một website |
| [002](002-public-api.md) | Public façade v1, internals thay được |
| [003](003-legacy-compatibility.md) | az-v1 và hai delivery profiles |
| [004](004-data-storage.md) | Adapter giữ legacy keys, native instance envelope khi cần |
| [005](005-template-map.md) | Template–Map pairing, typed fields/stable IDs |
| [006](006-asset-registry.md) | WP handles, provider→behavior→component, native-first |
| [007](007-child-overrides.md) | Declared views/variants/skins, không copy engine |
| [008](008-module-system.md) | Dependency/lifecycle/scope/disable đúng nghĩa |
| [009](009-content-pack.md) | Selective portable content/config + snapshots |
| [010](010-versioning.md) | Independent SemVer/schema/migration versions |
