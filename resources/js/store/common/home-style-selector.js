function initSelector(selector) {
    const tabs = Array.from(selector.querySelectorAll('[data-style-tab]'));
    const panes = Array.from(selector.querySelectorAll('[data-style-pane]'));

    if (tabs.length === 0 || tabs.length !== panes.length) {
        return;
    }

    const select = (index, focus = false) => {
        tabs.forEach((tab, tabIndex) => {
            const isActive = tabIndex === index;
            tab.classList.toggle('is-active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            tab.tabIndex = isActive ? 0 : -1;

            if (isActive && focus) {
                tab.focus();
            }
        });

        panes.forEach((pane, paneIndex) => {
            const isActive = paneIndex === index;
            pane.classList.toggle('is-active', isActive);
            pane.hidden = !isActive;
        });
    };

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => select(index));
        tab.addEventListener('mouseenter', () => select(index));
        tab.addEventListener('focus', () => select(index));
        tab.addEventListener('keydown', event => {
            if (!['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
                return;
            }

            event.preventDefault();
            const nextIndex = event.key === 'Home'
                ? 0
                : event.key === 'End'
                    ? tabs.length - 1
                    : (index + (event.key === 'ArrowDown' ? 1 : -1) + tabs.length) % tabs.length;
            select(nextIndex, true);
        });
    });
}

export function init() {
    document.querySelectorAll('[data-home-style-selector]').forEach(initSelector);
}

export default { init };
