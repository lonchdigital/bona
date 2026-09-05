const MOBILE_BREAKPOINT = '(max-width: 960px)';
const REVEAL_SCROLL_OFFSET = 32;

function initNavigation(navigation)
{
    const mediaQuery = window.matchMedia(MOBILE_BREAKPOINT);
    const header = document.querySelector('[data-site-header]');
    const menuToggle = header?.querySelector('[data-menu-toggle]');
    const categoriesLink = navigation.querySelector('[data-mobile-bottom-categories]');
    const revealOnScroll = navigation.hasAttribute('data-reveal-on-scroll');
    const navigationEntry = typeof performance.getEntriesByType === 'function'
        ? performance.getEntriesByType('navigation')[0]
        : null;
    const isFreshNavigation = !navigationEntry || navigationEntry.type === 'navigate';
    let hasBeenRevealed = !revealOnScroll;
    let pageHasShown = document.readyState === 'complete';

    const updateRevealState = () => {
        if (hasBeenRevealed
            || (!pageHasShown && isFreshNavigation)
            || window.scrollY < REVEAL_SCROLL_OFFSET) {
            return;
        }

        hasBeenRevealed = true;
        window.removeEventListener('scroll', handleScroll);
    };

    const setVisible = () => {
        const shouldShow = mediaQuery.matches
            && hasBeenRevealed
            && !document.body.classList.contains('bona-menu-open')
            && !document.body.classList.contains('bona-cart-drawer-open');

        navigation.classList.toggle('is-visible', shouldShow);
        navigation.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');
        document.body.classList.toggle('bona-mobile-bottom-nav-visible', shouldShow);
    };

    function handleScroll()
    {
        updateRevealState();
        setVisible();
    }

    const handlePageShow = (event) => {
        pageHasShown = true;

        if (!isFreshNavigation || event.persisted) {
            updateRevealState();
        }

        setVisible();
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

    if (revealOnScroll) {
        window.addEventListener('scroll', handleScroll, { passive: true });

        if (pageHasShown && !isFreshNavigation) {
            updateRevealState();
        }
    }

    window.addEventListener('pageshow', handlePageShow);
    setVisible();
}

export default {
    init() {
        document.querySelectorAll('[data-mobile-bottom-navigation]').forEach(initNavigation);
    },
};
