(function () {
    'use strict';
    var toggle = document.querySelector('.az-steel-category-toggle');
    var categories = document.getElementById('az-steel-categories');
    if (toggle && categories) {
        var mobile = window.matchMedia('(max-width: 640px)');
        var dropdown = !!toggle.closest('.az-steel-category-dropdown');
        function syncMenu() {
            categories.classList.remove('az-is-open');
            toggle.setAttribute('aria-expanded', dropdown || mobile.matches ? 'false' : 'true');
        }
        syncMenu();
        mobile.addEventListener('change', syncMenu);
        toggle.addEventListener('click', function () {
            if (!dropdown && !mobile.matches) { var first = categories.querySelector('a'); if (first) { first.focus(); } return; }
            toggle.setAttribute('aria-expanded', categories.classList.toggle('az-is-open') ? 'true' : 'false');
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && categories.classList.contains('az-is-open')) { syncMenu(); toggle.focus(); }
        });
        document.addEventListener('click', function (event) {
            if (dropdown && !categories.contains(event.target) && !toggle.contains(event.target)) { syncMenu(); }
        });
        categories.addEventListener('focusout', function (event) {
            if (dropdown && !categories.contains(event.relatedTarget) && !toggle.contains(event.relatedTarget)) { syncMenu(); }
        });
    }
    document.querySelectorAll('[data-az-steel-slider]').forEach(function (slider) {
        if (slider.dataset.azSteelInitialized === '1') { return; }
        slider.dataset.azSteelInitialized = '1';
        var slides = Array.from(slider.querySelectorAll('[data-az-steel-slide]'));
        if (slides.length < 2) { return; }
        var index = 0, timer = null, paused = false, hovering = false, focused = false;
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
        var status = slider.querySelector('[data-az-steel-status]');
        var pause = slider.querySelector('[data-az-steel-pause]');
        var dots = Array.from(slider.querySelectorAll('[data-az-steel-dot]'));
        var features = Array.from(slider.querySelectorAll('[data-az-steel-feature]'));
        var stage = slider.querySelector('.az-child-carousel__stage');
        function show(next) {
            index = (next + slides.length) % slides.length;
            slides.forEach(function (slide, i) { slide.hidden = i !== index; });
            features.forEach(function (feature) { feature.hidden = Number(feature.dataset.azSteelFeature) !== index; });
            if (features.length && stage) { stage.classList.toggle('az-child-carousel__stage--feature', features.some(function (feature) { return !feature.hidden; })); }
            if (status) { status.textContent = (index + 1) + ' / ' + slides.length; }
            dots.forEach(function (dot, i) { dot.setAttribute('aria-pressed', String(i === index)); });
            slider.querySelectorAll('[data-az-project-peek]').forEach(function (peek) {
                var direction = peek.dataset.azProjectPeek === 'prev' ? -1 : 1;
                var clone = slides[(index + direction + slides.length) % slides.length].cloneNode(true);
                clone.hidden = false;
                clone.removeAttribute('data-az-steel-slide');
                peek.replaceChildren(clone);
            });
        }
        function restart() {
            window.clearInterval(timer);
            if (slider.dataset.autoplay === '1' && !paused && !hovering && !focused && !reduce.matches && !document.hidden) {
                timer = window.setInterval(function () { show(index + 1); }, 3000);
            }
        }
        slider.querySelector('[data-az-steel-prev]').addEventListener('click', function () { show(index - 1); restart(); });
        slider.querySelector('[data-az-steel-next]').addEventListener('click', function () { show(index + 1); restart(); });
        dots.forEach(function (dot, i) { dot.addEventListener('click', function () { show(i); restart(); }); });
        if (pause) { pause.addEventListener('click', function () {
            paused = !paused;
            pause.setAttribute('aria-pressed', paused ? 'true' : 'false');
            pause.setAttribute('aria-label', paused ? 'Tiếp tục tự chuyển banner' : 'Tạm dừng tự chuyển banner');
            pause.textContent = paused ? '▶' : 'Ⅱ';
            restart();
        }); }
        slider.addEventListener('mouseenter', function () { hovering = true; restart(); });
        slider.addEventListener('mouseleave', function () { hovering = false; restart(); });
        slider.addEventListener('focusin', function () { focused = true; restart(); });
        slider.addEventListener('focusout', function (event) { focused = slider.contains(event.relatedTarget); restart(); });
        slider.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') { event.preventDefault(); show(index + (event.key === 'ArrowLeft' ? -1 : 1)); restart(); }
        });
        document.addEventListener('visibilitychange', restart);
        reduce.addEventListener('change', restart);
        restart();
    });
})();
