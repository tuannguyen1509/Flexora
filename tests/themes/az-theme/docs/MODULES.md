# Modules: current inventory

## STEP 16 bổ sung

[Homepage](../inc/modules/homepage/README.md) đã triển khai: `inc/modules/homepage/module.php`, admin.php, templates và assets. Có chín section với toggle/order/title và limit/columns cho danh sách. UI `themes.php?page=az-homepage`, action `admin_post_az_homepage_save`, nested storage `az_theme_options.homepage.sections`. Product sections bỏ qua khi Woo thiếu. Module manager chung vẫn planned; khối legacy bên dưới là baseline STEP 00.

Các hàng sau là nhóm source hiện hữu, không phải module có toggle. 19 module được nạp vô điều kiện từ functions.php; guard bên trong callback không tương đương disabled module. Tất cả nằm dưới `inc/functions/` trừ khi ghi khác.

| File / nhóm | Chức năng, hooks / dependencies | Trạng thái |
| --- | --- | --- |
| security.php | send_headers, wp_generator, login_errors | Active legacy |
| woocommerce.php | Cart fragments, gettext/ngettext, gateway/notices, viewed cookies, ensure shop/cart/checkout pages, pre_get_posts, wp_footer | Active legacy; nhiều guard WC, chưa gate loader |
| theme-setup.php | Setup supports, menus/walkers, product_warehouse, bilingual helper, enqueue/font/menu | Active legacy; mixed core/Woo/assets |
| pricing.php | CPT pricing, meta pricing/testimonial, customize_register | Active legacy; testimonial CPT provider chưa xác định |
| licenses.php | CPT license, metabox/list columns/save_post_license | Active legacy |
| admin-settings.php | Contact parent menu, lead/SMTP/custom-code settings, dbDelta/seed content, catalogue helpers | Active legacy |
| contact-settings.php | Schema/render/save contact, admin_post_az_save_contact_settings, screen assets | Active; standalone options, chưa registry STEP 02 |
| contact-form.php | CAPTCHA, public/private AJAX, lead + email | Active legacy; depends admin-settings helpers |
| integrations-access.php | APIs, cron expiration, login CSS, admin restrictions, plugin ZIP download | Active legacy; includes/license/plugin dependencies |
| translation.php | Google Translate calls, chunk/HTML handling | Active legacy external HTTP |
| term-language.php | EN category/tag meta; flush rewrite on theme switch | Active legacy |
| home-products.php | Categories/cards/prices/pagination and AJAX load more | Active legacy; WC dependency |
| customizer.php | Logo dynamic CSS, live preview, about fields include | Active legacy; optional service areas file missing |
| category-images.php | Category image metadata and admin media picker | Active legacy |
| menu-icons.php | Menu custom fields, saved icon, title filter, picker | Active legacy |
| product-attributes-pricing.php | Checkbox attribute type; category price multiplier + variation cache; category HTML filtering | Active legacy; checkbox replacement UI currently bypassed by early return |
| product-fields.php | Extra product metadata/editor fields | Active legacy Woo |
| content-media.php | Localize remote images on save, image grouping/TinyMCE fixes | Active legacy; post/page/product and network/media |
| product-videos.php | YouTube normalization and product metabox save | Active legacy Woo |
| inc/gioi-thieu-page-fields.php | About page structured fields, save_post_page | Active legacy |
| inc/license-api.php | License verification/activation/usage/tokens and login sync | Active legacy; external helpers |

## AJAX / admin-post

| Action | Callback | Access |
| --- | --- | --- |
| az_contact_refresh_captcha | az_contact_refresh_captcha | wp_ajax and nopriv; nonce |
| az_contact_form_submit | az_handle_contact_form | wp_ajax and nopriv; nonce + CAPTCHA |
| az_home_load_categories | az_home_load_categories_ajax | wp_ajax and nopriv; WC/taxonomy/post-type guards + nonce |
| az_save_contact_settings | az_save_contact_settings | admin_post only; manage_options + nonce |

## REST

All routes use POST and permission_callback __return_true. Callback-specific checks are separate from route permissions.

| Namespace | Route | Callback |
| --- | --- | --- |
| az-license/v1 | /verify | az_api_verify_license |
| az-license/v1 | /activate | az_api_activate_license |
| az-license/v1 | /usage | az_api_update_usage |
| az-license/v1 | /update-tokens | az_api_update_tokens |

Prompt namespace only registers on license-server configuration. Keep old namespaces for clients; future new APIs use az/v1 with explicit compatibility plan.

## CPT / taxonomy / shortcode / cron

- Registered CPT: pricing, license. Testimonial metabox exists but no testimonial registration found in theme.
- Registered taxonomy: product_warehouse attached to product. Other taxonomy templates consume product_cat/product_brand/pa_brand/pa_thuong-hieu; their existence depends on Woo/plugin/configuration.
- No add_shortcode or do_shortcode call found in theme. Seeded content contains external shortcodes: contact-form-7, woocommerce_cart, woocommerce_checkout, woocommerce_my_account, woocommerce_order_tracking. Rendering depends on WordPress content filters and shortcode providers; see admin-settings.php and woocommerce.php.
- Daily cron: az_check_expired_licenses -> az_auto_expire_licenses; scheduling occurs during module load if missing. No theme unschedule call identified by scan.

## Planned manager

blog, woocommerce, ajax_search, wishlist, compare, recently_viewed, floating_contact, popup, mega_menu, performance are planned registry IDs in STEP 12. Blog/viewed/contact functionality exists in legacy form; do not confuse it with completed optional modules. No new runtime module was created in STEP 00, therefore no module folder README was added; future modules must use FEATURE-TEMPLATE.

Full WooCommerce add_action/add_filter/do_action/apply_filters and asset callsites: [CALLSITES](audit/CALLSITES.md); filter first argument containing woocommerce/product_attributes/loop_shop. Full include map: [FILES](audit/FILES-AND-PREFIXES.md).

## Cập nhật 2026-10-05

Đã gỡ Prompt Execution API và các endpoint `az-prompt/v1` khỏi theme. License API khác được giữ nguyên. Thư mục `audit/` là snapshot lịch sử, không phản ánh code hiện tại.
