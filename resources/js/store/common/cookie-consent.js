const STORAGE_KEY = 'bona_cookie_consent_v1';
const ACCEPTED = 'all';
const NECESSARY = 'necessary';

function setGoogleConsent(value) {
    window.dataLayer = window.dataLayer || [];
    window.gtag = window.gtag || function gtag() {
        window.dataLayer.push(arguments);
    };

    const granted = value === ACCEPTED ? 'granted' : 'denied';

    window.gtag('consent', value === ACCEPTED ? 'update' : 'default', {
        ad_storage: granted,
        ad_user_data: granted,
        ad_personalization: granted,
        analytics_storage: granted,
        functionality_storage: 'granted',
        security_storage: 'granted',
        wait_for_update: 500,
    });
}

function loadTagManager(containerId) {
    if (!containerId || document.querySelector('script[data-bona-gtm]')) {
        return;
    }

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
        'gtm.start': Date.now(),
        event: 'gtm.js',
    });

    const script = document.createElement('script');
    script.async = true;
    script.dataset.bonaGtm = 'true';
    script.src = `https://www.googletagmanager.com/gtm.js?id=${encodeURIComponent(containerId)}`;
    document.head.appendChild(script);
}

function readChoice() {
    try {
        const value = window.localStorage.getItem(STORAGE_KEY);

        return value === ACCEPTED || value === NECESSARY ? value : null;
    } catch (error) {
        return null;
    }
}

function saveChoice(value) {
    try {
        window.localStorage.setItem(STORAGE_KEY, value);
    } catch (error) {
        // A privacy-focused browser may block storage. The selected consent
        // still applies for the current page without breaking the storefront.
    }
}

function showBanner(banner) {
    banner.hidden = false;
    window.requestAnimationFrame(() => banner.classList.add('is-visible'));
}

function hideBanner(banner) {
    banner.classList.remove('is-visible');
    window.setTimeout(() => {
        banner.hidden = true;
    }, 220);
}

function applyChoice(choice, containerId) {
    setGoogleConsent(choice);

    if (choice === ACCEPTED) {
        loadTagManager(containerId);
    }

    window.dispatchEvent(new CustomEvent('bona:cookie-consent', {
        detail: { choice },
    }));
}

function init() {
    const banner = document.querySelector('[data-cookie-consent]');

    if (!banner) {
        return;
    }

    const containerId = banner.dataset.gtmId || '';
    const existingChoice = readChoice();

    // Establish a denied default before any optional tag is allowed to load.
    setGoogleConsent(NECESSARY);

    if (existingChoice) {
        applyChoice(existingChoice, containerId);
    } else {
        showBanner(banner);
    }

    banner.querySelector('[data-cookie-consent-accept]')?.addEventListener('click', () => {
        saveChoice(ACCEPTED);
        applyChoice(ACCEPTED, containerId);
        hideBanner(banner);
    });

    banner.querySelector('[data-cookie-consent-necessary]')?.addEventListener('click', () => {
        const hadOptionalTags = readChoice() === ACCEPTED;

        saveChoice(NECESSARY);
        applyChoice(NECESSARY, containerId);
        hideBanner(banner);

        // Scripts already loaded by GTM cannot be reliably unloaded. Reload
        // once after a withdrawal so the new denied choice takes effect fully.
        if (hadOptionalTags) {
            window.location.reload();
        }
    });

    document.querySelectorAll('[data-cookie-settings]').forEach((button) => {
        button.addEventListener('click', () => {
            showBanner(banner);
            banner.querySelector('button')?.focus();
        });
    });
}

export default { init };
