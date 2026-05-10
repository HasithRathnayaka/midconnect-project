(function () {
    const EXIT_DURATION_MS = 320;

    function shouldHandleNavigation(link, event) {
        if (!link || event.defaultPrevented) return false;
        if (event.button !== 0) return false;
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;
        if (link.target && link.target.toLowerCase() === '_blank') return false;
        if (link.hasAttribute('download')) return false;

        const href = link.getAttribute('href') || '';
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return false;
        if (href.startsWith('mailto:') || href.startsWith('tel:')) return false;

        const url = new URL(link.href, window.location.href);
        if (url.origin !== window.location.origin) return false;
        if (url.pathname === window.location.pathname && url.search === window.location.search) return false;

        return true;
    }

    function runEnterAnimation() {
        document.body.classList.add('page-enter');
        window.setTimeout(() => {
            document.body.classList.remove('page-enter');
        }, EXIT_DURATION_MS);
    }

    function setupExitAnimation() {
        document.addEventListener('click', function (event) {
            const link = event.target.closest('a[href]');
            if (!shouldHandleNavigation(link, event)) return;

            event.preventDefault();
            const destination = link.href;

            document.body.classList.add('page-exit');
            window.setTimeout(() => {
                window.location.href = destination;
            }, EXIT_DURATION_MS);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Respect reduced motion preferences.
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        runEnterAnimation();
        setupExitAnimation();
    });
})();
