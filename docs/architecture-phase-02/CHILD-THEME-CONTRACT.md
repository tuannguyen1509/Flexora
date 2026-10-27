# Child Theme Contract / Starter / Promotion

Một Child = một website. Source Child không chứa copy site khác, sample SOVIBAN/steel mặc định hay live settings/content/user data. Tái sử dụng Parent trước khi custom. Blueprint này không tạo Child source.

## Cấu trúc starter đề xuất

```text
site-child/
  style.css                       # theme metadata; Template theo delivery profile
  functions.php                   # register callback, không copy bootstrap Parent
  child-manifest.json             # site identity/version/API/module constraints
  config/                         # code defaults/capability declarations, no secrets
  templates/
    pages/about/{template.php,map.php,style.css}
    pages/contact/{template.php,map.php,style.css}
  page-about.php                  # thin WP filename bridge nếu cần
  overrides/
    views/<component-id>/<variant>.php
    styles/<component-id>.css
    templates/<template-id>.php
  assets/{images,icons,styles,scripts}/ # own artwork/skin/site-only behavior
  features/site-specific/<id>/
  features/candidates/<id>/
  docs/{ownership,contracts,content-deployment}.md
  tests/{maps,views,compatibility}/
```

`child-manifest` yêu cầu child_id (stable không domain), version, package_profile, parent/api/module SemVer constraints, templates/maps/view contracts, asset IDs, ownership records. Không ghi localhost/domain/uploads path tuyệt đối. Gói code chỉ code/schema/own assets; database changes là operation riêng theo Content Pack/migration coordinator.

## Hai thứ tự khác nhau

Development: **Search Parent → reuse → extend Parent nếu reusable → Child override → custom Child nếu thật sự riêng**. AI phải ghi existing IDs đã kiểm tra và lý do custom.

Runtime renderer lookup: **explicit Child custom renderer → Child declared view override → Parent variant/view**. Sau chọn view, resolver vẫn sử dụng **behavior → provider** của component. Behavior/provider không phải fallback view trả HTML; thiếu behavior phải báo unavailable/fallback static theo component spec, không thử include library làm template.

### Override contract

| Loại | Child được làm | Giới hạn / fallback |
|---|---|---|
| View | Register exact component/variant + view_contract version + owner-relative path | Giữ required roles/data selectors/props; missing file → Parent view nếu compatible; invalid contract → reject và diagnostic |
| Style | Declare skin asset depends on Parent base; token/variant override có scope | Không replace engine/init bằng inline script. Replace-view-css cần explicit policy, accessible base invariants vẫn giữ |
| Variant | Add stable variant ID và props defaults, reuse data/behavior | Không copy timer/query engine; unsupported variant → registered default, không đoán file name |
| Provider | Explicit registered replacement đủ capability/version, no duplicate namespace | Không override bằng filename. Conflict/cap mismatch → blocked; không tự cài third-party |
| Template | Register logical ID→view+map+assets hoặc compatible override | WP root filename bridge giữ data `_wp_page_template`; không đổi schema binding vì đổi HTML |
| Asset | Child owns asset ID riêng; parent replacement chỉ khi override_policy cho | No core/vendor deregister toàn site; registry duy nhất, version/hash và usage scoped |
| Site feature | Module descriptor under `site.*`, permission/data ownership rõ | Không gọi Parent internals, không ghi Parent options unknown tùy tiện |

Resolver nhận trusted package paths, realpath confined vào declared owner root; không path từ request/map values. Cache theo Child+Parent+view-contract version. Override precedence deterministic; duplicate explicit renderer registration từ hai owner reject. File update không tự reset values hoặc cài child data defaults.

## Header/footer, Woo và homepage

Child giữ branding/layout/header/footer HTML, shared primitives/actions/menu/mobile behaviors từ Parent. Product card đổi HTML bằng view/variant, ratio/count bằng props/settings, không copy nguyên single-product engine. Homepage là Template Map instance, Parent cung cấp carousel/gallery/product/post components; `site.about`/homepage data không thành singleton global dùng lẫn các website.

Với AZ Child hiện tại, mọi rule mới chỉ áp khi đã chuyển Child sang v1. Không ép rewrite old Child trong update Parent; early require/pluggable registry/DOM được bridge xử lý. Footer SOVIBAN hiện đọc Page homepage metadata, không tự di chuyển sang settings chung để “chuẩn hóa”. Legacy routing Child giữ owner tới khi promote có test redirect.

## Candidate → Parent workflow

Ownership record cho mỗi feature/component gồm: ID/owner/status, source_files, behavior/API/assets/data binding, dependencies, evidence of reuse, site assumptions, a11y/security/perf notes, license/author, proposed public contract, migration/cutover plan, replacement ID/version.

| Trạng thái | Điều kiện vào/ra |
|---|---|
| `site-specific` | Brand/layout/workflow riêng, ở Child; có thể đề xuất candidate khi bóc được assumptions |
| `candidate` | Có lợi ích nhiều site nhưng còn local; ghi evidence và điểm site coupling; không tự copy Parent |
| `parent-ready` | Review API/module boundary, typed props, usage assets, a11y/security/perf, docs/tests, multi-site examples hoặc use cases rõ; maintainer chấp nhận promotion proposal |
| `deprecated-local` | Parent version đã release replacement, Child chuyển gọi public ID, compatibility adapter/tests có sẵn; chỉ one implementation active |

Quy trình: inventory Parent/registry → proposal + generic contract → review → implementation trong phase được authorise → regression/a11y/perf → Parent release → Child raises minimum range và switches public calls → giữ thin local adapter nếu old consumer cần → remove local duplication **chỉ trong task được duyệt**, không auto-deletion. Data schema/item IDs/DOM legacy giữ alias có version khi cần.

AZ candidates thực: steel/shared-banner carousel, Child Mega Menu, product ratio/count/gallery, partners media gallery, contact launcher, typed repeater tooling. SOVIBAN copy/images, industry fallback labels, location artwork, footer defaults tiếp tục site-specific. Product pretty URLs có thể candidate nhưng không bật mặc định site mới. Không tự promote một feature chỉ vì xuất hiện hai lần copy.

## Update guarantees và kiểm tra

Parent update không ghi Child source, không reset data/default seed. Child update không thay data ngoài explicit migration. Compatibility range mismatch có diagnostic và safe fallback, không silent fatal. Tests tương lai: map references/presence, changed view required selectors, no Parent internal imports, registry usage/no duplicate vendor, conditional template CSS, Parent minor update với Child snapshot, Content Pack dry-run/rollback. Không chỉ kiểm tên class/source substring.
