# Contact Shortcodes

## General fields

Advanced General stores repeatable contact data in a repeater. Each row has two fields:

- Hotline row: `Tên hotline` + `Số điện thoại`.
- Address row: `Tên địa điểm` + `Địa chỉ`.

Legacy options such as `az_contact_phone` and `az_contact_address` remain fallback values and are not deleted.

## Shortcodes

Use `[az_contact type="phone"]` to render hotline items.

Use `[az_contact type="address"]` to render address items.

## Typography

Advanced Typography reads registered WordPress Font Library families when the API is available. The selected family is saved in `az_theme_options` and used by the site's dynamic CSS variables.

Phone values are rendered as `tel:` links. Empty values are omitted. The shortcode output uses the `az-` class namespace.
