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

function loadGoogleAnalytics(measurementId) {
    if (!measurementId || document.querySelector('script[data-bona-google-analytics]')) {
        return;
    }

    const script = document.createElement('script');
    script.async = true;
    script.dataset.bonaGoogleAnalytics = 'true';
    script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(measurementId)}`;
    document.head.appendChild(script);

    window.gtag('js', new Date());
    window.gtag('config', measurementId);
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

function applyChoice(choice, measurementId) {
    setGoogleConsent(choice);

    if (choice === ACCEPTED) {
        loadGoogleAnalytics(measurementId);
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

    const measurementId = banner.dataset.googleAnalyticsId || '';
    const existingChoice = readChoice();

    // Establish a denied default before any optional tag is allowed to load.
    setGoogleConsent(NECESSARY);

    if (existingChoice) {
        applyChoice(existingChoice, measurementId);
    } else {
        showBanner(banner);
    }

    banner.querySelector('[data-cookie-consent-accept]')?.addEventListener('click', () => {
        saveChoice(ACCEPTED);
        applyChoice(ACCEPTED, measurementId);
        hideBanner(banner);
    });

    banner.querySelector('[data-cookie-consent-necessary]')?.addEventListener('click', () => {
        const hadOptionalTags = readChoice() === ACCEPTED;

        saveChoice(NECESSARY);
        applyChoice(NECESSARY, measurementId);
        hideBanner(banner);

        // A Google tag that is already loaded cannot be reliably unloaded. Reload
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

export function trackGoogleEvent(eventName, parameters = {}) {
    if (readChoice() !== ACCEPTED || typeof window.gtag !== 'function' || !eventName) {
        return;
    }

    window.gtag('event', eventName, parameters);
}

export default { init };
