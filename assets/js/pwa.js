(() => {
    'use strict';

    if (!('serviceWorker' in navigator) || !window.isSecureContext) {
        return;
    }

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/portal-sw.js', { scope: '/' }).catch(() => {
            // PWA installation is progressive enhancement; Portal remains usable.
        });
    });
})();
