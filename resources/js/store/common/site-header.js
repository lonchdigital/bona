function initMegaMenu(header) {
    const toggle = header.querySelector('[data-mega-toggle]');
    const mega = header.querySelector('[data-mega-menu]');
    const catalog = toggle?.closest('.bona-mainnav__catalog');

    if (!toggle || !mega || !catalog) {
        return;
    }

    const tabs = [...mega.querySelectorAll('[data-mega-tab]')];
    const panels = [...mega.querySelectorAll('[data-mega-panel]')];

    const selectTab = (tab) => {
        const key = tab.dataset.megaTab;

        tabs.forEach((item) => {
            const active = item === tab;
            item.classList.toggle('is-active', active);
            item.setAttribute('aria-selected', String(active));
            item.tabIndex = active ? 0 : -1;
        });

        panels.forEach((panel) => {
            const active = panel.dataset.megaPanel === key;
            panel.classList.toggle('is-active', active);
            panel.hidden = !active;
        });
    };

    const setOpen = (open) => {
        if (window.matchMedia('(max-width: 960px)').matches) {
            open = false;
        }

        mega.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
    };

    toggle.addEventListener('click', () => setOpen(!mega.classList.contains('is-open')));
    catalog.addEventListener('focusout', (event) => {
        if (!catalog.contains(event.relatedTarget)) {
            setOpen(false);
        }
    });

    tabs.forEach((tab, index) => {
        ['mouseenter', 'focus', 'click'].forEach((eventName) => {
            tab.addEventListener(eventName, () => selectTab(tab));
        });

        tab.addEventListener('keydown', (event) => {
            if (!['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
                return;
            }

            event.preventDefault();
            let nextIndex = index;

            if (event.key === 'ArrowDown') nextIndex = (index + 1) % tabs.length;
            if (event.key === 'ArrowUp') nextIndex = (index - 1 + tabs.length) % tabs.length;
            if (event.key === 'Home') nextIndex = 0;
            if (event.key === 'End') nextIndex = tabs.length - 1;

            tabs[nextIndex].focus();
        });
    });

    document.addEventListener('pointerdown', (event) => {
        if (!catalog.contains(event.target)) {
            setOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mega.classList.contains('is-open')) {
            setOpen(false);
            toggle.focus();
        }
    });
}

function initMobileMenu(header) {
    const toggle = header.querySelector('[data-menu-toggle]');
    const navigation = header.querySelector('[data-main-navigation]');
    const mobileNavigation = navigation?.querySelector('[data-mobile-navigation]');

    if (!toggle || !navigation || !mobileNavigation) {
        return;
    }

    const panels = [...mobileNavigation.querySelectorAll('[data-mobile-menu-panel]')];
    const levelOpeners = [...mobileNavigation.querySelectorAll('[data-mobile-menu-open]')];
    const backControls = [...mobileNavigation.querySelectorAll('[data-mobile-menu-back]')];
    const mobileMedia = window.matchMedia('(max-width: 960px)');
    let activeLevel = 'root';
    let activeLevelOpener = null;

    const panelFocusable = (level) => mobileNavigation
        .querySelector(`[data-mobile-menu-panel="${level}"]`)
        ?.querySelector('a[href], button:not([disabled])');

    const setLevel = (level, { moveFocus = false, returnFocus = false } = {}) => {
        const nextPanel = panels.find((panel) => panel.dataset.mobileMenuPanel === level);
        const nextLevel = nextPanel ? level : 'root';
        const previousOpener = activeLevelOpener;

        activeLevel = nextLevel;
        activeLevelOpener = nextLevel === 'root'
            ? null
            : levelOpeners.find((opener) => opener.dataset.mobileMenuOpen === nextLevel) ?? null;
        mobileNavigation.dataset.mobileMenuLevel = nextLevel;

        panels.forEach((panel) => {
            const active = panel.dataset.mobileMenuPanel === nextLevel;
            panel.setAttribute('aria-hidden', String(!active));
            panel.toggleAttribute('inert', !active);
        });

        levelOpeners.forEach((opener) => {
            opener.setAttribute('aria-expanded', String(opener === activeLevelOpener));
        });

        if (returnFocus && previousOpener) {
            previousOpener.focus({ preventScroll: true });
        } else if (moveFocus) {
            panelFocusable(nextLevel)?.focus({ preventScroll: true });
        }
    };

    const setOpen = (open, restoreFocus = false) => {
        if (!open) {
            setLevel('root');
        }

        header.classList.toggle('is-menu-open', open);
        document.body.classList.toggle('bona-menu-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? toggle.dataset.closeLabel : toggle.dataset.openLabel);
        window.dispatchEvent(new CustomEvent('bona:mobile-menu-change', {
            detail: { open },
        }));

        if (open) {
            setLevel('root');
            panelFocusable('root')?.focus({ preventScroll: true });
        } else if (restoreFocus) {
            toggle.focus({ preventScroll: true });
        }
    };

    toggle.addEventListener('click', () => setOpen(!header.classList.contains('is-menu-open')));

    levelOpeners.forEach((opener) => {
        opener.addEventListener('click', () => {
            setLevel(opener.dataset.mobileMenuOpen, { moveFocus: true });
        });
    });

    backControls.forEach((control) => {
        control.addEventListener('click', () => setLevel('root', { returnFocus: true }));
    });

    navigation.addEventListener('click', (event) => {
        if (event.target.closest('a') && mobileMedia.matches) {
            setOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (!header.classList.contains('is-menu-open') || !mobileMedia.matches) {
            return;
        }

        if (event.key === 'Escape' && activeLevel !== 'root') {
            setLevel('root', { returnFocus: true });
            return;
        }

        if (event.key === 'Escape') {
            setOpen(false, true);
            return;
        }

        if (event.key === 'Tab') {
            const focusable = [...header.querySelectorAll('a[href], button:not([disabled]), input:not([disabled])')]
                .filter((element) => !element.closest('[hidden], [inert], [aria-hidden="true"]') && element.offsetParent !== null);

            if (focusable.length === 0) {
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 960 && header.classList.contains('is-menu-open')) {
            setOpen(false);
        } else if (window.innerWidth > 960) {
            setLevel('root');
        }
    });
}

function keepFreshHomepageAtTheHeader(header) {
    if (!window.matchMedia('(max-width: 960px)').matches
        || !header.hasAttribute('data-home-overlay-header')
        || window.location.hash) {
        return;
    }

    const navigationEntry = typeof performance.getEntriesByType === 'function'
        ? performance.getEntriesByType('navigation')[0]
        : null;
    const isFreshNavigation = !navigationEntry || navigationEntry.type === 'navigate';

    if (!isFreshNavigation) {
        return;
    }

    const resetUnexpectedOffset = () => {
        if (window.scrollY > 0) {
            window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
        }
    };

    resetUnexpectedOffset();

    if (document.readyState === 'complete') {
        requestAnimationFrame(resetUnexpectedOffset);
    } else {
        window.addEventListener('pageshow', resetUnexpectedOffset, { once: true });
    }
}

export default {
    init() {
        document.querySelectorAll('[data-site-header]').forEach((header) => {
            keepFreshHomepageAtTheHeader(header);
            initMegaMenu(header);
            initMobileMenu(header);
        });
    },
};
