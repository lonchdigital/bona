const MOBILE_BREAKPOINT = '(max-width: 960px)';

function initNavigation(navigation)
{
    const mediaQuery = window.matchMedia(MOBILE_BREAKPOINT);
    const header = document.querySelector('[data-site-header]');
    const menuToggle = header?.querySelector('[data-menu-toggle]');
    const categoriesLink = navigation.querySelector('[data-mobile-bottom-categories]');

    const setVisible = () => {
        const shouldShow = mediaQuery.matches
            && !document.body.classList.contains('bona-menu-open')
            && !document.body.classList.contains('bona-cart-drawer-open');

        navigation.classList.toggle('is-visible', shouldShow);
        navigation.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');
        document.body.classList.toggle('bona-mobile-bottom-nav-visible', shouldShow);
    };

    categoriesLink?.addEventListener('click', (event) => {
        if (!mediaQuery.matches || !menuToggle) {
            return;
        }

        event.preventDefault();
        menuToggle.click();
    });

    if (typeof mediaQuery.addEventListener === 'function') {
        mediaQuery.addEventListener('change', setVisible);
    } else {
        mediaQuery.addListener(setVisible);
    }

    const bodyClassObserver = new MutationObserver(setVisible);
    bodyClassObserver.observe(document.body, {
        attributes: true,
        attributeFilter: ['class'],
    });

    window.addEventListener('pageshow', setVisible);
    setVisible();
}

export default {
    init() {
        document.querySelectorAll('[data-mobile-bottom-navigation]').forEach(initNavigation);
    },
};
