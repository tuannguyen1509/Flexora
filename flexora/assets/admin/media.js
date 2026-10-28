(function () {
    'use strict';
    var activeDrag;
    function preview(container, attachment) {
        var target = container.querySelector('.flx-media-preview');
        target.replaceChildren();
        if (attachment) {
            var image = document.createElement('img'); image.alt = attachment.alt || '';
            image.src = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
            target.append(image);
        }
        container.querySelector('[data-flx-media-select]').textContent = attachment ? 'Thay ảnh' : 'Chọn ảnh';
    }
    function galleryItem(container, data) {
        var item = document.createElement('div'); item.className = 'flx-gallery-item'; item.draggable = true;
        var input = document.createElement('input'); input.type = 'hidden'; input.name = container.dataset.flxName + '[]'; input.value = data.id;
        var image = document.createElement('img'); image.alt = data.alt || ''; image.src = data.sizes && data.sizes.thumbnail ? data.sizes.thumbnail.url : data.url;
        item.append(input, image);
        var buttons = document.createElement('div');
        [['up', '↑', 'Đưa ảnh lên'], ['down', '↓', 'Đưa ảnh xuống'], ['remove', '×', 'Xóa ảnh']].forEach(function (spec) {
            var button = document.createElement('button'); button.type = 'button'; button.className = 'button'; button.dataset['flxGallery' + spec[0][0].toUpperCase() + spec[0].slice(1)] = ''; button.textContent = spec[1]; button.setAttribute('aria-label', spec[2]); buttons.append(button);
        });
        item.append(buttons); return item;
    }
    document.addEventListener('click', function (event) {
        var button = event.target.closest('button'); if (!button) return;
        var media = button.closest('[data-flx-media]'), gallery = button.closest('[data-flx-gallery]');
        if (media && button.hasAttribute('data-flx-media-remove')) { media.querySelector('input').value = '0'; preview(media, null); }
        if (media && button.hasAttribute('data-flx-media-select') || gallery && button.hasAttribute('data-flx-gallery-select')) {
            var frame = wp.media({title: gallery ? 'Chọn nhiều ảnh' : 'Chọn ảnh', library: {type: 'image'}, multiple: !!gallery});
            frame.on('close', function () { frame.remove(); });
            frame.on('open', function () {
                if (media && Number(media.querySelector('input').value)) frame.state().get('selection').add(wp.media.attachment(Number(media.querySelector('input').value)));
            });
            frame.on('select', function () {
                var selected = frame.state().get('selection').toJSON();
                if (media && selected[0]) { media.querySelector('input').value = selected[0].id; preview(media, selected[0]); }
                if (gallery) {
                    var list = gallery.querySelector('.flx-gallery-items');
                    selected.forEach(function (data) { if (!list.querySelector('input[value="' + data.id + '"]')) list.append(galleryItem(gallery, data)); });
                }
            }); frame.open();
        }
        if (gallery) {
            var item = button.closest('.flx-gallery-item'); if (!item) return;
            if (button.hasAttribute('data-flx-gallery-remove')) { item.remove(); gallery.querySelector('[data-flx-gallery-select]').focus(); }
            if (button.hasAttribute('data-flx-gallery-up') && item.previousElementSibling) item.previousElementSibling.before(item);
            if (button.hasAttribute('data-flx-gallery-down') && item.nextElementSibling) item.nextElementSibling.after(item);
            if (button.isConnected) button.focus();
        }
    });
    document.addEventListener('dragstart', function (event) { var item = event.target.closest('.flx-gallery-item'); if (item) { activeDrag = item; event.dataTransfer.setData('text/plain', 'flx-gallery'); } });
    document.addEventListener('dragover', function (event) { if (activeDrag && event.target.closest('.flx-gallery-item')) event.preventDefault(); });
    document.addEventListener('drop', function (event) { var target = event.target.closest('.flx-gallery-item'); if (activeDrag && target && target !== activeDrag && target.parentNode === activeDrag.parentNode) { event.preventDefault(); target.before(activeDrag); } activeDrag = null; });
    document.addEventListener('dragend', function () { activeDrag = null; });
}());
