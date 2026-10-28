(function () {
    'use strict';
    document.querySelectorAll('[data-home-partners-admin]').forEach(function (root) {
        var list = root.querySelector('[data-home-partner-list]');
        var template = root.querySelector('[data-home-partner-template]');
        var add = root.querySelector('[data-home-partner-add]');
        var clear = root.querySelector('[data-home-partner-clear]');
        var frame;
        function refresh() {
            var count = list.children.length;
            root.querySelector('[data-home-partner-count]').textContent = count + ' logo đã chọn';
            root.querySelector('[data-home-partner-empty]').hidden = count > 0;
            clear.hidden = count === 0;
        }
        function append(image) {
            if (image.type !== 'image' || !image.id || !image.url) { return; }
            var duplicate = Array.from(list.children).some(function (row) {
                return Number(row.querySelector('[name="az_home_partner_id[]"]').value) === image.id || row.querySelector('[name="az_home_partner_image[]"]').value === image.url;
            });
            if (duplicate) { return; }
            var row = template.content.firstElementChild.cloneNode(true);
            row.querySelector('[name="az_home_partner_id[]"]').value = image.id;
            row.querySelector('[name="az_home_partner_image[]"]').value = image.url;
            row.querySelector('img').src = image.sizes && image.sizes.thumbnail ? image.sizes.thumbnail.url : image.url;
            list.appendChild(row);
        }
        add.addEventListener('click', function () {
            if (!window.wp || !wp.media) { return; }
            if (!frame) {
                frame = wp.media({title: 'Chọn logo đối tác', button: {text: 'Thêm các logo đã chọn'}, multiple: 'add', library: {type: 'image'}});
                frame.on('open', function () { frame.state().get('selection').reset(); });
                frame.on('select', function () {
                    frame.state().get('selection').toJSON().forEach(append);
                    refresh();
                    add.focus();
                });
            }
            frame.open();
        });
        root.addEventListener('click', function (event) {
            var remove = event.target.closest('[data-home-partner-remove]');
            if (remove) {
                remove.closest('[data-home-partner-logo]').remove();
                refresh();
                add.focus();
            }
        });
        clear.addEventListener('click', function () {
            list.replaceChildren();
            refresh();
            add.focus();
        });
        refresh();
    });
})();
