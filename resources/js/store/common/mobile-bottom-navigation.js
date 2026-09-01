const MOBILE_BREAKPOINT = '(max-width: 960px)';
const MIN_SCROLL_DELTA = 8;
const MIN_TOP_GUARD = 96;

function initNavigation(navigation)
{
    const mediaQuery = window.matchMedia(MOBILE_BREAKPOINT);
    const header = document.querySelector('[data-site-header]');
    const menuToggle = header?.querySelector('[data-menu-toggle]');
    const categoriesLink = navigation.querySelector('[data-mobile-bottom-categories]');
    let lastDirectionPoint = Math.max(window.scrollY, 0);
    let animationFrame = null;

    const topGuard = () => Math.max(
        MIN_TOP_GUARD,
        Math.min(header?.offsetHeight || MIN_TOP_GUARD, 160),
    );

    const setVisible = (visible) => {
        const shouldShow = visible
            && mediaQuery.matches
            && !document.body.classList.contains('bona-menu-open')
            && window.scrollY > topGuard();

        navigation.classList.toggle('is-visible', shouldShow);
        navigation.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');
        document.body.classList.toggle('bona-mobile-bottom-nav-visible', shouldShow);
    };

    const updateFromScroll = () => {
        animationFrame = null;

        if (!mediaQuery.matches || document.body.classList.contains('bona-menu-open')) {
            setVisible(false);
            lastDirectionPoint = Math.max(window.scrollY, 0);

            return;
        }

        const currentScroll = Math.max(window.scrollY, 0);

        if (currentScroll <= topGuard()) {
            setVisible(false);
            lastDirectionPoint = currentScroll;

            return;
        }

        const delta = currentScroll - lastDirectionPoint;

        if (Math.abs(delta) < MIN_SCROLL_DELTA) {
            return;
        }

        setVisible(delta > 0);
        lastDirectionPoint = currentScroll;
    };

    const scheduleUpdate = () => {
        if (animationFrame !== null) {
            return;
        }

        animationFrame = window.requestAnimationFrame(updateFromScroll);
    };

    categoriesLink?.addEventListener('click', (event) => {
        if (!mediaQuery.matches || !menuToggle) {
            return;
        }

        event.preventDefault();
        setVisible(false);
        menuToggle.click();
    });

    window.addEventListener('scroll', scheduleUpdate, { passive: true });
    const handleBreakpointChange = () => {
        lastDirectionPoint = Math.max(window.scrollY, 0);
        setVisible(false);
    };

    if (typeof mediaQuery.addEventListener === 'function') {
        mediaQuery.addEventListener('change', handleBreakpointChange);
    } else {
        mediaQuery.addListener(handleBreakpointChange);
    }

    window.addEventListener('bona:mobile-menu-change', (event) => {
        if (event.detail?.open) {
            setVisible(false);
        }
    });
    window.addEventListener('pageshow', () => {
        lastDirectionPoint = Math.max(window.scrollY, 0);
        setVisible(false);
    });
}

export default {
    init() {
        document.querySelectorAll('[data-mobile-bottom-navigation]').forEach(initNavigation);
    },
};
