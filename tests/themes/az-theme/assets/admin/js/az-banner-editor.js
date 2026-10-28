(function () {
  'use strict';
  document.querySelectorAll('.az-banner-editor').forEach(function (editor) {
    var input = editor.querySelector('[data-az-banner-images]');
    var preview = editor.querySelector('[data-az-banner-preview]');
    var frame;
    editor.querySelector('[data-az-banner-pick]').addEventListener('click', function () {
      if (!frame) {
        frame = wp.media({ title: 'Chọn ảnh banner', library: { type: 'image' }, multiple: 'add', button: { text: 'Dùng các ảnh này' } });
        frame.on('open', function () {
          var selection = frame.state().get('selection'); selection.reset();
          input.value.split(',').filter(Boolean).forEach(function (id) {
            var attachment = wp.media.attachment(Number(id)); attachment.fetch(); selection.add(attachment);
          });
        });
        frame.on('select', function () {
          var images = frame.state().get('selection').toJSON().slice(0, 20);
          input.value = images.map(function (image) { return image.id; }).join(',');
          preview.replaceChildren();
          images.forEach(function (image) {
            var img = document.createElement('img');
            img.src = image.sizes && image.sizes.thumbnail ? image.sizes.thumbnail.url : image.url;
            img.alt = ''; img.width = 80; img.height = 60; img.dataset.id = image.id;
            img.style.cssText = 'object-fit:cover;margin:4px'; preview.appendChild(img);
          });
          decorate();
        });
      }
      frame.open();
    });
    function decorate() {
      preview.querySelectorAll('img').forEach(function (img) {
        if (img.parentElement.tagName === 'SPAN') return;
        var item = document.createElement('span'); item.style.display = 'inline-block';
        img.before(item); item.appendChild(img);
        [['←', 'Đưa ảnh lên trước', -1], ['→', 'Đưa ảnh về sau', 1], ['×', 'Xóa ảnh', 0]].forEach(function (action) {
          var button = document.createElement('button'); button.type = 'button'; button.className = 'button'; button.textContent = action[0]; button.setAttribute('aria-label', action[1]);
          button.addEventListener('click', function () {
            if (!action[2]) item.remove();
            else if (action[2] < 0 && item.previousElementSibling) item.previousElementSibling.before(item);
            else if (action[2] > 0 && item.nextElementSibling) item.nextElementSibling.after(item);
            input.value = Array.from(preview.querySelectorAll('img')).map(function (image) { return image.dataset.id; }).join(',');
          });
          item.appendChild(button);
        });
      });
    }
    decorate();
    editor.querySelector('[data-az-banner-clear]').addEventListener('click', function () { input.value = ''; preview.replaceChildren(); });
  });
  // Restore defaults after a successful AJAX term creation, not after validation errors.
  if (window.jQuery) window.jQuery(document).ajaxSuccess(function (event, xhr, settings) {
    if (typeof settings.data !== 'string' || !/(^|&)action=add-tag(&|$)/.test(settings.data)) return;
    if (!xhr.responseXML || xhr.responseXML.querySelector('wp_error') || !xhr.responseXML.querySelector('term_id')) return;
    var editor = document.querySelector('#addtag .az-banner-editor');
    if (!editor) return;
    editor.querySelectorAll('input').forEach(function (input) { input.value = input.defaultValue; input.checked = input.defaultChecked; });
    editor.querySelector('select').value = 'none';
    editor.querySelector('[data-az-banner-preview]').replaceChildren();
  });
})();
