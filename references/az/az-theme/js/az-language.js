/**
 * AZ Language Switcher - Client-side bilingual system
 * Reads localStorage, swaps [data-vi] / [data-en] elements instantly
 * No server reload needed — bypasses all caching layers
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'az_language';
    var DEFAULT_LANG = 'vi';

    // ── Helpers ──────────────────────────────────────────────────────────────

    function getLang() {
        try {
            return localStorage.getItem(STORAGE_KEY) || DEFAULT_LANG;
        } catch (e) {
            return DEFAULT_LANG;
        }
    }

    function setLang(lang) {
        try {
            localStorage.setItem(STORAGE_KEY, lang);
        } catch (e) {}
        // Also set cookie so PHP can read it on next server render
        var d = new Date();
        d.setTime(d.getTime() + 30 * 24 * 60 * 60 * 1000);
        document.cookie = STORAGE_KEY + '=' + lang + '; expires=' + d.toUTCString() + '; path=/; SameSite=Lax';
    }

    // ── Apply language to all bilingual elements ──────────────────────────────

    function applyLang(lang) {
        // Swap all [data-vi] / [data-en] elements
        var els = document.querySelectorAll('[data-vi][data-en]');
        for (var i = 0; i < els.length; i++) {
            var el = els[i];
            // For <option> elements, use .text property to avoid stripping child nodes
            if (el.tagName === 'OPTION') {
                el.text = lang === 'en' ? el.getAttribute('data-en') : el.getAttribute('data-vi');
            } else {
                el.textContent = lang === 'en' ? el.getAttribute('data-en') : el.getAttribute('data-vi');
            }
        }

        // Swap article count labels (archive page)
        var countEls = document.querySelectorAll('.az-article-count');
        for (var c = 0; c < countEls.length; c++) {
            var ce = countEls[c];
            var n = parseInt(ce.getAttribute('data-count'), 10) || 0;
            var label = lang === 'en'
                ? (n === 1 ? ce.getAttribute('data-en-singular') : ce.getAttribute('data-en-plural'))
                : (n === 1 ? ce.getAttribute('data-vi-singular') : ce.getAttribute('data-vi-plural'));
            ce.textContent = n + ' ' + label;
        }

        // Swap placeholder attributes for inputs
        var inputs = document.querySelectorAll('[data-placeholder-vi][data-placeholder-en]');
        for (var p = 0; p < inputs.length; p++) {
            inputs[p].placeholder = lang === 'en'
                ? inputs[p].getAttribute('data-placeholder-en')
                : inputs[p].getAttribute('data-placeholder-vi');
        }

        // Toggle VI/EN content blocks — only hide VI if a paired EN sibling exists with content
        var viBlocks = document.querySelectorAll('.az-content-vi');
        for (var v = 0; v < viBlocks.length; v++) {
            var viBlock = viBlocks[v];
            // Look for the next sibling .az-content-en within the same parent
            var enBlock = null;
            var sibling = viBlock.nextElementSibling;
            while (sibling) {
                if (sibling.classList && sibling.classList.contains('az-content-en')) {
                    enBlock = sibling;
                    break;
                }
                sibling = sibling.nextElementSibling;
            }

            if (lang === 'en') {
                if (enBlock && enBlock.innerHTML.trim() !== '') {
                    // Has EN content — swap
                    viBlock.style.display = 'none';
                    enBlock.style.display = '';
                } else {
                    // No EN content — keep VI visible, hide empty EN block
                    viBlock.style.display = '';
                    if (enBlock) enBlock.style.display = 'none';
                }
            } else {
                // VI mode — always show VI, hide EN
                viBlock.style.display = '';
                if (enBlock) enBlock.style.display = 'none';
            }
        }

        // Update switcher button active state
        var btns = document.querySelectorAll('#az-lang-switcher .az-lang-btn');
        for (var j = 0; j < btns.length; j++) {
            var btn = btns[j];
            if (btn.getAttribute('data-lang') === lang) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        }

        // Set html lang attribute
        document.documentElement.lang = lang === 'en' ? 'en' : 'vi';

        // Update document title if EN title is defined
        var titleEl = document.querySelector('meta[name="az-title-en"]');
        if (titleEl) {
            var titleVi = document.querySelector('meta[name="az-title-vi"]');
            if (lang === 'en' && titleEl.content) {
                document.title = titleEl.content;
            } else if (lang === 'vi' && titleVi && titleVi.content) {
                document.title = titleVi.content;
            }
        }

        // Update data-vnd / data-usd price elements (product cards on all pages)
        var priceEls = document.querySelectorAll('[data-vnd]');
        for (var k = 0; k < priceEls.length; k++) {
            var pe  = priceEls[k];
            var usd = pe.getAttribute('data-usd');
            var vnd = pe.getAttribute('data-vnd');
            if (lang === 'en' && usd && usd !== '') {
                pe.textContent = usd;
            } else if (vnd && vnd !== '') {
                pe.textContent = vnd;
            }
        }
    }

    // ── Init ─────────────────────────────────────────────────────────────────

    function init() {
        var lang = getLang();
        applyLang(lang);

        // Bind switcher buttons
        document.addEventListener('click', function (e) {
            // Support clicks on child elements (img, span) inside the button
            var target = e.target;
            var btn = null;
            if (target.classList && target.classList.contains('az-lang-btn')) {
                btn = target;
            } else if (target.closest) {
                btn = target.closest('.az-lang-btn');
            } else {
                // IE fallback: walk up the tree
                var el = target;
                while (el && el !== document) {
                    if (el.classList && el.classList.contains('az-lang-btn')) { btn = el; break; }
                    el = el.parentNode;
                }
            }
            if (!btn) return;

            var newLang = btn.getAttribute('data-lang');
            if (!newLang || newLang === getLang()) return;

            setLang(newLang);
            applyLang(newLang);
            // Notify other scripts that language changed
            document.dispatchEvent(new CustomEvent('az:langchange', { detail: { lang: newLang } }));
        });
    }

    // Run as early as possible
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Also run immediately for elements already in DOM (inline scripts)
    applyLang(getLang());

    // Re-apply after DOM fully loaded to catch elements rendered after script tag
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            applyLang(getLang());
        });
    }

    // Expose applyLang globally so external scripts can trigger a re-apply
    window.azApplyLang = applyLang;
    window.azGetLang   = getLang;

    // After DOM ready, trigger price currency update if a price handler exists
    function triggerPriceUpdate() {
        var lang = getLang();
        if (lang === 'en' && typeof window.azApplyPriceCurrency === 'function') {
            window.azApplyPriceCurrency('en');
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', triggerPriceUpdate);
    } else {
        triggerPriceUpdate();
    }

})();
