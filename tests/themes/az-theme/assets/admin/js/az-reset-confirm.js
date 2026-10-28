(function () {
  'use strict';
  var form = document.getElementById('az-reset-section-form');
  if (!form) return;
  var clean = form.cloneNode(true);
  form.parentNode.replaceChild(clean, form);
  clean.addEventListener('submit', function () {
    return window.confirm('Bạn có chắc chắn muốn reset toàn bộ cài đặt của mục này về mặc định không? Dữ liệu hiện tại của mục đang mở sẽ bị thay thế.');
  });
})();
