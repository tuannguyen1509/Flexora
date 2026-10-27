(function () {
    'use strict';

    var settings = window.azLogoVariants || {};

    function selectLogo(header) {
        var isMobile = window.matchMedia('(max-width: 782px)').matches;
        if (isMobile && settings.mobile) {
            return settings.mobile;
        }
        return settings.desktop || '';
    }

    function refreshLogo(header) {
        var source = selectLogo(header);
        var link = header.querySelector('.az-logo');

        if (!source || !link) {
            return;
        }

        var image = link.querySelector('img');
        if (!image) {
            image = document.createElement('img');
            image.alt = settings.alt || '';
            image.decoding = 'async';
            link.textContent = '';
            link.appendChild(image);
        }

        if (image.getAttribute('src') !== source) {
            image.setAttribute('src', source);
            image.removeAttribute('srcset');
        }
    }

    function refreshAll() {
        document.querySelectorAll('.az-site-header').forEach(refreshLogo);
    }

    document.addEventListener('DOMContentLoaded', function () {
        refreshAll();
        window.addEventListener('resize', refreshAll, { passive: true });
        window.addEventListener('scroll', refreshAll, { passive: true });
    });
}());
