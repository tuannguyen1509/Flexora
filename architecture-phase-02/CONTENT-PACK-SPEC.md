# Content Pack Specification v1.0.0

Content Pack là selective content/config transport Local→Production. Không phải whole-database dump, không có PHP/plugin installer và không tự kích hoạt module. Specification frozen, chưa có exporter/importer runtime.

## Code Package khác Content Pack

| Gói | Có | Không có |
|---|---|---|
| Child Code Package | PHP/HTML/CSS/site-only JS, Map schema, overrides, own assets, manifest/version | live values/users/orders/leads/credentials/database dump |
| Content Pack | selected page values/config, allowlisted site/theme settings, media references, identity/schema/version metadata | executable PHP/JS/schema callbacks, module implementation, whole DB, account/commerce records |

Deploy code trước hoặc song song vào staging, verify target có map/schema/provider đúng version; import data sau qua preview/snapshot/apply. Code update không tự import defaults/content.

## Container

```text
content-pack.zip
  manifest.json
  objects/pages.json
  values/maps.json
  settings/selected.json
  references/media.json
  references/links.json
  media/<sha256>.<validated-extension>  # optional binaries
```

JSON UTF-8, schema-strict, typed values; empty list `[]`, empty object `{}`, null khác nhau. Không PHP serialized objects, no eval/unserialize. Archive paths relative allowlist, no traversal/absolute paths/symlinks, no archive bombs. Manifest declares per-file SHA256 + uncompressed bytes + max objects/depth/media count/total-size supported limits; importer rejects unsupported/over-limit before extraction to private staging. Hash là integrity, **không chứng minh publisher trustworthy**; origin authorization và explicit target apply vẫn cần. Signing optional provider future, unsigned pack không được coi trusted code.

### Manifest fields bắt buộc

`format_version`, `pack_id` UUID, `content_revision`, `created_at` ISO8601, `source_site_id` logical opaque (no credential), `child_id`, `child_version`, `parent/api/module_constraints`, `maps` IDs/schema versions/hashes, `files` checksums, `scope`, `excluded_categories`, `reference_policy`, `merge_policy`, `required_capabilities`.

Scope là explicit objects/fields/keys allowlist, không wildcard wp_options hoặc meta. Manifest không được tự mở rộng privilege ngoài target allowed export/import binding registry. Any supplied unknown top-level critical field/version → reject hoặc capability negotiation theo format version; không silent assume.

### Ví dụ payload tối thiểu (minh họa data format)

```json
{
  "pack_id": "c8a6de13-8aa1-4d10-8fb0-810e09a6ba35",
  "objects": [
    {"ref": "page:about", "type": "page", "logical_id": "site-a-about", "match": "explicit-target", "template_id": "site.about"}
  ],
  "map_values": [
    {"object_ref": "page:about", "map_id": "site.about", "schema_version": "1.0.0", "present_fields": ["about.hero.title", "about.services"], "values": {"about.hero.title": "Giới thiệu", "about.services": []}}
  ],
  "settings": [],
  "media": []
}
```

Đây không phải complete manifest; fixed fields định nghĩa ở trên là bắt buộc trong gói thật. Source numeric IDs không là identity target. Repeater serialized transport dùng ordered item IDs/values theo map codec; empty `[]` được parser map thành empty collection có chủ đích, không mất presence.

## Portable references

| Ref | Resolve target |
|---|---|
| `media:<logical-id>` | exact existing attachment bằng verified checksum+type hoặc upload binary validated; filename/URL trùng không đủ |
| `package-asset:<owner>/<path>` | target code package URI, không tạo attachment nếu chỉ decorative asset |
| `page:<logical-id>` / `term:<logical-id>` | explicit mapped target object, existence/type/access checked; numeric origin chỉ hint |
| `menu:<logical-id>` | selected menu object/locations remap, không copy term ID |
| external URL | field policy http(s), preserved literal; source-local unresolved không giả external valid |

Nếu media đã tồn tại: dedupe checksum+mime, giữ existing alt/caption trừ explicit selected update; nếu missing binary/reference → unresolved conflict, không import URL localhost. Validate MIME thực, file count/total-size/dimensions, no executable SVG mặc định; không fetch arbitrary external URLs vì pack liệt kê. Remote fetch nếu sau này hỗ trợ cần provider policy riêng. Editor HTML có wp-image IDs/src/srcset/href links cần parser rewrite; CSS URL không phải content pack values trừ field codec rõ.

## Merge policy và conflict preview

Default `fill-missing`: chỉ set leaf missing, giữ existing null/false/0/empty string/empty list. Pack field omitted không write. `replace-selected` cần exact field selection + current target hash/revision trong plan. Không replace whole az_theme_options vì muốn đổi một hero title. Unknown stored branches preserve; unknown submitted fields reject. Retired schema fields không auto-delete; pack cannot request purge/drop/table SQL.

Conflict classes: target content ambiguous/no identity, unsupported schema/version, missing references, concurrent edits, incompatible field type/provider, excluded sensitive field, target permission thiếu. Preview hiển thị per field old/new/presence/action, objects create/match, IDs/media mapping, dependencies, warnings, exact exclusion, backup/rollback scope. Missing/ambiguous identity không auto choose by slug; operator chọn mapping trong plan.

Apply chỉ dùng approved concrete plan ID/hash và expected revisions; plan drift → regenerate preview. Idempotency `(pack_id,content_revision,target_scope)` + journal; retry không tạo thêm Page/attachment/repeater IDs. Migration schema required chưa installed → reject, không execute arbitrary schema từ pack.

## Allowed / excluded v1

Allowed opt-in: Page title/content/excerpt/template assignment được chọn, map values, selected public business/site/header/footer/theme settings, menu/widget assignment và Custom CSS **chỉ khi binding có codec+snapshot support và được chọn rõ**. V1 không auto-import front_page/show_on_front/domain settings; selected page_on_front change là explicit selected config action với target ID mapping/preview.

Excluded: orders/order meta/HPOS tables/customers/live leads/users/usermeta/accounts/session/password/auth salts; credentials/secret fields; license keys/counters/entitlements; cron/rewrite raw options/transients; arbitrary wp_options; plugin installs; arbitrary PHP/JS. User-meta storage capability không nằm Content Pack allowed scope. Không import script snippets hoặc imported HMAC trusted flag; Custom CSS selected phải sanitize scope/content, script integrations cấu hình target riêng.

## Import/export và recovery

Export yêu cầu capability và registry allowlist, redacts/excludes sensitive values; no “export all options”. Import require manage_options + object edit caps + scope rules; nonce/session transport kiểm nhưng không thay capability. Staging extraction không public execution. Snapshot required trước apply theo [migration strategy](DATA-MIGRATION-STRATEGY.md), gồm touched data/media refs/mods/CSS/widgets. No successful snapshot/restore capability → no write.

Post-apply verify object/template/map references/render-safe data, asset preflight, checksums; no mail/user registration/order action. Failure stops batches, creates compensating rollback plan, preserves subsequent live edits. New media auto-delete forbidden; restore refs trước. Pack/snapshot retention/access logging policy theo deployment, không publish raw customer/site private config trong reports.

Kết quả mong muốn: dựng Local một lần, deploy code + selected pack, remap target IDs/media/link/config có preview, không phải nhập lại từng field; dữ liệu production ngoài scope được giữ nguyên.
