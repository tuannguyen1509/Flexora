(function ($) {
    'use strict';

    $(function () {
        if ($.fn.wpColorPicker) {
            $('.az-advanced__color').wpColorPicker();
        }

        $('[data-az-range]').on('input change', function () {
            var $input = $(this);
            var unit = $input.closest('.az-advanced__range').data('unit') || '';
            $input.siblings('[data-az-range-value]').text($input.val() + (unit ? ' ' + unit : ''));
        });

        function updateAttributeFilterStatus(input) {
            var checkbox = $(input);
            var status = checkbox.closest('.az-advanced__attribute-filter-control').find('.az-advanced__attribute-filter-status');
            if (!status.length) {
                return;
            }
            var enabled = checkbox.is(':checked');
            checkbox.closest('.az-advanced__repeater-row--attribute').toggleClass('is-enabled', enabled);
            status.text(enabled ? status.data('enabled') : status.data('disabled'));
        }

        $(document).on('change', '.az-advanced__repeater-row--attribute input[type="checkbox"]', function () {
            updateAttributeFilterStatus(this);
        });
        $('.az-advanced__repeater-row--attribute input[type="checkbox"]').each(function () {
            updateAttributeFilterStatus(this);
        });

        var frame;
        $('.az-advanced__media-button').on('click', function () {
            var button = $(this);
            var target = $('#' + button.data('target'));
            frame = wp.media({
                title: 'Select media',
                button: { text: 'Use this media' },
                multiple: false
            });
            frame.on('select', function () {
                var item = frame.state().get('selection').first().toJSON();
                var media = target.closest('.az-advanced__media');
                target.val(item.id);
                media.find('[data-preview]').html(item.type === 'image' && item.url ? '<img src="' + item.url.replace(/"/g, '&quot;') + '" alt="">' : '<span class="dashicons dashicons-media-default"></span>');
                media.find('[data-name]').text(item.filename || item.title || ('Attachment #' + item.id));
            });
            frame.open();
        });

        $('.az-advanced__media-remove').on('click', function () {
            var target = $('#' + $(this).data('target'));
            var media = target.closest('.az-advanced__media');
            target.val(0);
            media.find('[data-preview]').html('<span class="dashicons dashicons-format-image"></span>');
            media.find('[data-name]').text('No media selected');
        });

        $('#az-reset-section-form').on('submit', function () {
            return window.confirm('Reset this section?');
        });

        $(document).on('click', '[data-repeater-add]', function () {
            var box = $(this).closest('[data-repeater]');
            var row = box.find('[data-repeater-row]').first();
            var index = 0;
            box.find('[data-repeater-row] input,[data-repeater-row] textarea').each(function () {
                var match = this.name.match(/\[(\d+)\]/);
                if (match) {
                    index = Math.max(index, Number(match[1]) + 1);
                }
            });
            if (!row.length) {
                return;
            }
            var clone = row.clone();
            clone.removeAttr('data-sort-bound').removeClass('is-dragging');
            clone.find('input,select,textarea').each(function () {
                this.value = '';
                this.name = this.name.replace(/\[\d+\]/, '[' + index + ']');
            });
            box.find('[data-repeater-add]').before(clone);
        });

        $(document).on('click', '[data-repeater-remove]', function () {
            var box = $(this).closest('[data-repeater]');
            if (box.find('[data-repeater-row]').length > 1) {
                $(this).closest('[data-repeater-row]').remove();
            }
        });

        function toggleFields() {
            var fields = [
                { control: 'top_bar', targets: ['top_bar_text', 'top_menu'] },
                { control: 'show_search', targets: ['search_placeholder'] },
                { control: 'show_category', targets: ['category_menu', 'category_button_text'] },
                { control: 'show_account', targets: ['account_label'] },
                { control: 'show_cart', targets: ['cart_label'] },
                { control: 'show_cta', targets: ['cta_text', 'cta_url'] },
                { control: 'show_primary_menu', targets: ['primary_menu'] },
                { control: 'sticky', targets: ['sticky_top_bar', 'sticky_header_main', 'sticky_header_bottom', 'sticky_menu', 'sticky_show_search'] }
            ];
            fields.forEach(function (item) {
                var active = $('[name="az_theme_options[header][' + item.control + ']"]').is(':checked');
                item.targets.forEach(function (target) {
                    $('[name="az_theme_options[header][' + target + ']"]').closest('.az-advanced__field').toggle(active);
                });
            });
            var sticky = $('[name="az_theme_options[header][sticky]"]').is(':checked');
            var stickyBottom = sticky && $('[name="az_theme_options[header][sticky_header_bottom]"]').is(':checked');
            var stickyMain = sticky && $('[name="az_theme_options[header][sticky_header_main]"]').is(':checked');
            $('[name="az_theme_options[header][sticky_menu]"]').closest('.az-advanced__field').toggle(stickyBottom);
            $('[name="az_theme_options[header][sticky_show_search]"]').closest('.az-advanced__field').toggle(stickyMain);
        }

        $(document).on('change', '[name^="az_theme_options[header]"]', toggleFields);
        toggleFields();
    });
}(jQuery));
