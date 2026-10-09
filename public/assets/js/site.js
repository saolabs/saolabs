(() => {
    // Delegation keeps these controls working after Saola hydration or navigation.
    const closeMenu = () => {
        const toggle = document.querySelector('.menu-toggle');
        const menu = document.getElementById('mobile-nav');
        if (!toggle || !menu) return;
        menu.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
    };

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) return;
        const toggle = event.target.closest('.menu-toggle');
        if (toggle) {
            const menu = document.getElementById('mobile-nav');
            if (!menu) return;
            menu.hidden = !menu.hidden;
            toggle.setAttribute('aria-expanded', String(!menu.hidden));
            return;
        }
        const link = event.target.closest('a');
        if (link?.hasAttribute('data-language-link')) {
            // Preserve a shared section when changing the URL's language.
            const destination = new URL(link.href, location.origin);
            // Carry a section only when it also exists on the translated page.
            if (location.hash && document.getElementById(location.hash.slice(1))) {
                destination.hash = location.hash;
            }
            link.href = destination.href;
        }
        if (event.target.closest('#mobile-nav a') || !event.target.closest('.site-header')) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && document.querySelector('.menu-toggle')?.getAttribute('aria-expanded') === 'true') {
            closeMenu();
            document.querySelector('.menu-toggle')?.focus();
        }
    });

    const wideScreen = window.matchMedia('(min-width: 901px)');
    wideScreen.addEventListener('change', (event) => {
        if (event.matches) closeMenu();
    });
})();
