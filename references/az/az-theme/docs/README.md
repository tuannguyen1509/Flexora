# AZ Theme Documentation

Update 2026-09-17: [Homepage Framework STEP 16](../inc/modules/homepage/README.md) implemented independently. STEP 01–15 remain planned. Audit JSON/callsites below are historical STEP 00 snapshots, not current source hashes; do not regenerate OPTIONS-SCHEMA automatically without preserving the STEP 16 schema section.

Baseline STEP 00, ngày 2026-09-16. Phạm vi: theme tại `wp-content/themes/az-theme`, không bao gồm WordPress core, plugin, dữ liệu database hoặc file ZIP ngoài thư mục theme.

## Trạng thái

Theme hiện tại là Classic Theme, tên trong stylesheet là TN Digi Solution, version 1.0.0, text domain `az-ai-theme`. Đã có loader 19 module trong `inc/functions/` và form liên hệ admin mới từ các thay đổi trước STEP 00. Đây chưa phải framework có registry/module manager theo kiến trúc đích.

STEP 00 chỉ thêm tài liệu và công cụ quét source. Không sửa PHP/CSS/JS chạy trên website; không bootstrap WordPress, không truy cập DB, không đổi option hoặc chạy migration. Asset và template cũ giữ nguyên.

## Đọc tài liệu

- [ROADMAP](ROADMAP.md): phạm vi STEP 00–15 và điều kiện dừng.
- [ARCHITECTURE](ARCHITECTURE.md): kiến trúc hiện tại và đích dự kiến.
- [CODING-STANDARDS](CODING-STANDARDS.md): quy ước code mới và tương thích legacy.
- [MODULES](MODULES.md): các chức năng đã có, hooks và phụ thuộc.
- [OPTIONS-SCHEMA](OPTIONS-SCHEMA.md): khóa option/theme_mod, callsite và trạng thái.
- [DATABASE](DATABASE.md): bảng, metadata, transient, cron và tác động ghi hiện có.
- [COMPATIBILITY](COMPATIBILITY.md): hợp đồng phải giữ, WooCommerce OFF và phụ thuộc thiếu.
- [SECURITY](SECURITY.md): phát hiện tĩnh và tiêu chí kiểm tra.
- [TESTING](TESTING.md): kiểm tra đã chạy và ma trận runtime chưa chạy.
- [CHANGELOG](CHANGELOG.md): thay đổi theo STEP.
- [FEATURE-TEMPLATE](FEATURE-TEMPLATE.md): mẫu tài liệu cho feature/module mới.

## Bằng chứng đầy đủ

- [Files và prefixes](audit/FILES-AND-PREFIXES.md): toàn bộ file, SHA nằm trong JSON, khai báo hàm/class và prefix.
- [API callsites](audit/CALLSITES.md): toàn bộ lần khớp API đọc/ghi option, theme_mod, hook, endpoint, CPT, taxonomy, shortcode, cron, enqueue và metadata.
- [Source inventory JSON](audit/source-inventory.json): biểu thức tham số đầy đủ và hash source.
- [Công cụ audit](tools/audit-source.cjs): Node.js, không cần WordPress/PHP/database; chạy từ bất kỳ thư mục nào bằng đường dẫn tới script.

Kết quả scanner là bằng chứng tĩnh, không phải danh sách module đã bật trên site. Biểu thức động được giữ nguyên, không giả định giá trị DB. Comment, JavaScript nhúng và phương thức cùng tên có thể xuất hiện trong danh sách ứng viên; các nhận định runtime phải đọc context và kiểm thử riêng.

Các tài liệu cũ trong `plants/` ở root repository vẫn là tham chiếu lịch sử về CSS/admin. Bộ docs này đi cùng theme khi phân phối; không xóa hay di chuyển các tài liệu cũ trong STEP 00.
