/* A UI deterrent only: browsers and source code cannot be secured by JavaScript. */
(() => {
    'use strict';
    const editable = (target) => target instanceof Element && !!target.closest('input, textarea, select, [contenteditable]:not([contenteditable="false"]), [role="textbox"]');
    for (const name of ['copy', 'cut', 'contextmenu']) {
        document.addEventListener(name, (event) => {
            if (!editable(event.target)) event.preventDefault();
        });
    }
    document.addEventListener('keydown', (event) => {
        if (editable(event.target)) return;
        const key = event.key.toLowerCase();
        const modifier = event.ctrlKey || event.metaKey;
        const devtools = key === 'f12' || (modifier && event.shiftKey && ['i', 'j', 'c'].includes(key)) || (event.metaKey && event.altKey && ['i', 'j', 'c'].includes(key));
        if (devtools || (modifier && ['c', 'x', 'u'].includes(key))) event.preventDefault();
    });
})();
